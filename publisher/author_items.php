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
 * @version         $Id: author_items.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/header.php';

$uid = XoopsRequest::getInt('uid', 0,'GET');
if (empty($uid)) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
    exit();
}

$member_handler = xoops_gethandler('member');
$thisuser = $member_handler->getUser($uid);
if (!is_object($thisuser)) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
    exit();
}

if (!$publisher->getConfig('perm_author_items')) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
    exit();
}

$myts = MyTextSanitizer::getInstance();

$xoopsOption['template_main'] = 'publisher_author_items.tpl';
include_once XOOPS_ROOT_PATH . '/header.php';
include_once PUBLISHER_ROOT_PATH . '/footer.php';

$criteria = new CriteriaCompo(new Criteria('datesub', time(), '<='));
$criteria->add(new Criteria('uid', $uid));

$items = $publisher->getHandler('item')->getItems($limit = 0, $start = 0, array(PublisherConstants::_PUBLISHER_STATUS_PUBLISHED), -1, 'datesub', 'DESC', '', true, $criteria);
unset($criteria);
$count = count($items);

$xoopsTpl->assign('total_items', $count);
$xoopsTpl->assign('rating', $publisher->getConfig('perm_rating'));

xoops_load('XoopsUserUtility');
$author_name = XoopsUserUtility::getUnameFromId($uid, $publisher->getConfig('format_realname'), true);
$xoopsTpl->assign('author_name_with_link', $author_name);

$xoopsTpl->assign('user_avatarurl', XOOPS_URL . '/uploads/' . $thisuser->getVar('user_avatar'));

$categories = array();
if ($count > 0) {
    foreach ($items as $item) {
        $catid = $item->categoryid();
        if (!isset($categories[$catid])) {
            $categories[$catid] = array(
                'count_items' => 0,
                'count_hits' => 0,
                'title' => $item->getCategoryName(),
                'link' => $item->getCategoryLink()
            );
        }

        $categories[$catid]['count_items']++;
        $categories[$catid]['count_hits'] += $item->counter();
        $categories[$catid]['items'][] = array(
            'title' => $item->title(),
            'hits' => $item->counter(),
            'link' => $item->getItemLink(),
            'published' => $item->datesub(),
            'rating' => $item->rating());
    }
}

$xoopsTpl->assign('categories', $categories);

$title = _MD_PUBLISHER_ITEMS_SAME_AUTHOR . ' - ' . $author_name;

/**
 * Generating meta information for this page
 */
$publisher_metagen = new PublisherMetagen($title, '', $title);
$publisher_metagen->createMetaTags();

include_once XOOPS_ROOT_PATH . '/footer.php';
