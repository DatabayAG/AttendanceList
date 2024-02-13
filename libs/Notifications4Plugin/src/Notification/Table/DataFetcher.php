<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Table;

use srag\Plugins\AttendanceList\Libs\DataTableUI\Component\Data\Data;
use srag\Plugins\AttendanceList\Libs\DataTableUI\Component\Data\Row\RowData;
use srag\Plugins\AttendanceList\Libs\DataTableUI\Component\Settings\Settings;
use srag\Plugins\AttendanceList\Libs\DataTableUI\Implementation\Data\Fetcher\AbstractDataFetcher;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationInterface;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * Class DataFetcher
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Table
 */
class DataFetcher extends AbstractDataFetcher
{
    use Notifications4PluginTrait;


    public function fetchData(Settings $settings): Data
    {
        return self::dataTableUI()->data()->data(
            array_map(function (
                NotificationInterface $notification
            ): RowData {
                return self::dataTableUI()->data()->row()->getter($notification->getId(), $notification);
            }, self::notifications4plugin()->notifications()->getNotifications($settings)),
            self::notifications4plugin()->notifications()->getNotificationsCount()
        );
    }
}
