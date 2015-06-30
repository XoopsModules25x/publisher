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
        $template_list     = array_diff(scandir($templateDirectory), array('..', '.'));
        foreach ($template_list as $k => $v) {
            $fileinfo = new SplFileInfo($templateDirectory . $v);
            if ($fileinfo->getExtension() == 'html' && $fileinfo->getFilename() != 'index.html') {
                if (file_exists($templateDirectory . $v)) {
                    unlink($templateDirectory . $v);
                }
            }
        }
        // delete old block html template files
        $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/blocks/');
        $template_list     = array_diff(scandir($templateDirectory), array('..', '.'));
        foreach ($template_list as $k => $v) {
            $fileinfo = new SplFileInfo($templateDirectory . $v);
            if ($fileinfo->getExtension() == 'html' && $fileinfo->getFilename() != 'index.html') {
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

    $gperm_handler = xoops_gethandler('groupperm');

    return $gperm_handler->deleteByModule($module->getVar('mid'), 'item_read');
}
