<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

declare(strict_types=1);


/**
 * Class xaliSettingsGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xaliSettingsGUI extends xaliGUI {

	public const CMD_STANDARD = 'showContent';
	public const CMD_SAVE = 'save';

    /**
     * @throws ilCtrlException
     */
    public function showContent(): void
    {
		$xaliSettingsFormGUI = new xaliSettingsFormGUI($this, $this->parent_gui->getObject());
		$this->tpl->setContent($xaliSettingsFormGUI->getHTML());
	}

	public function save(): void
    {
		$xaliSettingsFormGUI = new xaliSettingsFormGUI($this, $this->parent_gui->getObject());
		$xaliSettingsFormGUI->setValuesByPost();
		if ($xaliSettingsFormGUI->saveSettings()) {

			// update LP
			xaliUserStatus::updateUserStatuses($this->parent_gui->getObject()->getId());

            $this->tpl->setOnScreenMessage('success',  $this->lng->txt("saved_successfully"), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
			return;
		}
		$this->tpl->setContent($xaliSettingsFormGUI->getHTML());
	}
}