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
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: onupdate.php 10374 2012-12-12 23:39:48Z trabis $
 *
 * @param      $module
 * @param null $oldversion
 *
 * @return
 */

function xoops_module_update_publisher(XoopsModule $module, $oldversion = null)
{
    global $xoopsDB;
    if ($oldversion < 102) {
        // delete old html template files
        $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/');
        $templateList      = array_diff(scandir($templateDirectory), array('..', '.'));
        foreach ($templateList as $k => $v) {
            $fileInfo = new SplFileInfo($templateDirectory . $v);
            if ($fileInfo->getExtension() === 'html' && $fileInfo->getFilename() !== 'index.html') {
                if (file_exists($templateDirectory . $v)) {
                    unlink($templateDirectory . $v);
                }
            }
        }
        // delete old block html template files
        $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/blocks/');
        $templateList      = array_diff(scandir($templateDirectory), array('..', '.'));
        foreach ($templateList as $k => $v) {
            $fileInfo = new SplFileInfo($templateDirectory . $v);
            if ($fileInfo->getExtension() === 'html' && $fileInfo->getFilename() !== 'index.html') {
                if (file_exists($templateDirectory . $v)) {
                    unlink($templateDirectory . $v);
                }
            }
        }

        //delete old files:
        $oldFiles = array(
            '/class/request.php',
            '/class/registry.php',
            '/include/constants.php',
            '/ajaxrating.txt');

        foreach (array_keys($oldFiles) as $i) {
            unlink($GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . $oldFiles[$i]));
        }

        //delete .html entries from the tpl table
        $sql = 'DELETE FROM ' . $xoopsDB->prefix('tplfile') . " WHERE `tpl_module` = '" . $module->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
        $xoopsDB->queryF($sql);

        // Load class XoopsFile
        xoops_load('XoopsFile');
        //delete /images directory
        $imagesDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/images/');
        $folderHandler   = XoopsFile::getHandler('folder', $imagesDirectory);
        $folderHandler->delete($imagesDirectory);
        //delete /css directory
        $cssDirectory  = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/css/');
        $folderHandler = XoopsFile::getHandler('folder', $cssDirectory);
        $folderHandler->delete($cssDirectory);
        //delete /js directory
        $jsDirectory   = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/js/');
        $folderHandler = XoopsFile::getHandler('folder', $jsDirectory);
        $folderHandler->delete($jsDirectory);
        //delete /tcpdf directory
        $tcpdfDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/tcpdf/');
        $folderHandler  = XoopsFile::getHandler('folder', $tcpdfDirectory);
        $folderHandler->delete($tcpdfDirectory);
        //delete /templates/style.css file
        //       $cssFile = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/style.css');
        //       $folderHandler   = XoopsFile::getHandler('file', $cssFile);
        //       $folderHandler->delete($cssFile);

        //create upload directories, if needed
        $moduleDirName =  $module->getVar('dirname');
        include $GLOBALS['xoops']->path('modules/'.$moduleDirName.'/include/config.php');

        foreach (array_keys($uploadFolders) as $i) {
            PublisherUtilities::createFolder($uploadFolders[$i]);
        }
        //copy blank.png files, if needed
        $file = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
        foreach (array_keys($copyFiles) as $i) {
            $dest   = $copyFiles[$i] . '/blank.png';
            PublisherUtilities::copyFile($file, $dest);
        }
    }

    $gpermHandler = xoops_getHandler('groupperm');

    return $gpermHandler->deleteByModule($module->getVar('mid'), 'item_read');
}
