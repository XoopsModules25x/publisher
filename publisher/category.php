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
 * @subpackage      Action
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: category.php 10746 2013-01-10 20:54:35Z trabis $
 */

include_once __DIR__ . '/header.php';

$categoryid = XoopsRequest::getInt('categoryid', 0, 'GET');

// Creating the category object for the selected category
$categoryObj =& $publisher->getHandler('category')->get($categoryid);

// if the selected category was not found, exit
if (!is_object($categoryObj) || $categoryObj->notLoaded()) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOCATEGORYSELECTED);
    //    exit();
}

// Check user permissions to access this category
if (!$categoryObj->checkPermission()) {
    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    //    exit;
}

// At which record shall we start
$start = XoopsRequest::getInt('start', 0, 'GET');

$item_page_id = XoopsRequest::getInt('page', -1, 'GET');

$totalItems =& $publisher->getHandler('category')->publishedItemsCount();

// if there is no Item under this categories or the sub-categories, exit
// why?
if (!isset($totalItems[$categoryid]) || $totalItems[$categoryid] == 0) {
    //redirect_header("index.php", 1, _MD_PUBLISHER_MAINNOFAQS);
    //exit;
}

// Added by skalpa: custom template support
$xoopsOption['template_main'] = $categoryObj->template();
if (empty($xoopsOption['template_main'])) {
    $xoopsOption['template_main'] = 'publisher_display' . '_' . $publisher->getConfig('idxcat_items_display_type') . '.tpl';
}

include_once $GLOBALS['xoops']->path('header.php');
include_once PUBLISHER_ROOT_PATH . '/footer.php';

$module_id = $publisher->getModule()->getVar('mid');

