<?php

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Loader;

use Closure;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Implementation\Render\Loader;
use ILIAS\UI\Implementation\Render\RendererFactory;
use ILIAS\UI\Renderer;
use Pimple\Container;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent\InputGUIWrapperUIInputComponent;
use srag\Plugins\AttendanceList\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent\Renderer as InputGUIWrapperUIInputComponentRenderer;
use ILIAS\Data\Factory;

/**
 * Class CustomInputGUIsLoaderDetector
 *
 * @package srag\Plugins\AttendanceList\Libs\CustomInputGUIs\Loader
 */
class CustomInputGUIsLoaderDetector extends AbstractLoaderDetector
{
    /**
     * @var bool
     */
    protected static $has_fix_ctrl_namespace_current_url = false;
    /**
     * @var callable[]|null
     */
    protected $get_renderer_for_hooks;
    private Container $dic;


    /**
     *
     *
     * @param callable[]|null $get_renderer_for_hooks
     */
    public function __construct(Loader $loader, /*?*/ array $get_renderer_for_hooks = null)
    {
        parent::__construct($loader);
        global $DIC;
        $this->dic = $DIC;

        $this->get_renderer_for_hooks = $get_renderer_for_hooks;
    }


    /**
     * @param callable[]|null $get_renderer_for_hooks
     *
     */
    public static function exchangeUIRendererAfterInitialization(/*?*/ array $get_renderer_for_hooks = null): callable
    {
        global $DIC;
        self::fixCtrlNamespaceCurrentUrl();

        $previous_renderer = Closure::bind(function (): callable {
            return $this->raw("ui.renderer");
        }, $DIC, Container::class)();

        return function () use ($DIC, $previous_renderer, $get_renderer_for_hooks): Renderer {
            $previous_renderer = $previous_renderer($DIC);

            if ($previous_renderer instanceof DefaultRenderer) {
                $previous_renderer_loader = Closure::bind(function (): Loader {
                    return $this->component_renderer_loader;
                }, $previous_renderer, DefaultRenderer::class)();
            } else {
                $previous_renderer_loader = null; // TODO:
            }

            return new DefaultRenderer(new self($previous_renderer_loader, $get_renderer_for_hooks));
        };
    }



    private static function fixCtrlNamespaceCurrentUrl(): void
    {
        if (!self::$has_fix_ctrl_namespace_current_url) {
            self::$has_fix_ctrl_namespace_current_url = true;

            // Fix language select meta bar which current ctrl gui has namespaces (public page)
            $_SERVER["REQUEST_URI"] = str_replace("\\", "%5C", $_SERVER["REQUEST_URI"]);
        }
    }



    public function getRendererFor(Component $component, array $contexts): ComponentRenderer
    {
        $renderer = null;

        if (!empty($this->get_renderer_for_hooks)) {
            foreach ($this->get_renderer_for_hooks as $get_renderer_for_hook) {
                $renderer = $get_renderer_for_hook($component, $contexts);
                if ($renderer !== null) {
                    break;
                }
            }
        }

        if ($renderer === null) {
            if ($component instanceof InputGUIWrapperUIInputComponent) {
                $renderer = new InputGUIWrapperUIInputComponentRenderer(
                    $this->dic->ui()->factory(),
                    $this->dic["ui.template_factory"],
                    $this->dic->language(),
                    $this->dic["ui.javascript_binding"],
                    $this->dic->refinery(),
                    $this->dic["ui.pathresolver"],
                    new \ILIAS\Data\Factory(),
                    $this->dic["help.text_retriever"],
                    $this->dic["ui.upload_limit_resolver"]
                );
            } else {
                $renderer = parent::getRendererFor($component, $contexts);
            }
        }

        return $renderer;
    }
}
