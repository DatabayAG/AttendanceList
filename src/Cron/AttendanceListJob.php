<?php

namespace srag\Plugins\AttendanceList\Cron;

use ilAttendanceListPlugin;
use ilCronJob;
use ilCronJobResult;
use ilCtrl;
use ilDBInterface;
use ILIAS;
use ILIAS\Cron\Schedule\CronJobScheduleType;
use ilLogger;
use ilObjUser;
use ilRbacReview;
use xaliAbsenceStatement;
use xaliChecklist;
use xaliChecklistEntry;
use xaliConfig;
use xaliLastReminder;
use xaliSetting;
use xaliUserStatus;

/**
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class AttendanceListJob extends ilCronJob
{
    public const CRON_JOB_ID = ilAttendanceListPlugin::PLUGIN_ID;

    public const DEBUG = false;
    public const NOTIFICATION_NAME = "absence_reminder";
    protected ILIAS $ilias;
    protected ilAttendanceListPlugin $pl;
    protected ilLogger $log;
    protected ilRbacReview $rbacreview;
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;

    /**
     * AttendanceListJob constructor
     */
    public function __construct(ilAttendanceListPlugin $pl)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $ilLog = $DIC->logger()->root();
        $rbacreview = $DIC->rbac()->review();
        if (self::DEBUG) {
            $ilLog->write('Auth passed for async AttendanceList');
        }

        $this->pl = $pl;
        $this->db = $ilDB;
        $this->user = $ilUser;
        $this->ctrl = $ilCtrl;
        $this->log = $ilLog;
        $this->rbacreview = $rbacreview;
    }

    public function getId(): string
    {
        return self::CRON_JOB_ID;
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }


    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_IN_MINUTES;
    }


    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }


    public function getTitle(): string
    {
        return ilAttendanceListPlugin::PLUGIN_NAME . ": " . $this->pl->txt("cron_title");
    }


    public function getDescription(): string
    {
        return $this->pl->txt("cron_description");
    }


    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();

        $this->sendAbsenceReminders();
        $this->updateLearningProgress();

        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }

    protected function sendAbsenceReminders(): void
    {
        $interval = xaliConfig::getConfig(xaliConfig::F_INTERVAL_REMINDER_EMAIL);
        if (!$interval) {
            return;
        }

        $send_mail = [];

        $now = date('Y-m-d');
        $now_minus_30_days = date('Y-m-d', strtotime('-30 days'));

        // fetch open absence statements for checklists which are online
        $query = "
			SELECT 
			    " . xaliChecklistEntry::DB_TABLE_NAME . ".*, object_reference.ref_id, " . xaliChecklist::DB_TABLE_NAME . ".checklist_date
			FROM
			    " . xaliSetting::DB_TABLE_NAME . "
				    INNER JOIN 
				object_reference on " . xaliSetting::DB_TABLE_NAME . ".id = object_reference.obj_id
			        INNER JOIN
			    " . xaliChecklist::DB_TABLE_NAME . " ON " . xaliChecklist::DB_TABLE_NAME . ".obj_id = " . xaliSetting::DB_TABLE_NAME . ".id
					INNER JOIN 
				" . xaliChecklistEntry::DB_TABLE_NAME . " ON " . xaliChecklistEntry::DB_TABLE_NAME . ".checklist_id = " . xaliChecklist::DB_TABLE_NAME
            . ".id
					LEFT JOIN
				" . xaliAbsenceStatement::TABLE_NAME . " ON " . xaliAbsenceStatement::TABLE_NAME . ".entry_id = " . xaliChecklistEntry::DB_TABLE_NAME
            . ".id
			WHERE
			    " . xaliSetting::DB_TABLE_NAME . ".is_online = 1
			        AND " . xaliSetting::DB_TABLE_NAME . ".activation_from <= '$now'
			        AND " . xaliSetting::DB_TABLE_NAME . ".activation_to > '$now_minus_30_days'
					AND " . xaliChecklistEntry::DB_TABLE_NAME . ".status = 1
					AND " . xaliAbsenceStatement::TABLE_NAME . ".entry_id IS NULL
					AND object_reference.deleted IS NULL;";

        $sql = $this->db->query($query);
        // array format:
        // [ user_id =>
        //      [ ref_id =>
        //          [ entry_id => checklist_date ]
        //      ]
        // ]
        while ($res = $this->db->fetchAssoc($sql)) {
            if (!ilObjUser::_exists($res['user_id'], false, 'usr')) {
                continue;
            }
            if (!isset($send_mail[$res['user_id']])) {
                $send_mail[$res['user_id']] = [];
            }
            if (!isset($send_mail[$res['user_id']][$res['ref_id']])) {
                $send_mail[$res['user_id']][$res['ref_id']] = [];
            }
            $send_mail[$res['user_id']][$res['ref_id']][$res['id']] = $res['checklist_date'];
        }

        // send mails
        foreach ($send_mail as $user_id => $array) {
            /** @var xaliLastReminder $last_reminder */
            $last_reminder = xaliLastReminder::where(['user_id' => $user_id])->first();

            if (!$last_reminder) {
                $last_reminder = new xaliLastReminder();
                $last_reminder->setUserId($user_id);
                $last_reminder->create();
            }

            if ($last_reminder->getLastReminder() > date('Y-m-d', strtotime("now -$interval days"))) {
                continue;
            }

            $ilObjUser = new ilObjUser($user_id);
            $sender_id = xaliConfig::getConfig(xaliConfig::F_SENDER_REMINDER_EMAIL);
            $sender = self::notifications4plugin()->sender()->factory()->internalMail($sender_id, $user_id);

            if (!$sender_id) {
                return;
            }

            $open_absences = '';
            foreach ($array as $ref_id => $entry_array) {
                $parent_course = $this->pl->getParentCourseOrGroup($ref_id);

                // check if user is still assigned to course
                if (!$this->rbacreview->isAssigned($user_id, $parent_course->getDefaultMemberRole())) {
                    continue;
                }

                $base_link = xaliConfig::getConfig(xaliConfig::F_HTTP_PATH) . '/goto.php?target=xali_' . $ref_id;

                $open_absences .= 'Kurs "' . $parent_course->getTitle() . "\": \n";
                foreach ($entry_array as $entry_id => $checklist_date) {
                    $open_absences .= "» $checklist_date: " . $base_link . "_$entry_id \n";
                }
                $open_absences .= "\n";
            }

            if (!$open_absences) {
                continue;
            }

            $placeholders = ['user' => $ilObjUser, 'open_absences' => $open_absences];

            try {
                $notification = self::notifications4plugin()->notifications()->getNotificationByName(self::NOTIFICATION_NAME);
                self::notifications4plugin()->sender()->send($sender, $notification, $placeholders);

                $last_reminder->setLastReminder(date('Y-m-d'));
                $last_reminder->update();
            } catch (Notifications4PluginException $ex) {

            }
        }
    }

    protected function updateLearningProgress(): void
    {
        $yesterday = date("Y-m-d", time() - 60 * 60 * 24);
        /** @var xaliSetting $setting */
        foreach (xaliSetting::where(['activation_to' => $yesterday])->get() as $setting) {
            xaliUserStatus::updateUserStatuses($setting->getId());
        }

    }
}
