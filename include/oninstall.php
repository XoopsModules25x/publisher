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
    $moduleDirName = basename(dirname(__DIR__));

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
    require_once __DIR__ . '/../../../mainfile.php';
    require_once __DIR__ . '/../include/config.php';

    $moduleDirName = basename(dirname(__DIR__));

    if (false !== ($moduleHelper = Xmf\Module\Helper::getHelper($moduleDirName))) {
    } else {
        $moduleHelper = Xmf\Module\Helper::getHelper('system');
    }

    // Load language files
    $moduleHelper->loadLanguage('admin');
    $moduleHelper->loadLanguage('modinfo');

    $configurator = new PublisherConfigurator();
    $classUtility = ucfirst($moduleDirName) . 'Utility';
    if (!class_exists($classUtility)) {
        xoops_load('utility', $moduleDirName);
    }

    //    $moduleDirName = $xoopsModule->getVar('dirname');
    //    require_once $GLOBALS['xoops']->path('modules/' . $moduleDirName . '/include/config.php');

    //  ---  CREATE FOLDERS ---------------
    if (count($configurator->uploadFolders) > 0) {
        //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
        foreach (array_keys($configurator->uploadFolders) as $i) {
            $classUtility::createFolder($configurator->uploadFolders[$i]);
        }
    }

    //  ---  COPY blank.png FILES ---------------
    if (count($configurator->blankFiles) > 0) {
        $file = __DIR__ . '/../assets/images/blank.png';
        foreach (array_keys($configurator->blankFiles) as $i) {
            $dest = $configurator->blankFiles[$i] . '/blank.png';
            $classUtility::copyFile($file, $dest);
        }
    }

    /*
        foreach (array_keys($uploadFolders) as $i) {
            PublisherUtility::createFolder($uploadFolders[$i]);
        }

        $file = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
        foreach (array_keys($blankFiles) as $i) {
            $dest = $blankFiles[$i] . '/blank.png';
            PublisherUtility::copyFile($file, $dest);
        }
    */

    return true;
}
