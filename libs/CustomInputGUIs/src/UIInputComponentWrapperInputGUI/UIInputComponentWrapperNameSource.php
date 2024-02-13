<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\UIInputComponentWrapperInputGUI;

use ILIAS\UI\Implementation\Component\Input\NameSource;

/**
 * Class UIInputComponentWrapperNameSource
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\UIInputComponentWrapperInputGUI
 */
class UIInputComponentWrapperNameSource implements NameSource
{
    /**
     * @var string
     */
    protected $post_var;


    /**
     * UIInputComponentWrapperNameSource constructor
     *
     */
    public function __construct(string $post_var)
    {
        $this->post_var = $post_var;
    }



    public function getNewName(): string
    {
        return $this->post_var;
    }
}
