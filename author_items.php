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

$uid = Request::getInt('uid', 0, 'GET');
if (0 == $uid) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
    //   exit();
}

$memberHandler = xoops_getHandler('member');
$thisuser      = $memberHandler->getUser($uid);
if (!is_object($thisuser)) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
    //    exit();
}

if (!$helper->getConfig('perm_author_items')) {
    redirect_header('index.php', 2, _CO_PUBLISHER_ERROR);
    //mb    exit();
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
$author_name = \XoopsUserUtility::getUnameFromId($uid, $helper->getConfig('format_realname'), true);
$xoopsTpl->assign('author_name_with_link', $author_name);
$xoopsTpl->assign('user_avatarurl', XOOPS_URL . '/uploads/' . $thisuser->getVar('user_avatar'));
//$xoopsLocal = new \XoopsLocal();
$categories = [];
if ($count > 0) {
    /** @var  Publisher\Item $item */
    foreach ($items as $item) {
        $catid = $item->categoryid();
        if (!isset($categories[$catid])) {
            $categories[$catid] = [
                'count_items' => 0,
                'count_hits'  => 0,
                'title'       => $item->getCategoryName(),
                'link'        => $item->getCategoryLink()
            ];
        }

        $categories[$catid]['count_items']++;
        $categories[$catid]['count_hits'] += $item->counter();
        $categories[$catid]['items'][]    = [
            'title'     => $item->getTitle(),
            'hits'      => $item->counter(),
            'link'      => $item->getItemLink(),
            'published' => $item->getDatesub(_SHORTDATESTRING),
            //'rating'    => $xoopsLocal->number_format((float)$item->rating())
            'rating'    => $item->rating()
        ];
    }
}
unset($item);
$xoopsTpl->assign('categories', $categories);

$title = _MD_PUBLISHER_ITEMS_SAME_AUTHOR . ' - ' . $author_name;

/**
 * Generating meta information for this page
 */
$publisherMetagen = new Publisher\Metagen($title, '', $title);
$publisherMetagen->createMetaTags();

require_once $GLOBALS['xoops']->path('footer.php');
