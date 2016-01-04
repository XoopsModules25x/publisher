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
 * @subpackage      Blocks
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: items_random_item.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_items_random_item_show($options)
{
    $block     = array();
    $publisher =& PublisherPublisher::getInstance();
    // creating the ITEM object
    $itemsObj =& $publisher->getHandler('item')->getRandomItem('', array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED));

    if (!is_object($itemsObj)) {
        return $block;
    }

    $block['content']       = $itemsObj->getBlockSummary(300, true); //show complete summary  but truncate to 300 if only body available
    $block['id']            = $itemsObj->itemid();
    $block['url']           = $itemsObj->getItemUrl();
    $block['lang_fullitem'] = _MB_PUBLISHER_FULLITEM;

    return $block;
}
