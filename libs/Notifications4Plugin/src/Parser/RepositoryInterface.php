<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser;

use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Exception\Notifications4PluginException;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationInterface;

/**
 * Interface RepositoryInterface
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser
 */
interface RepositoryInterface
{
    public function addParser(Parser $parser): void;


    /**
     * @internal
     */
    public function dropTables(): void;



    public function factory(): FactoryInterface;


    /**
     *
     *
     * @throws Notifications4PluginException
     */
    public function getParserByClass(string $parser_class): Parser;


    /**
     *
     *
     * @throws Notifications4PluginException
     */
    public function getParserForNotification(NotificationInterface $notification): Parser;


    /**
     * @return Parser[]
     */
    public function getPossibleParsers(): array;


    /**
     * @internal
     */
    public function installTables(): void;


    /**
     *
     *
     * @throws Notifications4PluginException
     */
    public function parseSubject(Parser $parser, NotificationInterface $notification, array $placeholders = [], ?string $language = null): string;


    /**
     *
     *
     * @throws Notifications4PluginException
     */
    public function parseText(Parser $parser, NotificationInterface $notification, array $placeholders = [], ?string $language = null): string;
}
