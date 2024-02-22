<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;

class InputGUIWrapperConstraint extends CustomConstraint implements Constraint
{
    use InputGUIWrapperConstraintTrait;
}
