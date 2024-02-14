<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiSelectSearchNewInputGUI;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use ilOrgUnitPathStorage;

class OrgUnitAjaxAutoCompleteCtrl extends AbstractAjaxAutoCompleteCtrl
{

    public function __construct(?array $skip_ids = null)
    {
        parent::__construct($skip_ids);
    }



    public function fillOptions(array $ids): array
    {
        if (!empty($ids)) {
            return $this->skipIds(ilOrgUnitPathStorage::where([
                "ref_id" => $ids
            ])->getArray("ref_id", "path"));
        } else {
            return [];
        }
    }



    public function searchOptions(?string $search = null): array
    {
        if (!empty($search)) {
            $where = ilOrgUnitPathStorage::where([
                "path" => "%" . $search . "%"
            ], "LIKE");
        } else {
            $where = ilOrgUnitPathStorage::where([]);
        }

        return $this->skipIds($where->orderBy("path")->getArray("ref_id", "path"));
    }
}
