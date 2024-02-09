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

class xaliChecklistEntry extends ActiveRecord
{
    public const DB_TABLE_NAME = "xali_entry";
    public const STATUS_ABSENT_UNEXCUSED = 1;
    public const STATUS_ABSENT_EXCUSED = 2; // DEPRECATED
    public const STATUS_PRESENT = 3;
    public const STATUS_NOT_RELEVANT = 4;
    public const NOTIFICATION_NAME = "absence";

    public static function returnDbTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected ?string $id;

    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $checklist_id = 0;

    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $user_id = 0;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $status = 0;

    protected bool $status_changed = false;

    public function create(): void
    {
        parent::create();
        if ($this->status == self::STATUS_ABSENT_UNEXCUSED) {
            $this->sendAbsenceNotification();
        }
    }

    public function update(): void
    {
        if (($this->status == self::STATUS_ABSENT_UNEXCUSED) && $this->status_changed) {
            $this->sendAbsenceNotification();
        }
        parent::update();
    }

    protected function sendAbsenceNotification(): void
    {
        return;
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilObjUser = new ilObjUser($this->getUserId());

        /** @var xaliChecklist $xaliChecklist */
        $xaliChecklist = xaliChecklist::find($this->getChecklistId());
        $ref_id = ilAttendanceListPlugin::lookupRefId($xaliChecklist->getObjId());
        $link = xaliConfig::getConfig(xaliConfig::F_HTTP_PATH) . '/goto.php?target=xali_' . $ref_id . '_' . $this->id;

        $parent_course = ilAttendanceListPlugin::getInstance()->getParentCourseOrGroup($ref_id);
        $absence_date = $xaliChecklist->getChecklistDate('d.m.Y');
        $absence = 'Kurs "' . $parent_course->getTitle() . "\": \n";
        $absence .= "» $absence_date: " . $link . "\n";

        $placeholders = ['user' => $ilObjUser, 'absence' => $absence];

        $notification = self::notifications4plugin()->notifications()->getNotificationByName(self::NOTIFICATION_NAME);

        $sender_id = xaliConfig::getConfig(xaliConfig::F_SENDER_REMINDER_EMAIL);
        $sender = self::notifications4plugin()->sender()->factory()->internalMail($sender_id, $ilObjUser->getId());

        try {
            self::notifications4plugin()->sender()->send($sender, $notification, $placeholders);

            $interval = xaliConfig::getConfig(xaliConfig::F_INTERVAL_REMINDER_EMAIL);
            if (!$interval) {
                return;
            }

            $last_reminder = xaliLastReminder::where(['user_id' => $ilObjUser->getId()])->first();

            if (!$last_reminder) {
                $last_reminder = new xaliLastReminder();
                $last_reminder->setLastReminder(date('Y-m-d'));
                $last_reminder->setUserId($ilObjUser->getId());
                $last_reminder->create();
            } elseif ($last_reminder->getLastReminder() <= date('Y-m-d', strtotime("now -$interval days"))) {
                $last_reminder->setLastReminder(date('Y-m-d'));
                $last_reminder->update();
            }
            // don't reset the last reminder if there has already been a reminder
        } catch (Notifications4PluginException $ex) {

        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getChecklistId(): int
    {
        return $this->checklist_id;
    }

    public function setChecklistId(int $checklist_id): void
    {
        $this->checklist_id = $checklist_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        if ($this->status !== $status) {
            $this->status_changed = true;
        }
        $this->status = $status;
    }
}
