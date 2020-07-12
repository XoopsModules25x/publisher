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
use XoopsModules\Publisher\{
    Helper,
    Utility
};

require_once __DIR__ . '/admin_header.php';

$op = Request::getString('op');

/**
 * @param bool $showmenu
 * @param int  $fileid
 * @param int  $itemId
 */
function publisher_editFile($showmenu = false, $fileid = 0, $itemId = 0)
{
    $helper = Helper::getInstance();
    require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

    // if there is a parameter, and the id exists, retrieve data: we're editing a file
    if (0 != $fileid) {
        // Creating the File object
        /** @var \XoopsModules\Publisher\File $fileObj */
        $fileObj = $helper->getHandler('File')->get($fileid);

        if ($fileObj->notLoaded()) {
            redirect_header('<script>javascript:history.go(-1)</script>', 1, _AM_PUBLISHER_NOFILESELECTED);
        }

        echo "<br>\n";
        echo "<span style='color: #2F5376; font-weight: bold; font-size: 16px; margin: 6px 6px 0 0; '>" . _AM_PUBLISHER_FILE_EDITING . '</span>';
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_FILE_EDITING_DSC . '</span>';
        Utility::openCollapsableBar('editfile', 'editfileicon', _AM_PUBLISHER_FILE_INFORMATIONS);
    } else {
        // there's no parameter, so we're adding an item
        $fileObj = $helper->getHandler('File')->create();
        $fileObj->setVar('itemid', $itemId);
        echo "<span style='color: #2F5376; font-weight: bold; font-size: 16px; margin: 6px 6px 0 0; '>" . _AM_PUBLISHER_FILE_ADDING . '</span>';
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_FILE_ADDING_DSC . '</span>';
        Utility::openCollapsableBar('addfile', 'addfileicon', _AM_PUBLISHER_FILE_INFORMATIONS);
    }

    // FILES UPLOAD FORM
    /** @var Publisher\File $fileObj */
    $uploadForm = $fileObj->getForm();
    $uploadForm->display();

    if (0 != $fileid) {
        Utility::closeCollapsableBar('editfile', 'editfileicon');
    } else {
        Utility::closeCollapsableBar('addfile', 'addfileicon');
    }
}

$false = false;
/* -- Available operations -- */
switch ($op) {
    case 'uploadfile':
        Utility::uploadFile(false, true, $false);
        exit;
    case 'uploadanother':
        Utility::uploadFile(true, true, $false);
        exit;
    case 'mod':
        $fileid = Request::getInt('fileid', 0, 'GET');
        $itemId = Request::getInt('itemid', 0, 'GET');
        if ((0 == $fileid) && (0 == $itemId)) {
            redirect_header('<script>javascript:history.go(-1)</script>', 3, _AM_PUBLISHER_NOITEMSELECTED);
        }

        Utility::cpHeader();
        require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

        publisher_editFile(true, $fileid, $itemId);
        break;
    case 'modify':
        $fileid = Request::getInt('fileid', 0, 'POST');

        // Creating the file object
        if (0 != $fileid) {
            $fileObj = $helper->getHandler('File')->get($fileid);
        } else {
            $fileObj = $helper->getHandler('File')->create();
        }

        // Putting the values in the file object
        $fileObj->setVar('name', Request::getString('name', '', 'POST'));
        $fileObj->setVar('description', Request::getString('description', '', 'POST'));
        $fileObj->setVar('status', Request::getInt('status', 0, 'POST'));

        // Storing the file
        if (!$fileObj->store()) {
            redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 3, _AM_PUBLISHER_FILE_EDITING_ERROR . Utility::formatErrors($fileObj->getErrors()));
        }

        redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 2, _AM_PUBLISHER_FILE_EDITING_SUCCESS);
        break;
    case 'del':
        $fileid = Request::getInt('fileid', 0, 'POST');
        $fileid = Request::getInt('fileid', $fileid, 'GET');

        $fileObj = $helper->getHandler('File')->get($fileid);

        $confirm = Request::getInt('confirm', 0, 'POST');
        $title   = Request::getString('title', '', 'POST');

        if ($confirm) {
            if (!$helper->getHandler('File')->delete($fileObj)) {
                redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 2, _AM_PUBLISHER_FILE_DELETE_ERROR);
            }

            redirect_header('item.php?op=mod&itemid=' . $fileObj->itemid() . '#tab_2', 2, sprintf(_AM_PUBLISHER_FILEISDELETED, $fileObj->name()));
        } else {
            // no confirm: show deletion condition
            $fileid = Request::getInt('fileid', 0, 'GET');

            Utility::cpHeader();
            xoops_confirm(['op' => 'del', 'fileid' => $fileObj->fileid(), 'confirm' => 1, 'name' => $fileObj->name()], 'file.php', _AM_PUBLISHER_DELETETHISFILE . ' <br>' . $fileObj->name() . ' <br> <br>', _AM_PUBLISHER_DELETE);
            xoops_cp_footer();
        }

        exit();
    case 'default':
    default:
        Utility::cpHeader();
        //publisher_adminMenu(2, _AM_PUBLISHER_ITEMS);
        break;
}
require_once __DIR__ . '/admin_footer.php';
