<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiSelectSearchNewInputGUI;

use ILIAS\DI\Container;

abstract class AbstractAjaxAutoCompleteCtrl
{
    public const CMD_AJAX_AUTO_COMPLETE = "ajaxAutoComplete";
    /**
     * @var array|null
     */
    protected $skip_ids = null;
    private Container $dic;



    public function __construct(/*?*/ array $skip_ids = null)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->skip_ids = $skip_ids;
    }



    public function executeCommand(): void
    {
        $next_class = $this->dic->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            default:
                $cmd = $this->dic->ctrl()->getCmd();

                switch ($cmd) {
                    case self::CMD_AJAX_AUTO_COMPLETE:
                        $this->{$cmd}();
                        break;

                    default:
                        break;
                }
                break;
        }
    }



    abstract public function fillOptions(array $ids): array;



    abstract public function searchOptions(/*?*/ string $search = null): array;



    public function validateOptions(array $ids): bool
    {
        return (count($this->skipIds($ids)) === count($this->fillOptions($ids)));
    }



    protected function ajaxAutoComplete(): void
    {
        $search = strval(filter_input(INPUT_GET, "term"));

        $options = [];

        foreach ($this->searchOptions($search) as $id => $title) {
            $options[] = [
                "id" => $id,
                "text" => $title
            ];
        }

        self::output()->outputJSON(["results" => $options]);
    }



    protected function skipIds(array $ids): array
    {
        if (empty($this->skip_ids)) {
            return $ids;
        }

        return array_filter($ids, function ($id): bool {
            return (!in_array($id, $this->skip_ids));
        }, ARRAY_FILTER_USE_KEY);
    }
}
