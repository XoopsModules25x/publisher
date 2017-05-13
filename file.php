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

use \Xmf\Request;

require_once __DIR__ . '/header.php';
xoops_loadLanguage('admin', PUBLISHER_DIRNAME);

$op     = Request::getString('op', Request::getString('op', '', 'GET'), 'POST');
$fileid = Request::getInt('fileid', Request::getInt('fileid', 0, 'GET'), 'POST');

if ($fileid == 0) {
    redirect_header('index.php', 2, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

$fileObj = $publisher->getHandler('file')->get($fileid);

// if the selected item was not found, exit
if (!$fileObj) {
    redirect_header('index.php', 1, _NOPERM);
    //    exit();
}

$itemObj = $publisher->getHandler('item')->get($fileObj->getVar('itemid'));

// if the user does not have permission to modify this file, exit
if (!(PublisherUtility::userIsAdmin() || PublisherUtility::userIsModerator($itemObj) || (is_object($GLOBALS['xoopsUser']) && $fileObj->getVar('uid') == $GLOBALS['xoopsUser']->getVar('uid')))) {
    redirect_header('index.php', 1, _NOPERM);
    //    exit();
}

/* -- Available operations -- */
switch ($op) {
    case 'default':
    case 'mod':
        include_once $GLOBALS['xoops']->path('header.php');
        include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

        // FILES UPLOAD FORM
        $uploadForm = $fileObj->getForm();
        $uploadForm->display();
        break;

    case 'modify':
        $fileid = Request::getInt('fileid', 0, 'POST');

        // Creating the file object
        if ($fileid != 0) {
            $fileObj = $publisher->getHandler('file')->get($fileid);
        } else {
            redirect_header('index.php', 1, _NOPERM);
            //            exit();
        }

        // Putting the values in the file object
        $fileObj->setVar('name', Request::getString('name'));
        $fileObj->setVar('description', Request::getString('description'));
        $fileObj->setVar('status', Request::getInt('file_status', 0, 'GET'));

        // attach file if any

        if (Request::getString('item_upload_file', '', 'FILES') != '') {
            $oldfile = $fileObj->getFilePath();

            // Get available mimetypes for file uploading
            $allowed_mimetypes = $publisher->getHandler('mimetype')->getArrayByType();
            // TODO : display the available mimetypes to the user
            $errors = array();

            //            if ($publisher->getConfig('perm_upload') && is_uploaded_file(Request::getArray('item_upload_file', array(), 'FILES')['tmp_name'])) {
            $temp = Request::getArray('item_upload_file', array(), 'FILES');
            if ($publisher->getConfig('perm_upload') && is_uploaded_file($temp['tmp_name'])) {
                if ($fileObj->checkUpload('item_upload_file', $allowed_mimetypes, $errors)) {
                    if ($fileObj->storeUpload('item_upload_file', $allowed_mimetypes, $errors)) {
                        unlink($oldfile);
                    }
                }
            }
        }

        if (!$publisher->getHandler('file')->insert($fileObj)) {
            redirect_header('item.php?itemid=' . $fileObj->itemid(), 3, _AM_PUBLISHER_FILE_EDITING_ERROR . PublisherUtility::formatErrors($fileObj->getErrors()));
            //            exit;
        }

        redirect_header('item.php?itemid=' . $fileObj->itemid(), 2, _AM_PUBLISHER_FILE_EDITING_SUCCESS);
        //        exit();
        break;

    case 'clear':
        //mb        echo 'my time is now ' . now;
        break;

    case 'del':
        $confirm = Request::getInt('confirm', '', 'POST');

        if ($confirm) {
            if (!$publisher->getHandler('file')->delete($fileObj)) {
                redirect_header('item.php?itemid=' . $fileObj->itemid(), 2, _AM_PUBLISHER_FILE_DELETE_ERROR);
                //                exit;
            }

            redirect_header('item.php?itemid=' . $fileObj->itemid(), 2, sprintf(_AM_PUBLISHER_FILEISDELETED, $fileObj->name()));
            //            exit();
        } else {
            // no confirm: show deletion condition

            include_once $GLOBALS['xoops']->path('header.php');
            xoops_confirm(array('op' => 'del', 'fileid' => $fileObj->fileid(), 'confirm' => 1, 'name' => $fileObj->name()), 'file.php',
                          _AM_PUBLISHER_DELETETHISFILE . ' <br>' . $fileObj->name() . ' <br> <br>', _AM_PUBLISHER_DELETE);
            include_once $GLOBALS['xoops']->path('footer.php');
        }
        exit();
        break;
}
include_once $GLOBALS['xoops']->path('footer.php');
