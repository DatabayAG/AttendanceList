<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification;

use ilPluginConfigGUI;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Form\FormBuilder;
use stdClass;

interface FactoryInterface
{
    public function fromDB(stdClass $data): NotificationInterface;



    public function newFormBuilderInstance(ilPluginConfigGUI $parentGui, NotificationInterface $notification): FormBuilder;



    public function newInstance(): NotificationInterface;
}
