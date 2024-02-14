<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\UIInputComponentWrapperInputGUI;

use ILIAS\UI\Implementation\Component\Input\NameSource;

class UIInputComponentWrapperNameSource implements NameSource
{
    /**
     * @var string
     */
    protected $post_var;


    public function __construct(string $post_var)
    {
        $this->post_var = $post_var;
    }


    public function getNewName(): string
    {
        return $this->post_var;
    }
}
