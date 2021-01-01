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
use XoopsModules\Publisher\{Category,
    Constants,
    Helper,
    Item,
    Metagen,
    Seo,
    Utility
};

require_once __DIR__ . '/header.php';

$categoryId = Request::getInt('categoryid', 0, 'GET');

// Creating the category object for the selected category
/** @var Helper $helper */
$categoryObj = $helper->getHandler('Category')->get($categoryId);

// if the selected category was not found, exit
if (!is_object($categoryObj) || $categoryObj->notLoaded()) {
    redirect_header('<script>javascript:history.go(-1)</script>', 1, _MD_PUBLISHER_NOCATEGORYSELECTED);
}

// Check user permissions to access this category
if (!$categoryObj->checkPermission()) {
    redirect_header('<script>javascript:history.go(-1)</script>', 1, _NOPERM);
}

// At which record shall we start
$start = Request::getInt('start', 0, 'GET');

$itemPageId = Request::getInt('page', -1, 'GET');

$totalItems = $helper->getHandler('Category')->publishedItemsCount();

// if there is no Item under this categories or the sub-categories, exit
// why?
if (!isset($totalItems[$categoryId]) || 0 == $totalItems[$categoryId]) {
    //redirect_header("index.php", 1, _MD_PUBLISHER_MAINNOFAQS);
}

// Added by skalpa: custom template support
/** @var Category $categoryObj */
$GLOBALS['xoopsOption']['template_main'] = $categoryObj->getTemplate();
if (empty($GLOBALS['xoopsOption']['template_main'])) {
    $GLOBALS['xoopsOption']['template_main'] = 'publisher_display' . '_' . $helper->getConfig('idxcat_items_display_type') . '.tpl';
}

require_once $GLOBALS['xoops']->path('header.php');
require_once PUBLISHER_ROOT_PATH . '/footer.php';

$moduleId = $helper->getModule()->getVar('mid');

// creating the Item objects that belong to the selected category
switch ($helper->getConfig('format_order_by')) {
    case 'title':
        $sort  = 'title';
        $order = 'ASC';
        break;
    case 'date':
        $sort  = 'datesub';
        $order = 'DESC';
        break;
    case 'counter':
        $sort  = 'counter';
        $order = 'DESC';
        break;
    case 'rating':
        $sort  = 'rating';
        $order = 'DESC';
        break;
    case 'votes':
        $sort  = 'votes';
        $order = 'DESC';
        break;
    case 'comments':
        $sort  = 'comments';
        $order = 'DESC';
        break;
    default:
        $sort  = 'weight';
        $order = 'ASC';
        break;
}

$itemsObj = $helper->getHandler('Item')->getAllPublished($helper->getConfig('idxcat_index_perpage'), $start, $categoryId, $sort, $order);

$totalItemOnPage = 0;
if ($itemsObj) {
    $totalItemOnPage = count($itemsObj);
}

// Arrays that will hold the informations passed on to smarty variables
$category = [];
$items    = [];

// Populating the smarty variables with informations related to the selected category
$category                 = $categoryObj->toArraySimple(null);
$category['categoryPath'] = $categoryObj->getCategoryPath($helper->getConfig('format_linked_path'));

//$totalItems = $publisher_categoryHandler->publishedItemsCount($helper->getConfig('idxcat_display_last_item'));

if (1 == $helper->getConfig('idxcat_display_last_item')) {
    // Get the last smartitem
    $lastItemObj = $helper->getHandler('Item')->getLastPublishedByCat([[$categoryObj]]);
}
$lastitemsize = (int)$helper->getConfig('idxcat_last_item_size');

// Creating the sub-categories objects that belong to the selected category
$subcatsObj   = $helper->getHandler('Category')->getCategories(0, 0, $categoryId);
$totalSubcats = count($subcatsObj);

$total_items = 0;

$subcategories = [];

if ('no' !== $helper->getConfig('idxcat_show_subcats')) {
    // if this category has subcats
    if (isset($subcatsObj) && $totalSubcats > 0) {
        foreach ($subcatsObj as $key => $subcat) {
            // Get the items count of this very category
            $subcatTotalItems = $totalItems[$key] ?? 0;

            // Do we display empty sub-cats ?
            if (($subcatTotalItems > 0) || ('all' === $helper->getConfig('idxcat_show_subcats'))) {
                $subcatId = $subcat->getVar('categoryid');
                // if we retreived the last item object for this category
                if (isset($lastItemObj[$subcatId])) {
                    $subcat->setVar('last_itemid', $lastItemObj[$subcatId]->itemid());
                    $subcat->setVar('last_title_link', $lastItemObj[$key]->getItemLink(false, $lastitemsize));
                }

                $numItems = isset($totalItems[$subcatId]) ? $totalItems[$key] : 0;
                $subcat->setVar('itemcount', $numItems);
                // Put this subcat in the smarty variable
                $subcategories[$key] = $subcat->toArraySimple();
                //$total += $numItems;
            }

            if ($subcatTotalItems > 0) {
                $subcatId = $subcat->getVar('categoryid');
                // if we retreived the last item object for this category
                if (isset($lastItemObj[$subcatId])) {
                    $subcat->setVar('last_itemid', $lastItemObj[$subcatId]->itemid());
                    $subcat->setVar('last_title_link', $lastItemObj[$key]->getItemLink(false, $lastitemsize));
                }

                $numItems = isset($totalItems[$subcatId]) ? $totalItems[$key] : 0;
                $subcat->setVar('itemcount', $numItems);
                // Put this subcat in the smarty variable
                $subcategories[$key] = $subcat->toArraySimple();
                //$total += $numItems;
            }
        }
        unset($key, $subcatsObj);
    }
}

