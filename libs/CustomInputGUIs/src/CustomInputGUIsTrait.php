<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs;

/**
 * Trait CustomInputGUIsTrait
 *
 */
trait CustomInputGUIsTrait
{
    final protected static function customInputGUIs(): CustomInputGUIs
    {
        return CustomInputGUIs::getInstance();
    }
}
