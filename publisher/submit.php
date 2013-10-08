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
 * @version         $Id: submit.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__FILE__) . '/header.php';
xoops_loadLanguage('admin', PUBLISHER_DIRNAME);

// Get the total number of categories
$categoriesArray = $publisher->getHandler('category')->getCategoriesForSubmit();

if (!$categoriesArray) {
    redirect_header("index.php", 1, _MD_PUBLISHER_NEED_CATEGORY_ITEM);
    exit();
}

$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
$gperm_handler = xoops_getmodulehandler('groupperm');
$module_id = $publisher->getModule()->getVar('mid');

$itemid = PublisherRequest::getInt('itemid');
if ($itemid != 0) {
    // We are editing or deleting an article
    $itemObj = $publisher->getHandler('item')->get($itemid);
    if (!(publisher_userIsAdmin() || publisher_userIsAuthor($itemObj) || publisher_userIsModerator($itemObj))) {
        redirect_header("index.php", 1, _NOPERM);
        exit();
    }
    if (!publisher_userIsAdmin() || !publisher_userIsModerator($itemObj)) {
        if (isset($_GET['op']) && $_GET['op']  == 'del' && !$publisher->getConfig('perm_delete')) {
            redirect_header("index.php", 1, _NOPERM);
            exit();
        } else if (!$publisher->getConfig('perm_edit')) {
            redirect_header("index.php", 1, _NOPERM);
            exit();
        }
    }

    $categoryObj = $itemObj->category();
} else {
    // we are submitting a new article
    // if the user is not admin AND we don't allow user submission, exit
    if (!(publisher_userIsAdmin() || ($publisher->getConfig('perm_submit') == 1 && (is_object($xoopsUser) || ($publisher->getConfig('perm_anon_submit') == 1))))) {
        redirect_header("index.php", 1, _NOPERM);
        exit();
    }
    $itemObj = $publisher->getHandler('item')->create();
    $categoryObj = $publisher->getHandler('category')->create();
}

if (isset($_GET['op']) && $_GET['op'] == 'clone') {
    $formtitle = _MD_PUBLISHER_SUB_CLONE;
    $itemObj->setNew();
    $itemObj->setVar('itemid', 0);
} else {
    $formtitle = _MD_PUBLISHER_SUB_SMNAME;
}

$op = '';
if (isset($_POST['additem'])) {
    $op = 'post';
} elseif (isset($_POST['preview'])) {
    $op = 'preview';
} else {
    $op = 'add';
}

if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'del') {
    $op = 'del';
}

$allowed_editors = publisher_getEditors($gperm_handler->getItemIds('editors', $groups, $module_id));
$form_view = $gperm_handler->getItemIds('form_view', $groups, $module_id);

// This code makes sure permissions are not manipulated
$elements = array(
    'summary', 'available_page_wrap', 'item_tag', 'image_item',
    'item_upload_file', 'uid', 'datesub', 'status', 'item_short_url',
    'item_meta_keywords', 'item_meta_description', 'weight',
    'allowcomments',
    'dohtml', 'dosmiley', 'doxcode', 'doimage', 'dolinebreak',
    'notify', 'subtitle', 'author_alias');
foreach ($elements as $element) {
    if (isset($_REQUEST[$element]) && !in_array(constant('_PUBLISHER_' . strtoupper($element)), $form_view)) {
        redirect_header("index.php", 1, _MD_PUBLISHER_SUBMIT_ERROR);
        exit();
    }
}

$item_upload_file = isset($_FILES['item_upload_file']) ? $_FILES['item_upload_file'] : '';

