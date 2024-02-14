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
 * Class xaliOverviewListTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliOverviewListTableGUI extends ilTable2GUI
{
    protected ilAttendanceListPlugin $pl;
    protected int $obj_id;
    protected ilCtrl $ctrl;

    /**
     * xaliOverviewListTableGUI constructor.
     *
     * @throws ilException
     */
    public function __construct(xaliOverviewGUI $a_parent_obj, int $obj_id)
    {
        global $DIC;
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->pl = ilAttendanceListPlugin::getInstance();
        $this->obj_id = $obj_id;
        $this->setId('xali_lists_overview_' . $obj_id);

        parent::__construct($a_parent_obj, xaliOverviewGUI::CMD_LISTS);
        $this->setRowTemplate('tpl.list_overview_row.html', $this->pl->getDirectory());
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

        $this->initColumns();
        $this->initFilter();

        $this->setShowRowsSelector(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));

        $this->setFilterCommand(xaliOverviewGUI::CMD_APPLY_FILTER_LISTS);
        $this->setResetCommand(xaliOverviewGUI::CMD_RESET_FILTER_LISTS);

        $this->addMultiCommand(xaliOverviewGUI::CMD_CONFIRM_DELETE_LISTS, $this->lng->txt('delete'));

        $this->setDefaultOrderField('sort_date');

        $this->parseData();
    }

    public function parseData(): void
    {
        $data = array();
        foreach (xaliChecklist::where(array('obj_id' => $this->obj_id))->get() as $checklist) {
            /** @var $checklist xaliChecklist */
            $dataset = array();
            $dataset['id'] = $checklist->getId();
            $dataset['sort_date'] = $checklist->getChecklistDate(false);
            $dataset['date'] = $checklist->getChecklistDate();
            $dataset['date'] .= $checklist->isComplete() ? '' : ' (' . $this->pl->txt('incomplete') . ')';

            if ($date_filter = $this->filter['date']) {
                /** @var ilDateTime $from */
                if (isset($date_filter['from']) && $date_filter['from']->get(IL_CAL_DATE, 'Y-m-d') > $dataset['sort_date']) {
                    continue;
                }
                if (isset($date_filter['to']) && $date_filter['to']->get(IL_CAL_DATE, 'Y-m-d') < $dataset['sort_date']) {
                    continue;
                }
            }

            $dataset['tutor'] = $checklist->getLastEditedBy(true);

            $present = $checklist->getStatusCount(xaliChecklistEntry::STATUS_PRESENT);
            //			$excused = $checklist->getStatusCount(xaliChecklistEntry::STATUS_ABSENT_EXCUSED);
            $unexcused = $checklist->getStatusCount(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED);
            $not_relevant = $checklist->getStatusCount(xaliChecklistEntry::STATUS_NOT_RELEVANT);
            //			$total = $present + $excused + $unexcused + $not_relevant;
            $total = $present + $unexcused + $not_relevant;

            $dataset['present'] = $total ? $present . ' (' . round($present / $total * 100) . '%)' : '-';
            //			$dataset['excused'] = $total ? $excused . ' (' . round($excused / $total * 100) . '%)' : '-';
            $dataset['unexcused'] = $total ? $unexcused . ' (' . round($unexcused / $total * 100) . '%)' : '-';
            if (xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT)) {
                $dataset['not_relevant'] = $total ? $not_relevant . ' (' . round($not_relevant / $total * 100) . '%)' : '-';
            }
            $data[] = $dataset;
        }
        $this->setData($data);
    }

    /**
     * @throws ilCtrlException
     * @throws JsonException
     */
    public function fillRow($a_set): void
    {
        parent::fillRow($a_set);
        $this->ctrl->setParameter($this->parent_obj, 'checklist_id', $a_set['id']);
        $this->tpl->setVariable('VAL_EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'editList'));
        $this->tpl->setVariable('ACTIONS', $this->buildAction());
    }

    /**
     * @throws ilCtrlException
     * @throws JsonException
     */
    public function buildAction(): string
    {
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($this->lng->txt('actions'));
        $actions->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_EDIT_LIST));
        $actions->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_CONFIRM_DELETE_LISTS));
        return $actions->getHTML();
    }

    /**
     * @throws Exception
     */
    public function initFilter(): void
    {
        $date_filter = $this->addFilterItemByMetaType('date', self::FILTER_DATE_RANGE);
        $date_filter->readFromSession();
        $this->filter['date'] = $date_filter->getDate();
    }

    protected function initColumns(): void
    {
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->pl->txt('table_column_date'), 'sort_date');
        $this->addColumn($this->pl->txt('table_column_tutor'), 'tutor');
        $this->addColumn($this->pl->txt('table_column_present'));
        //		$this->addColumn($this->pl->txt('table_column_excused'));
        $this->addColumn($this->pl->txt('table_column_unexcused'));
        if (xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT)) {
            $this->addColumn($this->pl->txt('table_column_not_relevant'));
        }
        $this->addColumn("", "", '30px', true);
    }

    protected function fillRowCSV($a_csv, array $a_set): void
    {
        unset($a_set['id']);
        unset($a_set['sort_date']);
        parent::fillRowCSV($a_csv, $a_set);
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        unset($a_set['id']);
        unset($a_set['sort_date']);
        parent::fillRowExcel($a_excel, $a_row, $a_set);
    }

}
