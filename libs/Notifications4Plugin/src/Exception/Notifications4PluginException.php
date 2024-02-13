<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Exception;

use ilException;

/**
 * Class Notifications4PluginException
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Exception
 */
class Notifications4PluginException extends ilException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
