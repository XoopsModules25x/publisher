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
use XoopsModules\Publisher\{Constants,
    Utility
};

require_once __DIR__ . '/admin_header.php';
require_once $GLOBALS['xoops']->path('class/xoopslists.php');
require_once $GLOBALS['xoops']->path('class/pagenav.php');
// require_once  \dirname(__DIR__) . '/class/Utility.php';
require_once \dirname(__DIR__) . '/include/common.php';

$itemId = Request::getInt('itemid', 0, 'POST');

$pick      = Request::getInt('pick', Request::getInt('pick', 0, 'GET'), 'POST');
$statussel = Request::getInt('statussel', Request::getInt('statussel', 0, 'GET'), 'POST');
$sortsel   = Request::getString('sortsel', Request::getString('sortsel', 'itemid', 'GET'), 'POST');
$ordersel  = Request::getString('ordersel', Request::getString('ordersel', 'DESC', 'GET'), 'POST');

$moduleId = $helper->getModule()->mid();
/** @var XoopsGroupPermHandler $grouppermHandler */
/** @var \XoopsGroupPermHandler $grouppermHandler */
$grouppermHandler = xoops_getHandler('groupperm');
$groups           = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;

// Code for the page

$startentry = Request::getInt('startentry', 0, 'GET');

Utility::cpHeader();
//publisher_adminMenu(0, _AM_PUBLISHER_INDEX);

// Total ITEMs -- includes everything on the table
$totalitems = $helper->getHandler('Item')->getItemsCount();

// Total categories
$totalcategories = $helper->getHandler('Category')->getCategoriesCount(-1);

// Total submitted ITEMs
$totalsubmitted = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_SUBMITTED]);

// Total published ITEMs
$totalpublished = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_PUBLISHED]);

// Total offline ITEMs
$totaloffline = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_OFFLINE]);

// Total rejected
$totalrejected = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_REJECTED]);

// Check Path Configuration
if ((Utility::getPathStatus('root', true) < 0)
    || (Utility::getPathStatus('images', true) < 0)
    || (Utility::getPathStatus('images/category', true) < 0)
    || (Utility::getPathStatus('images/item', true) < 0)
    || (Utility::getPathStatus('content', true) < 0)) {
    Utility::createDir();
}

Utility::openCollapsableBar('inventorytable', 'inventoryicon', _AM_PUBLISHER_INVENTORY);
echo '<br>';
echo "<table width='100%' class='outer' cellspacing='1' cellpadding='3' border='0' ><tr>";
echo "<td class='head'>" . _AM_PUBLISHER_TOTALCAT . "</td><td align='center' class='even'>" . $totalcategories . '</td>';
echo "<td class='head'>" . _AM_PUBLISHER_TOTALSUBMITTED . "</td><td align='center' class='even'>" . $totalsubmitted . '</td>';
echo "<td class='head'>" . _AM_PUBLISHER_TOTALPUBLISHED . "</td><td align='center' class='even'>" . $totalpublished . '</td>';
echo "<td class='head'>" . _AM_PUBLISHER_TOTAL_OFFLINE . "</td><td align='center' class='even'>" . $totaloffline . '</td>';
echo '</tr></table>';
echo '<br>';

echo '<form><div style="margin-bottom: 12px;">';
echo "<input type='button' name='button' onclick=\"location='category.php?op=mod'\" value='" . _AM_PUBLISHER_CATEGORY_CREATE . "'>&nbsp;&nbsp;";
echo "<input type='button' name='button' onclick=\"location='item.php?op=mod'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
echo '</div></form>';

Utility::closeCollapsableBar('inventorytable', 'inventoryicon');

// Construction of lower table
Utility::openCollapsableBar('allitemstable', 'allitemsicon', _AM_PUBLISHER_ALLITEMS, _AM_PUBLISHER_ALLITEMSMSG);

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
        $sorttxttitle = 'selected';
        break;
    case 'datesub':
        $sorttxtcreated = 'selected';
        break;
    case 'weight':
        $sorttxtweight = 'selected';
        break;
    case 'counter':
        $sorttxthits = 'selected';
        break;
    case 'rating':
        $sorttxtrating = 'selected';
        break;
    case 'votes':
        $sorttxtvotes = 'selected';
        break;
    case 'comments':
        $sorttxtcomments = 'selected';
        break;
    default:
        $sorttxtitemid = 'selected';
        break;
}

switch ($ordersel) {
    case 'ASC':
        $ordertxtasc = 'selected';
        break;
    default:
        $ordertxtdesc = 'selected';
        break;
}

