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
    Helper,
    Item,
    Metagen
};

require_once __DIR__ . '/header.php';

$uid = Request::getInt('uid', 0, 'GET');
if (0 == $uid) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
}

/** @var \XoopsMemberHandler $memberHandler */
$memberHandler = xoops_getHandler('member');
$thisuser      = $memberHandler->getUser($uid);
if (!is_object($thisuser)) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
}

/** @var Helper $helper */
if (!$helper->getConfig('perm_author_items')) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
}

$myts = \MyTextSanitizer::getInstance();

$GLOBALS['xoopsOption']['template_main'] = 'publisher_author_items.tpl';
require_once $GLOBALS['xoops']->path('header.php');
require_once PUBLISHER_ROOT_PATH . '/footer.php';

$criteria = new \CriteriaCompo(new \Criteria('datesub', time(), '<='));
$criteria->add(new \Criteria('uid', $uid));

$items = $helper->getHandler('Item')->getItems($limit = 0, $start = 0, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, 'datesub', 'DESC', '', true, $criteria);
unset($criteria);
$count = count($items);

$xoopsTpl->assign('total_items', $count);
$xoopsTpl->assign('permRating', $helper->getConfig('perm_rating'));

xoops_load('XoopsUserUtility');
$authorName = \XoopsUserUtility::getUnameFromId($uid, $helper->getConfig('format_realname'), true);
$xoopsTpl->assign('author_name_with_link', $authorName);
$xoopsTpl->assign('user_avatarurl', XOOPS_URL . '/uploads/' . $thisuser->getVar('user_avatar'));
//$xoopsLocal = new \XoopsLocal();
$categories = [];
if ($count > 0) {
    /** @var Item $item */
    foreach ($items as $item) {
        $catId = $item->categoryid();
        if (!isset($categories[$catId])) {
            $categories[$catId] = [
                'count_items' => 0,
                'count_hits'  => 0,
                'title'       => $item->getCategoryName(),
                'link'        => $item->getCategoryLink(),
            ];
        }
        //mb start
        $mainImage = $item->getMainImage();
        if (empty($mainImage['image_path'])) {
            $mainImage['image_path'] = PUBLISHER_URL . '/assets/images/default_image.jpg';
        }
        // check to see if GD function exist
        if (!empty($mainImage['image_path']) && !function_exists('imagecreatetruecolor')) {
            $image = $mainImage['image_path'];
        } else {
            $image = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=100';
        }
        //mb end
        $comments = $item->comments();
        if ($comments > 0) {
            //shows 1 comment instead of 1 comm. if comments ==1
            //langugage file modified accordingly
            if (1 == $comments) {
                $comment = '&nbsp;' . _MD_PUBLISHER_ONECOMMENT . '&nbsp;';
            } else {
                $comment = '&nbsp;' . $comments . '&nbsp;' . _MD_PUBLISHER_COMMENTS . '&nbsp;';
            }
        } else {
            $comment = '&nbsp;' . _MD_PUBLISHER_NO_COMMENTS . '&nbsp;';
        }

        $categories[$catId]['count_items']++;
        $categories[$catId]['count_hits'] += $item->counter();
        $categories[$catId]['items'][]    = [
            'title'      => $item->getTitle(),
            'cleantitle' => strip_tags($item->getTitle()),
            'itemurl'    => $item->getItemUrl(),
            'summary'    => $item->getSummary(),
            'comment'    => $comment,
            'cancomment' => $item->cancomment(),
            'hits'       => $item->counter(),
            'link'       => $item->getItemLink(),
            'published'  => $item->getDatesub(),
            //'published' => $item->getDatesub(_SHORTDATESTRING),
            //'rating'    => $xoopsLocal->number_format((float)$item->rating())
            'rating'     => $item->rating(),
            'image'      => $image,
        ];
    }
}
unset($item);
$xoopsTpl->assign('categories', $categories);

$title = _MD_PUBLISHER_ITEMS_SAME_AUTHOR . ' - ' . $authorName;

/**
 * Generating meta information for this page
 */
$publisherMetagen = new Metagen($title, '', $title);
$publisherMetagen->createMetaTags();

require_once $GLOBALS['xoops']->path('footer.php');
