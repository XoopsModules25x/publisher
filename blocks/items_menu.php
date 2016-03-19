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
 * @version         $Id: items_menu.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_items_menu_show($options)
{
    $block = array();

    $publisher = PublisherPublisher::getInstance();

    // Getting all top cats
    $blockCategoriesObj = $publisher->getHandler('category')->getCategories(0, 0, 0);

    if (count($blockCategoriesObj) == 0) {
        return $block;
    }

    // Are we in Publisher ?
    $block['inModule'] = (isset($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') == $publisher->getModule()->getVar('dirname'));

    $catLinkClass = 'menuMain';

    $categoryid = 0;

    if ($block['inModule']) {
        // Are we in a category and if yes, in which one ?
        $categoryid = XoopsRequest::getInt('categoryid', 0, 'GET');

        if ($categoryid != 0) {
            // if we are in a category, then the $categoryObj is already defined in publisher/category.php
            global $categoryObj;
            $block['currentcat'] = $categoryObj->getCategoryLink('menuTop');
            $catLinkClass       = 'menuSub';
        }
    }

    foreach ($blockCategoriesObj as $catid => $blockCategoryObj) {
        if ($catid != $categoryid) {
            $block['categories'][$catid]['categoryLink'] = $blockCategoryObj->getCategoryLink($catLinkClass);
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
    include_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new PublisherBlockForm();

    $catEle   = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisherCreateCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(array(
                                  'datesub' => _MB_PUBLISHER_DATE,
                                  'counter' => _MB_PUBLISHER_HITS,
                                  'weight'  => _MB_PUBLISHER_WEIGHT));
    $dispEle = new XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);

    return $form->render();
}
