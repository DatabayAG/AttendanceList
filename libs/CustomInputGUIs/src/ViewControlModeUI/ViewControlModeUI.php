<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\ViewControlModeUI;

use ILIAS\DI\Container;
use ilSession;

class ViewControlModeUI
{
    public const CMD_HANDLE_BUTTONS = "ViewControlModeUIHandleButtons";
    /**
     * @var array
     */
    protected $buttons = [];
    /**
     * @var string
     */
    protected $default_active_id = "";
    /**
     * @var string
     */
    protected $id = "";
    /**
     * @var string
     */
    protected $link = "";
    private Container $dic;



    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }



    public function getActiveId(): string
    {
        $active_id = ilSession::get(self::CMD_HANDLE_BUTTONS . "_" . $this->id);

        if ($active_id === null || !isset($this->buttons[$active_id])) {
            return $active_id = $this->default_active_id;
        }

        return $active_id;
    }



    public function handleButtons(): void
    {
        $active_id = filter_input(INPUT_GET, self::CMD_HANDLE_BUTTONS);

        ilSession::set(self::CMD_HANDLE_BUTTONS . "_" . $this->id, $active_id);

        $this->dic->ctrl()->redirectToURL(ilSession::get(self::CMD_HANDLE_BUTTONS . "_" . $this->id . "_url"));
    }



    public function render(): string
    {
        ilSession::set(self::CMD_HANDLE_BUTTONS . "_" . $this->id . "_url", $_SERVER["REQUEST_URI"]);

        $actions = [];

        foreach ($this->buttons as $id => $txt) {
            $actions[$txt] = $this->link . "&" . self::CMD_HANDLE_BUTTONS . "=" . $id;
        }

        return self::output()->getHTML($this->dic->ui()->factory()->viewControl()->mode($actions, "")
            ->withActive($this->buttons[$this->getActiveId()]));
    }



    public function withButtons(array $buttons): self
    {
        $this->buttons = $buttons;

        return $this;
    }



    public function withDefaultActiveId(string $default_active_id): self
    {
        $this->default_active_id = $default_active_id;

        return $this;
    }



    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }



    public function withLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