$category['subcats']      = $subcategories;
$category['subcatscount'] = count($subcategories);

$thiscategory_itemcount = $totalItems[$categoryId] ?? 0;
$category['total']      = $thiscategory_itemcount;

if (count($itemsObj) > 0) {
    /*$userids  = [];
    if ($itemsObj) {
        foreach ($itemsObj as $key => $thisItem) {
            $itemIds[] = $thisItem->getVar('itemid');
            $userids[$thisItem->uid()] = 1;
        }
    }
    $memberHandler = xoops_getHandler('member');
    //$users = $memberHandler->getUsers(new \Criteria('uid', "(" . implode(',', array_keys($userids)) . ")", "IN"), true);
    */
    // Adding the items of the selected category

    for ($i = 0; $i < $totalItemOnPage; ++$i) {
        $item                 = $itemsObj[$i]->toArraySimple('default', $helper->getConfig('item_title_size'));
        $item['categoryname'] = $categoryObj->name();
        $item['categorylink'] = "<a href='" . Seo::generateUrl('category', $itemsObj[$i]->categoryid(), $categoryObj->short_url()) . "'>" . $categoryObj->name() . '</a>';
        $item['who_when']     = $itemsObj[$i]->getWhoAndWhen();
        $xoopsTpl->append('items', $item);
    }

    if (!empty($lastItemObj[$categoryObj->getVar('categoryid')])) {
        $category['last_itemid']     = $lastItemObj[$categoryObj->getVar('categoryid')]->getVar('itemid');
        $category['last_title_link'] = $lastItemObj[$categoryObj->getVar('categoryid')]->getItemLink(false, $lastitemsize);
    }

    $xoopsTpl->assign('show_subtitle', $helper->getConfig('cat_disp_subtitle'));
}

$categories   = [];
$categories[] = $category;
$xoopsTpl->assign('category', $category);
$xoopsTpl->assign('categories', $categories);

// Language constants
$xoopsTpl->assign('sectionname', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('whereInSection', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('module_dirname', $helper->getDirname());
$xoopsTpl->assign('lang_category_summary', sprintf(_MD_PUBLISHER_CATEGORY_SUMMARY, $categoryObj->name()));
$xoopsTpl->assign('lang_category_summary_info', sprintf(_MD_PUBLISHER_CATEGORY_SUMMARY_INFO, $categoryObj->name()));
$xoopsTpl->assign('lang_items_title', sprintf(_MD_PUBLISHER_ITEMS_TITLE, $categoryObj->name()));
$xoopsTpl->assign('module_home', Utility::moduleHome($helper->getConfig('format_linked_path')));
$xoopsTpl->assign('categoryPath', '<li>' . $category['categoryPath'] . '</li>');
$xoopsTpl->assign('selected_category', $categoryId);

// The Navigation Bar
require_once $GLOBALS['xoops']->path('class/pagenav.php');
$pagenav = new \XoopsPageNav($thiscategory_itemcount, $helper->getConfig('idxcat_index_perpage'), $start, 'start', 'categoryid=' . $categoryObj->getVar('categoryid'));
if (1 == $helper->getConfig('format_image_nav')) {
    $navbar = '<div style="text-align:right;">' . $pagenav->renderImageNav() . '</div>';
} else {
    $navbar = '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';
}
$xoopsTpl->assign('navbar', $navbar);

/**
 * Generating meta information for this page
 */
$publisherMetagen = new Metagen($categoryObj->getVar('name'), $categoryObj->getVar('meta_keywords', 'n'), $categoryObj->getVar('meta_description', 'n'), $categoryObj->getCategoryPathForMetaTitle());
$publisherMetagen->createMetaTags();

// RSS Link
if (1 == $helper->getConfig('idxcat_show_rss_link')) {
    $link = sprintf("<a href='%s' title='%s'><img src='%s' border=0 alt='%s'></a>", PUBLISHER_URL . '/backend.php?categoryid=' . $categoryId, _MD_PUBLISHER_RSSFEED, PUBLISHER_URL . '/assets/images/rss.gif', _MD_PUBLISHER_RSSFEED);
    $xoopsTpl->assign('rssfeed_link', $link);
}

require_once $GLOBALS['xoops']->path('footer.php');
