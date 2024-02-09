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

use JetBrains\PhpStorm\NoReturn;

class xaliAbsenceStatementGUI extends xaliGUI
{
    public const CMD_UPDATE = 'update';
    public const CMD_STANDARD = 'show';
    public const CMD_DOWNLOAD_FILE = 'downloadFile';

    protected function show(): void
    {
        $entry_id = $_GET['entry_id'];
        if (!$entry_id) {
            $entry_id = xaliChecklistEntry::where(
                [
                    'checklist_id' => $_GET['checklist_id'],
                    'user_id' => $_GET['user_id']]
            )->first()->getId();
        }
        /** @var xaliAbsenceStatement $absence */
        $absence = xaliAbsenceStatement::findOrGetInstance($entry_id);
        $xaliAbsenceFormGUI = new xaliAbsenceStatementFormGUI($this, $absence);
        $xaliAbsenceFormGUI->fillForm();
        $this->tpl->setContent($xaliAbsenceFormGUI->getHTML());
    }

    protected function update(): void
    {
        /** @var xaliAbsenceStatement $absence */
        $absence = xaliAbsenceStatement::findOrGetInstance($_GET['entry_id']);
        $xaliAbsenceFormGUI = new xaliAbsenceStatementFormGUI($this, $absence);
        $xaliAbsenceFormGUI->setValuesByPost();
        if ($xaliAbsenceFormGUI->saveForm()) {
            $user_id = xaliChecklistEntry::find($_GET['entry_id'])->getUserId();
            xaliUserStatus::updateUserStatus($user_id, $this->parent_gui->getObject()->getId());
            $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_saved"), true);

            $this->cancel();
        }
        $this->tpl->setContent($xaliAbsenceFormGUI->getHTML());
    }

    #[NoReturn] protected function downloadFile(): void
    {
        $file_id = $_GET['file_id'];
        $fileObj = new ilObjFile($file_id, false);
        $fileObj->sendFile();
        exit;
    }

    protected function cancel(): void
    {
        if ($back_cmd = $_GET['back_cmd']) {
            $this->ctrl->setParameterByClass(xaliOverviewGUI::class, 'entry_id', $_GET['entry_id']);
            $this->ctrl->redirectByClass(xaliOverviewGUI::class, $back_cmd);
        }
        $this->ctrl->returnToParent($this);
    }
}
