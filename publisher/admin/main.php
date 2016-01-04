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
 * @version         $Id:main.php  335 2011-12-05 20:24:01Z lusopoemas@gmail.com $
 */

include_once __DIR__ . '/admin_header.php';
include_once $GLOBALS['xoops']->path('class/xoopslists.php');
include_once $GLOBALS['xoops']->path('class/pagenav.php');

$itemid = XoopsRequest::getInt('itemid', 0, 'POST');

$pick      = XoopsRequest::getInt('pick', XoopsRequest::getInt('pick', 0, 'GET'), 'POST');
$statussel = XoopsRequest::getInt('statussel', XoopsRequest::getInt('statussel', 0, 'GET'), 'POST');
$sortsel   = XoopsRequest::getString('sortsel', XoopsRequest::getString('sortsel', 'itemid', 'GET'), 'POST');
$ordersel  = XoopsRequest::getString('ordersel', XoopsRequest::getString('ordersel', 'DESC', 'GET'), 'POST');

$module_id    = $publisher->getModule()->mid();
$gpermHandler =& xoops_getHandler('groupperm');
$groups       = ($GLOBALS['xoopsUser']) ? ($GLOBALS['xoopsUser']->getGroups()) : XOOPS_GROUP_ANONYMOUS;

// Code for the page

$startentry = XoopsRequest::getInt('startentry', 0, 'GET');

publisherCpHeader();
//publisher_adminMenu(0, _AM_PUBLISHER_INDEX);

// Total ITEMs -- includes everything on the table
$totalitems =& $publisher->getHandler('item')->getItemsCount();

// Total categories
$totalcategories =& $publisher->getHandler('category')->getCategoriesCount(-1);

// Total submitted ITEMs
$totalsubmitted =& $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstants::PUBLISHER_STATUS_SUBMITTED));

// Total published ITEMs
$totalpublished =& $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED));

// Total offline ITEMs
$totaloffline =& $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstants::PUBLISHER_STATUS_OFFLINE));

// Total rejected
$totalrejected =& $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstants::PUBLISHER_STATUS_REJECTED));

// Check Path Configuration
if ((publisherGetPathStatus('root', true) < 0) || (publisherGetPathStatus('images', true) < 0) || (publisherGetPathStatus('images/category', true) < 0) || (publisherGetPathStatus('images/item', true) < 0) || (publisherGetPathStatus('content', true) < 0)) {
    PublisherUtilities::createDir();
}

publisherOpenCollapsableBar('inventorytable', 'inventoryicon', _AM_PUBLISHER_INVENTORY);
echo '<br />';
echo "<table width='100%' class='outer' cellspacing='1' cellpadding='3' border='0' ><tr>";
echo "<td class='head'>" . _AM_PUBLISHER_TOTALCAT . "</td><td align='center' class='even'>" . $totalcategories . '</td>';
echo "<td class='head'>" . _AM_PUBLISHER_TOTALSUBMITTED . "</td><td align='center' class='even'>" . $totalsubmitted . '</td>';
echo "<td class='head'>" . _AM_PUBLISHER_TOTALPUBLISHED . "</td><td align='center' class='even'>" . $totalpublished . '</td>';
echo "<td class='head'>" . _AM_PUBLISHER_TOTAL_OFFLINE . "</td><td align='center' class='even'>" . $totaloffline . '</td>';
echo '</tr></table>';
echo '<br />';

echo "<form><div style=\"margin-bottom: 12px;\">";
echo "<input type='button' name='button' onclick=\"location='category.php?op=mod'\" value='" . _AM_PUBLISHER_CATEGORY_CREATE . "'>&nbsp;&nbsp;";
echo "<input type='button' name='button' onclick=\"location='item.php?op=mod'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
echo '</div></form>';

publisherCloseCollapsableBar('inventorytable', 'inventoryicon');

// Construction of lower table
publisherOpenCollapsableBar('allitemstable', 'allitemsicon', _AM_PUBLISHER_ALLITEMS, _AM_PUBLISHER_ALLITEMSMSG);

$showingtxt   = '';
$selectedtxt  = '';
$cond         = '';
$selectedtxt0 = '';
$selectedtxt1 = '';
$selectedtxt2 = '';
$selectedtxt3 = '';
$selectedtxt4 = '';

$sorttxttitle   = '';
$sorttxtcreated = '';
$sorttxtweight  = '';
$sorttxtitemid  = '';

$sorttxthits     = '';
$sorttxtvotes    = '';
$sorttxtcomments = '';
$sorttxtrating   = '';

$ordertxtasc  = '';
$ordertxtdesc = '';

switch ($sortsel) {
    case 'title':
        $sorttxttitle = "selected='selected'";
        break;

    case 'datesub':
        $sorttxtcreated = "selected='selected'";
        break;

    case 'weight':
        $sorttxtweight = "selected='selected'";
        break;

    case 'counter':
        $sorttxthits = "selected='selected'";
        break;

    case 'rating':
        $sorttxtrating = "selected='selected'";
        break;

    case 'votes':
        $sorttxtvotes = "selected='selected'";
        break;

    case 'comments':
        $sorttxtcomments = "selected='selected'";
        break;

    default:
        $sorttxtitemid = "selected='selected'";
        break;
}

