<?php
// $Id: ajax_upload.php 10374 2012-12-12 23:39:48Z trabis $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

error_reporting(0);
include dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
include_once __DIR__ . '/common.php';

$GLOBALS['xoopsLogger']->activated = false;
xoops_loadLanguage('common', basename(dirname(__DIR__)));

if (!is_object($GLOBALS['xoopsUser'])) {
    $group = array(XOOPS_GROUP_ANONYMOUS);
} else {
    $group = $GLOBALS['xoopsUser']->getGroups();
}

$filename       = basename($_FILES['publisher_upload_file']['name']);
$image_nicename = XoopsRequest::getString('image_nicename', '', 'POST');
if ($image_nicename == '' || $image_nicename == _CO_PUBLISHER_IMAGE_NICENAME) {
    $image_nicename = $filename;
}

$imgcat_id = XoopsRequest::getInt('imgcat_id', 0, 'POST');

$imgcatHandler = xoops_getHandler('imagecategory');
$imgcat        = $imgcatHandler->get($imgcat_id);

$error = false;
if (!is_object($imgcat)) {
    $error = _CO_PUBLISHER_IMAGE_CAT_NONE;
} else {
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

if ($error === false) {
    xoops_load('XoopsMediaUploader');
    $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH . '/images', array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'), $imgcat->getVar('imgcat_maxsize'), $imgcat->getVar('imgcat_maxwidth'), $imgcat->getVar('imgcat_maxheight'));
    $uploader->setPrefix('img');
    if ($uploader->fetchMedia('publisher_upload_file')) {
        if (!$uploader->upload()) {
            $error = implode('<br />', $uploader->getErrors(false));
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
            if ($imgcat->getVar('imgcat_storetype') === 'db') {
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
        $error = sprintf(_FAILFETCHIMG, 0) . '<br />' . implode('<br />', $uploader->getErrors(false));
    }
}

$arr = array('success', $image->getVar('image_name'), publisherConvertCharset($image->getVar('image_nicename')));
if ($error) {
    $arr = array('error', publisherConvertCharset($error));
}

echo json_encode($arr);
