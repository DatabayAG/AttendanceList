<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template;

use ilTemplate;

class Template extends ilTemplate
{
    public function __construct(string $template_file, bool $remove_unknown_variables = true, bool $remove_empty_blocks = true)
    {
        parent::__construct($template_file, $remove_unknown_variables, $remove_empty_blocks);
    }



    /**
     * @param mixed $value
     */
    public function setVariableEscaped(string $key, $value): void
    {
        $this->setVariable($key, htmlspecialchars($value));
    }
}
