<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright      {@link https://xoops.org/ XOOPS Project}
 * @license        {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author         XOOPS Development Team
 */

use Xmf\Request;
use XoopsModules\Publisher;

error_reporting(0);
include dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
require_once __DIR__ . '/common.php';

$GLOBALS['xoopsLogger']->activated = false;
$helper = Publisher\Helper::getInstance();
$helper->loadLanguage('common');

if (!is_object($GLOBALS['xoopsUser'])) {
    $group = [XOOPS_GROUP_ANONYMOUS];
} else {
    $group = $GLOBALS['xoopsUser']->getGroups();
}

$filename       = basename($_FILES['publisher_upload_file']['name']);
$image_nicename = Request::getString('image_nicename', '', 'POST');
if ('' == $image_nicename || _CO_PUBLISHER_IMAGE_NICENAME == $image_nicename) {
    $image_nicename = $filename;
}

$imgcat_id = Request::getInt('imgcat_id', 0, 'POST');

$imgcatHandler = xoops_getHandler('imagecategory');
$imgcat        = $imgcatHandler->get($imgcat_id);

$error = false;
if (!is_object($imgcat)) {
    $error = _CO_PUBLISHER_IMAGE_CAT_NONE;
} else {
    /* @var  $imgcatpermHandler XoopsGroupPermHandler */
    $imgcatpermHandler = xoops_getHandler('groupperm');
    if (is_object($GLOBALS['xoopsUser'])) {
        if (!$imgcatpermHandler->checkRight('imgcat_write', $imgcat_id, $GLOBALS['xoopsUser']->getGroups())) {
            $error = _CO_PUBLISHER_IMAGE_CAT_NONE;
        }
    } else {
        if (!$imgcatpermHandler->checkRight('imgcat_write', $imgcat_id, XOOPS_GROUP_ANONYMOUS)) {
            $error = _CO_PUBLISHER_IMAGE_CAT_NOPERM;
        }
    }
}

if (false === $error) {
    xoops_load('XoopsMediaUploader');
    $uploader = new \XoopsMediaUploader(XOOPS_UPLOAD_PATH . '/images', ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'], $imgcat->getVar('imgcat_maxsize'), $imgcat->getVar('imgcat_maxwidth'), $imgcat->getVar('imgcat_maxheight'));
    $uploader->setPrefix('img');
    if ($uploader->fetchMedia('publisher_upload_file')) {
        if (!$uploader->upload()) {
            $error = implode('<br>', $uploader->getErrors(false));
        } else {
            $imageHandler = xoops_getHandler('image');
            $image        = $imageHandler->create();
            $image->setVar('image_name', 'images/' . $uploader->getSavedFileName());
            $image->setVar('image_nicename', $image_nicename);
            $image->setVar('image_mimetype', $uploader->getMediaType());
            $image->setVar('image_created', time());
            $image->setVar('image_display', 1);
            $image->setVar('image_weight', 0);
            $image->setVar('imgcat_id', $imgcat_id);
            if ('db' === $imgcat->getVar('imgcat_storetype')) {
                $fp      = @fopen($uploader->getSavedDestination(), 'rb');
                $fbinary = @fread($fp, filesize($uploader->getSavedDestination()));
                @fclose($fp);
                $image->setVar('image_body', $fbinary, true);
                if (file_exists($uploader->getSavedDestination())) {
                    unlink($uploader->getSavedDestination());
                }
            }
            if (!$imageHandler->insert($image)) {
                $error = sprintf(_FAILSAVEIMG, $image->getVar('image_nicename'));
            }
        }
    } else {
        $error = sprintf(_FAILFETCHIMG, 0) . '<br>' . implode('<br>', $uploader->getErrors(false));
    }
}

$arr = ['success', $image->getVar('image_name'), Publisher\Utility::convertCharset($image->getVar('image_nicename'))];
if (false !== $error) {
    $arr = ['error', Publisher\Utility::convertCharset($error)];
}

echo json_encode($arr);
