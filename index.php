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
 * @version         $Id: index.php 10727 2013-01-09 22:03:19Z trabis $
 */

include_once __DIR__ . '/header.php';

// At which record shall we start for the Categories
$catstart = XoopsRequest::getInt('catstart', 0, 'GET');

// At which record shall we start for the ITEM
$start = XoopsRequest::getInt('start', 0, 'GET');

// Number of categories at the top level
$totalCategories = $publisher->getHandler('category')->getCategoriesCount(0);

// if there ain't no category to display, let's get out of here
if ($totalCategories == 0) {
    redirect_header(XOOPS_URL, 2, _MD_PUBLISHER_NO_TOP_PERMISSIONS);
    //    exit;
}

$xoopsOption['template_main'] = 'publisher_display' . '_' . $publisher->getConfig('idxcat_items_display_type') . '.tpl';
include_once $GLOBALS['xoops']->path('header.php');
include_once PUBLISHER_ROOT_PATH . '/footer.php';

$gpermHandler = xoops_getHandler('groupperm');

// Creating the top categories objects
$categoriesObj = $publisher->getHandler('category')->getCategories($publisher->getConfig('idxcat_cat_perpage'), $catstart);

// if no categories are found, exit
$totalCategoriesOnPage = count($categoriesObj);
if ($totalCategoriesOnPage == 0) {
    redirect_header('javascript:history.go(-1)', 2, _MD_PUBLISHER_NO_CAT_EXISTS);
    //    exit;
}

// Get subcats of the top categories
$subcats = $publisher->getHandler('category')->getSubCats($categoriesObj);

// Count of items within each top categories
$totalItems = $publisher->getHandler('category')->publishedItemsCount();

// real total count of items
$real_total_items = $publisher->getHandler('item')->getItemsCount(-1, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED));

if ($publisher->getConfig('idxcat_display_last_item') == 1) {
    // Get the last item in each category
    $lastItemObj = $publisher->getHandler('item')->getLastPublishedByCat(array_merge(array($categoriesObj), $subcats));
}

// Max size of the title in the last item column
$lastitemsize = (int)$publisher->getConfig('idxcat_last_item_size');

// Hide sub categories in main page only - hacked by Mowaffak
if ('nomain' === $publisher->getConfig('idxcat_show_subcats')) {
    $publisher->setConfig('idxcat_show_subcats', 'no');
}

$categories = array();
foreach ($categoriesObj as $catId => $category) {
    $total = 0;
    // Do we display sub categories ?
    if ($publisher->getConfig('idxcat_show_subcats') !== 'no') {
        // if this category has subcats
        if (isset($subcats[$catId])) {
            foreach ($subcats[$catId] as $key => $subcat) {
                // Get the items count of this very category
                $subcat_total_items = isset($totalItems[$key]) ? $totalItems[$key] : 0;
                // Do we display empty sub-cats ?
                if (($subcat_total_items > 0) || ($publisher->getConfig('idxcat_show_subcats') === 'all')) {
                    $subcat_id = $subcat->getVar('categoryid');
                    // if we retrieved the last item object for this category
                    if (isset($lastItemObj[$subcat_id])) {
                        $subcat->setVar('last_itemid', $lastItemObj[$subcat_id]->itemid());
                        $subcat->setVar('last_title_link', $lastItemObj[$subcat_id]->getItemLink(false, $lastitemsize));
                    }

                    $numItems = isset($totalItems[$subcat_id]) ? $totalItems[$key] : 0;
                    $subcat->setVar('itemcount', $numItems);
                    // Put this subcat in the smarty variable
                    $categories[$catId]['subcats'][$key] = $subcat->toArrayTable();
                    //$total += $numItems;
                }
            }
            //            unset($subcat);
        }
    }

    $categories[$catId]['subcatscount'] = isset($subcats[$catId]) ? count($subcats[$catId]) : 0;

    // Get the items count of this very category
    if (isset($totalItems[$catId]) && $totalItems[$catId] > 0) {
        $total += $totalItems[$catId];
    }
    // I'm commenting out this to also display empty categories...
    // if ($total > 0) {
    if (isset($lastItemObj[$catId])) {
        $category->setVar('last_itemid', $lastItemObj[$catId]->getVar('itemid'));
        $category->setVar('last_title_link', $lastItemObj[$catId]->getItemLink(false, $lastitemsize));
    }
    $category->setVar('itemcount', $total);

    if (!isset($categories[$catId])) {
        $categories[$catId] = array();
    }

    $categories[$catId] = $category->toArrayTable($categories[$catId]);
}
unset($categoriesObj);

