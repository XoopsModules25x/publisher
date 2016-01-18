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
 * @version         $Id: category_items_sel.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_category_items_sel_show($options)
{
    $publisher =& PublisherPublisher::getInstance();

    $block = array();

    $categories =& $publisher->getHandler('category')->getCategories(0, 0, -1);

    if (count($categories) === 0) {
        return $block;
    }

    $selectedcatids = explode(',', $options[0]);
    $sort           = $options[1];
    $order          = publisherGetOrderBy($sort);
    $limit          = $options[2];
    $start          = 0;

    // creating the ITEM objects that belong to the selected category
    $block['categories'] = array();
    foreach ($categories as $catID => $catObj) {
        if (!in_array(0, $selectedcatids) && !in_array($catID, $selectedcatids)) {
            continue;
        }

        $criteria = new Criteria('categoryid', $catID);
        $items    =& $publisher->getHandler('item')->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED), -1, $sort, $order, '', true, $criteria, true);
        unset($criteria);

        if (count($items) === 0) {
            continue;
        }

        $item['title']                          = $catObj->name();
        $item['itemurl']                        = 'none';
        $block['categories'][$catID]['items'][] = $item;

        foreach ($items as $itemObj) {
            $item['title']                          = $itemObj->getTitle(isset($options[3]) ? $options[3] : 0);
            $item['itemurl']                        = $itemObj->getItemUrl();
            $block['categories'][$catID]['items'][] = $item;
        }
        $block['categories'][$catID]['name'] = $catObj->name();
    }

    unset($items, $categories, $itemObj, $catID, $catObj);

    if (count($block['categories']) === 0) {
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
    include_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new PublisherBlockForm();

    $catEle   = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisherCreateCategorySelect($options[0]), 'options[0]');
    $orderEle = new XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(array(
                                  'datesub' => _MB_PUBLISHER_DATE,
                                  'counter' => _MB_PUBLISHER_HITS,
                                  'weight'  => _MB_PUBLISHER_WEIGHT));
    $dispEle  = new XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);
    $charsEle = new XoopsFormText(_MB_PUBLISHER_CHARS, 'options[3]', 10, 255, $options[3]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);
    $form->addElement($charsEle);

    return $form->render();
}
