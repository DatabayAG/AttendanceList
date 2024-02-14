<?php

namespace srag\Plugins\AttendanceList\Cron;

use ilAttendanceListPlugin;
use ilCronJob;
use ilCronJobResult;
use ilCtrl;
use ilDBInterface;
use ILIAS;
use ILIAS\Cron\Schedule\CronJobScheduleType;
use ilLogger;
use ilMail;
use ilObject;
use ilObjUser;
use ilRbacReview;
use xaliAbsenceStatement;
use xaliChecklist;
use xaliChecklistEntry;
use xaliConfig;
use xaliLastReminder;
use xaliSetting;
use xaliUserStatus;

/**
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class AttendanceListJob extends ilCronJob
{
    public const CRON_JOB_ID = ilAttendanceListPlugin::PLUGIN_ID;
    public const PLUGIN_CLASS_NAME = ilAttendanceListPlugin::class;


    /**
     * AttendanceListJob constructor
     */
    public function __construct()
    {

    }



    public function getId(): string
    {
        return self::CRON_JOB_ID;
    }



    public function hasAutoActivation(): bool
    {
        return true;
    }



    public function hasFlexibleSchedule(): bool
    {
        return true;
    }



    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }



    public function getDefaultScheduleValue()/* : ?int*/
    {
        return 1;
    }



    public function getTitle(): string
    {
        return ilAttendanceListPlugin::PLUGIN_NAME . ": " . self::plugin()->translate("cron_title");
    }



    public function getDescription(): string
    {
        return self::plugin()->translate("cron_description");
    }



    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();

        $cron = new xaliCron();
        $cron->run();

        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
