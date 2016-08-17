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
 * @author          Bandit-X
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once dirname(__DIR__) . '/include/common.php';

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
    $publisher = PublisherPublisher::getInstance();

    //Column Settings
    $optNumColumns  = isset($options[0]) ? (int)$options[0] : '2';
    $selCategories  = isset($options[1]) ? explode(',', $options[1]) : array();
    $optCatItems    = (int)$options[2];
    $optCatTruncate = isset($options[3]) ? (int)$options[3] : '0';

    $block                  = array();
    $block['lang_reads']    = _MB_PUBLISHER_READS;
    $block['lang_comments'] = _MB_PUBLISHER_COMMENTS;
    $block['lang_readmore'] = _MB_PUBLISHER_READMORE;

    $selCategoriesObj = array();

    //get permited categories only once
    $categoriesObj = $publisher->getHandler('category')->getCategories(0, 0, -1);

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

    if ($ccount === 0) {
        return false;
    }

    if ($ccount < $optNumColumns) {
        $optNumColumns = $ccount;
    }

    $k       = 0;
    $columns = array();

    foreach ($selCategoriesObj as $categoryId => $mainitemCatObj) {
        $categoryItemsObj = $publisher->getHandler('item')->getAllPublished($optCatItems, 0, $categoryId);
        $scount           = count($categoryItemsObj);
        if ($scount > 0 && is_array($categoryItemsObj)) {
            reset($categoryItemsObj);
            //First Item
            list($itemid, $thisitem) = each($categoryItemsObj);

            $mainitem['item_title']      = $thisitem->getTitle();
            $mainitem['item_cleantitle'] = strip_tags($thisitem->getTitle());
            $mainitem['item_link']       = $thisitem->itemid();
            $mainitem['itemurl']         = $thisitem->getItemUrl();
            $mainImage                   = $thisitem->getMainImage();

            // check to see if GD function exist
            $mainitem['item_image'] = $mainImage['image_path'];
            if (!empty($mainImage['image_path']) && function_exists('imagecreatetruecolor')) {
                $mainitem['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=100';
            }

            $mainitem['item_summary'] = $thisitem->getBlockSummary($optCatTruncate);

            $mainitem['item_cat_name']        = $mainitemCatObj->name();
            $mainitem['item_cat_description'] = $mainitemCatObj->description() !== '' ? $mainitemCatObj->description() : $mainitemCatObj->name();
            $mainitem['item_cat_link']        = $mainitemCatObj->getCategoryLink();
            $mainitem['categoryurl']          = $mainitemCatObj->getCategoryUrl();

            //The Rest
            if ($scount > 1) {
                while ((list($itemid, $thisitem) = each($categoryItemsObj)) !== false) {
                    $subitem['title']      = $thisitem->getTitle();
                    $subitem['cleantitle'] = strip_tags($thisitem->getTitle());
                    $subitem['link']       = $thisitem->getItemLink();
                    $subitem['itemurl']    = $thisitem->getItemUrl();
                    $subitem['summary']    = $thisitem->getBlockSummary($optCatTruncate);
                    $mainitem['subitem'][] = $subitem;
                    unset($subitem);
                }
            }
            $columns[$k][] = $mainitem;
            unset($thisitem, $mainitem);
            ++$k;

            if ($k == $optNumColumns) {
                $k = 0;
            }
        }
    }
    unset($categoryId, $mainitemCatObj);

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
    include_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form   = new PublisherBlockForm();
    $colEle = new XoopsFormSelect(_MB_PUBLISHER_NUMBER_COLUMN_VIEW, 'options[0]', $options[0]);
    $colEle->addOptionArray(array(
                                '1' => 1,
                                '2' => 2,
                                '3' => 3,
                                '4' => 4,
                                '5' => 5
                            ));
    $catEle      = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisherCreateCategorySelect($options[1], 0, true, 'options[1]'));
    $cItemsEle   = new XoopsFormText(_MB_PUBLISHER_NUMBER_ITEMS_CAT, 'options[2]', 4, 255, $options[2]);
    $truncateEle = new XoopsFormText(_MB_PUBLISHER_TRUNCATE, 'options[3]', 4, 255, $options[3]);

    $tempEle = new XoopsFormSelect(_MB_PUBLISHER_TEMPLATE, 'options[4]', $options[4]);
    $tempEle->addOptionArray(array(
                                 'normal'   => _MB_PUBLISHER_TEMPLATE_NORMAL,
                                 'extended' => _MB_PUBLISHER_TEMPLATE_EXTENDED
                             ));

    $form->addElement($colEle);
    $form->addElement($catEle);
    $form->addElement($cItemsEle);
    $form->addElement($truncateEle);
    $form->addElement($tempEle);

    return $form->render();
}
