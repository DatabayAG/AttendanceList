<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use ILIAS\DI\Container;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * Class NotificationsCtrl
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification
 */
class NotificationsCtrl
{
    use Notifications4PluginTrait;

    public const CMD_LIST_NOTIFICATIONS = "listNotifications";
    public const LANG_MODULE = "notifications4plugin";
    public const TAB_NOTIFICATIONS = "notifications";

    private Container $dic;


    /**
     * NotificationsCtrl constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }



    public function executeCommand(): void
    {
        $this->setTabs();

        $next_class = $this->dic->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            case strtolower(NotificationCtrl::class):
                $this->dic->ctrl()->forwardCommand(new NotificationCtrl($this));
                break;

            default:
                $cmd = $this->dic->ctrl()->getCmd();

                switch ($cmd) {
                    case self::CMD_LIST_NOTIFICATIONS:
                        $this->{$cmd}();
                        break;

                    default:
                        break;
                }
                break;
        }
    }



    protected function listNotifications(): void
    {
        $table = self::notifications4plugin()->notifications()->factory()->newTableBuilderInstance($this);
        self::output()->output($table);
    }



    protected function setTabs(): void
    {

    }
}
