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
 */

use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_category_items_sel_show($options)
{
    $helper = Publisher\Helper::getInstance();

    $block = $item = [];

    $categories = $helper->getHandler('Category')->getCategories(0, 0, -1);

    if (0 === count($categories)) {
        return $block;
    }

    $selectedcatids = explode(',', $options[0]);
    $sort           = $options[1];
    $order          = Publisher\Utility::getOrderBy($sort);
    $limit          = $options[2];
    $start          = 0;

    // creating the ITEM objects that belong to the selected category
    $block['categories'] = [];
    foreach ($categories as $catID => $catObj) {
        if (!in_array(0, $selectedcatids) && !in_array($catID, $selectedcatids)) {
            continue;
        }

        $criteria = new \Criteria('categoryid', $catID);
        $items    = $helper->getHandler('Item')->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, true);
        unset($criteria);

        if (0 === count($items)) {
            continue;
        }

        $item['title']                          = $catObj->name();
        $item['itemurl']                        = 'none';
        $block['categories'][$catID]['items'][] = $item;

        foreach ($items[''] as $itemObj) {
            $item['title']                          = $itemObj->getTitle(isset($options[3]) ? $options[3] : 0);
            $item['itemurl']                        = $itemObj->getItemUrl();
            $block['categories'][$catID]['items'][] = $item;
        }
        $block['categories'][$catID]['name'] = $catObj->name();
    }

    unset($items, $categories, $itemObj, $catID, $catObj);

    if (0 === count($block['categories'])) {
        return $block;
    }

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_category_items_sel_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new Publisher\BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Publisher\Utility::createCategorySelect($options[0]), 'options[0]');
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray([
                                  'datesub' => _MB_PUBLISHER_DATE,
                                  'counter' => _MB_PUBLISHER_HITS,
                                  'weight'  => _MB_PUBLISHER_WEIGHT
                              ]);
    $dispEle  = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);
    $charsEle = new \XoopsFormText(_MB_PUBLISHER_CHARS, 'options[3]', 10, 255, $options[3]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);
    $form->addElement($charsEle);

    return $form->render();
}
