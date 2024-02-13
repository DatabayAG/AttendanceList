<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\Items;

use ilDateTime;
use ilFormPropertyGUI;
use ilFormSectionHeaderGUI;
use ILIAS\UI\Implementation\Component\Input\Input;
use ilNumberInputGUI;
use ilPropertyFormGUI;
use ilRadioOption;
use ilRepositorySelector2InputGUI;
use ilUtil;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\HiddenInputGUI\HiddenInputGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\Exception\PropertyFormGUIException;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\PropertyFormGUI\PropertyFormGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\TableGUI\TableGUI;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\UIInputComponentWrapperInputGUI\UIInputComponentWrapperInputGUI;
use srag\Plugins\AttendanceList\Libs\DIC\DICTrait;
use srag\Plugins\AttendanceList\Libs\DIC\Plugin\PluginInterface;
use srag\Plugins\AttendanceList\Libs\DIC\Version\PluginVersionParameter;
use TypeError;

/**
 *
 *
 *
 * @access  namespace
 */
final class Items
{
    /**
     * @var bool
     */
    protected static $init = false;


    /**
     * Items constructor
     */
    private function __construct()
    {
    }


    /**
     * @param string                              $key
     * @param ilPropertyFormGUI|ilFormPropertyGUI $parent_item
     * @param PropertyFormGUI|TableGUI            $parent
     *
     * @return ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption
     *
     * @deprecated
     */
    final public static function getItem($key, array $field, $parent_item, $parent)
    {
        /**
         * @var ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption $item
         */
        if ($field[PropertyFormGUI::PROPERTY_CLASS] instanceof Input) {
            $item = new UIInputComponentWrapperInputGUI($field[PropertyFormGUI::PROPERTY_CLASS], $key);

            if (empty($item->getTitle())) {
                if (!$field["setTitle"]) {
                    $item->setTitle($parent->txt($key));
                }
            }

            if (empty($item->getInfo())) {
                if (!$field["setInfo"]) {
                    $item->setInfo($parent->txt($key . "_info", ""));
                }
            }
        } else {
            if (!class_exists($field[PropertyFormGUI::PROPERTY_CLASS])) {
                throw new PropertyFormGUIException("Class " . $field[PropertyFormGUI::PROPERTY_CLASS]
                    . " not exists!", PropertyFormGUIException::CODE_INVALID_PROPERTY_CLASS);
            }

            if ($field[PropertyFormGUI::PROPERTY_CLASS] === ilRepositorySelector2InputGUI::class) {
                $item = new $field[PropertyFormGUI::PROPERTY_CLASS]("", $key, false, get_class($parent));
            } else {
                $item = new $field[PropertyFormGUI::PROPERTY_CLASS]();
            }

            if ($item instanceof ilFormSectionHeaderGUI) {
                if (!$field["setTitle"]) {
                    $item->setTitle($parent->txt($key));
                }
            } else {
                if ($item instanceof ilRadioOption) {
                    if (!$field["setTitle"]) {
                        $item->setTitle($parent->txt($parent_item->getPostVar() . "_" . $key));
                    }

                    $item->setValue($key);
                } else {
                    if (!$field["setTitle"]) {
                        $item->setTitle($parent->txt($key));
                    }

                    $item->setPostVar($key);
                }
            }

            if (!$field["setInfo"]) {
                $item->setInfo($parent->txt($key . "_info", ""));
            }
        }

        self::setPropertiesToItem($item, $field);

        if ($item instanceof ilFormPropertyGUI) {
            if (isset($field[PropertyFormGUI::PROPERTY_VALUE])) {
                $value = $field[PropertyFormGUI::PROPERTY_VALUE];

                Items::setValueToItem($item, $value);
            }
        }

        return $item;
    }


    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption $item
     *
     * @return mixed
     *
     * @deprecated
     */
    public static function getValueFromItem($item)
    {
        if (method_exists($item, "getChecked")) {
            return boolval($item->getChecked());
        }

        if (method_exists($item, "getDate")) {
            return $item->getDate();
        }

        if (method_exists($item, "getImage")) {
            return $item->getImage();
        }

        if (method_exists($item, "getValue") && !($item instanceof ilRadioOption)) {
            if ($item->getMulti()) {
                return $item->getMultiValues();
            } else {
                $value = $item->getValue();

                if ($item instanceof ilNumberInputGUI) {
                    $value = floatval($value);
                } else {
                    if (empty($value) && !is_array($value)) {
                        $value = "";
                    }
                }

                return $value;
            }
        }

        return null;
    }


    /**
     * @param object $object
     *
     * @return mixed
     */
    public static function getter(/*object*/ $object, string $property)
    {
        if (method_exists($object, $method = "get" . self::strToCamelCase($property))) {
            return $object->{$method}();
        }

        if (method_exists($object, $method = "is" . self::strToCamelCase($property))) {
            return $object->{$method}();
        }

        return null;
    }



    public static function init(/*?*/ PluginInterface $plugin = null): void
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

