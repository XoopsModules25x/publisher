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
 * @author          Bandit-X
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Xoops Modules Dev Team
 */
######################################################################
# Original version:
# [11-may-2001] Kenneth Lee - http://www.nexgear.com/
######################################################################

use Xmf\Request;

require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'publisher_archive.tpl';

require_once $GLOBALS['xoops']->path('header.php');
require_once PUBLISHER_ROOT_PATH . '/footer.php';
xoops_loadLanguage('calendar');
//mb xoops_load('XoopsLocal');

$lastyear    = 0;
$lastmonth   = 0;
$monthsArray = [
    1  => _CAL_JANUARY,
    2  => _CAL_FEBRUARY,
    3  => _CAL_MARCH,
    4  => _CAL_APRIL,
    5  => _CAL_MAY,
    6  => _CAL_JUNE,
    7  => _CAL_JULY,
    8  => _CAL_AUGUST,
    9  => _CAL_SEPTEMBER,
    10 => _CAL_OCTOBER,
    11 => _CAL_NOVEMBER,
    12 => _CAL_DECEMBER
];
$fromyear    = Request::getInt('year');
$frommonth   = Request::getInt('month');

$pgtitle = '';
if ($fromyear && $frommonth) {
    $pgtitle = sprintf(' - %d - %d', $fromyear, $frommonth);
}

$dateformat = $helper->getConfig('format_date');

if ('' === $dateformat) {
    $dateformat = 'm';
}

$myts = \MyTextSanitizer::getInstance();
$xoopsTpl->assign('xoops_pagetitle', $myts->htmlSpecialChars(_MD_PUBLISHER_ARCHIVES) . $pgtitle . ' - ' . $myts->htmlSpecialChars($GLOBALS['xoopsModule']->name()));

$useroffset = '';
if (is_object($GLOBALS['xoopsUser'])) {
    $timezone = $GLOBALS['xoopsUser']->timezone();
    if (isset($timezone)) {
        $useroffset = $GLOBALS['xoopsUser']->timezone();
    } else {
        $useroffset = $GLOBALS['xoopsConfig']['default_TZ'];
    }
}

$criteria = new \CriteriaCompo();
$criteria->add(new \Criteria('status', 2), 'AND');
$criteria->add(new \Criteria('datesub', time(), '<='), 'AND');
$categoriesGranted = $helper->getHandler('Permission')->getGrantedItems('category_read');
$criteria->add(new \Criteria('categoryid', '(' . implode(',', $categoriesGranted) . ')', 'IN'));
$criteria->setSort('datesub');
$criteria->setOrder('DESC');
//Get all articles dates as an array to save memory
$items      = $helper->getHandler('Item')->getAll($criteria, ['datesub'], false);
$itemsCount = count($items);

if (!($itemsCount > 0)) {
    redirect_header(XOOPS_URL, 2, _MD_PUBLISHER_NO_TOP_PERMISSIONS);
//mb    exit;
} else {
    $years  = [];
    $months = [];
    $i      = 0;
    foreach ($items as $item) {
        //mb        $time = \XoopsLocal::formatTimestamp($item['datesub'], 'mysql', $useroffset);
        $time = formatTimestamp($item['datesub'], 'mysql', $useroffset);
        if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})/', $time, $datetime)) {
            $thisYear  = (int)$datetime[1];
            $thisMonth = (int)$datetime[2];
            //first year
            if (empty($lastyear)) {
                $lastyear          = $thisYear;
                $articlesThisYear  = 0;
                $articlesThisMonth = 0;
            }
            //first month of the year reset
            if (0 == $lastmonth) {
                $lastmonth                    = $thisMonth;
                $months[$lastmonth]['string'] = $monthsArray[$lastmonth];
                $months[$lastmonth]['number'] = $lastmonth;
                //                $months[$lastmonth]['articlesMonthCount'] = 1;
                $articlesThisMonth = 0;
            }
            //new year
            if ($lastyear != $thisYear) {
                $years[$i]['number'] = $lastyear;
                $years[$i]['months'] = $months;

                $years[$i]['articlesYearCount'] = $articlesThisYear;

                $months            = [];
                $lastmonth         = 0;
                $lastyear          = $thisYear;
                $articlesThisYear  = 0;
                $articlesThisMonth = 0;
                ++$i;
            }
            //new month
            if ($lastmonth != $thisMonth) {
                if ($articlesThisMonth > 0) {
                    $months[$lastmonth]['articlesMonthCount'] = $articlesThisMonth;
                }
                $lastmonth                                = $thisMonth;
                $months[$lastmonth]['string']             = $monthsArray[$lastmonth];
                $months[$lastmonth]['number']             = $lastmonth;
                $months[$lastmonth]['articlesMonthCount'] = 1;
                $articlesThisMonth                        = 0;
            }

            ++$articlesThisMonth;
            ++$articlesThisYear;
        }
    }
    //    unset($item);
    $years[$i]['number'] = $thisYear;
    $years[$i]['months'] = $months;

    $years[$i]['articlesYearCount'] = $articlesThisYear;

    $xoopsTpl->assign('years', $years);
}
unset($items);

