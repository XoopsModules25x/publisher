<?php declare(strict_types=1);

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 * uninstall.php - cleanup on module uninstall
 *
 * @author          XOOPS Module Development Team
 * @copyright       {@link https://xoops.org 2001-2016 XOOPS Project}
 * @license         {@link GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)}
 * @link            https://xoops.org XOOPS
 */

use XoopsModules\Publisher\{
    Helper,
    Utility
};
/** @var Helper $helper */

/**
 * Prepares system prior to attempting to uninstall module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if ready to uninstall, false if not
 */
function xoops_module_pre_uninstall_publisher(\XoopsModule $module): bool
{
    // Do some synchronization if needed
    return true;
}

/**
 * Performs tasks required during uninstallation of the module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if uninstallation successful, false if not
 */
function xoops_module_uninstall_publisher(\XoopsModule $module): bool
{
    //clean Cache
    Utility::cleanCache();

    // Rename uploads folder to BAK and add date to name
    $success = Utility::renameUploadFolder();

    return $success;
}
