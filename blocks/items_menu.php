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
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher\{BlockForm,
    CategoryHandler,
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
function publisher_items_menu_show($options)
{
    $block = [];

    $helper = Helper::getInstance();

    // Getting all top cats
    /** @var CategoryHandler $categoryHandler */
    $categoryHandler    = $helper->getHandler('Category');
    $blockCategoriesObj = $categoryHandler->getCategories(0, 0, 0);

    if (0 == count($blockCategoriesObj)) {
        return $block;
    }

    // Are we in Publisher ?
    $block['inModule'] = (isset($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') == $helper->getDirname());

    $catLinkClass = 'menuMain';

    $categoryid = 0;

    if ($block['inModule']) {
        // Are we in a category and if yes, in which one ?
        $categoryid = Request::getInt('categoryid', 0, 'GET');

        if (0 != $categoryid) {
            // if we are in a category, then the $categoryObj is already defined in publisher/category.php
            global $categoryObj;
            $block['currentcat'] = $categoryObj->getCategoryLink('menuTop');
            $catLinkClass        = 'menuSub';
        }
    }

    foreach ($blockCategoriesObj as $catId => $blockCategoryObj) {
        if ($catId != $categoryid) {
            $block['categories'][$catId]['categoryLink'] = $blockCategoryObj->getCategoryLink($catLinkClass);
        }
    }

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_items_menu_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Utility::createCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(
        [
            'datesub' => _MB_PUBLISHER_DATE,
            'counter' => _MB_PUBLISHER_HITS,
            'weight'  => _MB_PUBLISHER_WEIGHT,
        ]
    );
    $dispEle = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);

    return $form->render();
}
