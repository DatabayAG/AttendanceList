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

class xaliGUI
{
    public const CMD_STANDARD = 'show';
    public const CMD_CANCEL = 'cancel';

    protected mixed $tpl;
    protected mixed $ctrl;
    protected ilAttendanceListPlugin|ilPlugin $pl;
    protected ilObjAttendanceListGUI $parent_gui;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;

    public function __construct(ilObjAttendanceListGUI $parent_gui)
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $ilToolbar = $DIC->toolbar();
        $this->toolbar = $ilToolbar;
        $this->user = $ilUser;
        $this->lng = $lng;
        $this->tabs = $ilTabs;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        /** @var $component_factory ilComponentFactory */
        $component_factory = $DIC['component.factory'];
        $this->pl = $component_factory->getPlugin(ilAttendanceListPlugin::PLUGIN_ID);
        $this->parent_gui = $parent_gui;
    }

    /**
     *
     */
    public function executeCommand(): void
    {
        $this->prepareOutput();
        if (ilObjAttendanceListAccess::hasWriteAccess()) {
            $this->parent_gui->checkPassedIncompleteLists();
        }

        $nextClass = $this->ctrl->getNextClass();

        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(static::CMD_STANDARD);
                $this->{$cmd}();
                break;
        }
    }

    protected function prepareOutput(): void
    {
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, static::CMD_STANDARD);
    }
}
