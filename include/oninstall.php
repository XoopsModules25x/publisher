<?php
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

use XoopsModules\Publisher;
use XoopsModules\Publisher\Common;

/**
 * @param  \XoopsModule $module
 * @return bool
 */
function xoops_module_pre_install_publisher(\XoopsModule $module)
{
    include __DIR__ . '/../preloads/autoloader.php';
    /** @var Publisher\Utility $utility */
    $utility      = new Publisher\Utility();

    //check for minimum XOOPS version
    $xoopsSuccess = $utility::checkVerXoops($module);
    
    // check for minimum PHP version
    $phpSuccess   = $utility::checkVerPhp($module);

    if (false !== $xoopsSuccess && false !==  $phpSuccess) {
        $moduleTables =& $module->getInfo('tables');
        foreach ($moduleTables as $table) {
            $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
        }
    }
    return $xoopsSuccess && $phpSuccess;
}

/**
 * @param \XoopsModule $module
 *
 * @return bool|string
 */
function xoops_module_install_publisher(\XoopsModule $module)
{
    include __DIR__ . '/../preloads/autoloader.php';

    $moduleDirName = basename(dirname(__DIR__));

    
    /** @var Publisher\Helper $helper */
    /** @var Publisher\Utility $utility */
    /** @var Common\Configurator $configurator */
    $helper       = Publisher\Helper::getInstance();
    $utility      = new Publisher\Utility();
    $configurator = new Common\Configurator();

    // Load language files
    $helper->loadLanguage('admin');
    $helper->loadLanguage('modinfo');


    //  ---  CREATE FOLDERS ---------------
    if (count($configurator->uploadFolders) > 0) {
        //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
        foreach (array_keys($configurator->uploadFolders) as $i) {
            $utility::createFolder($configurator->uploadFolders[$i]);
        }
    }

    //  ---  COPY blank.png FILES ---------------
    if (count($configurator->copyBlankFiles) > 0) {
        $file = __DIR__ . '/../assets/images/blank.png';
        foreach (array_keys($configurator->copyBlankFiles) as $i) {
            $dest = $configurator->copyBlankFiles[$i] . '/blank.png';
            $utility::copyFile($file, $dest);
        }
    }


    //  ---  COPY test folder files ---------------
    if (count($configurator->copyTestFolders) > 0) {
        //        $file = __DIR__ . '/../testdata/images/';
        foreach (array_keys($configurator->copyTestFolders) as $i) {
            $src  = $configurator->copyTestFolders[$i][0];
            $dest = $configurator->copyTestFolders[$i][1];
            $utility::xcopy($src, $dest);
        }
    }


    //delete .html entries from the tpl table
    $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('tplfile') . " WHERE `tpl_module` = '" . $module->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
    $GLOBALS['xoopsDB']->queryF($sql);

    return true;
}
