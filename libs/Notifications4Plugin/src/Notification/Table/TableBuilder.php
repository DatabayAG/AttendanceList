<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Table;

use ILIAS\DI\Container;
use srag\Plugins\AttendanceList\Libs\DataTableUI\Component\Table;
use srag\Plugins\AttendanceList\Libs\DataTableUI\Implementation\Utils\AbstractTableBuilder;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationsCtrl;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * Class TableBuilder
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Table
 */
class TableBuilder extends AbstractTableBuilder
{
    use Notifications4PluginTrait;

    private Container $dic;

    public function __construct(NotificationsCtrl $parent)
    {
        parent::__construct($parent);
        global $DIC;
        $this->dic = $DIC;
    }



    public function render(): string
    {
        $this->dic->toolbar()->addComponent($this->dic->ui()->factory()->button()->standard(
            self::notifications4plugin()->getPlugin()->txt("notifications4plugin_add_notification"),
            $this->dic->ctrl()->getLinkTargetByClass(NotificationCtrl::class, NotificationCtrl::CMD_ADD_NOTIFICATION, "", false, false)
        ));

        return parent::render();
    }



    protected function buildTable(): Table
    {
        $table = self::dataTableUI()->table(
            "notifications4plugin_" . self::notifications4plugin()->getPlugin()->getPluginObject()->getId(),
            $this->dic->ctrl()->getLinkTarget($this->parent, NotificationsCtrl::CMD_LIST_NOTIFICATIONS, "", false, false),
            "",
            [
                self::dataTableUI()->column()->column(
                    "title",
                    self::notifications4plugin()->getPlugin()->txt("notifications4plugin_title")
                )->withDefaultSort(true),
                self::dataTableUI()->column()->column(
                    "description",
                    self::notifications4plugin()->getPlugin()->txt("notifications4plugin_description")
                ),
                self::dataTableUI()->column()->column(
                    "name",
                    self::notifications4plugin()->getPlugin()->txt("notifications4plugin_name")
                ),
                self::dataTableUI()->column()->column(
                    "actions",
                    self::notifications4plugin()->getPlugin()->txt("notifications4plugin_actions")
                )->withFormatter(self::dataTableUI()
                    ->column()
                    ->formatter()
                    ->actions()
                    ->actionsDropdown())
            ],
            new DataFetcher()
        )->withPlugin(self::notifications4plugin()->getPlugin());

        return $table;
    }
}
