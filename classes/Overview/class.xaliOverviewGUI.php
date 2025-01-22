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

class xaliOverviewGUI extends xaliGUI
{
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
    public const CMD_ADD_USER_AUTO_COMPLETE = 'addUserAutoComplete';
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
        $xaliOverviewUserTableGUI = new xaliOverviewUserTableGUI($this, $users, $this->parent_gui->getObject()->getId());
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
        $attendance_status = $this->httpWrapper->post()->retrieve(
            "attendance_status",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        if (count($this->parent_gui->getMembers()) !== count($attendance_status)) {
            $this->tpl->setOnScreenMessage('failure', $this->pl->txt('warning_list_incomplete'), true);
            $this->editList();
            return;
        }

        $checklist_id = $this->httpWrapper->query()->retrieve(
            "checklist_id",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );

        if ($checklist_id) {
            $checklist = xaliChecklist::find($checklist_id);
        } else {
            $checklist = new xaliChecklist();
            $checklist->setObjId($this->parent_gui->getObject()->getId());
        }

        $checklist->setLastEditedBy($this->user->getId());
        $checklist->setLastUpdate(time());
        $checklist->store();

        $absence_reason = $this->httpWrapper->post()->retrieve(
            "absence_reason",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        foreach ($attendance_status as $usr_id => $status) {
            $entry = $checklist->getEntryOfUser($usr_id);
            $entry->setChecklistId($checklist->getId());
            $entry->setStatus($status);
            $entry->setUserId($usr_id);
            $entry->store();
            if ($status === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
                $reason_id = $absence_reason[$entry->getId()] ?? null;
                if ($reason_id) {
                    /** @var xaliAbsenceStatement $stm */
                    $stm = xaliAbsenceStatement::findOrGetInstance($entry->getId());
                    $stm->setReasonId($reason_id);
                    $stm->store();
                }
            }
        }

        // update LP
        xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_checklist_saved"), true);

        $this->ctrl->redirect($this, self::CMD_LISTS);
    }

    public function saveUser(): void
    {
        $user_id = $this->httpWrapper->query()->retrieve(
            "user_id",
            $this->refinery->kindlyTo()->int()
        );

        $attendance_status = $this->httpWrapper->post()->retrieve(
            "attendance_status",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        $absence_reason = $this->httpWrapper->post()->retrieve(
            "absence_reason",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        foreach ($attendance_status as $checklist_id => $status) {
            /** @var xaliChecklist $checklist */
            $checklist = xaliChecklist::find($checklist_id);
            $entry = $checklist->getEntryOfUser($user_id);
            $entry->setStatus($status);
            $entry->store();

            $checklist->setLastEditedBy($this->user->getId());
            $checklist->setLastUpdate(time());
            $checklist->store();
            if ($status === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
                $reason_id = $absence_reason[$entry->getId()] ?? null;

                if ($reason_id) {
                    /** @var xaliAbsenceStatement $stm */
                    $stm = xaliAbsenceStatement::findOrGetInstance($entry->getId());
                    $stm->setReasonId($reason_id);
                    $stm->store();
                }
            }
        }

        // update LP
        xaliUserStatus::updateUserStatus($user_id, $this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_user_saved"), true);

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
        $checklist_date = $this->httpWrapper->post()->retrieve(
            "checklist_date",
            $this->refinery->kindlyTo()->string()
        );

        $date = (new DateTime($checklist_date))->format('Y-m-d');
        $this->checkDate($date);
        $checklist = new xaliChecklist();
        $checklist->setObjId($this->parent_gui->getObject()->getId());
        $checklist->setChecklistDate($date);
        $checklist->setLastEditedBy($this->user->getId());
        $checklist->setLastUpdate(time());
        $checklist->create();

        // update LP
        xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_list_created"), true);

        $this->ctrl->setParameter($this, 'checklist_id', $checklist->getId());
        $this->ctrl->redirect($this, self::CMD_EDIT_LIST);
    }

    protected function checkDate($date): void
    {
        $where = xaliChecklist::where(['checklist_date' => $date, 'obj_id' => $this->parent_gui->getObject()->getId()]);
        if ($where->hasSets()) {
            $this->tpl->setOnScreenMessage('failure', $this->pl->txt('msg_date_already_used'), true);
            $this->ctrl->redirect($this, self::CMD_ADD_LIST);
        }
    }

    public function editList(): void
    {
        $checklist_id = $this->httpWrapper->query()->retrieve(
            "checklist_id",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );
        if (!$checklist_id) {
            $entryId = $this->httpWrapper->query()->retrieve(
                "entry_id",
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->always(null)
                ])
            );
            if ($entryId) {
                $checklist_id = xaliChecklistEntry::find($entryId)->getChecklistId();
            } else {
                $this->ctrl->redirect($this, self::CMD_LISTS);
            }
        }

        $this->ctrl->setParameter($this, 'checklist_id', $checklist_id);
        $users = $this->parent_gui->getMembers();
        /** @var xaliChecklist $checklist */
        $checklist = xaliChecklist::find($checklist_id);
        if (!$checklist->hasSavedEntries()) {
            $this->tpl->setOnScreenMessage('info', $this->pl->txt('list_unsaved'), true);
        }

        $xaliChecklistTableGUI = new xaliChecklistTableGUI($this, $checklist, $users);
        $xaliChecklistTableGUI->setTitle(sprintf($this->pl->txt('table_checklist_title'), date('D, d.m.Y', strtotime($checklist->getChecklistDate()))));

        $this->tpl->setContent($xaliChecklistTableGUI->getHTML());
    }

    public function editUser(): void
    {
        $user_id = $this->httpWrapper->query()->retrieve(
            "user_id",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );

        if (!$user_id) {
            $entry_id = $this->httpWrapper->query()->retrieve(
                "entry_id",
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->always(null)
                ])
            );

            if ($entry_id) {
                $user_id = xaliChecklistEntry::find($entry_id)->getUserId();
            } else {
                $this->ctrl->redirect($this, self::CMD_STANDARD);
            }
        }

        $xaliUserDetailsGUI = new xaliUserDetailsTableGUI($this, (int) $user_id, $this->parent_gui->getObject()->getId());
        $this->tpl->setContent($xaliUserDetailsGUI->getHTML());
    }

