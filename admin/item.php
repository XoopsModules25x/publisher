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
 */

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/admin_header.php';

// all post requests should have a valid token
if ('POST' === Request::getMethod() && !$GLOBALS['xoopsSecurity']->check()) {
    redirect_header('item.php', 2, _CO_PUBLISHER_BAD_TOKEN);
}

$itemid = Request::getInt('itemid', Request::getInt('itemid', 0, 'POST'), 'GET');
$op     = ($itemid > 0 || Request::getString('editor', '', 'POST')) ? 'mod' : '';
//$op     = Request::getString('op', $op, 'GET');

$op = Request::getString('op', Request::getString('op', $op, 'POST'), 'GET');

$op = Request::getString('additem', '', 'POST') ? 'additem' : (Request::getString('del', '', 'POST') ? 'del' : $op);

// Where shall we start ?
$submittedstartitem = Request::getInt('submittedstartitem', Request::getInt('submittedstartitem', 0, 'GET'), 'POST');
$publishedstartitem = Request::getInt('publishedstartitem', Request::getInt('publishedstartitem', 0, 'GET'), 'POST');
$offlinestartitem   = Request::getInt('offlinestartitem', Request::getInt('offlinestartitem', 0, 'GET'), 'POST');
$rejectedstartitem  = Request::getInt('rejectedstartitem', Request::getInt('submittedstartitem', 0, 'GET'), 'POST');

