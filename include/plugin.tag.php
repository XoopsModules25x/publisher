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
 * @package         XoopsModules\Publisher
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/** Get item fields: title, content, time, link, uid, tags
 *
 * @param array $items
 */
function publisher_tag_iteminfo(&$items)
{
    if (empty($items) || !is_array($items)) {
        return false;
    }

    $items_id = [];
    foreach (array_keys($items) as $cat_id) {
        // Some handling here to build the link upon cat_id
        // if cat_id is not used, just skip it
        foreach (array_keys($items[$cat_id]) as $item_id) {
            // In article, the item_id is "art_id"
            $items_id[] = (int)$item_id;
        }
    }
    $items_id = array_unique($items_id); // remove duplicate ids

    /** @var \XoopsModules\Publisher\Helper $helper */
    $helper = \XoopsModules\Publisher\Helper::getInstance();
    /** @var Publisher\ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    $criteria    = new \Criteria('itemid', '(' . implode(', ', $items_id) . ')', 'IN');
    $items_obj   = $itemHandler->getObjects($criteria, 'itemid');

    //make sure Tag module tag_parse_tag() can be found
    if (!method_exists('XoopsModule\Tag\Utility', 'tag_parse_tag')) {
        require_once $GLOBALS['xoops']->path('modules/tag/include/functions.php');
        $parse_function = 'tag_parse_tag';
    } else {
        $parse_function = 'XoopsModule\Tag\Utility::tag_parse_tag';
    }

    /** @var Publisher\Item $item_obj */
    foreach (array_keys($items) as $cat_id) {
        foreach (array_keys($items[$cat_id]) as $item_id) {
            $item_obj                = $items_obj[$item_id];
            $items[$cat_id][$item_id] = [
                'title'   => $item_obj->getVar('title'),
                'uid'     => $item_obj->getVar('uid'),
                'link'    => "item.php?itemid={$item_id}",
                'time'    => $item_obj->getVar('datesub'),
                'tags'    => $parse_function($item_obj->getVar('item_tag', 'n')), // optional
                'content' => '',
            ];
        }
    }
    unset($items_obj);
    return true;
}

/** Remove orphan tag-item links *
 * @param int $mid
 */
function publisher_tag_synchronization($mid)
{
    // Optional
}
