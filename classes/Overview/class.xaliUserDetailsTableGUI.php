<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xaliUserDetailsTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliUserDetailsTableGUI extends ilTable2GUI {
	protected ilObjUser $user;
	protected string $obj_id;
	protected ilCtrl $ctrl;
	protected ilAttendanceListPlugin $pl;
	protected ilLanguage $lng;
	protected ?object $parent_obj;

	public function __construct(xaliOverviewGUI $a_parent_obj, string $user_id, string $obj_id) {
		global $DIC;
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		$tpl = $DIC->ui()->mainTemplate();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->pl = ilAttendanceListPlugin::getInstance();
		$this->user = new ilObjUser($user_id);
		$this->obj_id = $obj_id;
		$this->parent_cmd = 'editUser';

		$this->setPrefix('xali_usr_detail');
		$this->setId($user_id);

		parent::__construct($a_parent_obj, xaliOverviewGUI::CMD_EDIT_USER);

		$this->setTitle($this->user->getFirstname() . ' ' . $this->user->getLastname());

		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

		$this->setEnableNumInfo(false);
		$this->setRowTemplate('tpl.user_details_row.html', $this->pl->getDirectory());
		$this->ctrl->setParameter($this->parent_obj, 'user_id', $this->user->getId());
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'editUser'));
		$this->setLimit(0);
		$this->resetOffset();
		$this->initColumns();

		$this->initCommands();

		$this->parseData();

		$async_links = array();
		$this->ctrl->setParameter($this->parent_obj, 'user_id', $this->user->getId());
		foreach ($this->getData() as $data_set) {
			$this->ctrl->setParameter($this->parent_obj, 'checklist_id',$data_set['id']);
			$async_links[] = [ "save_status" => $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_SAVE_ENTRY, "", true), "save_absence_reason" => $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_SAVE_ABSENCE_REASON, "", true)];
		}
		$tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/srAttendanceList.js');
		$tpl->addOnLoadCode('srAttendanceList.initUserDetails(' . json_encode($async_links).');');
	}

	protected function initCommands(): void
    {
		$this->addCommandButton(xaliOverviewGUI::CMD_SAVE_USER, $this->pl->txt('save_all'));
		$this->addCommandButton(xaliOverviewGUI::CMD_SHOW_USERS, $this->lng->txt('cancel'));
	}

	protected function initColumns(): void
    {
		$this->addColumn($this->pl->txt('table_column_date'), "", "200px");
		$this->addColumn($this->pl->txt('table_column_tutor'), "","200px");
		$this->addColumn($this->pl->txt('table_column_status'), "", "550px");
		$this->addColumn($this->pl->txt('table_column_absence_reason'), "", "300px");
	}

	protected function parseData(): void
    {
		$data = array();
		/** @var xaliChecklist $checklist */
		foreach (xaliChecklist::where(array(
			'obj_id' => $this->obj_id,
			'checklist_date' => date('Y-m-d')
		), array(
			'obj_id' => '=',
			'checklist_date' => '<='
		))->orderBy('checklist_date')->get() as $checklist) {
			$checklist_data = array();
			$checklist_data["id"] = $checklist->getId();
			$checklist_data["date"] = $checklist->getChecklistDate();
			$checklist_data["tutor"] = $checklist->getLastEditedBy(true);

			$checklist_entry = $checklist->getEntryOfUser($this->user->getId());
			$checklist_data['entry_id'] = $checklist_entry->getId();
			if ($status = $checklist_entry->getStatus()) {
                if (!xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT) ? intval($status) !== xaliChecklistEntry::STATUS_NOT_RELEVANT : true) {
                    $checklist_data["checked_$status"] = 'checked';
                    $checklist_data["link_save_hidden"] = 'hidden';
                }
			} else {
				$checklist_data["checked_" . xaliChecklistEntry::STATUS_PRESENT] = 'checked';
				$checklist_data["warning"] = $this->pl->txt('warning_not_filled_out');
			}

            $checklist_data["link_save_absence_reason_hidden"] = 'hidden';
			if (!xaliAbsenceStatement::find($checklist_entry->getId())) {
                $checklist_data["warning_absence_reason"] = $this->pl->txt('warning_absence_reason_not_filled_out');
                if (intval($status) === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
                    $checklist_data["link_save_absence_reason_hidden"] = '';
                }
            }

			$data[] = $checklist_data;
		}
		ksort($data);
		$this->setData($data);
	}

	protected function fillRow(array $a_set): void
    {
		parent::fillRow($a_set);

		$this->ctrl->setParameter($this->parent_obj, 'checklist_id', $a_set['id']);
		$this->tpl->setVariable('VAL_EDIT_LINK', $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_EDIT_LIST));
		$this->tpl->setVariable('VAL_SAVE_LINK', $this->ctrl->getLinkTarget($this->parent_obj, xaliOverviewGUI::CMD_SAVE_ENTRY, "", true));
		$this->tpl->setVariable('VAL_SAVE', $this->pl->txt('save_entry'));
		$this->tpl->setVariable('VAL_SAVING', $this->pl->txt('saving_entry'));

		$this->ctrl->clearParametersByClass(xaliAbsenceStatementGUI::class);
		$this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class,'back_cmd', xaliOverviewGUI::CMD_EDIT_USER);
		if ($a_set['entry_id']) {
			$this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class,'entry_id', $a_set['entry_id']);
		} else {
			$this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class,'checklist_id', $a_set['id']);
			$this->ctrl->setParameterByClass(xaliAbsenceStatementGUI::class,'user_id', $this->user->getId());
		}
		$link_to_absence_form = $this->ctrl->getLinkTargetByClass(xaliAbsenceStatementGUI::class, xaliAbsenceStatementGUI::CMD_STANDARD);
		$this->tpl->setVariable('LINK_ABSENCE_REASON', $link_to_absence_form);
        /** @var xaliAbsenceStatement $stm */
        $stm = xaliAbsenceStatement::findOrGetInstance($a_set['entry_id']);
		$reason = $stm->getReason();
		$this->tpl->setVariable('VAL_ABSENCE_REASON', $reason ? $reason : $this->pl->txt('no_absence_reason'));

		if (key_exists('checked_' . xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED, $a_set) && !$a_set['checked_' . xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED]) {
			$this->tpl->setVariable('VAL_LINK_ABSENCE_HIDDEN', 'hidden');
		}

        if (xaliAbsenceReason::where("has_comment=true OR has_upload=true")->count() === 0) {
            $this->tpl->setVariable('VAL_LINK_ABSENCE_HIDDEN', 'hidden');
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
            if (!$a_set['checked_' . xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED]) {
                $this->tpl->setVariable('VAL_ABSENCE_REASON_HIDDEN', 'hidden');
            }
            $this->tpl->setVariable('VAL_ABSENCE_REASON_ID', $a_set["entry_id"]);
            if ($a_set['link_save_absence_reason_hidden']) {
                $this->tpl->setVariable('VAL_ABSENCE_LINK_SAVE_ABSENCE_REASON_HIDDEN', $a_set['link_save_absence_reason_hidden']);
            }
            if ($a_set['warning_absence_reason']) {
                $this->tpl->setVariable('VAL_ABSENCE_WARNING_ABSENCE_REASON', $a_set['warning_absence_reason']);
            }
        }

		//		$this->tpl->setVariable('VAL_LINK_ABSENCE', )

		//		foreach (array('unexcused', 'excused', 'present') as $label) {
		foreach (array('unexcused', 'present') as $label) {
			$this->tpl->setVariable('LABEL_'.strtoupper($label), $this->pl->txt('label_'.$label));
		}
        if (xaliConfig::getConfig(xaliConfig::F_SHOW_NOT_RELEVANT)) {
            $this->tpl->setVariable('LABEL_NOT_RELEVANT', $this->pl->txt('label_not_relevant'));
        }
	}

	public function fillRowCSV($a_csv, array $a_set): void
    {
		unset($a_set['id']);
		unset($a_set['link_save_hidden']);
		foreach ($a_set as $key => $value)
		{
			if ($value == 'checked') {
				if (isset($a_set['warning'])) {
					continue;
				}
				$status_id = substr($key, -1);
				$value = $this->pl->txt('status_' . $status_id);
			} elseif(is_array($value)) {
				$value = implode(', ', $value);
			}
			$a_csv->addColumn(strip_tags($value));
		}
		$a_csv->addRow();
	}


	protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
		unset($a_set['id']);
		unset($a_set['link_save_hidden']);
		$col = 0;
		foreach ($a_set as $key => $value)
		{
			if ($value == 'checked') {
				if (isset($a_set['warning'])) {
					continue;
				}
				$status_id = substr($key, -1);
				$value = $this->pl->txt('status_' . $status_id);
			}
			if(is_array($value))
			{
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

	public function exportData(int $format, $send = false): void
    {
		if($this->dataExists())
		{
			// #9640: sort
			if (!$this->getExternalSorting() && $this->enabled["sort"])
			{
				$this->determineOffsetAndOrder(true);

				$this->row_data = ilUtil::sortArray($this->row_data, $this->getOrderField(),
					$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
			}

			$filename = 'Anwesenheitsliste_' . $this->user->getFirstname() . '_' . $this->user->getLastname();

			switch($format)
			{
				case self::EXPORT_EXCEL:
				    $excel = new ilExcel();
				    $excel->addSheet($filename);
                    $row = 0;

                    ob_start();
                    $this->fillMetaExcel($excel, $row); // row must be increment in fillMetaExcel()! (optional method)

                    // #14813
                    $pre = $row;
                    $this->fillHeaderExcel($excel, $row); // row should NOT be incremented in fillHeaderExcel()! (required method)
                    if($pre == $row)
                    {
                        $row++;
                    }

                    foreach($this->row_data as $set)
                    {
                        $this->fillRowExcel($excel, $row, $set);
                        $row++; // #14760
                    }
                    ob_end_clean();

                    $excel->sendToClient($filename);
                    break;

                case self::EXPORT_CSV:
					$csv = new ilCSVWriter();
					$csv->setSeparator(";");

					ob_start();
					$this->fillMetaCSV($csv);
					$this->fillHeaderCSV($csv);
					foreach($this->row_data as $set)
					{
						$this->fillRowCSV($csv, $set);
					}
					ob_end_clean();

					if($send)
					{
						$filename .= ".csv";
						header("Content-type: text/comma-separated-values");
						header("Content-Disposition: attachment; filename=\"".$filename."\"");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
						header("Pragma: public");
						echo $csv->getCSVString();

					}
					else
					{
						file_put_contents($filename, $csv->getCSVString());
					}
					break;
			}

			if($send)
			{
				exit();
			}
		}
	}
}