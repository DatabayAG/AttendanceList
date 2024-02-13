<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\WeekdayInputGUI;

use ilCalendarUtil;
use ilFormPropertyGUI;
use ILIAS\DI\Container;
use ilTableFilterItem;
use ilTemplate;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;
use srag\Plugins\AttendanceList\Libs\DIC\DICTrait;

class WeekdayInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    public const TYPE = 'weekday';
    /**
     * @var array
     */
    protected $value = [];
    private Container $dic;


    /**
     * WeekdayInputGUI constructor
     *
     */
    public function __construct(string $a_title, string $a_postvar)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($a_title, $a_postvar);

        $this->setType(self::TYPE);
    }



    public function checkInput(): bool
    {
        return ($_POST[$this->getPostVar()] == null) || (count($_POST[$this->getPostVar()]) <= 7);
    }



    public function getTableFilterHTML(): string
    {
        $html = $this->render();

        return $html;
    }



    public function getValue(): array
    {
        return $this->value;
    }


    /**
     * @param array $value
     */
    public function setValue(/*array*/ $value): void
    {
        $this->value = $value;
    }



    public function insert(ilTemplate $tpl): void
    {
        $html = $this->render();

        $tpl->setCurrentBlock("prop_generic");
        $tpl->setVariable("PROP_GENERIC", $html);
        $tpl->parseCurrentBlock();
    }



    public function render(): string
    {
        $tpl = new Template(__DIR__ . "/templates/tpl.weekday_input.html", true, true);

        $days = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];

        for ($i = 1; $i < 8; $i++) {
            $tpl->setCurrentBlock('byday_simple');

            if (in_array($days[$i], $this->getValue())) {
                $tpl->setVariable('BYDAY_WEEKLY_CHECKED', 'checked="checked"');
            }
            $tpl->setVariable('TXT_ON', $this->dic->language()->txt('cal_on'));
            $tpl->setVariable('BYDAY_WEEKLY_VAL', $days[$i]);
            $tpl->setVariable('TXT_DAY_SHORT', ilCalendarUtil::_numericDayToString($i, false));
            $tpl->setVariable('POSTVAR', $this->getPostVar());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }


    /**
     * @param array $values
     */
    public function setValueByArray(/*array*/ $values): void
    {
        $this->setValue($values[$this->getPostVar()] ? $values[$this->getPostVar()] : []);
    }
}
