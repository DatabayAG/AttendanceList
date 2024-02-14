<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs;


trait CustomInputGUIsTrait
{
    final protected static function customInputGUIs(): CustomInputGUIs
    {
        return CustomInputGUIs::getInstance();
    }
}
