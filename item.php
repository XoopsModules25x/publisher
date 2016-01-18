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
 * @subpackage      Action
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: item.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/header.php';

$itemId     = XoopsRequest::getInt('itemid', 0, 'GET');
$itemPageId = XoopsRequest::getInt('page', -1, 'GET');

if ($itemId == 0) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

// Creating the item object for the selected item
$itemObj = $publisher->getHandler('item')->get($itemId);

// if the selected item was not found, exit
if (!$itemObj) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

$xoopsOption['template_main'] = 'publisher_item.tpl';
include_once $GLOBALS['xoops']->path('header.php');

$xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
//$xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery-migrate-1.2.1.js');

$xoTheme->addScript(PUBLISHER_URL . '/assets/js/jquery.popeye-2.1.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/jquery.popeye-2.0.4.js');
$xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');

$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.css');
$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');

include_once PUBLISHER_ROOT_PATH . '/footer.php';

// Creating the category object that holds the selected item
$categoryObj = $publisher->getHandler('category')->get($itemObj->categoryid());

// Check user permissions to access that category of the selected item
if (!$itemObj->accessGranted()) {
    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    //    exit;
}

// Update the read counter of the selected item
if (!$GLOBALS['xoopsUser'] || ($GLOBALS['xoopsUser'] && !$GLOBALS['xoopsUser']->isAdmin($publisher->getModule()->mid())) || ($GLOBALS['xoopsUser']->isAdmin($publisher->getModule()->mid()) && $publisher->getConfig('item_admin_hits') == 1)) {
    $itemObj->updateCounter();
}

