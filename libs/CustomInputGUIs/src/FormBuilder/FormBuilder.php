<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\FormBuilder;

use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * Interface FormBuilder
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\FormBuilder
 */
interface FormBuilder
{
    public function getForm(): Form;



    public function render(): string;



    public function storeForm(): bool;
}
