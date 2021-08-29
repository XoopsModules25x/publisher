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
    Category,
    CategoryHandler,
    Helper,
    Item,
    ItemHandler,
    Utility
};

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array|bool
 */
function publisher_items_spot_show($options)
{
    //    global $xoTheme;
    $helper = Helper::getInstance();
    /** @var CategoryHandler $categoryHandler */
    $categoryHandler = $helper->getHandler('Category');
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    xoops_loadLanguage('main', 'publisher');

    $optDisplayLast         = $options[0];
    $optItemsCount          = $options[1];
    $optCategoryId          = $options[2];
    $selItems               = isset($options[3]) ? explode(',', $options[3]) : '';
    $optDisplayPoster       = $options[4];
    $optDisplayComment      = $options[5];
    $optDisplayType         = $options[6];
    $optTruncate            = (int)$options[7];
    $optCatImage            = $options[8];
    $optSortOrder           = $options[9] ?? '';
    $optBtnDisplayMore      = $options[10] ?? '';
    $optDisplayReads        = $options[11] ?? '';
    $optdisplayitemimage    = $options[12] ?? '';
    $optdisplaywhenlink     = $options[13] ?? '';
    $optdisplaycategorylink = $options[14] ?? '';
    $optdisplayadminlink    = $options[15] ?? '';
    $optdisplayreadmore     = $options[16] ?? '';

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
                /** @var Category $cat */
                $cat                     = $categoryHandler->get($optCategoryId);
                $category['name']        = $cat->name;
                $category['categoryurl'] = $cat->getCategoryUrl();
                if ('blank.png' !== $cat->getImage()) {
                    $category['image_path'] = Utility::getImageDir('category', false) . $cat->getImage();
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
                /** @var Item $itemObj */
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
                        $item['summary']   = Utility::truncateHtml($item['summary'], $optTruncate);
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
    $block['lang_reads']    = _MB_PUBLISHER_READS;
    $block['lang_comments'] = _MB_PUBLISHER_COMMENTS;
    $block['lang_readmore'] = _MB_PUBLISHER_READMORE;
    $block['lang_poster']   = _MB_PUBLISHER_POSTEDBY;
    $block['lang_date']     = _MB_PUBLISHER_ON;
    $block['lang_category'] = _MB_PUBLISHER_CATEGORY;

    $block['display_whowhen_link'] = $optDisplayPoster;
    $block['display_who_link']     = $optDisplayPoster;
    $block['display_comment_link'] = $optDisplayComment;
    $block['display_type']         = $optDisplayType;
    $block['display_reads']        = $optDisplayReads;
    $block['display_cat_image']    = $optCatImage;
    $block['display_item_image']   = $optdisplayitemimage;
    $block['display_when_link']    = $optdisplaywhenlink;
    $block['display_categorylink'] = $optdisplaycategorylink;
    $block['display_adminlink']    = $optdisplayadminlink;
    $block['display_readmore']     = $optdisplayreadmore;

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
    $form     = new BlockForm();
    $autoEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_AUTO_LAST_ITEMS, 'options[0]', $options[0]);
    $countEle = new \XoopsFormText(_MB_PUBLISHER_LAST_ITEMS_COUNT, 'options[1]', 2, 255, $options[1]);
    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Utility::createCategorySelect($options[2], 0, true, 'options[2]', false));
    $helper   = Helper::getInstance();
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    $criteria    = new \CriteriaCompo();
    $criteria->setSort('datesub');
    $criteria->setOrder('DESC');
    $itemsObj = $itemHandler->getList($criteria);
    $keys     = array_keys($itemsObj);
    unset($criteria);
    if (empty($options[3]) || (0 == $options[3])) {
        $selItems = $keys[0] ?? 0;
    } else {
        $selItems = explode(',', $options[3]);
    }
    $itemEle = new \XoopsFormSelect(_MB_PUBLISHER_SELECT_ITEMS, 'options[3]', $selItems, 10, true);
    $itemEle->addOptionArray($itemsObj);
    $whoEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTEDBY, 'options[4]', $options[4]);
    $comEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENTS, 'options[5]', $options[5]);
    $typeEle = new \XoopsFormSelect(_MB_PUBLISHER_DISPLAY_TYPE, 'options[6]', $options[6]);
    $typeEle->addOptionArray(
        [
            'block'  => _MB_PUBLISHER_DISPLAY_TYPE_BLOCK,
            'bullet' => _MB_PUBLISHER_DISPLAY_TYPE_BULLET,
        ]
    );
    $truncateEle = new \XoopsFormText(_MB_PUBLISHER_TRUNCATE, 'options[7]', 4, 255, $options[7]);
    $imageEle    = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_CATIMAGE, 'options[8]', $options[8]);
    $sortEle     = new \XoopsFormSelect(_MI_PUBLISHER_ORDERBY, 'options[9]', $options[9]);
    $sortEle->addOptionArray(
        [
            'title'    => _MI_PUBLISHER_ORDERBY_TITLE,
            'date'     => _MI_PUBLISHER_ORDERBY_DATE,
            'counter'  => _MI_PUBLISHER_ORDERBY_HITS,
            'rating'   => _MI_PUBLISHER_ORDERBY_RATING,
            'votes'    => _MI_PUBLISHER_ORDERBY_VOTES,
            'comments' => _MI_PUBLISHER_ORDERBY_COMMENTS,
            'weight'   => _MI_PUBLISHER_ORDERBY_WEIGHT,
        ]
    );
    $dispMoreEle   = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_MORELINK, 'options[10]', $options[10]);
    $readsEle      = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ, 'options[11]', $options[11]);
    $dispImage     = new \XoopsFormRadioYN(_MB_PUBLISHER_IMGDISPLAY, 'options[12]', $options[12]);
    $dispDate      = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTTIME, 'options[13]', $options[13]);
    $dispCategory  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_TOPICLINK, 'options[14]', $options[14]);
    $dispAdminlink = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_ADMINLINK, 'options[15]', $options[15]);
    $dispReadmore  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ_FULLITEM, 'options[16]', $options[16]);

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
    $form->addElement($dispImage);
    $form->addElement($dispDate);
    $form->addElement($dispCategory);
    $form->addElement($dispAdminlink);
    $form->addElement($dispReadmore);

    return $form->render();
}
