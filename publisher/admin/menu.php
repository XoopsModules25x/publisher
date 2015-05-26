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

$dirname        = basename(dirname(__DIR__));
$moduleHandler = xoops_gethandler('module');
$module         = $moduleHandler->getByDirname($dirname);
$pathIcon32     = $module->getInfo('icons32');

include_once dirname(__DIR__) . '/include/config.php';

xoops_loadLanguage('admin', $dirname);

$i = 0;

// Index
$adminmenu[$i]['title'] = _MI_PUBLISHER_ADMENU0;
$adminmenu[$i]['link']  = "admin/index.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/home.png';
++$i;

$adminmenu[$i]['title'] = _MI_PUBLISHER_ADMENU1;
$adminmenu[$i]['link']  = "admin/main.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/manage.png';
++$i;

// Category
$adminmenu[$i]['title'] = _MI_PUBLISHER_ADMENU2;
$adminmenu[$i]['link']  = "admin/category.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/category.png';
++$i;

// Items
$adminmenu[$i]['title'] = _MI_PUBLISHER_ADMENU3;
$adminmenu[$i]['link']  = "admin/item.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/content.png';
++$i;

// Permissions
$adminmenu[$i]['title'] = _MI_PUBLISHER_ADMENU4;
$adminmenu[$i]['link']  = "admin/permissions.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/permissions.png';
++$i;

// Mimetypes
$adminmenu[$i]['title'] = _MI_PUBLISHER_ADMENU6;
$adminmenu[$i]['link']  = "admin/mimetypes.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/type.png';
++$i;

// Preferences
$adminmenu[$i]['title'] = _PREFERENCES;
$adminmenu[$i]['link']  = "admin/preferences.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/administration.png';
++$i;
/*
$adminmenu[$i]['title'] = _AM_PUBLISHER_COMMENTS;
$adminmenu[$i]['link']  = '../../modules/system/admin.php?fct=comments&amp;module=' . $module->getVar('mid');
$adminmenu[$i]["icon"]  = './assets/images/icon32/folder_txt.png';
++$i;
*/
$adminmenu[$i]['title'] = _AM_PUBLISHER_IMPORT;
$adminmenu[$i]['link']  = "admin/import.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/download.png';
++$i;

$adminmenu[$i]['title'] = _AM_PUBLISHER_CLONE;
$adminmenu[$i]['link']  = "admin/clone.php";
$adminmenu[$i]["icon"]  = './assets/images/icon32/editcopy.png';
++$i;

$adminmenu[$i]['title'] = _AM_PUBLISHER_ABOUT;
$adminmenu[$i]['link']  = "admin/about.php";
$adminmenu[$i]["icon"]  = '../../' . $pathIcon32 . '/about.png';

$GLOBALS['xoTheme']->addStylesheet("modules/" . $dirname . "/assets/css/style.css");
