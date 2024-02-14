<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\AttendanceList\Libs\CustomInputGUIs\src\Utils;

use ilPlugin;
use ilUtil;
use JsonException;

class PluginVersionParameter
{
    protected ?ilPlugin $plugin = null;


    private function __construct()
    {

    }


    public static function getInstance(): self
    {
        return new self();
    }


    /**
     * @throws JsonException
     */
    public function appendToUrl(string $prod_url, ?string $dev_url = null): string
    {
        if (!empty($dev_url) && $this->isDevMode()) {
            $url = $dev_url;
        } else {
            $url = $prod_url;
        }

        if ($this->plugin === null) {
            return $url;
        }

        $params = [
            "version" => $this->plugin->getVersion()
        ];

        $hash = hash("sha256", base64_encode(json_encode($params, JSON_THROW_ON_ERROR)));

        return ilUtil::appendUrlParameterString($url, "plugin_version=" . $hash);
    }

    public function getPlugin(): ilPlugin
    {
        return $this->plugin;
    }

    public function withPlugin(ilPlugin $plugin): self
    {
        $this->plugin = $plugin;

        return $this;
    }


    protected function isDevMode(): bool
    {
        return (defined("DEVMODE") && DEVMODE === 1);
    }
}
