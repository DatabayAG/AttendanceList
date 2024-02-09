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

require_once __DIR__ . '/../vendor/autoload.php';

class ilObjAttendanceListAccess extends ilObjectPluginAccess
{
    public function _checkAccess(string $a_cmd, string $a_permission, int $a_ref_id, int $a_obj_id, ?int $a_user_id = null): bool
    {
        global $DIC;
        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();
        if ($a_user_id == '') {
            $a_user_id = $ilUser->getId();
        }
        if ($a_obj_id === null) {
            $a_obj_id = ilObject2::_lookupObjId($a_ref_id);
        }

        switch ($a_permission) {
            case 'read':
            case 'visible':
                if ((!ilObjAttendanceListAccess::checkOnline($a_obj_id) or !ilObjAttendanceListAccess::checkActivation($a_obj_id))
                    and !$ilAccess->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)) {
                    return false;
                }
                break;
        }

        return true;
    }

    public function checkAccess(string $a_permission, string $a_cmd, int $a_ref_id, string $a_type = "", ?int $a_obj_id = null, ?int $a_tree_id = null): bool
    {
        return $this->access->checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id, $a_tree_id);
    }

    public static function checkOnline(int $a_id): bool
    {
        /**
         * @var $xaliSettings xaliSetting
         */
        $xaliSettings = xaliSetting::findOrGetInstance($a_id);

        return (bool) $xaliSettings->getIsOnline();
    }

    public static function checkActivation($a_id): bool
    {
        /** @var xaliSetting $settings */
        $settings = xaliSetting::find($a_id);
        $today = date('Y-m-d');

        return !$settings->getActivation() || (($today >= $settings->getActivationFrom()) && ($today <= $settings->getActivationTo()));
    }

    public static function hasReadAccess(?int $ref_id = null, ?int $user_id = null): bool
    {
        return self::hasAccess('read', $ref_id, $user_id);
    }

    public static function hasWriteAccess(?int $ref_id = null, ?int $user_id = null): bool
    {
        return self::hasAccess('write', $ref_id, $user_id);
    }

    protected static function hasAccess(string $permission, ?int $ref_id = null, ?int $user_id = null): bool
    {
        global $DIC;
        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();
        $ilLog = $DIC->logger()->root();
        $ref_id = $ref_id ?: (int) $_GET['ref_id'];
        $user_id = $user_id ?: $ilUser->getId();

        return $ilAccess->checkAccessOfUser($user_id, $permission, '', $ref_id);
    }

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);
        if (count($t_arr) === 3) { // access to absence statement -> access will be checked later
            return true;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }
}
