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
 */

use XoopsModules\Publisher;

$moduleDirName = basename(dirname(__DIR__));
$moduleDirNameUpper = strtoupper($moduleDirName);


/** @var \XoopsDatabase $db */
/** @var Publisher\Helper $helper */
/** @var Publisher\Utility $utility */

$db     = \XoopsDatabaseFactory::getDatabaseConnection();
$helper = Publisher\Helper::getInstance();
$utility = new Publisher\Utility();
//$configurator = new Publisher\Common\Configurator();

$helper->loadLanguage('common');

//$utilities                = new Publisher\Utilities();
//$brokenHandler            = new Publisher\BrokenHandler($db);
//$categoryHandler          = new Publisher\CategoryHandler($db);
//$downlimitHandler         = new Publisher\DownlimitHandler($db);
//$downloadsHandler         = new Publisher\DownloadsHandler($db);
//$fielddataHandler         = new Publisher\FielddataHandler($db);
//$fieldHandler             = new Publisher\FieldHandler($db);
//$modifiedfielddataHandler = new Publisher\ModifiedfielddataHandler($db);
//$modifiedHandler          = new Publisher\ModifiedHandler($db);
//$ratingHandler            = new Publisher\RatingHandler($db);



if (!defined($moduleDirNameUpper . '_CONSTANTS_DEFINED')) {
    define($moduleDirNameUpper . '_DIRNAME', basename(dirname(__DIR__)));
    define($moduleDirNameUpper . '_ROOT_PATH', XOOPS_ROOT_PATH . '/modules/' . $moduleDirName);
    define($moduleDirNameUpper . '_PATH', XOOPS_ROOT_PATH . '/modules/' . $moduleDirName);
    define($moduleDirNameUpper . '_URL', XOOPS_URL . '/modules/' . $moduleDirName);
    define($moduleDirNameUpper . '_IMAGES_URL', constant($moduleDirNameUpper . '_URL') . '/assets/images/');
    define($moduleDirNameUpper . '_IMAGES_PATH', constant($moduleDirNameUpper . '_ROOT_PATH') . '/assets/images/');
    define($moduleDirNameUpper . '_ADMIN_URL', constant($moduleDirNameUpper . '_URL') . '/admin/');
    define($moduleDirNameUpper . '_ADMIN_PATH', constant($moduleDirNameUpper . '_ROOT_PATH') . '/admin/');
    define($moduleDirNameUpper . '_ADMIN', constant($moduleDirNameUpper . '_URL') . '/admin/index.php');
    define($moduleDirNameUpper . '_AUTHOR_LOGOIMG', constant($moduleDirNameUpper . '_URL') . '/assets/images/logoModule.png');
    define($moduleDirNameUpper . '_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . $moduleDirName); // WITHOUT Trailing slash
    define($moduleDirNameUpper . '_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . $moduleDirName); // WITHOUT Trailing slash
    define($moduleDirNameUpper . '_CAT_IMAGES_URL', XOOPS_UPLOAD_URL . ' / ' . constant($moduleDirNameUpper . '_DIRNAME') . '/images/category');
    define($moduleDirNameUpper . '_CAT_IMAGES_PATH', XOOPS_UPLOAD_PATH . '/' . constant($moduleDirNameUpper . '_DIRNAME') . ' / images / category');
    define($moduleDirNameUpper . '_CACHE_PATH', XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_CONSTANTS_DEFINED', 1);
}


//if (!defined('PUBLISHER_DIRNAME')) {
//    define('PUBLISHER_DIRNAME', basename(dirname(__DIR__)));
//    define('PUBLISHER_URL', XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME);
//    define('PUBLISHER_PATH', XOOPS_ROOT_PATH . '/modules/' . PUBLISHER_DIRNAME);
//    define('PUBLISHER_IMAGES_URL', PUBLISHER_URL . '/assets/images');
//    define('PUBLISHER_ADMIN_URL', PUBLISHER_URL . '/admin');
//    define('PUBLISHER_ADMIN_PATH', PUBLISHER_PATH . '/admin/index.php');
//    define('PUBLISHER_ROOT_PATH', $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME));
//    define('PUBLISHER_AUTHOR_LOGOIMG', PUBLISHER_URL . '/assets/images/logo.png');
//    define('PUBLISHER_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . PUBLISHER_DIRNAME); // WITHOUT Trailing slash
//    define('PUBLISHER_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . PUBLISHER_DIRNAME); // WITHOUT Trailing slash
//}

//require_once PUBLISHER_ROOT_PATH . '/include/functions.php';
//require_once PUBLISHER_ROOT_PATH . '/include/constants.php';
//require_once PUBLISHER_ROOT_PATH . '/include/seo_functions.php';
//require_once PUBLISHER_ROOT_PATH . '/class/metagen.php';
//require_once PUBLISHER_ROOT_PATH . '/class/session.php';
//require_once PUBLISHER_ROOT_PATH . '/class/request.php';




//xoops_load('Constants', PUBLISHER_DIRNAME);

//This is needed or it will not work in blocks.
global $publisherIsAdmin;

// Load only if module is installed
if (is_object($helper->getModule())) {
    // Find if the user is admin of the module
    $publisherIsAdmin = Publisher\Utility::userIsAdmin();
    // get current page
    $publisherCurrentPage = Publisher\Utility::getCurrentPage();
}

$pathIcon16    = Xmf\Module\Admin::iconUrl('', 16);
$pathIcon32    = Xmf\Module\Admin::iconUrl('', 32);
//$pathModIcon16 = $helper->getModule()->getInfo('modicons16');
//$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$icons = [
    'edit'    => "<img src='" . $pathIcon16 . "/edit.png'  alt=" . _EDIT . "' align='middle'>",
    'delete'  => "<img src='" . $pathIcon16 . "/delete.png' alt='" . _DELETE . "' align='middle'>",
    'clone'   => "<img src='" . $pathIcon16 . "/editcopy.png' alt='" . _CLONE . "' align='middle'>",
    'preview' => "<img src='" . $pathIcon16 . "/view.png' alt='" . _PREVIEW . "' align='middle'>",
    'print'   => "<img src='" . $pathIcon16 . "/printer.png' alt='" . _CLONE . "' align='middle'>",
    'pdf'     => "<img src='" . $pathIcon16 . "/pdf.png' alt='" . _CLONE . "' align='middle'>",
    'add'     => "<img src='" . $pathIcon16 . "/add.png' alt='" . _ADD . "' align='middle'>",
    '0'       => "<img src='" . $pathIcon16 . "/0.png' alt='" . 0 . "' align='middle'>",
    '1'       => "<img src='" . $pathIcon16 . "/1.png' alt='" . 1 . "' align='middle'>",
];

$debug = false;

// MyTextSanitizer object
$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $GLOBALS['xoopsTpl'] = new \XoopsTpl();
}

$GLOBALS['xoopsTpl']->assign('mod_url', XOOPS_URL . '/modules/' . $moduleDirName);
// Local icons path
if (is_object($helper->getModule())) {
    $pathModIcon16 = $helper->getModule()->getInfo('modicons16');
    $pathModIcon32 = $helper->getModule()->getInfo('modicons32');

    $GLOBALS['xoopsTpl']->assign('pathModIcon16', XOOPS_URL . '/modules/' . $moduleDirName . '/' . $pathModIcon16);
    $GLOBALS['xoopsTpl']->assign('pathModIcon32', $pathModIcon32);
}
