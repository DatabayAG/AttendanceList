<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\Container;
use srag\Plugins\AttendanceList\Cron\AttendanceListJob;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Loader\CustomInputGUIsLoaderDetector;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

require_once __DIR__ . '/../vendor/autoload.php';


class ilAttendanceListPlugin extends ilRepositoryObjectPlugin implements ilCronJobProvider
{
    use Notifications4PluginTrait;

    public const PLUGIN_ID = 'xali';
    public const PLUGIN_NAME = 'AttendanceList';
    public const PLUGIN_CLASS_NAME = self::class;

    protected static bool $init_notifications = false;
    private Container $dic;


    public static function initNotifications(): void
    {
        if (!self::$init_notifications) {
            self::$init_notifications = true;
            self::notifications4plugin()->withTableNamePrefix(self::PLUGIN_ID)->withPlugin(self::getInstance());
        }
    }

    protected static ilAttendanceListPlugin $instance;
    protected ilDBInterface $db;

    public function __construct(
        ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($db, $component_repository, $id);

        $this->db = $DIC->database();
    }

    public static function getInstance(): ilAttendanceListPlugin
    {
        if (!isset(self::$instance)) {
            global $DIC;

            /** @var $component_factory ilComponentFactory */
            $component_factory = $DIC['component.factory'];
            /** @var $plugin ilAttendanceListPlugin */
            $plugin = $component_factory->getPlugin(self::PLUGIN_ID);

            self::$instance = $plugin;
        }

        return self::$instance;
    }


    protected function init(): void
    {
        self::initNotifications();
    }

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }


    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        return CustomInputGUIsLoaderDetector::exchangeUIRendererAfterInitialization();
    }

    protected function uninstallCustom(): void
    {
        $this->db->dropTable(xaliConfig::TABLE_NAME, false);
        $this->db->dropTable(xaliLastReminder::TABLE_NAME, false);
        $this->db->dropTable(xaliAbsenceReason::TABLE_NAME, false);
        $this->db->dropTable(xaliAbsenceStatement::TABLE_NAME, false);
        $this->db->dropTable(xaliSetting::DB_TABLE_NAME, false);
        $this->db->dropTable(xaliChecklist::DB_TABLE_NAME, false);
        $this->db->dropTable(xaliChecklistEntry::DB_TABLE_NAME, false);
        $this->db->dropTable(xaliUserStatus::TABLE_NAME, false);
        self::notifications4plugin()->dropTables();
    }

    /**
     * The ref id is unambiguous since there can't be references to attendance lists.
     */
    public static function lookupRefId(int $obj_id): ?int
    {
        $allReferences = ilObject2::_getAllReferences($obj_id);
        return array_shift($allReferences);
    }

    public function getMembers(int $ref_id = 0): array
    {
        $httpWrapper = $this->dic->http()->wrapper();
        $refinery = $this->dic->refinery();
        $rbacreview = $this->dic->rbac()->review();
        static $members;
        if (!$members) {

            if (!$ref_id) {
                $ref_id = $httpWrapper->query()->retrieve(
                    "ref_id",
                    $refinery->kindlyTo()->int()
                );
            }

            $parent = $this->getParentCourseOrGroup($ref_id);
            $member_role = $parent->getDefaultMemberRole();
            $members = $rbacreview->assignedUsers($member_role);
            $members = array_filter($members, function ($usr_id) {
                return ilObjUser::_exists($usr_id);
            });
        }

        return $members;
    }

    /**
     * @throws Exception
     */
    public function getParentCourseOrGroup(int $ref_id = 0): ilObjGroup|ilObjCourse
    {
        $httpWrapper = $this->dic->http()->wrapper();
        $refinery = $this->dic->refinery();
        if (!$ref_id) {
            $refId = $httpWrapper->query()->retrieve(
                "ref_id",
                $refinery->kindlyTo()->int()
            );
        }

        return ilObjectFactory::getInstanceByRefId($this->getParentCourseOrGroupId($ref_id));
    }

    /**
     * @throws Exception
     */
    public function getParentCourseOrGroupId(int $ref_id): int
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $orig_ref_id = $ref_id;
        while (!in_array(ilObject2::_lookupType($ref_id, true), ['crs', 'grp'])) {
            if ($ref_id == 1 || !$ref_id) {
                throw new Exception("Parent of ref id {$orig_ref_id} is neither course nor group.");
            }
            $ref_id = (int) $tree->getParentId($ref_id);
        }

        return $ref_id;
    }

    public function getAttendancesForUserAndCourse(int $user_id, int $crs_ref_id): array
    {
        $obj_id = $this->getAttendanceListIdForCourse($crs_ref_id);
        $settings = new xaliSetting($obj_id);

        /** @var xaliUserStatus $xaliUserStatus */
        $xaliUserStatus = xaliUserStatus::getInstance($user_id, $obj_id);

        return [
            'present' => $xaliUserStatus->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT),
            'absent' => $xaliUserStatus->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED),
            'unedited' => $xaliUserStatus->getUnedited(),
            'percentage' => $xaliUserStatus->getReachedPercentage(),
            'minimum_attendance' => $obj_id ? $xaliUserStatus->calcMinimumAttendance() : 0
        ];
    }

    public function getAttendanceListIdForCourse(int $crs_ref_id, bool $get_ref_id = false): int
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $subTree = $tree->getSubTree($tree->getNodeData($crs_ref_id), true, [$this->getId()]);
        $attendancelist = array_shift($subTree);
        $ref_id = $attendancelist['child'];
        if ($get_ref_id) {
            return $ref_id;
        }

        return ilObjAttendanceList::_lookupObjectId($ref_id);
    }


    public function allowCopy(): bool
    {
        return true;
    }

    public function getCronJobInstances(): array
    {
        return [
            new AttendanceListJob($this)
        ];
    }

    public function getCronJobInstance(string $jobId): ilCronJob
    {
        foreach ($this->getCronJobInstances() as $cronJobInstance) {
            if ($cronJobInstance->getId() === $jobId) {
                return $cronJobInstance;
            }
        }
        throw new Exception("No cron job found with the id '$jobId'.");
    }
}
