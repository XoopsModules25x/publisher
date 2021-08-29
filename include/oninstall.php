<?php

declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          luciorota <lucio.rota@gmail.com>
 */

use XoopsModules\Publisher\{
    Common\Configurator,
    Helper,
    Utility
};

/** @var Helper $helper */
/** @var Utility $utility */
/** @var Common\Configurator $configurator */

/**
 * @param \XoopsModule $module
 * @return bool
 */
function xoops_module_pre_install_publisher(\XoopsModule $module)
{
    require \dirname(__DIR__) . '/preloads/autoloader.php';
    $utility = new Utility();

    //check for minimum XOOPS version
    $xoopsSuccess = $utility::checkVerXoops($module);

    // check for minimum PHP version
    $phpSuccess = $utility::checkVerPhp($module);

    if ($xoopsSuccess && $phpSuccess) {
        $moduleTables = &$module->getInfo('tables');
        foreach ($moduleTables as $table) {
            $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
        }
    }

    return $xoopsSuccess && $phpSuccess;
}

/**
 * @param \XoopsModule $module
 * @return bool|string
 */
function xoops_module_install_publisher(\XoopsModule $module)
{
    require \dirname(__DIR__) . '/preloads/autoloader.php';

    $helper       = Helper::getInstance();
    $utility      = new Utility();
    $configurator = new Configurator();

    // Load language files
    $helper->loadLanguage('admin');
    $helper->loadLanguage('modinfo');

    //  ---  CREATE FOLDERS ---------------
    if ($configurator->uploadFolders && is_array($configurator->uploadFolders)) {
        //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
        foreach (array_keys($configurator->uploadFolders) as $i) {
            $utility::createFolder($configurator->uploadFolders[$i]);
        }
    }

    //  ---  COPY blank.png FILES ---------------
    if ($configurator->copyBlankFiles && is_array($configurator->copyBlankFiles)) {
        $file = \dirname(__DIR__) . '/assets/images/blank.png';
        foreach (array_keys($configurator->copyBlankFiles) as $i) {
            $dest = $configurator->copyBlankFiles[$i] . '/blank.png';
            $utility::copyFile($file, $dest);
        }
    }

    //  ---  COPY test folder files ---------------
    if ($configurator->copyTestFolders && is_array($configurator->copyTestFolders)) {
        //        $file =  \dirname(__DIR__) . '/testdata/images/';
        foreach (array_keys($configurator->copyTestFolders) as $i) {
            $src  = $configurator->copyTestFolders[$i][0];
            $dest = $configurator->copyTestFolders[$i][1];
            $utility::rcopy($src, $dest);
        }
    }

    //delete .html entries from the tpl table
    $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('tplfile') . " WHERE `tpl_module` = '" . $module->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
    $GLOBALS['xoopsDB']->queryF($sql);

    return true;
}