if (isset($categories[$catId])) {
    $categories[$catId]                 = $category->toArraySimple($categories[$catId]);
    $categories[$catId]['categoryPath'] = $category->getCategoryPath($publisher->getConfig('format_linked_path'));
}

unset($catId, $category);

$xoopsTpl->assign('categories', $categories);

if ($publisher->getConfig('index_display_last_items')) {
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

    // Creating the last ITEMs
    $itemsObj   = $publisher->getHandler('item')->getAllPublished($publisher->getConfig('idxcat_index_perpage'), $start, -1, $sort, $order);
    $itemsCount = count($itemsObj);

    //todo: make config for summary size
    if ($itemsCount > 0) {
        foreach ($itemsObj as $itemObj) {
            $xoopsTpl->append('items', $itemObj->toArraySimple($publisher->getConfig('idxcat_items_display_type'), $publisher->getConfig('item_title_size'), 300, true)); //if no summary truncate body to 300
        }
        $xoopsTpl->assign('show_subtitle', $publisher->getConfig('index_disp_subtitle'));
        unset($allcategories, $itemObj);
    }
    unset($itemsObj);
}

// Language constants
$xoopsTpl->assign('title_and_welcome', $publisher->getConfig('index_title_and_welcome')); //SHINE ADDED DEBUG mainintro txt
$xoopsTpl->assign('lang_mainintro', $myts->displayTarea($publisher->getConfig('index_welcome_msg'), 1));
$xoopsTpl->assign('sectionname', $publisher->getModule()->getVar('name'));
$xoopsTpl->assign('whereInSection', $publisher->getModule()->getVar('name'));
$xoopsTpl->assign('module_home', publisherModuleHome(false));
$xoopsTpl->assign('indexfooter', $myts->displayTarea($publisher->getConfig('index_footer'), 1));

$xoopsTpl->assign('lang_category_summary', _MD_PUBLISHER_INDEX_CATEGORIES_SUMMARY);
$xoopsTpl->assign('lang_category_summary_info', _MD_PUBLISHER_INDEX_CATEGORIES_SUMMARY_INFO);
$xoopsTpl->assign('lang_items_title', _MD_PUBLISHER_INDEX_ITEMS);
$xoopsTpl->assign('indexpage', true);

include_once $GLOBALS['xoops']->path('class/pagenav.php');
// Category Navigation Bar
$pagenav = new XoopsPageNav($totalCategories, $publisher->getConfig('idxcat_cat_perpage'), $catstart, 'catstart', '');
if ($publisher->getConfig('format_image_nav') == 1) {
    $xoopsTpl->assign('catnavbar', '<div style="text-align:right;">' . $pagenav->renderImageNav() . '</div>');
} else {
    $xoopsTpl->assign('catnavbar', '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>');
}
// ITEM Navigation Bar
$pagenav = new XoopsPageNav($real_total_items, $publisher->getConfig('idxcat_index_perpage'), $start, 'start', '');
if ($publisher->getConfig('format_image_nav') == 1) {
    $xoopsTpl->assign('navbar', '<div style="text-align:right;">' . $pagenav->renderImageNav() . '</div>');
} else {
    $xoopsTpl->assign('navbar', '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>');
}
//show subcategories
$xoopsTpl->assign('show_subcats', $publisher->getConfig('idxcat_show_subcats'));
$xoopsTpl->assign('displaylastitems', $publisher->getConfig('index_display_last_items'));

/**
 * Generating meta information for this page
 */
$publisherMetagen = new PublisherMetagen($publisher->getModule()->getVar('name'));
$publisherMetagen->createMetaTags();

// RSS Link
if ($publisher->getConfig('idxcat_show_rss_link') == 1) {
    $link = sprintf("<a href='%s' title='%s'><img src='%s' border=0 alt='%s'></a>", PUBLISHER_URL . '/backend.php', _MD_PUBLISHER_RSSFEED, PUBLISHER_URL . '/assets/images/rss.gif', _MD_PUBLISHER_RSSFEED);
    $xoopsTpl->assign('rssfeed_link', $link);
}

include_once $GLOBALS['xoops']->path('footer.php');
