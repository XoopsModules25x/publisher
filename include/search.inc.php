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
 * @subpackage      Include
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: search.inc.php 10374 2012-12-12 23:39:48Z trabis $
 */
// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param        $queryarray
 * @param        $andor
 * @param        $limit
 * @param        $offset
 * @param        $userid
 * @param array  $categories
 * @param int    $sortby
 * @param string $searchin
 * @param string $extra
 *
 * @return array
 */
function publisher_search($queryarray, $andor, $limit, $offset, $userid, $categories = array(), $sortby = 0, $searchin = '', $extra = '')
{
    $publisher =& PublisherPublisher::getInstance();
    $ret       = array();
    if ($queryarray == '' || count($queryarray) == 0) {
        $hightlightKey = '';
    } else {
        $keywords       = implode('+', $queryarray);
        $hightlightKey = '&amp;keywords=' . $keywords;
    }
    $itemsObjs        =& $publisher->getHandler('item')->getItemsFromSearch($queryarray, $andor, $limit, $offset, $userid, $categories, $sortby, $searchin, $extra);
    $withCategoryPath = $publisher->getConfig('search_cat_path');
    //xoops_load("xoopslocal");
    $usersIds = array();
    foreach ($itemsObjs as $obj) {
        $item['image'] = 'assets/images/item_icon.gif';
        $item['link']  = $obj->getItemUrl();
        $item['link'] .= (!empty($hightlightKey) && (strpos($item['link'], '.php?') === false)) ? '?' . ltrim($hightlightKey, '&amp;') : $hightlightKey;
        if ($withCategoryPath) {
            $item['title'] = $obj->getCategoryPath(false) . ' > ' . $obj->getTitle();
        } else {
            $item['title'] = $obj->getTitle();
        }
        $item['time'] = $obj->getVar('datesub'); //must go has unix timestamp
        $item['uid']  = $obj->uid();
        //"Fulltext search/highlight
        $text           = $obj->getBody();
        $sanitizedText = '';
        $textLower         = strtolower($text);
        $queryarray     = is_array($queryarray) ? $queryarray : array($queryarray);

        if ($queryarray[0] != '' && count($queryarray) > 0) {
            foreach ($queryarray as $query) {
                $pos     = strpos($textLower, strtolower($query)); //xoops_local("strpos", $textLower, strtolower($query));
                $start   = max(($pos - 100), 0);
                $length  = strlen($query) + 200; //xoops_local("strlen", $query) + 200;
                $context = $obj->highlight(xoops_substr($text, $start, $length, ' [...]'), $query);
                $sanitizedText .= '<p>[...] ' . $context . '</p>';
            }
        }
        //End of highlight
        $item['text']          = $sanitizedText;
        $item['author']        = $obj->author_alias();
        $item['datesub']       = $obj->getDatesub($publisher->getConfig('format_date'));
        $usersIds[$obj->uid()] = $obj->uid();
        $ret[]                 = $item;
        unset($item, $sanitizedText);
    }
    xoops_load('XoopsUserUtility');
    $usersNames = XoopsUserUtility::getUnameFromIds($usersIds, $publisher->getConfig('format_realname'), true);
    foreach ($ret as $key => $item) {
        if ($item['author'] == '') {
            $ret[$key]['author'] = isset($usersNames[$item['uid']]) ? $usersNames[$item['uid']] : '';
        }
    }
    unset($usersNames, $usersIds);

    return $ret;
}
