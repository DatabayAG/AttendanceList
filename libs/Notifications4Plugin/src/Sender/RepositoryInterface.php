<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Sender;

use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Exception\Notifications4PluginException;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationInterface;

/**
 * Interface RepositoryInterface
 *
 */
interface RepositoryInterface
{
    /**
     * @internal
     */
    public function dropTables(): void;



    public function factory(): FactoryInterface;


    /**
     * @internal
     */
    public function installTables(): void;


    /**
     * @param Sender                $sender   A concrete srNotificationSender object, e.g. srNotificationMailSender
     * @param string|null           $language Omit to choose the default language
     *
     * @throws Notifications4PluginException
     */
    public function send(Sender $sender, NotificationInterface $notification, array $placeholders = [], ?string $language = null): void;
}
