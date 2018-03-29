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

require_once __DIR__ . '/header.php';
$helper = Publisher\Helper::getInstance();
$helper->loadLanguage('admin');
//xoops_loadLanguage('admin', PUBLISHER_DIRNAME);

$op     = Request::getString('op', Request::getString('op', '', 'GET'), 'POST');
$fileid = Request::getInt('fileid', Request::getInt('fileid', 0, 'GET'), 'POST');

if (0 == $fileid) {
    redirect_header('index.php', 2, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

$fileObj = $helper->getHandler('File')->get($fileid);

// if the selected item was not found, exit
if (!$fileObj) {
    redirect_header('index.php', 1, _NOPERM);
    //    exit();
}

$itemObj = $helper->getHandler('Item')->get($fileObj->getVar('itemid'));

// if the user does not have permission to modify this file, exit
if (!(Publisher\Utility::userIsAdmin() || Publisher\Utility::userIsModerator($itemObj) || (is_object($GLOBALS['xoopsUser']) && $fileObj->getVar('uid') == $GLOBALS['xoopsUser']->getVar('uid')))) {
    redirect_header('index.php', 1, _NOPERM);
    //    exit();
}

/* -- Available operations -- */
switch ($op) {
    case 'default':
    case 'mod':
        require_once $GLOBALS['xoops']->path('header.php');
        require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

        // FILES UPLOAD FORM
        $uploadForm = $fileObj->getForm();
        $uploadForm->display();
        break;

    case 'modify':
        $fileid = Request::getInt('fileid', 0, 'POST');

        // Creating the file object
        if (0 != $fileid) {
            $fileObj = $helper->getHandler('File')->get($fileid);
        } else {
            redirect_header('index.php', 1, _NOPERM);
            //            exit();
        }

        // Putting the values in the file object
        $fileObj->setVar('name', Request::getString('name'));
        $fileObj->setVar('description', Request::getString('description'));
        $fileObj->setVar('status', Request::getInt('file_status', 0, 'GET'));

        // attach file if any

        if ('' != Request::getString('item_upload_file', '', 'FILES')) {
            $oldfile = $fileObj->getFilePath();

            // Get available mimetypes for file uploading
            $allowed_mimetypes = $helper->getHandler('Mimetype')->getArrayByType();
            // TODO : display the available mimetypes to the user
            $errors = [];

            //            if ($helper->getConfig('perm_upload') && is_uploaded_file(Request::getArray('item_upload_file', array(), 'FILES')['tmp_name'])) {
            $temp = Request::getArray('item_upload_file', [], 'FILES');
            if ($helper->getConfig('perm_upload') && is_uploaded_file($temp['tmp_name'])) {
                if ($fileObj->checkUpload('item_upload_file', $allowed_mimetypes, $errors)) {
                    if ($fileObj->storeUpload('item_upload_file', $allowed_mimetypes, $errors)) {
                        unlink($oldfile);
                    }
                }
            }
        }

        if (!$helper->getHandler('File')->insert($fileObj)) {
            redirect_header('item.php?itemid=' . $fileObj->itemid(), 3, _AM_PUBLISHER_FILE_EDITING_ERROR . Publisher\Utility::formatErrors($fileObj->getErrors()));
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
            if (!$helper->getHandler('File')->delete($fileObj)) {
                redirect_header('item.php?itemid=' . $fileObj->itemid(), 2, _AM_PUBLISHER_FILE_DELETE_ERROR);
                //                exit;
            }

            redirect_header('item.php?itemid=' . $fileObj->itemid(), 2, sprintf(_AM_PUBLISHER_FILEISDELETED, $fileObj->name()));
        //            exit();
        } else {
            // no confirm: show deletion condition

            require_once $GLOBALS['xoops']->path('header.php');
            xoops_confirm(['op' => 'del', 'fileid' => $fileObj->fileid(), 'confirm' => 1, 'name' => $fileObj->name()], 'file.php', _AM_PUBLISHER_DELETETHISFILE . ' <br>' . $fileObj->name() . ' <br> <br>', _AM_PUBLISHER_DELETE);
            require_once $GLOBALS['xoops']->path('footer.php');
        }
        exit();
        break;
}
require_once $GLOBALS['xoops']->path('footer.php');
