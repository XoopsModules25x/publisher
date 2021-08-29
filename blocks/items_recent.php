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
function publisher_items_recent_show($options)
{
    $helper = Helper::getInstance();
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    $myts        = \MyTextSanitizer::getInstance();

    $block = $newItems = [];

    $selectedcatids = explode(',', $options[0]);

    $allcats = false;
    if (in_array(0, $selectedcatids, false)) {
        $allcats = true;
    }

    $sort  = $options[1];
    $order = Utility::getOrderBy($sort);
    $limit = $options[2];
    $start = 0;

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

    $itemsObj = $itemHandler->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, 'none');

    $totalItems = count($itemsObj);

    if ($itemsObj && $totalItems > 0) {
        foreach ($itemsObj as $iValue) {
            $newItems['itemid']       = $iValue->itemid();
            $newItems['itemurl']      = $iValue->getItemUrl();
            $newItems['title']        = $iValue->getTitle();
            $newItems['alt']          = strip_tags($iValue->getItemLink());
            $newItems['categoryname'] = $iValue->getCategoryName();
            $newItems['categoryid']   = $iValue->categoryid();
            $newItems['date']         = $iValue->getDatesub();
            $newItems['poster']       = $iValue->getLinkedPosterName();
            $newItems['itemlink']     = $iValue->getItemLink(false, $options[3] ?? 65);
            $newItems['categorylink'] = $iValue->getCategoryLink();
            $newItems['hits']         = '&nbsp;' . $iValue->counter() . ' ' . _READS . '';
            $newItems['summary']      = $iValue->getBlockSummary(300, true); //show complete summary  but truncate to 300 if only body available

            $mainImage = $iValue->getMainImage(); // check to see if GD function exist
            if (empty($mainImage['image_path'])) {
                $mainImage['image_path'] = PUBLISHER_URL . '/assets/images/default_image.jpg';
            }
            if (function_exists('imagecreatetruecolor')) {
                $newItems['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '';
                $newItems['image_path'] = $mainImage['image_path'];
            } else {
                $newItems['item_image'] = $mainImage['image_path'];
            }
            $newItems['cancomment'] = $iValue->cancomment();
            $comments               = $iValue->comments();
            if ($comments > 0) {
                //shows 1 comment instead of 1 comm. if comments ==1
                //langugage file modified accordingly
                if (1 == $comments) {
                    $newItems['comment'] = '&nbsp;' . _MB_PUBLISHER_ONECOMMENT . '&nbsp;';
                } else {
                    $newItems['comment'] = '&nbsp;' . $comments . '&nbsp;' . _MB_PUBLISHER_COMMENTS . '&nbsp;';
                }
            } else {
                $newItems['comment'] = '&nbsp;' . _MB_PUBLISHER_NO_COMMENTS . '&nbsp;';
            }

            $block['items'][] = $newItems;
        }
        $block['publisher_url']  = PUBLISHER_URL;
        $block['lang_title']     = _MB_PUBLISHER_ITEMS;
        $block['lang_category']  = _MB_PUBLISHER_CATEGORY;
        $block['lang_poster']    = _MB_PUBLISHER_POSTEDBY;
        $block['lang_date']      = _MB_PUBLISHER_DATE;
        $moduleName              = $myts->displayTarea($helper->getModule()->getVar('name'));
        $block['lang_visitItem'] = _MB_PUBLISHER_VISITITEM . ' ' . $moduleName;

        $block['show_image']    = $options[4];
        $block['show_summary']  = $options[5];
        $block['show_category'] = $options[6];
        $block['show_poster']   = $options[7];
        $block['show_date']     = $options[8];
        $block['show_hits']     = $options[9];
        $block['show_comment']  = $options[10];
        $block['show_morelink'] = $options[11];
    }

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_items_recent_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Utility::createCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(
        [
            'datesub' => _MB_PUBLISHER_DATE,
            'counter' => _MB_PUBLISHER_HITS,
            'weight'  => _MB_PUBLISHER_WEIGHT,
        ]
    );
    $dispEle      = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);
    $charsEle     = new \XoopsFormText(_MB_PUBLISHER_CHARS, 'options[3]', 10, 255, $options[3]);
    $showImage    = new \XoopsFormRadioYN(_MB_PUBLISHER_IMGDISPLAY, 'options[4]', $options[4]);
    $showSummary  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_SUMMARY, 'options[5]', $options[5]);
    $showCategory = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_TOPICLINK, 'options[6]', $options[6]);
    $showPoster   = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTEDBY, 'options[7]', $options[7]);
    $showDate     = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTTIME, 'options[8]', $options[8]);
    $showHits     = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ, 'options[9]', $options[9]);
    $showComment  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENT, 'options[10]', $options[10]);
    $dispMoreEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_MORELINK, 'options[11]', $options[11]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);
    $form->addElement($charsEle);
    $form->addElement($showImage);
    $form->addElement($showSummary);
    $form->addElement($showCategory);
    $form->addElement($showPoster);
    $form->addElement($showDate);
    $form->addElement($showHits);
    $form->addElement($showComment);
    $form->addElement($dispMoreEle);

    return $form->render();
}
