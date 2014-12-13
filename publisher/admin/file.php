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
 * @version         $Id: file.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';

$op = XoopsRequest::getString('op');

/**
 * @param bool $showmenu
 * @param int  $fileid
 * @param int  $itemid
 */
function publisher_editFile($showmenu = false, $fileid = 0, $itemid = 0)
{
    $publisher = PublisherPublisher::getInstance();
    include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    // if there is a parameter, and the id exists, retrieve data: we're editing a file
    if ($fileid != 0) {

        // Creating the File object
        $fileObj = $publisher->getHandler('file')->get($fileid);

        if ($fileObj->notLoaded()) {
            redirect_header("javascript:history.go(-1)", 1, _AM_PUBLISHER_NOFILESELECTED);
            exit();
        }

        if ($showmenu) {
            //publisher_adminMenu(2, _AM_PUBLISHER_FILE . " > " . _AM_PUBLISHER_EDITING);
        }

        echo "<br />\n";
        echo "<span style='color: #2F5376; font-weight: bold; font-size: 16px; margin: 6px 6px 0 0; '>" . _AM_PUBLISHER_FILE_EDITING . "</span>";
        echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . _AM_PUBLISHER_FILE_EDITING_DSC . "</span>";
        publisher_openCollapsableBar('editfile', 'editfileicon', _AM_PUBLISHER_FILE_INFORMATIONS);
    } else {
        // there's no parameter, so we're adding an item
        $fileObj = $publisher->getHandler('file')->create();
        $fileObj->setVar('itemid', $itemid);
        if ($showmenu) {
            //publisher_adminMenu(2, _AM_PUBLISHER_FILE . " > " . _AM_PUBLISHER_FILE_ADD);
        }
        echo "<span style='color: #2F5376; font-weight: bold; font-size: 16px; margin: 6px 6px 0 0; '>" . _AM_PUBLISHER_FILE_ADDING . "</span>";
        echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . _AM_PUBLISHER_FILE_ADDING_DSC . "</span>";
        publisher_openCollapsableBar('addfile', 'addfileicon', _AM_PUBLISHER_FILE_INFORMATIONS);
    }

    // FILES UPLOAD FORM
    $files_form = $fileObj->getForm();
    $files_form->display();

    if ($fileid != 0) {
        publisher_closeCollapsableBar('editfile', 'editfileicon');
    } else {
        publisher_closeCollapsableBar('addfile', 'addfileicon');
    }

}

$false = false;
/* -- Available operations -- */
switch ($op) {
    case "uploadfile";
        publisher_uploadFile(false, true, $false);
        exit;
        break;

    case "uploadanother";
        publisher_uploadFile(true, true, $false);
        exit;
        break;

    case "mod":
        $fileid = isset($_GET['fileid']) ? $_GET['fileid'] : 0;
        $itemid = isset($_GET['itemid']) ? $_GET['itemid'] : 0;
        if (($fileid == 0) && ($itemid == 0)) {
            redirect_header("javascript:history.go(-1)", 3, _AM_PUBLISHER_NOITEMSELECTED);
            exit();
        }

        publisher_cpHeader();
        include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

        publisher_editFile(true, $fileid, $itemid);
        break;

    case "modify":
        $fileid = isset($_POST['fileid']) ? intval($_POST['fileid']) : 0;

        // Creating the file object
        if ($fileid != 0) {
            $fileObj = $publisher->getHandler('file')->get($fileid);
        } else {
            $fileObj = $publisher->getHandler('file')->create();
        }

        // Putting the values in the file object
        $fileObj->setVar('name', $_POST['name']);
        $fileObj->setVar('description', $_POST['description']);
        $fileObj->setVar('status', intval($_POST['file_status']));

        // Storing the file
        if (!$fileObj->store()) {
            redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid(), 3, _AM_PUBLISHER_FILE_EDITING_ERROR . publisher_formatErrors($fileObj->getErrors()));
            exit;
        }

        redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid(), 2, _AM_PUBLISHER_FILE_EDITING_SUCCESS);
        exit();
        break;

    case "del":

        $fileid = isset($_POST['fileid']) ? intval($_POST['fileid']) : 0;
        $fileid = isset($_GET['fileid']) ? intval($_GET['fileid']) : $fileid;

        $fileObj = $publisher->getHandler('file')->get($fileid);

        $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : 0;
        $title = isset($_POST['title']) ? $_POST['title'] : '';

        if ($confirm) {
            if (!$publisher->getHandler('file')->delete($fileObj)) {
                redirect_header('item.php', 2, _AM_PUBLISHER_FILE_DELETE_ERROR);
                exit;
            }

            redirect_header('item.php', 2, sprintf(_AM_PUBLISHER_FILEISDELETED, $fileObj->name()));
            exit();
        } else {
            // no confirm: show deletion condition
            $fileid = isset($_GET['fileid']) ? intval($_GET['fileid']) : 0;

            publisher_cpHeader();
            xoops_confirm(array('op' => 'del', 'fileid' => $fileObj->fileid(), 'confirm' => 1, 'name' => $fileObj->name()), 'file.php', _AM_PUBLISHER_DELETETHISFILE . " <br />" . $fileObj->name() . " <br /> <br />", _AM_PUBLISHER_DELETE);
            xoops_cp_footer();
        }

        exit();
        break;

    case "default":
    default:
        publisher_cpHeader();
        //publisher_adminMenu(2, _AM_PUBLISHER_ITEMS);
        break;
}
xoops_cp_footer();