switch ($op) {
    case 'clone':
        if (0 == $itemid) {
            $totalcategories = $helper->getHandler('Category')->getCategoriesCount(-1);
            if (0 == $totalcategories) {
                redirect_header('category.php?op=mod', 3, _AM_PUBLISHER_NEED_CATEGORY_ITEM);
                //                exit();
            }
        }
        Publisher\Utility::cpHeader();
        publisher_editItem(true, $itemid, true);
        break;

    case 'mod':
        if (0 == $itemid) {
            $totalcategories = $helper->getHandler('Category')->getCategoriesCount(-1);
            if (0 == $totalcategories) {
                redirect_header('category.php?op=mod', 3, _AM_PUBLISHER_NEED_CATEGORY_ITEM);
                //                exit();
            }
        }

        Publisher\Utility::cpHeader();
        publisher_editItem(true, $itemid);
        break;

    case 'additem':
        $redirect_msg = $error_msg = '';
        // Creating the item object
        if (0 != $itemid) {
            $itemObj = $helper->getHandler('Item')->get($itemid);
        } else {
            $itemObj = $helper->getHandler('Item')->create();
        }

        $itemObj->setVarsFromRequest();

        $old_status = $itemObj->status();
        $newStatus  = Request::getInt('status', Constants::PUBLISHER_STATUS_PUBLISHED); //_PUBLISHER_STATUS_NOTSET;

        switch ($newStatus) {
            case Constants::PUBLISHER_STATUS_SUBMITTED:
                $error_msg = _AM_PUBLISHER_ITEMNOTCREATED;
                if ($old_status == Constants::PUBLISHER_STATUS_NOTSET) {
                    $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                }
                $redirect_msg = _AM_PUBLISHER_ITEM_RECEIVED_NEED_APPROVAL;
                break;

            case Constants::PUBLISHER_STATUS_PUBLISHED:
                if (($old_status == Constants::PUBLISHER_STATUS_NOTSET) || ($old_status == Constants::PUBLISHER_STATUS_SUBMITTED)) {
                    $redirect_msg = _AM_PUBLISHER_SUBMITTED_APPROVE_SUCCESS;
                    $notifToDo    = [Constants::PUBLISHER_NOTIFY_ITEM_PUBLISHED];
                } else {
                    $redirect_msg = _AM_PUBLISHER_PUBLISHED_MOD_SUCCESS;
                }
                $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                break;

            case Constants::PUBLISHER_STATUS_OFFLINE:
                $redirect_msg = _AM_PUBLISHER_OFFLINE_MOD_SUCCESS;
                if ($old_status == Constants::PUBLISHER_STATUS_NOTSET) {
                    $redirect_msg = _AM_PUBLISHER_OFFLINE_CREATED_SUCCESS;
                }
                $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                break;

            case Constants::PUBLISHER_STATUS_REJECTED:
                $error_msg = _AM_PUBLISHER_ITEMNOTCREATED;
                if ($old_status == Constants::PUBLISHER_STATUS_NOTSET) {
                    $error_msg = _AM_PUBLISHER_ITEMNOTUPDATED;
                }
                $redirect_msg = _AM_PUBLISHER_ITEM_REJECTED;
                break;
        }
        $itemObj->setVar('status', $newStatus);

        // Storing the item
        if (!$itemObj->store()) {
            redirect_header('javascript:history.go(-1)', 3, $error_msg . Publisher\Utility::formatErrors($itemObj->getErrors()));
            //            exit;
        }

        // attach file if any
        if (($item_upload_file = Request::getArray('item_upload_file', '', 'FILES')) && '' !== $item_upload_file['name']) {
            $file_upload_result = Publisher\Utility::uploadFile(false, false, $itemObj);
            if (true !== $file_upload_result) {
                redirect_header('javascript:history.go(-1)', 3, $file_upload_result);
                //                exit;
            }
        }

        // Send notifications
        if ($notifToDo) {
            $itemObj->sendNotifications($notifToDo);
        }

        redirect_header('item.php', 2, $redirect_msg);

        break;

    case 'del':
        $itemObj = $helper->getHandler('Item')->get($itemid);
        $confirm = Request::getInt('confirm', 0, 'POST');

        if ($confirm) {
            if (!$helper->getHandler('Item')->delete($itemObj)) {
                redirect_header('item.php', 2, _AM_PUBLISHER_ITEM_DELETE_ERROR . Publisher\Utility::formatErrors($itemObj->getErrors()));
                //                exit();
            }
            redirect_header('item.php', 2, sprintf(_AM_PUBLISHER_ITEMISDELETED, $itemObj->getTitle()));
        //            exit();
        } else {
            xoops_cp_header();
            xoops_confirm(['op' => 'del', 'itemid' => $itemObj->itemid(), 'confirm' => 1, 'name' => $itemObj->getTitle()], 'item.php', _AM_PUBLISHER_DELETETHISITEM . " <br>'" . $itemObj->getTitle() . "'. <br> <br>", _AM_PUBLISHER_DELETE);
            xoops_cp_footer();
        }
        exit();
        break;

    case 'default':
    default:
        Publisher\Utility::cpHeader();
        //publisher_adminMenu(2, _AM_PUBLISHER_ITEMS);
        xoops_load('XoopsPageNav');

        echo "<br>\n";
        echo '<form><div style="margin-bottom: 12px;">';
        echo "<input type='button' name='button' onclick=\"location='item.php?op=mod'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
        echo '</div></form>';

        $orderBy   = 'datesub';
        $ascOrDesc = 'DESC';

        // Display Submited articles
        Publisher\Utility::openCollapsableBar('submiteditemstable', 'submiteditemsicon', _AM_PUBLISHER_SUBMISSIONSMNGMT, _AM_PUBLISHER_SUBMITTED_EXP);

        // Get the total number of submitted ITEM
        $totalitems = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_SUBMITTED]);

        $itemsObj = $helper->getHandler('Item')->getAllSubmitted($helper->getConfig('idxcat_perpage'), $submittedstartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo '<tr>';
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . '</strong></td>';
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . '</strong></td>';
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_AUTHOR . '</strong></td>';
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
        echo '</tr>';
        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->getCategory();

                $approve = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/approve.gif' title='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' alt='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "'></a>&nbsp;";
                $clone   = '';
                $delete  = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'></a>";
                $modify  = '';

                echo '<tr>';
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . '</td>';
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . '</td>';
                echo "<td class='even' align='left'><a href='" . PUBLISHER_URL . '/item.php?itemid=' . $itemsObj[$i]->itemid() . "'>" . $itemsObj[$i]->getTitle() . '</a></td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getDatesub() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getWho() . '</td>';
                echo "<td class='even' align='center'> $approve $clone $modify $delete </td>";
                echo '</tr>';
            }
        } else {
            $itemid = 0;
            echo '<tr>';
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS_SUBMITTED . '</td>';
            echo '</tr>';
        }
        echo "</table>\n";
        echo "<br>\n";

        $pagenav = new \XoopsPageNav($totalitems, $helper->getConfig('idxcat_perpage'), $submittedstartitem, 'submittedstartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        Publisher\Utility::closeCollapsableBar('submiteditemstable', 'submiteditemsicon');

        // Display Published articles
        Publisher\Utility::openCollapsableBar('item_publisheditemstable', 'item_publisheditemsicon', _AM_PUBLISHER_PUBLISHEDITEMS, _AM_PUBLISHER_PUBLISHED_DSC);

        // Get the total number of published ITEM
        $totalitems = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_PUBLISHED]);

        $itemsObj = $helper->getHandler('Item')->getAllPublished($helper->getConfig('idxcat_perpage'), $publishedstartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo '<tr>';
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . '</strong></td>';
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . '</strong></td>';
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . '</strong></td>';
        echo "<th width='30' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEM_VIEWS . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_AUTHOR . '</strong></td>';
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
        echo '</tr>';
        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->getCategory();

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "'></a>";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'></a>";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "'></a>";

                echo '<tr>';
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . '</td>';
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . '</td>';
                echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->counter() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getDatesub() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getWho() . '</td>';
                echo "<td class='even' align='center'> $modify $delete $clone</td>";
                echo '</tr>';
            }
        } else {
            $itemid = 0;
            echo '<tr>';
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS . '</td>';
            echo '</tr>';
        }
        echo "</table>\n";
        echo "<br>\n";

        $pagenav = new \XoopsPageNav($totalitems, $helper->getConfig('idxcat_perpage'), $publishedstartitem, 'publishedstartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        Publisher\Utility::closeCollapsableBar('item_publisheditemstable', 'item_publisheditemsicon');

        // Display Offline articles
        Publisher\Utility::openCollapsableBar('offlineitemstable', 'offlineitemsicon', _AM_PUBLISHER_ITEMS . ' ' . _CO_PUBLISHER_OFFLINE, _AM_PUBLISHER_OFFLINE_EXP);

        $totalitems = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_OFFLINE]);

        $itemsObj = $helper->getHandler('Item')->getAllOffline($helper->getConfig('idxcat_perpage'), $offlinestartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo '<tr>';
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . '</strong></td>';
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . '</strong></td>';
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . '</strong></td>';
        echo "<th width='30' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEM_VIEWS . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_AUTHOR . '</strong></td>';

        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
        echo '</tr>';
        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->getCategory();

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "'></a>";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'></a>";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "'></a>";

                echo '<tr>';
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . '</td>';
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . '</td>';
                echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->counter() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getDatesub() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getWho() . '</td>';
                echo "<td class='even' align='center'>  $modify $delete $clone</td>";
                echo '</tr>';
            }
        } else {
            $itemid = 0;
            echo '<tr>';
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS_OFFLINE . '</td>';
            echo '</tr>';
        }
        echo "</table>\n";
        echo "<br>\n";

        $pagenav = new \XoopsPageNav($totalitems, $helper->getConfig('idxcat_perpage'), $offlinestartitem, 'offlinestartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        Publisher\Utility::closeCollapsableBar('offlineitemstable', 'offlineitemsicon');

        // Display Rejected articles
        Publisher\Utility::openCollapsableBar('Rejecteditemstable', 'rejecteditemsicon', _AM_PUBLISHER_REJECTED_ITEM, _AM_PUBLISHER_REJECTED_ITEM_EXP, _AM_PUBLISHER_SUBMITTED_EXP);

        // Get the total number of Rejected ITEM
        $totalitems = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_REJECTED]);
        $itemsObj   = $helper->getHandler('Item')->getAllRejected($helper->getConfig('idxcat_perpage'), $rejectedstartitem, -1, $orderBy, $ascOrDesc);

        $totalItemsOnPage = count($itemsObj);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo '<tr>';
        echo "<th width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . '</strong></td>';
        echo "<th width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . '</strong></td>';
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_TITLE . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . '</strong></td>';
        echo "<th width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_AUTHOR . '</strong></td>';
        echo "<th width='80' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
        echo '</tr>';

        if ($totalitems > 0) {
            for ($i = 0; $i < $totalItemsOnPage; ++$i) {
                $categoryObj = $itemsObj[$i]->getCategory();

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "'></a>";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'></a>";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "'></a>";

                echo '<tr>';
                echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . '</td>';
                echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . '</td>';
                echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getDatesub() . '</td>';
                echo "<td class='even' align='center'>" . $itemsObj[$i]->getWho() . '</td>';
                echo "<td class='even' align='center'> $modify $delete $clone</td>";
                echo '</tr>';
            }
        } else {
            $itemid = 0;
            echo '<tr>';
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS_REJECTED . '</td>';
            echo '</tr>';
        }
        echo "</table>\n";
        echo "<br>\n";

        $pagenav = new \XoopsPageNav($totalitems, $helper->getConfig('idxcat_perpage'), $rejectedstartitem, 'rejectedstartitem');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

        Publisher\Utility::closeCollapsableBar('Rejecteditemstable', 'rejecteditemsicon');
        break;
}
require_once __DIR__ . '/admin_footer.php';

