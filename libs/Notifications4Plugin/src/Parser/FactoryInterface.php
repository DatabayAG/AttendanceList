<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser;

/**
 * Interface FactoryInterface
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser
 */
interface FactoryInterface
{
    public function twig(): twigParser;
}
