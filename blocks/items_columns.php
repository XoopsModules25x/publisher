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
 * @author          Bandit-x
 */

use XoopsModules\Publisher\{BlockForm,
    CategoryHandler,
    Helper,
    ItemHandler,
    Utility
};

require_once \dirname(__DIR__) . '/include/common.php';

/***
 * Function To Show Publisher Items From Categories In Their Own Columns
 *
 * @param array $options Block Options
 *
 * @return bool|array
 */
function publisher_items_columns_show($options)
{
    //    global $xoTheme;
    $helper = Helper::getInstance();
    /** @var CategoryHandler $categoryHandler */
    $categoryHandler = $helper->getHandler('Category');
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    //Column Settings
    $optNumColumns  = isset($options[0]) ? (int)$options[0] : '2';
    $selCategories  = isset($options[1]) ? array_map('\intval', explode(',', $options[1])) : [];
    $optCatItems    = (int)$options[2];
    $optCatTruncate = isset($options[3]) ? (int)$options[3] : '0';

    $block                  = [];
    $block['lang_reads']    = _MB_PUBLISHER_READS;
    $block['lang_comments'] = _MB_PUBLISHER_COMMENTS;
    $block['lang_readmore'] = _MB_PUBLISHER_READMORE;

    $selCategoriesObj = [];

    //get permited categories only once
    $categoriesObj = $categoryHandler->getCategories(0, 0, -1);

    //if not selected 'all', let's get the selected ones
    if (in_array(0, $selCategories, true)) {
        $selCategoriesObj = $categoriesObj;
    } else {
        foreach ($categoriesObj as $key => $value) {
            if (in_array($key, $selCategories, true)) {
                $selCategoriesObj[$key] = $value;
            }
        }
    }
    unset($key, $value);

    $ccount = count($selCategoriesObj);

    if (0 === $ccount) {
        return false;
    }

    if ($ccount < $optNumColumns) {
        $optNumColumns = $ccount;
    }

    $k       = 0;
    $columns = $mainItem = $subItem = [];

    foreach ($selCategoriesObj as $categoryid => $mainItemCatObj) {
        $categoryItemsObj = $itemHandler->getAllPublished($optCatItems, 0, $categoryid);
        $scount           = count($categoryItemsObj);
        if ($scount > 0 && is_array($categoryItemsObj)) {
            reset($categoryItemsObj);
            //First Item
            $thisItem = array_values($categoryItemsObj)[0];

            $mainItem['item_title']      = $thisItem->getTitle();
            $mainItem['item_cleantitle'] = strip_tags($thisItem->getTitle());
            $mainItem['item_link']       = $thisItem->itemid();
            $mainItem['itemurl']         = $thisItem->getItemUrl();
            $mainItem['date']            = $thisItem->getDatesub();

            $mainImage = $thisItem->getMainImage();
            if (empty($mainImage['image_path'])) {
                $mainImage['image_path'] = PUBLISHER_URL . '/assets/images/default_image.jpg';
            }
            // check to see if GD function exist
            $mainItem['item_image'] = $mainImage['image_path'];
            if (!empty($mainImage['image_path']) && function_exists('imagecreatetruecolor')) {
                $mainItem['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=100';
                $mainItem['image_path'] = $mainImage['image_path'];
            }

            $mainItem['item_summary'] = $thisItem->getBlockSummary($optCatTruncate);

            $mainItem['item_cat_name']        = $mainItemCatObj->name();
            $mainItem['item_cat_description'] = '' !== $mainItemCatObj->description() ? $mainItemCatObj->description() : $mainItemCatObj->name();
            $mainItem['item_cat_link']        = $mainItemCatObj->getCategoryLink();
            $mainItem['categoryurl']          = $mainItemCatObj->getCategoryUrl();

            //The Rest
            if ($scount > 1) {
                //                while ((list($itemId, $thisItem) = each($categoryItemsObj)) !== false) {
                foreach ($categoryItemsObj as $itemId => $thisItem) {
                    //TODO do I need to start with 2nd element?
                    $subItem['title']      = $thisItem->getTitle();
                    $subItem['cleantitle'] = strip_tags($thisItem->getTitle());
                    $subItem['link']       = $thisItem->getItemLink();
                    $subItem['itemurl']    = $thisItem->getItemUrl();
                    $subItem['summary']    = $thisItem->getBlockSummary($optCatTruncate);
                    $subItem['date']       = $thisItem->getDatesub();
                    $mainItem['subitem'][] = $subItem;
                    unset($subItem);
                }
            }
            $columns[$k][] = $mainItem;
            unset($thisItem, $mainItem);
            ++$k;

            if ($k == $optNumColumns) {
                $k = 0;
            }
        }
    }
    unset($categoryid);

    $block['template']             = $options[4];
    $block['columns']              = $columns;
    $block['columnwidth']          = (int)(100 / $optNumColumns);
    $block['display_datemainitem'] = $options[5] ?? '';
    $block['display_datesubitem']  = $options[6] ?? '';

    $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/assets/css/publisher.css');

    return $block;
}

/***
 * Edit Function For Multi-Column Category Items Display Block
 *
 * @param array $options Block Options
 *
 * @return string
 */
function publisher_items_columns_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form   = new BlockForm();
    $colEle = new \XoopsFormSelect(_MB_PUBLISHER_NUMBER_COLUMN_VIEW, 'options[0]', $options[0]);
    $colEle->addOptionArray(
        [
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
        ]
    );
    $catEle = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Utility::createCategorySelect($options[1], 0, true, 'options[1]'));

    $cItemsEle = new \XoopsFormText(_MB_PUBLISHER_NUMBER_ITEMS_CAT, 'options[2]', 4, 255, $options[2]);

    $truncateEle = new \XoopsFormText(_MB_PUBLISHER_TRUNCATE, 'options[3]', 4, 255, $options[3]);

    $tempEle = new \XoopsFormSelect(_MB_PUBLISHER_TEMPLATE, 'options[4]', $options[4]);
    $tempEle->addOptionArray(
        [
            'normal'   => _MB_PUBLISHER_TEMPLATE_NORMAL,
            'extended' => _MB_PUBLISHER_TEMPLATE_EXTENDED,
        ]
    );
    $dateMain = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_DATE_MAINITEM, 'options[5]', $options[5]);
    $dateSub  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_DATE_SUBITEM, 'options[6]', $options[6]);

    $form->addElement($colEle);
    $form->addElement($catEle);
    $form->addElement($cItemsEle);
    $form->addElement($truncateEle);
    $form->addElement($tempEle);
    $form->addElement($dateMain);
    $form->addElement($dateSub);

    return $form->render();
}
