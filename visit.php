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
 * @subpackage      Action
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/header.php';

$fileid = Request::getInt('fileid', 0, 'GET');

// Creating the item object for the selected item
$fileObj = $helper->getHandler('File')->get($fileid);

if ($fileObj->getVar('status' !== Constants::PUBLISHER_STATUS_FILE_ACTIVE)) {
    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
}

$itemObj = $helper->getHandler('Item')->get($fileObj->getVar('itemid'));

// Check user permissions to access this file
if (!$itemObj->accessGranted()) {
    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    //    exit();
}
// Creating the category object that holds the selected ITEM
$categoryObj = $itemObj->getCategory();

$fileObj->updateCounter();

if (!preg_match("/^ed2k*:\/\//i", $fileObj->getFileUrl())) {
    header('Location: ' . $fileObj->getFileUrl());
}

echo '<html><head><meta http-equiv="Refresh" content="0; URL=' . $myts->oopsHtmlSpecialChars($fileObj->getFileUrl()) . '"></head><body></body></html>';
exit();
