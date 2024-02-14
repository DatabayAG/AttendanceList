<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiSelectSearchNewInputGUI;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use ilDBConstants;
use ILIAS\DI\Container;

class ObjectsAjaxAutoCompleteCtrl extends AbstractAjaxAutoCompleteCtrl
{
    /**
     * @var bool
     */
    protected $ref_id;
    /**
     * @var string
     */
    protected $type;
    private Container $dic;


    /**
     */
    public function __construct(string $type, bool $ref_id = false, /*?*/ array $skip_ids = null)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($skip_ids);

        $this->type = $type;
        $this->ref_id = $ref_id;
    }



    public function fillOptions(array $ids): array
    {
        $result = $this->dic->database()->queryF('
SELECT ' . ($this->ref_id ? 'object_reference.ref_id' : 'object_data.obj_id') . ', title
FROM object_data
INNER JOIN object_reference ON object_data.obj_id=object_reference.obj_id
WHERE type=%s
AND object_reference.deleted IS NULL
AND ' . $this->dic
                ->database()
                ->in(($this->ref_id ? 'object_reference.ref_id' : 'object_data.obj_id'), $ids, false, ilDBConstants::T_INTEGER) . ' ORDER BY title ASC', [ilDBConstants::T_TEXT], [$this->type]);

        return $this->formatObjects($this->dic->database()->fetchAll($result));
    }



    public function searchOptions(?string $search = null): array
    {
        $result = $this->dic->database()->queryF('
SELECT ' . ($this->ref_id ? 'object_reference.ref_id' : 'object_data.obj_id') . ', title
FROM object_data
INNER JOIN object_reference ON object_data.obj_id=object_reference.obj_id
WHERE type=%s
AND object_reference.deleted IS NULL
' . (!empty($search) ? ' AND ' . $this->dic
                    ->database()
                    ->like("title", ilDBConstants::T_TEXT, '%%' . $search . '%%') : '') . ' ORDER BY title ASC', [ilDBConstants::T_TEXT], [$this->type]);

        return $this->formatObjects($this->dic->database()->fetchAll($result));
    }



    protected function formatObjects(array $objects): array
    {
        $formatted_objects = [];

        foreach ($objects as $object) {
            $formatted_objects[$object[($this->ref_id ? 'ref_id' : 'obj_id')]] = $object["title"];
        }

        return $this->skipIds($formatted_objects);
    }
}
