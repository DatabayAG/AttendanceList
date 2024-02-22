<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\HiddenInputGUI;

use ilHiddenInputGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;

class HiddenInputGUI extends ilHiddenInputGUI
{
    public function __construct(string $a_postvar = "")
    {
        parent::__construct($a_postvar);
    }


    public function render(): string
    {
        $tpl = new Template("Services/Form/templates/default/tpl.property_form.html", true, true);

        $this->insert($tpl);

        return $tpl->get();
    }
}
