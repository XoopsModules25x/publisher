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
    $publisher           = PublisherPublisher::getInstance();
    $opt_display_last    = $options[0];
    $opt_items_count     = $options[1];
    $opt_categoryid      = $options[2];
    $sel_items           = isset($options[3]) ? explode(',', $options[3]) : '';
    $opt_display_poster  = $options[4];
    $opt_display_comment = $options[5];
    $opt_display_type    = $options[6];
    $opt_truncate        = intval($options[7]);
    $opt_catimage        = $options[8];
    if ($opt_categoryid == 0) {
        $opt_categoryid = -1;
    }
    $block = array();
    if ($opt_display_last == 1) {
        $itemsObj   = $publisher->getHandler('item')->getAllPublished($opt_items_count, 0, $opt_categoryid, $sort = 'datesub', $order = 'DESC', 'summary');
        $i          = 1;
        $itemsCount = count($itemsObj);
        if ($itemsObj) {
            if ($opt_categoryid != -1 && $opt_catimage) {
                $cat                     = $publisher->getHandler('category')->get($opt_categoryid);
                $category['name']        = $cat->name();
                $category['categoryurl'] = $cat->getCategoryUrl();
                if ($cat->image() != 'blank.png') {
                    $category['image_path'] = publisher_getImageDir('category', false) . $cat->image();
                } else {
                    $category['image_path'] = '';
                }
                $block['category'] = $category;
            }
            foreach ($itemsObj as $key => $thisitem) {
                $item = $thisitem->toArraySimple('default', 0, $opt_truncate);
                if ($i < $itemsCount) {
                    $item['showline'] = true;
                } else {
                    $item['showline'] = false;
                }
                if ($opt_truncate > 0) {
                    $block['truncate'] = true;
                }
                $block['items'][] = $item;
                ++$i;
            }
        }
    } else {
        $i          = 1;
        $itemsCount = count($sel_items);
        foreach ($sel_items as $item_id) {
            $itemObj = $publisher->getHandler('item')->get($item_id);
            if (!$itemObj->notLoaded()) {
                $item             = $itemObj->toArraySimple();
                $item['who_when'] = sprintf(_MB_PUBLISHER_WHO_WHEN, $itemObj->posterName(), $itemObj->datesub());
                if ($i < $itemsCount) {
                    $item['showline'] = true;
                } else {
                    $item['showline'] = false;
                }
                if ($opt_truncate > 0) {
                    $block['truncate'] = true;
                    $item['summary']   = publisher_truncateTagSafe($item['summary'], $opt_truncate);
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
    $block['display_whowhen_link'] = $opt_display_poster;
    $block['display_comment_link'] = $opt_display_comment;
    $block['display_type']         = $opt_display_type;

    $block["publisher_url"] = PUBLISHER_URL;
    $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/css/publisher.css');

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
    $catEle    = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisher_createCategorySelect($options[2], 0, true, 'options[2]'));
    $publisher = PublisherPublisher::getInstance();
    $criteria  = new CriteriaCompo();
    $criteria->setSort('datesub');
    $criteria->setOrder('DESC');
    $itemsObj = $publisher->getHandler('item')->getList($criteria);
    $keys     = array_keys($itemsObj);
    unset($criteria);
    if (empty($options[3]) || ($options[3] == 0)) {
        $sel_items = isset($keys[0]) ? $keys[0] : 0;
    } else {
        $sel_items = explode(',', $options[3]);
    }
    $itemEle = new XoopsFormSelect(_MB_PUBLISHER_SELECT_ITEMS, 'options[3]', $sel_items, 10, true);
    $itemEle->addOptionArray($itemsObj);
    $whoEle  = new XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_WHO_AND_WHEN, 'options[4]', $options[4]);
    $comEle  = new XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENTS, 'options[5]', $options[5]);
    $typeEle = new XoopsFormSelect(_MB_PUBLISHER_DISPLAY_TYPE, 'options[6]', $options[6]);
    $typeEle->addOptionArray(array(
                                 'block'  => _MB_PUBLISHER_DISPLAY_TYPE_BLOCK,
                                 'bullet' => _MB_PUBLISHER_DISPLAY_TYPE_BULLET,
                             ));
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
