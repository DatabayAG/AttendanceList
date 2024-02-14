<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\TableGUI;

use ilCSVWriter;
use ilExcel;
use ilFormPropertyGUI;
use ilHtmlToPdfTransformerFactory;
use ILIAS\DI\Container;
use ilTable2GUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiLineNewInputGUI\MultiLineNewInputGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\PropertyFormGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\TableGUI\Exception\TableGUIException;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;

/**
 *
 *
 *
 * @deprecated Please use "srag/datatable" library (`AbstractTableBuilder`)
 */
abstract class TableGUI extends ilTable2GUI
{
    /**
     * @var int
     *
     * @deprecated
     */
    public const DEFAULT_FORMAT = 0;
    /**
     * @var int
     *
     * @deprecated
     */
    public const EXPORT_PDF = 3;
    /**
     * @var string
     *
     * @deprecated
     */
    public const LANG_MODULE = "";
    /**
     * @var string
     *
     * @abstract
     *
     * @deprecated
     */
    public const ROW_TEMPLATE = "";
    /**
     * @var array
     *
     * @deprecated
     */
    protected $filter_fields = [];
    /**
     * @var Template
     *
     * @deprecated
     */
    protected $tpl;
    /**
     * @var ilFormPropertyGUI[]
     *
     * @deprecated
     */
    private $filter_cache = [];
    private Container $dic;


    /**
     * TableGUI constructor
     *
     * @param object $parent
     *
     * @deprecated
     */
    public function __construct(/*object*/ $parent, string $parent_cmd)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->parent_obj = $parent;
        $this->parent_cmd = $parent_cmd;

        $this->initId();

        parent::__construct($parent, $parent_cmd);

