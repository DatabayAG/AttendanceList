<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use ilFormPropertyGUI;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;

/**
 * Trait InputGUIWrapperConstraintTrait
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent
 */
trait InputGUIWrapperConstraintTrait
{
    /**
     * InputGUIWrapperConstraintTrait constructor
     *
     */
    public function __construct(ilFormPropertyGUI $input, DataFactory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            function ($value) use ($input): bool {
                return boolval($input->checkInput());
            },
            function (callable $txt, $value) use ($input): string {
                return strval($input->getAlert());
            },
            $data_factory,
            $lng
        );
    }
}
