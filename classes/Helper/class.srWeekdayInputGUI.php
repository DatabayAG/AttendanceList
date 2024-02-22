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

/**
 * Class srWeekdayInputGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class srWeekdayInputGUI extends ilFormPropertyGUI
{
    public const TYPE = 'weekday';
    protected array $value = [];
    protected ilLanguage $lng;
    protected ilAttendanceListPlugin $pl;

    public function __construct($a_title, $a_postvar)
    {
        global $DIC;
        $lng = $DIC->language();
        $this->lng = $lng;
        $this->pl = ilAttendanceListPlugin::getInstance();
        parent::__construct($a_title, $a_postvar);
        $this->setType(self::TYPE);
    }

    public function setValue(array $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? []);
    }

    public function checkInput(): bool
    {
        if (!$this->http->wrapper()->post()->has($this->getPostVar())) {
            return true;
        }


        $postData = $this->http->wrapper()->post()->retrieve(
            $this->getPostVar(),
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
        );
        return count($postData) <= 7;
    }

    /**
     * Insert property html
     */
    public function insert(&$a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * @throws ilTemplateException
     */
    protected function render(): string
    {
        $tpl = $this->pl->getTemplate("default/tpl.weekday_input.html");

        $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];

        for ($i = 1; $i < 8; $i++) {
            $tpl->setCurrentBlock('byday_simple');

            if (is_array($this->getValue()) && in_array($days[$i], $this->getValue())) {
                $tpl->setVariable('BYDAY_WEEKLY_CHECKED', 'checked="checked"');
            }
            $tpl->setVariable('TXT_ON', $this->lng->txt('cal_on'));
            $tpl->setVariable('BYDAY_WEEKLY_VAL', $days[$i]);
            $tpl->setVariable('TXT_DAY_SHORT', ilCalendarUtil::_numericDayToString($i, false));
            $tpl->setVariable('POSTVAR', $this->getPostVar());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Get HTML for table filter
     * @throws ilTemplateException
     */
    public function getTableFilterHTML(): string
    {
        return $this->render();
    }
}
