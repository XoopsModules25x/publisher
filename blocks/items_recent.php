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
 * @version         $Id: items_recent.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_items_recent_show($options)
{
    $publisher =& PublisherPublisher::getInstance();
    $myts      = MyTextSanitizer::getInstance();

    $block = array();

    $selectedcatids = explode(',', $options[0]);

    if (in_array(0, $selectedcatids)) {
        $allcats = true;
    } else {
        $allcats = false;
    }

    $sort  = $options[1];
    $order = publisherGetOrderBy($sort);
    $limit = $options[2];
    $start = 0;

    // creating the ITEM objects that belong to the selected category
    if ($allcats) {
        $criteria = null;
    } else {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('categoryid', '(' . $options[0] . ')', 'IN'));
    }
    $itemsObj =& $publisher->getHandler('item')->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED), -1, $sort, $order, '', true, $criteria, true);

    $totalItems = count($itemsObj);

    if ($itemsObj && $totalItems > 1) {
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
        $modulename              = $myts->displayTarea($publisher->getModule()->getVar('name'));
        $block['lang_visitItem'] = _MB_PUBLISHER_VISITITEM . ' ' . $modulename;
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
    include_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new PublisherBlockForm();

    $catEle   = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisherCreateCategorySelect($options[0], 0, true, 'options[0]'));
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