if (0 != $fromyear && 0 != $frommonth) {
    $xoopsTpl->assign('show_articles', true);
    $xoopsTpl->assign('lang_articles', _MD_PUBLISHER_ITEM);
    $xoopsTpl->assign('currentmonth', $monthsArray[$frommonth]);
    $xoopsTpl->assign('currentyear', $fromyear);
    $xoopsTpl->assign('lang_actions', _MD_PUBLISHER_ACTIONS);
    $xoopsTpl->assign('lang_date', _MD_PUBLISHER_DATE);
    $xoopsTpl->assign('lang_views', _MD_PUBLISHER_HITS);

    // must adjust the selected time to server timestamp
    $timeoffset = $useroffset - $GLOBALS['xoopsConfig']['server_TZ'];
    $monthstart = mktime(0 - $timeoffset, 0, 0, $frommonth, 1, $fromyear);
    $monthend   = mktime(23 - $timeoffset, 59, 59, $frommonth + 1, 0, $fromyear);
    $monthend   = ($monthend > time()) ? time() : $monthend;

    $count = 0;

    $itemHandler               = $helper->getHandler('Item');
    $itemHandler->table_link   = $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_categories');
    $itemHandler->field_link   = 'categoryid';
    $itemHandler->field_object = 'categoryid';
    // Categories for which user has access
    $categoriesGranted = $helper->getHandler('Permission')->getGrantedItems('category_read');
    $grantedCategories = new \Criteria('l.categoryid', '(' . implode(',', $categoriesGranted) . ')', 'IN');
    $criteria          = new \CriteriaCompo();
    $criteria->add($grantedCategories, 'AND');
    $criteria->add(new \Criteria('o.status', 2), 'AND');
    $critdatesub = new \CriteriaCompo();
    $critdatesub->add(new \Criteria('o.datesub', $monthstart, '>'), 'AND');
    $critdatesub->add(new \Criteria('o.datesub', $monthend, '<='), 'AND');
    $criteria->add($critdatesub);
    $criteria->setSort('o.datesub');
    $criteria->setOrder('DESC');
    $criteria->setLimit(3000);
    $storyarray = $itemHandler->getByLink($criteria); //Query Efficiency?

    $count = count($storyarray);
    if (is_array($storyarray) && $count > 0) {
        foreach ($storyarray as $item) {
            $story               = [];
            $htmltitle           = '';
            $story['title']      = "<a href='" . XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/category.php?categoryid=' . $item->categoryid() . "'>" . $item->getCategoryName() . "</a>: <a href='" . $item->getItemUrl() . "'" . $htmltitle . '>' . $item->getTitle() . '</a>';
            $story['counter']    = $item->counter();
            $story['date']       = $item->getDatesub();
            $story['print_link'] = XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/print.php?itemid=' . $item->itemid();
            $story['mail_link']  = 'mailto:?subject=' . sprintf(_CO_PUBLISHER_INTITEM, $GLOBALS['xoopsConfig']['sitename']) . '&amp;body=' . sprintf(_CO_PUBLISHER_INTITEMFOUND, $GLOBALS['xoopsConfig']['sitename']) . ':  ' . $item->getItemUrl();

            $xoopsTpl->append('stories', $story);
        }
        //        unset($item);
    }
    $xoopsTpl->assign('lang_printer', _MD_PUBLISHER_PRINTERFRIENDLY);
    $xoopsTpl->assign('lang_sendstory', _MD_PUBLISHER_SENDSTORY);
    $xoopsTpl->assign('lang_storytotal', _MD_PUBLISHER_TOTAL_ITEMS . ' ' . $count);
} else {
    $xoopsTpl->assign('show_articles', false);
}

$xoopsTpl->assign('lang_newsarchives', _MD_PUBLISHER_ARCHIVES);

require_once $GLOBALS['xoops']->path('footer.php');