    public function confirmDeleteLists(): void
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->pl->txt('msg_confirm_delete_list'));
        $conf->setConfirm($this->lng->txt('delete'), 'deleteLists');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');

        $checklistId = $this->httpWrapper->query()->retrieve(
            "checklist_id",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );


        $checklist_ids = $checklistId ? [$checklistId] : $this->httpWrapper->post()->retrieve(
            "checklist_ids",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );
        foreach ($checklist_ids as $id) {
            /** @var xaliChecklist $checklist */
            $checklist = xaliChecklist::find($id);
            $conf->addItem('checklist_id[]', (string) $checklist->getId(), sprintf($this->pl->txt('table_checklist_title'), $checklist->getChecklistDate()));
        }
        $this->tpl->setContent($conf->getHTML());
    }

    public function deleteLists(): void
    {
        $checklist_id = $this->httpWrapper->post()->retrieve(
            "checklist_id",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always([])
            ])
        );


        $checklist_ids = is_array($checklist_id) ? $checklist_id : [$checklist_id];
        foreach ($checklist_ids as $id) {
            /** @var xaliChecklist $checklist */
            $checklist = xaliChecklist::find($id);
            $checklist->delete();
        }

        // update LP
        xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

        $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_list_deleted"), true);

        $this->ctrl->redirect($this, self::CMD_LISTS);
    }

    public function saveEntry(): never
    {
        $checklistId = $this->httpWrapper->query()->retrieve(
            "checklist_id",
            $this->refinery->kindlyTo()->int()
        );
        $userId = $this->httpWrapper->query()->retrieve(
            "user_id",
            $this->refinery->kindlyTo()->int()
        );
        $status = $this->httpWrapper->query()->retrieve(
            "status",
            $this->refinery->kindlyTo()->int()
        );

        /** @var xaliChecklist $checklist */
        $checklist = xaliChecklist::find($checklistId);
        $checklist_entry = $checklist->getEntryOfUser($userId);
        $checklist_entry->setStatus($status);
        $checklist_entry->store();

        // update LP
        xaliUserStatus::updateUserStatus($userId, $this->parent_gui->getObject()->getId());

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
    public function addUserAutoComplete(): never
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(['login', 'firstname', 'lastname']);
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

    protected function saveAbsenceReason(): never
    {
        $checklistId = $this->httpWrapper->query()->retrieve(
            "checklist_id",
            $this->refinery->kindlyTo()->int()
        );
        $userId = $this->httpWrapper->query()->retrieve(
            "user_id",
            $this->refinery->kindlyTo()->int()
        );
        $reason_id = $this->httpWrapper->query()->retrieve(
            "absence_reason",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );

        /** @var xaliChecklist $checklist */
        $checklist = xaliChecklist::find($checklistId);

        $entry = $checklist->getEntryOfUser($userId);

        if ($entry->getStatus() === xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) {
            if ($reason_id) {
                /** @var xaliAbsenceStatement $stm */
                $stm = xaliAbsenceStatement::findOrGetInstance($entry->getId());
                $stm->setReasonId($reason_id);
                $stm->store();
            }
        }

        exit;
    }
}
