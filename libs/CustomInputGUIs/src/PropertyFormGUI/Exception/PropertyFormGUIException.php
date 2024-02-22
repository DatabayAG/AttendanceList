<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\Exception;

use ilFormException;

/**
 *
 *
 *
 * @deprecated
 */
final class PropertyFormGUIException extends ilFormException
{
    /**
     * @var int
     * @deprecated
     */
    public const CODE_INVALID_FIELD = 2;
    /**
     * @var int
     * @deprecated
     */
    public const CODE_INVALID_PROPERTY_CLASS = 1;
    /**
     * @var int
     * @deprecated
     */
    public const CODE_MISSING_CONST_CONFIG_CLASS_NAME = 3;


    /**
     * @deprecated
     */
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }
}
