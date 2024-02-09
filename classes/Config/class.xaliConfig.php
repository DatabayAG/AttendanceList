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
 * Class xaliConfig
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliConfig extends ActiveRecord
{
    public const F_INTERVAL_REMINDER_EMAIL = 'interval_reminder_email';
    public const F_SENDER_REMINDER_EMAIL = 'sender_reminder_email';
    public const F_HTTP_PATH = 'http_path';
    public const F_SHOW_NOT_RELEVANT = 'show_not_relevant';
    public const F_SHOW_PRESENT_TOTAL = 'show_present_total';
    public const TABLE_NAME = 'xali_config';

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    protected static array $cache = [];
    protected static array $cache_loaded = [];

    public static function getConfig($name): mixed
    {
        if (!array_key_exists($name, self::$cache_loaded)) {
            self::$cache_loaded[$name] = false;
        }

        if (!self::$cache_loaded[$name]) {
            try {
                $obj = new self($name);
            } catch (Exception $e) {
                $obj = new self();
                $obj->setName($name);
            }
            self::$cache[$name] = json_decode($obj->getValue(), true);
            self::$cache_loaded[$name] = true;
        }

        return self::$cache[$name];
    }

    public static function set($name, $value): void
    {
        try {
            $obj = new self($name);
        } catch (Exception $e) {
            $obj = new self();
            $obj->setName($name);
        }
        $obj->setValue(json_encode($value, JSON_THROW_ON_ERROR));

        if (self::where(['name' => $name])->hasSets()) {
            $obj->update();
        } else {
            $obj->create();
        }
    }

    /**
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           250
     */
    protected ?string $name;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     */
    protected string $value = "";

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
