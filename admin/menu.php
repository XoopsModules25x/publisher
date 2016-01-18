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
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: menu.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

$dirname       = basename(dirname(__DIR__));
$moduleHandler =& xoops_getHandler('module');
$module        = $moduleHandler->getByDirname($dirname);
$pathIcon32     = '../../' . $module->getInfo('sysIcons32');
$pathModIcon32     = $module->getInfo('modIcons32');

include_once dirname(__DIR__) . '/include/config.php';

xoops_loadLanguage('admin', $dirname);

$adminmenu = array(
    array(
        'title' => _MI_PUBLISHER_ADMENU0,
        'link'  => 'admin/index.php',
        'icon'  => $pathIcon32 . '/home.png'),

    array(
        'title' => _MI_PUBLISHER_ADMENU1,
        'link'  => 'admin/main.php',
        'icon'  => $pathIcon32 . '/manage.png'),

    // Category
    array(
        'title' => _MI_PUBLISHER_ADMENU2,
        'link'  => 'admin/category.php',
        'icon'  => $pathIcon32 . '/category.png'),

    // Items
    array(
        'title' => _MI_PUBLISHER_ADMENU3,
        'link'  => 'admin/item.php',
        'icon'  => $pathIcon32 . '/content.png'),

    // Permissions
    array(
        'title' => _MI_PUBLISHER_ADMENU4,
        'link'  => 'admin/permissions.php',
        'icon'  => $pathIcon32 . '/permissions.png'),

    // Mimetypes
    array(
        'title' => _MI_PUBLISHER_ADMENU6,
        'link'  => 'admin/mimetypes.php',
        'icon'  => $pathIcon32 . '/type.png'),

    // Preferences
    //    array(
    //        'title' => _PREFERENCES,
    //        'link'  => 'admin/preferences.php',
    //        'icon'  => '../../' . $pathIcon32 . '/administration.png'),

    /*
     //Comments
        array(
            "title" => _AM_PUBLISHER_COMMENTS,
            "link"  => '../../modules/system/admin.php?fct=comments&amp;module=' . $module->getVar('mid'),
            "icon"  => './assets/images/icon32/folder_txt.png'),
    */

    //Import
    array(
        'title' => _AM_PUBLISHER_IMPORT,
        'link'  => 'admin/import.php',
        'icon'  => $pathIcon32 . '/download.png'),

    //Clone
    array(
        'title' => _AM_PUBLISHER_CLONE,
        'link'  => 'admin/clone.php',
        'icon'  => $pathModIcon32.'/editcopy.png'),

    //About
    array(
        'title' => _AM_PUBLISHER_ABOUT,
        'link'  => 'admin/about.php',
        'icon'  => $pathIcon32 . '/about.png'));

$GLOBALS['xoTheme']->addStylesheet('modules/' . $dirname . '/assets/css/style.css');
