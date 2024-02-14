<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\ColorPickerInputGUI;

use ilColorPickerInputGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;

class ColorPickerInputGUI extends ilColorPickerInputGUI
{
    public function render(/*string*/ $a_mode = ""): string
    {
        $tpl = new Template("Services/Form/templates/default/tpl.property_form.html", true, true);

        $this->insert($tpl);

        $html = $tpl->get();

        $html = preg_replace("/<\/div>\s*<!--/", "<!--", $html);
        $html = preg_replace("/<\/div>\s*<!--/", "<!--", $html);

        return $html;
    }
}
