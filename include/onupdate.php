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
 * @author          trabis <lusopoemas@gmail.com>
 */

use XoopsModules\Publisher\{Common,
    Helper,
    Utility
};

if ((!defined('XOOPS_ROOT_PATH')) || !($GLOBALS['xoopsUser'] instanceof \XoopsUser)
    || !$GLOBALS['xoopsUser']->isAdmin()) {
    exit('Restricted access' . PHP_EOL);
}

/**
 * Prepares system prior to attempting to install module
 * @param \XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if ready to install, false if not
 */
function xoops_module_pre_update_publisher(\XoopsModule $module)
{
    $utility = new Utility();

    $xoopsSuccess = $utility::checkVerXoops($module);
    $phpSuccess   = $utility::checkVerPhp($module);

    return $xoopsSuccess && $phpSuccess;
}

/**
 * Performs tasks required during update of the module
 * @param \XoopsModule $module {@link XoopsModule}
 * @param null|string  $previousVersion
 *
 * @return bool true if update successful, false if not
 */
function xoops_module_update_publisher(\XoopsModule $module, $previousVersion = null)
{
    global $xoopsDB;
    $moduleDirName = \basename(\dirname(__DIR__));
    //    $moduleDirNameUpper = mb_strtoupper($moduleDirName);

    /** @var Helper $helper */
    /** @var Common\Configurator $configurator */
    $helper       = Helper::getInstance();
    $configurator = new Common\Configurator();

    $helper->loadLanguage('common');

    //delete .html entries from the tpl table
    $sql = 'DELETE FROM ' . $xoopsDB->prefix('tplfile') . " WHERE `tpl_module` = '" . $module->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
    $xoopsDB->queryF($sql);
    $sql = 'DELETE FROM ' . $xoopsDB->prefix('newblocks') . " WHERE `dirname` = '" . $module->getVar('dirname', 'n') . "' AND `template` LIKE '%.html%'";
    $xoopsDB->queryF($sql);

    if ($previousVersion <= 105) {
        //change TEXT fields to NULL
        $sql = '    ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_categories') . ' MODIFY `description` TEXT NULL';
        $xoopsDB->queryF($sql);
        $sql = '    ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_categories') . ' MODIFY `header` TEXT NULL';
        $xoopsDB->queryF($sql);
        $sql = '    ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_categories') . ' MODIFY `meta_keywords` TEXT NULL';
        $xoopsDB->queryF($sql);
        $sql = '    ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_categories') . ' MODIFY `meta_description` TEXT NULL';
        $xoopsDB->queryF($sql);
        $utility = new Utility();

        //delete old HTML templates
        if (count($configurator->templateFolders) > 0) {
            foreach ($configurator->templateFolders as $folder) {
                $templateFolder = $GLOBALS['xoops']->path('modules/' . $moduleDirName . $folder);
                if (is_dir($templateFolder)) {
                    $templateList = array_diff(scandir($templateFolder, SCANDIR_SORT_NONE), ['..', '.']);
                    foreach ($templateList as $k => $v) {
                        $fileInfo = new \SplFileInfo($templateFolder . $v);
                        if ('html' === $fileInfo->getExtension() && 'index.html' !== $fileInfo->getFilename()) {
                            if (is_file($templateFolder . $v)) {
                                unlink($templateFolder . $v);
                            }
                        }
                    }
                }
            }
        }

        //  ---  DELETE OLD FILES ---------------
        if (count($configurator->oldFiles) > 0) {
            //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
            foreach (array_keys($configurator->oldFiles) as $i) {
                $tempFile = $GLOBALS['xoops']->path('modules/' . $moduleDirName . $configurator->oldFiles[$i]);
                if (is_file($tempFile)) {
                    unlink($tempFile);
                }
            }
        }

        //  ---  DELETE OLD FOLDERS ---------------
        xoops_load('XoopsFile');
        if (count($configurator->oldFolders) > 0) {
            //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
            foreach (array_keys($configurator->oldFolders) as $i) {
                $tempFolder = $GLOBALS['xoops']->path('modules/' . $moduleDirName . $configurator->oldFolders[$i]);
                /** @var \XoopsObjectHandler $folderHandler */
                $folderHandler = \XoopsFile::getHandler('folder', $tempFolder);
                $folderHandler->delete($tempFolder);
            }
        }

        //  ---  CREATE FOLDERS ---------------
        if (count($configurator->uploadFolders) > 0) {
            //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
            foreach (array_keys($configurator->uploadFolders) as $i) {
                $utility::createFolder($configurator->uploadFolders[$i]);
            }
        }

        //  ---  COPY blank.png FILES ---------------
        if (count($configurator->copyBlankFiles) > 0) {
            $file = \dirname(__DIR__) . '/assets/images/blank.png';
            foreach (array_keys($configurator->copyBlankFiles) as $i) {
                $dest = $configurator->copyBlankFiles[$i] . '/blank.png';
                $utility::copyFile($file, $dest);
            }
        }

        //delete .html entries from the tpl table
        $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('tplfile') . " WHERE `tpl_module` = '" . $module->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
        $GLOBALS['xoopsDB']->queryF($sql);

        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = xoops_getHandler('groupperm');

        return $grouppermHandler->deleteByModule($module->getVar('mid'), 'item_read');
    }

    // check table items for field `dateexpire`
    if (!$GLOBALS['xoopsDB']->query('SELECT dateexpire FROM ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_items'))) {
        $sql = 'ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_items') . " ADD `dateexpire` INT(11) NULL DEFAULT '0' AFTER `datesub`";
        $GLOBALS['xoopsDB']->queryF($sql);
    }
    // check table items for field `votetype`
    if (!$GLOBALS['xoopsDB']->query('SELECT votetype FROM ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_items'))) {
        $sql = 'ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_items') . " ADD `votetype` TINYINT(1) NOT NULL DEFAULT '0' AFTER `item_tag`";
        $GLOBALS['xoopsDB']->queryF($sql);
    }

    return true;
}
