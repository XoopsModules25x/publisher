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
    VoteHandler,
    Utility
};

/** @var Helper $helper */

require __DIR__ . '/header.php';
$op             = Request::getCmd('op', 'list');
$source         = Request::getInt('source', 0);
$voteHandler    = $helper->getHandler('Vote');
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
        $redir  = Request::getString('HTTP_REFERER', '', 'SERVER');
        if (Constants::TABLE_CATEGORY === $source) {
            $itemId = Request::getInt('itemid', 0);
            $redir  = 'category.php?op=show&amp;itemid=' . $itemId;
        }
        if (Constants::TABLE_ARTICLE === $source) {
            $itemId = Request::getInt('itemid', 0);
            $redir  = 'item.php?op=show&amp;itemid=' . $itemId;
        }

        // Check permissions
        $rate_allowed = false;
        $groups       = (isset($GLOBALS['xoopsUser']) && \is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        foreach ($groups as $group) {
            if (XOOPS_GROUP_ADMIN == $group || \in_array($group, $helper->getConfig('ratingbar_groups'))) {
                $rate_allowed = true;
                break;
            }
        }
        if (!$rate_allowed) {
            \redirect_header('index.php', 3, _MA_BLOG_RATING_NOPERM);
        }

        // Check rating value
        switch ((int)$helper->getConfig('ratingbars')) {
            case Constants::RATING_NONE:
            default:
                \redirect_header('index.php', 3, _MA_BLOG_RATING_VOTE_BAD);
                exit;
                break;
            case Constants::RATING_LIKES:
                if ($rating > 1 || $rating < -1) {
                    \redirect_header('index.php', 3, _MA_BLOG_RATING_VOTE_BAD);
                    exit;
                }
                break;
            case Constants::RATING_5STARS:
                if ($rating > 5 || $rating < 1) {
                    \redirect_header('index.php', 3, _MA_BLOG_RATING_VOTE_BAD);
                    exit;
                }
                break;
            case Constants::RATING_REACTION:
                if ($rating > 6 || $rating < 1) {
                    \redirect_header('index.php', 3, _MA_BLOG_RATING_VOTE_BAD);
                    exit;
                }
                break;
            case Constants::RATING_10STARS:
            case Constants::RATING_10NUM:
                if ($rating > 10 || $rating < 1) {
                    \redirect_header('index.php', 3, _MA_BLOG_RATING_VOTE_BAD);
                    exit;
                }
                break;
        }

        // Get existing rating
        $itemrating = $voteHandler->getItemRating($itemId, $source);

        // Set data rating
        if ($itemrating['voted']) {
            // If yo want to avoid revoting then activate next line
            //            \redirect_header('index.php', 3, _MA_BLOG_RATING_VOTE_BAD);
            //            \redirect_header('item.php?itemid=' .$itemId, 3, _MA_BLOG_RATING_VOTE_BAD);
            $helper->redirect('item.php?itemid=' . $itemId, 2, _MD_PUBLISHER_VOTE_ALREADY);
            //            redirect_header(PUBLISHER_URL . '/item.php?itemid=' . $itemId, 2, _MD_PUBLISHER_VOTE_ALREADY);

            $voteObj = $voteHandler->get($itemrating['ratingid']);
        } else {
            $voteObj = $voteHandler->create();
        }
        $voteObj->setVar('source', $source);
        $voteObj->setVar('itemid', $itemId);
        $voteObj->setVar('rate', $rating);
        $voteObj->setVar('uid', $itemrating['uid']);
        $voteObj->setVar('ip', $itemrating['ip']);
        $voteObj->setVar('date', \time());
        // Insert Data
        if ($voteHandler->insert($voteObj)) {
            unset($voteObj);
            // Calc average rating value
            $nb_vote        = 0;
            $avg_rate_value = 0;
            $current_rating = 0;
            $crVote         = new \CriteriaCompo();
            $crVote->add(new \Criteria('source', $source));
            $crVote->add(new \Criteria('itemid', $itemId));
            $voteCount = $voteHandler->getCount($crVote);
            $voteAll   = $voteHandler->getAll($crVote);
            foreach (\array_keys($voteAll) as $i) {
                $current_rating += $voteAll[$i]->getVar('rate');
            }
            unset($voteAll);
            if ($voteCount > 0) {
                $avg_rate_value = number_format($current_rating / $voteCount, 2);
            }
            // Update related table
            if (Constants::TABLE_CATEGORY === $source) {
                $tableName   = 'category';
                $fieldVote   = '_vote';
                $fieldVotes  = '_votes';
                $categoryObj = $categoryHandler->get($itemId);
                $categoryObj->setVar('_vote', $avg_rate_value);
                $categoryObj->setVar('_votes', $voteCount);
                if ($categoryHandler->insert($categoryObj)) {
                    \redirect_header($redir, 2, _MA_BLOG_RATING_VOTE_THANKS);
                } else {
                    \redirect_header('category.php', 3, _MA_BLOG_RATING_ERROR1);
                }
                unset($categoryObj);
            }
            if (Constants::TABLE_ARTICLE === $source) {
                $tableName  = 'article';
                $fieldVote  = '_vote';
                $fieldVotes = '_votes';
                $articleObj = $articleHandler->get($itemId);
                $articleObj->setVar('_vote', $avg_rate_value);
                $articleObj->setVar('_votes', $voteCount);
                if ($articleHandler->insert($articleObj)) {
                    \redirect_header($redir, 2, _MA_BLOG_RATING_VOTE_THANKS);
                } else {
                    \redirect_header('item.php', 3, _MA_BLOG_RATING_ERROR1);
                }
                unset($articleObj);
            }

            \redirect_header('index.php', 2, _MA_BLOG_RATING_VOTE_THANKS);
        }
        // Get Error
        echo 'Error: ' . $voteObj->getHtmlErrors();
        break;
}
require __DIR__ . '/footer.php';
