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
 */

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/header.php';

// At which record shall we start for the Categories
$catstart = Request::getInt('catstart', 0, 'GET');

// At which record shall we start for the ITEM
$start = Request::getInt('start', 0, 'GET');

// Number of categories at the top level
$totalCategories = $helper->getHandler('Category')->getCategoriesCount(0);

// if there ain't no category to display, let's get out of here
if (0 == $totalCategories) {
    redirect_header(XOOPS_URL, 2, _MD_PUBLISHER_NO_TOP_PERMISSIONS);
    //    exit;
}

$GLOBALS['xoopsOption']['template_main'] = 'publisher_display' . '_' . $helper->getConfig('idxcat_items_display_type') . '.tpl';
require_once $GLOBALS['xoops']->path('header.php');
require_once PUBLISHER_ROOT_PATH . '/footer.php';
/* @var  $gpermHandler XoopsGroupPermHandler */
$gpermHandler = xoops_getHandler('groupperm');

// Creating the top categories objects
$categoriesObj = $helper->getHandler('Category')->getCategories($helper->getConfig('idxcat_cat_perpage'), $catstart);

// if no categories are found, exit
$totalCategoriesOnPage = count($categoriesObj);
if (0 == $totalCategoriesOnPage) {
    redirect_header('javascript:history.go(-1)', 2, _MD_PUBLISHER_NO_CAT_EXISTS);
    //    exit;
}

// Get subcats of the top categories
$subcats = $helper->getHandler('Category')->getSubCats($categoriesObj);

// Count of items within each top categories
$totalItems = $helper->getHandler('Category')->publishedItemsCount();

// real total count of items
$real_total_items = $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_PUBLISHED]);

if (1 == $helper->getConfig('idxcat_display_last_item')) {
    // Get the last item in each category
    $lastItemObj = $helper->getHandler('Item')->getLastPublishedByCat(array_merge([$categoriesObj], $subcats));
}

// Max size of the title in the last item column
$lastitemsize = (int)$helper->getConfig('idxcat_last_item_size');

// Hide sub categories in main page only - hacked by Mowaffak
if ('nomain' === $helper->getConfig('idxcat_show_subcats')) {
    $helper->setConfig('idxcat_show_subcats', 'no');
}

$categories = [];
foreach ($categoriesObj as $catId => $category) {
    $total = 0;
    // Do we display sub categories ?
    if ('no' !== $helper->getConfig('idxcat_show_subcats')) {
        // if this category has subcats
        if (isset($subcats[$catId])) {
            foreach ($subcats[$catId] as $key => $subcat) {
                // Get the items count of this very category
                $subcat_total_items = isset($totalItems[$key]) ? $totalItems[$key] : 0;
                // Do we display empty sub-cats ?
                if (($subcat_total_items > 0) || ('all' === $helper->getConfig('idxcat_show_subcats'))) {
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
        $categories[$catId] = [];
    }

    $categories[$catId] = $category->toArrayTable($categories[$catId]);
}
unset($categoriesObj);

if (isset($categories[$catId])) {
    $categories[$catId]                 = $category->toArraySimple($categories[$catId]);
    $categories[$catId]['categoryPath'] = $category->getCategoryPath($helper->getConfig('format_linked_path'));
}

unset($catId, $category);

$xoopsTpl->assign('categories', $categories);

if ($helper->getConfig('index_display_last_items')) {
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

    // Creating the last ITEMs
    $itemsObj   = $helper->getHandler('Item')->getAllPublished($helper->getConfig('idxcat_index_perpage'), $start, -1, $sort, $order);
    $itemsCount = count($itemsObj);

    //todo: make config for summary size
    if ($itemsCount > 0) {
        foreach ($itemsObj as $itemObj) {
            $xoopsTpl->append('items', $itemObj->toArraySimple($helper->getConfig('idxcat_items_display_type'), $helper->getConfig('item_title_size'), 300, true)); //if no summary truncate body to 300
        }
        $xoopsTpl->assign('show_subtitle', $helper->getConfig('index_disp_subtitle'));
        unset($allcategories, $itemObj);
    }
    unset($itemsObj);
}

// Language constants
$xoopsTpl->assign('title_and_welcome', $helper->getConfig('index_title_and_welcome')); //SHINE ADDED DEBUG mainintro txt
$xoopsTpl->assign('lang_mainintro', $myts->displayTarea($helper->getConfig('index_welcome_msg'), 1));
$xoopsTpl->assign('sectionname', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('whereInSection', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('module_home', Publisher\Utility::moduleHome(false));
$xoopsTpl->assign('indexfooter', $myts->displayTarea($helper->getConfig('index_footer'), 1));

$xoopsTpl->assign('lang_category_summary', _MD_PUBLISHER_INDEX_CATEGORIES_SUMMARY);
$xoopsTpl->assign('lang_category_summary_info', _MD_PUBLISHER_INDEX_CATEGORIES_SUMMARY_INFO);
$xoopsTpl->assign('lang_items_title', _MD_PUBLISHER_INDEX_ITEMS);
$xoopsTpl->assign('indexpage', true);

require_once $GLOBALS['xoops']->path('class/pagenav.php');
// Category Navigation Bar
$pagenav = new \XoopsPageNav($totalCategories, $helper->getConfig('idxcat_cat_perpage'), $catstart, 'catstart', '');
if (1 == $helper->getConfig('format_image_nav')) {
    $xoopsTpl->assign('catnavbar', '<div style="text-align:right;">' . $pagenav->renderImageNav() . '</div>');
} else {
    $xoopsTpl->assign('catnavbar', '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>');
}
// ITEM Navigation Bar
$pagenav = new \XoopsPageNav($real_total_items, $helper->getConfig('idxcat_index_perpage'), $start, 'start', '');
if (1 == $helper->getConfig('format_image_nav')) {
    $xoopsTpl->assign('navbar', '<div style="text-align:right;">' . $pagenav->renderImageNav() . '</div>');
} else {
    $xoopsTpl->assign('navbar', '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>');
}
//show subcategories
$xoopsTpl->assign('show_subcats', $helper->getConfig('idxcat_show_subcats'));
$xoopsTpl->assign('displaylastitems', $helper->getConfig('index_display_last_items'));

/**
 * Generating meta information for this page
 */
$publisherMetagen = new Publisher\Metagen($helper->getModule()->getVar('name'));
$publisherMetagen->createMetaTags();

// RSS Link
if (1 == $helper->getConfig('idxcat_show_rss_link')) {
    $link = sprintf("<a href='%s' title='%s'><img src='%s' border=0 alt='%s'></a>", PUBLISHER_URL . '/backend.php', _MD_PUBLISHER_RSSFEED, PUBLISHER_URL . '/assets/images/rss.gif', _MD_PUBLISHER_RSSFEED);
    $xoopsTpl->assign('rssfeed_link', $link);
}

require_once $GLOBALS['xoops']->path('footer.php');
?>

<!--<script type="text/javascript">-->
<!--    $(document).ready(function () {-->
<!--        $("img").addClass("img-responsive");-->
<!--    });-->
<!--</script>-->
