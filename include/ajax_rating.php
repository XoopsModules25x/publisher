<?php
/*
 Page:           rpc.php
 Created:        Aug 2006
 Last Mod:       Mar 18 2007
 This page handles the 'AJAX' type response if the user
 has Javascript enabled.
 ---------------------------------------------------------
 ryan masuga, masugadesign.com
 ryan@masugadesign.com
 Licensed under a Creative Commons Attribution 3.0 License.
 http://creativecommons.org/licenses/by/3.0/
 See ajaxrating.txt for full credit details.
 --------------------------------------------------------- */

//  Author: Trabis
//  URL: http://www.xuups.com
//  E-Mail: lusopoemas@gmail.com

use Xmf\Request;
use XoopsModules\Publisher;

require_once dirname(__DIR__) . '/header.php';
$helper = Publisher\Helper::getInstance();

error_reporting(0);
$xoopsLogger->activated = false;

header('Cache-Control: no-cache');
header('Pragma: nocache');

//getting the values
$rating = Request::getInt('rating', 0, 'GET');
$itemid = Request::getInt('itemid', 0, 'GET');

$helper->loadLanguage('main');
$groups = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
/* @var $gpermHandler XoopsGroupPermHandler */
$gpermHandler = $helper->getHandler('Groupperm');
/* @var $configHandler XoopsConfigHandler */
$configHandler = xoops_getHandler('config');
$module_id     = $helper->getModule()->getVar('mid');

//Checking permissions
//if (!$helper->getConfig('perm_rating') || !$gpermHandler->checkRight('global', _PUBLISHER_RATE, $groups, $module_id)) {
//    $output = "unit_long$itemid|" . _NOPERM . "\n";
//    echo $output;
//    exit();
//}

try {
    if (!$helper->getConfig('perm_rating') || !$gpermHandler->checkRight('global', _PUBLISHER_RATE, $groups, $module_id)) {
        throw new RuntimeException(_NOPERM);
    }
} catch (Exception $e) {
    $helper->addLog($e);
    //    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    $output = "unit_long$itemid|" . _NOPERM . "\n";
    echo $output;
}

$rating_unitwidth = 30;
$units            = 5;

//if ($rating > 5 || $rating < 1) {
//    $output = "unit_long$itemid|" . _MD_PUBLISHER_VOTE_BAD . "\n";
//    echo $output;
//    exit();
//}

try {
    if ($rating > 5 || $rating < 1) {
        throw new RuntimeException(_MD_PUBLISHER_VOTE_BAD);
    }
} catch (Exception $e) {
    $helper->addLog($e);
    //    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    $output = "unit_long$itemid|" . _MD_PUBLISHER_VOTE_BAD . "\n";
    echo $output;
}

$criteria   = new \Criteria('itemid', $itemid);
$ratingObjs = $helper->getHandler('Rating')->getObjects($criteria);

$uid            = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
$count          = count($ratingObjs);
$current_rating = 0;
$voted          = false;
$ip             = getenv('REMOTE_ADDR');

foreach ($ratingObjs as $ratingObj) {
    $current_rating += $ratingObj->getVar('rate');
    if ($ratingObj->getVar('ip') == $ip || ($uid > 0 && $uid == $ratingObj->getVar('uid'))) {
        $voted = true;
    }
}

//if ($voted) {
//    $output = "unit_long$itemid|" . _MD_PUBLISHER_VOTE_ALREADY . "\n";
//    echo $output;
//    exit();
//}

try {
    if ($voted) {
        throw new RuntimeException(_MD_PUBLISHER_VOTE_ALREADY);
    }
} catch (Exception $e) {
    $helper->addLog($e);
    //    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    $output = "unit_long$itemid|" . _MD_PUBLISHER_VOTE_ALREADY . "\n";
    echo $output;
}

$newRatingObj = $helper->getHandler('Rating')->create();
$newRatingObj->setVar('itemid', $itemid);
$newRatingObj->setVar('ip', $ip);
$newRatingObj->setVar('uid', $uid);
$newRatingObj->setVar('rate', $rating);
$newRatingObj->setVar('date', time());
$helper->getHandler('Rating')->insert($newRatingObj);

$current_rating += $rating;
++$count;

$helper->getHandler('Item')->updateAll('rating', number_format($current_rating / $count, 4), $criteria, true);
$helper->getHandler('Item')->updateAll('votes', $count, $criteria, true);

$tense = 1 == $count ? _MD_PUBLISHER_VOTE_VOTE : _MD_PUBLISHER_VOTE_VOTES; //plural form votes/vote

// $new_back is what gets 'drawn' on your page after a successful 'AJAX/Javascript' vote
$new_back = [];

$new_back[] .= '<div class="publisher_unit-rating" style="width:' . $units * $rating_unitwidth . 'px;">';
$new_back[] .= '<div class="publisher_current-rating" style="width:' . (0 !== $count ? number_format($current_rating / $count, 2) * $rating_unitwidth : 0) . 'px;">' . _MD_PUBLISHER_VOTE_RATING . '</div>';
$new_back[] .= '<div class="publisher_r1-unit">1</div>';
$new_back[] .= '<div class="publisher_r2-unit">2</div>';
$new_back[] .= '<div class="publisher_r3-unit">3</div>';
$new_back[] .= '<div class="publisher_r4-unit">4</div>';
$new_back[] .= '<div class="publisher_r5-unit">5</div>';
$new_back[] .= '<div class="publisher_r6-unit">6</div>';
$new_back[] .= '<div class="publisher_r7-unit">7</div>';
$new_back[] .= '<div class="publisher_r8-unit">8</div>';
$new_back[] .= '<div class="publisher_r9-unit">9</div>';
$new_back[] .= '<div class="publisher_r10-unit">10</div>';
$new_back[] .= '</div>';
$new_back[] .= '<div class="publisher_voted">' . _MD_PUBLISHER_VOTE_RATING . ' <strong>' . (0 !== $count ? number_format($current_rating / $count, 2) : 0) . '</strong>/' . $units . ' (' . $count . ' ' . $tense . ')</div>';
$new_back[] .= '<div class="publisher_thanks">' . _MD_PUBLISHER_VOTE_THANKS . '</div>';

$allnewback = implode("\n", $new_back);

// ========================

//name of the div id to be updated | the html that needs to be changed
$output = "unit_long$itemid|$allnewback";
echo $output;
