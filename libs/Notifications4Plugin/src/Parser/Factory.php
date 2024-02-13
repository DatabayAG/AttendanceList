<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser;

use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * Class Factory
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser
 */
final class Factory implements FactoryInterface
{
    use Notifications4PluginTrait;

    /**
     * @var FactoryInterface|null
     */
    protected static $instance = null;


    /**
     * Factory constructor
     */
    private function __construct()
    {

    }



    public static function getInstance(): FactoryInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }



    public function twig(): twigParser
    {
        return new twigParser();
    }
}