/**
 * @param bool $showmenu
 * @param int  $itemid
 * @param bool $clone
 */
function publisher_editItem($showmenu = false, $itemid = 0, $clone = false)
{
    $helper = Publisher\Helper::getInstance();
    global $publisherCurrentPage;

    xoops_load('XoopsFormLoader');

    $formTpl = new \XoopsTpl();
    //publisher_submit.html

    // if there is a parameter, and the id exists, retrieve data: we're editing a item

    if (0 != $itemid) {
        // Creating the ITEM object
        $itemObj = $helper->getHandler('Item')->get($itemid);

        if (!$itemObj) {
            redirect_header('item.php', 1, _AM_PUBLISHER_NOITEMSELECTED);
            //            exit();
        }

        if ($clone) {
            $itemObj->setNew();
            $itemObj->setVar('itemid', 0);
            $itemObj->setVar('status', Constants::PUBLISHER_STATUS_NOTSET);
            $itemObj->setVar('datesub', time());
        }

        switch ($itemObj->status()) {
            case Constants::PUBLISHER_STATUS_SUBMITTED:
                $breadcrumbAction1 = _CO_PUBLISHER_SUBMITTED;
                $breadcrumbAction2 = _AM_PUBLISHER_APPROVING;
                $pageTitle         = _AM_PUBLISHER_SUBMITTED_TITLE;
                $pageInfo          = _AM_PUBLISHER_SUBMITTED_INFO;
                $buttonCaption     = _AM_PUBLISHER_APPROVE;
                $newStatus         = Constants::PUBLISHER_STATUS_PUBLISHED;
                break;

            case Constants::PUBLISHER_STATUS_PUBLISHED:
                $breadcrumbAction1 = _CO_PUBLISHER_PUBLISHED;
                $breadcrumbAction2 = _AM_PUBLISHER_EDITING;
                $pageTitle         = _AM_PUBLISHER_PUBLISHEDEDITING;
                $pageInfo          = _AM_PUBLISHER_PUBLISHEDEDITING_INFO;
                $buttonCaption     = _AM_PUBLISHER_MODIFY;
                $newStatus         = Constants::PUBLISHER_STATUS_PUBLISHED;
                break;

            case Constants::PUBLISHER_STATUS_OFFLINE:
                $breadcrumbAction1 = _CO_PUBLISHER_OFFLINE;
                $breadcrumbAction2 = _AM_PUBLISHER_EDITING;
                $pageTitle         = _AM_PUBLISHER_OFFLINEEDITING;
                $pageInfo          = _AM_PUBLISHER_OFFLINEEDITING_INFO;
                $buttonCaption     = _AM_PUBLISHER_MODIFY;
                $newStatus         = Constants::PUBLISHER_STATUS_OFFLINE;
                break;

            case Constants::PUBLISHER_STATUS_REJECTED:
                $breadcrumbAction1 = _CO_PUBLISHER_REJECTED;
                $breadcrumbAction2 = _AM_PUBLISHER_REJECTED;
                $pageTitle         = _AM_PUBLISHER_REJECTED_EDIT;
                $pageInfo          = _AM_PUBLISHER_REJECTED_EDIT_INFO;
                $buttonCaption     = _AM_PUBLISHER_MODIFY;
                $newStatus         = Constants::PUBLISHER_STATUS_REJECTED;
                break;

            case Constants::PUBLISHER_STATUS_NOTSET: // Then it's a clone...
                $breadcrumbAction1 = _AM_PUBLISHER_ITEMS;
                $breadcrumbAction2 = _AM_PUBLISHER_CLONE_NEW;
                $buttonCaption     = _AM_PUBLISHER_CREATE;
                $newStatus         = Constants::PUBLISHER_STATUS_PUBLISHED;
                $pageTitle         = _AM_PUBLISHER_ITEM_DUPLICATING;
                $pageInfo          = _AM_PUBLISHER_ITEM_DUPLICATING_DSC;
                break;

            case 'default':
            default:
                $breadcrumbAction1 = _AM_PUBLISHER_ITEMS;
                $breadcrumbAction2 = _AM_PUBLISHER_EDITING;
                $pageTitle         = _AM_PUBLISHER_PUBLISHEDEDITING;
                $pageInfo          = _AM_PUBLISHER_PUBLISHEDEDITING_INFO;
                $buttonCaption     = _AM_PUBLISHER_MODIFY;
                $newStatus         = Constants::PUBLISHER_STATUS_PUBLISHED;
                break;
        }

        $categoryObj = $itemObj->getCategory();

        echo "<br>\n";
        Publisher\Utility::openCollapsableBar('edititemtable', 'edititemicon', $pageTitle, $pageInfo);

        if ($clone) {
            echo '<form><div style="margin-bottom: 10px;">';
            echo "<input type='button' name='button' onclick=\"location='item.php?op=clone&itemid=" . $itemObj->itemid() . "'\" value='" . _AM_PUBLISHER_CLONE_ITEM . "'>&nbsp;&nbsp;";
            echo '</div></form>';
        }
    } else {
        // there's no parameter, so we're adding an item

        $itemObj = $helper->getHandler('Item')->create();
        $itemObj->setVarsFromRequest();

        $categoryObj       = $helper->getHandler('Category')->create();
        $breadcrumbAction1 = _AM_PUBLISHER_ITEMS;
        $breadcrumbAction2 = _AM_PUBLISHER_CREATINGNEW;
        $buttonCaption     = _AM_PUBLISHER_CREATE;
        $newStatus         = Constants::PUBLISHER_STATUS_PUBLISHED;

        $categoryObj->setVar('categoryid', Request::getInt('categoryid', 0, 'GET'));

        Publisher\Utility::openCollapsableBar('createitemtable', 'createitemicon', _AM_PUBLISHER_ITEM_CREATING, _AM_PUBLISHER_ITEM_CREATING_DSC);
    }

    $sform = $itemObj->getForm(_AM_PUBLISHER_ITEMS);

    $sform->assign($formTpl);
    $formTpl->display('db:publisher_submit.tpl');

    Publisher\Utility::closeCollapsableBar('edititemtable', 'edititemicon');

    Publisher\Utility::openCollapsableBar('pagewraptable', 'pagewrapicon', _AM_PUBLISHER_PAGEWRAP, _AM_PUBLISHER_PAGEWRAPDSC);

    $dir = Publisher\Utility::getUploadDir(true, 'content');

    if (!is_writable($dir)) {
        echo "<span style='color:#ff0000;'><h4>" . _AM_PUBLISHER_PERMERROR . '</h4></span>';
    }

    // Upload File
    echo "<form name='form_name2' id='form_name2' action='pw_upload_file.php' method='post' enctype='multipart/form-data'>";
    echo "<table cellspacing='1' width='100%' class='outer'>";
    echo "<tr><th colspan='2'>" . _AM_PUBLISHER_UPLOAD_FILE . '</th></tr>';
    echo "<tr valign='top' align='left'><td class='head'>" . _AM_PUBLISHER_SEARCH_PW . "</td><td class='even'><input type='file' name='fileupload' id='fileupload' size='30'></td></tr>";
    echo "<tr valign='top' align='left'><td class='head'><input type='hidden' name='MAX_FILE_SIZE' id='op' value='500000'></td><td class='even'><input type='submit' name='submit' value='" . _AM_PUBLISHER_UPLOAD . "'></td></tr>";
    echo "<input type='hidden' name='backto' value='$publisherCurrentPage'>";
    echo '</table>';
    echo '</form>';

    // Delete File
    $form = new \XoopsThemeForm(_CO_PUBLISHER_DELETEFILE, 'form_name', 'pw_delete_file.php');

    $pWrapSelect = new \XoopsFormSelect(Publisher\Utility::getUploadDir(true, 'content'), 'address');
    $folder      = dir($dir);
    while (false !== ($file = $folder->read())) {
        if ('.' !== $file && '..' !== $file) {
            $pWrapSelect->addOption($file, $file);
        }
    }
    $folder->close();
    $form->addElement($pWrapSelect);

    $delfile = 'delfile';
    $form->addElement(new \XoopsFormHidden('op', $delfile));
    $submit = new \XoopsFormButton('', 'submit', _AM_PUBLISHER_BUTTON_DELETE, 'submit');
    $form->addElement($submit);

    $form->addElement(new \XoopsFormHidden('backto', $publisherCurrentPage));
    $form->display();

    Publisher\Utility::closeCollapsableBar('pagewraptable', 'pagewrapicon');
}
