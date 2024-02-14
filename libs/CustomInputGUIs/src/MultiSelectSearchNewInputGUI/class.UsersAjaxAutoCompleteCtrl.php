<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiSelectSearchNewInputGUI;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use ilDBConstants;
use ILIAS\DI\Container;
use ilObjUser;

class UsersAjaxAutoCompleteCtrl extends AbstractAjaxAutoCompleteCtrl
{
    private Container $dic;


    public function __construct(?array $skip_ids = null)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($skip_ids);
    }


    public function fillOptions(array $ids): array
    {
        return $this->formatUsers($this->dic->database()->fetchAll($this->dic->database()->queryF('
SELECT usr_id, firstname, lastname, login
FROM usr_data
WHERE active=1
AND usr_id!=%s
AND ' . $this->dic
                ->database()
                ->in("usr_id", $ids, false, ilDBConstants::T_INTEGER), [ilDBConstants::T_INTEGER], [ANONYMOUS_USER_ID])));
    }


    public function searchOptions(?string $search = null): array
    {
        return $this->formatUsers(ilObjUser::searchUsers($search));
    }


    protected function formatUsers(array $users): array
    {
        $formatted_users = [];

        foreach ($users as $user) {
            $formatted_users[$user["usr_id"]] = $user["firstname"] . " " . $user["lastname"] . " (" . $user["login"] . ")";
        }

        return $this->skipIds($formatted_users);
    }
}
