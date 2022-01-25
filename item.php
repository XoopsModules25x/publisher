<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         https://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher\Category;
use XoopsModules\Publisher\Constants;
use XoopsModules\Publisher\Helper;
use XoopsModules\Publisher\Item;
use XoopsModules\Publisher\Jsonld;
use XoopsModules\Publisher\Metagen;
use XoopsModules\Publisher\Utility;
use XoopsModules\Tag\Tagbar;

/** @var Category $categoryObj */
require_once __DIR__ . '/header.php';

$itemId     = Request::getInt('itemid', 0, 'GET');
$itemPageId = Request::getInt('page', -1, 'GET');

if (0 == $itemId) {
    //    redirect_header('<script>javascript:history.go(-1)</script>', 1, _MD_PUBLISHER_NOITEMSELECTED);
}

$helper = Helper::getInstance();

// Creating the item object for the selected item
/** @var Item $itemObj */
$itemObj = $helper->getHandler('Item')->get($itemId);

// if the selected item was not found, exit
if (null === $itemObj) {
    //    redirect_header('<script>javascript:history.go(-1)</script>', 1, _MD_PUBLISHER_NOITEMSELECTED);
}

// Creating the category object that holds the selected item
$categoryObj = $helper->getHandler('Category')->get($itemObj->categoryid());

$categoryid = (int)$categoryObj->getVar('categoryid');

$GLOBALS['xoopsOption']['template_main'] = 'publisher_item.tpl'; //default template

//Option for a custom template for a category
$catItemTemplate =  $categoryObj->getVar('template_item');
if (!empty($catItemTemplate)){
    $GLOBALS['xoopsOption']['template_main'] = 'publisher_category_item_custom.tpl' ;
}

require_once $GLOBALS['xoops']->path('header.php');

//$xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/jquery.popeye-2.1.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
//
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.css');
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');
$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/rating.css');

$xoopsTpl->assign('customitemtemplate', $catItemTemplate); //assign custom template


require_once PUBLISHER_ROOT_PATH . '/footer.php';

// Check user permissions to access that category of the selected item
if (!$itemObj->accessGranted()) {
    redirect_header('<script>javascript:history.go(-1)</script>', 1, _NOPERM);
}
$com_replytitle = $itemObj->getTitle();

// Update the read counter of the selected item
if (!$GLOBALS['xoopsUser']
    || ($GLOBALS['xoopsUser'] && !$GLOBALS['xoopsUser']->isAdmin($helper->getModule()->mid()))
    || ($GLOBALS['xoopsUser']->isAdmin($helper->getModule()->mid()) && 1 == $helper->getConfig('item_admin_hits'))) {
    $itemObj->updateCounter();
}