// creating the Item objects that belong to the selected category
switch ($publisher->getConfig('format_order_by')) {
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

$itemsObj =& $publisher->getHandler('item')->getAllPublished($publisher->getConfig('idxcat_index_perpage'), $start, $categoryid, $sort, $order);

if ($itemsObj) {
    $totalItemOnPage = count($itemsObj);
} else {
    $totalItemOnPage = 0;
}

// Arrays that will hold the informations passed on to smarty variables
$category = array();
$items    = array();

// Populating the smarty variables with informations related to the selected category
$category                 = $categoryObj->toArraySimple(null, true);
$category['categoryPath'] = $categoryObj->getCategoryPath($publisher->getConfig('format_linked_path'));

//$totalItems = $publisher_categoryHandler->publishedItemsCount($publisher->getConfig('idxcat_display_last_item'));

if ($publisher->getConfig('idxcat_display_last_item') == 1) {
    // Get the last smartitem
    $lastItemObj =& $publisher->getHandler('item')->getLastPublishedByCat(array(array($categoryObj)));
}
$lastitemsize = (int)($publisher->getConfig('idxcat_last_item_size'));

// Creating the sub-categories objects that belong to the selected category
$subcatsObj    =& $publisher->getHandler('category')->getCategories(0, 0, $categoryid);
$total_subcats = count($subcatsObj);

$total_items = 0;

$subcategories = array();

if ($publisher->getConfig('idxcat_show_subcats') !== 'no') {
    // if this category has subcats
    if (isset($subcatsObj) && $total_subcats > 0) {
        foreach ($subcatsObj as $key => $subcat) {
            // Get the items count of this very category
            $subcat_total_items = isset($totalItems[$key]) ? $totalItems[$key] : 0;

            // Do we display empty sub-cats ?
            if (($subcat_total_items > 0) || ($publisher->getConfig('idxcat_show_subcats') === 'all')) {
                $subcat_id = $subcat->getVar('categoryid');
                // if we retreived the last item object for this category
                if (isset($lastItemObj[$subcat_id])) {
                    $subcat->setVar('last_itemid', $lastItemObj[$subcat_id]->itemid());
                    $subcat->setVar('last_title_link', $lastItemObj[$key]->getItemLink(false, $lastitemsize));
                }

                $numItems = isset($totalItems[$subcat_id]) ? $totalItems[$key] : 0;
                $subcat->setVar('itemcount', $numItems);
                // Put this subcat in the smarty variable
                $subcategories[$key] = $subcat->toArraySimple();
                //$total += $numItems;
            }

            if ($subcat_total_items > 0) {
                $subcat_id = $subcat->getVar('categoryid');
                // if we retreived the last item object for this category
                if (isset($lastItemObj[$subcat_id])) {
                    $subcat->setVar('last_itemid', $lastItemObj[$subcat_id]->itemid());
                    $subcat->setVar('last_title_link', $lastItemObj[$key]->getItemLink(false, $lastitemsize));
                }

                $numItems = isset($totalItems[$subcat_id]) ? $totalItems[$key] : 0;
                $subcat->setVar('itemcount', $numItems);
                // Put this subcat in the smarty variable
                $subcategories[$key] = $subcat->toArraySimple();
                //$total += $numItems;
            }
        }
        unset($key, $subcat, $subcatsObj);
    }
}

$category['subcats']      = $subcategories;
$category['subcatscount'] = count($subcategories);

$thiscategory_itemcount = isset($totalItems[$categoryid]) ? $totalItems[$categoryid] : 0;
$category['total']      = $thiscategory_itemcount;

if (count($itemsObj) > 0) {
    /*$userids = array();
    if ($itemsObj) {
        foreach ($itemsObj as $key => $thisitem) {
            $itemids[] = $thisitem->getVar('itemid');
            $userids[$thisitem->uid()] = 1;
        }
    }
    $memberHandler =& xoops_getHandler('member');
    //$users = $memberHandler->getUsers(new Criteria('uid', "(" . implode(',', array_keys($userids)) . ")", "IN"), true);
    */
    // Adding the items of the selected category

    for ($i = 0; $i < $totalItemOnPage; ++$i) {
        $item                 = $itemsObj[$i]->toArraySimple('default', $publisher->getConfig('item_title_size'));
        $item['categoryname'] = $categoryObj->name();
        $item['categorylink'] = "<a href='" . PublisherSeo::generateUrl('category', $itemsObj[$i]->categoryid(), $categoryObj->short_url()) . "'>" . $categoryObj->name() . '</a>';
        $item['who_when']     = $itemsObj[$i]->getWhoAndWhen();
        $xoopsTpl->append('items', $item);
    }

    if (isset($lastItemObj[$categoryObj->getVar('categoryid')]) && $lastItemObj[$categoryObj->getVar('categoryid')]) {
        $category['last_itemid']     = $lastItemObj[$categoryObj->getVar('categoryid')]->getVar('itemid');
        $category['last_title_link'] = $lastItemObj[$categoryObj->getVar('categoryid')]->getItemLink(false, $lastitemsize);
    }

    $xoopsTpl->assign('show_subtitle', $publisher->getConfig('cat_disp_subtitle'));
}

$categories   = array();
$categories[] = $category;
$xoopsTpl->assign('category', $category);
$xoopsTpl->assign('categories', $categories);

// Language constants
$xoopsTpl->assign('sectionname', $publisher->getModule()->getVar('name'));
$xoopsTpl->assign('whereInSection', $publisher->getModule()->getVar('name'));
$xoopsTpl->assign('module_dirname', $publisher->getModule()->getVar('dirname'));
$xoopsTpl->assign('lang_category_summary', sprintf(_MD_PUBLISHER_CATEGORY_SUMMARY, $categoryObj->name()));
$xoopsTpl->assign('lang_category_summary_info', sprintf(_MD_PUBLISHER_CATEGORY_SUMMARY_INFO, $categoryObj->name()));
$xoopsTpl->assign('lang_items_title', sprintf(_MD_PUBLISHER_ITEMS_TITLE, $categoryObj->name()));
$xoopsTpl->assign('module_home', publisherModuleHome($publisher->getConfig('format_linked_path')));
$xoopsTpl->assign('categoryPath', $category['categoryPath']);
$xoopsTpl->assign('selected_category', $categoryid);

// The Navigation Bar
include_once $GLOBALS['xoops']->path('class/pagenav.php');
$pagenav = new XoopsPageNav($thiscategory_itemcount, $publisher->getConfig('idxcat_index_perpage'), $start, 'start', 'categoryid=' . $categoryObj->getVar('categoryid'));
if ($publisher->getConfig('format_image_nav') == 1) {
    $navbar = '<div style="text-align:right;">' . $pagenav->renderImageNav() . '</div>';
} else {
    $navbar = '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';
}
$xoopsTpl->assign('navbar', $navbar);

/**
 * Generating meta information for this page
 */
$publisherMetagen = new PublisherMetagen($categoryObj->getVar('name'), $categoryObj->getVar('meta_keywords', 'n'), $categoryObj->getVar('meta_description', 'n'), $categoryObj->getCategoryPathForMetaTitle());
$publisherMetagen->createMetaTags();

// RSS Link
if ($publisher->getConfig('idxcat_show_rss_link') == 1) {
    $link = sprintf("<a href='%s' title='%s'><img src='%s' border=0 alt='%s'></a>", PUBLISHER_URL . '/backend.php?categoryid=' . $categoryid, _MD_PUBLISHER_RSSFEED, PUBLISHER_URL . '/assets/images/rss.gif', _MD_PUBLISHER_RSSFEED);
    $xoopsTpl->assign('rssfeed_link', $link);
}

include_once $GLOBALS['xoops']->path('footer.php');
