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
 * @subpackage      Utils
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/include/common.php';


$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
//$xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/jquery.popeye-2.0.4.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
$xoTheme->addScript(PUBLISHER_URL . '/assets/js/cookies.js');
$xoTheme->addScript(PUBLISHER_URL . '/assets/js/funcs.js');

$xoopsTpl->assign('xoops_module_header', '<link rel="alternate" type="application/rss+xml" title="' . $helper->getModule()->name() . '" href="' . PUBLISHER_URL . '/backend.php">' . @$xoopsTpl->get_template_vars('xoops_module_header'));

$xoopsTpl->assign('publisher_adminpage', "<a href='" . PUBLISHER_URL . "/admin/index.php'>" . _MD_PUBLISHER_ADMIN_PAGE . '</a>');
$xoopsTpl->assign('isAdmin', $publisherIsAdmin);
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
$xoopsTpl->assign('displayarticlescount', $helper->getConfig('idxcat_display_art_count'));
$xoopsTpl->assign('display_date_col', $helper->getConfig('idxcat_display_date_col'));
$xoopsTpl->assign('display_hits_col', $helper->getConfig('idxcat_display_hits_col'));
$xoopsTpl->assign('cat_list_image_width', $helper->getConfig('cat_list_image_width'));
$xoopsTpl->assign('cat_main_image_width', $helper->getConfig('cat_main_image_width'));
