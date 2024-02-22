<?php

/** @noinspection NullPointerExceptionInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */

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

use ILIAS\HTTP\Wrapper\WrapperFactory;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @ilCtrl_isCalledBy   ilObjAttendanceListGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls        ilObjAttendanceListGUI: xaliChecklistGUI, xaliSettingsGUI, xaliOverviewGUI
 * @ilCtrl_Calls        ilObjAttendanceListGUI: xaliAbsenceStatementGUI
 * @ilCtrl_Calls        ilObjAttendanceListGUI: ilInfoScreenGUI, ilPermissionGUI, ilCommonActionDispatcherGUI, ilLearningProgressGUI
 */
class ilObjAttendanceListGUI extends ilObjectPluginGUI
{
    public const CMD_STANDARD = 'showContent';
    public const CMD_OVERVIEW = 'showOverview';
    public const CMD_EDIT_SETTINGS = 'editSettings';
    public const TAB_CONTENT = 'tab_content';
    public const TAB_OVERVIEW = 'tab_overview';
    public const TAB_SETTINGS = 'tab_settings';
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs;
    protected ilAttendanceListPlugin|ilPlugin $pl;
    protected ilObjAttendanceListAccess $ilObjAttendanceListAccess;
    protected ilRbacReview $rbacreview;
    protected ilObjUser $user;
    protected xaliSetting $setting;
    private WrapperFactory $httpWrapper;


