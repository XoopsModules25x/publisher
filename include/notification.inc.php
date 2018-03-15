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

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

//require_once __DIR__ . '/seo_functions.php';

/**
 * @param $category
 * @param $itemId
 *
 * @return mixed
 */
function publisher_notify_iteminfo($category, $itemId)
{
    if ('global' === $category) {
        $item['name'] = '';
        $item['url']  = '';

        return $item;
    }

    global $module;
    if ('category' === $category) {
        // Assume we have a valid category id
        $sql          = 'SELECT name, short_url FROM ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_categories') . ' WHERE categoryid  = ' . $itemId;
        $result       = $GLOBALS['xoopsDB']->query($sql); // TODO: error check
        $resultArray  = $GLOBALS['xoopsDB']->fetchArray($result);
        $item['name'] = $resultArray['name'];
        $item['url']  = Publisher\Seo::generateUrl('category', $itemId, $resultArray['short_url']);

        return $item;
    }

    if ('item' === $category) {
        // Assume we have a valid story id
        $sql          = 'SELECT title, short_url FROM ' . $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_items') . ' WHERE itemid = ' . $itemId;
        $result       = $GLOBALS['xoopsDB']->query($sql); // TODO: error check
        $resultArray  = $GLOBALS['xoopsDB']->fetchArray($result);
        $item['name'] = $resultArray['title'];
        $item['url']  = Publisher\Seo::generateUrl('item', $itemId, $resultArray['short_url']);

        return $item;
    }

    return null;
}
