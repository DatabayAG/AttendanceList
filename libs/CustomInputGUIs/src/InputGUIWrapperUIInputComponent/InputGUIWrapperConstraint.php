<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;

/**
 * Class InputGUIWrapperConstraint
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent
 */
class InputGUIWrapperConstraint extends CustomConstraint implements Constraint
{
    use InputGUIWrapperConstraintTrait;
}
