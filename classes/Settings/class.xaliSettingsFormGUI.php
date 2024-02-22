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

/**
 * Class xaliSettingsFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliSettingsFormGUI extends ilPropertyFormGUI
{
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = 'description';
    public const F_ONLINE = 'online';
    public const F_MINIMUM_ATTENDANCE = 'minimum_attendance';
    public const F_ACTIVATION = 'activation';
    public const F_ACTIVATION_FROM = 'activation_from';
    public const F_ACTIVATION_TO = 'activation_to';
    public const F_ACTIVATION_WEEKDAYS = 'activation_weekdays';
    public const F_CREATE_LISTS = 'create_lists';
    public const F_DELETE_LISTS = 'delete_lists';
    public const F_WEEKDAYS = 'weekdays';
    protected xaliSettingsGUI $parent_gui;
    protected ilCtrl $ctrl;
    protected ilAttendanceListPlugin $pl;
    protected ilLanguage $lng;
    protected ?xaliSetting $xaliSetting;
    protected ilObjAttendanceList $object;

    /**
     * @throws ilCtrlException
     */
    public function __construct(xaliSettingsGUI $parent_gui, ilObjAttendanceList|ilObject $object)
    {
        global $DIC;
        parent::__construct();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();

        $this->parent_gui = $parent_gui;
        $this->ctrl = $ilCtrl;
        $this->pl = ilAttendanceListPlugin::getInstance();
        $this->lng = $lng;
        $this->object = $object;
        $this->xaliSetting = xaliSetting::find($object->getId());
        $this->setFormAction($this->ctrl->getFormAction($parent_gui));
        $this->initForm();
    }


    public function initForm(): void
    {
        $input = new ilTextInputGUI($this->lng->txt(self::F_TITLE), self::F_TITLE);
        $input->setRequired(true);
        $input->setValue($this->object->getTitle());
        $this->addItem($input);

        $input = new ilTextInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $input->setValue($this->object->getDescription());
        $this->addItem($input);

        $input = new ilCheckboxInputGUI($this->lng->txt(self::F_ONLINE), self::F_ONLINE);
        $input->setChecked((bool) $this->xaliSetting->getIsOnline());
        $this->addItem($input);

        $input = new ilSelectInputGUI($this->pl->txt(self::F_MINIMUM_ATTENDANCE), self::F_MINIMUM_ATTENDANCE);
        $options = [];
        $options[xaliSetting::CALC_AUTO_MINIMUM_ATTENDANCE] = $this->pl->txt(self::F_MINIMUM_ATTENDANCE . '_auto');
        for ($i = 0; $i <= 100; $i++) {
            $options[$i] = $i . '%';
        }
        $input->setOptions($options);
        $input->setValue($this->xaliSetting->getMinimumAttendance());
        $input->setInfo($this->pl->txt(self::F_MINIMUM_ATTENDANCE . '_info'));
        $this->addItem($input);

        $input = new ilDateTimeInputGUI($this->pl->txt(self::F_ACTIVATION_FROM), self::F_ACTIVATION_FROM);
        $input->setRequired(true);
        $input->setValueByArray([self::F_ACTIVATION_FROM => $this->xaliSetting->getActivationFrom()]);
        $this->addItem($input);

        $input = new ilDateTimeInputGUI($this->pl->txt(self::F_ACTIVATION_TO), self::F_ACTIVATION_TO);
        $input->setRequired(true);
        $input->setValueByArray([self::F_ACTIVATION_TO => $this->xaliSetting->getActivationTo()]);
        $this->addItem($input);

        $input = new srWeekdayInputGUI($this->pl->txt(self::F_WEEKDAYS), self::F_WEEKDAYS);
        $input->setValue($this->xaliSetting->getActivationWeekdays());
        $this->addItem($input);

        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_CREATE_LISTS), self::F_CREATE_LISTS);
        $input->setInfo($this->pl->txt(self::F_CREATE_LISTS . '_info'));
        $this->addItem($input);

        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_DELETE_LISTS), self::F_DELETE_LISTS);
        $input->setInfo($this->pl->txt(self::F_DELETE_LISTS . '_info'));
        $this->addItem($input);

        $this->addCommandButton(xaliSettingsGUI::CMD_SAVE, $this->lng->txt('save'));
    }

    /**
     * @throws Exception
     */
    public function saveSettings(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->object->setTitle($this->getInput(self::F_TITLE));
        $this->object->setDescription($this->getInput(self::F_DESCRIPTION));
        $this->object->update();

        $this->xaliSetting->setIsOnline((int) $this->getInput(self::F_ONLINE));
        $this->xaliSetting->setMinimumAttendance((int) $this->getInput(self::F_MINIMUM_ATTENDANCE));
        $this->xaliSetting->setActivation((int) $this->getInput(self::F_ACTIVATION));

        $activation_from = $this->getInput(self::F_ACTIVATION_FROM);
        $this->xaliSetting->setActivationFrom($activation_from);

        $activation_to = $this->getInput(self::F_ACTIVATION_TO);
        $this->xaliSetting->setActivationTo($activation_to);

        $weekdays = (array) $this->getInput(self::F_WEEKDAYS);

        $this->xaliSetting->setActivationWeekdays($weekdays === [""] ? [] : $weekdays);

        $this->xaliSetting->update();

        if ($this->getInput(self::F_CREATE_LISTS) || $this->getInput(self::F_DELETE_LISTS)) {
            $this->xaliSetting->createOrDeleteEmptyLists((bool) $this->getInput(self::F_CREATE_LISTS), (bool) $this->getInput(self::F_DELETE_LISTS));
        }

        return true;
    }
}
