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
 * @subpackage      Include
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Sudhaker Raj <http://xoops.biz>
 * @version         $Id: seo.inc.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

//$seoOp = @$_GET['seoOp'];
$seoOp = XoopsRequest::getString('seoOp', '', 'GET');

//$seoArg = @$_GET['seoArg'];
$seoArg = XoopsRequest::getString('seoArg', '', 'GET');

if ('' == $seoOp && XoopsRequest::getString('PATH_INFO', '', 'SERVER')) {
    // SEO mode is path-info
    /*
    Sample URL for path-info
    http://localhost/modules/publisher/seo.php/item.2/can-i-turn-the-ads-off.html
    */
    $data = explode('/', XoopsRequest::getString('PATH_INFO', '', 'SERVER'));

    $seoParts = explode('.', $data[1]);
    $seoOp    = $seoParts[0];
    $seoArg   = $seoParts[1];
    // for multi-argument modules, where itemid and catid both are required.
    // $seoArg = substr($data[1], strlen($seoOp) + 1);
}

$seoMap = array(
    'category' => 'category.php',
    'item'     => 'item.php',
    'print'    => 'print.php');

if (!empty($seoOp) && isset($seoMap[$seoOp])) {
    // module specific dispatching logic, other module must implement as
    // per their requirements.

    $url_arr = explode('/modules/', XoopsRequest::getString('PHP_SELF', '', 'SERVER'));
    $newUrl  = $url_arr[0] . '/modules/' . PUBLISHER_DIRNAME . '/' . $seoMap[$seoOp];

    $_ENV['PHP_SELF']       = $newUrl;
    $_SERVER['SCRIPT_NAME'] = $newUrl;
    $_SERVER['PHP_SELF']    = $newUrl;
    switch ($seoOp) {
        case 'category':
            $_SERVER['REQUEST_URI'] = $newUrl . '?categoryid=' . $seoArg;
            $_GET['categoryid']     = $seoArg;
            $_REQUEST['categoryid'] = $seoArg;
            break;
        case 'item':
        case 'print':
        default:
            $_SERVER['REQUEST_URI'] = $newUrl . '?itemid=' . $seoArg;
            $_GET['itemid']         = $seoArg;
            $_REQUEST['itemid']     = $seoArg;
    }
    include PUBLISHER_ROOT_PATH . '/' . $seoMap[$seoOp];
    exit;
}
