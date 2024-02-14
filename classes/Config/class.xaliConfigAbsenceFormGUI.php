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
class xaliConfigAbsenceFormGUI extends ilPropertyFormGUI
{
    protected ilAttendanceListPlugin $pl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected xaliAbsenceReason $absence_reason;
    private ilAttendanceListConfigGUI $parent_gui;

    public function __construct($parent_gui, xaliAbsenceReason $absence_reason)
    {
        parent::__construct();

        global $DIC;
        $this->parent_gui = $parent_gui;

        $this->pl = ilAttendanceListPlugin::getInstance();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->absence_reason = $absence_reason;
        $this->http = $DIC->http();

        if ($ar_id = $this->absence_reason->getId()) {
            $this->ctrl->setParameter($this->parent_gui, 'ar_id', $ar_id);
        }
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->initForm();
    }

    protected function initForm(): void
    {
        $input = new ilTextInputGUI($this->pl->txt('config_form_' . xaliAbsenceReason::F_ABSENCE_REASONS_TITLE), xaliAbsenceReason::F_ABSENCE_REASONS_TITLE);
        $input->setRequired(true);
        $this->addItem($input);

        $input = new ilTextInputGUI($this->pl->txt('config_form_' . xaliAbsenceReason::F_ABSENCE_REASONS_INFO), xaliAbsenceReason::F_ABSENCE_REASONS_INFO);
        $this->addItem($input);

        $input = new ilCheckboxInputGUI($this->pl->txt('config_form_' . xaliAbsenceReason::F_ABSENCE_REASONS_HAS_COMMENT), xaliAbsenceReason::F_ABSENCE_REASONS_HAS_COMMENT);
        $this->addItem($input);

        $subinput = new ilCheckboxInputGUI($this->pl->txt('config_form_' . xaliAbsenceReason::F_ABSENCE_REASONS_COMMENT_REQ), xaliAbsenceReason::F_ABSENCE_REASONS_COMMENT_REQ);
        $input->addSubItem($subinput);

        $input = new ilCheckboxInputGUI($this->pl->txt('config_form_' . xaliAbsenceReason::F_ABSENCE_REASONS_HAS_UPLOAD), xaliAbsenceReason::F_ABSENCE_REASONS_HAS_UPLOAD);
        $this->addItem($input);

        $subinput = new ilCheckboxInputGUI($this->pl->txt('config_form_' . xaliAbsenceReason::F_ABSENCE_REASONS_UPLOAD_REQ), xaliAbsenceReason::F_ABSENCE_REASONS_UPLOAD_REQ);
        $input->addSubItem($subinput);

        // Buttons
        $cmd = $this->absence_reason->getId() ? ilAttendanceListConfigGUI::CMD_UPDATE_REASON : ilAttendanceListConfigGUI::CMD_CREATE_REASON;

        $this->addCommandButton($cmd, $this->lng->txt('save'));
        $this->addCommandButton(ilAttendanceListConfigGUI::CMD_SHOW_REASONS, $this->lng->txt('cancel'));
    }

    public function fillForm(): void
    {
        $values = array(
            xaliAbsenceReason::F_ABSENCE_REASONS_TITLE => $this->absence_reason->getTitle(),
            xaliAbsenceReason::F_ABSENCE_REASONS_INFO => $this->absence_reason->getInfo(),
            xaliAbsenceReason::F_ABSENCE_REASONS_HAS_COMMENT => $this->absence_reason->hasComment(),
            xaliAbsenceReason::F_ABSENCE_REASONS_COMMENT_REQ => $this->absence_reason->getCommentReq(),
            xaliAbsenceReason::F_ABSENCE_REASONS_HAS_UPLOAD => $this->absence_reason->hasUpload(),
            xaliAbsenceReason::F_ABSENCE_REASONS_UPLOAD_REQ => $this->absence_reason->getUploadReq()
        );
        $this->setValuesByArray($values);
    }

    public function saveObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }
        $this->absence_reason->setTitle($this->getInput(xaliAbsenceReason::F_ABSENCE_REASONS_TITLE));
        $this->absence_reason->setInfo($this->getInput(xaliAbsenceReason::F_ABSENCE_REASONS_INFO));
        $this->absence_reason->setHasComment($this->getInput(xaliAbsenceReason::F_ABSENCE_REASONS_HAS_COMMENT));
        $this->absence_reason->setCommentReq($this->getInput(xaliAbsenceReason::F_ABSENCE_REASONS_COMMENT_REQ));
        $this->absence_reason->setHasUpload($this->getInput(xaliAbsenceReason::F_ABSENCE_REASONS_HAS_UPLOAD));
        $this->absence_reason->setUploadReq($this->getInput(xaliAbsenceReason::F_ABSENCE_REASONS_UPLOAD_REQ));

        $this->absence_reason->store();

        return true;
    }
}
