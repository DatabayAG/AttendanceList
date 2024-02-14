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
 * Class xaliChecklistTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliChecklistTableGUI extends ilTable2GUI
{
    protected ilAttendanceListPlugin $pl;
    protected xaliChecklist $checklist;
    protected array $users;
    protected bool $is_new;
    protected ilCtrl $ctrl;

    /**
     * xaliChecklistTableGUI constructor.
     *
     * @throws ilCtrlException
     */
    public function __construct(xaliChecklistGUI|xaliOverviewGUI $a_parent_obj, xaliChecklist $checklist, array $users)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->pl = ilAttendanceListPlugin::getInstance();
        $this->checklist = $checklist;
        $this->users = $users;
        $this->is_new = ($checklist->getId() == 0);

        parent::__construct($a_parent_obj);

        if (!$this->is_new) {
            $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
        }

        $this->setEnableNumInfo(false);
        $this->setRowTemplate('tpl.checklist_row.html', $this->pl->getDirectory());
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setLimit(0);
        $this->resetOffset();
        $this->initColumns();

        $this->initCommands();

        $this->parseData();
    }

    protected function initCommands(): void
    {
        $this->addCommandButton('saveList', $this->lng->txt('save'));
        if ($this->parent_obj instanceof xaliOverviewGUI) {
            $this->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->pl->txt('table_column_name'));
        $this->addColumn($this->pl->txt('table_column_login'));
        $this->addColumn($this->pl->txt('table_column_status'));
        $this->addColumn($this->pl->txt('table_column_absence_reason'));
    }

    protected function parseData(): void
    {
        $data = array();
        foreach ($this->users as $usr_id) {
            $user = new ilObjUser($usr_id);
            $user_data = array();
            $user_data["name"] = $user->getFullname();
            $user_data["login"] = $user->getLogin();
            $user_data["id"] = $user->getId();

            $checklist_entry = $this->checklist->getEntryOfUser($user->getId());
            $user_data['entry_id'] = $checklist_entry->getId();
            if ($status = $checklist_entry->getStatus()) {
                if (!xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT) ? intval($status) !== xaliChecklistEntry::STATUS_NOT_RELEVANT : true) {
                    $user_data["checked_$status"] = 'checked';
                }
            } else {
                $user_data["checked_" . xaliChecklistEntry::STATUS_PRESENT] = 'checked';
                $user_data["warning"] = $this->pl->txt('warning_not_filled_out');
            }

            $data[$user->getFullname() . $user->getId()] = $user_data;
        }
        ksort($data);
        $this->setData($data);
    }

    protected function fillRow($a_set): void
    {
        parent::fillRow($a_set);

        if (ilObjAttendanceListAccess::hasWriteAccess()) {
            $this->tpl->setCurrentBlock('name_with_link');
            $this->ctrl->setParameterByClass(xaliOverviewGUI::class, 'user_id', $a_set['id']);
            $this->tpl->setVariable('VAL_EDIT_LINK', $this->ctrl->getLinkTargetByClass(xaliOverviewGUI::class, xaliOverviewGUI::CMD_EDIT_USER));
        } else {
            $this->tpl->setCurrentBlock('name_without_link');
        }
        $this->tpl->setVariable('VAL_NAME', $a_set['name']);
        $this->tpl->parseCurrentBlock();

        /** @var xaliAbsenceStatement $stm */
        $stm = xaliAbsenceStatement::findOrGetInstance($a_set['entry_id']);

        if (key_exists('checked_' . xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED, $a_set) && $a_set['checked_' . xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED] === 'checked') {
            if (ilObjAttendanceListAccess::hasWriteAccess()) {
                if (xaliAbsenceReason::where("has_comment=true OR has_upload=true")->count() === 0) {
                    $this->tpl->setCurrentBlock('absence_reason_select');
                    $absence_options = [];
                    $absence_options[] = '<option value="">' . htmlspecialchars($this->pl->txt('no_absence_reason')) . '</option>';
                    /** @var xaliAbsenceReason $xaliReason */
                    foreach (xaliAbsenceReason::get() as $xaliReason) {
                        $absence_options[] = '<option value="' . htmlspecialchars($xaliReason->getId()) . '"' . (intval($xaliReason->getId()) === intval($stm->getReasonId()) ? ' selected' : '')
                            . '>'
                            . htmlspecialchars($xaliReason->getTitle()) . '</option>';
                    }
                    $this->tpl->setVariable('ABSENCE_REASON_OPTIONS', implode("", $absence_options));
                    $this->tpl->setVariable('VAL_ABSENCE_REASON_ID', $a_set["entry_id"]);
                } else {
                    $this->tpl->setCurrentBlock('absence_with_link');
                    $this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class, 'back_cmd', xaliOverviewGUI::CMD_EDIT_LIST);
                    if ($a_set['entry_id']) {
                        $this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class, 'entry_id', $a_set['entry_id']);
                    } else {
                        $this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class, 'checklist_id', $a_set['checklist_id']);
                        $this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class, 'user_id', $a_set['id']);
                    }
                    $link_to_absence_form = $this->ctrl->getLinkTargetByClass(xaliAbsenceStatementGUI::class, xaliAbsenceStatementGUI::CMD_STANDARD);
                    $this->tpl->setVariable('VAL_ABSENCE_LINK', $link_to_absence_form);
                }
            } else {
                $this->tpl->setCurrentBlock('absence_without_link');
            }

            if (!isset($absence_options)) {
                $reason = $stm->getReason();
                $this->tpl->setVariable('VAL_ABSENCE_REASON', $reason ? $reason : $this->pl->txt('no_absence_reason'));
            }
            $this->tpl->parseCurrentBlock();
        }

        //		foreach (array('unexcused', 'excused', 'present') as $label) {
        foreach (array('unexcused', 'present') as $label) {
            $this->tpl->setVariable('LABEL_' . strtoupper($label), $this->pl->txt('label_' . $label));
        }
        if (xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT)) {
            $this->tpl->setVariable('LABEL_NOT_RELEVANT', $this->pl->txt('label_not_relevant'));
        }
    }

    public function fillRowCSV($a_csv, array $a_set): void
    {
        unset($a_set['id']);
        foreach ($a_set as $key => $value) {
            if ($value === 'checked') {
                $status_id = substr($key, -1);
                $value = $this->pl->txt('status_' . $status_id);
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $a_csv->addColumn(strip_tags($value));
        }
        $a_csv->addRow();
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        unset($a_set['id']);
        $col = 0;
        foreach ($a_set as $key => $value) {
            if ($value === 'checked') {
                $status_id = substr($key, -1);
                $value = $this->pl->txt('status_' . $status_id);
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if (method_exists($a_excel, 'write')) {
                $a_excel->write($a_row, $col, strip_tags($value));
            } else {
                $a_excel->setCell($a_row, $col, strip_tags($value));
            }

            $col++;
        }
    }
}
