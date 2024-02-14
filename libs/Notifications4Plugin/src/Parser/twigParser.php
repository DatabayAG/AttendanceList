<?php

namespace srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Parser;

use srag\Plugins\AttendanceList\Libs\Notifications4Plugin\Notification\NotificationsCtrl;
use Twig_Environment;
use Twig_Error;
use Twig_Loader_String;

class twigParser extends AbstractParser
{
    public const DOC_LINK = "https://twig.symfony.com/doc/1.x/templates.html";
    public const NAME = "twig";



    public function __construct()
    {
        parent::__construct();
    }



    public function getOptionsFields(): array
    {
        return [
            "autoescape" => $this->dic
                ->ui()
                ->factory()
                ->input()
                ->field()
                ->checkbox(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape"))
                ->withByline(nl2br(implode("\n", [
                    sprintf(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_1"), "|raw"),
                    sprintf(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_2"), "|e"),
                    "<b>" . self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_3") . "</b>",
                    self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_4")
                ]), false))
        ];
    }


    /**
     * @throws Twig_Error
     */
    public function parse(string $text, array $placeholders = [], array $options = []): string
    {
        $loader = new Twig_Loader_String();

        $twig = new Twig_Environment($loader, [
            "autoescape" => boolval($options["autoescape"])
        ]);

        return $this->fixLineBreaks($twig->render($text, $placeholders));
    }
}