// creating the Item objects that belong to the selected category
switch ($publisher->getConfig('format_order_by')) {
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

if ('previous_next' === $publisher->getConfig('item_other_items_type')) {
    // Retrieving the next and previous object
    $previousItemLink = '';
    $previousItemUrl  = '';
    $nextItemLink     = '';
    $nextItemUrl      = '';

    $previousObj = $publisher->getHandler('item')->getPreviousPublished($itemObj);
    $nextObj     = $publisher->getHandler('item')->getNextPublished($itemObj);
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
if ($publisher->getConfig('item_other_items_type') === 'all') {
    $itemsObj = $publisher->getHandler('item')->getAllPublished(0, 0, $categoryObj->categoryid(), $sort, $order, '', true, true);
    $items    = array();
    foreach ($itemsObj as $theItemObj) {
        $theItem              = array();
        $theItem['titlelink'] = $theItemObj->getItemLink();
        $theItem['datesub']   = $theItemObj->getDatesub();
        $theItem['counter']   = $theItemObj->counter();
        if ($theItemObj->itemId() == $itemObj->itemId()) {
            $theItem['titlelink'] = $theItemObj->getTitle();
        }
        $items[] = $theItem;
        unset($theItem);
    }
    unset($itemsObj, $theItemObj);
    $xoopsTpl->assign('items', $items);
    unset($items);
}

// Populating the smarty variables with information related to the selected item
$item = $itemObj->toArraySimple($itemPageId);
$xoopsTpl->assign('show_subtitle', $publisher->getConfig('item_disp_subtitle'));

if ($itemObj->pagescount() > 0) {
    if ($itemPageId == -1) {
        $itemPageId = 0;
    }
    include_once $GLOBALS['xoops']->path('class/pagenav.php');
    $pagenav = new XoopsPageNav($itemObj->pagescount(), 1, $itemPageId, 'page', 'itemid=' . $itemObj->itemId());
    $xoopsTpl->assign('pagenav', $pagenav->renderNav());
}

// Creating the files object associated with this item
$file         = array();
$files        = array();
$embededFiles = array();
$filesObj     = $itemObj->getFiles();

// check if user has permission to modify files
$hasFilePermissions = true;
if (!(publisherUserIsAdmin() || publisherUserIsModerator($itemObj))) {
    $hasFilePermissions = false;
}
if (null !== $filesObj) {
    foreach ($filesObj as $fileObj) {
        $file        = array();
        $file['mod'] = false;
        if ($hasFilePermissions || (is_object($GLOBALS['xoopsUser']) && $fileObj->getVar('uid') == $GLOBALS['xoopsUser']->getVar('uid'))) {
            $file['mod'] = true;
        }

        if ($fileObj->mimetype() === 'application/x-shockwave-flash') {
            $file['content'] = $fileObj->displayFlash();
            if (strpos($item['maintext'], '[flash-' . $fileObj->getVar('fileid') . ']')) {
                $item['maintext'] = str_replace('[flash-' . $fileObj->getVar('fileid') . ']', $file['content'], $item['maintext']);
            } else {
                $embededFiles[] = $file;
            }
        } else {
            $file['fileid']      = $fileObj->fileid();
            $file['name']        = $fileObj->name();
            $file['description'] = $fileObj->description();
            $file['name']        = $fileObj->name();
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
$xoopsTpl->assign('itemid', $itemObj->itemId());
$xoopsTpl->assign('sectionname', $publisher->getModule()->getVar('name'));
$xoopsTpl->assign('module_dirname', $publisher->getModule()->getVar('dirname'));
$xoopsTpl->assign('module_home', publisherModuleHome($publisher->getConfig('format_linked_path')));
$xoopsTpl->assign('categoryPath', $item['categoryPath'] . ' > ' . $item['title']);
$xoopsTpl->assign('commentatarticlelevel', $publisher->getConfig('perm_com_art_level'));
$xoopsTpl->assign('com_rule', $publisher->getConfig('com_rule'));
$xoopsTpl->assign('other_items', $publisher->getConfig('item_other_items_type'));
$xoopsTpl->assign('itemfooter', $myts->displayTarea($publisher->getConfig('item_footer'), 1));
$xoopsTpl->assign('perm_author_items', $publisher->getConfig('perm_author_items'));

// tags support
if (xoops_isActiveModule('tag')) {
    include_once $GLOBALS['xoops']->path('modules/tag/include/tagbar.php');
    $xoopsTpl->assign('tagbar', tagbar($itemId, $catid = 0));
}

/**
 * Generating meta information for this page
 */
$publisherMetagen = new PublisherMetagen($itemObj->getVar('title'), $itemObj->getVar('meta_keywords', 'n'), $itemObj->getVar('meta_description', 'n'), $itemObj->getCategoryPath());
$publisherMetagen->createMetaTags();

// Include the comments if the selected ITEM supports comments
if (($publisher->getConfig('com_rule') <> 0) && (($itemObj->cancomment() == 1) || !$publisher->getConfig('perm_com_art_level'))) {
    include_once $GLOBALS['xoops']->path('include/comment_view.php');
    // Problem with url_rewrite and posting comments :
    $xoopsTpl->assign(array(
                          'editcomment_link'   => PUBLISHER_URL . '/comment_edit.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
                          'deletecomment_link' => PUBLISHER_URL . '/comment_delete.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
                          'replycomment_link'  => PUBLISHER_URL . '/comment_reply.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra));
    $xoopsTpl->_tpl_vars['commentsnav'] = str_replace("self.location.href='", "self.location.href='" . PUBLISHER_URL . '/', $xoopsTpl->_tpl_vars['commentsnav']);
}

// Include support for AJAX rating
if ($publisher->getConfig('perm_rating')) {
    $xoopsTpl->assign('rating_enabled', true);
    $item['ratingbar'] = publisherRatingBar($itemId);
    $xoTheme->addScript(PUBLISHER_URL . '/assets/js/behavior.js');
    $xoTheme->addScript(PUBLISHER_URL . '/assets/js/rating.js');
}

$xoopsTpl->assign('item', $item);
include_once $GLOBALS['xoops']->path('footer.php');
