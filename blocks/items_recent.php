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
function publisher_items_recent_show($options)
{
    $helper = Publisher\Helper::getInstance();
    $myts      = \MyTextSanitizer::getInstance();

    $block = $newItems = [];

    $selectedcatids = explode(',', $options[0]);

    $allcats = false;
    if (in_array(0, $selectedcatids)) {
        $allcats = true;
    }

    $sort  = $options[1];
    $order = Publisher\Utility::getOrderBy($sort);
    $limit = $options[2];
    $start = 0;

    // creating the ITEM objects that belong to the selected category
    if ($allcats) {
        $criteria = null;
    } else {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('categoryid', '(' . $options[0] . ')', 'IN'));
    }
    $itemsObj = $helper->getHandler('Item')->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, 'none');

    $totalItems = count($itemsObj);

    if ($itemsObj && $totalItems > 0) {
        for ($i = 0; $i < $totalItems; ++$i) {
            $newItems['itemid']       = $itemsObj[$i]->itemid();
            $newItems['title']        = $itemsObj[$i]->getTitle();
            $newItems['categoryname'] = $itemsObj[$i]->getCategoryName();
            $newItems['categoryid']   = $itemsObj[$i]->categoryid();
            $newItems['date']         = $itemsObj[$i]->getDatesub();
            $newItems['poster']       = $itemsObj[$i]->getLinkedPosterName();
            $newItems['itemlink']     = $itemsObj[$i]->getItemLink(false, isset($options[3]) ? $options[3] : 65);
            $newItems['categorylink'] = $itemsObj[$i]->getCategoryLink();

            $block['items'][] = $newItems;
        }

        $block['lang_title']     = _MB_PUBLISHER_ITEMS;
        $block['lang_category']  = _MB_PUBLISHER_CATEGORY;
        $block['lang_poster']    = _MB_PUBLISHER_POSTEDBY;
        $block['lang_date']      = _MB_PUBLISHER_DATE;
        $moduleName              = $myts->displayTarea($helper->getModule()->getVar('name'));
        $block['lang_visitItem'] = _MB_PUBLISHER_VISITITEM . ' ' . $moduleName;
    }

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_items_recent_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new Publisher\BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Publisher\Utility::createCategorySelect($options[0], 0, true, 'options[0]'));
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
