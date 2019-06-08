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
use XoopsModules\Publisher\Constants;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_items_new_show($options)
{
    /** @var Publisher\Helper $helper */
    $helper = Publisher\Helper::getInstance();
    /** @var Publisher\ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');

    $selectedcatids = explode(',', $options[0]);

    $block   = [];
    $allcats = false;
    if (in_array(0, $selectedcatids)) {
        $allcats = true;
    }

    $sort  = $options[1];
    $order = Publisher\Utility::getOrderBy($sort);
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
//    $categoryId = -1;
//    $categoryItemsObj = $itemHandler->getAllPublished($optCatItems, 0, $categoryId);



    $itemsObj = $itemHandler->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, 'none');

    $totalitems = count($itemsObj);
    if ($totalitems > 0) {
        foreach ($itemsObj as $iValue) {
            $item           = [];
            $item['link']   = $iValue->getItemLink(false, isset($options[4]) ? $options[4] : 65);
            $item['id']     = $iValue->itemid();
            $item['poster'] = $iValue->posterName(); // for make poster name linked, use getLinkedPosterName() instead of posterName()

            if ('article' === $image) {
                $item['image']      = XOOPS_URL . '/uploads/blank.gif';
                $item['image_name'] = '';
                $images             = $iValue->getImages();
                if (is_object($images['main'])) {
                    // check to see if GD function exist
                    if (!function_exists('imagecreatetruecolor')) {
                        $item['image'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
                    } else {
                        $item['image'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '&amp;w=50';
                    }
                    $item['image_name'] = $images['main']->getVar('image_nicename');
                }
            } elseif ('category' === $image) {
                $item['image']      = $iValue->getCategoryImagePath();
                $item['image_name'] = $iValue->getCategoryName();
            } elseif ('avatar' === $image) {
                if ('0' == $iValue->uid()) {
                    $item['image'] = XOOPS_URL . '/uploads/blank.gif';
                    $images        = $iValue->getImages();
                    if (is_object($images['main'])) {
                        // check to see if GD function exist
                        if (!function_exists('imagecreatetruecolor')) {
                            $item['image'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
                        } else {
                            $item['image'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '&amp;w=50';
                        }
                    }
                } else {
                    // check to see if GD function exist
                    if (!function_exists('imagecreatetruecolor')) {
                        $item['image'] = XOOPS_URL . '/uploads/' . $iValue->posterAvatar();
                    } else {
                        $item['image'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $iValue->posterAvatar() . '&amp;w=50';
                    }
                }
                $item['image_name'] = $iValue->posterName();
            }

            $item['title'] = $iValue->getTitle();

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

    $block['show_order'] = $options[2];

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

    $form = new Publisher\BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Publisher\Utility::createCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray([
                                  'datesub'  => _MB_PUBLISHER_DATE,
                                  'counter'  => _MB_PUBLISHER_HITS,
                                  'weight'   => _MB_PUBLISHER_WEIGHT,
                                  'rating'   => _MI_PUBLISHER_ORDERBY_RATING,
                                  'votes'    => _MI_PUBLISHER_ORDERBY_VOTES,
                                  'comments' => _MI_PUBLISHER_ORDERBY_COMMENTS,
                              ]);

    $showEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_ORDER_SHOW, 'options[2]', $options[2]);
    $dispEle  = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[3]', 10, 255, $options[3]);
    $charsEle = new \XoopsFormText(_MB_PUBLISHER_CHARS, 'options[4]', 10, 255, $options[4]);

    $imageEle = new \XoopsFormSelect(_MB_PUBLISHER_IMAGE_TO_DISPLAY, 'options[5]', $options[5]);
    $imageEle->addOptionArray([
                                  'none'     => _NONE,
                                  'article'  => _MB_PUBLISHER_IMAGE_ARTICLE,
                                  'category' => _MB_PUBLISHER_IMAGE_CATEGORY,
                                  'avatar'   => _MB_PUBLISHER_IMAGE_AVATAR,
                              ]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($showEle);
    $form->addElement($dispEle);
    $form->addElement($charsEle);
    $form->addElement($imageEle);

    return $form->render();
}
