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
 * @version         $Id: visit.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__FILE__) . '/header.php';

$fileid = PublisherRequest::getInt('fileid');

// Creating the item object for the selected item
$fileObj = $publisher->getHandler('file')->get($fileid);

if ($fileObj->getVar('status' != _PUBLISHER_STATUS_FILE_ACTIVE)) {
    redirect_header("javascript:history.go(-1)", 1, _NOPERM);
}

$itemObj = $publisher->getHandler('item')->get($fileObj->getVar('itemid'));

// Check user permissions to access this file
if (!$itemObj->accessGranted()) {
    redirect_header("javascript:history.go(-1)", 1, _NOPERM);
    exit();
}
// Creating the category object that holds the selected ITEM
$categoryObj = $itemObj->category();

$fileObj->updateCounter();

if (!preg_match("/^ed2k*:\/\//i", $fileObj->getFileUrl())) {
    header("Location: " . $fileObj->getFileUrl());
}

echo "<html><head><meta http-equiv=\"Refresh\" content=\"0; URL=" . $myts->oopsHtmlSpecialChars($fileObj->getFileUrl()) . "\"></meta></head><body></body></html>";
exit();
?>