        $this->initTable();
    }


    /**
     *
     *
     * @param int  $format
     * @param bool $send
     *
     * @deprecated
     */
    public function exportData(/*int*/ $format, /*bool*/ $send = false): void
    {
        switch ($format) {
            case self::EXPORT_PDF:
                $this->exportPDF($format);
                break;

            default:
                parent::exportData($format, $send);
                break;
        }
    }


    /**
     *
     *
     * @deprecated
     */
    public function fillFooter(): void
    {
        parent::fillFooter();
    }


    /**
     *
     *
     * @deprecated
     */
    public function fillHeader(): void
    {
        parent::fillHeader();
    }


    /**
     *
     *
     *
     * @deprecated
     */
    final public function getSelectableColumns(): array
    {
        return array_map(function (array &$column): array {
            if (!isset($column["txt"])) {
                $column["txt"] = $this->txt($column["id"]);
            }

            return $column;
        }, $this->getSelectableColumns2());
    }


    /**
     *
     *
     * @throws TableGUIException $filters needs to be an array!
     * @throws TableGUIException $field needs to be an array!
     *
     * @deprecated
     */
    final public function initFilter(): void
    {
        $this->setDisableFilterHiding(true);

        $this->initFilterFields();

        if (!is_array($this->filter_fields)) {
            throw new TableGUIException("\$filters needs to be an array!", TableGUIException::CODE_INVALID_FIELD);
        }

        foreach ($this->filter_fields as $key => $field) {
            if (!is_array($field)) {
                throw new TableGUIException("\$field needs to be an array!", TableGUIException::CODE_INVALID_FIELD);
            }

            if ($field[PropertyFormGUI::PROPERTY_NOT_ADD]) {
                continue;
            }

            $item = Items::getItem($key, $field, $this, $this);

            /*if (!($item instanceof ilTableFilterItem)) {
                throw new TableGUIException("\$item must be an instance of ilTableFilterItem!", TableGUIException::CODE_INVALID_FIELD);
            }*/

            if ($item instanceof MultiLineNewInputGUI) {
                if (is_array($field[PropertyFormGUI::PROPERTY_SUBITEMS])) {
                    foreach ($field[PropertyFormGUI::PROPERTY_SUBITEMS] as $child_key => $child_field) {
                        if (!is_array($child_field)) {
                            throw new TableGUIException("\$fields needs to be an array!", TableGUIException::CODE_INVALID_FIELD);
                        }

                        if ($child_field[PropertyFormGUI::PROPERTY_NOT_ADD]) {
                            continue;
                        }

                        $child_item = Items::getItem($child_key, $child_field, $item, $this);

                        $item->addInput($child_item);
                    }
                }
            }

            $this->filter_cache[$key] = $item;

            $this->addFilterItem($item);

            if ($this->hasSessionValue($item->getFieldId())) { // Supports filter default values
                $item->readFromSession();
            }
        }
    }


    /**
     *
     *
     * @param string $col
     *
     *
     * @deprecated
     */
    public function isColumnSelected(/*string*/ $col): bool
    {
        return parent::isColumnSelected($col);
    }


    /**
     *
     *
     *
     * @deprecated
     */
    public function setExportFormats(array $formats): void
    {
        parent::setExportFormats($formats);

        $valid = [self::EXPORT_PDF => "pdf"];

        foreach ($formats as $format) {
            if (isset($valid[$format])) {
                $this->export_formats[$format] = self::plugin()->getPluginObject()->getPrefix() . "_tablegui_export_" . $valid[$format];
            }
        }
    }


    /**
     *
     *
     * @deprecated
     */
    public function txt(string $key, /*?*/ string $default = null): string
    {
        if ($default !== null) {
            return self::plugin()->translate($key, static::LANG_MODULE, [], true, "", $default);
        } else {
            return self::plugin()->translate($key, static::LANG_MODULE);
        }
    }


    /**
     *
     * @deprecated
     */
    protected function exportPDF(bool $send = false): void
    {

        $css = file_get_contents(__DIR__ . "/css/table_pdf_export.css");

        $tpl = new Template(__DIR__ . "/templates/table_pdf_export.html");

        $tpl->setVariable("CSS", $css);

        $tpl->setCurrentBlock("header");
        foreach ($this->fillHeaderPDF() as $column) {
            $tpl->setVariable("HEADER", $column);

            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("body");
        foreach ($this->row_data as $row) {
            $tpl_row = new Template(__DIR__ . "/templates/table_pdf_export_row.html");

            $tpl_row->setCurrentBlock("row");

            foreach ($this->fillRowPDF($row) as $column) {
                $tpl_row->setVariable("COLUMN", $column);

                $tpl_row->parseCurrentBlock();
            }

            $tpl->setVariable("ROW", self::output()->getHTML($tpl_row));

            $tpl->parseCurrentBlock();
        }

        $html = $tpl->get();

        $a = new ilHtmlToPdfTransformerFactory();
        $a->deliverPDFFromHTMLString($html, "export.pdf", $send ? ilHtmlToPdfTransformerFactory::PDF_OUTPUT_DOWNLOAD : ilHtmlToPdfTransformerFactory::PDF_OUTPUT_FILE, static::PLUGIN_CLASS_NAME, "");
    }


    /**
     *
     *
     * @param ilCSVWriter $csv
     *
     * @deprecated
     */
    protected function fillHeaderCSV(/*ilCSVWriter*/ $csv): void
    {
        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $csv->addColumn($column["txt"]);
            }
        }

        $csv->addRow();
    }


    /**
     *
     *
     * @param int     $row
     *
     * @deprecated
     */
    protected function fillHeaderExcel(ilExcel $excel, /*int*/ &$row): void
    {
        $col = 0;

        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $excel->setCell($row, $col, $column["txt"]);
                $col++;
            }
        }

        if ($col > 0) {
            $excel->setBold("A" . $row . ":" . $excel->getColumnCoord($col - 1) . $row);
        }
    }


    /**
     *
     * @deprecated
     */
    protected function fillHeaderPDF(): array
    {
        $columns = [];

        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $columns[] = $column["txt"];
            }
        }

        return $columns;
    }


    /**
     *
     *
     * @param array|object $row
     *
     * @deprecated
     */
    protected function fillRow(/*array*/ $row): void
    {
        $this->tpl->setCurrentBlock("column");

        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $column = $this->getColumnValue($column["id"], $row);

                if (!empty($column)) {
                    $this->tpl->setVariable("COLUMN", $column);
                } else {
                    $this->tpl->setVariable("COLUMN", " ");
                }

                $this->tpl->parseCurrentBlock();
            }
        }
    }


    /**
     *
     *
     * @param ilCSVWriter  $csv
     * @param array|object $row
     *
     * @deprecated
     */
    protected function fillRowCSV(/*ilCSVWriter*/ $csv, /*array*/ $row): void
    {
        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $csv->addColumn($this->getColumnValue($column["id"], $row, self::EXPORT_CSV));
            }
        }

        $csv->addRow();
    }


    /**
     *
     *
     * @param int          $row
     * @param array|object $result
     *
     * @deprecated
     */
    protected function fillRowExcel(ilExcel $excel, /*int*/ &$row, /*array*/ $result): void
    {
        $col = 0;
        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $excel->setCell($row, $col, $this->getColumnValue($column["id"], $result, self::EXPORT_EXCEL));
                $col++;
            }
        }
    }


    /**
     * @param array $row
     *
     *
     * @deprecated
     */
    protected function fillRowPDF(/*array*/ $row): array
    {
        $strings = [];

        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $strings[] = $this->getColumnValue($column["id"], $row, self::EXPORT_PDF);
            }
        }

        return $strings;
    }


    /**
     * @param array|object $row
     *
     *
     * @deprecated
     */
    abstract protected function getColumnValue(string $column, /*array*/ $row, int $format = self::DEFAULT_FORMAT): string;


    /**
     *
     * @deprecated
     */
    final protected function getFilterValues(): array
    {
        return array_map(function ($item) {
            return Items::getValueFromItem($item);
        }, $this->filter_cache);
    }


    /**
     *
     * @deprecated
     */
    abstract protected function getSelectableColumns2(): array;


    /**
     *
     *
     * @deprecated
     */
    final protected function hasSessionValue(string $field_id): bool
    {
        // Not set (null) on first visit, false on reset filter, string if is set
        return (isset($_SESSION["form_" . $this->getId()][$field_id]) && $_SESSION["form_" . $this->getId()][$field_id] !== false);
    }


    /**
     * @deprecated
     */
    protected function initAction(): void
    {
        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_obj));
    }


    /**
     * @deprecated
     */
    protected function initColumns(): void
    {
        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $this->addColumn($column["txt"], ($column["sort"] ? $column["id"] : null));
            }
        }
    }


    /**
     * @deprecated
     */
    protected function initCommands(): void
    {

    }


    /**
     * @deprecated
     */
    abstract protected function initData(): void ;


    /**
     * @deprecated
     */
    protected function initExport(): void
    {

    }


    /**
     * @deprecated
     */
    abstract protected function initFilterFields(): void ;


    /**
     * @deprecated
     */
    abstract protected function initId(): void ;


    /**
     * @deprecated
     */
    abstract protected function initTitle(): void ;


    /**
     *
     * @deprecated
     */
    private function checkRowTemplateConst(): bool
    {
        return (defined("static::ROW_TEMPLATE") && !empty(static::ROW_TEMPLATE));
    }


    /**
     * @deprecated
     */
    private function initRowTemplate(): void
    {
        if ($this->checkRowTemplateConst()) {
            $this->setRowTemplate(static::ROW_TEMPLATE, self::plugin()->directory());
        } else {
            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);
            $this->setRowTemplate("table_row.html", $dir);
        }
    }


    /**
     * @deprecated
     */
    private function initTable(): void
    {
        if (!(strpos($this->parent_cmd, "applyFilter") === 0
            || strpos($this->parent_cmd, "resetFilter") === 0)
        ) {
            $this->tpl = new Template($this->tpl->lastTemplatefile, $this->tpl->removeUnknownVariables, $this->tpl->removeEmptyBlocks);

            $this->initAction();

            $this->initTitle();

            $this->initFilter();

            $this->initData();

            $this->initColumns();

            $this->initExport();

            $this->initRowTemplate();

            $this->initCommands();
        } else {
            // Speed up, not init data on applyFilter or resetFilter, only filter
            $this->initFilter();
        }
    }
}
