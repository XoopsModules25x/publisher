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
 * @version         $Id: backend.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__FILE__) . '/header.php';
xoops_load('XoopsLocal');

error_reporting(0);
$GLOBALS['xoopsLogger']->activated = false;

include_once XOOPS_ROOT_PATH . '/class/template.php';
if (function_exists('mb_http_output')) {
    mb_http_output('pass');
}

$categoryid = isset($_GET['categoryid']) ? $_GET['categoryid'] : -1;

if ($categoryid != -1) {
    $categoryObj = $publisher->getHandler('category')->get($categoryid);
}

header('Content-Type:text/xml; charset=' . _CHARSET);
$tpl = new XoopsTpl();
$tpl->xoops_setCaching(2);
$tpl->xoops_setCacheTime(0);
$myts = MyTextSanitizer::getInstance();
if (!$tpl->is_cached('db:publisher_rss.html')) {
    $channel_category = $publisher->getModule()->name();
    // Check if ML Hack is installed, and if yes, parse the $content in formatForML
    if (method_exists($myts, 'formatForML')) {
        $xoopsConfig['sitename'] = $myts->formatForML($xoopsConfig['sitename']);
        $xoopsConfig['slogan'] = $myts->formatForML($xoopsConfig['slogan']);
        $channel_category = $myts->formatForML($channel_category);
    }
    $tpl->assign('channel_charset', _CHARSET);
    $tpl->assign('channel_title', htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES));
    $tpl->assign('channel_link', PUBLISHER_URL);
    $tpl->assign('channel_desc', htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES));
    $tpl->assign('channel_lastbuild', XoopsLocal::formatTimestamp(time(), 'rss'));
    $tpl->assign('channel_webmaster', $xoopsConfig['adminmail']);
    $tpl->assign('channel_editor', $xoopsConfig['adminmail']);

    if ($categoryid != -1) {
        $channel_category .= " > " . $categoryObj->name();
    }

    $tpl->assign('channel_category', htmlspecialchars($channel_category));
    $tpl->assign('channel_generator', $publisher->getModule()->name());
    $tpl->assign('channel_language', _LANGCODE);
    $tpl->assign('image_url', XOOPS_URL . '/images/logo.gif');
    $dimention = getimagesize(XOOPS_ROOT_PATH . '/images/logo.gif');
    if (empty($dimention[0])) {
        $width = 140;
        $height = 140;
    } else {
        $width = ($dimention[0] > 140) ? 140 : $dimention[0];
        $dimention[1] = $dimention[1] * $width / $dimention[0];
        $height = ($dimention[1] > 140) ? $dimention[1] * $dimention[0] / 140 : $dimention[1];
    }
    $tpl->assign('image_width', $width);
    $tpl->assign('image_height', $height);
    $sarray = $publisher->getHandler('item')->getAllPublished(10, 0, $categoryid);
    if (is_array($sarray)) {
        $count = $sarray;
        foreach ($sarray as $item) {
            $tpl->append('items',
                         array('title' => htmlspecialchars($item->title(), ENT_QUOTES),
                               'link' => $item->getItemUrl(),
                               'guid' => $item->getItemUrl(),
                               'pubdate' => XoopsLocal::formatTimestamp($item->getVar('datesub'), 'rss'),
                               'description' => htmlspecialchars($item->getBlockSummary(300, true), ENT_QUOTES)));
        }
    }
}
$tpl->display('db:publisher_rss.html');