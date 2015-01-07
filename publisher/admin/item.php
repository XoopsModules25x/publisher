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
 * @package         Admin
 * @subpackage      Action
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: item.php 10661 2013-01-04 19:22:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';

$itemid = XoopsRequest::getInt('itemid', 0, 'GET');
$op     = ($itemid > 0 || !empty(XoopsRequest::getString('editor', '', 'POST'))) ? 'mod' : '';
$op     = XoopsRequest::getString('op', $op, 'POST');

//if (!empty(XoopsRequest::getString('additem', '', 'POST'))) {
//    $op = 'additem';
//} elseif (!empty(XoopsRequest::getString('del', '', 'POST'))) {
//    $op = 'del';
//}

$op = !empty(XoopsRequest::getString('additem', '', 'POST')) ? 'additem' : (!empty(XoopsRequest::getString('del', '', 'POST')) ? 'del' : $op);

// Where shall we start ?
$submittedstartitem = XoopsRequest::getInt('submittedstartitem');
$publishedstartitem = XoopsRequest::getInt('publishedstartitem');
$offlinestartitem   = XoopsRequest::getInt('offlinestartitem');
$rejectedstartitem  = XoopsRequest::getInt('rejectedstartitem');

switch ($op) {
    case "clone":
        if ($itemid == 0) {
            $totalcategories = $publisher->getHandler('category')->getCategoriesCount(-1);
            if ($totalcategories == 0) {
                redirect_header("category.php?op=mod", 3, _AM_PUBLISHER_NEED_CATEGORY_ITEM);
//                exit();
            }
        }
        publisherCpHeader();
        publisher_editItem(true, $itemid, true);
        break;

    case "mod":
        if ($itemid == 0) {
            $totalcategories = $publisher->getHandler('category')->getCategoriesCount(-1);
            if ($totalcategories == 0) {
                redirect_header("category.php?op=mod", 3, _AM_PUBLISHER_NEED_CATEGORY_ITEM);
//                exit();
            }
        }

        publisherCpHeader();
        publisher_editItem(true, $itemid);
        break;

    case "additem":
        $redirect_msg = '';
        // Creating the item object
        if ($itemid != 0) {
            $itemObj = $publisher->getHandler('item')->get($itemid);
        } else {
            $itemObj = $publisher->getHandler('item')->create();
        }

        $itemObj->setVarsFromRequest();

        $old_status = $itemObj->status();
        $new_status = XoopsRequest::getInt('status', PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED); //_PUBLISHER_STATUS_NOTSET;

        switch ($new_status) {
            case PublisherConstantsInterface::PUBLISHER_STATUS_SUBMITTED:
                if (($old_status == PublisherConstantsInterface::PUBLISHER_STATUS_NOTSET)) {
                    $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                } else {
                    $error_msg = _AM_PUBLISHER_ITEMNOTCREATED;
                }
                $redirect_msg = _AM_PUBLISHER_ITEM_RECEIVED_NEED_APPROVAL;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED:
                if (($old_status == PublisherConstantsInterface::PUBLISHER_STATUS_NOTSET) || ($old_status == PublisherConstantsInterface::PUBLISHER_STATUS_SUBMITTED)) {
                    $redirect_msg = _AM_PUBLISHER_SUBMITTED_APPROVE_SUCCESS;
                    $notifToDo    = array(PublisherConstantsInterface::PUBLISHER_NOT_ITEM_PUBLISHED);
                } else {
                    $redirect_msg = _AM_PUBLISHER_PUBLISHED_MOD_SUCCESS;
                }
                $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_OFFLINE:
                if ($old_status == PublisherConstantsInterface::PUBLISHER_STATUS_NOTSET) {
                    $redirect_msg = _AM_PUBLISHER_OFFLINE_CREATED_SUCCESS;
                } else {
                    $redirect_msg = _AM_PUBLISHER_OFFLINE_MOD_SUCCESS;
                }
                $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_REJECTED:
                if ($old_status == PublisherConstantsInterface::PUBLISHER_STATUS_NOTSET) {
                    $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                } else {
                    $error_msg = _AM_PUBLISHER_ITEMNOTCREATED;
                }
                $redirect_msg = _AM_PUBLISHER_ITEM_REJECTED;
                break;
        }
        $itemObj->setVar('status', $new_status);

        // Storing the item
        if (!$itemObj->store()) {
            redirect_header("javascript:history.go(-1)", 3, $error_msg . publisherFormatErrors($itemObj->getErrors()));
//            exit;
        }

        // attach file if any
        if (!empty($item_upload_file = XoopsRequest::getArray('item_upload_file', '', 'FILES')) && $item_upload_file['name'] != "") {
            $file_upload_result = publisherUploadFile(false, false, $itemObj);
            if ($file_upload_result !== true) {
                redirect_header("javascript:history.go(-1)", 3, $file_upload_result);
//                exit;
            }
        }

        // Send notifications
        if (!empty($notifToDo)) {
            $itemObj->sendNotifications($notifToDo);
        }

        redirect_header("item.php", 2, $redirect_msg);

        break;

    case "del":
        $itemObj = $publisher->getHandler('item')->get($itemid);
        $confirm = XoopsRequest::getInt('confirm', 0, 'POST');

        if ($confirm) {
            if (!$publisher->getHandler('item')->delete($itemObj)) {
                redirect_header("item.php", 2, _AM_PUBLISHER_ITEM_DELETE_ERROR . publisherFormatErrors($itemObj->getErrors()));
//                exit();
            }
            redirect_header("item.php", 2, sprintf(_AM_PUBLISHER_ITEMISDELETED, $itemObj->title()));
//            exit();
        } else {
            xoops_cp_header();
            xoops_confirm(array('op' => 'del', 'itemid' => $itemObj->itemid(), 'confirm' => 1, 'name' => $itemObj->title()), 'item.php', _AM_PUBLISHER_DELETETHISITEM . " <br />'" . $itemObj->title() . "'. <br /> <br />", _AM_PUBLISHER_DELETE);
            xoops_cp_footer();
        }
        exit();
        break;

    case "default":
    default:
        publisherCpHeader();
        //publisher_adminMenu(2, _AM_PUBLISHER_ITEMS);
        xoops_load('XoopsPageNav');

        echo "<br />\n";
        echo "<form><div style=\"margin-bottom: 12px;\">";
        echo "<input type='button' name='button' onclick=\"location='item.php?op=mod'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
        echo "</div></form>";

        $orderBy   = 'datesub';
        $ascOrDesc = 'DESC';

        // Display Submited articles
        publisherOpenCollapsableBar('submiteditemstable', 'submiteditemsicon', _AM_PUBLISHER_SUBMISSIONSMNGMT, _AM_PUBLISHER_SUBMITTED_EXP);

        // Get the total number of submitted ITEM
        $totalitems = $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstantsInterface::PUBLISHER_STATUS_SUBMITTED));

        $itemsObj = $publisher->getHandler('item')->getAllSubmitted($publisher->getConfig('idxcat_perpage'), $submittedstartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo "<tr>";
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . "</strong></td>";
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . "</strong></td>";
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . "</strong></td>";
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . "</strong></td>";
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
        echo "</tr>";
        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->category();

                $approve = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/approve.gif' title='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' alt='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' /></a>&nbsp;";
                $clone   = '';
                $delete  = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "' /></a>";
                $modify  = "";

                echo "<tr>";
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . "</td>";
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . "</td>";
                echo "<td class='even' align='left'><a href='" . PUBLISHER_URL . "/item.php?itemid=" . $itemsObj[$i]->itemid() . "'>" . $itemsObj[$i]->title() . "</a></td>";
                echo "<td class='even' align='center'>" . $itemsObj[$i]->datesub() . "</td>";
                echo "<td class='even' align='center'> $approve $clone $modify $delete </td>";
                echo "</tr>";
            }
        } else {
            $itemid = 0;
            echo "<tr>";
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS_SUBMITTED . "</td>";
            echo "</tr>";
        }
        echo "</table>\n";
        echo "<br />\n";

        $pagenav = new XoopsPageNav($totalitems, $publisher->getConfig('idxcat_perpage'), $submittedstartitem, 'submittedstartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        publisherCloseCollapsableBar('submiteditemstable', 'submiteditemsicon');

        // Display Published articles
        publisherOpenCollapsableBar('item_publisheditemstable', 'item_publisheditemsicon', _AM_PUBLISHER_PUBLISHEDITEMS, _AM_PUBLISHER_PUBLISHED_DSC);

        // Get the total number of published ITEM
        $totalitems = $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED));

        $itemsObj = $publisher->getHandler('item')->getAllPublished($publisher->getConfig('idxcat_perpage'), $publishedstartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo "<tr>";
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . "</strong></td>";
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . "</strong></td>";
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . "</strong></td>";
        echo "<th width='30' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEM_VIEWS . "</strong></td>";
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . "</strong></td>";
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
        echo "</tr>";
        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->category();

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "' /></a>";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'/></a>";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "' /></a>";

                echo "<tr>";
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . "</td>";
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . "</td>";
                echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . "</td>";
                echo "<td class='even' align='center'>" . $itemsObj[$i]->counter() . "</td>";
                echo "<td class='even' align='center'>" . $itemsObj[$i]->datesub() . "</td>";
                echo "<td class='even' align='center'> $clone $modify $delete </td>";
                echo "</tr>";
            }
        } else {
            $itemid = 0;
            echo "<tr>";
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS . "</td>";
            echo "</tr>";
        }
        echo "</table>\n";
        echo "<br />\n";

        $pagenav = new XoopsPageNav($totalitems, $publisher->getConfig('idxcat_perpage'), $publishedstartitem, 'publishedstartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        publisherCloseCollapsableBar('item_publisheditemstable', 'item_publisheditemsicon');

        // Display Offline articles
        publisherOpenCollapsableBar('offlineitemstable', 'offlineitemsicon', _AM_PUBLISHER_ITEMS . " " . _CO_PUBLISHER_OFFLINE, _AM_PUBLISHER_OFFLINE_EXP);

        $totalitems = $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstantsInterface::PUBLISHER_STATUS_OFFLINE));

        $itemsObj = $publisher->getHandler('item')->getAllOffline($publisher->getConfig('idxcat_perpage'), $offlinestartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo "<tr>";
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . "</strong></td>";
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . "</strong></td>";
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . "</strong></td>";
        echo "<th width='30' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEM_VIEWS . "</strong></td>";
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . "</strong></td>";
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
        echo "</tr>";
        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->category();

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "' /></a>";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'/></a>";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "' /></a>";

                echo "<tr>";
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . "</td>";
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . "</td>";
                echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . "</td>";
                echo "<td class='even' align='center'>" . $itemsObj[$i]->counter() . "</td>";
                echo "<td class='even' align='center'>" . $itemsObj[$i]->datesub() . "</td>";
                echo "<td class='even' align='center'> $clone $modify $delete </td>";
                echo "</tr>";
            }
        } else {
            $itemid = 0;
            echo "<tr>";
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS_OFFLINE . "</td>";
            echo "</tr>";
        }
        echo "</table>\n";
        echo "<br />\n";

        $pagenav = new XoopsPageNav($totalitems, $publisher->getConfig('idxcat_perpage'), $offlinestartitem, 'offlinestartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        publisherCloseCollapsableBar('offlineitemstable', 'offlineitemsicon');

        // Display Rejected articles
        publisherOpenCollapsableBar('Rejecteditemstable', 'rejecteditemsicon', _AM_PUBLISHER_REJECTED_ITEM, _AM_PUBLISHER_REJECTED_ITEM_EXP, _AM_PUBLISHER_SUBMITTED_EXP);

        // Get the total number of Rejected ITEM
        $totalitems = $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstantsInterface::PUBLISHER_STATUS_REJECTED));
        $itemsObj = $publisher->getHandler('item')->getAllRejected($publisher->getConfig('idxcat_perpage'), $rejectedstartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo "<tr>";
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . "</strong></td>";
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . "</strong></td>";
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . "</strong></td>";
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . "</strong></td>";
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
        echo "</tr>";

    if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->category();

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "' /></a>";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'/></a>";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "' /></a>";

                echo "<tr>";
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . "</td>";
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . "</td>";
                echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . "</td>";
                echo "<td class='even' align='center'>" . $itemsObj[$i]->datesub() . "</td>";
                echo "<td class='even' align='center'> $clone $modify $delete </td>";
                echo "</tr>";
            }
        } else {
            $itemid = 0;
            echo "<tr>";
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS_REJECTED . "</td>";
            echo "</tr>";
        }
        echo "</table>\n";
        echo "<br />\n";

        $pagenav = new XoopsPageNav($totalitems, $publisher->getConfig('idxcat_perpage'), $rejectedstartitem, 'rejectedstartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        publisherCloseCollapsableBar('Rejecteditemstable', 'rejecteditemsicon');
        break;
}
xoops_cp_footer();