switch ($statussel) {
    case Constants::PUBLISHER_STATUS_ALL:
        $selectedtxt0      = 'selected';
        $caption           = _AM_PUBLISHER_ALL;
        $cond              = '';
        $statusExplanation = _AM_PUBLISHER_ALL_EXP;
        break;
    case Constants::PUBLISHER_STATUS_SUBMITTED:
        $selectedtxt1      = 'selected';
        $caption           = _CO_PUBLISHER_SUBMITTED;
        $cond              = ' WHERE status = ' . Constants::PUBLISHER_STATUS_SUBMITTED . ' ';
        $statusExplanation = _AM_PUBLISHER_SUBMITTED_EXP;
        break;
    case Constants::PUBLISHER_STATUS_PUBLISHED:
        $selectedtxt2      = 'selected';
        $caption           = _CO_PUBLISHER_PUBLISHED;
        $cond              = ' WHERE status = ' . Constants::PUBLISHER_STATUS_PUBLISHED . ' ';
        $statusExplanation = _AM_PUBLISHER_PUBLISHED_EXP;
        break;
    case Constants::PUBLISHER_STATUS_OFFLINE:
        $selectedtxt3      = 'selected';
        $caption           = _CO_PUBLISHER_OFFLINE;
        $cond              = ' WHERE status = ' . Constants::PUBLISHER_STATUS_OFFLINE . ' ';
        $statusExplanation = _AM_PUBLISHER_OFFLINE_EXP;
        break;
    case Constants::PUBLISHER_STATUS_REJECTED:
        $selectedtxt4      = 'selected';
        $caption           = _CO_PUBLISHER_REJECTED;
        $cond              = ' WHERE status = ' . Constants::PUBLISHER_STATUS_REJECTED . ' ';
        $statusExplanation = _AM_PUBLISHER_REJECTED_ITEM_EXP;
        break;
}

/* -- Code to show selected terms -- */
echo "<form name='pick' id='pick' action='" . Request::getString('SCRIPT_NAME', '', 'SERVER') . "' method='POST' style='margin: 0;'>";

echo "
    <table width='100%' cellspacing='1' cellpadding='2' border='0' style='border-left: 1px solid #c0c0c0; border-top: 1px solid #c0c0c0; border-right: 1px solid #c0c0c0;'>
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
$statusSelected = (0 == $statussel) ? -1 : $statussel;

$numrows = $helper->getHandler('Item')->getItemsCount(-1, $statusSelected);

// creating the Q&As objects
$itemsObj = $helper->getHandler('Item')->getItems($helper->getConfig('idxcat_perpage'), $startentry, $statusSelected, -1, $sortsel, $ordersel);

$totalItemsOnPage = count($itemsObj);

Utility::buildTableItemTitleRow();

if ($numrows > 0) {
    for ($i = 0; $i < $totalItemsOnPage; ++$i) {
        // Creating the category object to which this item is linked
        $categoryObj = $itemsObj[$i]->getCategory();
        $approve     = '';
        switch ($itemsObj[$i]->status()) {
            case Constants::PUBLISHER_STATUS_SUBMITTED:
                $statustxt = _CO_PUBLISHER_SUBMITTED;
                $approve   = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['moderate'] . '</a>&nbsp;';
                $clone     = '';
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['delete'] . '</a>';
                $modify    = '';
                break;
            case Constants::PUBLISHER_STATUS_PUBLISHED:
                $statustxt = _CO_PUBLISHER_PUBLISHED;
                $approve   = '';
                $modify = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['edit'] . '</a>&nbsp;';
                $delete = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['delete'] . '</a>&nbsp;';
                $clone  = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['clone'] . '</a>&nbsp;';
                break;
            case Constants::PUBLISHER_STATUS_OFFLINE:
                $statustxt = _CO_PUBLISHER_OFFLINE;
                $approve   = '';
                $modify    = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['edit'] . '</a>&nbsp;';
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['delete'] . '</a>&nbsp;';
                $clone     = /** @lang text */
                    "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['clone'] . '</a>&nbsp;';
                break;
            case Constants::PUBLISHER_STATUS_REJECTED:
                $statustxt = _CO_PUBLISHER_REJECTED;
                $approve   = '';
                $modify    = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['rejectededit'] . '</a>&nbsp;';
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['delete'] . '</a>&nbsp;';
                $clone     = "<a href='item.php?op=clone&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['clone'] . '</a>&nbsp;';
                break;
            case 'default':
            default:
                $statustxt = _AM_PUBLISHER_STATUS0;
                $approve   = '';
                $clone     = '';
                $modify    = "<a href='item.php?op=mod&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['rejectededit'] . '</a>&nbsp;';
                $delete    = "<a href='item.php?op=del&itemid=" . $itemsObj[$i]->itemid() . "'>" . $icons['delete'] . '</a>';
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
        echo "<td class='even' align='center'> " . $approve . $modify . $delete . $clone . '</td>';
        echo '</tr>';
    }
} else {
    // that is, $numrows = 0, there's no entries yet
    echo '<tr>';
    echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMSSEL . '</td>';
    echo '</tr>';
}
echo "</table>\n";
echo "<span style=\"color: #567; margin: 3px 0 18px 0; font-size: small; display: block; \">$statusExplanation</span>";
$pagenav = new \XoopsPageNav($numrows, $helper->getConfig('idxcat_perpage'), $startentry, 'startentry', "statussel=$statussel&amp;sortsel=$sortsel&amp;ordersel=$ordersel");

if (1 == $helper->getConfig('format_image_nav')) {
    echo '<div style="text-align:right; background-color: #ffffff; margin: 10px 0;">' . $pagenav->renderImageNav() . '</div>';
} else {
    echo '<div style="text-align:right; background-color: #ffffff; margin: 10px 0;">' . $pagenav->renderNav() . '</div>';
}
// ENDs code to show active entries
Utility::closeCollapsableBar('allitemstable', 'allitemsicon');
// Close the collapsable div

require_once __DIR__ . '/admin_footer.php';
