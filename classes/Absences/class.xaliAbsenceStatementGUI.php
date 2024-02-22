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

use JetBrains\PhpStorm\NoReturn;

class xaliAbsenceStatementGUI extends xaliGUI
{
    public const CMD_UPDATE = 'update';
    public const CMD_STANDARD = 'show';
    public const CMD_DOWNLOAD_FILE = 'downloadFile';

    protected function show(): void
    {
        $entry_id = $this->httpWrapper->query()->retrieve(
            "entry_id",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );
        if (!$entry_id) {
            $checklistId = $this->httpWrapper->query()->retrieve(
                "checklist_id",
                $this->refinery->kindlyTo()->int()
            );

            $userId = $this->httpWrapper->query()->retrieve(
                "user_id",
                $this->refinery->kindlyTo()->int()
            );

            $entry_id = xaliChecklistEntry::where(
                [
                    'checklist_id' => $checklistId,
                    'user_id' => $userId]
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

        $entryId = $this->httpWrapper->query()->retrieve(
            "entry_id",
            $this->refinery->kindlyTo()->int()
        );

        $absence = xaliAbsenceStatement::findOrGetInstance($entryId);
        $xaliAbsenceFormGUI = new xaliAbsenceStatementFormGUI($this, $absence);
        $xaliAbsenceFormGUI->setValuesByPost();
        if ($xaliAbsenceFormGUI->saveForm()) {
            $user_id = xaliChecklistEntry::find($entryId)->getUserId();
            xaliUserStatus::updateUserStatus($user_id, $this->parent_gui->getObject()->getId());
            $this->tpl->setOnScreenMessage('success', $this->pl->txt("msg_saved"), true);

            $this->cancel();
        }
        $this->tpl->setContent($xaliAbsenceFormGUI->getHTML());
    }

    #[NoReturn] protected function downloadFile(): void
    {
        $file_id = $this->httpWrapper->query()->retrieve(
            "file_id",
            $this->refinery->kindlyTo()->int()
        );
        $fileObj = new ilObjFile($file_id, false);
        $fileObj->sendFile();
        exit;
    }

    protected function cancel(): void
    {
        $back_cmd = $this->httpWrapper->query()->retrieve(
            "back_cmd",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null),
            ])
        );
        if ($back_cmd) {
            $entryId = $this->httpWrapper->query()->retrieve(
                "entry_id",
                $this->refinery->kindlyTo()->int()
            );

            $this->ctrl->setParameterByClass(xaliOverviewGUI::class, 'entry_id', $entryId);
            $this->ctrl->redirectByClass(xaliOverviewGUI::class, $back_cmd);
        }

        $refId = $this->httpWrapper->query()->retrieve(
            "ref_id",
            $this->refinery->kindlyTo()->int()
        );

        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->tree->checkForParentType($refId, "crs"));
        $this->ctrl->redirectByClass(ilRepositoryGUI::class, $back_cmd);
    }
}