switch ($ordersel) {
    case 'ASC':
        $ordertxtasc = "selected='selected'";
        break;

    default:
        $ordertxtdesc = "selected='selected'";
        break;
}

switch ($statussel) {
    case PublisherConstants::PUBLISHER_STATUS_ALL:
        $selectedtxt0        = "selected='selected'";
        $caption             = _AM_PUBLISHER_ALL;
        $cond                = '';
        $status_explaination = _AM_PUBLISHER_ALL_EXP;
        break;

    case PublisherConstants::PUBLISHER_STATUS_SUBMITTED:
        $selectedtxt1        = "selected='selected'";
        $caption             = _CO_PUBLISHER_SUBMITTED;
        $cond                = ' WHERE status = ' . PublisherConstants::PUBLISHER_STATUS_SUBMITTED . ' ';
        $status_explaination = _AM_PUBLISHER_SUBMITTED_EXP;
        break;

    case PublisherConstants::PUBLISHER_STATUS_PUBLISHED:
        $selectedtxt2        = "selected='selected'";
        $caption             = _CO_PUBLISHER_PUBLISHED;
        $cond                = ' WHERE status = ' . PublisherConstants::PUBLISHER_STATUS_PUBLISHED . ' ';
        $status_explaination = _AM_PUBLISHER_PUBLISHED_EXP;
        break;

    case PublisherConstants::PUBLISHER_STATUS_OFFLINE:
        $selectedtxt3        = "selected='selected'";
        $caption             = _CO_PUBLISHER_OFFLINE;
        $cond                = ' WHERE status = ' . PublisherConstants::PUBLISHER_STATUS_OFFLINE . ' ';
        $status_explaination = _AM_PUBLISHER_OFFLINE_EXP;
        break;

    case PublisherConstants::PUBLISHER_STATUS_REJECTED:
        $selectedtxt4        = "selected='selected'";
        $caption             = _CO_PUBLISHER_REJECTED;
        $cond                = ' WHERE status = ' . PublisherConstants::PUBLISHER_STATUS_REJECTED . ' ';
        $status_explaination = _AM_PUBLISHER_REJECTED_ITEM_EXP;
        break;
}

/* -- Code to show selected terms -- */
echo "<form name='pick' id='pick' action='" . XoopsRequest::getString('PHP_SELF', '', 'SERVER') . "' method='POST' style='margin: 0;'>";

echo "
    <table width='100%' cellspacing='1' cellpadding='2' border='0' style='border-left: 1px solid silver; border-top: 1px solid silver; border-right: 1px solid silver;'>
        <tr>
            <td><span style='font-weight: bold; font-variant: small-caps;'>" . _AM_PUBLISHER_SHOWING . ' ' . $caption . "</span></td>
            <td align='right'>" . _AM_PUBLISHER_SELECT_SORT . "
                <select name='sortsel' onchange='submit()'>
                    <option value='itemid' $sorttxtitemid>" . _AM_PUBLISHER_ID . "</option>
                    <option value='title' $sorttxttitle>" . _AM_PUBLISHER_TITLE . "</option>
                    <option value='datesub' $sorttxtcreated>" . _AM_PUBLISHER_CREATED . "</option>
                    <option value='weight' $sorttxtweight>" . _CO_PUBLISHER_WEIGHT . "</option>

                    <option value='counter' $sorttxthits>" . _AM_PUBLISHER_HITS . "</option>
                    <option value='rating' $sorttxtrating>" . _AM_PUBLISHER_RATE . "</option>
                    <option value='votes' $sorttxtvotes>" . _AM_PUBLISHER_VOTES . "</option>
                    <option value='comments' $sorttxtcomments>" . _AM_PUBLISHER_COMMENTS_COUNT . "</option>

                </select>
                <select name='ordersel' onchange='submit()'>
                    <option value='ASC' $ordertxtasc>" . _AM_PUBLISHER_ASC . "</option>
                    <option value='DESC' $ordertxtdesc>" . _AM_PUBLISHER_DESC . '</option>
                </select>
            ' . _AM_PUBLISHER_SELECT_STATUS . " :
                <select name='statussel' onchange='submit()'>
                    <option value='0' $selectedtxt0>" . _AM_PUBLISHER_ALL . " [$totalitems]</option>
                    <option value='1' $selectedtxt1>" . _CO_PUBLISHER_SUBMITTED . " [$totalsubmitted]</option>
                    <option value='2' $selectedtxt2>" . _CO_PUBLISHER_PUBLISHED . " [$totalpublished]</option>
                    <option value='3' $selectedtxt3>" . _CO_PUBLISHER_OFFLINE . " [$totaloffline]</option>
                    <option value='4' $selectedtxt4>" . _CO_PUBLISHER_REJECTED . " [$totalrejected]</option>
                </select>
            </td>
        </tr>
    </table>
    </form>";

