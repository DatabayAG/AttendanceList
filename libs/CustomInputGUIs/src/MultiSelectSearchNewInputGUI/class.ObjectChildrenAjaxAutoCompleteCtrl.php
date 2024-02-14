<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiSelectSearchNewInputGUI;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use ILIAS\DI\Container;
use ilObjOrgUnit;

class ObjectChildrenAjaxAutoCompleteCtrl extends ObjectsAjaxAutoCompleteCtrl
{
    /**
     * @var int
     */
    protected $parent_ref_id;
    private Container $dic;


    /**
     */
    public function __construct(string $type, /*?*/ int $parent_ref_id = null, /*?*/ array $skip_ids = null)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($type, ($type === "orgu"), $skip_ids);

        $this->parent_ref_id = $parent_ref_id ?? ($type === "orgu" ? ilObjOrgUnit::getRootOrgRefId() : 1);
    }



    public function searchOptions(/*?*/ string $search = null): array
    {
        $org_units = [];

        foreach (
            array_filter($this->dic->repositoryTree()->getSubTree($this->dic->repositoryTree()->getNodeData($this->parent_ref_id)), function (array $item) use ($search): bool {
                return (stripos($item["title"], $search) !== false);
            }) as $item
        ) {
            $org_units[$item["child"]] = $item["title"];
        }

        return $this->skipIds($org_units);
    }
}
