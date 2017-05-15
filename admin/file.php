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

require_once __DIR__ . '/admin_header.php';

$op = Request::getString('op');

/**
 * @param bool $showmenu
 * @param int  $fileid
 * @param int  $itemid
 */
function publisher_editFile($showmenu = false, $fileid = 0, $itemid = 0)
{
    $publisher = PublisherPublisher::getInstance();
    include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

    // if there is a parameter, and the id exists, retrieve data: we're editing a file
    if ($fileid != 0) {
        // Creating the File object
        $fileObj = $publisher->getHandler('file')->get($fileid);

        if ($fileObj->notLoaded()) {
            redirect_header('javascript:history.go(-1)', 1, _AM_PUBLISHER_NOFILESELECTED);
            //            exit();
        }

        echo "<br>\n";
        echo "<span style='color: #2F5376; font-weight: bold; font-size: 16px; margin: 6px 6px 0 0; '>" . _AM_PUBLISHER_FILE_EDITING . '</span>';
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_FILE_EDITING_DSC . '</span>';
        PublisherUtility::openCollapsableBar('editfile', 'editfileicon', _AM_PUBLISHER_FILE_INFORMATIONS);
    } else {
        // there's no parameter, so we're adding an item
        $fileObj = $publisher->getHandler('file')->create();
        $fileObj->setVar('itemid', $itemid);
        echo "<span style='color: #2F5376; font-weight: bold; font-size: 16px; margin: 6px 6px 0 0; '>" . _AM_PUBLISHER_FILE_ADDING . '</span>';
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_FILE_ADDING_DSC . '</span>';
        PublisherUtility::openCollapsableBar('addfile', 'addfileicon', _AM_PUBLISHER_FILE_INFORMATIONS);
    }

    // FILES UPLOAD FORM
    $uploadForm = $fileObj->getForm();
    $uploadForm->display();

    if ($fileid != 0) {
        PublisherUtility::closeCollapsableBar('editfile', 'editfileicon');
    } else {
        PublisherUtility::closeCollapsableBar('addfile', 'addfileicon');
    }
}

$false = false;
/* -- Available operations -- */
switch ($op) {
    case 'uploadfile':
        PublisherUtility::uploadFile(false, true, $false);
        exit;
        break;

    case 'uploadanother':
        PublisherUtility::uploadFile(true, true, $false);
        exit;
        break;

    case 'mod':
        $fileid = Request::getInt('fileid', 0, 'GET');
        $itemid = Request::getInt('itemid', 0, 'GET');
        if (($fileid == 0) && ($itemid == 0)) {
            redirect_header('javascript:history.go(-1)', 3, _AM_PUBLISHER_NOITEMSELECTED);
            //            exit();
        }

        PublisherUtility::cpHeader();
        include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

        publisher_editFile(true, $fileid, $itemid);
        break;

    case 'modify':
        $fileid = Request::getInt('fileid', 0, 'POST');

        // Creating the file object
        if ($fileid != 0) {
            $fileObj = $publisher->getHandler('file')->get($fileid);
        } else {
            $fileObj = $publisher->getHandler('file')->create();
        }

        // Putting the values in the file object
        $fileObj->setVar('name', Request::getString('name', '', 'POST'));
        $fileObj->setVar('description', Request::getString('description', '', 'POST'));
        $fileObj->setVar('status', Request::getInt('status', 0, 'POST'));

        // Storing the file
        if (!$fileObj->store()) {
            redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 3, _AM_PUBLISHER_FILE_EDITING_ERROR . PublisherUtility::formatErrors($fileObj->getErrors()));
            //            exit;
        }

        redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 2, _AM_PUBLISHER_FILE_EDITING_SUCCESS);
        //        exit();
        break;

    case 'del':
        $fileid = Request::getInt('fileid', 0, 'POST');
        $fileid = Request::getInt('fileid', $fileid, 'GET');

        $fileObj = $publisher->getHandler('file')->get($fileid);

        $confirm = Request::getInt('confirm', 0, 'POST');
        $title   = Request::getString('title', '', 'POST');

        if ($confirm) {
            if (!$publisher->getHandler('file')->delete($fileObj)) {
                redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 2, _AM_PUBLISHER_FILE_DELETE_ERROR);
                //                exit;
            }

            redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 2, sprintf(_AM_PUBLISHER_FILEISDELETED, $fileObj->name()));
            //            exit();
        } else {
            // no confirm: show deletion condition
            $fileid = Request::getInt('fileid', 0, 'GET');

            PublisherUtility::cpHeader();
            xoops_confirm(array('op' => 'del', 'fileid' => $fileObj->fileid(), 'confirm' => 1, 'name' => $fileObj->name()), 'file.php',
                          _AM_PUBLISHER_DELETETHISFILE . ' <br>' . $fileObj->name() . ' <br> <br>', _AM_PUBLISHER_DELETE);
            xoops_cp_footer();
        }

        exit();
        break;

    case 'default':
    default:
        PublisherUtility::cpHeader();
        //publisher_adminMenu(2, _AM_PUBLISHER_ITEMS);
        break;
}
require_once __DIR__ . '/admin_footer.php';