/**
 * @param bool $showmenu
 * @param int $itemid
 * @param bool $clone
 */
function publisher_editItem($showmenu = false, $itemid = 0, $clone = false)
{
    $publisher = PublisherPublisher::getInstance();
    global $publisher_current_page;

    xoops_load('XoopsFormLoader');

    $formTpl = new XoopsTpl();
    //publisher_submit.html

    // if there is a parameter, and the id exists, retrieve data: we're editing a item

    if ($itemid != 0) {

        // Creating the ITEM object
        $itemObj = $publisher->getHandler('item')->get($itemid);

        if (!$itemObj) {
            redirect_header("item.php", 1, _AM_PUBLISHER_NOITEMSELECTED);
//            exit();
        }

        if ($clone) {
            $itemObj->setNew();
            $itemObj->setVar('itemid', 0);
            $itemObj->setVar('status', PublisherConstantsInterface::PUBLISHER_STATUS_NOTSET);
            $itemObj->setVar('datesub', time());
        }

        switch ($itemObj->status()) {

            case PublisherConstantsInterface::PUBLISHER_STATUS_SUBMITTED:
                $breadcrumb_action1 = _CO_PUBLISHER_SUBMITTED;
                $breadcrumb_action2 = _AM_PUBLISHER_APPROVING;
                $page_title         = _AM_PUBLISHER_SUBMITTED_TITLE;
                $page_info          = _AM_PUBLISHER_SUBMITTED_INFO;
                $button_caption     = _AM_PUBLISHER_APPROVE;
                $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED:
                $breadcrumb_action1 = _CO_PUBLISHER_PUBLISHED;
                $breadcrumb_action2 = _AM_PUBLISHER_EDITING;
                $page_title         = _AM_PUBLISHER_PUBLISHEDEDITING;
                $page_info          = _AM_PUBLISHER_PUBLISHEDEDITING_INFO;
                $button_caption     = _AM_PUBLISHER_MODIFY;
                $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_OFFLINE:
                $breadcrumb_action1 = _CO_PUBLISHER_OFFLINE;
                $breadcrumb_action2 = _AM_PUBLISHER_EDITING;
                $page_title         = _AM_PUBLISHER_OFFLINEEDITING;
                $page_info          = _AM_PUBLISHER_OFFLINEEDITING_INFO;
                $button_caption     = _AM_PUBLISHER_MODIFY;
                $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_OFFLINE;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_REJECTED:
                $breadcrumb_action1 = _CO_PUBLISHER_REJECTED;
                $breadcrumb_action2 = _AM_PUBLISHER_REJECTED;
                $page_title         = _AM_PUBLISHER_REJECTED_EDIT;
                $page_info          = _AM_PUBLISHER_REJECTED_EDIT_INFO;
                $button_caption     = _AM_PUBLISHER_MODIFY;
                $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_REJECTED;
                break;

            case PublisherConstantsInterface::PUBLISHER_STATUS_NOTSET: // Then it's a clone...
                $breadcrumb_action1 = _AM_PUBLISHER_ITEMS;
                $breadcrumb_action2 = _AM_PUBLISHER_CLONE_NEW;
                $button_caption     = _AM_PUBLISHER_CREATE;
                $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED;
                $page_title         = _AM_PUBLISHER_ITEM_DUPLICATING;
                $page_info          = _AM_PUBLISHER_ITEM_DUPLICATING_DSC;
                break;

            case "default" :
            default :
                $breadcrumb_action1 = _AM_PUBLISHER_ITEMS;
                $breadcrumb_action2 = _AM_PUBLISHER_EDITING;
                $page_title         = _AM_PUBLISHER_PUBLISHEDEDITING;
                $page_info          = _AM_PUBLISHER_PUBLISHEDEDITING_INFO;
                $button_caption     = _AM_PUBLISHER_MODIFY;
                $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED;
                break;
        }

        $categoryObj = $itemObj->category();

        if ($showmenu) {
            //publisher_adminMenu(2, $breadcrumb_action1 . " > " . $breadcrumb_action2);
        }

        echo "<br />\n";
        publisherOpenCollapsableBar('edititemtable', 'edititemicon', $page_title, $page_info);

        if (!$clone) {
            echo "<form><div style=\"margin-bottom: 10px;\">";
            echo "<input type='button' name='button' onclick=\"location='item.php?op=clone&itemid=" . $itemObj->itemid() . "'\" value='" . _AM_PUBLISHER_CLONE_ITEM . "'>&nbsp;&nbsp;";
            echo "</div></form>";
        }

    } else {
        // there's no parameter, so we're adding an item

        $itemObj = $publisher->getHandler('item')->create();
        $itemObj->setVarsFromRequest();

        $categoryObj        = $publisher->getHandler('category')->create();
        $breadcrumb_action1 = _AM_PUBLISHER_ITEMS;
        $breadcrumb_action2 = _AM_PUBLISHER_CREATINGNEW;
        $button_caption     = _AM_PUBLISHER_CREATE;
        $new_status         = PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED;

        if ($showmenu) {
            //publisher_adminMenu(2, $breadcrumb_action1 . " > " . $breadcrumb_action2);
        }

        $sel_categoryid = XoopsRequest::getInt('categoryid', 0, 'GET');
        $categoryObj->setVar('categoryid', $sel_categoryid);

        publisherOpenCollapsableBar('createitemtable', 'createitemicon', _AM_PUBLISHER_ITEM_CREATING, _AM_PUBLISHER_ITEM_CREATING_DSC);
    }

    $sform = $itemObj->getForm(_AM_PUBLISHER_ITEMS);
    $sform->assign($formTpl);
    $formTpl->display('db:publisher_submit.tpl');

    publisherCloseCollapsableBar('edititemtable', 'edititemicon');

    publisherOpenCollapsableBar('pagewraptable', 'pagewrapicon', _AM_PUBLISHER_PAGEWRAP, _AM_PUBLISHER_PAGEWRAPDSC);

    $dir = publisherGetUploadDir(true, 'content');

    if (!preg_match('/777/i', decoct(fileperms($dir)))) {
        echo "<span style='color:#ff0000;'><h4>" . _AM_PUBLISHER_PERMERROR . "</h4></span>";
    }

    // Upload File
    echo "<form name='form_name2' id='form_name2' action='pw_upload_file.php' method='post' enctype='multipart/form-data'>";
    echo "<table cellspacing='1' width='100%' class='outer'>";
    echo "<tr><th colspan='2'>" . _AM_PUBLISHER_UPLOAD_FILE . "</th></tr>";
    echo "<tr valign='top' align='left'><td class='head'>" . _AM_PUBLISHER_SEARCH_PW . "</td><td class='even'><input type='file' name='fileupload' id='fileupload' size='30' /></td></tr>";
    echo "<tr valign='top' align='left'><td class='head'><input type='hidden' name='MAX_FILE_SIZE' id='op' value='500000' /></td><td class='even'><input type='submit' name='submit' value='" . _AM_PUBLISHER_UPLOAD . "' /></td></tr>";
    echo "<input type='hidden' name='backto' value='$publisher_current_page'/>";
    echo "</table>";
    echo "</form>";

    // Delete File
    $form = new XoopsThemeForm(_CO_PUBLISHER_DELETEFILE, "form_name", "pw_delete_file.php");

    $pWrap_select = new XoopsFormSelect(publisherGetUploadDir(true, 'content'), "address");
    $folder       = dir($dir);
    while (($file = $folder->read()) !== false) {
        if ($file != "." && $file != "..") {
            $pWrap_select->addOption($file, $file);
        }
    }
    $folder->close();
    $form->addElement($pWrap_select);

    $delfile = "delfile";
    $form->addElement(new XoopsFormHidden('op', $delfile));
    $submit = new XoopsFormButton("", "submit", _AM_PUBLISHER_BUTTON_DELETE, "submit");
    $form->addElement($submit);

    $form->addElement(new XoopsFormHidden('backto', $publisher_current_page));
    $form->display();

    publisherCloseCollapsableBar('pagewraptable', 'pagewrapicon');

}
