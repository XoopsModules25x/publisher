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

use XoopsModules\Publisher\{BlockForm,
    CategoryHandler,
    Constants,
    Helper,
    ItemHandler,
    Utility
};

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_category_items_sel_show($options)
{
    $helper = Helper::getInstance();

    $block = $item = [];

    /** @var CategoryHandler $categoryHandler */
    $categoryHandler = $helper->getHandler('Category');
    $categories      = $categoryHandler->getCategories(0, 0, -1);

    if (0 === count($categories)) {
        return $block;
    }

    $selectedcatids = explode(',', $options[0]);
    $sort           = $options[1];
    $order          = Utility::getOrderBy($sort);
    $limit          = $options[2];
    $start          = 0;

    // creating the ITEM objects that belong to the selected category
    $block['categories'] = [];
    foreach ($categories as $catId => $catObj) {
        if (!in_array(0, $selectedcatids, true) && !in_array($catId, $selectedcatids, true)) {
            continue;
        }

        //        $criteria = new \Criteria('categoryid', $catId);

        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('categoryid', $catId));

        /** @var ItemHandler $itemHandler */
        $itemHandler = $helper->getHandler('Item');

        $publisherIsAdmin = $helper->isUserAdmin();
        if (!$publisherIsAdmin) {
            $criteriaDateSub = new \Criteria('datesub', time(), '<=');
            $criteria->add($criteriaDateSub);
        }

        $items = $itemHandler->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, true);
        unset($criteria);

        if (0 === count($items)) {
            continue;
        }

        $item['title']                          = $catObj->name();
        $item['itemurl']                        = 'none';
        $block['categories'][$catId]['items'][] = $item;

        foreach ($items[''] as $itemObj) {
            $item['title']                          = $itemObj->getTitle($options[3] ?? 0);
            $item['itemurl']                        = $itemObj->getItemUrl();
            $block['categories'][$catId]['items'][] = $item;
        }
        $block['categories'][$catId]['name'] = $catObj->name();
    }

    unset($items, $categories, $catId);

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

    $form = new BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Utility::createCategorySelect($options[0]), 'options[0]');
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(
        [
            'datesub' => _MB_PUBLISHER_DATE,
            'counter' => _MB_PUBLISHER_HITS,
            'weight'  => _MB_PUBLISHER_WEIGHT,
        ]
    );
    $dispEle  = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);
    $charsEle = new \XoopsFormText(_MB_PUBLISHER_CHARS, 'options[3]', 10, 255, $options[3]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);
    $form->addElement($charsEle);

    return $form->render();
}