// creating the Item objects that belong to the selected category
switch ($helper->getConfig('format_order_by')) {
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

if ('previous_next' === $helper->getConfig('item_other_items_type')) {
    // Retrieving the next and previous object
    $previousItemLink = '';
    $previousItemUrl  = '';
    $nextItemLink     = '';
    $nextItemUrl      = '';

    $previousObj = $helper->getHandler('Item')->getPreviousPublished($itemObj);
    $nextObj     = $helper->getHandler('Item')->getNextPublished($itemObj);
    if (is_object($previousObj)) {
        $previousItemLink = $previousObj->getItemLink();
        $previousItemUrl  = $previousObj->getItemUrl();
    }

    if (is_object($nextObj)) {
        $nextItemLink = $nextObj->getItemLink();
        $nextItemUrl  = $nextObj->getItemUrl();
    }
    unset($previousObj, $nextObj);
    $xoopsTpl->assign('previousItemLink', $previousItemLink);
    $xoopsTpl->assign('nextItemLink', $nextItemLink);
    $xoopsTpl->assign('previousItemUrl', $previousItemUrl);
    $xoopsTpl->assign('nextItemUrl', $nextItemUrl);
}

//CAREFUL!! with many items this will exhaust memory
if ('all' === $helper->getConfig('item_other_items_type')) {
    $itemsObj = $helper->getHandler('Item')->getAllPublished(0, 0, $categoryObj->categoryid, $sort, $order, '', true, true);
    $items    = [];
    foreach ($itemsObj[''] as $theItemObj) {
        $theItem              = [];
        $theItem['body']      = $theItemObj->getBody();
        $theItem['title']     = $theItemObj->getTitle();
        $theItem['titlelink'] = $theItemObj->getItemLink();
        $theItem['itemid']    = $theItemObj->itemid();
        $theItem['itemurl']   = $theItemObj->getItemUrl();
        $theItem['datesub']   = $theItemObj->getDatesub();
        $theItem['counter']   = $theItemObj->counter();
        $theItem['who']       = $theItemObj->getWho();
        $theItem['category']  = $theItemObj->getCategoryLink();
        $theItem['more']      = '<a href="' . $theItemObj->getItemUrl() . '">' . _MD_PUBLISHER_READMORE . '</a>';

        $summary = $theItemObj->getSummary(300);
        if (!$summary) {
            $summary = $theItemObj->getBody(300);
        }
        $theItem['summary'] = $summary;

        $theItem['cancomment'] = $theItemObj->cancomment();
        $comments              = $theItemObj->comments();
        if ($comments > 0) {
            //shows 1 comment instead of 1 comm. if comments ==1
            //langugage file modified accordingly
            if (1 == $comments) {
                $theItem['comments'] = '&nbsp;' . _MD_PUBLISHER_ONECOMMENT . '&nbsp;';
            } else {
                $theItem['comments'] = '&nbsp;' . $comments . '&nbsp;' . _MD_PUBLISHER_COMMENTS . '&nbsp;';
            }
        } else {
            $theItem['comments'] = '&nbsp;' . _MD_PUBLISHER_NO_COMMENTS . '&nbsp;';
        }

        $mainImage = $theItemObj->getMainImage();
        // check to see if GD function exist
        $theItem['item_image'] = $mainImage['image_path'];
        if (!empty($mainImage['image_path']) && function_exists('imagecreatetruecolor')) {
            $theItem['item_image'] = PUBLISHER_URL . '/thumb.php?src=' . $mainImage['image_path'] . '&amp;w=100';
            $theItem['image_path'] = $mainImage['image_path'];
        }

        if ($theItemObj->itemid == $itemObj->itemid()) {
            $theItem['titlelink'] = $theItemObj->getItemLink();
        }
        $items[] = $theItem;
        unset($theItem);
    }
    unset($itemsObj);
    $xoopsTpl->assign('items', $items);
    unset($items);
}

// Populating the smarty variables with information related to the selected item
$item = $itemObj->toArraySimple($itemPageId);
$xoopsTpl->assign('show_subtitle', $helper->getConfig('item_disp_subtitle'));

if ($itemObj->pagescount() > 0) {
    if (-1 == $itemPageId) {
        $itemPageId = 0;
    }
    require_once $GLOBALS['xoops']->path('class/pagenav.php');
    //    $pagenav = new \XoopsPageNav($itemObj->pagescount(), 1, $itemPageId, 'page', 'itemid=' . $itemObj->itemid());

    $pagenav = new \XoopsPageNav($itemObj->pagescount(), 1, $itemPageId, 'page', 'itemid=' . $itemObj->itemid()); //SMEDrieben changed ->itemId to ->itemid

    $xoopsTpl->assign('pagenav', $pagenav->renderNav());
}

// Creating the files object associated with this item
$file         = [];
$files        = [];
$embededFiles = [];
$filesObj     = $itemObj->getFiles();

// check if user has permission to modify files
$hasFilePermissions = true;
if (!(Utility::userIsAdmin() || Utility::userIsModerator($itemObj))) {
    $hasFilePermissions = false;
}
if (null !== $filesObj) {
    foreach ($filesObj as $fileObj) {
        $file        = [];
        $file['mod'] = false;
        if ($hasFilePermissions || (is_object($GLOBALS['xoopsUser']) && $fileObj->getVar('uid') == $GLOBALS['xoopsUser']->getVar('uid'))) {
            $file['mod'] = true;
        }

        if ('application/x-shockwave-flash' === $fileObj->mimetype()) {
            $file['content'] = $fileObj->displayFlash();
            if (mb_strpos($item['maintext'], '[flash-' . $fileObj->getVar('fileid') . ']')) {
                $item['maintext'] = str_replace('[flash-' . $fileObj->getVar('fileid') . ']', $file['content'], $item['maintext']);
            } else {
                $embededFiles[] = $file;
            }
        } else {
            $file['fileid']      = $fileObj->fileid();
            $file['name']        = $fileObj->name();
            $file['description'] = $fileObj->description();
            $file['filename']    = $fileObj->filename();
            $file['type']        = $fileObj->mimetype();
            $file['datesub']     = $fileObj->getDatesub();
            $file['hits']        = $fileObj->counter();
            $files[]             = $file;
        }
    }
}

$item['files']         = $files;
$item['embeded_files'] = $embededFiles;
unset($file, $embededFiles, $filesObj, $fileObj);

// Language constants
$xoopsTpl->assign('mail_link', 'mailto:?subject=' . sprintf(_CO_PUBLISHER_INTITEM, $GLOBALS['xoopsConfig']['sitename']) . '&amp;body=' . sprintf(_CO_PUBLISHER_INTITEMFOUND, $GLOBALS['xoopsConfig']['sitename']) . ': ' . $itemObj->getItemUrl());
$xoopsTpl->assign('itemid', $itemObj->itemid());
$xoopsTpl->assign('sectionname', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('module_dirname', $helper->getDirname());
$xoopsTpl->assign('module_home', Utility::moduleHome($helper->getConfig('format_linked_path')));
$xoopsTpl->assign('categoryPath', '<li>' . $item['categoryPath'] . '</li><li> ' . $item['title'] . '</li>');
$xoopsTpl->assign('commentatarticlelevel', $helper->getConfig('perm_com_art_level'));
$xoopsTpl->assign('com_rule', $helper->getConfig('com_rule'));
$xoopsTpl->assign('other_items', $helper->getConfig('item_other_items_type'));
$xoopsTpl->assign('itemfooter', $myts->displayTarea($helper->getConfig('item_footer'), 1));
$xoopsTpl->assign('perm_author_items', $helper->getConfig('perm_author_items'));

// tags support
if (xoops_isActiveModule('tag')) {
    $tagbar = new Tagbar();
    $xoopsTpl->assign('tagbar', $tagbar->getTagbar($itemId, $categoryid = 0));
}

/**
 * Generating meta information for this page
 */
$publisherMetagen = new Metagen($itemObj->getVar('title'), $itemObj->getVar('meta_keywords', 'n'), $itemObj->getVar('meta_description', 'n'), $itemObj->getCategoryPath());
$publisherMetagen->createMetaTags();


// generate JSON-LD and add to page
if ($helper->getConfig('generate_jsonld')) {
    $jsonld = Jsonld::getItem($itemObj, $categoryObj);
    echo $jsonld;
}

// Include the comments if the selected ITEM supports comments
if ((0 != $helper->getConfig('com_rule')) && ((1 == $itemObj->cancomment()) || !$helper->getConfig('perm_com_art_level'))) {
    require_once $GLOBALS['xoops']->path('include/comment_view.php');
    // Problem with url_rewrite and posting comments :
    //    $xoopsTpl->assign(
    //        [
    //            'editcomment_link'   => PUBLISHER_URL . '/comment_edit.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
    //            'deletecomment_link' => PUBLISHER_URL . '/comment_delete.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
    //            'replycomment_link'  => PUBLISHER_URL . '/comment_reply.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
    //        ]
    //    );
    $xoopsTpl->_tpl_vars['commentsnav'] = str_replace(
        "self.location.href='",
        "self.location.href='" . PUBLISHER_URL . '/',
        $xoopsTpl->_tpl_vars['commentsnav'] ?? ''
    );
}

// Original AJAX rating
if ($helper->getConfig('perm_rating')) {
    $xoopsTpl->assign('rating_enabled', true);
    $item['ratingbar'] = Utility::ratingBar($itemId);

    //    $xoTheme->addScript(PUBLISHER_URL . '/assets/js/behavior.js');
    //    $xoTheme->addScript(PUBLISHER_URL . '/assets/js/rating.js');
    //}

    //=============== START VOTE RATING ======================================

    $start = Request::getInt('start', 0);
    $limit = Request::getInt('limit', $helper->getConfig('userpager'));
    $id    = Request::getInt('itemid', 0, 'GET');

    //    $ratingbars = (int)$helper->getConfig('ratingbars'); //from Preferences

    $voteType = $itemObj->votetype();

    if ($voteType > 0) {
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/rating.css', null);
        $GLOBALS['xoopsTpl']->assign('rating', $voteType);
        $GLOBALS['xoopsTpl']->assign('rating_5stars', (Constants::RATING_5STARS === $voteType));
        $GLOBALS['xoopsTpl']->assign('rating_10stars', (Constants::RATING_10STARS === $voteType));
        $GLOBALS['xoopsTpl']->assign('rating_10num', (Constants::RATING_10NUM === $voteType));
        $GLOBALS['xoopsTpl']->assign('rating_likes', (Constants::RATING_LIKES === $voteType));
        $GLOBALS['xoopsTpl']->assign('rating_reaction', (Constants::RATING_REACTION === $voteType));
        $GLOBALS['xoopsTpl']->assign('itemid', 'itemid');
        $GLOBALS['xoopsTpl']->assign('blog_icon_url_16', PUBLISHER_URL . '/' . $modPathIcon16);
    }

    /** @var VoteHandler $voteHandler */
    $voteHandler = $helper->getHandler('Vote');

    $rating5 = $voteHandler->getItemRating5($itemObj, Constants::TABLE_ARTICLE);
    $xoopsTpl->assign('rating', $rating5);
    $item['rating'] = $rating5;

    //    $GLOBALS['xoopsTpl']->assign('article', $article);
    //        $xoopsTpl->assign('article', $article);
    $xoopsTpl->assign('item2', $item);
    //        $xoopsTpl->assign('rating', $rating);
    //        unset($article);
    //    }

    $GLOBALS['xoopsTpl']->assign('type', $helper->getConfig('table_type'));
    $GLOBALS['xoopsTpl']->assign('divideby', $helper->getConfig('divideby'));
    $GLOBALS['xoopsTpl']->assign('numb_col', $helper->getConfig('numb_col'));
}

//=================== END VOTE RATING =========================================

//$xoopsTpl->assign('article', $article);
$xoopsTpl->assign('item', $item);
$GLOBALS['xoopsTpl']->assign('mod_path', $helper->path());
require_once XOOPS_ROOT_PATH . '/footer.php';
