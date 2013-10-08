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
 *  Publisher class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Include
 * @subpackage      Functions
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: common.php 10374 2012-12-12 23:39:48Z trabis $
 */
defined("XOOPS_ROOT_PATH") or die("XOOPS root path not defined");

define("PUBLISHER_DIRNAME", basename(dirname(dirname(__FILE__))));
define("PUBLISHER_URL", XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME);
define("PUBLISHER_IMAGES_URL", PUBLISHER_URL . '/images');
define("PUBLISHER_ADMIN_URL", PUBLISHER_URL . '/admin');
define("PUBLISHER_UPLOADS_URL", XOOPS_URL . '/uploads/' . PUBLISHER_DIRNAME);
define("PUBLISHER_ROOT_PATH", XOOPS_ROOT_PATH . '/modules/' . PUBLISHER_DIRNAME);
define("PUBLISHER_UPLOADS_PATH", XOOPS_ROOT_PATH . '/uploads/' . PUBLISHER_DIRNAME);

xoops_loadLanguage('common', PUBLISHER_DIRNAME);

include_once PUBLISHER_ROOT_PATH . '/include/functions.php';
include_once PUBLISHER_ROOT_PATH . '/include/constants.php';
include_once PUBLISHER_ROOT_PATH . '/include/seo_functions.php';
include_once PUBLISHER_ROOT_PATH . '/class/metagen.php';
include_once PUBLISHER_ROOT_PATH . '/class/session.php';
include_once PUBLISHER_ROOT_PATH . '/class/publisher.php';
include_once PUBLISHER_ROOT_PATH . '/class/request.php';

$debug = false;
$publisher = PublisherPublisher::getInstance($debug);

//This is needed or it will not work in blocks.
global $publisher_isAdmin;

// Load only if module is installed
if (is_object($publisher->getModule())) {
    // Find if the user is admin of the module
    $publisher_isAdmin = publisher_userIsAdmin();
    // get current page
    $publisher_current_page = publisher_getCurrentPage();
}