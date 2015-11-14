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
 * @version         $Id: items_spot.php 10374 2012-12-12 23:39:48Z trabis $
 */
// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array|bool
 */
function publisher_items_spot_show($options)
{
    //    global $xoTheme;
    $publisher           =& PublisherPublisher::getInstance();
    $optDisplayLast    = $options[0];
    $optItemsCount     = $options[1];
    $optCategoryId      = $options[2];
    $selItems           = isset($options[3]) ? explode(',', $options[3]) : '';
    $optDisplayPoster  = $options[4];
    $optDisplayComment = $options[5];
    $optDisplayType    = $options[6];
    $optTruncate        = (int)($options[7]);
    $optCatImage        = $options[8];
    if ($optCategoryId == 0) {
        $optCategoryId = -1;
    }
    $block = array();
    if ($optDisplayLast == 1) {
        $itemsObj   =& $publisher->getHandler('item')->getAllPublished($optItemsCount, 0, $optCategoryId, $sort = 'datesub', $order = 'DESC', 'summary');
        $i          = 1;
        $itemsCount = count($itemsObj);
        if ($itemsObj) {
            if ($optCategoryId != -1 && $optCatImage) {
                $cat                     =& $publisher->getHandler('category')->get($optCategoryId);
                $category['name']        = $cat->name();
                $category['categoryurl'] = $cat->getCategoryUrl();
                if ($cat->getImage() !== 'blank.png') {
                    $category['image_path'] = publisherGetImageDir('category', false) . $cat->getImage();
                } else {
                    $category['image_path'] = '';
                }
                $block['category'] = $category;
            }
            foreach ($itemsObj as $key => $thisitem) {
                $item = $thisitem->toArraySimple('default', 0, $optTruncate);
                if ($i < $itemsCount) {
                    $item['showline'] = true;
                } else {
                    $item['showline'] = false;
                }
                if ($optTruncate > 0) {
                    $block['truncate'] = true;
                }
                $block['items'][] = $item;
                ++$i;
            }
        }
    } else {
        $i          = 1;
        $itemsCount = count($selItems);
        foreach ($selItems as $itemId) {
            $itemObj =& $publisher->getHandler('item')->get($itemId);
            if (!$itemObj->notLoaded()) {
                $item             = $itemObj->toArraySimple();
                $item['who_when'] = sprintf(_MB_PUBLISHER_WHO_WHEN, $itemObj->posterName(), $itemObj->getDatesub());
                if ($i < $itemsCount) {
                    $item['showline'] = true;
                } else {
                    $item['showline'] = false;
                }
                if ($optTruncate > 0) {
                    $block['truncate'] = true;
                    $item['summary']   = publisherTruncateTagSafe($item['summary'], $optTruncate);
                }
                $block['items'][] = $item;
                ++$i;
            }
        }
    }
    if (!isset($block['items']) || count($block['items']) == 0) {
        return false;
    }
    $block['lang_reads']           = _MB_PUBLISHER_READS;
    $block['lang_comments']        = _MB_PUBLISHER_COMMENTS;
    $block['lang_readmore']        = _MB_PUBLISHER_READMORE;
    $block['display_whowhen_link'] = $optDisplayPoster;
    $block['display_comment_link'] = $optDisplayComment;
    $block['display_type']         = $optDisplayType;

    $block['moduleUrl'] = PUBLISHER_URL;
    $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/assets/css/publisher.css');

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_items_spot_edit($options)
{
    include_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');
    $form      = new PublisherBlockForm();
    $autoEle   = new XoopsFormRadioYN(_MB_PUBLISHER_AUTO_LAST_ITEMS, 'options[0]', $options[0]);
    $countEle  = new XoopsFormText(_MB_PUBLISHER_LAST_ITEMS_COUNT, 'options[1]', 2, 255, $options[1]);
    $catEle    = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisherCreateCategorySelect($options[2], 0, true, 'options[2]'));
    $publisher =& PublisherPublisher::getInstance();
    $criteria  = new CriteriaCompo();
    $criteria->setSort('datesub');
    $criteria->setOrder('DESC');
    $itemsObj =& $publisher->getHandler('item')->getList($criteria);
    $keys     = array_keys($itemsObj);
    unset($criteria);
    if (empty($options[3]) || ($options[3] == 0)) {
        $selItems = isset($keys[0]) ? $keys[0] : 0;
    } else {
        $selItems = explode(',', $options[3]);
    }
    $itemEle = new XoopsFormSelect(_MB_PUBLISHER_SELECT_ITEMS, 'options[3]', $selItems, 10, true);
    $itemEle->addOptionArray($itemsObj);
    $whoEle  = new XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_WHO_AND_WHEN, 'options[4]', $options[4]);
    $comEle  = new XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENTS, 'options[5]', $options[5]);
    $typeEle = new XoopsFormSelect(_MB_PUBLISHER_DISPLAY_TYPE, 'options[6]', $options[6]);
    $typeEle->addOptionArray(array(
                                 'block'  => _MB_PUBLISHER_DISPLAY_TYPE_BLOCK,
                                 'bullet' => _MB_PUBLISHER_DISPLAY_TYPE_BULLET));
    $truncateEle = new XoopsFormText(_MB_PUBLISHER_TRUNCATE, 'options[7]', 4, 255, $options[7]);
    $imageEle    = new XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_CATIMAGE, 'options[8]', $options[8]);
    $form->addElement($autoEle);
    $form->addElement($countEle);
    $form->addElement($catEle);
    $form->addElement($itemEle);
    $form->addElement($whoEle);
    $form->addElement($comEle);
    $form->addElement($typeEle);
    $form->addElement($truncateEle);
    $form->addElement($imageEle);

    return $form->render();
}
