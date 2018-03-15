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
 */

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/header.php';

//getting the values
$rating = Request::getInt('rating', 0, 'GET');
$itemid = Request::getInt('itemid', 0, 'GET');

$groups = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
/* @var $gpermHandler XoopsGroupPermHandler */
$gpermHandler = \XoopsModules\Publisher\Helper::getInstance()->getHandler('Groupperm');//xoops_getModuleHandler('groupperm');
/* @var $configHandler XoopsConfigHandler */
$configHandler = xoops_getHandler('config');
$module_id     = $helper->getModule()->getVar('mid');

//Checking permissions
if (!$helper->getConfig('perm_rating') || !$gpermHandler->checkRight('global', Constants::PUBLISHER_RATE, $groups, $module_id)) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _NOPERM);
    //    exit();
}

if ($rating > 5 || $rating < 1) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _MD_PUBLISHER_VOTE_BAD);
    //    exit();
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
//unset($ratingObj);

if ($voted) {
    redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _MD_PUBLISHER_VOTE_ALREADY);
    //    exit();
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

redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemid, 2, _MD_PUBLISHER_VOTE_THANKS);
//exit();
