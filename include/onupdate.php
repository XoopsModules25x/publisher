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
 * @author          trabis <lusopoemas@gmail.com>
 *
 */

use Xoopsmodules\publisher;

if ((!defined('XOOPS_ROOT_PATH')) || !($GLOBALS['xoopsUser'] instanceof XoopsUser)
    || !$GLOBALS['xoopsUser']->IsAdmin()
) {
    exit('Restricted access' . PHP_EOL);
}

/**
 *
 * Prepares system prior to attempting to install module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if ready to install, false if not
 */
function xoops_module_pre_update_publisher(XoopsModule $module)
{
    $moduleDirName = basename(dirname(__DIR__));
    /** @var \Utility $utility */
    $utility = new publisher\Utility();

    $xoopsSuccess = publisher\Utility::checkVerXoops($module);
    $phpSuccess   = publisher\Utility::checkVerPhp($module);
    return $xoopsSuccess && $phpSuccess;
}
/**
 *
 * Performs tasks required during update of the module
 * @param XoopsModule $module {@link XoopsModule}
 * @param null        $previousVersion
 *
 * @return bool true if update successful, false if not
 */

function xoops_module_update_publisher(XoopsModule $module, $previousVersion = null)
{
    global $xoopsDB;
    require_once __DIR__ . '/../../../mainfile.php';
    require_once __DIR__ . '/../include/config.php';

    $moduleDirName = basename(dirname(__DIR__));
    $helper      = \Xmf\Module\Helper::getHelper($moduleDirName);
    /** @var \Utility $utility */
    $utility = new \Xoopsmodules\publisher\Utility();


    // Load language files
    $helper->loadLanguage('admin');
    $helper->loadLanguage('modinfo');

    xoops_load('configurator', $moduleDirName);
    $configurator = new publisher\Configurator();
    /** @var \Utility $utility */
    $utility = new publisher\Utility();

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
        /** @var \Utility $utility */
        $utility = new publisher\Utility();

        //delete old HTML templates
        if (count($configurator->templateFolders) > 0) {
            foreach ($configurator->templateFolders as $folder) {
                $templateFolder = $GLOBALS['xoops']->path('modules/' . $moduleDirName . $folder);
                if (is_dir($templateFolder)) {
                    $templateList = array_diff(scandir($templateFolder, SCANDIR_SORT_NONE), ['..', '.']);
                    foreach ($templateList as $k => $v) {
                        $fileInfo = new SplFileInfo($templateFolder . $v);
                        if ('html' === $fileInfo->getExtension() && 'index.html' !== $fileInfo->getFilename()) {
                            if (file_exists($templateFolder . $v)) {
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
                /** @var XoopsObjectHandler $folderHandler */
                $folderHandler = \XoopsFile::getHandler('folder', $tempFolder);
                $folderHandler->delete($tempFolder);
            }
        }

        //  ---  CREATE FOLDERS ---------------
        if (count($configurator->uploadFolders) > 0) {
            //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
            foreach (array_keys($configurator->uploadFolders) as $i) {
                publisher\Utility::createFolder($configurator->uploadFolders[$i]);
            }
        }

        //  ---  COPY blank.png FILES ---------------
        if (count($configurator->blankFiles) > 0) {
            $file = __DIR__ . '/../assets/images/blank.png';
            foreach (array_keys($configurator->blankFiles) as $i) {
                $dest = $configurator->blankFiles[$i] . '/blank.png';
                publisher\Utility::copyFile($file, $dest);
            }
        }

        //delete .html entries from the tpl table
        $sql = 'DELETE FROM ' . $xoopsDB->prefix('tplfile') . " WHERE `tpl_module` = '" . $module->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
        $xoopsDB->queryF($sql);
    }
    /* @var  $gpermHandler XoopsGroupPermHandler */
    $gpermHandler = xoops_getHandler('groupperm');

    return $gpermHandler->deleteByModule($module->getVar('mid'), 'item_read');
}
