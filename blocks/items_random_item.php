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

use XoopsModules\Publisher\{Constants,
    BlockForm,
    CategoryHandler,
    Helper,
    ItemHandler
};

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 * @throws \Exception
 */
function publisher_items_random_item_show($options)
{
    $block = [];

    $helper = Helper::getInstance();
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    // creating the ITEM object
    $itemsObj = $itemHandler->getRandomItem('', [Constants::PUBLISHER_STATUS_PUBLISHED]);

    if (!is_object($itemsObj)) {
        return $block;
    }

    $block['content']       = $itemsObj->getBlockSummary(300, true); //show complete summary  but truncate to 300 if only body available
    $block['id']            = $itemsObj->itemid();
    $block['url']           = $itemsObj->getItemUrl();
    $block['lang_fullitem'] = _MB_PUBLISHER_FULLITEM;
    $block['lang_poster']   = _MB_PUBLISHER_POSTEDBY;
    $block['lang_date']     = _MB_PUBLISHER_ON;
    $block['lang_category'] = _MB_PUBLISHER_CATEGORY;
    $block['lang_reads']    = _MB_PUBLISHER_HITS;
    $block['titlelink']     = $itemsObj->getItemLink('titlelink');
    $block['alt']           = strip_tags($itemsObj->getItemLink());
    $block['date']          = $itemsObj->getDatesub();
    $block['poster']        = $itemsObj->getLinkedPosterName();
    $block['categorylink']  = $itemsObj->getCategoryLink();
    $block['hits']          = '&nbsp;' . $itemsObj->counter() . ' ' . _READS . '';

    $mainImage = $itemsObj->getMainImage(); // check to see if GD function exist
    if (empty($mainImage['image_path'])) {
        $mainImage['image_path'] = PUBLISHER_URL . '/assets/images/default_image.jpg';
    }
    if (function_exists('imagecreatetruecolor')) {
        $block['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '';
        $block['image_path'] = $mainImage['image_path'];
    } else {
        $block['item_image'] = $mainImage['image_path'];
    }

    $block['cancomment'] = $itemsObj->cancomment();
    $comments            = $itemsObj->comments();
    if ($comments > 0) {
        //shows 1 comment instead of 1 comm. if comments ==1
        //langugage file modified accordingly
        if (1 == $comments) {
            $block['comment'] = '&nbsp;' . _MB_PUBLISHER_ONECOMMENT . '&nbsp;';
        } else {
            $block['comment'] = '&nbsp;' . $comments . '&nbsp;' . _MB_PUBLISHER_COMMENTS . '&nbsp;';
        }
    } else {
        $block['comment'] = '&nbsp;' . _MB_PUBLISHER_NO_COMMENTS . '&nbsp;';
    }
    $block['display_summary']       = $options[0];
    $block['display_item_image']    = $options[1];
    $block['display_poster']        = $options[2];
    $block['display_date']          = $options[3];
    $block['display_categorylink']  = $options[4];
    $block['display_hits']          = $options[5];
    $block['display_comment']       = $options[6];
    $block['display_lang_fullitem'] = $options[7];

    $block['items'][] = $block;

    return $block;
}

/**
 * @param $options
 * @return string
 */
function publisher_items_random_item_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form         = new BlockForm();
    $showSummary  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_SUMMARY, 'options[0]', $options[0]);
    $showImage    = new \XoopsFormRadioYN(_MB_PUBLISHER_IMGDISPLAY, 'options[1]', $options[1]);
    $showPoster   = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTEDBY, 'options[2]', $options[2]);
    $showDate     = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_POSTTIME, 'options[3]', $options[3]);
    $showCategory = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_TOPICLINK, 'options[4]', $options[4]);
    $showHits     = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ, 'options[5]', $options[5]);
    $showComment  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_COMMENT, 'options[6]', $options[6]);
    $dispMoreEle  = new \XoopsFormRadioYN(_MB_PUBLISHER_DISPLAY_READ_FULLITEM, 'options[7]', $options[7]);

    $form->addElement($showSummary);
    $form->addElement($showImage);
    $form->addElement($showPoster);
    $form->addElement($showDate);
    $form->addElement($showCategory);
    $form->addElement($showHits);
    $form->addElement($showComment);
    $form->addElement($dispMoreEle);

    return $form->render();
}
