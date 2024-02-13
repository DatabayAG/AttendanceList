<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification;

use ilPluginConfigGUI;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Form\FormBuilder;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Table\TableBuilder;
use stdClass;

/**
 * Interface FactoryInterface
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification
 */
interface FactoryInterface
{
    public function fromDB(stdClass $data): NotificationInterface;



    public function newFormBuilderInstance(ilPluginConfigGUI $parentGui, NotificationInterface $notification): FormBuilder;



    public function newInstance(): NotificationInterface;



    public function newTableBuilderInstance(NotificationsCtrl $parent): TableBuilder;
}
