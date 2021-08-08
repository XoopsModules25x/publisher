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

use XoopsModules\Publisher\{Helper,
    Item,
    ItemHandler
};

/** @var ItemHandler $itemHandler */

require_once __DIR__ . '/common.php';

/**
 * @param array        $queryArray
 * @param              $andor
 * @param              $limit
 * @param              $offset
 * @param              $userid
 * @param array        $categories
 * @param int          $sortby
 * @param string       $searchin
 * @param string       $extra
 *
 * @return array
 */
function publisher_search($queryArray, $andor, $limit, $offset, $userid, $categories = [], $sortby = 0, $searchin = '', $extra = '')
{
    $helper = Helper::getInstance();
    $ret    = $item = [];
    $hightlightKey = '';

    if (is_array($queryArray)) {
        if (0 === count($queryArray)) {
            $hightlightKey = '';
        } else {
            $keywords      = implode('+', $queryArray);
            $hightlightKey = '&amp;keywords=' . $keywords;
        }
    }


    $itemHandler      = $helper->getHandler('Item');
    $itemsObjs        = $itemHandler->getItemsFromSearch($queryArray, $andor, $limit, $offset, $userid, $categories, $sortby, $searchin, $extra);
    $withCategoryPath = $helper->getConfig('search_cat_path');
    //xoops_load("xoopslocal");
    $usersIds = [];
    /** @var Item $obj */
    if (0 !== count($itemsObjs)) {
        foreach ($itemsObjs as $obj) {
            $item['image'] = 'assets/images/item_icon.gif';
            $item['link']  = $obj->getItemUrl();
            $item['link']  .= (!empty($hightlightKey) && (false === mb_strpos($item['link'], '.php?'))) ? '?' . ltrim($hightlightKey, '&amp;') : $hightlightKey;
            if ($withCategoryPath) {
                $item['title'] = $obj->getCategoryPath(false) . ' > ' . $obj->getTitle();
            } else {
                $item['title'] = $obj->getTitle();
            }
            $item['time'] = $obj->getVar('datesub'); //must go has unix timestamp
            $item['uid']  = $obj->uid();
            //"Fulltext search/highlight
            $text          = $obj->getBody();
            $sanitizedText = '';
            $textLower     = mb_strtolower($text);
            $queryArray    = is_array($queryArray) ? $queryArray : [$queryArray];

            if ('' != $queryArray[0] && count($queryArray) > 0) {
                foreach ($queryArray as $query) {
                    $pos           = mb_stripos($textLower, $query); //xoops_local("strpos", $textLower, mb_strtolower($query));
                    $start         = max($pos - 100, 0);
                    $length        = mb_strlen($query) + 200; //xoops_local("strlen", $query) + 200;
                    $context       = $obj->highlight(xoops_substr($text, $start, $length, ' [...]'), $query);
                    $sanitizedText .= '<p>[...] ' . $context . '</p>';
                }
            }
            //End of highlight
            $item['text']      = $sanitizedText;
            $item['author']    = $obj->author_alias();
            $item['datesub']   = $obj->getDatesub($helper->getConfig('format_date'));
            $objUid            = $obj->uid();
            $usersIds[$objUid] = $objUid;
            $ret[]             = $item;
            unset($item, $sanitizedText);
        }
    }
    xoops_load('XoopsUserUtility');
    $usersNames = \XoopsUserUtility::getUnameFromIds($usersIds, $helper->getConfig('format_realname'), true);
    foreach ($ret as $key => $item) {
        if ('' == $item['author']) {
            $ret[$key]['author'] = $usersNames[$item['uid']] ?? '';
        }
    }
    unset($usersNames, $usersIds);

    return $ret;
}
