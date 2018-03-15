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
 * @author          The SmartFactory <www.smartfactory.ca>
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/** Get item fields: title, content, time, link, uid, uname, tags *
 *
 * @param $items
 */
function publisher_tag_iteminfo(&$items)
{
    $itemsId = [];
    foreach (array_keys($items) as $catId) {
        // Some handling here to build the link upon catid
        // if catid is not used, just skip it
        foreach (array_keys($items[$catId]) as $itemId) {
            // In article, the item_id is "art_id"
            $itemsId[] = (int)$itemId;
        }
    }

    /** @var \XoopsModules\Publisher\ItemHandler $itemHandler */
    $itemHandler = \XoopsModules\Publisher\Helper::getInstance()->getHandler('Item');

    $criteria        = new \Criteria('itemid', '(' . implode(', ', $itemsId) . ')', 'IN');
    $itemsObj        = $itemHandler->getObjects($criteria, 'itemid');

    foreach (array_keys($items) as $catId) {
        foreach (array_keys($items[$catId]) as $itemId) {
            $itemObj                = $itemsObj[$itemId];
            $items[$catId][$itemId] = [
                'title'   => $itemObj->getVar('title'),
                'uid'     => $itemObj->getVar('uid'),
                'link'    => "item.php?itemid={$itemId}",
                'time'    => $itemObj->getVar('datesub'),
                'tags'    => tag_parse_tag($itemObj->getVar('item_tag', 'n')), // optional
                'content' => ''
            ];
        }
    }
    unset($itemsObj);
}

/** Remove orphan tag-item links *
 * @param $mid
 */
function publisher_tag_synchronization($mid)
{
    // Optional
}
