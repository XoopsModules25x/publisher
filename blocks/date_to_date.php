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

use XoopsModules\Publisher\{BlockForm,
    Helper,
    ItemHandler
};

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_date_to_date_show($options)
{
    $myts   = \MyTextSanitizer::getInstance();
    $helper = Helper::getInstance();

    $block = $newItems = [];

    $criteria = new \CriteriaCompo();
    $criteria->add(new \Criteria('datesub', strtotime($options[0]), '>'));
    $criteria->add(new \Criteria('datesub', isset($options[1]) ? strtotime($options[1]) : '', '<'));
    $criteria->setSort('datesub');
    $criteria->setOrder('DESC');

    // creating the ITEM objects that belong to the selected category
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    $itemsObj    = $itemHandler->getObjects($criteria);
    //    $totalItems = count($itemsObj);

    if ($itemsObj && is_array($itemsObj)) {
        foreach ($itemsObj as $iValue) {
            $newItems['itemid']       = $iValue->itemid();
            $newItems['title']        = $iValue->getTitle();
            $newItems['categoryname'] = $iValue->getCategoryName();
            $newItems['categoryid']   = $iValue->categoryid();
            $newItems['date']         = $iValue->getDatesub();
            $newItems['poster']       = $iValue->getLinkedPosterName();
            $newItems['itemlink']     = $iValue->getItemLink(false, $options[3] ?? 65);
            $newItems['categorylink'] = $iValue->getCategoryLink();
            $block['items'][]         = $newItems;
        }

        $block['lang_title']            = _MB_PUBLISHER_ITEMS;
        $block['lang_category']         = _MB_PUBLISHER_CATEGORY;
        $block['lang_poster']           = _MB_PUBLISHER_POSTEDBY;
        $block['lang_date']             = _MB_PUBLISHER_DATE;
        $moduleName                     = $myts->displayTarea($helper->getModule()->getVar('name'));
        $block['lang_visitItem']        = _MB_PUBLISHER_VISITITEM . ' ' . $moduleName;
        $block['lang_articles_from_to'] = sprintf(_MB_PUBLISHER_ARTICLES_FROM_TO, $options[0], $options[1] ?? 0);
    }

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_date_to_date_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');
    xoops_load('XoopsFormTextDateSelect');

    if ('' === $options[0]) {
        $options[0] = formatTimestamp(1424860422);
    }

    $form    = new BlockForm();
    $fromEle = new \XoopsFormTextDateSelect(_MB_PUBLISHER_FROM, 'options[0]', 15, strtotime($options[0]));
    //    $fromEle->setNocolspan();
    $untilEle = new \XoopsFormTextDateSelect(_MB_PUBLISHER_UNTIL, 'options[1]', 15, isset($options[1]) ? strtotime($options[1]) : '');
    //    $untilEle->setNocolspan();

    $form->addElement($fromEle);
    $form->addElement($untilEle);

    return $form->render();
}
