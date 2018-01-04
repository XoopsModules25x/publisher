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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/admin_header.php';

$errors = [];

if (publisher_pagewrap_upload($errors)) {
    redirect_header(Request::getString('backto', '', 'POST'), 2, _AM_PUBLISHER_FILEUPLOAD_SUCCESS);
} else {
    $errorstxt = implode('<br>', $errors);
    $message   = sprintf(_CO_PUBLISHER_MESSAGE_FILE_ERROR, $errorstxt);
    redirect_header(Request::getString('backto', '', 'POST'), 5, $message);
}

/**
 * @param $errors
 *
 * @return bool
 */
function publisher_pagewrap_upload(&$errors)
{
    //    require_once PUBLISHER_ROOT_PATH . '/class/uploader.php';
    xoops_load('XoopsMediaUploader');

    $helper = Publisher\Helper::getInstance();
    $postField = 'fileupload';

    $maxFileSize    = $helper->getConfig('maximum_filesize');
    $maxImageWidth  = $helper->getConfig('maximum_image_width');
    $maxImageHeight = $helper->getConfig('maximum_image_height');

    if (!is_dir(Publisher\Utility::getUploadDir(true, 'content'))) {
        mkdir(Publisher\Utility::getUploadDir(true, 'content'), 0757);
    }
    $allowedMimeTypes = ['text/html', 'text/plain', 'application/xhtml+xml'];
    $uploader         = new \XoopsMediaUploader(Publisher\Utility::getUploadDir(true, 'content') . '/', $allowedMimeTypes, $maxFileSize, $maxImageWidth, $maxImageHeight);
    if ($uploader->fetchMedia($postField)) {
        $uploader->setTargetFileName($uploader->getMediaName());
        if ($uploader->upload()) {
            return true;
        } else {
            $errors = array_merge($errors, $uploader->getErrors(false));

            return false;
        }
    } else {
        $errors = array_merge($errors, $uploader->getErrors(false));

        return false;
    }
}
