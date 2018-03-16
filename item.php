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
 */

use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/header.php';

$itemId     = Request::getInt('itemid', 0, 'GET');
$itemPageId = Request::getInt('page', -1, 'GET');

if (0 == $itemId) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

// Creating the item object for the selected item
$itemObj = $helper->getHandler('Item')->get($itemId);

// if the selected item was not found, exit
if (!$itemObj) {
    redirect_header('javascript:history.go(-1)', 1, _MD_PUBLISHER_NOITEMSELECTED);
    //    exit();
}

$GLOBALS['xoopsOption']['template_main'] = 'publisher_item.tpl';
require_once $GLOBALS['xoops']->path('header.php');

//$xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/jquery.popeye-2.1.js');
//$xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
//
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.css');
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
//$xoTheme->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');

require_once PUBLISHER_ROOT_PATH . '/footer.php';

// Creating the category object that holds the selected item
$categoryObj = $helper->getHandler('Category')->get($itemObj->categoryid());

// Check user permissions to access that category of the selected item
if (!$itemObj->accessGranted()) {
    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
    //    exit;
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
    $itemsObj = $helper->getHandler('Item')->getAllPublished(0, 0, $categoryObj->categoryid(), $sort, $order, '', true, true);
    $items    = [];
    foreach ($itemsObj[''] as $theItemObj) {
        $theItem              = [];
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
$xoopsTpl->assign('show_subtitle', $helper->getConfig('item_disp_subtitle'));

if ($itemObj->pagescount() > 0) {
    if ($itemPageId == -1) {
        $itemPageId = 0;
    }
    require_once $GLOBALS['xoops']->path('class/pagenav.php');
    //    $pagenav = new \XoopsPageNav($itemObj->pagescount(), 1, $itemPageId, 'page', 'itemid=' . $itemObj->itemId());

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
if (!(Publisher\Utility::userIsAdmin() || Publisher\Utility::userIsModerator($itemObj))) {
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
$xoopsTpl->assign('sectionname', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('module_dirname', $helper->getDirname());
$xoopsTpl->assign('module_home', Publisher\Utility::moduleHome($helper->getConfig('format_linked_path')));
$xoopsTpl->assign('categoryPath', '<li>' . $item['categoryPath'] . '</li><li> ' . $item['title'] . '</li>');
$xoopsTpl->assign('commentatarticlelevel', $helper->getConfig('perm_com_art_level'));
$xoopsTpl->assign('com_rule', $helper->getConfig('com_rule'));
$xoopsTpl->assign('other_items', $helper->getConfig('item_other_items_type'));
$xoopsTpl->assign('itemfooter', $myts->displayTarea($helper->getConfig('item_footer'), 1));
$xoopsTpl->assign('perm_author_items', $helper->getConfig('perm_author_items'));

// tags support
if (xoops_isActiveModule('tag')) {
    require_once $GLOBALS['xoops']->path('modules/tag/include/tagbar.php');
    $xoopsTpl->assign('tagbar', tagBar($itemId, $catid = 0));
}

/**
 * Generating meta information for this page
 */
$publisherMetagen = new Publisher\Metagen($itemObj->getVar('title'), $itemObj->getVar('meta_keywords', 'n'), $itemObj->getVar('meta_description', 'n'), $itemObj->getCategoryPath());
$publisherMetagen->createMetaTags();

// Include the comments if the selected ITEM supports comments
if ((0 <> $helper->getConfig('com_rule')) && ((1 == $itemObj->cancomment()) || !$helper->getConfig('perm_com_art_level'))) {
    require_once $GLOBALS['xoops']->path('include/comment_view.php');
    // Problem with url_rewrite and posting comments :
    $xoopsTpl->assign([
                          'editcomment_link'   => PUBLISHER_URL . '/comment_edit.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
                          'deletecomment_link' => PUBLISHER_URL . '/comment_delete.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra,
                          'replycomment_link'  => PUBLISHER_URL . '/comment_reply.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . $link_extra
                      ]);
    $xoopsTpl->_tpl_vars['commentsnav'] = str_replace("self.location.href='", "self.location.href='" . PUBLISHER_URL . '/', $xoopsTpl->_tpl_vars['commentsnav']);
}

// Include support for AJAX rating
if ($helper->getConfig('perm_rating')) {
    $xoopsTpl->assign('rating_enabled', true);
    $item['ratingbar'] = Publisher\Utility::ratingBar($itemId);
    $xoTheme->addScript(PUBLISHER_URL . '/assets/js/behavior.js');
    $xoTheme->addScript(PUBLISHER_URL . '/assets/js/rating.js');
}

$xoopsTpl->assign('item', $item);
require_once $GLOBALS['xoops']->path('footer.php');
?>
<!--<script type="text/javascript">-->
<!--    $(document).ready(function () {-->
<!--        $("img").addClass("img-responsive");-->
<!--    });-->
<!--</script>-->
