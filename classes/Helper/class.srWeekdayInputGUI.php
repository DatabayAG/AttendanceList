<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class srWeekdayInputGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class srWeekdayInputGUI extends ilFormPropertyGUI {

	public const TYPE = 'weekday';
    protected array $value = array();
    protected ilLanguage $lng;
	protected ilAttendanceListPlugin $pl;


	public function __construct($a_title, $a_postvar) {
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
		$this->setValue($a_values[$this->getPostVar()]);
	}


	public function checkInput(): bool
    {
		return ($_POST[$this->getPostVar()] == null) || (count($_POST[$this->getPostVar()]) <= 7);
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

		$days = array( 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun' );

		for ($i = 1; $i < 8; $i ++) {
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