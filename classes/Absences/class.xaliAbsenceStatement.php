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

class xaliAbsenceStatement extends ActiveRecord
{
    public const TABLE_NAME = 'xali_absence_statement';

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     */
    protected ?string $entry_id;

    /**
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $reason_id = 0;

    /**
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_is_unique        true
     * @db_length           256
     * @db_fieldtype        text
     */
    protected string $comment_text = "";

    /**
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $file_id = 0;

    public function getReason(): ?string
    {
        if ($this->getReasonId()) {
            /** @var xaliAbsenceReason $reason */
            $reason = xaliAbsenceReason::find($this->getReasonId());
            if ($reason) {
                return $reason->getTitle();
            }
        }

        return null;
    }

    public function getEntryId(): string
    {
        return $this->entry_id;
    }

    public function setEntryId(string $entry_id): void
    {
        $this->entry_id = $entry_id;
    }

    public function getReasonId(): int
    {
        return $this->reason_id;
    }

    public function setReasonId(int $reason_id): void
    {
        $this->reason_id = $reason_id;
    }

    public function getComment(): string
    {
        return $this->comment_text;
    }

    public function setComment(string $comment): void
    {
        $this->comment_text = $comment;
    }

    public function getFileId(): int
    {
        return $this->file_id;
    }

    public function setFileId(int $file_id): void
    {
        $this->file_id = $file_id;
    }
}
