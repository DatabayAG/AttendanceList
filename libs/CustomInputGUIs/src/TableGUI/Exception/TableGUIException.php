<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\TableGUI\Exception;

use ilException;

/**
 *
 *
 *
 * @deprecated
 */
final class TableGUIException extends ilException
{
    /**
     * @var int
     *
     * @deprecated
     */
    public const CODE_INVALID_FIELD = 1;


    /**
     * TableGUIException constructor
     *
     *
     * @deprecated
     */
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }
}