// Get number of entries in the selected state
$statusSelected = ($statussel == 0) ? -1 : $statussel;

$numrows =& $publisher->getHandler('item')->getItemsCount(-1, $statusSelected);

// creating the Q&As objects
$itemsObj =& $publisher->getHandler('item')->getItems($publisher->getConfig('idxcat_perpage'), $startentry, $statusSelected, -1, $sortsel, $ordersel);

$totalItemsOnPage = count($itemsObj);

PublisherUtilities::buildTableItemTitleRow();

if ($numrows > 0) {
    for ($i = 0; $i < $totalItemsOnPage; ++$i) {
        // Creating the category object to which this item is linked
        $categoryObj = $itemsObj[$i]->getCategory();
        $approve     = '';
        switch ($itemsObj[$i]->status()) {

            case PublisherConstants::PUBLISHER_STATUS_SUBMITTED:
                $statustxt = _CO_PUBLISHER_SUBMITTED;
                $approve   = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/approve.gif' title='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' alt='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' /></a>&nbsp;";
                $clone     = '';
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "' /></a>";
                $modify    = '';
                break;

            case PublisherConstants::PUBLISHER_STATUS_PUBLISHED:
                $statustxt = _CO_PUBLISHER_PUBLISHED;
                $approve   = '';

                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_ITEM_EDIT . "' alt='" . _AM_PUBLISHER_ITEM_EDIT . "' /></a>&nbsp;";
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "' /></a>&nbsp;";
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "' /></a>&nbsp;";
                break;

            case PublisherConstants::PUBLISHER_STATUS_OFFLINE:
                $statustxt = _CO_PUBLISHER_OFFLINE;
                $approve   = '';
                $modify    = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_ITEM_EDIT . "' alt='" . _AM_PUBLISHER_ITEM_EDIT . "' /></a>&nbsp;";
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "' /></a>&nbsp;";
                $clone     = /** @lang text */
                    "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "' /></a>&nbsp;";
                break;

            case PublisherConstants::PUBLISHER_STATUS_REJECTED:
                $statustxt = _CO_PUBLISHER_REJECTED;
                $approve   = '';
                $modify    = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_REJECTED_EDIT . "' alt='" . _AM_PUBLISHER_REJECTED_EDIT . "' /></a>&nbsp;";
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "' /></a>&nbsp;";
                $clone     = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/clone.gif' title='" . _AM_PUBLISHER_CLONE_ITEM . "' alt='" . _AM_PUBLISHER_CLONE_ITEM . "' /></a>&nbsp;";
                break;

            case 'default':
            default:
                $statustxt = _AM_PUBLISHER_STATUS0;
                $approve   = '';
                $clone     = '';
                $modify    = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_REJECTED_EDIT . "' alt='" . _AM_PUBLISHER_REJECTED_EDIT . "' /></a>&nbsp;";
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . '/modules/' . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "' /></a>";
                break;
        }

        echo '<tr>';
        echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . '</td>';
        echo "<td class='even' align='left'>" . $categoryObj->getCategoryLink() . '</td>';
        echo "<td class='even' align='left'>" . $itemsObj[$i]->getItemLink() . '</td>';
        echo "<td class='even' align='center'>" . $itemsObj[$i]->getDatesub() . '</td>';

        echo "<td class='even' align='center'>" . $itemsObj[$i]->weight() . '</td>';
        echo "<td class='even' align='center'>" . $itemsObj[$i]->counter() . '</td>';
        echo "<td class='even' align='center'>" . $itemsObj[$i]->rating() . '</td>';
        echo "<td class='even' align='center'>" . $itemsObj[$i]->votes() . '</td>';
        echo "<td class='even' align='center'>" . $itemsObj[$i]->comments() . '</td>';

        echo "<td class='even' align='center'>" . $statustxt . '</td>';
        echo "<td class='even' align='center'> " . $approve . $clone . $modify . $delete . '</td>';
        echo '</tr>';
    }
} else {
    // that is, $numrows = 0, there's no entries yet
    echo '<tr>';
    echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMSSEL . '</td>';
    echo '</tr>';
}
echo "</table>\n";
echo "<span style=\"color: #567; margin: 3px 0 18px 0; font-size: small; display: block; \">$status_explaination</span>";
$pagenav = new XoopsPageNav($numrows, $publisher->getConfig('idxcat_perpage'), $startentry, 'startentry', "statussel=$statussel&amp;sortsel=$sortsel&amp;ordersel=$ordersel");

if ($publisher->getConfig('format_image_nav') == 1) {
    echo '<div style="text-align:right; background-color: white; margin: 10px 0;">' . $pagenav->renderImageNav() . '</div>';
} else {
    echo '<div style="text-align:right; background-color: white; margin: 10px 0;">' . $pagenav->renderNav() . '</div>';
}
// ENDs code to show active entries
publisherCloseCollapsableBar('allitemstable', 'allitemsicon');
// Close the collapsable div

include_once __DIR__ . '/admin_footer.php';
