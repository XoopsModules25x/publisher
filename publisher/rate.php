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
 * @version         $Id: rate.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__FILE__) . '/header.php';

//getting the values
$rating = PublisherRequest::getInt('rating');
$itemid = PublisherRequest::getInt('itemid');

$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
$gperm_handler = xoops_getmodulehandler('groupperm');
$hModConfig = xoops_gethandler('config');
$module_id = $publisher->getModule()->getVar('mid');

//Checking permissions
if (!$publisher->getConfig('perm_rating') || !$gperm_handler->checkRight('global', _PUBLISHER_RATE, $groups, $module_id)) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _NOPERM);
    exit();
}

if ($rating > 5 || $rating < 1) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _MD_PUBLISHER_VOTE_BAD);
    exit();
}

$criteria = new Criteria('itemid', $itemid);
$ratingObjs = $publisher->getHandler('rating')->getObjects($criteria);

$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
$count = count($ratingObjs);
$current_rating = 0;
$voted = false;
$ip = getenv('REMOTE_ADDR');

foreach ($ratingObjs as $ratingObj) {
    $current_rating += $ratingObj->getVar('rate');
    if ($ratingObj->getVar('ip') == $ip || ($uid > 0 && $uid == $ratingObj->getVar('uid'))) {
        $voted = true;
    }
}

if ($voted) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _MD_PUBLISHER_VOTE_ALREADY);
    exit();
}

$newRatingObj = $publisher->getHandler('rating')->create();
$newRatingObj->setVar('itemid', $itemid);
$newRatingObj->setVar('ip', $ip);
$newRatingObj->setVar('uid', $uid);
$newRatingObj->setVar('rate', $rating);
$newRatingObj->setVar('date', time());
$publisher->getHandler('rating')->insert($newRatingObj);

$current_rating += $rating;
$count++;

$publisher->getHandler('item')->updateAll('rating', number_format($current_rating / $count, 4), $criteria, true);
$publisher->getHandler('item')->updateAll('votes', $count, $criteria, true);

redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _MD_PUBLISHER_VOTE_THANKS);
exit();
?>