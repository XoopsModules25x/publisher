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
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Module\Admin;
use XoopsModules\Publisher\Helper;

require_once \dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);

$helper = Helper::getInstance();
$helper->loadLanguage('common');
$helper->loadLanguage('feedback');

// get path to icons
$pathIcon32    = Admin::menuIconPath('');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU0,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png',
];

$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU1,
    'link'  => 'admin/main.php',
    'icon'  => $pathIcon32 . '/manage.png',
];

// Category

$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU2,
    'link'  => 'admin/category.php',
    'icon'  => $pathIcon32 . '/category.png',
];

// Items
$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU3,
    'link'  => 'admin/item.php',
    'icon'  => $pathIcon32 . '/content.png',
];

// Trello
$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU7,
    'link'  => 'admin/trello.php',
    'icon'  => $pathIcon32 . '/extention.png',
];

// Permissions
$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU4,
    'link'  => 'admin/permissions.php',
    'icon'  => $pathIcon32 . '/permissions.png',
];

// Blocks Admin
$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU5,
    'link'  => 'admin/blocksadmin.php',
    'icon'  => $pathIcon32 . '/block.png',
];
// Mimetypes
$adminmenu[] = [
    'title' => _MI_PUBLISHER_ADMENU6,
    'link'  => 'admin/mimetypes.php',
    'icon'  => $pathIcon32 . '/type.png',
];

//$adminmenu[] = [

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

//];
//Import
$adminmenu[] = [
    'title' => _MI_PUBLISHER_IMPORT,
    'link'  => 'admin/import.php',
    'icon'  => $pathIcon32 . '/download.png',
];

//Clone
$adminmenu[] = [
    'title' => _MI_PUBLISHER_MENU_CLONE,
    'link'  => 'admin/clone.php',
    'icon'  => $pathModIcon32 . '/editcopy.png',
];

//    [
//        'title' => _MI_PUBLISHER_MENU_HISTORY,
//        'link'  => 'admin/history.php',
//        'icon'  => $pathModIcon32 . '/editcopy.png'
//    ],

//Feedback
$adminmenu[] = [
    'title' => constant('CO_' . $moduleDirNameUpper . '_' . 'ADMENU_FEEDBACK'),
    'link'  => 'admin/feedback.php',
    'icon'  => $pathIcon32 . '/mail_foward.png',
];

if (is_object($helper->getModule()) && $helper->getConfig('displayDeveloperTools')) {
    $adminmenu[] = [
        'title' => constant('CO_' . $moduleDirNameUpper . '_' . 'ADMENU_MIGRATE'),
        'link'  => 'admin/migrate.php',
        'icon'  => $pathIcon32 . '/database_go.png',
    ];
}

//About
$adminmenu[] = [
    'title' => _MI_PUBLISHER_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png',
];

$GLOBALS['xoTheme']->addStylesheet('modules/' . $moduleDirName . '/assets/css/style.css');
