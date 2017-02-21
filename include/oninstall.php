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
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          luciorota <lucio.rota@gmail.com>
 *
 * @param $xoopsModule
 *
 * @return bool
 */

/**
 * @param  XoopsModule $xoopsModule
 * @return bool
 */
function xoops_module_pre_install_publisher(XoopsModule $xoopsModule)
{
    if (!isset($moduleDirName)) {
        $moduleDirName = basename(dirname(__DIR__));
    }
    $classUtility = ucfirst($moduleDirName) . 'Utility';
    if (!class_exists($classUtility)) {
        xoops_load('utility', $moduleDirName);
    }
    //check for minimum XOOPS version
    if (!$classUtility::checkVerXoops($xoopsModule)) {
        return false;
    }

    // check for minimum PHP version
    if (!$classUtility::checkVerPhp($xoopsModule)) {
        return false;
    }

    $modTables =& $xoopsModule->getInfo('tables');
    foreach ($modTables as $table) {
        $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
    }

    return true;
}

/**
 * @param $xoopsModule
 *
 * @return bool|string
 */
function xoops_module_install_publisher(XoopsModule $xoopsModule)
{
    include_once dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
    if (!isset($moduleDirName)) {
        $moduleDirName = basename(dirname(__DIR__));
    }

    xoops_loadLanguage('admin', $moduleDirName);
    xoops_loadLanguage('modinfo', $moduleDirName);

//    $moduleDirName = $xoopsModule->getVar('dirname');
    include_once $GLOBALS['xoops']->path('modules/' . $moduleDirName . '/include/config.php');

    foreach (array_keys($uploadFolders) as $i) {
        PublisherUtility::createFolder($uploadFolders[$i]);
    }

    $file = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
    foreach (array_keys($blankFiles) as $i) {
        $dest = $blankFiles[$i] . '/blank.png';
        PublisherUtility::copyFile($file, $dest);
    }

    return true;
}
