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
 * @author          Bandit-x
 * @author          Mowaffak
 */

use XoopsModules\Publisher\{
    Common\Configurator,
    Constants,
    Helper,
    ItemHandler,
    Seo,
    Utility
};

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return bool|array
 */
function publisher_latest_news_show($options)
{
    $block = [];

    $configurator = new Configurator();
    $icons = $configurator->icons;

    $helper = Helper::getInstance();
    $helper->loadLanguage('main');
    /** @var ItemHandler $itemHandler */
    $itemHandler = $helper->getHandler('Item');
    //    xoops_loadLanguage('main', 'publisher');

    $start           = $options[0]; // You can show articles from specified range
    $limit           = $options[1];
    $columnCount     = $options[2];
    $letters         = $options[3];
    $selectedStories = $options[4];
    $sort            = $options[9];
    $order           = Utility::getOrderBy($sort);
    $imgWidth        = $options[11];
    $imgHeight       = $options[12];
    $border          = $options[13];
    $bordercolor     = $options[14];

    $block['spec']['columnwidth'] = (1 / $columnCount * 100);

    $allcats = false;
    if (!isset($options[31])) {
        $allcats = true;
    } elseif (in_array(0, explode(',', $options[31]), true)) {
        $allcats = true;
    }

    // creating the ITEM objects that belong to the selected category
    if ($allcats) {
        $criteria = null;
    } else {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('categoryid', '(' . $options[31] . ')', 'IN'));
    }

    // Use specific ITEMS
    if (0 != $selectedStories) {
        unset($criteria); //removes category option
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('itemid', '(' . $selectedStories . ')', 'IN'));
    }

    $publisherIsAdmin = $helper->isUserAdmin();
    if (!$publisherIsAdmin) {
        if (null === $criteria) {
            $criteria = new \CriteriaCompo();
        }
        $criteriaDateSub = new \Criteria('datesub', time(), '<=');
        $criteria->add($criteriaDateSub);
    }

    $itemsObj = $itemHandler->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], -1, $sort, $order, '', true, $criteria, 'itemid');

    $scount = count($itemsObj);

    if (0 == $scount) {
        return false;
    }
    $k       = 0;
    $columns = [];

    foreach ($itemsObj as $itemId => $itemObj) {
        $item            = [];
        $item['itemurl'] = $itemObj->getItemUrl();
        $item['title']   = $itemObj->getItemLink();
        $item['alt']     = strip_tags($itemObj->getItemLink());
        $mainImage       = $itemObj->getMainImage();
        if (empty($mainImage['image_path'])) {
            $mainImage['image_path'] = PUBLISHER_URL . '/assets/images/default_image.jpg';
        }
        // check to see if GD function exist
        if (!empty($mainImage['image_path']) && !function_exists('imagecreatetruecolor')) {
            $item['item_image'] = $mainImage['image_path'];
        } else {
            $item['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=' . $imgWidth; // No $imgHeight for autoheight option
            $item['image_path'] = $mainImage['image_path'];
        }
        $item['text']               = $itemObj->getBlockSummary($letters);
        $item['display_item_image'] = $options[10];
        $item['display_summary']    = $options[16];
        $item['display_adminlink']  = $options[29];
        $item                       = $itemObj->getMainImage($item); //returns an array

        $lsHeight = $imgPosition = $lsMargin = '';
        if (0 != $options[12]) {
            $lsHeight = 'height="' . $imgHeight . '" ';
        } // set height = 0 in block option for auto height

        if ('LEFT' === $options[15]) {
            $imgPosition       = 'float: left';
            $lsMargin          = '-right';
            $block['position'] = $imgPosition;
            $block['margin']   = $lsMargin;
        }

        if ('CENTER' === $options[15]) {
            $imgPosition       = 'text-align:center';
            $lsMargin          = '';
            $block['position'] = $imgPosition;
            $block['margin']   = $lsMargin;
        }

        if ('RIGHT' === $options[15]) {
            $imgPosition       = 'float: right';
            $lsMargin          = '-left';
            $block['position'] = $imgPosition;
            $block['margin']   = $lsMargin;
        }

        //Image
        if (1 == $options[10] && '' != $item['image_path']) {
            $startdiv = '<div style="' . $imgPosition . '"><a href="' . $item['itemurl'] . '">';
            $style    = 'style="margin' . $lsMargin . ': 10px; padding: 2px; border: ' . $border . 'px solid #' . $bordercolor . '"';
            $enddiv   = 'width="' . $imgWidth . '" ' . $lsHeight . '></a></div>';
            $image    = $startdiv . '<img ' . $style . ' src="' . $item['item_image'] . '" alt="' . $item['image_name'] . '" ' . $enddiv;

            $item['image'] = $image;
        }

        if (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->isAdmin(-1)) {
            $item['admin'] = "<a href='" . PUBLISHER_URL . '/submit.php?itemid=' . $itemObj->itemid() . "'" . $icons['edit'] . '</a>&nbsp;';
            $item['admin'] .= "<a href='" . PUBLISHER_URL . '/admin/item.php?op=del&amp;itemid=' . $itemObj->itemid() . "'>" . $icons['delete'] . '</a>';
        } else {
            $item['admin'] = '';
        }

        $block['topiclink'] = '';

        if (1 == $options[16]) {
            $block['text'] = $itemObj->getBlockSummary($letters);
        }

        $block['archivelink'] = '';
        if (1 == $options[17]) {
            $block['archivelink'] = '| <a href="' . PUBLISHER_URL . '/archive.php">' . _MB_PUBLISHER_ARCHIVE . '</a> ';
        }

        //TODO: Should we not show link to Anonymous?
        $block['submitlink'] = '';
        if (1 == $options[18] && $GLOBALS['xoopsUser']) {
            $block['submitlink'] = '| <a href="' . PUBLISHER_URL . '/submit.php">' . _MB_PUBLISHER_SUBMITNEWS . '</a> ';
        }

        $item['poster'] = '';
        if (1 == $options[19]) {
            $item['poster']       = $itemObj->posterName();
            $block['lang_poster'] = _MB_PUBLISHER_POSTEDBY;
        }

        $item['posttime'] = '';
        if (1 == $options[20]) {
            $item['posttime']   = $itemObj->getDatesub();
            $block['lang_date'] = _MB_PUBLISHER_ON;
        }

        $item['topic_title'] = '';
        if (1 == $options[21]) {
            $item['topic_title']    = $itemObj->getCategoryLink();
            $item['category']       = strip_tags($itemObj->getCategoryLink());
            $block['lang_category'] = _MB_PUBLISHER_CATEGORY;
        }

        $item['read'] = '';
        if (1 == $options[22]) {
            $item['read']        = $itemObj->counter();
            $block['lang_reads'] = _MB_PUBLISHER_READS;
        }
        $item['cancomment'] = $itemObj->cancomment();
        $comments           = $itemObj->comments();
        if (1 == $options[23]) {
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
        }

        $item['print'] = '';
        if (1 == $options[24]) {
            $item['print'] = '<a href="' . Seo::generateUrl('print', $itemObj->itemid(), $itemObj->short_url()) . '" rel="nofollow">' . $icons['print'] . '</a>&nbsp;';
        }

        $item['pdf'] = '';

        if (1 == $options[25]) {
            $item['pdf'] = "<a href='" . PUBLISHER_URL . '/makepdf.php?itemid=' . $itemObj->itemid() . "' rel='nofollow'>" . $icons['pdf'] . '</a>&nbsp;';
        }

        $item['email'] = '';
        if (1 == $options[26]) {
            $maillink      = 'mailto:?subject=' . sprintf(_CO_PUBLISHER_INTITEM, $GLOBALS['xoopsConfig']['sitename']) . '&amp;body=' . sprintf(_CO_PUBLISHER_INTITEMFOUND, $GLOBALS['xoopsConfig']['sitename']) . ':  ' . $itemObj->getItemUrl();
            $item['email'] = '<a href="' . $maillink . '">' . $icons['mail'] . '</a>&nbsp;';
        }

        $block['morelink'] = '';
        if (1 == $options[27]) {
            $block['morelink'] = '<a href="' . PUBLISHER_URL . '/index.php">' . _MB_PUBLISHER_MORE_ITEMS . '</a> ';
        }

        $item['more'] = '';
        if ((1 == $options[28] && '' != $itemObj->body()) || $itemObj->comments() > 0) {
            $item['more'] = '<a href="' . $itemObj->getItemUrl() . '">' . _MB_PUBLISHER_READMORE . '</a>';
        }

        $block['latestnews_scroll'] = false;
        if (1 == $options[5]) {
            $block['latestnews_scroll'] = true;
        }

        $block['scrollheight'] = $options[6];
        $block['scrollspeed']  = $options[7];
        $block['scrolldir']    = $options[8];

        $block['template'] = $options[30];

        $block['imgwidth']    = $options[11];
        $block['imgheight']   = $options[12];
        $block['border']      = $options[13];
        $block['bordercolor'] = $options[14];

        $block['letters'] = $letters;

        $columns[$k][] = $item;
        ++$k;

        if ($k == $columnCount) {
            $k = 0;
        }
    }

    unset($item);
    $block['columns'] = $columns;
    $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/' . PUBLISHER_DIRNAME . '/assets/css/' . PUBLISHER_DIRNAME . '.css');

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_latest_news_edit($options)
{
    $tabletag1 = '<tr><td style="padding:3px;" width="37%">';
    $tabletag2 = '</td><td style="padding:3px;">';
    $tabletag3 = '<tr><td style="padding-top:20px;border-bottom:1px solid #000;" colspan="2">';
    $tabletag4 = '</td></tr>';

    $form = "<table border='0' cellpadding='0' cellspacing='0'>";
    $form .= $tabletag3 . _MB_PUBLISHER_GENERALCONFIG . $tabletag4; // General Options
    $form .= $tabletag1 . _MB_PUBLISHER_FIRST . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[0] . "' size='4'>&nbsp;" . _MB_PUBLISHER_ITEMS . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_DISP . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[1] . "' size='4'>&nbsp;" . _MB_PUBLISHER_ITEMS . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_COLUMNS . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[2] . "' size='4'>&nbsp;" . _MB_PUBLISHER_COLUMN . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_TEXTLENGTH . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[3] . "' size='4'>&nbsp;" . _MB_PUBLISHER_LETTER . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_SELECTEDSTORIES . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[4] . "' size='16'></td></tr>";
    $form .= $tabletag1 . _MB_PUBLISHER_SCROLL . $tabletag2;
    $form .= publisher_mk_chkbox($options, 5);
    $form .= $tabletag1 . _MB_PUBLISHER_SCROLLHEIGHT . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[6] . "' size='4'></td></tr>";
    $form .= $tabletag1 . _MB_PUBLISHER_SCROLLSPEED . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[7] . "' size='4'></td></tr>";
    $form .= $tabletag1 . _MB_PUBLISHER_SCROLLDIR . $tabletag2;

    $form .= "<select size='1' name='options[8]'>";

    $directions = ['right' => _MB_PUBLISHER_SCROLL_RIGHT, 'left' => _MB_PUBLISHER_SCROLL_LEFT, 'up' => _MB_PUBLISHER_SCROLL_UP, 'down' => _MB_PUBLISHER_SCROLL_DOWN];
    foreach ($directions as $key => $value) {
        $form .= "<option value='{$key}'";
        if ($options[8] == $key) {
            $form .= ' selected';
        }
        $form .= ">{$value}</option>";
    }
    $form .= '</select></td></tr>';

    $form .= $tabletag1 . _MB_PUBLISHER_ORDER . $tabletag2;
    $form .= "<select name='options[9]'>";
    $form .= "<option value='datesub'";
    if ('datesub' === $options[9]) {
        $form .= ' selected';
    }
    $form .= '>' . _MB_PUBLISHER_DATE . '</option>';
    $form .= "<option value='counter'";
    if ('counter' === $options[9]) {
        $form .= ' selected';
    }
    $form .= '>' . _MB_PUBLISHER_HITS . '</option>';
    $form .= "<option value='weight'";
    if ('weight' === $options[9]) {
        $form .= ' selected';
    }
    $form .= '>' . _MB_PUBLISHER_WEIGHT . '</option>';
    $form .= '</select></td></tr>';

    $form .= $tabletag3 . _MB_PUBLISHER_PHOTOSCONFIG . $tabletag4; // Photos Options
    $form .= $tabletag1 . _MB_PUBLISHER_IMGDISPLAY . $tabletag2;
    $form .= publisher_mk_chkbox($options, 10);
    $form .= $tabletag1 . _MB_PUBLISHER_IMGWIDTH . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[11] . "' size='4'>&nbsp;" . _MB_PUBLISHER_PIXEL . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_IMGHEIGHT . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[12] . "' size='4'>&nbsp;" . _MB_PUBLISHER_PIXEL . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_BORDER . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[13] . "' size='4'>&nbsp;" . _MB_PUBLISHER_PIXEL . '</td></tr>';
    $form .= $tabletag1 . _MB_PUBLISHER_BORDERCOLOR . $tabletag2;
    $form .= "<input type='text' name='options[]' value='" . $options[14] . "' size='8'></td></tr>";
    $form .= $tabletag1 . _MB_PUBLISHER_IMGPOSITION . $tabletag2;
    $form .= "<select name='options[]'>";
    $form .= "<option value='LEFT'";
    if ('LEFT' === $options[15]) {
        $form .= ' selected';
    }
    $form .= '>' . _LEFT . "</option>\n";

    $form .= "<option value='CENTER'";
    if ('CENTER' === $options[15]) {
        $form .= ' selected';
    }
    $form .= '>' . _CENTER . "</option>\n";

    $form .= "<option value='RIGHT'";
    if ('RIGHT' === $options[15]) {
        $form .= ' selected';
    }
    $form .= '>' . _RIGHT . '</option>';
    $form .= '</select></td></tr>';

    $form .= $tabletag3 . _MB_PUBLISHER_LINKSCONFIG . $tabletag4; // Links Options
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_SUMMARY . $tabletag2;
    $form .= publisher_mk_chkbox($options, 16);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_ARCHIVELINK . $tabletag2;
    $form .= publisher_mk_chkbox($options, 17);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_SUBMITLINK . $tabletag2;
    $form .= publisher_mk_chkbox($options, 18);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_POSTEDBY . $tabletag2;
    $form .= publisher_mk_chkbox($options, 19);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_POSTTIME . $tabletag2;
    $form .= publisher_mk_chkbox($options, 20);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_TOPICTITLE . $tabletag2;
    $form .= publisher_mk_chkbox($options, 21);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_READ . $tabletag2;
    $form .= publisher_mk_chkbox($options, 22);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_COMMENT . $tabletag2;
    $form .= publisher_mk_chkbox($options, 23);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_PRINT . $tabletag2;
    $form .= publisher_mk_chkbox($options, 24);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_PDF . $tabletag2;
    $form .= publisher_mk_chkbox($options, 25);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_EMAIL . $tabletag2;
    $form .= publisher_mk_chkbox($options, 26);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_MORELINK . $tabletag2;
    $form .= publisher_mk_chkbox($options, 27);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_READ_FULLITEM . $tabletag2;
    $form .= publisher_mk_chkbox($options, 28);
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_ADMINLINK . $tabletag2;
    $form .= publisher_mk_chkbox($options, 29);
    $form .= $tabletag3 . _MB_PUBLISHER_TEMPLATESCONFIG . $tabletag4; // Templates Options
    $form .= $tabletag1 . _MB_PUBLISHER_TEMPLATE . $tabletag2;
    $form .= "<select size='1' name='options[30]'>";

    $templates = [
        'normal'   => _MB_PUBLISHER_TEMPLATE_NORMAL,
        'extended' => _MB_PUBLISHER_TEMPLATE_EXTENDED,
        'ticker'   => _MB_PUBLISHER_TEMPLATE_TICKER,
        'slider1'  => _MB_PUBLISHER_TEMPLATE_SLIDER1,
        'slider2'  => _MB_PUBLISHER_TEMPLATE_SLIDER2,
    ];
    foreach ($templates as $key => $value) {
        $form .= "<option value='{$key}'";
        if ($options[30] == $key) {
            $form .= ' selected';
        }
        $form .= ">{$value}</option>";
    }
    $form .= '</select></td></tr>';

    //Select Which Categories To Show
    $form .= $tabletag3 . _MB_PUBLISHER_TOPICSCONFIG . $tabletag4; // Topics Options
    $form .= $tabletag1 . _MB_PUBLISHER_TOPICSDISPLAY . $tabletag2;
    $form .= Utility::createCategorySelect($options[31], 0, true, 'options[31]');
    $form .= '</td></tr>';

    $form .= '</table>';

    return $form;
}

/**
 * @param $options
 * @param $number
 *
 * @return string
 */
function publisher_mk_chkbox($options, $number)
{
    $chk = '';
    if (1 == $options[$number]) {
        $chk = ' checked';
    }
    $chkbox = "<input type='radio' name='options[{$number}]' value='1'" . $chk . '>&nbsp;' . _YES . '&nbsp;&nbsp;';
    $chk    = '';
    if (0 == $options[$number]) {
        $chk = ' checked';
    }
    $chkbox .= "<input type='radio' name='options[{$number}]' value='0'" . $chk . '>&nbsp;' . _NO . '</td></tr>';

    return $chkbox;
}

/**
 * @param $options
 * @param $number
 *
 * @return string
 */
function publisher_mk_select($options, $number)
{
    $slc = '';
    if (2 == $options[$number]) {
        $slc = ' checked';
    }
    $select = "<input type='radio' name='options[{$number}]' value='2'" . $slc . '>&nbsp;' . _LEFT . '&nbsp;&nbsp;';
    $slc    = '';
    if (1 == $options[$number]) {
        $slc = ' checked';
    }
    $select = "<input type='radio' name='options[{$number}]' value='1'" . $slc . '>&nbsp;' . _CENTER . '&nbsp;&nbsp;';
    $slc    = '';
    if (0 == $options[$number]) {
        $slc = ' checked';
    }
    $select .= "<input type='radio' name='options[{$number}]' value='0'" . $slc . '>&nbsp;' . _RIGHT . '</td></tr>';

    return $select;
}
