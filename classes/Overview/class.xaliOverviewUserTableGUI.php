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
 * Class xaliOverviewUserTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliOverviewUserTableGUI extends ilTable2GUI
{
    protected ilAttendanceListPlugin $pl;
    protected array $users;
    protected string|int $obj_id;
    protected ActiveRecord|null|xaliSetting $settings;
    protected bool $has_passed_students = false;

    public function __construct(xaliOverviewGUI $a_parent_obj, array $users, int $obj_id)
    {
        global $DIC;
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->pl = ilAttendanceListPlugin::getInstance();
        $this->users = $users;
        $this->obj_id = $obj_id;
        $this->settings = xaliSetting::find($obj_id);

        parent::__construct($a_parent_obj);
        $this->setRowTemplate('tpl.user_overview_row.html', $this->pl->getDirectory());
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

        $this->setLimit(0);
        $this->initColumns();
        $this->initFilter();
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, xaliOverviewGUI::CMD_APPLY_FILTER_USERS));

        $this->setDefaultOrderField('name');

        $this->setFilterCommand(xaliOverviewGUI::CMD_APPLY_FILTER_USERS);
        $this->setResetCommand(xaliOverviewGUI::CMD_RESET_FILTER_USERS);

        if (empty($_GET['_xpt']) || in_array($_GET['_xpt'], array_keys($this->export_formats))) {
            $this->parseData();
        }
    }

    public function parseData(): void
    {
        $data = array();
        foreach ($this->users as $usr_id) {
            $user = new ilObjUser($usr_id);
            $user_data = array();
            if ($this->filter['login'] != '' && $this->filter['login'] != $user->getLogin()) {
                continue;
            }
            $user_data["name"] = $user->getFullname();
            $user_data["login"] = $user->getLogin();
            $user_data["id"] = $user->getId();

            /** @var xaliUserStatus $xaliUserStatus */
            $xaliUserStatus = xaliUserStatus::getInstance($user->getId(), $this->obj_id);

            $user_data["present"] = $xaliUserStatus->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT);
            //			$user_data["excused"] = $xaliUserStatus->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_EXCUSED);
            $user_data["unexcused"] = $xaliUserStatus->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED);
            if (xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT)) {
                $user_data["not_relevant"] = $xaliUserStatus->getAttendanceStatuses(xaliChecklistEntry::STATUS_NOT_RELEVANT);
            }
            if (xaliConfig::getConfig(xaliConfig::F_SHOW_PRESENT_TOTAL)) {
                $user_data["present_total"] = $xaliUserStatus->getPresentTotalString();
            }

            $user_data['reached_percentage'] = $xaliUserStatus->getReachedPercentage();

            $user_data['no_status'] = $xaliUserStatus->getUnedited();
            $user_data['minimum_attendance'] = $xaliUserStatus->calcMinimumAttendance();
            $user_data['percentage'] = $user_data['reached_percentage'] . '% / ' . $user_data['minimum_attendance'] . '%';
            $data[] = $user_data;
        }
        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        parent::fillRow($a_set);
        $color = ($a_set['reached_percentage'] < $a_set['minimum_attendance']) ? 'red' : 'green';
        if ($color == 'green') {
            $this->has_passed_students = true;
        }
        $this->ctrl->setParameter($this->parent_obj, 'user_id', $a_set['id']);
        $this->tpl->setVariable('VAL_EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_EDIT_USER));
        $this->tpl->setVariable('COLOR', $color);
    }

    protected function getChecklistIds(): array
    {
        $ids = array();
        foreach (xaliChecklist::where(array('obj_id' => $this->obj_id))->get() as $checklist) {
            $ids[] = $checklist->getId();
        }

        return $ids;
    }

    public function initFilter(): void
    {
        $user_filter = new ilTextInputGUI($this->lng->txt('login'), 'name');
        $this->ctrl->saveParameterByClass(ilAttendanceListPlugin::class, 'ref_id', $_GET['ref_id']);
        $user_filter->setDataSource($this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_ADD_USER_AUTO_COMPLETE, "", true));
        $this->addFilterItem($user_filter);
        $user_filter->readFromSession();
        $this->filter['login'] = $user_filter->getValue();
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->pl->txt('table_column_name'), 'name');
        $this->addColumn($this->pl->txt('table_column_login'), 'login');
        $this->addColumn($this->pl->txt('table_column_present'), 'present');
        //		$this->addColumn($this->pl->txt('table_column_excused'), 'excused');
        $this->addColumn($this->pl->txt('table_column_unexcused'), 'unexcused');
        if (xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT)) {
            $this->addColumn($this->pl->txt('table_column_not_relevant'), 'not_relevant');
        }
        $this->addColumn($this->pl->txt('table_column_no_status'), 'no_status');
        $this->addColumn($this->pl->txt('table_column_percentage'), 'reached_percentage');
        if (xaliConfig::getConfig(xaliConfig::F_SHOW_PRESENT_TOTAL)) {
            $this->addColumn($this->pl->txt('table_column_present_total'), 'present_total');
        }
    }

    public function numericOrdering($a_field): bool
    {
        switch ($a_field) {
            case 'present':
            case 'excused':
            case 'unexcused':
            case 'not_relevant':
            case 'no_status':
            case 'reached_percentage':
                return true;
            default:
                return false;
        }
    }

    protected function fillRowCSV($a_csv, array $a_set): void
    {
        unset($a_set['id']);
        unset($a_set['reached_percentage']);
        parent::fillRowCSV($a_csv, $a_set);
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        unset($a_set['id']);
        unset($a_set['reached_percentage']);
        parent::fillRowExcel($a_excel, $a_row, $a_set);
    }

    public function hasPassedStudents(): bool
    {
        return $this->has_passed_students;
    }
}
