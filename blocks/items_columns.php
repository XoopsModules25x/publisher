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
 * @author          Bandit-x
 */

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/***
 * Function To Show Publisher Items From Categories In Their Own Columns
 *
 * @param    array $options Block Options
 *
 * @return bool|array
 */
function publisher_items_columns_show($options)
{
    //    global $xoTheme;
    $helper = Publisher\Helper::getInstance();

    //Column Settings
    $optNumColumns  = isset($options[0]) ? (int)$options[0] : '2';
    $selCategories  = isset($options[1]) ? explode(',', $options[1]) : [];
    $optCatItems    = (int)$options[2];
    $optCatTruncate = isset($options[3]) ? (int)$options[3] : '0';

    $block                  = [];
    $block['lang_reads']    = _MB_PUBLISHER_READS;
    $block['lang_comments'] = _MB_PUBLISHER_COMMENTS;
    $block['lang_readmore'] = _MB_PUBLISHER_READMORE;

    $selCategoriesObj = [];

    //get permited categories only once
    $categoriesObj = $helper->getHandler('Category')->getCategories(0, 0, -1);

    //if not selected 'all', let's get the selected ones
    if (!in_array(0, $selCategories)) {
        foreach ($categoriesObj as $key => $value) {
            if (in_array($key, $selCategories)) {
                $selCategoriesObj[$key] = $value;
            }
        }
    } else {
        $selCategoriesObj = $categoriesObj;
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

    foreach ($selCategoriesObj as $categoryId => $mainItemCatObj) {
        $categoryItemsObj = $helper->getHandler('Item')->getAllPublished($optCatItems, 0, $categoryId);
        $scount           = count($categoryItemsObj);
        if ($scount > 0 && is_array($categoryItemsObj)) {
            reset($categoryItemsObj);
            //First Item
            $thisitem = array_values($categoryItemsObj)[0];

            $mainItem['item_title']      = $thisitem->getTitle();
            $mainItem['item_cleantitle'] = strip_tags($thisitem->getTitle());
            $mainItem['item_link']       = $thisitem->itemid();
            $mainItem['itemurl']         = $thisitem->getItemUrl();
            $mainImage                   = $thisitem->getMainImage();

            // check to see if GD function exist
            $mainItem['item_image'] = $mainImage['image_path'];
            if (!empty($mainImage['image_path']) && function_exists('imagecreatetruecolor')) {
                $mainItem['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=100';
            }

            $mainItem['item_summary'] = $thisitem->getBlockSummary($optCatTruncate);

            $mainItem['item_cat_name']        = $mainItemCatObj->name();
            $mainItem['item_cat_description'] = '' !== $mainItemCatObj->description() ? $mainItemCatObj->description() : $mainItemCatObj->name();
            $mainItem['item_cat_link']        = $mainItemCatObj->getCategoryLink();
            $mainItem['categoryurl']          = $mainItemCatObj->getCategoryUrl();

            //The Rest
            if ($scount > 1) {
                //                while ((list($itemid, $thisitem) = each($categoryItemsObj)) !== false) {
                foreach ($categoryItemsObj as $itemid => $thisitem) { //TODO do I need to start with 2nd element?
                    $subItem['title']      = $thisitem->getTitle();
                    $subItem['cleantitle'] = strip_tags($thisitem->getTitle());
                    $subItem['link']       = $thisitem->getItemLink();
                    $subItem['itemurl']    = $thisitem->getItemUrl();
                    $subItem['summary']    = $thisitem->getBlockSummary($optCatTruncate);
                    $mainItem['subitem'][] = $subItem;
                    unset($subItem);
                }
            }
            $columns[$k][] = $mainItem;
            unset($thisitem, $mainItem);
            ++$k;

            if ($k == $optNumColumns) {
                $k = 0;
            }
        }
    }
    unset($categoryId, $mainItemCatObj);

    $block['template']    = $options[4];
    $block['columns']     = $columns;
    $block['columnwidth'] = (int)(100 / $optNumColumns);

    $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/assets/css/publisher.css');

    return $block;
}

/***
 * Edit Function For Multi-Column Category Items Display Block
 *
 * @param    array $options Block Options
 *
 * @return string
 */
function publisher_items_columns_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form   = new Publisher\BlockForm();
    $colEle = new \XoopsFormSelect(_MB_PUBLISHER_NUMBER_COLUMN_VIEW, 'options[0]', $options[0]);
    $colEle->addOptionArray([
                                '1' => 1,
                                '2' => 2,
                                '3' => 3,
                                '4' => 4,
                                '5' => 5
                            ]);
    $catEle      = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Publisher\Utility::createCategorySelect($options[1], 0, true, 'options[1]'));
    $cItemsEle   = new \XoopsFormText(_MB_PUBLISHER_NUMBER_ITEMS_CAT, 'options[2]', 4, 255, $options[2]);
    $truncateEle = new \XoopsFormText(_MB_PUBLISHER_TRUNCATE, 'options[3]', 4, 255, $options[3]);

    $tempEle = new \XoopsFormSelect(_MB_PUBLISHER_TEMPLATE, 'options[4]', $options[4]);
    $tempEle->addOptionArray([
                                 'normal'   => _MB_PUBLISHER_TEMPLATE_NORMAL,
                                 'extended' => _MB_PUBLISHER_TEMPLATE_EXTENDED
                             ]);

    $form->addElement($colEle);
    $form->addElement($catEle);
    $form->addElement($cItemsEle);
    $form->addElement($truncateEle);
    $form->addElement($tempEle);

    return $form->render();
}
