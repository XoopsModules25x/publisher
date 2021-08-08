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
    RatingsHandler,
    Utility
};

/** @var Helper $helper */

require __DIR__ . '/header.php';
$op             = Request::getCmd('op', 'list');
$source         = Request::getInt('source', 0);
$ratingsHandler = $helper->getHandler('Ratings');
$articleHandler = $helper->getHandler('Item');

switch ($op) {
    case 'list':
    default:
        // default should not happen
        \redirect_header('index.php', 3, _NOPERM);
        break;
    case 'save':
        // Security Check
        if ($GLOBALS['xoopsSecurity']->check()) {
            \redirect_header('index.php', 3, \implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $rating = Request::getInt('rating', 0);
        $itemId = 0;
        $redir  = $_SERVER['HTTP_REFERER'];
        if (Constants::TABLE_CATEGORY === $source) {
            $itemId = Request::getInt('id', 0);
            $redir  = 'category.php?op=show&amp;itemid=' . $itemId;
        }
        if (Constants::TABLE_ARTICLE === $source) {
            $itemId = Request::getInt('id', 0);
            $redir  = 'item.php?op=show&amp;itemid=' . $itemId;
        }

        // Check permissions
        $rateAllowed = false;
        $groups       = (isset($GLOBALS['xoopsUser']) && \is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        foreach ($groups as $group) {
            if (XOOPS_GROUP_ADMIN == $group || \in_array($group, $helper->getConfig('ratingbar_groups'))) {
                $rateAllowed = true;
                break;
            }
        }
        if (!$rateAllowed) {
            \redirect_header('index.php', 3, _MA_PUBLISHER_RATING_NOPERM);
        }

        // Check rating value
        switch ((int)$helper->getConfig('ratingbars')) {
            case Constants::RATING_NONE:
            default:
                \redirect_header('index.php', 3, _MA_PUBLISHER_RATING_VOTE_BAD);
            case Constants::RATING_LIKES:
                if ($rating > 1 || $rating < -1) {
                    \redirect_header('index.php', 3, _MA_PUBLISHER_RATING_VOTE_BAD);
                }
                break;
            case Constants::RATING_5STARS:
                if ($rating > 5 || $rating < 1) {
                    \redirect_header('index.php', 3, _MA_PUBLISHER_RATING_VOTE_BAD);
                }
                break;
            case Constants::RATING_REACTION:
                if ($rating > 6 || $rating < 1) {
                    \redirect_header('index.php', 3, _MA_PUBLISHER_RATING_VOTE_BAD);
                }
                break;
            case Constants::RATING_10STARS:
            case Constants::RATING_10NUM:
                if ($rating > 10 || $rating < 1) {
                    \redirect_header('index.php', 3, _MA_PUBLISHER_RATING_VOTE_BAD);
                }
                break;
        }

        // Get existing rating
        $itemRating = $ratingsHandler->getItemRating($itemId, $source);

        // Set data rating
        if ($itemRating['voted']) {
            // If yo want to avoid revoting then activate next line
            //\redirect_header('index.php', 3, _MA_PUBLISHER_RATING_VOTE_BAD);
            $ratingsObj = $ratingsHandler->get($itemRating['rate_id']);
        } else {
            $ratingsObj = $ratingsHandler->create();
        }
        $ratingsObj->setVar('rate_source', $source);
        $ratingsObj->setVar('rate_itemid', $itemId);
        $ratingsObj->setVar('rate_value', $rating);
        $ratingsObj->setVar('rate_uid', $itemRating['uid']);
        $ratingsObj->setVar('rate_ip', $itemRating['ip']);
        $ratingsObj->setVar('rate_date', \time());
        // Insert Data
        if ($ratingsHandler->insert($ratingsObj)) {
            unset($ratingsObj);
            // Calc average rating value
            $nb_ratings     = 0;
            $avg_rate_value = 0;
            $currentRating = 0;
            $crRatings      = new \CriteriaCompo();
            $crRatings->add(new \Criteria('rate_source', $source));
            $crRatings->add(new \Criteria('rate_itemid', $itemId));
            $ratingsCount = $ratingsHandler->getCount($crRatings);
            $ratingsAll   = $ratingsHandler->getAll($crRatings);
            foreach (\array_keys($ratingsAll) as $i) {
                $currentRating += $ratingsAll[$i]->getVar('rate_value');
            }
            unset($ratingsAll);
            if ($ratingsCount > 0) {
                $avg_rate_value = number_format($currentRating / $ratingsCount, 2);
            }
            // Update related table
            if (Constants::TABLE_CATEGORY === $source) {
                $tableName    = 'category';
                $fieldRatings = '_ratings';
                $fieldVotes   = '_votes';
                $categoryObj  = $categoryHandler->get($itemId);
                $categoryObj->setVar('_ratings', $avg_rate_value);
                $categoryObj->setVar('_votes', $ratingsCount);
                if ($categoryHandler->insert($categoryObj)) {
                    \redirect_header($redir, 2, _MA_PUBLISHER_RATING_VOTE_THANKS);
                } else {
                    \redirect_header('category.php', 3, _MA_PUBLISHER_RATING_ERROR1);
                }
                unset($categoryObj);
            }
            if (Constants::TABLE_ARTICLE === $source) {
                $tableName    = 'article';
                $fieldRatings = '_ratings';
                $fieldVotes   = '_votes';
                $articleObj   = $articleHandler->get($itemId);
                $articleObj->setVar('_ratings', $avg_rate_value);
                $articleObj->setVar('_votes', $ratingsCount);
                if ($articleHandler->insert($articleObj)) {
                    \redirect_header($redir, 2, _MA_PUBLISHER_RATING_VOTE_THANKS);
                } else {
                    \redirect_header('item.php', 3, _MA_PUBLISHER_RATING_ERROR1);
                }
                unset($articleObj);
            }

            \redirect_header('index.php', 2, _MA_PUBLISHER_RATING_VOTE_THANKS);
        }
        // Get Error
        echo 'Error: ' . $ratingsObj->getHtmlErrors();
        break;
}
require __DIR__ . '/footer.php';
