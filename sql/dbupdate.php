<#1>
<?php
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList/vendor/autoload.php';
xaliChecklist::updateDB();
xaliChecklistEntry::updateDB();
xaliSetting::updateDB();
xaliUserStatus::updateDB();
?>
<#2>
<?php
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList/vendor/autoload.php';
foreach (xaliChecklistEntry::where(['status' => xaliChecklistEntry::STATUS_ABSENT_EXCUSED])->get() as $entry) {
    $entry->setStatus(xaliChecklistEntry::STATUS_ABSENT_UNEXCUSED);
    $entry->update();
}
?>
<#3>
<?php
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList/vendor/autoload.php';
xaliConfig::updateDB();
xaliAbsenceReason::updateDB();
xaliLastReminder::updateDB();
xaliAbsenceStatement::updateDB();
?>
<#4>
<?php
\srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()->installTables();

if (\srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()
        ->migrateFromOldGlobalPlugin(xaliChecklistEntry::NOTIFICATION_NAME) === null) {

    $notification = \srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()
        ->factory()->newInstance();

    $notification->setName(xaliChecklistEntry::NOTIFICATION_NAME);
    $notification->setTitle("Absence");
    $notification->setDescription("Mail which will be sent directly after a user has been defined as absent");

    $notification->setSubject("Absence", "default");
    $notification->setText("Hello {{user.getFirstname}} {{user.getLastname}},

        You were absent in one of your courses:

        {{absence}}

        Please click on the link and specify a reason for your absence.", "default");

    \srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()
        ->storeNotification($notification);
}

if (\srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()
        ->migrateFromOldGlobalPlugin(\srag\Plugins\AttendanceList\Cron\AttendanceListJob::NOTIFICATION_NAME) === null) {

    $notification = \srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()
        ->factory()->newInstance();

    $notification->setName(\srag\Plugins\AttendanceList\Cron\AttendanceListJob::NOTIFICATION_NAME);
    $notification->setTitle("Absence Reminder");
    $notification->setDescription("Reminder email listing all open absence reasons");

    $notification->setSubject("Reminder: reasons for absence still open", "default");
    $notification->setText("Hello {{user.getFirstname}} {{user.getLastname}},

        You haven't yet specified the reason for your absence in the following courses:

        {{open_absences}}

        Please click on the link(s) and specify a reason for your absence.", "default");

    \srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()
        ->storeNotification($notification);
}

?>
<#5>
<?php
\srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\Repository::getInstance()->installTables();
?>
<#6>
<?php
global $ilDB;
if ($ilDB->tableExists('xali_data') && $ilDB->tableColumnExists('xali_data', 'activation')) {
    $ilDB->manipulateF(
        "UPDATE xali_data SET activation = %s WHERE activation IS NULL",
        ['integer'],
        [0]
    );
}
?>
<#7>
<?php
if ($ilDB->tableExists('xali_checklist') && $ilDB->tableColumnExists('xali_checklist', 'last_edited_by')) {
    $ilDB->manipulateF(
        "UPDATE xali_checklist SET last_edited_by = %s WHERE last_edited_by IS NULL",
        ['integer'],
        [0]
    );
}
?>
<#8>
<?php
$columns_to_update = [
        'xali_absence_statement' => [
                'reason_id' => [ilDBConstants::T_INTEGER, 0],
                'comment_text' => [ilDBConstants::T_TEXT, ''],
                'file_id' => [ilDBConstants::T_INTEGER, 0]
        ],
        'xali_checklist' => [
                'obj_id' => [ilDBConstants::T_INTEGER, 0],
                'checklist_date' => [ilDBConstants::T_DATE, ''],
                'last_update' => [ilDBConstants::T_INTEGER, 0]
        ],
        'xali_entry' => [
                'checklist_id' => [ilDBConstants::T_INTEGER, 0],
                'user_id' => [ilDBConstants::T_INTEGER, 0],
                'status' => [ilDBConstants::T_INTEGER, 0]
        ],
        'xali_absence_reasons' => [
                'title' => [ilDBConstants::T_TEXT, ''],
                'info' => [ilDBConstants::T_TEXT, ''],
                'has_comment' => [ilDBConstants::T_INTEGER, 0],
                'comment_req' => [ilDBConstants::T_INTEGER, 0],
                'has_upload' => [ilDBConstants::T_INTEGER, 0],
                'upload_req' => [ilDBConstants::T_INTEGER, 0]
            ],
        'xali_config' => [
                'value' => [ilDBConstants::T_TEXT, '']
        ],
        'xali_last_reminder' => [
                'last_reminder' => [ilDBConstants::T_DATE, '']
        ],
        'xali_data' => [
                'is_online' => [ilDBConstants::T_INTEGER, 0],
                'minumum_attendance' => [ilDBConstants::T_INTEGER, 80],
                'activation_from' => [ilDBConstants::T_DATE, ''],
                'activation_to' => [ilDBConstants::T_DATE, ''],
                'activation_weekdays' => [ilDBConstants::T_TEXT, '']
        ],
        'xali_user_status' => [
                'attendancelist_id' => [ilDBConstants::T_INTEGER, 0],
                'user_id' => [ilDBConstants::T_INTEGER, 0],
                'created_at' => [ilDBConstants::T_TIMESTAMP, ''],
                'updated_at' => [ilDBConstants::T_TIMESTAMP, ''],
                'created_user_id' => [ilDBConstants::T_INTEGER, 0],
                'updated_user_id' => [ilDBConstants::T_INTEGER, 0],
                'status' => [ilDBConstants::T_INTEGER, 0]
        ]

];

foreach ($columns_to_update as $table => $columns) {
    foreach ($columns as $column => $definition) {
        if ($ilDB->tableExists($table) && $ilDB->tableColumnExists($table, $column)) {
            $ilDB->manipulateF(
                "UPDATE $table SET $column = %s WHERE $column IS NULL",
                [$definition[0]],
                [$definition[1]]
            );
        }
    }
}
?>
