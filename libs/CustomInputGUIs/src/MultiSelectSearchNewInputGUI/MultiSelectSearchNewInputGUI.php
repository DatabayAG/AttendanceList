<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\MultiSelectSearchNewInputGUI;

use ilFormPropertyGUI;
use ILIAS\DI\Container;
use ilTableFilterItem;
use ilTemplate;
use ilToolbarItem;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\src\Utils\PluginVersionParameter;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;

class MultiSelectSearchNewInputGUI extends ilFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    public const EMPTY_PLACEHOLDER = "__empty_placeholder__";
    /**
     * @var bool
     */
    protected static $init = false;
    /**
     * @var AbstractAjaxAutoCompleteCtrl|null
     */
    protected $ajax_auto_complete_ctrl = null;
    /**
     * @var int|null
     */
    protected $limit_count = null;
    /**
     * @var int|null
     */
    protected $minimum_input_length = null;
    /**
     * @var array
     */
    protected $options = [];
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



    public static function cleanValues(array $values): array
    {
        return array_values(array_filter($values, function ($value): bool {
            return ($value !== self::EMPTY_PLACEHOLDER);
        }));
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

            $DIC->ui()->mainTemplate()->addCss($version_parameter->appendToUrl($dir . "/../../node_modules/select2/dist/css/select2.min.css"));

            $DIC->ui()->mainTemplate()->addCss($version_parameter->appendToUrl($dir . "/css/multi_select_search_new_input_gui.css"));

            $DIC->ui()->mainTemplate()->addJavaScript($version_parameter->appendToUrl($dir . "/../../node_modules/select2/dist/js/select2.full.min.js"));

            $DIC->ui()->mainTemplate()->addJavaScript($version_parameter->appendToUrl($dir . "/../../node_modules/select2/dist/js/i18n/" . $DIC->user()->getCurrentLanguage()
                . ".js"));
        }
    }


    /**
     * @param mixed  $value
     */
    public function addOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }



    public function checkInput(): bool
    {
        $values = $_POST[$this->getPostVar()];
        if (!is_array($values)) {
            $values = [];
        }

        $values = self::cleanValues($values);

        if ($this->getRequired() && empty($values)) {
            $this->setAlert($this->dic->language()->txt("msg_input_is_required"));

            return false;
        }

        if ($this->getLimitCount() !== null && count($values) > $this->getLimitCount()) {
            $this->setAlert($this->dic->language()->txt("form_input_not_valid"));

            return false;
        }

        if ($this->getAjaxAutoCompleteCtrl() !== null) {
            if (!$this->getAjaxAutoCompleteCtrl()->validateOptions($values)) {
                $this->setAlert($this->dic->language()->txt("form_input_not_valid"));

                return false;
            }
        } else {
            foreach ($values as $key => $value) {
                if (!isset($this->getOptions()[$value])) {
                    $this->setAlert($this->dic->language()->txt("form_input_not_valid"));

                    return false;
                }
            }
        }

        return true;
    }



    public function getAjaxAutoCompleteCtrl(): ?AbstractAjaxAutoCompleteCtrl
    {
        return $this->ajax_auto_complete_ctrl;
    }



    public function setAjaxAutoCompleteCtrl(/*?*/ AbstractAjaxAutoCompleteCtrl $ajax_auto_complete_ctrl = null): void
    {
        $this->ajax_auto_complete_ctrl = $ajax_auto_complete_ctrl;
    }



    public function getLimitCount(): ?int
    {
        return $this->limit_count;
    }



    public function setLimitCount(/*?*/ int $limit_count = null): void
    {
        $this->limit_count = $limit_count;
    }



    public function getMinimumInputLength(): int
    {
        if ($this->minimum_input_length !== null) {
            return $this->minimum_input_length;
        } else {
            return ($this->getAjaxAutoCompleteCtrl() !== null ? 3 : 0);
        }
    }



    public function setMinimumInputLength(/*?*/ int $minimum_input_length = null): void
    {
        $this->minimum_input_length = $minimum_input_length;
    }



    public function getOptions(): array
    {
        return $this->options;
    }



    public function setOptions(array $options): void
    {
        $this->options = $options;
    }



    public function getTableFilterHTML(): string
    {
        return $this->render();
    }



    public function getToolbarHTML(): string
    {
        return $this->render();
    }



    public function getValue(): array
    {
        return $this->value;
    }


    /**
     * @param array $value
     */
    public function setValue(/*array*/ $value): void
    {
        if (is_array($value)) {
            $this->value = self::cleanValues($value);
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



    public function render(): string
    {
        $tpl = new Template(__DIR__ . "/templates/multi_select_search_new_input_gui.html");

        $tpl->setVariableEscaped("ID", $this->getFieldId());

        $tpl->setVariableEscaped("POST_VAR", $this->getPostVar());

        $tpl->setVariableEscaped("EMPTY_PLACEHOLDER", self::EMPTY_PLACEHOLDER); // ILIAS 6 will not set `null` value to input on post

        $config = [
            "maximumSelectionLength" => $this->getLimitCount(),
            "minimumInputLength" => $this->getMinimumInputLength()
        ];
        if ($this->getAjaxAutoCompleteCtrl() !== null) {
            $config["ajax"] = [
                "delay" => 500,
                "url" => $this->dic->ctrl()->getLinkTarget($this->getAjaxAutoCompleteCtrl(), AbstractAjaxAutoCompleteCtrl::CMD_AJAX_AUTO_COMPLETE, "", true, false)
            ];

            $options = $this->getAjaxAutoCompleteCtrl()->fillOptions($this->getValue());
        } else {
            $options = $this->getOptions();
        }

        $tpl->setVariableEscaped("CONFIG", base64_encode(json_encode($config)));

        if (!empty($options)) {

            $tpl->setCurrentBlock("option");

            foreach ($options as $option_value => $option_text) {
                $selected = in_array($option_value, $this->getValue());

                if ($selected) {
                    $tpl->setVariableEscaped("SELECTED", "selected");
                }

                $tpl->setVariableEscaped("VAL", $option_value);
                $tpl->setVariableEscaped("TEXT", $option_text);

                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }


    /**
     * @param array $values
     */
    public function setValueByArray(/*array*/ $values): void
    {
        $this->setValue($values[$this->getPostVar()]);
    }
}