            $DIC->ui()->mainTemplate()->addCss($version_parameter->appendToUrl($dir . "/css/input_gui_input.css"));
        }
    }


    /**
     * @param ilFormPropertyGUI[] $inputs
     *
     */
    public static function renderInputs(array $inputs): string
    {
        global $DIC;
        self::init(); // TODO: Pass $plugin

        $input_tpl = new Template(__DIR__ . "/templates/input_gui_input.html");

        $input_tpl->setCurrentBlock("input");

        foreach ($inputs as $input) {
            if ($input instanceof HiddenInputGUI) {
                $input_tpl->setVariableEscaped("HIDDEN", " hidden");
            }

            $input_tpl->setVariableEscaped("TITLE", $input->getTitle());

            if ($input->getRequired()) {
                $input_tpl->setVariable("REQUIRED", (new Template(__DIR__ . "/templates/input_gui_input_required.html", true, false))->get());
            }

            $input_html = str_replace('<div class="help-block"></div>', "", $input->render());
            $input_tpl->setVariable("INPUT", $input_html);

            if ($input->getInfo()) {
                $input_info_tpl = new Template(__DIR__ . "/templates/input_gui_input_info.html");

                $input_info_tpl->setVariableEscaped("INFO", $input->getInfo());

                $input_tpl->setVariable("INFO", $input_info_tpl->get());
            }

            if ($input->getAlert()) {
                $input_alert_tpl = new Template(__DIR__ . "/templates/input_gui_input_alert.html");
                $input_alert_tpl->setVariable(
                    "IMG_SRC",
                    ilUtil::getImagePath("standard/icon_alert.svg")
                );
                $input_alert_tpl->setVariable(
                    "IMG_ALT",
                    $DIC->language()->txt("alert")
                );
                $input_alert_tpl->setVariableEscaped("TXT", $input->getAlert());
                $input_tpl->setVariable("ALERT", $input_alert_tpl->get());
            }

            $input_tpl->parseCurrentBlock();
        }

        return $input_tpl->get();
    }


    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption $item
     * @param mixed                                                  $value
     *
     * @deprecated
     */
    public static function setValueToItem($item, $value): void
    {
        if ($item instanceof MultiLineInputGUI) {
            $item->setValueByArray([
                $item->getPostVar() => $value
            ]);

            return;
        }

        if (method_exists($item, "setChecked")) {
            $item->setChecked($value);

            return;
        }

        if (method_exists($item, "setDate")) {
            if (is_string($value)) {
                $value = new ilDateTime($value, IL_CAL_DATE);
            }

            $item->setDate($value);

            return;
        }

        if (method_exists($item, "setImage")) {
            $item->setImage($value);

            return;
        }

        if (method_exists($item, "setValue") && !($item instanceof ilRadioOption)) {
            $item->setValue($value);
        }
    }


    /**
     * @param object $object
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function setter(/*object*/ $object, string $property, $value)
    {
        $res = null;

        if (method_exists($object, $method = "with" . self::strToCamelCase($property)) || method_exists($object, $method = "set" . self::strToCamelCase($property))) {
            try {
                $res = $object->{$method}($value);
            } catch (TypeError $ex) {
                try {
                    $res = $object->{$method}(intval($value));
                } catch (TypeError $ex) {
                    $res = $object->{$method}(boolval($value));
                }
            }
        }

        return $res;
    }



    public static function strToCamelCase(string $string): string
    {
        return str_replace("_", "", ucwords($string, "_"));
    }


    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption $item
     *
     * @deprecated
     */
    private static function setPropertiesToItem($item, array $properties): void
    {
        foreach ($properties as $property_key => $property_value) {
            $property = "";

            switch ($property_key) {
                case PropertyFormGUI::PROPERTY_DISABLED:
                    $property = "setDisabled";
                    break;

                case PropertyFormGUI::PROPERTY_MULTI:
                    $property = "setMulti";
                    break;

                case PropertyFormGUI::PROPERTY_OPTIONS:
                    $property = "setOptions";
                    $property_value = [$property_value];
                    break;

                case PropertyFormGUI::PROPERTY_REQUIRED:
                    $property = "setRequired";
                    break;

                case PropertyFormGUI::PROPERTY_CLASS:
                case PropertyFormGUI::PROPERTY_NOT_ADD:
                case PropertyFormGUI::PROPERTY_SUBITEMS:
                case PropertyFormGUI::PROPERTY_VALUE:
                    break;

                default:
                    $property = $property_key;
                    break;
            }

            if (!empty($property)) {
                if (!is_array($property_value)) {
                    $property_value = [$property_value];
                }

                if (method_exists($item, $property)) {
                    call_user_func_array([$item, $property], $property_value);
                } else {
                    if ($item instanceof ilRepositorySelector2InputGUI) {
                        if (method_exists($item->getExplorerGUI(), $property)) {
                            call_user_func_array([$item->getExplorerGUI(), $property], $property_value);
                        } else {
                            throw new PropertyFormGUIException("Class " . get_class($item)
                                . " has no method " . $property . "!", PropertyFormGUIException::CODE_INVALID_FIELD);
                        }
                    } else {
                        throw new PropertyFormGUIException("Class " . get_class($item)
                            . " has no method " . $property . "!", PropertyFormGUIException::CODE_INVALID_FIELD);
                    }
                }
            }
        }
    }
}
