<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser;

use ILIAS\UI\Implementation\Component\Input\Input;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Exception\Notifications4PluginException;

/**
 * Interface Parser
 *
 */
interface Parser
{
    /**
     * @var string
     *
     * @abstract
     */
    public const DOC_LINK = "";
    /**
     * @var string
     *
     * @abstract
     */
    public const NAME = "";



    public function getClass(): string;



    public function getDocLink(): string;



    public function getName(): string;


    /**
     * @return Input[]
     */
    public function getOptionsFields(): array;


    /**
     *
     *
     * @throws Notifications4PluginException
     */
    public function parse(string $text, array $placeholders = [], array $options = []): string;
}
