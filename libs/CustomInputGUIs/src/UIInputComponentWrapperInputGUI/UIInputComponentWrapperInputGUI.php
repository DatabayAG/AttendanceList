<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\UIInputComponentWrapperInputGUI;

use ilFormException;
use ilFormPropertyGUI;
use ILIAS\DI\Container;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Component\Input\PostDataFromServerRequest;
use ilTableFilterItem;
use ilTemplate;
use ilToolbarItem;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\src\Utils\PluginVersionParameter;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;



use Throwable;

class UIInputComponentWrapperInputGUI extends ilFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    /**
     * @var bool
     */
    protected static $init = false;
    /**
     * @var Input
     */
    protected $input;
    private Container $dic;



    public function __construct(Input $input, string $post_var = "")
    {
        global $DIC;
        $this->dic = $DIC;
        $this->input = $input;

        $this->setPostVar($post_var);

        //parent::__construct($title, $post_var);

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

            $DIC->ui()->mainTemplate()->addCss($version_parameter->appendToUrl($dir . "/css/UIInputComponentWrapperInputGUI.css"));
        }
    }



    public function checkInput(): bool
    {
        try {
            $this->input = $this->input->withInput(new PostDataFromServerRequest($this->dic->http()->request()));

            return (!$this->input->getContent()->isError());
        } catch (Throwable $ex) {
            return false;
        }
    }



    public function getAlert(): string
    {
        return $this->input->getError();
    }


    /**
     * @throws ilFormException
     */
    public function getDisabled(): bool
    {
        return $this->input->isDisabled();
    }



    public function getInfo(): string
    {
        return $this->input->getByline();
    }



    public function getInput(): Input
    {
        return $this->input;
    }



    public function setInput(Input $input): void
    {
        $this->input = $input;
    }



    public function getPostVar(): string
    {
        return $this->input->getName();
    }



    public function getRequired(): bool
    {
        return $this->input->isRequired();
    }



    public function getTableFilterHTML(): string
    {
        return $this->render();
    }



    public function getTitle(): string
    {
        return $this->input->getLabel();
    }



    public function getToolbarHTML(): string
    {
        return $this->render();
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->input->getValue();
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
        $tpl = new Template(__DIR__ . "/templates/input.html");

        $tpl->setVariable("INPUT", self::output()->getHTML($this->input));

        return $tpl->get();
    }



    public function setAlert(/*string*/ $error): void
    {
        $this->input = $this->input->withError($error);
    }


    /**
     * @throws ilFormException
     */
    public function setDisabled(/*bool*/ $disabled): void
    {
        $this->input = $this->input->withDisabled($disabled);
    }



    public function setInfo(/*string*/ $info): void
    {
        $this->input = $this->input->withByline($info);
    }



    public function setPostVar(/*string*/ $post_var): void
    {
        $this->input = $this->input->withNameFrom(new UIInputComponentWrapperNameSource($post_var));
    }



    public function setRequired(/*bool*/ $required): void
    {
        $this->input = $this->input->withRequired($required);
    }



    public function setTitle(/*string*/ $title): void
    {
        $this->input = $this->input->withLabel($title);
    }


    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        try {
            $this->input = $this->input->withValue($value);
        } catch (Throwable $ex) {

        }
    }


    /**
     * @param array $values
     */
    public function setValueByArray(/*array*/ $values): void
    {
        if (isset($values[$this->getPostVar()])) {
            $this->setValue($values[$this->getPostVar()]);
        }
    }
}
