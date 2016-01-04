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
        // Load class XoopsFile
        xoops_load('xoopsfile');
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
    }
    
    // Checking/Making of "uploads" folders
    $indexFile = $GLOBALS['xoops']->path('uploads/index.html');
    $dirUpload = $GLOBALS['xoops']->path('uploads/' . $module->getVar('dirname', 'n') . '/images/');
    if(!is_dir($dirUpload)) {
      mkdir($dirUpload, 0777);
      chmod($dirUpload, 0777);
      copy($indexFile, $dirUpload.'/index.html');
    }
    $dirUpload = $GLOBALS['xoops']->path('uploads/' . $module->getVar('dirname', 'n') . '/images/category/');
    if(!is_dir($dirUpload)) {
      mkdir($dirUpload, 0777);
      chmod($dirUpload, 0777);
      copy($indexFile, $dirUpload.'/index.html');
    }

    $gpermHandler =& xoops_getHandler('groupperm');
    
    return $gpermHandler->deleteByModule($module->getVar('mid'), 'item_read');

}
