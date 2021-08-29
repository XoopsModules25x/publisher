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
/** @var Helper $helper */

require_once __DIR__ . '/header.php';
//xoops_load('XoopsLocal'); //mb

error_reporting(0);
$GLOBALS['xoopsLogger']->activated = false;

require_once $GLOBALS['xoops']->path('class/template.php');
if (function_exists('mb_http_output')) {
    mb_http_output('pass');
}

$categoryid = Request::getInt('categoryid', -1, 'GET');

if (-1 != $categoryid) {
    $categoryObj = $helper->getHandler('Category')->get($categoryid);
}

header('Content-Type:text/xml; charset=' . _CHARSET);
$tpl          = new \XoopsTpl();
$tpl->caching = 2;
//$tpl->xoops_setCacheTime(0);
$tpl->cache_lifetime = 0;
$myts                = \MyTextSanitizer::getInstance();
if (!$tpl->is_cached('db:publisher_rss.tpl')) {
    //    xoops_load('XoopsLocal');
    $channelCategory = $helper->getModule()->name();
    // Check if ML Hack is installed, and if yes, parse the $content in formatForML
    if (method_exists($myts, 'formatForML')) {
        $GLOBALS['xoopsConfig']['sitename'] = $myts->formatForML($GLOBALS['xoopsConfig']['sitename']);
        $GLOBALS['xoopsConfig']['slogan']   = $myts->formatForML($GLOBALS['xoopsConfig']['slogan']);
        $channelCategory                    = $myts->formatForML($channelCategory);
    }
    $tpl->assign('channel_charset', _CHARSET);
    $tpl->assign('channel_title', htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES | ENT_HTML5));
    $tpl->assign('channel_link', htmlspecialchars(PUBLISHER_URL, ENT_QUOTES | ENT_HTML5));
    $tpl->assign('channel_desc', htmlspecialchars($GLOBALS['xoopsConfig']['slogan'], ENT_QUOTES | ENT_HTML5));
    //mb    $tpl->assign('channel_lastbuild', XoopsLocal::formatTimestamp(time(), 'rss'));
    $tpl->assign('channel_lastbuild', formatTimestamp(time(), 'rss'));
    $tpl->assign('channel_webmaster', $GLOBALS['xoopsConfig']['adminmail'] . '( ' . htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES | ENT_HTML5) . ' )');
    $tpl->assign('channel_editor', $GLOBALS['xoopsConfig']['adminmail'] . '( ' . htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES | ENT_HTML5) . ' )');

    if (-1 != $categoryid) {
        $channelCategory .= ' > ' . $categoryObj->name();
    }

    $tpl->assign('channelCategory', htmlspecialchars($channelCategory, ENT_QUOTES | ENT_HTML5));
    $tpl->assign('channel_generator', $helper->getModule()->name());
    $tpl->assign('channel_language', _LANGCODE);
    $tpl->assign('image_url', XOOPS_URL . '/images/logo.png');
    $dimension = getimagesize($GLOBALS['xoops']->path('images/logo.png'));
    if (empty($dimension[0])) {
        $width  = 140;
        $height = 140;
    } else {
        $width        = ($dimension[0] > 140) ? 140 : $dimension[0];
        $dimension[1] = $dimension[1] * $width / $dimension[0];
        $height       = ($dimension[1] > 140) ? $dimension[1] * $dimension[0] / 140 : $dimension[1];
    }
    $height = round($height, 0, PHP_ROUND_HALF_UP);
    $tpl->assign('image_width', $width);
    $tpl->assign('image_height', $height);
    $sarray = $helper->getHandler('Item')->getAllPublished(10, 0, $categoryid);
    if (!empty($sarray) && is_array($sarray)) {
        $count = $sarray;
        foreach ($sarray as $item) {
            $tpl->append(
                'items',
                [
                    'title'       => htmlspecialchars($item->getTitle(), ENT_QUOTES | ENT_HTML5),
                    'link'        => htmlspecialchars($item->getItemUrl(), ENT_QUOTES | ENT_HTML5),
                    'guid'        => $item->getItemUrl(),
                    //mb            'pubdate'     => XoopsLocal::formatTimestamp($item->getVar('datesub'), 'rss'),
                    'pubdate'     => formatTimestamp($item->getVar('datesub'), 'rss'),
                    'description' => htmlspecialchars($item->getBlockSummary(300, true), ENT_QUOTES | ENT_HTML5),
                ]
            );
        }
        //        unset($item);
    }
}
$tpl->display('db:publisher_rss.tpl');
