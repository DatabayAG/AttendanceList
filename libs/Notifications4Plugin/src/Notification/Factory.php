<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification;

use ilDateTime;

use ilPluginConfigGUI;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Form\FormBuilder;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Table\TableBuilder;
use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;
use stdClass;

/**
 * Class Factory
 *
 * @package srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification
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



    public function fromDB(stdClass $data): NotificationInterface
    {
        $notification = $this->newInstance();

        $notification->setId($data->id);
        $notification->setName($data->name);
        $notification->setTitle($data->title);
        $notification->setDescription($data->description);
        $notification->setParser($data->parser);
        $notification->setParserOptions(json_decode($data->parser_options, true) ?? []);
        $notification->setSubjects(json_decode($data->subject, true) ?? []);
        $notification->setTexts(json_decode($data->text, true) ?? []);
        $notification->setCreatedAt(new ilDateTime($data->created_at, IL_CAL_DATETIME));
        $notification->setUpdatedAt(new ilDateTime($data->updated_at, IL_CAL_DATETIME));

        if (isset($data->default_language)) {
            $notification->default_language = $data->default_language;
        }

        return $notification;
    }



    public function newFormBuilderInstance(ilPluginConfigGUI $parentGui, NotificationInterface $notification): FormBuilder
    {
        $form = new FormBuilder($parentGui, $notification);

        return $form;
    }



    public function newInstance(): NotificationInterface
    {
        $notification = new Notification();

        return $notification;
    }



    public function newTableBuilderInstance(NotificationsCtrl $parent): TableBuilder
    {
        $table = new TableBuilder($parent);

        return $table;
    }
}
