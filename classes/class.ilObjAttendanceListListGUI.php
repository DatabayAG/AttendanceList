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

require_once __DIR__ . '/../vendor/autoload.php';

class ilObjAttendanceListListGUI extends ilObjectPluginListGUI
{
    private bool $payment_enabled;

    public function getGuiClass(): string
    {
        return ilObjAttendanceListGUI::class;
    }

    public function initCommands(): array
    {
        // Always set
        $this->timings_enabled = false;
        $this->subscribe_enabled = true;
        $this->payment_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->delete_enabled = true;
        $this->notes_enabled = true;
        $this->comments_enabled = true;

        // Should be overwritten according to status
        $this->cut_enabled = false;
        $this->copy_enabled = true;

        $commands = [
            [
                'permission' => 'read',
                'cmd' => ilObjAttendanceListGUI::CMD_STANDARD,
                'default' => true,
            ],
            [
                'permission' => 'write',
                'cmd' => ilObjAttendanceListGUI::CMD_EDIT_SETTINGS,
                'lang_var' => 'edit'
            ]
        ];

        return $commands;
    }

    /**
     * @return    array        array of property arrays:
     *                        'alert' (boolean) => display as an alert property (usually in red)
     *                        'property' (string) => property name
     *                        'value' (string) => property value
     */
    public function getCustomProperties(array $a_prop = []): array
    {

        $props = parent::getCustomProperties($a_prop);

        try {
            /** @var xaliSetting $settings */
            $settings = xaliSetting::find($this->obj_id);
            if ($settings !== null) {
                if ($settings->getActivation()) {
                    $activation_from = date('d. M Y', strtotime($settings->getActivationFrom()));
                    $activation_to = date('d. M Y', strtotime($settings->getActivationTo()));
                    $props[] = [
                        'alert' => false,
                        'newline' => true,
                        'property' => $this->lng->txt('activation'),
                        'value' => $activation_from . ' - ' . $activation_to,
                        'propertyNameVisible' => true
                    ];
                }
                if (!$settings->getIsOnline()) {
                    $props[] = [
                        'alert' => true,
                        'newline' => true,
                        'property' => 'Status',
                        'value' => 'Offline',
                        'propertyNameVisible' => true
                    ];
                }
            }
        } catch (Exception $e) {

        }

        return $props;
    }

    public function getAlertProperties(): array
    {
        $alert = [];
        foreach ((array) $this->getCustomProperties() as $prop) {
            if ($prop['alert']) {
                $alert[] = $prop;
            }
        }

        return $alert;
    }

    public function initType(): void
    {
        $this->setType(ilAttendanceListPlugin::PLUGIN_ID);
    }
}
