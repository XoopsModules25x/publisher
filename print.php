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

require_once __DIR__ . '/header.php';
require_once $GLOBALS['xoops']->path('class/template.php');

$itemid = Request::getInt('itemid', 0, 'GET');

if (0 == $itemid) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

// Creating the ITEM object for the selected ITEM
$itemObj = $helper->getHandler('Item')->get($itemid);

// if the selected ITEM was not found, exit
if ($itemObj->notLoaded()) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

// Check user permissions to access that category of the selected ITEM
if (!$itemObj->accessGranted()) {
    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    //    exit;
}

// Creating the category object that holds the selected ITEM
$categoryObj = $itemObj->getCategory();

$xoopsTpl = new \XoopsTpl();
$myts     = \MyTextSanitizer::getInstance();

$item['title']        = $itemObj->getTitle();
$item['body']         = $itemObj->getBody();
$item['categoryname'] = $myts->displayTarea($categoryObj->name());

$mainImage = $itemObj->getMainImage();
if ('' != $mainImage['image_path']) {
    $item['image'] = '<img src="' . $mainImage['image_path'] . '" alt="' . $myts->undoHtmlSpecialChars($mainImage['image_name']) . '">';
}
$xoopsTpl->assign('item', $item);
$xoopsTpl->assign('printtitle', $GLOBALS['xoopsConfig']['sitename'] . ' - ' . Publisher\Utility::html2text($categoryObj->getCategoryPath()) . ' > ' . $myts->displayTarea($itemObj->getTitle()));
$xoopsTpl->assign('printlogourl', $helper->getConfig('print_logourl'));
$xoopsTpl->assign('printheader', $myts->displayTarea($helper->getConfig('print_header'), 1));
$xoopsTpl->assign('lang_category', _CO_PUBLISHER_CATEGORY);
$xoopsTpl->assign('lang_author_date', sprintf(_MD_PUBLISHER_WHO_WHEN, $itemObj->posterName(), $itemObj->getDatesub()));

$doNotStartPrint = false;
$noTitle         = false;
$noCategory      = false;
$smartPopup      = false;

$xoopsTpl->assign('doNotStartPrint', $doNotStartPrint);
$xoopsTpl->assign('noTitle', $noTitle);
$xoopsTpl->assign('smartPopup', $smartPopup);
$xoopsTpl->assign('current_language', $GLOBALS['xoopsConfig']['language']);

if ('item footer' === $helper->getConfig('print_footer') || 'both' === $helper->getConfig('print_footer')) {
    $xoopsTpl->assign('itemfooter', $myts->displayTarea($helper->getConfig('item_footer'), 1));
}
if ('index footer' === $helper->getConfig('print_footer') || 'both' === $helper->getConfig('print_footer')) {
    $xoopsTpl->assign('indexfooter', $myts->displayTarea($helper->getConfig('index_footer'), 1));
}

$xoopsTpl->assign('display_whowhen_link', $helper->getConfig('item_disp_whowhen_link'));

$xoopsTpl->display('db:publisher_print.tpl');
