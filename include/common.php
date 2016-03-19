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
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

//include_once __DIR__ . '/config.php';

define('PUBLISHER_DIRNAME', basename(dirname(__DIR__)));
define('PUBLISHER_URL', XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME);
define('PUBLISHER_PATH', XOOPS_ROOT_PATH . '/modules/' . PUBLISHER_DIRNAME);
define('PUBLISHER_IMAGES_URL', PUBLISHER_URL . '/assets/images');
define('PUBLISHER_ADMIN_URL', PUBLISHER_URL . '/admin');
define('PUBLISHER_ADMIN_PATH', PUBLISHER_PATH . '/admin/index.php');
define('PUBLISHER_ROOT_PATH', $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME));
define('PUBLISHER_AUTHOR_LOGOIMG', PUBLISHER_URL . '/assets/images/logo.png');
define('PUBLISHER_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . PUBLISHER_DIRNAME); // WITHOUT Trailing slash
define('PUBLISHER_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . PUBLISHER_DIRNAME); // WITHOUT Trailing slash

include_once PUBLISHER_ROOT_PATH . '/include/functions.php';
//include_once PUBLISHER_ROOT_PATH . '/include/constants.php';
include_once PUBLISHER_ROOT_PATH . '/include/seo_functions.php';
include_once PUBLISHER_ROOT_PATH . '/class/metagen.php';
include_once PUBLISHER_ROOT_PATH . '/class/session.php';
include_once PUBLISHER_ROOT_PATH . '/class/publisher.php';
//include_once PUBLISHER_ROOT_PATH . '/class/request.php';

// module information
$mod_copyright = "<a href='http://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . PUBLISHER_AUTHOR_LOGOIMG . "' alt='XOOPS Project' /></a>";

xoops_loadLanguage('common', PUBLISHER_DIRNAME);

xoops_load('constants', PUBLISHER_DIRNAME);
xoops_load('utilities', PUBLISHER_DIRNAME);
xoops_load('XoopsRequest');
xoops_load('XoopsFilterInput');

$debug     = false;
$publisher = PublisherPublisher::getInstance($debug);

//This is needed or it will not work in blocks.
global $publisherIsAdmin;

// Load only if module is installed
if (is_object($publisher->getModule())) {
    // Find if the user is admin of the module
    $publisherIsAdmin = publisherUserIsAdmin();
    // get current page
    $publisherCurrentPage = publisherGetCurrentPage();
}
