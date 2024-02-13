<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Waiter;

use ilGlobalTemplateInterface;
use ilTemplate;
use srag\Plugins\AttendanceList\Libs\DIC\DICTrait;
use srag\Plugins\AttendanceList\Libs\DIC\Plugin\PluginInterface;
use srag\Plugins\AttendanceList\Libs\DIC\Version\PluginVersionParameter;

/**
 * Class Waiter
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Waiter
 */
final class Waiter
{
    /**
     * @var string
     */
    public const TYPE_PERCENTAGE = "percentage";
    /**
     * @var string
     */
    public const TYPE_WAITER = "waiter";
    /**
     * @var bool
     */
    protected static $init = false;


    /**
     * Waiter constructor
     */
    private function __construct()
    {
    }


    /**
     * @param ilTemplate|ilGlobalTemplateInterface|null $tpl
     */
    final public static function init(string $type, /*?ilGlobalTemplateInterface*/ $tpl = null, /*?*/ PluginInterface $plugin = null): void
    {
        global $DIC;
        $tpl = $tpl ?? $DIC->ui()->mainTemplate();

        if (self::$init === false) {
            self::$init = true;

            $version_parameter = PluginVersionParameter::getInstance();
            if ($plugin !== null) {
                $version_parameter = $version_parameter->withPlugin($plugin);
            }

            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

            $tpl->addCss($version_parameter->appendToUrl($dir . "/css/waiter.css"));

            $tpl->addJavaScript($version_parameter->appendToUrl($dir . "/js/waiter.min.js", $dir . "/js/waiter.js"));
        }

        $tpl->addOnLoadCode('il.waiter.init("' . $type . '");');
    }
}
