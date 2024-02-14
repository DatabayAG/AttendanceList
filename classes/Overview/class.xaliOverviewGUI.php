<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use JetBrains\PhpStorm\NoReturn;

/**
 * Class xaliOverviewGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliOverviewGUI extends xaliGUI {

	public const CMD_STANDARD = 'initUserOverview';
	public const CMD_SHOW_USERS = 'showUsers';
	public const CMD_LISTS = 'showListsOverview';
	public const CMD_EDIT_LIST = 'editList';
	public const CMD_EDIT_USER = 'editUser';
	public const CMD_CONFIRM_DELETE_LISTS = 'confirmDeleteLists';
	public const CMD_ADD_LIST = 'addList';
	public const CMD_CREATE_LIST = 'createList';
	public const CMD_APPLY_FILTER_USERS = 'applyFilterUsers';
	public const CMD_RESET_FILTER_USERS = 'resetFilterUsers';
	public const CMD_APPLY_FILTER_LISTS = 'applyFilterLists';
	public const CMD_RESET_FILTER_LISTS = 'resetFilterLists';
	public const CMD_SAVE_ENTRY = 'saveEntry';
	public const CMD_SAVE_USER = 'saveUser';
    const CMD_ADD_USER_AUTO_COMPLETE = 'addUserAutoComplete';
	public const CMD_SAVE_ABSENCE_REASON = 'saveAbsenceReason';

	public const SUBTAB_USERS = 'subtab_users';
	public const SUBTAB_LISTS = 'subtab_lists';


	public function initUserOverview(): void
    {
		$this->setSubtabs(self::SUBTAB_USERS);
		$users = $this->parent_gui->getMembers();
		$xaliOverviewUserTableGUI = new xaliOverviewUserTableGUI($this, $users, $this->parent_gui->getObject()->getId());
		$this->tpl->setContent($xaliOverviewUserTableGUI->getHTML());
	}

	public function showUsers(): void
    {
		$this->setSubtabs(self::SUBTAB_USERS);
		$users = $this->parent_gui->getMembers();
		$xaliOverviewUserTableGUI = new xaliOverviewUserTableGUI($this, $users, $this->parent_gui->getObject()->getId());
		$xaliOverviewUserTableGUI->parseData();
		$this->tpl->setContent($xaliOverviewUserTableGUI->getHTML());
	}

	public function showListsOverview(): void
    {
		$this->setSubtabs(self::SUBTAB_LISTS);
		$add_button = ilLinkButton::getInstance();
		$add_button->setPrimary(true);
		$add_button->setCaption($this->pl->txt('button_add_list'), false);
		$add_button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_LIST));
		$this->toolbar->addButtonInstance($add_button);
		$xaliOverviewListTableGUI = new xaliOverviewListTableGUI($this, $this->parent_gui->getObject()->getId());
		$this->tpl->setContent($xaliOverviewListTableGUI->getHTML());
	}

	public function applyFilterUsers(): void
    {
		$users = $this->parent_gui->getMembers();
		$xaliOverviewUserTableGUI = new xaliOverviewUserTableGUI($this, $users, $this->parent_gui->getObject()->getId());
		$xaliOverviewUserTableGUI->writeFilterToSession();
		$xaliOverviewUserTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_SHOW_USERS);
	}

	public function resetFilterUsers(): void
    {
		$users = $this->parent_gui->getMembers();
		$xaliOverviewUserTableGUI = new xaliOverviewUserTableGUI($this, $users,$this->parent_gui->getObject()->getId());
		$xaliOverviewUserTableGUI->resetFilter();
		$xaliOverviewUserTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	public function applyFilterLists(): void
    {
		$xaliOverviewUserTableGUI = new xaliOverviewListTableGUI($this, $this->parent_gui->getObject()->getId());
		$xaliOverviewUserTableGUI->writeFilterToSession();
		$xaliOverviewUserTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_LISTS);
	}

	public function resetFilterLists(): void
    {
		$xaliOverviewUserTableGUI = new xaliOverviewListTableGUI($this, $this->parent_gui->getObject()->getId());
		$xaliOverviewUserTableGUI->resetFilter();
		$xaliOverviewUserTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_LISTS);
	}

	public function setSubtabs($active): void
    {
		$this->tabs->addSubTab(self::SUBTAB_USERS, $this->pl->txt(self::SUBTAB_USERS), $this->ctrl->getLinkTarget($this, self::CMD_STANDARD));
		$this->tabs->addSubTab(self::SUBTAB_LISTS, $this->pl->txt(self::SUBTAB_LISTS), $this->ctrl->getLinkTarget($this, self::CMD_LISTS));
		$this->tabs->setSubTabActive($active);
	}

	public function saveList(): void
    {
		if (!is_array($_POST['attendance_status']) || count($this->parent_gui->getMembers()) != count($_POST['attendance_status'])) {
            $this->tpl->setOnScreenMessage('failure',  $this->pl->txt('warning_list_incomplete'), true);
			$this->editList();
			return;
		}

		if ($checklist_id = $_GET['checklist_id']) {
			$checklist = xaliChecklist::find($checklist_id);
		} else {
			$checklist = new xaliChecklist();
			$checklist->setObjId($this->parent_gui->getObject()->getId());
		}

		$checklist->setLastEditedBy($this->user->getId());
		$checklist->setLastUpdate(time());
		$checklist->store();

		foreach ($_POST['attendance_status'] as $usr_id => $status) {
			$entry = $checklist->getEntryOfUser($usr_id);
			$entry->setChecklistId($checklist->getId());
			$entry->setStatus($status);
			$entry->setUserId($usr_id);
			$entry->store();
            if (intval($status) === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
                if (is_array( $_POST['absence_reason']) && key_exists( $entry->getId(), $_POST['absence_reason']) && $reason_id = $_POST['absence_reason'][$entry->getId()] !== null) {
                    /** @var xaliAbsenceStatement $stm */
                    $stm = xaliAbsenceStatement::findOrGetInstance($entry->getId());
                    $stm->setReasonId($reason_id);
                    $stm->store();
                }
            }
		}

		// update LP
		xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success',  $this->pl->txt("msg_checklist_saved"), true);

        $this->ctrl->redirect($this, self::CMD_LISTS);
	}

	public function saveUser(): void
    {
		$user_id = $_GET['user_id'];
		foreach ($_POST['attendance_status'] as $checklist_id => $status) {
			$checklist = xaliChecklist::find($checklist_id);
			$entry = $checklist->getEntryOfUser($user_id);
			$entry->setStatus($status);
			$entry->store();

			$checklist->setLastEditedBy($this->user->getId());
			$checklist->setLastUpdate(time());
			$checklist->store();
            if (intval($status) === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
                if (is_array( $_POST['absence_reason']) && key_exists( $entry->getId(), $_POST['absence_reason']) && $reason_id = $_POST['absence_reason'][$entry->getId()] !== null) {
                    /** @var xaliAbsenceStatement $stm */
                    $stm = xaliAbsenceStatement::findOrGetInstance($entry->getId());
                    $stm->setReasonId($reason_id);
                    $stm->store();
                }
            }
		}

		// update LP
		xaliUserStatus::updateUserStatus($user_id, $this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success',  $this->pl->txt("msg_user_saved"), true);

        $this->ctrl->redirect($this, self::CMD_SHOW_USERS);
	}

	public function addList(): void
    {
		$form = new ilPropertyFormGUI();

		$date_input = new ilDateTimeInputGUI($this->pl->txt('form_input_date'), 'checklist_date');
		$form->addItem($date_input);

		$form->addCommandButton(self::CMD_CREATE_LIST, $this->lng->txt('create'));
		$form->addCommandButton(self::CMD_CANCEL, $this->lng->txt('cancel'));

		$form->setFormAction($this->ctrl->getFormAction($this));

		$this->tpl->setContent($form->getHTML());
		return;
	}

	public function createList(): void
    {
		$date = date('Y-m-d', strtotime($_POST['checklist_date']));
		$this->checkDate($date);
		$checklist = new xaliChecklist();
		$checklist->setObjId($this->parent_gui->getObject()->getId());
		$checklist->setChecklistDate($date);
		$checklist->setLastEditedBy($this->user->getId());
		$checklist->setLastUpdate(time());
		$checklist->create();

		// update LP
		xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success',  $this->pl->txt("msg_list_created"), true);

        $this->ctrl->setParameter($this, 'checklist_id', $checklist->getId());
		$this->ctrl->redirect($this, self::CMD_EDIT_LIST);
	}


	protected function checkDate($date): void
    {
		$where = xaliChecklist::where(array('checklist_date' => $date, 'obj_id' => $this->parent_gui->getObject()->getId()));
		if ($where->hasSets()) {
            $this->tpl->setOnScreenMessage('failure',  $this->pl->txt('msg_date_already_used'), true);
			$this->ctrl->redirect($this, self::CMD_ADD_LIST);
		}
	}

	public function editList(): void
    {
		$checklist_id = $_GET['checklist_id'];
		if (!$checklist_id) {
			if ($_GET['entry_id']) {
				$checklist_id = xaliChecklistEntry::find($_GET['entry_id'])->getChecklistId();
			} else {
				$this->ctrl->redirect($this, self::CMD_LISTS);
			}
		}

		$this->ctrl->setParameter($this, 'checklist_id', $checklist_id);
		$users = $this->parent_gui->getMembers();
		$checklist = xaliChecklist::find($checklist_id);
		if (!$checklist->hasSavedEntries()) {
            $this->tpl->setOnScreenMessage('info',  $this->pl->txt('list_unsaved'), true);
		}

		$xaliChecklistTableGUI = new xaliChecklistTableGUI($this, $checklist, $users);
		$xaliChecklistTableGUI->setTitle(sprintf($this->pl->txt('table_checklist_title'), date('D, d.m.Y', strtotime($checklist->getChecklistDate()))));

		$this->tpl->setContent($xaliChecklistTableGUI->getHTML());
	}

	public function editUser(): void
    {
		$user_id = $_GET['user_id'];

		if (!$user_id) {
			if ($_GET['entry_id']) {
				$user_id = xaliChecklistEntry::find($_GET['entry_id'])->getUserId();
			} else {
				$this->ctrl->redirect($this, self::CMD_STANDARD);
			}
		}

		$xaliUserDetailsGUI = new xaliUserDetailsTableGUI($this, $user_id, $this->parent_gui->getObject()->getId());
		$this->tpl->setContent($xaliUserDetailsGUI->getHTML());
	}

	public function confirmDeleteLists(): void
    {
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->pl->txt('msg_confirm_delete_list'));
		$conf->setConfirm($this->lng->txt('delete'), 'deleteLists');
		$conf->setCancel($this->lng->txt('cancel'), 'cancel');

		$checklist_ids = $_GET['checklist_id'] ? array($_GET['checklist_id']) : $_POST['checklist_ids'];
		foreach ($checklist_ids as $id) {
			$checklist = xaliChecklist::find($id);
			$conf->addItem('checklist_id[]', $checklist->getId(), sprintf($this->pl->txt('table_checklist_title'), $checklist->getChecklistDate()));
		}
		$this->tpl->setContent($conf->getHTML());
	}

	public function deleteLists(): void
    {
		$checklist_ids = is_array($_POST['checklist_id']) ? $_POST['checklist_id'] : array($_POST['checklist_id']);
		foreach ($checklist_ids as $id) {
			$checklist = xaliChecklist::find($id);
			$checklist->delete();
		}

		// update LP
		xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success',  $this->pl->txt("msg_list_deleted"), true);

        $this->ctrl->redirect($this, self::CMD_LISTS);
	}

	#[NoReturn] public function saveEntry(): void
    {
		/** @var xaliChecklist $checklist */
		$checklist = xaliChecklist::find($_GET['checklist_id']);
		$checklist_entry = $checklist->getEntryOfUser($_GET['user_id']);
		$checklist_entry->setStatus($_GET['status']);
		$checklist_entry->store();

		// update LP
		xaliUserStatus::updateUserStatus($_GET['user_id'], $this->parent_gui->getObject()->getId());

		exit;
	}


    /**
     * @throws ilCtrlException
     */
	protected function cancel(): void
    {
		$this->ctrl->redirect($this, self::CMD_LISTS);
	}


    /**
     * async auto complete method for user filter in overview
     */
    #[NoReturn] public function addUserAutoComplete(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array( 'login', 'firstname', 'lastname' ));
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        $list = $auto->getList($_REQUEST['term']);

        $array = json_decode($list, true);
        $members = $this->pl->getMembers();

        foreach ($array['items'] as $key => $item) {
            if (!in_array($item['id'], $members)) {
                unset($array['items'][$key]);
            }
        }

        $list = json_encode($array);
        echo $list;
        exit();
    }

    #[NoReturn] protected function saveAbsenceReason(): void
    {
        /** @var xaliChecklist $checklist */
        $checklist = xaliChecklist::find($_GET['checklist_id']);

        $entry = $checklist->getEntryOfUser($_GET['user_id']);

        if (intval($entry->getStatus()) === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
            if (($reason_id = $_GET['absence_reason']) !== null) {
                /** @var xaliAbsenceStatement $stm */
                $stm = xaliAbsenceStatement::findOrGetInstance($entry->getId());
                $stm->setReasonId($reason_id);
                $stm->store();
            }
        }

        exit;
    }
}