//stripcslashes
switch ($op) {
    case 'del':
        $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : 0;

        if ($confirm) {
            if (!$publisher->getHandler('item')->delete($itemObj)) {
                redirect_header("index.php", 2, _AM_PUBLISHER_ITEM_DELETE_ERROR . publisher_formatErrors($itemObj->getErrors()));
                exit();
            }
            redirect_header("index.php", 2, sprintf(_AM_PUBLISHER_ITEMISDELETED, $itemObj->title()));
            exit();
        } else {
            include_once XOOPS_ROOT_PATH . '/header.php';
            xoops_confirm(array('op' => 'del', 'itemid' => $itemObj->itemid(), 'confirm' => 1, 'name' => $itemObj->title()), 'submit.php', _AM_PUBLISHER_DELETETHISITEM . " <br />'" . $itemObj->title() . "'. <br /> <br />", _AM_PUBLISHER_DELETE);
            include_once XOOPS_ROOT_PATH . '/footer.php';
        }
        exit();
        break;
    case 'preview':
        // Putting the values about the ITEM in the ITEM object
        $itemObj->setVarsFromRequest();

        $xoopsOption['template_main'] = 'publisher_submit.html';
        include_once XOOPS_ROOT_PATH . '/header.php';
        $xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
        $xoTheme->addScript(PUBLISHER_URL . '/js/publisher.js');
        include_once PUBLISHER_ROOT_PATH . '/footer.php';

        $categoryObj = $publisher->getHandler('category')->get($_POST['categoryid']);

        $item = $itemObj->ToArraySimple();
        $item['summary'] = $itemObj->body();
        $item['categoryPath'] = $categoryObj->getCategoryPath(true);
        $item['who_when'] = $itemObj->getWhoAndWhen();
        $item['comments'] = -1;
        $xoopsTpl->assign('item', $item);

        $xoopsTpl->assign('op', 'preview');
        $xoopsTpl->assign('module_home', publisher_moduleHome());

        if ($itemid) {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('lang_intro_title', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('lang_intro_text', '');
        } else {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_SUB_SNEWNAME);
            $xoopsTpl->assign('lang_intro_title', sprintf(_MD_PUBLISHER_SUB_SNEWNAME, ucwords($publisher->getModule()->name())));
            $xoopsTpl->assign('lang_intro_text', $publisher->getConfig('submit_intro_msg'));
        }

        $sform = $itemObj->getForm($formtitle, true);
        $sform->assign($xoopsTpl);
        include_once XOOPS_ROOT_PATH . '/footer.php';
        exit();

        break;

    case 'post':
        // Putting the values about the ITEM in the ITEM object
       // print_r($itemObj->getVars());
        $itemObj->setVarsFromRequest();
        //print_r($_POST);
       //print_r($itemObj->getVars());
        //exit;

        // Storing the item object in the database
        if (!$itemObj->store()) {
            redirect_header("javascript:history.go(-1)", 2, _MD_PUBLISHER_SUBMIT_ERROR);
            exit();
        }

        // attach file if any
        if ($item_upload_file && $item_upload_file['name'] != "") {
            $file_upload_result = publisher_uploadFile(false, false, $itemObj);
            if ($file_upload_result !== true) {
                redirect_header("javascript:history.go(-1)", 3, $file_upload_result);
                exit;
            }
        }

        // if autoapprove_submitted. This does not apply if we are editing an article
        if (!$itemid) {
            if ($itemObj->getVar('status') == _PUBLISHER_STATUS_PUBLISHED /*$publisher->getConfig('perm_autoapprove'] ==  1*/) {
                // We do not not subscribe user to notification on publish since we publish it right away

                // Send notifications
                $itemObj->sendNotifications(array(_PUBLISHER_NOT_ITEM_PUBLISHED));

                $redirect_msg = _MD_PUBLISHER_ITEM_RECEIVED_AND_PUBLISHED;
                redirect_header($itemObj->getItemUrl(), 2, $redirect_msg);
            } else {
                // Subscribe the user to On Published notification, if requested
                if ($itemObj->getVar('notifypub')) {
                    include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
                    $notification_handler = xoops_gethandler('notification');
                    $notification_handler->subscribe('item', $itemObj->itemid(), 'approved', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
                }
                // Send notifications
                $itemObj->sendNotifications(array(_PUBLISHER_NOT_ITEM_SUBMITTED));

                $redirect_msg = _MD_PUBLISHER_ITEM_RECEIVED_NEED_APPROVAL;
            }
        } else {
            $redirect_msg = _MD_PUBLISHER_ITEMMODIFIED;
            redirect_header($itemObj->getItemUrl(), 2, $redirect_msg);
        }
        redirect_header("index.php", 2, $redirect_msg);
        exit();

        break;

    case 'add':
    default:
        $xoopsOption['template_main'] = 'publisher_submit.html';
        include_once XOOPS_ROOT_PATH . '/header.php';
        $xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
        $xoTheme->addScript(PUBLISHER_URL . '/js/publisher.js');
        include_once PUBLISHER_ROOT_PATH . '/footer.php';

        $itemObj->setVarsFromRequest();

        $xoopsTpl->assign('module_home', publisher_moduleHome());
        if (isset($_GET['op']) && $_GET['op'] == 'clone') {
            $xoopsTpl->assign('categoryPath', _CO_PUBLISHER_CLONE);
            $xoopsTpl->assign('lang_intro_title', _CO_PUBLISHER_CLONE);
        } else if ($itemid) {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('lang_intro_title', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('lang_intro_text', '');
        } else {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_SUB_SNEWNAME);
            $xoopsTpl->assign('lang_intro_title', sprintf(_MD_PUBLISHER_SUB_SNEWNAME, ucwords($publisher->getModule()->name())));
            $xoopsTpl->assign('lang_intro_text', $publisher->getConfig('submit_intro_msg'));
        }
        $sform = $itemObj->getForm($formtitle, true);
        $sform->assign($xoopsTpl);

        include_once XOOPS_ROOT_PATH . '/footer.php';
        break;
}

?>