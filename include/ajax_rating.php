<?php

declare(strict_types=1);
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
use XoopsModules\Publisher\{GroupPermHandler,
    Helper,
    Rating
};

require_once \dirname(__DIR__) . '/header.php';
$helper = Helper::getInstance();

error_reporting(0);
$xoopsLogger->activated = false;

header('Cache-Control: no-cache');
header('Pragma: nocache');

//getting the values
$rating = Request::getInt('rating', 0, 'GET');
$itemId = Request::getInt('itemid', 0, 'GET');

$helper->loadLanguage('main');
$groups = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
/** @var GroupPermHandler $grouppermHandler */
$grouppermHandler = $helper->getHandler('GroupPerm');
/** @var \XoopsConfigHandler $configHandler */
$configHandler = xoops_getHandler('config');
$moduleId      = $helper->getModule()->getVar('mid');

//Checking permissions
//if (!$helper->getConfig('perm_rating') || !$grouppermHandler->checkRight('global', _PUBLISHER_RATE, $groups, $moduleId)) {
//    $output = "unit_long$itemId|" . _NOPERM . "\n";
//    echo $output;
//    exit();
//}

try {
    if (!$helper->getConfig('perm_rating') || !$grouppermHandler->checkRight('global', _PUBLISHER_RATE, $groups, $moduleId)) {
        throw new RuntimeException(_NOPERM);
    }
} catch (\Throwable $e) {
    $helper->addLog($e);
    //    redirect_header('<script>javascript:history.go(-1)</script>', 1, _NOPERM);
    $output = "unit_long$itemId|" . _NOPERM . "\n";
    echo $output;
}

$ratingUnitWidth = 30;
$units           = 5;

//if ($rating > 5 || $rating < 1) {
//    $output = "unit_long$itemId|" . _MD_PUBLISHER_VOTE_BAD . "\n";
//    echo $output;
//    exit();
//}

try {
    if ($rating > 5 || $rating < 1) {
        throw new RuntimeException(_MD_PUBLISHER_VOTE_BAD);
    }
} catch (\Throwable $e) {
    $helper->addLog($e);
    //    redirect_header('<script>javascript:history.go(-1)</script>', 1, _NOPERM);
    $output = "unit_long$itemId|" . _MD_PUBLISHER_VOTE_BAD . "\n";
    echo $output;
}

$criteria   = new \Criteria('itemid', $itemId);
$ratingObjs = $helper->getHandler('Rating')->getObjects($criteria);

$uid           = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
$count         = count($ratingObjs);
$currentRating = 0;
$voted         = false;
$ip            = getenv('REMOTE_ADDR');

/** @var Rating $ratingObj */
foreach ($ratingObjs as $ratingObj) {
    $currentRating += $ratingObj->getVar('rate');
    if ($ratingObj->getVar('ip') == $ip || ($uid > 0 && $uid == $ratingObj->getVar('uid'))) {
        $voted = true;
    }
}

//if ($voted) {
//    $output = "unit_long$itemId|" . _MD_PUBLISHER_VOTE_ALREADY . "\n";
//    echo $output;
//    exit();
//}

try {
    if ($voted) {
        throw new RuntimeException(_MD_PUBLISHER_VOTE_ALREADY);
    }
} catch (\Throwable $e) {
    $helper->addLog($e);
    //    redirect_header('<script>javascript:history.go(-1)</script>', 1, _NOPERM);
    $output = "unit_long$itemId|" . _MD_PUBLISHER_VOTE_ALREADY . "\n";
    echo $output;
}

$newRatingObj = $helper->getHandler('Rating')->create();
$newRatingObj->setVar('itemid', $itemId);
$newRatingObj->setVar('ip', $ip);
$newRatingObj->setVar('uid', $uid);
$newRatingObj->setVar('rate', $rating);
$newRatingObj->setVar('date', time());
$helper->getHandler('Rating')->insert($newRatingObj);

$currentRating += $rating;
++$count;

$helper->getHandler('Item')->updateAll('rating', number_format($currentRating / $count, 4), $criteria, true);
$helper->getHandler('Item')->updateAll('votes', $count, $criteria, true);

$tense = 1 == $count ? _MD_PUBLISHER_VOTE_VOTE : _MD_PUBLISHER_VOTE_VOTES; //plural form votes/vote

// $newBack is what gets 'drawn' on your page after a successful 'AJAX/Javascript' vote
$newBack = [];

$newBack[] .= '<div class="publisher_unit-rating" style="width:' . $units * $ratingUnitWidth . 'px;">';
$newBack[] .= '<div class="publisher_current-rating" style="width:' . (0 !== $count ? number_format($currentRating / $count, 2) * $ratingUnitWidth : 0) . 'px;">' . _MD_PUBLISHER_VOTE_RATING . '</div>';
$newBack[] .= '<div class="publisher_r1-unit">1</div>';
$newBack[] .= '<div class="publisher_r2-unit">2</div>';
$newBack[] .= '<div class="publisher_r3-unit">3</div>';
$newBack[] .= '<div class="publisher_r4-unit">4</div>';
$newBack[] .= '<div class="publisher_r5-unit">5</div>';
$newBack[] .= '<div class="publisher_r6-unit">6</div>';
$newBack[] .= '<div class="publisher_r7-unit">7</div>';
$newBack[] .= '<div class="publisher_r8-unit">8</div>';
$newBack[] .= '<div class="publisher_r9-unit">9</div>';
$newBack[] .= '<div class="publisher_r10-unit">10</div>';
$newBack[] .= '</div>';
$newBack[] .= '<div class="publisher_voted">' . _MD_PUBLISHER_VOTE_RATING . ' <strong>' . (0 !== $count ? number_format($currentRating / $count, 2) : 0) . '</strong>/' . $units . ' (' . $count . ' ' . $tense . ')</div>';
$newBack[] .= '<div class="publisher_thanks">' . _MD_PUBLISHER_VOTE_THANKS . '</div>';

$allnewback = implode("\n", $newBack);

// ========================

//name of the div id to be updated | the html that needs to be changed
$output = "unit_long$itemId|$allnewback";
echo $output;
