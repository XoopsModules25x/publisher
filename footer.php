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

use XoopsModules\Publisher\{Helper,
    Utility
};

require_once __DIR__ . '/include/common.php';

global $xoTheme;

$helper = Helper::getInstance();

$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
//$xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/jquery.popeye-2.0.4.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
$xoTheme->addScript(PUBLISHER_URL . '/assets/js/cookies.js');
$xoTheme->addScript(PUBLISHER_URL . '/assets/js/funcs.js');

$xoopsTpl->assign('xoops_module_header', '<link rel="alternate" type="application/rss+xml" title="' . $helper->getModule()->name() . '" href="' . PUBLISHER_URL . '/backend.php">' . @$xoopsTpl->get_template_vars('xoops_module_header'));

$xoopsTpl->assign('publisher_adminpage', "<a href='" . PUBLISHER_URL . "/admin/index.php'>" . _MD_PUBLISHER_ADMIN_PAGE . '</a>');
$xoopsTpl->assign('isAdmin', Utility::userIsAdmin());
$xoopsTpl->assign('publisher_url', PUBLISHER_URL);
$xoopsTpl->assign('publisherImagesUrl', PUBLISHER_IMAGES_URL);

$xoopsTpl->assign('displayType', $helper->getConfig('idxcat_items_display_type'));

// display_category_summary enabled by Freeform Solutions March 21 2006
$xoopsTpl->assign('display_category_summary', $helper->getConfig('cat_display_summary'));

$xoopsTpl->assign('displayList', 'list' === $helper->getConfig('idxcat_items_display_type'));
$xoopsTpl->assign('displayFull', 'full' === $helper->getConfig('idxcat_items_display_type'));
$xoopsTpl->assign('module_dirname', $helper->getModule()->dirname());

$xoopsTpl->assign('displaylastitem', $helper->getConfig('idxcat_display_last_item'));
$xoopsTpl->assign('displaysubcatdsc', $helper->getConfig('idxcat_display_subcat_dsc'));
$xoopsTpl->assign('publisher_display_breadcrumb', $helper->getConfig('display_breadcrumb'));
$xoopsTpl->assign('collapsable_heading', $helper->getConfig('idxcat_collaps_heading'));
$xoopsTpl->assign('display_comment_link', $helper->getConfig('item_disp_comment_link'));
$xoopsTpl->assign('display_whowhen_link', $helper->getConfig('item_disp_whowhen_link'));
$xoopsTpl->assign('display_who_link', $helper->getConfig('item_disp_who_link'));
$xoopsTpl->assign('display_when_link', $helper->getConfig('item_disp_when_link'));
$xoopsTpl->assign('display_hits_link', $helper->getConfig('item_disp_hits_link'));
$xoopsTpl->assign('display_print_link', $helper->getConfig('item_disp_print_link'));
$xoopsTpl->assign('display_pdf_button', $helper->getConfig('item_disp_pdf_button'));
$xoopsTpl->assign('display_itemcategory', $helper->getConfig('item_disp_itemcategory'));
$xoopsTpl->assign('display_defaultimage', $helper->getConfig('item_disp_defaultimage'));

$xoopsTpl->assign('displayarticlescount', $helper->getConfig('idxcat_display_art_count'));
$xoopsTpl->assign('display_date_col', $helper->getConfig('idxcat_display_date_col'));
$xoopsTpl->assign('display_hits_col', $helper->getConfig('idxcat_display_hits_col'));
$xoopsTpl->assign('cat_list_image_width', $helper->getConfig('cat_list_image_width'));
$xoopsTpl->assign('cat_main_image_width', $helper->getConfig('cat_main_image_width'));
$xoopsTpl->assign('display_mainimage', $helper->getConfig('idxcat_display_mainimage'));
$xoopsTpl->assign('display_summary', $helper->getConfig('idxcat_display_summary'));
$xoopsTpl->assign('display_readmore', $helper->getConfig('idxcat_display_readmore'));
$xoopsTpl->assign('display_category', $helper->getConfig('idxcat_display_category'));
$xoopsTpl->assign('display_poster', $helper->getConfig('idxcat_display_poster'));
$xoopsTpl->assign('display_commentlink', $helper->getConfig('idxcat_disp_commentlink'));

$xoopsTpl->assign('displaymainimage', $helper->getConfig('authorpage_display_image'));
$xoopsTpl->assign('displaysummary', $helper->getConfig('authorpage_disp_summary'));
$xoopsTpl->assign('displayhits', $helper->getConfig('authorpage_display_hits'));
$xoopsTpl->assign('displaycomment', $helper->getConfig('authorpage_disp_comment'));
$xoopsTpl->assign('displayrating', $helper->getConfig('authorpage_display_rating'));
$xoopsTpl->assign('displaylike', $helper->getConfig('ratingbars'));

$xoopsTpl->assign('show_date_col', $helper->getConfig('allitem_display_date_col'));
$xoopsTpl->assign('show_hits_col', $helper->getConfig('allitem_display_hits_col'));
$xoopsTpl->assign('show_mainimage', $helper->getConfig('allitem_display_mainimage'));
$xoopsTpl->assign('show_summary', $helper->getConfig('allitem_display_summary'));
$xoopsTpl->assign('show_readmore', $helper->getConfig('allitem_display_readmore'));
$xoopsTpl->assign('show_category', $helper->getConfig('allitem_display_category'));
$xoopsTpl->assign('show_poster', $helper->getConfig('allitem_display_poster'));
$xoopsTpl->assign('show_commentlink', $helper->getConfig('allitem_disp_commentlink'));

$xoopsTpl->assign('showdate', $helper->getConfig('archive_display_date_col'));
$xoopsTpl->assign('showhits', $helper->getConfig('archive_display_hits_col'));
$xoopsTpl->assign('showcategory', $helper->getConfig('archive_display_category'));
$xoopsTpl->assign('showposter', $helper->getConfig('archive_display_poster'));
$xoopsTpl->assign('showcomment', $helper->getConfig('archive_display_comment'));
$xoopsTpl->assign('showprintlink', $helper->getConfig('archive_display_printlink'));
$xoopsTpl->assign('showpdfbutton', $helper->getConfig('archive_display_pdfbutton'));
$xoopsTpl->assign('showemaillink', $helper->getConfig('archive_display_emaillink'));
$xoopsTpl->assign('showsummary', $helper->getConfig('archive_display_summary'));
$xoopsTpl->assign('showmainimage', $helper->getConfig('archive_display_mainimage'));
