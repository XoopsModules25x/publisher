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

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\MimetypesUtility;

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');

$start = Request::getInt('start', 0, 'GET');
$limit = Request::getInt('limit', Request::getInt('limit', 15, 'GET'), 'POST');

$aSortBy   = [
    'mime_id'    => _AM_PUBLISHER_MIME_ID,
    'mime_name'  => _AM_PUBLISHER_MIME_NAME,
    'mime_ext'   => _AM_PUBLISHER_MIME_EXT,
    'mime_admin' => _AM_PUBLISHER_MIME_ADMIN,
    'mime_user'  => _AM_PUBLISHER_MIME_USER,
];
$aOrderBy  = ['ASC' => _AM_PUBLISHER_TEXT_ASCENDING, 'DESC' => _AM_PUBLISHER_TEXT_DESCENDING];
$aLimitBy  = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];
$aSearchBy = ['mime_id' => _AM_PUBLISHER_MIME_ID, 'mime_name' => _AM_PUBLISHER_MIME_NAME, 'mime_ext' => _AM_PUBLISHER_MIME_EXT];

$error = [];

$op = Request::getString('op', 'default', 'GET');

// all post requests should have a valid token
if ('POST' === Request::getMethod() && !$GLOBALS['xoopsSecurity']->check()) {
    redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php?op=manage', 3, _CO_PUBLISHER_BAD_TOKEN);
}

switch ($op) {
    case 'add':
        MimetypesUtility::add();
        break;
    case 'delete':
        MimetypesUtility::delete();
        break;
    case 'edit':
        MimetypesUtility::edit();
        break;
    case 'search':
        MimetypesUtility::search($icons);
        break;
    case 'updateMimeValue':
        MimetypesUtility::updateMimeValue();
        break;
    case 'confirmUpdateMimeValue':
        MimetypesUtility::confirmUpdateMimeValue();
        break;
    case 'clearAddSession':
        MimetypesUtility::clearAddSession();
        break;
    case 'clearEditSession':
        MimetypesUtility::clearEditSession();
        break;
    case 'manage':
    default:
        MimetypesUtility::manage($icons);
        break;
}
