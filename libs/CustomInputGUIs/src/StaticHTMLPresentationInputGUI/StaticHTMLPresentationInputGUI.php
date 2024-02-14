<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\StaticHTMLPresentationInputGUI;

use ilFormException;
use ilFormPropertyGUI;
use ilTemplate;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Template\Template;

class StaticHTMLPresentationInputGUI extends ilFormPropertyGUI
{
    /**
     * @var string
     */
    protected $html = "";


    /**
     * StaticHTMLPresentationInputGUI constructor
     *
     */
    public function __construct(string $title = "")
    {
        parent::__construct($title, "");
    }



    public function checkInput(): bool
    {
        return true;
    }



    public function getHtml(): string
    {
        return $this->html;
    }



    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }



    public function getValue(): string
    {
        return "";
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
        $iframe_tpl = new Template(__DIR__ . "/templates/iframe.html");

        $iframe_tpl->setVariableEscaped("URL", $this->getDataUrl());

        return self::output()->getHTML($iframe_tpl);
    }


    /**
     * @param string $title
     *
     */
    public function setTitle(/*string*/ $title): self
    {
        $this->title = $title;

        return $this;
    }


    /**
     * @param string $value
     *
     * @throws ilFormException
     */
    public function setValue(/*string*/ $value): void
    {
        //throw new ilFormException("StaticHTMLPresentationInputGUI does not support set screenshots!");
    }


    /**
     * @param array $values
     *
     * @throws ilFormException
     */
    public function setValueByArray(/*array*/ $values): void
    {
        //throw new ilFormException("StaticHTMLPresentationInputGUI does not support set screenshots!");
    }



    protected function getDataUrl(): string
    {
        return "data:text/html;charset=UTF-8;base64," . base64_encode($this->html);
    }
}
