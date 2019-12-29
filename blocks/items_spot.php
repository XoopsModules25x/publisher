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

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array|bool
 */
function publisher_items_spot_show($options)
{
    //    global $xoTheme;
    /** @var Publisher\Helper $helper */
    $helper = Publisher\Helper::getInstance();
    /** @var Publisher\CategoryHandler $categoryHandler */
    $categoryHandler = $helper->getHandler('Category');
    /** @var Publisher\ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');

    $optDisplayLast     = $options[0];
    $optItemsCount      = $options[1];
    $optCategoryId      = $options[2];
    $selItems           = isset($options[3]) ? explode(',', $options[3]) : '';
    $optDisplayPoster   = $options[4];
    $optDisplayComment  = $options[5];
    $optDisplayType     = $options[6];
    $optTruncate        = (int)$options[7];
    $optCatImage        = $options[8];
    $optSortOrder       = isset($options[9])? $options[9]:'';
    $optBtnDisplayMore  = isset($options[10])? $options[10]:'';
    $optDisplayReads    = $options[11];

    if (0 == $optCategoryId) {
        $optCategoryId = -1;
    }
    $block = [];
    if (1 == $optDisplayLast) {
        switch ($optSortOrder) {
            case 'title':
                $sort  = 'title';
                $order = 'ASC';
                break;
            case 'date':
                $sort  = 'datesub';
                $order = 'DESC';
                break;
            case 'counter':
                $sort  = 'counter';
                $order = 'DESC';
                break;
            case 'rating':
                $sort  = 'rating';
                $order = 'DESC';
                break;
            case 'votes':
                $sort  = 'votes';
                $order = 'DESC';
                break;
            case 'comments':
                $sort  = 'comments';
                $order = 'DESC';
                break;
            default:
                $sort  = 'weight';
                $order = 'ASC';
                break;
        }
        $itemsObj   = $itemHandler->getAllPublished($optItemsCount, 0, $optCategoryId, $sort, $order, 'summary');
        $i          = 1;
        $itemsCount = count($itemsObj);
        if ($itemsObj) {
            if (-1 != $optCategoryId) {
                /** @var Publisher\Category $cat */
                $cat                     = $categoryHandler->get($optCategoryId);
                $category['name']        = $cat->name;
                $category['categoryurl'] = $cat->getCategoryUrl();
                if ('blank.png' !== $cat->getImage()) {
                    $category['image_path'] = Publisher\Utility::getImageDir('category', false) . $cat->getImage();
                } else {
                    $category['image_path'] = '';
                }
                $block['category'] = $category;
            } else {
                $block['category']['categoryurl'] = XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME;
            }
            foreach ($itemsObj as $key => $thisItem) {
                $item = $thisItem->toArraySimple('default', 0, $optTruncate);
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
        $i = 1;
        if ($selItems && is_array($selItems)) {
            $itemsCount = count($selItems);
            foreach ($selItems as $itemId) {
                /** @var Publisher\Item $itemObj */
                $itemObj = $itemHandler->get($itemId);
                if (null !== $itemObj && !$itemObj->notLoaded()) {
                    $item             = $itemObj->toArraySimple();
                    $item['who_when'] = sprintf(_MB_PUBLISHER_WHO_WHEN, $item['who'], $item['when']);
                    if ($i < $itemsCount) {
                        $item['showline'] = true;
                    } else {
                        $item['showline'] = false;
                    }
                    if ($optTruncate > 0) {
                        $block['truncate'] = true;
                        $item['summary']   = Publisher\Utility::truncateHtml($item['summary'], $optTruncate);
                    }
                    $block['items'][] = $item;
                    ++$i;
                }
            }
        }
    }
    if (!isset($block['items']) || 0 == count($block['items'])) {
        return false;
    }
    $block['lang_reads']           = _MB_PUBLISHER_READS;
    $block['lang_comments']        = _MB_PUBLISHER_COMMENTS;
    $block['lang_readmore']        = _MB_PUBLISHER_READMORE;
    $block['display_whowhen_link'] = $optDisplayPoster;
    $block['display_comment_link'] = $optDisplayComment;
    $block['display_type']         = $optDisplayType;
    $block['display_reads']        = $optDisplayReads;
    if ($optBtnDisplayMore) {
        $block['lang_displaymore'] = _MB_PUBLISHER_MORE_ITEMS;
    }

    $block['publisher_url'] = PUBLISHER_URL;
    $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/assets/css/' . PUBLISHER_DIRNAME . '.css');

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_items_spot_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');
    $form     = new Publisher\BlockForm();
    $autoEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_AUTO_LAST_ITEMS, 'options[0]', $options[0]);
    $countEle = new \XoopsFormText(_MB_PUBLISHER_LAST_ITEMS_COUNT, 'options[1]', 2, 255, $options[1]);
    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Publisher\Utility::createCategorySelect($options[2], 0, true, 'options[2]', false));
    /** @var Publisher\Helper $helper */
    $helper = Publisher\Helper::getInstance();
    /** @var Publisher\ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    $criteria    = new \CriteriaCompo();
    $criteria->setSort('datesub');
    $criteria->setOrder('DESC');
    $itemsObj = $itemHandler->getList($criteria);
    $keys     = array_keys($itemsObj);
    unset($criteria);
    if (empty($options[3]) || (0 == $options[3])) {
        $selItems = isset($keys[0]) ? $keys[0] : 0;
    } else {
        $selItems = explode(',', $options[3]);
    }
    $itemEle = new \XoopsFormSelect(_MB_PUBLISHER_SELECT_ITEMS, 'options[3]', $selItems, 10, true);
    $itemEle->addOptionArray($itemsObj);
    $whoEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_WHO_AND_WHEN, 'options[4]', $options[4]);
    $comEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENTS, 'options[5]', $options[5]);
    $typeEle = new \XoopsFormSelect(_MB_PUBLISHER_DISPLAY_TYPE, 'options[6]', $options[6]);
    $typeEle->addOptionArray([
                                 'block'  => _MB_PUBLISHER_DISPLAY_TYPE_BLOCK,
                                 'bullet' => _MB_PUBLISHER_DISPLAY_TYPE_BULLET,
                             ]);
    $truncateEle = new \XoopsFormText(_MB_PUBLISHER_TRUNCATE, 'options[7]', 4, 255, $options[7]);
    $imageEle    = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_CATIMAGE, 'options[8]', $options[8]);
    $sortEle     = new \XoopsFormSelect(_MI_PUBLISHER_ORDERBY, 'options[9]', $options[9]);
    $sortEle->addOptionArray([
                                'title'    => _MI_PUBLISHER_ORDERBY_TITLE,
                                'date'     => _MI_PUBLISHER_ORDERBY_DATE,
                                'counter'  => _MI_PUBLISHER_ORDERBY_HITS,
                                'rating'   => _MI_PUBLISHER_ORDERBY_RATING,
                                'votes'    => _MI_PUBLISHER_ORDERBY_VOTES,
                                'comments' => _MI_PUBLISHER_ORDERBY_COMMENTS,
                                'weight'   => _MI_PUBLISHER_ORDERBY_WEIGHT,                               
                             ]);
    $dispMoreEle = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_MORELINK, 'options[10]', $options[10]);
    $readsEle    = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ, 'options[11]', $options[11]);
    
    $form->addElement($autoEle);
    $form->addElement($countEle);
    $form->addElement($catEle);
    $form->addElement($itemEle);
    $form->addElement($whoEle);
    $form->addElement($comEle);
    $form->addElement($typeEle);
    $form->addElement($truncateEle);
    $form->addElement($imageEle);
    $form->addElement($sortEle);
    $form->addElement($dispMoreEle);
    $form->addElement($readsEle);

    return $form->render();
}
