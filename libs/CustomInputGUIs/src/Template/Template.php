<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template;

use ilTemplate;

/**
 * Class Template
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template
 */
class Template extends ilTemplate
{
    /**
     * Template constructor
     *
     */
    public function __construct(string $template_file, bool $remove_unknown_variables = true, bool $remove_empty_blocks = true)
    {
        parent::__construct($template_file, $remove_unknown_variables, $remove_empty_blocks);
    }

    /* *
     * @param bool $a_force
     * /
    public function fillJavaScriptFiles($a_force = false)
    {
        parent::fillJavaScriptFiles($a_force);

        if ($this->blockExists("js_file")) {
            reset($this->js_files);

            foreach ($this->js_files as $file) {
                if (strpos($file, "data:application/javascript;base64,") === 0) {
                    $this->fillJavascriptFile($file, "");
                }
            }
        }
    }*/

    /**
     * @param mixed  $value
     */
    public function setVariableEscaped(string $key, $value): void
    {
        $this->setVariable($key, htmlspecialchars($value));
    }
}
