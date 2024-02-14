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
 * Class xaliLastReminder
 *
 * database table to track the date of the last email reminder sent by this plugin
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliLastReminder extends ActiveRecord
{
    public const TABLE_NAME = 'xali_last_reminder';

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
    protected ?int $user_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        date
     */
    protected string $last_reminder = "";

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getLastReminder(): string
    {
        return $this->last_reminder;
    }

    public function setLastReminder(string $last_reminder): void
    {
        $this->last_reminder = $last_reminder;
    }
}
