<?php
/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: admin_header.php 10661 2013-01-04 19:22:48Z trabis $
 */

include_once dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
include_once $GLOBALS['xoops']->path('www/include/cp_functions.php');
include_once $GLOBALS['xoops']->path('www/include/cp_header.php');
include_once $GLOBALS['xoops']->path('www/class/xoopsformloader.php');

xoops_load('XoopsRequest');

include_once dirname(__DIR__) . '/include/config.php';

//xoops_loadLanguage('admin', PUBLISHER_DIRNAME);
xoops_loadLanguage('modinfo', PUBLISHER_DIRNAME);

$imagearray = array(
    'editimg'   => "<img src='" . PUBLISHER_IMAGES_URL . "/button_edit.png' alt='" . _AM_PUBLISHER_ICO_EDIT . "' align='middle' />",
    'deleteimg' => "<img src='" . PUBLISHER_IMAGES_URL . "/button_delete.png' alt='" . _AM_PUBLISHER_ICO_DELETE . "' align='middle' />",
    'online'    => "<img src='" . PUBLISHER_IMAGES_URL . "/on.png' alt='" . _AM_PUBLISHER_ICO_ONLINE . "' align='middle' />",
    'offline'   => "<img src='" . PUBLISHER_IMAGES_URL . "/off.png' alt='" . _AM_PUBLISHER_ICO_OFFLINE . "' align='middle' />");

include_once $GLOBALS['xoops']->path('Frameworks/moduleclasses/moduleadmin/moduleadmin.php');
//ModuleAdmin::loadLanguage();
/*
$myts = MyTextSanitizer::getInstance();

if (!isset($xoopsTpl) || !is_object($xoopsTpl)) {
  include_once(XOOPS_ROOT_PATH."/class/template.php");
  $xoopsTpl = new XoopsTpl();
} */
