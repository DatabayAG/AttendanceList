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


class xaliChecklistGUI extends xaliGUI
{
    protected xaliChecklist $checklist;
    protected ilObjUser $user;
    protected xaliSetting $settings;

    public function __construct(ilObjAttendanceListGUI $parent_gui)
    {
        parent::__construct($parent_gui);

        $this->settings = xaliSetting::find($parent_gui->getObject()->getId());

        $list_query = xaliChecklist::where(['checklist_date' => date('Y-m-d'), 'obj_id' => $parent_gui->getObject()->getId()]);
        if ($list_query->hasSets()) {
            $this->checklist = $list_query->first();
        } else {
            $this->checklist = new xaliChecklist();
            $this->checklist->setChecklistDate(date('Y-m-d'));
            $this->checklist->setObjId($parent_gui->getObject()->getId());
        }
    }

    public function show(): void
    {
        // activation passed, don't show a list
        if ((time() - (60 * 60 * 24)) > strtotime($this->settings->getActivationTo())) {
            $this->tpl->setOnScreenMessage('info', $this->pl->txt('activation_passed'), true);
            return;
        }

        // activation not yet begun, don't show a list
        if ((time()) < strtotime($this->settings->getActivationFrom())) {
            $this->tpl->setOnScreenMessage('info', $this->pl->txt('activation_not_started_yet'), true);
            return;
        }

        // incomplete, display info
        if (!$this->checklist->isComplete()) {
            $this->tpl->setOnScreenMessage('info', $this->pl->txt('list_unsaved_today'), true);
        }
        $users = $this->parent_gui->getMembers();

        $xaliChecklistTableGUI = new xaliChecklistTableGUI($this, $this->checklist, $users);
        $xaliChecklistTableGUI->setTitle(sprintf($this->pl->txt('table_checklist_title'), $this->checklist->getChecklistDate()));

        $this->tpl->setContent($xaliChecklistTableGUI->getHTML());
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
            $this->tpl->printToStdout();
            /*
            if (self::version()->is6()) {
                $this->tpl->printToStdout();
            } else {
                $this->show();
            }
            */
            return;
        }

        $this->checklist->setLastEditedBy($this->user->getId());
        $this->checklist->setLastUpdate(time());
        $this->checklist->store();

        $absence_reason = $this->httpWrapper->post()->retrieve(
            "absence_reason",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        foreach ($attendance_status as $usr_id => $status) {
            $entry = $this->checklist->getEntryOfUser($usr_id);
            $entry->setChecklistId((int) $this->checklist->getId());
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

        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

}
