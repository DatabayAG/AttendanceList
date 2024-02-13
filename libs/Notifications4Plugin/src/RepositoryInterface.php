<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin;

use srag\Plugins\AttendanceList\Libs\DIC\Plugin\Pluginable;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\RepositoryInterface as NotificationRepositoryInterface;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser\RepositoryInterface as ParserRepositoryInterface;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Sender\RepositoryInterface as SenderRepositoryInterface;

/**
 * Interface RepositoryInterface
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin
 */
interface RepositoryInterface
{
    public function dropTables(): void;



    public function getPlaceholderTypes(): array;



    public function getTableNamePrefix(): string;

    public function installTables(): void;



    public function notifications(): NotificationRepositoryInterface;



    public function parser(): ParserRepositoryInterface;



    public function sender(): SenderRepositoryInterface;



    public function withPlaceholderTypes(array $placeholder_types): self;



    public function withTableNamePrefix(string $table_name_prefix): self;
}
