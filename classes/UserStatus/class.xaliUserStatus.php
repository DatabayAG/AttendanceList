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


class xaliUserStatus extends ActiveRecord
{
    public const TABLE_NAME = 'xali_user_status';

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     * @db_is_primary   true
     * @db_sequence     true
     */
    protected ?int $id = 0;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected int $attendancelist_id = 0;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected int $user_id = 0;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    timestamp
     */
    protected string $created_at;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    timestamp
     */
    protected string $updated_at;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected int $created_user_id = 0;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected int $updated_user_id = 0;

    /**
     * @db_has_field    true
     * @db_is_notnull   true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected int $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;

    protected bool $status_changed = false;

    protected int $old_status;
    protected array $checklist_ids = [];
    protected array $attendance_statuses = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAttendancelistId(): int
    {
        return $this->attendancelist_id;
    }

    public function setAttendancelistId(int $attendancelist_id): void
    {
        $this->attendancelist_id = $attendancelist_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function getCreatedUserId(): int
    {
        return $this->created_user_id;
    }

    public function setCreatedUserId(int $created_user_id): void
    {
        $this->created_user_id = $created_user_id;
    }

    public function getUpdatedUserId(): int
    {
        return $this->updated_user_id;
    }

    public function setUpdatedUserId(int $updated_user_id): void
    {
        $this->updated_user_id = $updated_user_id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        if ($status != $this->status) {
            $this->old_status = $this->status;
            $this->status_changed = true;
        }
        $this->status = $status;
    }

    public function hasStatusChanged(): bool
    {
        return $this->status_changed;
    }

    public function create(): void
    {
        global $DIC;
        $ilUser = $DIC->user();

        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        $this->created_user_id = $ilUser->getId();
        $this->updated_user_id = $ilUser->getId();
        parent::create();
        ilLPStatusWrapper::_updateStatus($this->attendancelist_id, $this->user_id);
        ilLearningProgress::_tracProgress(
            $this->user_id,
            $this->attendancelist_id,
            ilAttendanceListPlugin::lookupRefId($this->attendancelist_id),
            'xali'
        );
    }

    public function update(): void
    {
        global $DIC;
        $ilUser = $DIC->user();

        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_user_id = $ilUser->getId();
        parent::update();

        ilLPStatusWrapper::_updateStatus($this->attendancelist_id, $this->user_id);
    }

    public function getAttendanceStatuses($status): mixed
    {
        if (!isset($this->attendance_statuses[$status])) {
            $checklist_ids = $this->getChecklistIds();
            if (!$checklist_ids) {
                $this->attendance_statuses[$status] = 0;
            } else {
                $operators = [
                    'user_id' => '=',
                    'status' => '=',
                    'checklist_id' => 'IN'
                ];

                $this->attendance_statuses[$status] = xaliChecklistEntry::where([
                    'user_id' => $this->user_id,
                    'status' => $status,
                    'checklist_id' => $checklist_ids
                ], $operators)->count();
            }
        }
        return $this->attendance_statuses[$status];
    }

    public function getReachedPercentage(): int
    {
        $nr_of_checklists = count($this->getChecklistIds());
        if (!$nr_of_checklists) {
            return 0;
        }

        $not_present = ($this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT) + $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_EXCUSED)
                + $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED)) * 100;

        if ($not_present === 0) {
            return 0;
        }

        return (int) round(
            $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT) / $nr_of_checklists * 100
        );
    }

    public function getUnedited(): int
    {
        return count($this->getChecklistIds())
            - $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT)
//			- $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_EXCUSED)
            - $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED)
            - $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_NOT_RELEVANT);
    }

    public static function getInstance(int $user_id, int $attendance_list_id): xaliUserStatus|ActiveRecord
    {
        $xaliUserStatus = xaliUserStatus::where(['user_id' => $user_id, 'attendancelist_id' => $attendance_list_id])->first();
        if (!$xaliUserStatus) {
            $xaliUserStatus = new self();
            $xaliUserStatus->setUserId($user_id);
            $xaliUserStatus->setAttendancelistId($attendance_list_id);
        }
        return $xaliUserStatus;
    }

    public function updateLPStatus(): void
    {
        $ilObjAttendanceList = new ilObjAttendanceList(ilAttendanceListPlugin::lookupRefId($this->getAttendancelistId()));

        /** @var xaliSetting $xaliSetting */
        $xaliSetting = xaliSetting::find($this->attendancelist_id);
        if ($this->getReachedPercentage() >= $this->calcMinimumAttendance() && !$ilObjAttendanceList->getOpenAbsenceStatementsForUser($this->getUserId())) {
            $this->setStatus(ilLPStatus::LP_STATUS_COMPLETED_NUM);                      //COMPLETED: minimum attendance is reached
        } elseif ((time() - (60 * 60 * 24)) > strtotime($xaliSetting->getActivationTo())) {
            $this->setStatus(ilLPStatus::LP_STATUS_FAILED_NUM);                         //FAILED: minimum attendance not reached and time is up
        } elseif ($this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT) == 0
            && $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) == 0) {
            $this->setStatus(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);                  //NOT ATTEMPTED: no absences and no presences
        } else {
            $this->setStatus(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);                    //IN PROGR: minimum attendance not reached, time not yet up
        }
    }

    public static function updateUserStatuses($attendancelist_id): void
    {
        foreach (ilAttendanceListPlugin::getInstance()->getMembers(ilAttendanceListPlugin::lookupRefId($attendancelist_id)) as $user_id) {
            self::updateUserStatus($user_id, $attendancelist_id);
        }
    }

    public static function updateUserStatus($user_id, $attendancelist_id): void
    {
        $user_status = self::getInstance($user_id, $attendancelist_id);
        $user_status->updateLPStatus();
        $user_status->store();
    }

    protected function getChecklistIds(): array
    {
        if (!$this->checklist_ids) {
            $this->checklist_ids = [];
            foreach (xaliChecklist::where(['obj_id' => $this->attendancelist_id])->get() as $checklist) {
                $this->checklist_ids[] = $checklist->getId();
            }
        }
        return $this->checklist_ids;
    }

    public function getPresentTotalString(): string
    {
        return $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT) . ' / ' . ($this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT)
                + $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED));
    }

    public function calcMinimumAttendance(): float|int
    {
        /** @var xaliSetting $xaliSetting */
        $xaliSetting = xaliSetting::find($this->attendancelist_id);

        $minimum_attendance = intval($xaliSetting->getMinimumAttendance());

        if ($minimum_attendance === xaliSetting::CALC_AUTO_MINIMUM_ATTENDANCE) {
            $total = $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED) + $this->getUnedited();
            if ($total > 0) {
                $minimum_attendance = $this->getAttendanceStatuses(xaliChecklistEntry::STATUS_PRESENT) / $total;
            } else {
                $minimum_attendance = 100;
            }
        }

        return $minimum_attendance;
    }
}
