<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

declare(strict_types=1);

use ILIAS\HTTP\Services;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use srag\Plugins\AttendanceList\Cron\AttendanceListJob;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationsCtrl;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAttendanceListConfigGUI
 *
 * @ilCtrl_IsCalledBy  ilAttendanceListConfigGUI: ilObjComponentSettingsGUI
 * @ilCtrl_isCalledBy srag\Plugins\AttendanceList\Notification\Notification\NotificationsCtrl: ilAttendanceListConfigGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAttendanceListConfigGUI extends ilPluginConfigGUI
{
    use Notifications4PluginTrait;

    public const SUBTAB_CONFIG = 'config';
    public const SUBTAB_ABSENCE_REASONS = 'absence_reasons';
    //const SUBTAB_NOTIFICATION_ABSENCE = NotificationsCtrl::TAB_NOTIFICATIONS . '_absence';
    //const SUBTAB_NOTIFICATION_ABSENCE_REMINDER = NotificationsCtrl::TAB_NOTIFICATIONS . '_absence_reminder';

    public const CMD_STANDARD = 'configure';
    public const CMD_ADD_REASON = 'addReason';
    public const CMD_SHOW_REASONS = 'showReasons';
    public const CMD_CREATE_REASON = 'createReason';
    public const CMD_EDIT_REASON = 'editReason';
    public const CMD_UPDATE_REASON = 'updateReason';
    public const CMD_DELETE_REASON = 'deleteReason';
    public const CMD_UPDATE_CONFIG = 'updateConfig';

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilAttendanceListPlugin $pl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    private Services $http;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $httpWrapper;
    private \ILIAS\Refinery\Factory $refinery;

    /**
     * ilAttendanceListConfigGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $ilToolbar = $DIC->toolbar();
        $ilTabs = $DIC->tabs();
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->tabs = $ilTabs;
        $this->http = $DIC->http();
        $this->httpWrapper = $this->http->wrapper();
        $this->refinery = $DIC->refinery();

        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        /** @var $plugin ilAttendanceListPlugin */
        $this->pl = $component_factory->getPlugin(ilAttendanceListPlugin::PLUGIN_ID);

        // this is for the cron job, since the ILIAS_HTTP_PATH is not initialized in cron context
        if (!xaliConfig::getConfig(xaliConfig::F_HTTP_PATH)) {
            xaliConfig::set(xaliConfig::F_HTTP_PATH, ILIAS_HTTP_PATH);
        }
    }

    public function performCommand($cmd): void
    {
        $this->tabs->addSubTab(self::SUBTAB_CONFIG, $this->pl->txt('subtab_'
            . self::SUBTAB_CONFIG), $this->ctrl->getLinkTarget($this, self::CMD_STANDARD));

        $this->tabs->addSubTab(self::SUBTAB_ABSENCE_REASONS, $this->pl->txt('subtab_'
            . self::SUBTAB_ABSENCE_REASONS), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_REASONS));

        //todo
        /*
        $this->ctrl->setParameterByClass(NotificationCtrl::class, NotificationCtrl::GET_PARAM_NOTIFICATION_ID, self::notifications4plugin()->notifications()
            ->getNotificationByName(xaliChecklistEntry::NOTIFICATION_NAME)->getId());
        $this->tabs->addSubTab(self::SUBTAB_NOTIFICATION_ABSENCE, $this->pl->txt('subtab_'
            . self::SUBTAB_NOTIFICATION_ABSENCE), $this->ctrl->getLinkTargetByClass([NotificationsCtrl::class, NotificationCtrl::class], NotificationCtrl::CMD_EDIT_NOTIFICATION));

        $this->ctrl->setParameterByClass(NotificationCtrl::class, NotificationCtrl::GET_PARAM_NOTIFICATION_ID, self::notifications4plugin()->notifications()
            ->getNotificationByName(xaliCron::NOTIFICATION_NAME)->getId());
        $this->tabs->addSubTab(self::SUBTAB_NOTIFICATION_ABSENCE_REMINDER, $this->pl->txt('subtab_'
            . self::SUBTAB_NOTIFICATION_ABSENCE_REMINDER), $this->ctrl->getLinkTargetByClass([NotificationsCtrl::class, NotificationCtrl::class], NotificationCtrl::CMD_EDIT_NOTIFICATION));
        */

        switch ($this->ctrl->getNextClass($this)) {
            /*case strtolower(NotificationsCtrl::class):
                if ($this->ctrl->getCmd() === NotificationsCtrl::CMD_LIST_NOTIFICATIONS) {
                    $this->ctrl->redirect($this, self::CMD_STANDARD);

                    return;
                }
                $notification = self::notifications4plugin()->notifications()->getNotificationById(intval(filter_input(INPUT_GET, NotificationCtrl::GET_PARAM_NOTIFICATION_ID)));
                if ($notification !== null) {
                    switch ($notification->getName()) {
                        case xaliChecklistEntry::NOTIFICATION_NAME:
                            $this->tabs->activateSubTab(self::SUBTAB_NOTIFICATION_ABSENCE);
                            self::notifications4plugin()->withPlaceholderTypes([
                                'user'    => 'object ' . ilObjUser::class,
                                'absence' => 'string'
                            ]);
                            break;

                        case xaliCron::NOTIFICATION_NAME:
                            $this->tabs->activateSubTab(self::SUBTAB_NOTIFICATION_ABSENCE_REMINDER);
                            self::notifications4plugin()->withPlaceholderTypes([
                                'user'          => 'object ' . ilObjUser::class,
                                'open_absences' => 'string'
                            ]);
                            break;

                        default:
                            break;
                    }
                }

                $this->ctrl->forwardCommand(new NotificationsCtrl());
                break;*/
            default:
                // this is redirect-abuse and should be somehow
                switch ($cmd) {
                    case self::CMD_SHOW_REASONS:
                        $this->addToolbarButton();
                        break;
                }
                $this->{$cmd}();
        }
    }

    protected function configure(): void
    {
        $this->tabs->activateSubTab(self::SUBTAB_CONFIG);
        $xaliConfigFormGUI = new xaliConfigFormGUI($this);
        $xaliConfigFormGUI->fillForm();
        $this->tpl->setContent($xaliConfigFormGUI->getHTML());
    }

    protected function showReasons(): void
    {
        $this->tabs->activateSubTab(self::SUBTAB_ABSENCE_REASONS);
        $xaliConfigAbsencesTableGUI = new xaliConfigAbsencesTableGUI($this);
        $this->tpl->setContent($xaliConfigAbsencesTableGUI->getHTML());
    }

    protected function addReason(): void
    {
        $xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason());
        $this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
    }

    protected function createReason(): void
    {
        $xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason());
        $xaliConfigAbsenceFormGUI->setValuesByPost();
        if ($xaliConfigAbsenceFormGUI->saveObject()) {

            $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_saved"), true);

            $this->ctrl->redirect($this, self::CMD_SHOW_REASONS);
        }
        $this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
    }

    protected function editReason(): void
    {
        $arId = $this->httpWrapper->query()->retrieve(
            "ar_id",
            $this->refinery->kindlyTo()->int()
        );
        $xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason($arId));
        $xaliConfigAbsenceFormGUI->fillForm();
        $this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
    }


    protected function updateReason(): void
    {
        $arId = $this->httpWrapper->query()->retrieve(
            "ar_id",
            $this->refinery->kindlyTo()->int()
        );
        $xaliConfigAbsenceFormGUI = new xaliConfigAbsenceFormGUI($this, new xaliAbsenceReason($arId));
        $xaliConfigAbsenceFormGUI->setValuesByPost();
        if ($xaliConfigAbsenceFormGUI->saveObject()) {

            $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_saved"), true);

            $this->ctrl->redirect($this, self::CMD_SHOW_REASONS);
        }
        $this->tpl->setContent($xaliConfigAbsenceFormGUI->getHTML());
    }

    protected function updateConfig(): void
    {
        $xaliConfigFormGUI = new xaliConfigFormGUI($this);
        $xaliConfigFormGUI->setValuesByPost();
        if ($xaliConfigFormGUI->saveObject()) {

            $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_saved"), true);

            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }
        $this->tpl->setContent($xaliConfigFormGUI->getHTML());
    }

    protected function addToolbarButton(): void
    {
        $button = ilLinkButton::getInstance();
        $button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_REASON));
        $button->setCaption($this->pl->txt('config_add_new_absence_reason'), false);
        $this->toolbar->addButtonInstance($button);
    }


    protected function deleteReason(): void
    {
        $arId = $this->httpWrapper->query()->retrieve(
            "ar_id",
            $this->refinery->kindlyTo()->int()
        );
        (new xaliAbsenceReason($arId))->delete();

        $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_deleted"), true);

        $this->ctrl->redirect($this, self::CMD_SHOW_REASONS);
    }
}
