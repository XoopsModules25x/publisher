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
 * animal module for xoops
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         GPL 2.0 or later
 * @package         Publisher
 * @subpackage      Config
 * @since           1.03
 * @author          XOOPS Development Team - ( http://xoops.org )
 * @version         $Id: const_entete.php 9860 2012-07-13 10:41:41Z beckmi $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");
require_once dirname(dirname(dirname(__DIR__))) . '/mainfile.php';

define('PUBLISHER_DIRNAME', basename(dirname(__DIR__)));
define('PUBLISHER_URL', XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME);
define('PUBLISHER_PATH', XOOPS_ROOT_PATH . '/modules/' . PUBLISHER_DIRNAME);
define('PUBLISHER_IMAGES_URL', PUBLISHER_URL . '/assets/images');
define('PUBLISHER_ADMIN_URL', PUBLISHER_URL . '/admin');
define('PUBLISHER_ADMIN_PATH', PUBLISHER_PATH . '/admin/index.php');
define('PUBLISHER_ROOT_PATH', $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME));
define('PUBLISHER_AUTHOR_LOGOIMG', PUBLISHER_URL . '/assets/images/logo.png');

/*
//global $xoopsModule;
if (!defined('PUBLISHER_MODULE_PATH')) {
    define('PUBLISHER_DIRNAME', $GLOBALS['xoopsModule']->dirname());
    define('PUBLISHER_PATH', XOOPS_ROOT_PATH . '/modules/' . PUBLISHER_DIRNAME);
    define('PUBLISHER_URL', XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME);
    define('PUBLISHER_ADMIN_URL', PUBLISHER_URL . '/admin/index.php');
    define('PUBLISHER_ADMIN_PATH', PUBLISHER_PATH . '/admin/index.php');
    define('PUBLISHER_AUTHOR_LOGOIMG', PUBLISHER_URL . '/assets/images/xoopsproject_logo.png');

}
*/

// Define here the folder for the main upload path
//$img_dir = $GLOBALS['xoopsModuleConfig']['uploaddir'];

define('PUBLISHER_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . PUBLISHER_DIRNAME); // WITHOUT Trailing slash
//define('PUBLISHER_UPLOAD_PATH', $img_dir); // WITHOUT Trailing slash
define('PUBLISHER_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . PUBLISHER_DIRNAME); // WITHOUT Trailing slash

//define('PUBLISHER_UPLOADS_URL', XOOPS_URL . '/uploads/' . PUBLISHER_DIRNAME);
//define('PUBLISHER_UPLOADS_PATH', $GLOBALS['xoops']->path('uploads/' . PUBLISHER_DIRNAME));

$uploadFolders = array(
    PUBLISHER_UPLOAD_PATH,
    PUBLISHER_UPLOAD_PATH . '/content',
    PUBLISHER_UPLOAD_PATH . '/images',
    PUBLISHER_UPLOAD_PATH . '/images/thumbnails');

// module information
$mod_copyright = "<a href='http://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . PUBLISHER_AUTHOR_LOGOIMG . "' alt='XOOPS Project' /></a>";
