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
 * @author          Bandit-x
 * @author          Mowaffak
 * @version         $Id: latest_news.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_latest_news_show($options)
{
    $block = array();

    xoops_loadLanguage('main', 'publisher');
    $publisher = PublisherPublisher::getInstance();

    $start           = $options[0]; // You can show articles from specified range
    $limit           = $options[1];
    $columnCount     = $options[2];
    $letters         = $options[3];
    $selectedStories = $options[4];
    $sort            = $options[9];
    $order           = publisherGetOrderBy($sort);
    $imgWidth        = $options[11];
    $imgHeight       = $options[12];
    $border          = $options[13];
    $bordercolor     = $options[14];

    $block['spec']['columnwidth'] = (int)(1 / $columnCount * 100);

    $allcats = false;
    if (!isset($options[29])) {
        $allcats = true;
    } elseif (in_array(0, explode(',', $options[29]))) {
        $allcats = true;
    }

    // creating the ITEM objects that belong to the selected category
    if ($allcats) {
        $criteria = null;
    } else {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('categoryid', '(' . $options[29] . ')', 'IN'));
    }

    // Use specific ITEMS
    if ($selectedStories != 0) {
        unset($criteria); //removes category option
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('itemid', '(' . $selectedStories . ')', 'IN'));
    }

    $itemsObj = $publisher->getHandler('item')->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED), -1, $sort, $order, '', true, $criteria, 'itemid');

    $scount = count($itemsObj);

    if ($scount == 0) {
        return false;
    }
    $k       = 0;
    $columns = array();

    foreach ($itemsObj as $itemid => $itemObj) {
        $item            = array();
        $item['itemurl'] = $itemObj->getItemUrl();
        $item['title']   = $itemObj->getItemLink();
        $item['alt']     = strip_tags($itemObj->getItemLink());
        $mainImage       = $itemObj->getMainImage();
        // check to see if GD function exist
        if (!function_exists('imagecreatetruecolor')) {
            $item['item_image'] = $mainImage['image_path'];
        } else {
            $item['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=' . $imgWidth; // No $imgHeight for autoheight option
        }
        $item['text'] = $itemObj->getBlockSummary($letters);

        $item = $itemObj->getMainImage($item); //returns an array

        $lsHeight = '';
        if ($options[12] != 0) {
            $lsHeight = 'height="' . $imgHeight . '" ';
        } // set height = 0 in block option for auto height

        if ($options[15] === 'LEFT') {
            $imgPosition = 'float: left';
            $lsMargin    = '-right';
        }

        if ($options[15] === 'CENTER') {
            $imgPosition = 'text-align:center';
            $lsMargin    = '';
        }

        if ($options[15] === 'RIGHT') {
            $imgPosition = 'float: right';
            $lsMargin    = '-left';
        }

        //Image
        if ($options[10] == 1 && $item['image_path'] != '') {
            $startdiv = '<div style="' . $imgPosition . '"><a href="' . $item['itemurl'] . '">';
            $style    = 'style="margin' . $lsMargin . ': 10px; padding: 2px; border: ' . $border . 'px solid #' . $bordercolor . '"';
            $enddiv   = 'width="' . $imgWidth . '" ' . $lsHeight . '/></a></div>';
            $image    = $startdiv . '<img ' . $style . ' src="' . $item['item_image'] . '" alt="' . $item['image_name'] . '" ' . $enddiv;

            $item['image'] = $image;
        }

        if (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->isAdmin(-1)) {
            $item['admin'] = "<a href='" . PUBLISHER_URL . '/submit.php?itemid=' . $itemObj->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif'" . " title='" . _CO_PUBLISHER_EDIT . "' alt='" . _CO_PUBLISHER_EDIT . "' /></a>&nbsp;";
            $item['admin'] .= "<a href='" . PUBLISHER_URL . '/admin/item.php?op=del&amp;itemid=' . $itemObj->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png'" . " title='" . _CO_PUBLISHER_DELETE . "' alt='" . _CO_PUBLISHER_DELETE . "' /></a>";
        } else {
            $item['admin'] = '';
        }

        $block['topiclink'] = '';
        /*
        if ($options[16] == 1) {
         $block['topiclink'] = '| <a href="'.XOOPS_URL.'/modules/news/topics_directory.php">'._AM_NEWS_TOPICS_DIRECTORY.'</a> ';
         }
         */
        $block['archivelink'] = '';
        if ($options[17] == 1) {
            $block['archivelink'] = '| <a href="' . PUBLISHER_URL . '/archive.php">' . _MB_PUBLISHER_ARCHIVE . '</a> ';
        }

        //TODO: Should we not show link to Anonymous?
        $block['submitlink'] = '';
        if ($options[18] == 1 && $GLOBALS['xoopsUser']) {
            $block['submitlink'] = '| <a href="' . PUBLISHER_URL . '/submit.php">' . _MB_PUBLISHER_SUBMITNEWS . '</a> ';
        }

        $item['poster'] = '';
        if ($options[19] == 1) {
            $item['poster'] = _MB_PUBLISHER_POSTER . ' ' . $itemObj->posterName();
        }

        $item['posttime'] = '';
        if ($options[20] == 1) {
            $item['posttime'] = _ON . ' ' . $itemObj->getDatesub();
        }

        $item['topic_title'] = '';
        if ($options[21] == 1) {
            $item['topic_title'] = $itemObj->getCategoryLink() . _MB_PUBLISHER_SP;
        }

        $item['read'] = '';
        if ($options[22] == 1) {
            $item['read'] = '&nbsp;(' . $itemObj->counter() . ' ' . _READS . ')';
        }

        $item['more'] = '';
        if ($itemObj->body() != '' || $itemObj->comments() > 0) {
            $item['more'] = '<a class="publisher_spotlight_readmore" href="' . $itemObj->getItemUrl() . '">' . _MB_PUBLISHER_READMORE . '</a>';
        }

        $comments = $itemObj->comments();
        if ($options[23] == 1) {
            if ($comments > 0) {
                //shows 1 comment instead of 1 comm. if comments ==1
                //langugage file modified accordingly
                if ($comments == 1) {
                    $item['comment'] = '&nbsp;' . _MB_PUBLISHER_ONECOMMENT . '&nbsp;';
                } else {
                    $item['comment'] = '&nbsp;' . $comments . '&nbsp;' . _MB_PUBLISHER_COMMENTS . '&nbsp;';
                }
            } else {
                $item['comment'] = '&nbsp;' . _MB_PUBLISHER_NO_COMMENTS . '&nbsp;';
            }
        }

        $item['print'] = '';
        if ($options[24] == 1) {
            $item['print'] = '<a href="' . PublisherSeo::generateUrl('print', $itemObj->itemid(), $itemObj->short_url()) . '" rel="nofollow"><img src="' . PUBLISHER_URL . '/assets/images/links/print.gif" title="' . _CO_PUBLISHER_PRINT . '" alt="' . _CO_PUBLISHER_PRINT . '" /></a>&nbsp;';
        }

        $item['pdf'] = '';
        if ($publisher->getConfig('display_pdf')) {
            if ($options[25] == 1) {
                $item['pdf'] = "<a href='" . PUBLISHER_URL . '/makepdf.php?itemid=' . $itemObj->itemid() . "' rel='nofollow'><img src='" . PUBLISHER_URL . "/assets/images/links/pdf.gif' title='" . _CO_PUBLISHER_PDF . "' alt='" . _CO_PUBLISHER_PDF . "' /></a>&nbsp;";
            }
        }
        $item['email'] = '';
        if ($options[26] == 1 && xoops_isActiveModule('tellafriend')) {
            $subject  = sprintf(_CO_PUBLISHER_INTITEMFOUND, $GLOBALS['xoopsConfig']['sitename']);
            $subject  = $itemObj->convertForJapanese($subject);
            $maillink = publisherTellAFriend($subject);

            $item['email'] = '<a href="' . $maillink . '"><img src="' . PUBLISHER_URL . '/assets/images/links/friend.gif" title="' . _CO_PUBLISHER_MAIL . '" alt="' . _CO_PUBLISHER_MAIL . '" /></a>&nbsp;';
        }

        $block['morelink'] = '';
        if ($options[27] == 1) {
            $block['morelink'] = '<a href="' . PUBLISHER_URL . '/index.php">' . _MB_PUBLISHER_MORE_ITEMS . '</a> ';
        }

        $block['latestnews_scroll'] = false;
        if ($options[5] == 1) {
            $block['latestnews_scroll'] = true;
        }

        $block['scrollheight'] = $options[6];
        $block['scrollspeed']  = $options[7];
        $block['scrolldir']    = $options[8];

        $block['template'] = $options[28];

        $block['imgwidth']  = $options[11];
        $block['imgheight'] = $options[12];

        $block['letters'] = $letters;

        $columns[$k][] = $item;
        ++$k;

        if ($k == $columnCount) {
            $k = 0;
        }
    }

    unset($item);
    $block['columns'] = $columns;

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

    $directions = array('right' => _MB_PUBLISHER_SCROLL_RIGHT, 'left' => _MB_PUBLISHER_SCROLL_LEFT, 'up' => _MB_PUBLISHER_SCROLL_UP, 'down' => _MB_PUBLISHER_SCROLL_DOWN);
    foreach ($directions as $key => $value) {
        $form .= "<option value='{$key}'";
        if ($options[8] == $key) {
            $form .= " selected='selected'";
        }
        $form .= ">{$value}</option>";
    }
    $form .= '</select></td></tr>';

    $form .= $tabletag1 . _MB_PUBLISHER_ORDER . $tabletag2;

    $form .= "<select name='options[9]'>";
    $form .= "<option value='datesub'";
    if ($options[9] === 'datesub') {
        $form .= " selected='selected'";
    }
    $form .= '>' . _MB_PUBLISHER_DATE . '</option>';

    $form .= "<option value='counter'";
    if ($options[9] === 'counter') {
        $form .= " selected='selected'";
    }
    $form .= '>' . _MB_PUBLISHER_HITS . '</option>';

    $form .= "<option value='weight'";
    if ($options[9] === 'weight') {
        $form .= " selected='selected'";
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
    if ($options[15] === 'LEFT') {
        $form .= " selected='selected'";
    }
    $form .= '>' . _LEFT . "</option>\n";

    $form .= "<option value='CENTER'";
    if ($options[15] === 'CENTER') {
        $form .= " selected='selected'";
    }
    $form .= '>' . _CENTER . "</option>\n";

    $form .= "<option value='RIGHT'";
    if ($options[15] === 'RIGHT') {
        $form .= " selected='selected'";
    }
    $form .= '>' . _RIGHT . '</option>';
    $form .= '</select></td></tr>';

    $form .= $tabletag3 . _MB_PUBLISHER_LINKSCONFIG . $tabletag4; // Links Options
    $form .= $tabletag1 . _MB_PUBLISHER_DISPLAY_TOPICLINK . $tabletag2;
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

    $form .= $tabletag3 . _MB_PUBLISHER_TEMPLATESCONFIG . $tabletag4; // Templates Options
    $form .= $tabletag1 . _MB_PUBLISHER_TEMPLATE . $tabletag2;
    $form .= "<select size='1' name='options[28]'>";

    $templates = array('normal' => _MB_PUBLISHER_TEMPLATE_NORMAL, 'extended' => _MB_PUBLISHER_TEMPLATE_EXTENDED, 'ticker' => _MB_PUBLISHER_TEMPLATE_TICKER, 'slider1' => _MB_PUBLISHER_TEMPLATE_SLIDER1, 'slider2' => _MB_PUBLISHER_TEMPLATE_SLIDER2);
    foreach ($templates as $key => $value) {
        $form .= "<option value='{$key}'";
        if ($options[28] == $key) {
            $form .= " selected='selected'";
        }
        $form .= ">{$value}</option>";
    }
    $form .= '</select></td></tr>';

    //Select Which Categories To Show
    $form .= $tabletag3 . _MB_PUBLISHER_TOPICSCONFIG . $tabletag4; // Topics Options
    $form .= $tabletag1 . _MB_PUBLISHER_TOPICSDISPLAY . $tabletag2;
    $form .= publisherCreateCategorySelect($options[29], 0, true, 'options[29]');
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
    if ($options[$number] == 1) {
        $chk = " checked='checked'";
    }
    $chkbox = "<input type='radio' name='options[{$number}]' value='1'" . $chk . ' />&nbsp;' . _YES . '&nbsp;&nbsp;';
    $chk    = '';
    if ($options[$number] == 0) {
        $chk = " checked='checked'";
    }
    $chkbox .= "<input type='radio' name='options[{$number}]' value='0'" . $chk . ' />&nbsp;' . _NO . '</td></tr>';

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
    if ($options[$number] == 2) {
        $slc = " checked='checked'";
    }
    $select = "<input type='radio' name='options[{$number}]' value='2'" . $slc . ' />&nbsp;' . _LEFT . '&nbsp;&nbsp;';
    $slc    = '';
    if ($options[$number] == 1) {
        $slc = " checked='checked'";
    }
    $select = "<input type='radio' name='options[{$number}]' value='1'" . $slc . ' />&nbsp;' . _CENTER . '&nbsp;&nbsp;';
    $slc    = '';
    if ($options[$number] == 0) {
        $slc = " checked='checked'";
    }
    $select .= "<input type='radio' name='options[{$number}]' value='0'" . $slc . ' />&nbsp;' . _RIGHT . '</td></tr>';

    return $select;
}
