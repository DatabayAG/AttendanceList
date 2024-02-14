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

/**
 * Class xaliConfigFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliConfigFormGUI extends ilPropertyFormGUI
{
    protected ilAttendanceListPlugin $pl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct($parent_gui)
    {
        global $DIC;
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->pl = ilAttendanceListPlugin::getInstance();
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->http = $DIC->http();

        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->initForm();
    }

    protected function initForm(): void
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->pl->txt('config_header_general'));
        $this->addItem($section);

        $subinput = new ilCheckboxInputGUI($this->pl->txt('config_' . xaliConfig::F_SHOW_NOT_RELEVANT), xaliConfig::F_SHOW_NOT_RELEVANT);
        $subinput->setInfo($this->pl->txt('config_' . xaliConfig::F_SHOW_NOT_RELEVANT . '_info'));
        $this->addItem($subinput);

        $subinput = new ilCheckboxInputGUI($this->pl->txt('config_' . xaliConfig::F_SHOW_PRESENT_TOTAL), xaliConfig::F_SHOW_PRESENT_TOTAL);
        $subinput->setInfo($this->pl->txt('config_' . xaliConfig::F_SHOW_PRESENT_TOTAL . '_info'));
        $this->addItem($subinput);

        /*
      $section = new ilFormSectionHeaderGUI();
      $section->setTitle($this->pl->txt('config_header_notification'));
      $this->addItem($section);

      $subinput = new ilNumberInputGUI($this->pl->txt('config_' . xaliConfig::F_INTERVAL_REMINDER_EMAIL), xaliConfig::F_INTERVAL_REMINDER_EMAIL);
      $subinput->setInfo($this->pl->txt('config_' . xaliConfig::F_INTERVAL_REMINDER_EMAIL . '_info'));
      $this->addItem($subinput);

      $subinput = new ilNumberInputGUI($this->pl->txt('config_' . xaliConfig::F_SENDER_REMINDER_EMAIL), xaliConfig::F_SENDER_REMINDER_EMAIL);
      $subinput->setInfo($this->pl->txt('config_' . xaliConfig::F_SENDER_REMINDER_EMAIL . '_info'));
      $this->addItem($subinput);
      */

        // Buttons
        $this->addCommandButton(ilAttendanceListConfigGUI::CMD_UPDATE_CONFIG, $this->lng->txt('save'));

    }

    public function fillForm(): void
    {
        $this->setValuesByArray(array(
            xaliConfig::F_INTERVAL_REMINDER_EMAIL => xaliConfig::getConfig(xaliConfig::F_INTERVAL_REMINDER_EMAIL),
            xaliConfig::F_SENDER_REMINDER_EMAIL => xaliConfig::getConfig(xaliConfig::F_SENDER_REMINDER_EMAIL),
            xaliConfig::F_SHOW_NOT_RELEVANT => xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT),
            xaliConfig::F_SHOW_PRESENT_TOTAL => xaliConfig::getConfig(xaliConfig::F_SHOW_PRESENT_TOTAL)
        ));
    }

    public function saveObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        xaliConfig::set(xaliConfig::F_INTERVAL_REMINDER_EMAIL, $this->getInput(xaliConfig::F_INTERVAL_REMINDER_EMAIL));
        xaliConfig::set(xaliConfig::F_SENDER_REMINDER_EMAIL, $this->getInput(xaliConfig::F_SENDER_REMINDER_EMAIL));
        xaliConfig::set(xaliConfig::F_SHOW_NOT_RELEVANT, $this->getInput(xaliConfig::F_SHOW_NOT_RELEVANT));
        xaliConfig::set(xaliConfig::F_SHOW_PRESENT_TOTAL, $this->getInput(xaliConfig::F_SHOW_PRESENT_TOTAL));

        return true;
    }
}