    protected function afterConstructor(): void
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $tree = $DIC->repositoryTree();
        $rbacreview = $DIC->rbac()->review();
        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->ilObjAttendanceListAccess = new ilObjAttendanceListAccess();
        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        /** @var $plugin ilAttendanceListPlugin */
        $this->pl = $component_factory->getPlugin(ilAttendanceListPlugin::PLUGIN_ID);
        $this->tree = $tree;
        $this->user = $ilUser;
        $this->rbacreview = $rbacreview;
        $this->httpWrapper = $DIC->http()->wrapper();
    }

    public function getType(): string
    {
        return ilAttendanceListPlugin::PLUGIN_ID;
    }

    protected function initHeaderAndLocator(): void
    {
        global $DIC;
        $ilNavigationHistory = $DIC['ilNavigationHistory'];

        $refId = $this->httpWrapper->query()->retrieve(
            "ref_id",
            $this->refinery->kindlyTo()->int()
        );

        $this->setTitleAndDescription();
        // set title
        if (!$this->getCreationMode()) {
            $this->tpl->setTitle($this->object->getTitle());
            $this->tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));

            $baseClass = $this->httpWrapper->query()->retrieve(
                "baseClass",
                $this->refinery->kindlyTo()->string()
            );

            if (strtolower($baseClass) !== 'iladministrationgui') {
                if (strtolower($this->ctrl->getCmdClass()) !== 'xaliabsencestatementgui') {
                    $this->setTabs();
                }
                $this->setLocator();
            } else {
                $this->addAdminLocatorItems();
                $this->tpl->setLocator();
                $this->setAdminTabs();
            }

            global $DIC;
            $ilAccess = $DIC->access();
            // add entry to navigation history

            if ($ilAccess->checkAccess('read', '', $refId)) {
                $ilNavigationHistory->addItem($refId, $this->ctrl->getLinkTarget($this, $this->getStandardCmd()), $this->getType());
            }
        } else {
            // show info of parent
            $this->tpl->setTitle(ilObject::_lookupTitle(ilObject::_lookupObjId($refId)));
            $this->tpl->setTitleIcon(ilObject::_getIcon(ilObject::_lookupObjId($refId)), $this->pl->txt('obj_'
                . ilObject::_lookupType($refId, true)));
            $this->setLocator();
        }
    }

    public function executeCommand(): void
    {
        $this->initHeaderAndLocator();

        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);

        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'xalichecklistgui':
                $this->checkPermission("read");
                $xaliChecklistGUI = new xaliChecklistGUI($this);
                $this->tabs->setTabActive(self::TAB_CONTENT);
                $this->ctrl->forwardCommand($xaliChecklistGUI);
                break;
            case "ilinfoscreengui":
                // cmd here is showSummary
                $this->checkPermission("visible");
                parent::infoScreen();    // forwards command
                break;
            case 'xalioverviewgui':
                $this->checkPermission("write");
                $xaliOverviewGUI = new xaliOverviewGUI($this);
                $this->tabs->setTabActive(self::TAB_OVERVIEW);
                $this->ctrl->forwardCommand($xaliOverviewGUI);
                break;
            case 'xalisettingsgui':
                $this->checkPermission("write");
                $xaliSettingsGUI = new xaliSettingsGUI($this);
                $this->tabs->setTabActive(self::TAB_SETTINGS);
                $this->ctrl->forwardCommand($xaliSettingsGUI);
                break;
            case 'xaliabsencestatementgui':
                $entryId = $this->httpWrapper->query()->retrieve(
                    "entry_id",
                    $this->refinery->kindlyTo()->int()
                );

                if (xaliChecklistEntry::find($entryId)->getUserId() != $this->user->getId()) {
                    $this->checkPermission("write");
                }
                $xaliAbsenceStatementGUI = new xaliAbsenceStatementGUI($this);
                $this->ctrl->forwardCommand($xaliAbsenceStatementGUI);
                break;
            case 'ilpermissiongui':
                $this->checkPermission("edit_permission");
                $perm_gui = new ilPermissionGUI($this);
                $this->tabs->setTabActive("id_permissions");
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                $this->$cmd();
                break;
        }
        if ($cmd !== 'create') {
            $this->tpl->printToStdout();
        }
    }

    public static function _goto($a_target): void
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];
        $class_name = $a_target[1];

        if (count($t) == 2) {
            $entry_id = $t[1];
            $ilCtrl->setTargetScript("ilias.php");
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->setParameterByClass($class_name, "entry_id", $entry_id);
            $ilCtrl->redirectByClass(["ilobjplugindispatchgui", self::class, xaliAbsenceStatementGUI::class], xaliAbsenceStatementGUI::CMD_STANDARD);
        }

        if ($ilAccess->checkAccess("read", "", $ref_id)) {
            $ilCtrl->setTargetScript("ilias.php");
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->redirectByClass(["ilobjplugindispatchgui", $class_name], "");
        } elseif ($ilAccess->checkAccess("visible", "", $ref_id)) {
            $ilCtrl->setTargetScript("ilias.php");
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->redirectByClass(["ilobjplugindispatchgui", $class_name], "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            global $DIC;
            $tpl = $DIC->ui()->mainTemplate();
            $tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    public function infoScreen(): void
    {
        $this->ctrl->setCmd('showSummary');
        $this->ctrl->setCmdClass("ilinfoscreengui");
        parent::infoScreen();
    }

    protected function setTabs(): void
    {
        $this->tabs->addTab(self::TAB_CONTENT, $this->pl->txt(self::TAB_CONTENT), $this->ctrl->getLinkTargetByClass(xaliChecklistGUI::class, xaliChecklistGUI::CMD_STANDARD));
        $this->addInfoTab();
        if (ilObjAttendanceListAccess::hasWriteAccess()) {
            $this->tabs->addTab(self::TAB_OVERVIEW, $this->pl->txt(self::TAB_OVERVIEW), $this->ctrl->getLinkTargetByClass(xaliOverviewGUI::class, xaliOverviewGUI::CMD_STANDARD));
            $this->tabs->addTab(self::TAB_SETTINGS, $this->pl->txt(self::TAB_SETTINGS), $this->ctrl->getLinkTargetByClass(xaliSettingsGUI::class, xaliSettingsGUI::CMD_STANDARD));
        }
        parent::setTabs();
    }

    public function showContent(): void
    {
        $this->ctrl->redirectByClass(xaliChecklistGUI::class, xaliChecklistGUI::CMD_STANDARD);
    }

    public function showOverview(): void
    {
        $this->ctrl->redirectByClass(xaliOverviewGUI::class, xaliOverviewGUI::CMD_STANDARD);
    }

    public function editSettings(): void
    {
        $this->ctrl->redirectByClass(xaliSettingsGUI::class, xaliSettingsGUI::CMD_STANDARD);
    }

    public function getAfterCreationCmd(): string
    {
        return self::CMD_EDIT_SETTINGS;
    }

    public function getStandardCmd(): string
    {
        return self::CMD_STANDARD;
    }

    protected function initCreationForms(string $a_new_type): array
    {
        try {
            $refId = $this->httpWrapper->query()->retrieve(
                "ref_id",
                $this->refinery->kindlyTo()->int()
            );
            $this->getParentCourseOrGroupId($refId);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->pl->txt('msg_creation_failed'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }

        $forms = [
            self::CFORM_NEW => $this->initCreateForm($a_new_type),
        ];

        return $forms;
    }

    public function initCreateForm(string $a_new_type): ilPropertyFormGUI
    {
        $form = parent::initCreateForm($a_new_type);

        $from = new ilDateTimeInputGUI($this->pl->txt(xaliSettingsFormGUI::F_ACTIVATION_FROM), xaliSettingsFormGUI::F_ACTIVATION_FROM);
        $from->setRequired(true);
        $form->addItem($from);

        $to = new ilDateTimeInputGUI($this->pl->txt(xaliSettingsFormGUI::F_ACTIVATION_TO), xaliSettingsFormGUI::F_ACTIVATION_TO);
        $to->setRequired(true);
        $form->addItem($to);

        $wd = new srWeekdayInputGUI($this->pl->txt(xaliSettingsFormGUI::F_WEEKDAYS), xaliSettingsFormGUI::F_WEEKDAYS);
        $form->addItem($wd);

        $form->setPreventDoubleSubmission(false);

        return $form;
    }

    public function save(): void
    {
        $form = $this->initCreateForm($this->getType());
        $form->setValuesByPost();
        $form->checkInput();

        $this->setting = new xaliSetting();
        $this->setting->setActivation(1);

        $from = $form->getInput(xaliSettingsFormGUI::F_ACTIVATION_FROM);
        $this->setting->setActivationFrom($from);

        $to = $form->getInput(xaliSettingsFormGUI::F_ACTIVATION_TO);
        $this->setting->setActivationTo($to);

        $weekdays = (array) $form->getInput(xaliSettingsFormGUI::F_WEEKDAYS);


        $this->setting->setActivationWeekdays($weekdays === [""] ? [] : $weekdays);

        $this->saveObject();
    }

    public function afterSave(ilObject $newObj): void
    {
        $this->setting->setId($newObj->getId());
        $this->setting->create();
        $this->setting->createOrDeleteEmptyLists(true, false);

        parent::afterSave($newObj);
    }

    public function checkPassedIncompleteLists(): bool
    {
        $members_count = count($this->getMembers());
        foreach (xaliChecklist::where(['obj_id' => $this->obj_id])->get() as $checklist) {
            if (date('Y-m-d') > $checklist->getChecklistDate()
                && ($checklist->getEntriesCount() < $members_count)) {
                $link_to_overview = $this->ctrl->getLinkTargetByClass(xaliOverviewGUI::class, xaliOverviewGUI::CMD_LISTS);

                $this->tpl->setOnScreenMessage('info', $this->pl->txt('msg_incomplete_lists'), true);

                return true;
            }
        }

        return false;
    }

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

    public function getMembers(): array
    {
        return $this->pl->getMembers($this->object->getRefId());
    }

    public function performCommand(string $cmd): void
    {
        // TODO: Implement performCommand() method.
    }
}
