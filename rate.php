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
 */

use Xmf\Request;
use XoopsModules\Publisher\{Constants,
    GroupPermHandler,
    Helper,
    Utility
};

/** @var Helper $helper */

require_once __DIR__ . '/header.php';

//getting the values
$rating = Request::getInt('rating', 0, 'GET');
$itemId = Request::getInt('itemid', 0, 'GET');

$groups = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
/** @var GroupPermHandler $grouppermHandler */
$grouppermHandler = Helper::getInstance()->getHandler('GroupPerm'); //xoops_getModuleHandler('groupperm');
/** @var \XoopsConfigHandler $configHandler */
$configHandler = xoops_getHandler('config');
$moduleId      = $helper->getModule()->getVar('mid');

//Checking permissions
if (!$helper->getConfig('perm_rating') || !$grouppermHandler->checkRight('global', Constants::PUBLISHER_RATE, $groups, $moduleId)) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemId, 2, _NOPERM);
}

if ($rating > 5 || $rating < 1) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemId, 2, _MD_PUBLISHER_VOTE_BAD);
}

$criteria   = new \Criteria('itemid', $itemId);
$ratingObjs = $helper->getHandler('Rating')->getObjects($criteria);

$uid           = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
$count         = count($ratingObjs);
$currentRating = 0;
$voted         = false;
$ip            = getenv('REMOTE_ADDR');

foreach ($ratingObjs as $ratingObj) {
    $currentRating += $ratingObj->getVar('rate');
    if ($ratingObj->getVar('ip') == $ip || ($uid > 0 && $uid == $ratingObj->getVar('uid'))) {
        $voted = true;
    }
}
//unset($ratingObj);

if ($voted) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemId, 2, _MD_PUBLISHER_VOTE_ALREADY);
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

redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemId, 2, _MD_PUBLISHER_VOTE_THANKS);
//exit();
