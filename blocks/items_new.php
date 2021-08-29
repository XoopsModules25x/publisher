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
    Constants,
    Helper,
    ItemHandler,
    Utility
};

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_items_new_show($options)
{
    $helper = Helper::getInstance();
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');

    $selectedcatids = explode(',', $options[0]);

    $block   = [];
    $allcats = false;
    if (in_array(0, $selectedcatids, true)) {
        $allcats = true;
    }

    $sort  = $options[1];
    $order = Utility::getOrderBy($sort);
    $limit = $options[3];
    $start = 0;
    $image = $options[5];

    // creating the ITEM objects that belong to the selected category
    if ($allcats) {
        $criteria = null;
    } else {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('categoryid', '(' . $options[0] . ')', 'IN'));
    }

    $publisherIsAdmin = $helper->isUserAdmin();
    if (!$publisherIsAdmin) {
        if (null === $criteria) {
            $criteria = new \CriteriaCompo();
        }
        $criteriaDateSub = new \Criteria('datesub', time(), '<=');
        $criteria->add($criteriaDateSub);
    }

    //    $optCatItems    = (int)$options[2];
    //    $categoryid = -1;
    //    $categoryItemsObj = $itemHandler->getAllPublished($optCatItems, 0, $categoryid);

    $itemsObj = $itemHandler->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, 'none');

    $totalitems = count($itemsObj);
    if ($totalitems > 0) {
        foreach ($itemsObj as $iValue) {
            $item                  = [];
            $item['itemurl']       = $iValue->getItemUrl();
            $item['link']          = $iValue->getItemLink(false, $options[4] ?? 65);
            $item['id']            = $iValue->itemid();
            $item['poster']        = $iValue->posterName(); // for make poster name linked, use getLinkedPosterName() instead of posterName()
            $item['categorylink']  = $iValue->getCategoryLink();
            $item['date']          = $iValue->getDatesub();
            $item['hits']          = $iValue->counter();
            $item['summary']       = $iValue->getBlockSummary(300, true); //show complete summary  but truncate to 300 if only body available
            $item['rating']        = $iValue->rating();
            $item['votes']         = $iValue->votes();
            $item['lang_fullitem'] = _MB_PUBLISHER_FULLITEM;
            $item['lang_poster']   = _MB_PUBLISHER_POSTEDBY;
            $item['lang_date']     = _MB_PUBLISHER_ON;
            $item['lang_category'] = _MB_PUBLISHER_CATEGORY;
            $item['lang_hits']     = _MB_PUBLISHER_TOTALHITS;
            $item['cancomment']    = $iValue->cancomment();
            $comments              = $iValue->comments();
            if ($comments > 0) {
                //shows 1 comment instead of 1 comm. if comments ==1
                //langugage file modified accordingly
                if (1 == $comments) {
                    $item['comment'] = '&nbsp;' . _MB_PUBLISHER_ONECOMMENT . '&nbsp;';
                } else {
                    $item['comment'] = '&nbsp;' . $comments . '&nbsp;' . _MB_PUBLISHER_COMMENTS . '&nbsp;';
                }
            } else {
                $item['comment'] = '&nbsp;' . _MB_PUBLISHER_NO_COMMENTS . '&nbsp;';
            }

            if ('article' === $image) {
                $item['image']      = PUBLISHER_URL . '/assets/images/default_image.jpg';
                $item['image_name'] = '';
                $images             = $iValue->getImages();

                if (empty($images['image_path'])) {
                    $images['image_path'] = PUBLISHER_URL . '/assets/images/default_image.jpg';
                }
                if (is_object($images['main'])) {
                    // check to see if GD function exist
                    if (function_exists('imagecreatetruecolor')) {
                        $item['image']      = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '';
                        $item['image_path'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '';
                    } else {
                        $item['image'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
                    }
                    $item['image_name'] = $images['main']->getVar('image_nicename');
                }
            } elseif ('category' === $image) {
                $item['image']      = $iValue->getCategoryImagePath();
                $item['image_name'] = $iValue->getCategoryName();
            } elseif ('avatar' === $image) {
                if ('0' == $iValue->uid()) {
                    $item['image'] = XOOPS_URL . '/uploads/avatars/blank.gif';
                    $images        = $iValue->getImages();
                    if (is_object($images['main'])) {
                        // check to see if GD function exist
                        if (function_exists('imagecreatetruecolor')) {
                            $item['image'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '&amp;w=50';
                        } else {
                            $item['image'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
                        }
                    }
                    // check to see if GD function exist
                } elseif (function_exists('imagecreatetruecolor')) {
                    $item['image'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $iValue->posterAvatar() . '&amp;w=50';
                } else {
                    $item['image'] = XOOPS_URL . '/uploads/' . $iValue->posterAvatar();
                }

                $item['image_name'] = $iValue->posterName();
            }

            $item['title'] = $iValue->getTitle();
            $item['alt']   = strip_tags($iValue->getItemLink());
            if ('datesub' === $sort) {
                $item['new'] = $iValue->getDatesub();
            } elseif ('counter' === $sort) {
                $item['new'] = $iValue->counter();
            } elseif ('weight' === $sort) {
                $item['new'] = $iValue->weight();
            } elseif ('rating' === $sort) {
                $item['new'] = $iValue->rating();
            } elseif ('votes' === $sort) {
                $item['new'] = $iValue->votes();
            } elseif ('comments' === $sort) {
                $item['new'] = $iValue->comments();
            }

            $block['newitems'][] = $item;
        }
    }

    $block['show_order']    = $options[2];
    $block['show_summary']  = $options[6];
    $block['show_poster']   = $options[7];
    $block['show_date']     = $options[8];
    $block['show_category'] = $options[9];
    $block['show_hits']     = $options[10];
    $block['show_comment']  = $options[11];
    $block['show_rating']   = $options[12];

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_items_new_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Utility::createCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(
        [
            'datesub'  => _MB_PUBLISHER_DATE,
            'counter'  => _MB_PUBLISHER_HITS,
            'weight'   => _MB_PUBLISHER_WEIGHT,
            'rating'   => _MI_PUBLISHER_ORDERBY_RATING,
            'votes'    => _MI_PUBLISHER_ORDERBY_VOTES,
            'comments' => _MI_PUBLISHER_ORDERBY_COMMENTS,
        ]
    );

    $showEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_ORDER_SHOW, 'options[2]', $options[2]);
    $dispEle  = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[3]', 10, 255, $options[3]);
    $charsEle = new \XoopsFormText(_MB_PUBLISHER_CHARS, 'options[4]', 10, 255, $options[4]);

    $imageEle = new \XoopsFormSelect(_MB_PUBLISHER_IMAGE_TO_DISPLAY, 'options[5]', $options[5]);
    $imageEle->addOptionArray(
        [
            'none'     => _NONE,
            'article'  => _MB_PUBLISHER_IMAGE_ARTICLE,
            'category' => _MB_PUBLISHER_IMAGE_CATEGORY,
            'avatar'   => _MB_PUBLISHER_IMAGE_AVATAR,
        ]
    );
    $showSummary  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_SUMMARY, 'options[6]', $options[6]);
    $showPoster   = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTEDBY, 'options[7]', $options[7]);
    $showDate     = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTTIME, 'options[8]', $options[8]);
    $showCategory = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_TOPICLINK, 'options[9]', $options[9]);
    $showHits     = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ, 'options[10]', $options[10]);
    $showComment  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENT, 'options[11]', $options[11]);
    $showRating   = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_RATING, 'options[12]', $options[12]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($showEle);
    $form->addElement($dispEle);
    $form->addElement($charsEle);
    $form->addElement($imageEle);
    $form->addElement($showSummary);
    $form->addElement($showPoster);
    $form->addElement($showDate);
    $form->addElement($showCategory);
    $form->addElement($showHits);
    $form->addElement($showComment);
    $form->addElement($showRating);

    return $form->render();
}
