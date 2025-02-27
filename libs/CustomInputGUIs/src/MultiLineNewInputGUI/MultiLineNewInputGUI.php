<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiLineNewInputGUI;

use ilFormPropertyGUI;
use ILIAS\DI\Container;
use ilTableFilterItem;
use ilTemplate;
use ilToolbarItem;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\src\Utils\PluginVersionParameter;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;

class MultiLineNewInputGUI extends ilFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    public const SHOW_INPUT_LABEL_ALWAYS = 3;
    public const SHOW_INPUT_LABEL_NONE = 1;
    public const SHOW_INPUT_LABEL_ONCE = 2;
    /**
     * @var int
     */
    protected static $counter = 0;
    /**
     * @var bool
     */
    protected static $init = false;
    /**
     * @var ilFormPropertyGUI[]
     */
    protected $inputs = [];
    /**
     * @var ilFormPropertyGUI[]|null
     */
    protected $inputs_generated = null;
    /**
     * @var int
     */
    protected $show_input_label = self::SHOW_INPUT_LABEL_ONCE;
    /**
     * @var bool
     */
    protected $show_sort = true;
    /**
     * @var array
     */
    protected $value = [];

    private Container $dic;


    public function __construct(string $title = "", string $post_var = "")
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($title, $post_var);

        self::init(); // TODO: Pass $plugin
    }


    public static function init(?ilPlugin $plugin = null): void
    {
        if (self::$init === false) {
            global $DIC;
            self::$init = true;

            $version_parameter = PluginVersionParameter::getInstance();
            if ($plugin !== null) {
                $version_parameter = $version_parameter->withPlugin($plugin);
            }

            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

            $DIC->ui()->mainTemplate()->addCss($version_parameter->appendToUrl($dir . "/css/multi_line_new_input_gui.css"));

            $DIC->ui()->mainTemplate()->addJavaScript($version_parameter->appendToUrl($dir . "/js/multi_line_new_input_gui.min.js", $dir . "/js/multi_line_new_input_gui.js"));
        }
    }


    public function addInput(ilFormPropertyGUI $input): void
    {
        $this->inputs[] = $input;
        $this->inputs_generated = null;
    }


    public function checkInput(): bool
    {
        $ok = true;

        foreach ($this->getInputs($this->getRequired()) as $i => $inputs) {
            foreach ($inputs as $org_post_var => $input) {
                $b_value = $_POST[$input->getPostVar()];

                $_POST[$input->getPostVar()] = $_POST[$this->getPostVar()][$i][$org_post_var];

                /*if ($this->getRequired()) {
                   $input->setRequired(true);
               }*/

                if (!$input->checkInput()) {
                    $ok = false;
                }

                $_POST[$input->getPostVar()] = $b_value;
            }
        }

        $this->inputs_generated = null;

        if ($ok) {
            return true;
        } else {
            //$this->setAlert($this->dic->language()->txt("form_input_not_valid"));

            return false;
        }
    }


    /**
     * @return ilFormPropertyGUI[][]
     */
    public function getInputs(bool $need_one_line_at_least = true): array
    {
        if ($this->inputs_generated === null) {
            $this->inputs_generated = [];

            foreach (array_values($this->getValue($need_one_line_at_least)) as $i => $value) {
                $inputs = [];

                foreach ($this->inputs as $input) {
                    $input = clone $input;

                    $org_post_var = $input->getPostVar();

                    Items::setValueToItem($input, $value[$org_post_var]);

                    $post_var = $this->getPostVar() . "[" . $i . "][";
                    if (strpos($org_post_var, "[") !== false) {
                        $post_var .= strstr($input->getPostVar(), "[", true) . "][" . strstr($org_post_var, "[");
                    } else {
                        $post_var .= $org_post_var . "]";
                    }
                    $input->setPostVar($post_var);

                    $inputs[$org_post_var] = $input;
                }

                $this->inputs_generated[] = $inputs;
            }
        }

        return $this->inputs_generated;
    }


    /**
     * @param ilFormPropertyGUI[] $inputs
     */
    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
        $this->inputs_generated = null;
    }


    public function getShowInputLabel(): int
    {
        return $this->show_input_label;
    }


    public function setShowInputLabel(int $show_input_label): void
    {
        $this->show_input_label = $show_input_label;
    }


    public function getTableFilterHTML(): string
    {
        return $this->render();
    }


    public function getToolbarHTML(): string
    {
        return $this->render();
    }


    public function getValue(bool $need_one_line_at_least = false): array
    {
        $values = $this->value;

        if ($need_one_line_at_least && empty($values)) {
            $values = [[]];
        }

        return $values;
    }



    public function setValue(array $value): void
    {
        if (is_array($value)) {
            $this->value = $value;
        } else {
            $this->value = [];
        }
    }


    public function insert(ilTemplate $tpl): void
    {
        $html = $this->render();

        $tpl->setCurrentBlock("prop_generic");
        $tpl->setVariable("PROP_GENERIC", $html);
        $tpl->parseCurrentBlock();
    }


    public function isShowSort(): bool
    {
        return $this->show_sort;
    }


    public function setShowSort(bool $show_sort): void
    {
        $this->show_sort = $show_sort;
    }


    public function render(): string
    {
        $counter = ++self::$counter;

        $tpl = new Template(__DIR__ . "/templates/multi_line_new_input_gui.html");

        $tpl->setVariableEscaped("COUNTER", $counter);

        $remove_first_line = (!$this->getRequired() && empty($this->getValue(false)));
        $tpl->setVariableEscaped("REMOVE_FIRST_LINE", $remove_first_line);
        $tpl->setVariableEscaped("REQUIRED", $this->getRequired());
        $tpl->setVariableEscaped("SHOW_INPUT_LABEL", $this->getShowInputLabel());

        if (!$this->getRequired()) {
            $tpl->setCurrentBlock("add_first_line");

            if (!empty($this->getInputs())) {
                $tpl->setVariable("HIDE_ADD_FIRST_LINE", self::output()->getHTML(new Template(__DIR__ . "/templates/multi_line_new_input_gui_hide.html", false, false)));
            }

            $tpl->setVariable("ADD_FIRST_LINE", self::output()->getHTML($this->dic->ui()->factory()->symbol()->glyph()->add()->withAdditionalOnLoadCode(function (string $id) use ($counter): string {
                return 'il.MultiLineNewInputGUI.init(' . $counter . ', $("#' . $id . '").parent().parent().parent(), true)';
            })));

            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("line");

        foreach ($this->getInputs() as $i => $inputs) {
            if ($remove_first_line) {
                $tpl->setVariable("HIDE_LINE", self::output()->getHTML(new Template(__DIR__ . "/templates/multi_line_new_input_gui_hide.html", false, false)));
            }

            $tpl->setVariable("INPUTS", Items::renderInputs($inputs));

            if ($this->isShowSort()) {
                $sort_tpl = new Template(__DIR__ . "/templates/multi_line_new_input_gui_sort.html");

                $sort_tpl->setVariable("UP", self::output()->getHTML($this->dic->ui()->factory()->symbol()->glyph()->sortAscending()));
                if ($i === 0) {
                    $sort_tpl->setVariable("HIDE_UP", self::output()->getHTML(new Template(__DIR__ . "/templates/multi_line_new_input_gui_hide.html", false, false)));
                }

                $sort_tpl->setVariable("DOWN", self::output()->getHTML($this->dic->ui()->factory()->symbol()->glyph()->sortDescending()));
                if ($i === (count($this->getInputs()) - 1)) {
                    $sort_tpl->setVariable("HIDE_DOWN", self::output()->getHTML(new Template(__DIR__ . "/templates/multi_line_new_input_gui_hide.html", false, false)));
                }

                $tpl->setVariable("SORT", self::output()->getHTML($sort_tpl));
            }

            $tpl->setVariable("ADD", self::output()->getHTML($this->dic->ui()->factory()->symbol()->glyph()->add()->withAdditionalOnLoadCode(function (string $id) use ($i, $counter): string {
                return 'il.MultiLineNewInputGUI.init(' . $counter . ', $("#' . $id . '").parent().parent().parent())' . ($i === (count($this->getInputs()) - 1) ? ';il.MultiLineNewInputGUI.update('
                        . $counter . ', $("#'
                        . $id
                        . '").parent().parent().parent().parent())' : '');
            })));

            $tpl->setVariable("REMOVE", self::output()->getHTML($this->dic->ui()->factory()->symbol()->glyph()->remove()));
            if ($this->getRequired() && count($this->getInputs()) < 2) {
                $tpl->setVariable("HIDE_REMOVE", self::output()->getHTML(new Template(__DIR__ . "/templates/multi_line_new_input_gui_hide.html", false, false)));
            }

            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }



    public function setValueByArray(array $values): void
    {
        $this->setValue($values[$this->getPostVar()]);
    }
}
