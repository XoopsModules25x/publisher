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
 * @version         $Id: notification.inc.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || die("XOOPS root path not defined");

include_once dirname(__FILE__) . '/seo_functions.php';

function publisher_notify_iteminfo($category, $item_id)
{
    if ($category == 'global') {
        $item['name'] = '';
        $item['url'] = '';

        return $item;
    }

    global $xoopsDB;

    if ($category == 'category') {
        // Assume we have a valid category id
        $sql = 'SELECT name, short_url FROM ' . $xoopsDB->prefix('publisher_categories') . ' WHERE categoryid  = ' . $item_id;
        $result = $xoopsDB->query($sql); // TODO: error check
        $result_array = $xoopsDB->fetchArray($result);
        $item['name'] = $result_array['name'];
        $item['url'] = publisher_seo_genUrl('category', $item_id, $result_array['short_url']);

        return $item;
    }

    if ($category == 'item') {
        // Assume we have a valid story id
        $sql = 'SELECT title, short_url FROM ' . $xoopsDB->prefix('publisher_items') . ' WHERE itemid = ' . $item_id;
        $result = $xoopsDB->query($sql); // TODO: error check
        $result_array = $xoopsDB->fetchArray($result);
        $item['name'] = $result_array['title'];
        $item['url'] = publisher_seo_genUrl('item', $item_id, $result_array['short_url']);

        return $item;
    }
}
