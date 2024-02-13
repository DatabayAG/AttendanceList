<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\NumberInputGUI;

use ilNumberInputGUI;
use ilTableFilterItem;
use ilToolbarItem;
use srag\Plugins\AttendanceList\Libs\DIC\DICTrait;

/**
 * Class NumberInputGUI
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\NumberInputGUI
 */
class NumberInputGUI extends ilNumberInputGUI implements ilTableFilterItem, ilToolbarItem
{
    public function getTableFilterHTML(): string
    {
        return $this->render();
    }



    public function getToolbarHTML(): string
    {
        return $this->render();
    }
}
