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

include_once __DIR__ . '/header.php';
xoops_loadLanguage('admin', PUBLISHER_DIRNAME);

// Get the total number of categories
$categoriesArray = $publisher->getHandler('category')->getCategoriesForSubmit();

if (!$categoriesArray) {
    redirect_header('index.php', 1, _MD_PUBLISHER_NEED_CATEGORY_ITEM);
    //    exit();
}

$groups       = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
$gpermHandler = xoops_getModuleHandler('groupperm');
$moduleId     = $publisher->getModule()->getVar('mid');

$itemId = XoopsRequest::getInt('itemid', XoopsRequest::getInt('itemid', 0, 'POST'), 'GET');
if ($itemId != 0) {
    // We are editing or deleting an article
    $itemObj = $publisher->getHandler('item')->get($itemId);
    if (!(publisherUserIsAdmin() || publisherUserIsAuthor($itemObj) || publisherUserIsModerator($itemObj))) {
        redirect_header('index.php', 1, _NOPERM);
        //        exit();
    }
    if (!publisherUserIsAdmin() || !publisherUserIsModerator($itemObj)) {
        if ('del' === XoopsRequest::getString('op', '', 'GET') && !$publisher->getConfig('perm_delete')) {
            redirect_header('index.php', 1, _NOPERM);
            //            exit();
        } elseif (!$publisher->getConfig('perm_edit')) {
            redirect_header('index.php', 1, _NOPERM);
            //            exit();
        }
    }

    $categoryObj = $itemObj->getCategory();
} else {
    // we are submitting a new article
    // if the user is not admin AND we don't allow user submission, exit
    if (!(publisherUserIsAdmin() || ($publisher->getConfig('perm_submit') == 1 && (is_object($GLOBALS['xoopsUser']) || ($publisher->getConfig('perm_anon_submit') == 1))))) {
        redirect_header('index.php', 1, _NOPERM);
        //        exit();
    }
    $itemObj     = $publisher->getHandler('item')->create();
    $categoryObj = $publisher->getHandler('category')->create();
}

if ('clone' === XoopsRequest::getString('op', '', 'GET')) {
    $formtitle = _MD_PUBLISHER_SUB_CLONE;
    $itemObj->setNew();
    $itemObj->setVar('itemid', 0);
} else {
    $formtitle = _MD_PUBLISHER_SUB_SMNAME;
}

//$op = '';
$op = 'add';
if (XoopsRequest::getString('additem', '', 'POST')) {
    $op = 'post';
} elseif (XoopsRequest::getString('preview', '', 'POST')) {
    $op = 'preview';
}

$op = XoopsRequest::getString('op', XoopsRequest::getString('op', $op, 'POST'), 'GET');

$allowedEditors = publisherGetEditors($gpermHandler->getItemIds('editors', $groups, $moduleId));
$formView       = $gpermHandler->getItemIds('form_view', $groups, $moduleId);

// This code makes sure permissions are not manipulated
$elements = array(
    'summary',
    'available_page_wrap',
    'item_tag',
    'image_item',
    'item_upload_file',
    'uid',
    'datesub',
    'status',
    'item_short_url',
    'item_meta_keywords',
    'item_meta_description',
    'weight',
    'allowcomments',
    'dohtml',
    'dosmiley',
    'doxcode',
    'doimage',
    'dolinebreak',
    'notify',
    'subtitle',
    'author_alias');
foreach ($elements as $element) {
    if (XoopsRequest::getString('element', '', 'POST') && !in_array(constant('PublisherConstants::PUBLISHER_' . strtoupper($element)), $formView)) {
        redirect_header('index.php', 1, _MD_PUBLISHER_SUBMIT_ERROR);
        //        exit();
    }
}
//unset($element);

$itemUploadFile = XoopsRequest::getArray('item_upload_file', array(), 'FILES');

//stripcslashes
switch ($op) {
    case 'del':
        $confirm = XoopsRequest::getInt('confirm', '', 'POST');

        if ($confirm) {
            if (!$publisher->getHandler('item')->delete($itemObj)) {
                redirect_header('index.php', 2, _AM_PUBLISHER_ITEM_DELETE_ERROR . publisherFormatErrors($itemObj->getErrors()));
                //                exit();
            }
            redirect_header('index.php', 2, sprintf(_AM_PUBLISHER_ITEMISDELETED, $itemObj->getTitle()));
            //            exit();
        } else {
            include_once $GLOBALS['xoops']->path('header.php');
            xoops_confirm(array('op' => 'del', 'itemid' => $itemObj->itemid(), 'confirm' => 1, 'name' => $itemObj->getTitle()), 'submit.php', _AM_PUBLISHER_DELETETHISITEM . " <br />'" . $itemObj->getTitle() . "'. <br /> <br />", _AM_PUBLISHER_DELETE);
            include_once $GLOBALS['xoops']->path('footer.php');
        }
        exit();
        break;
    case 'preview':
        // Putting the values about the ITEM in the ITEM object
        $itemObj->setVarsFromRequest();

        $xoopsOption['template_main'] = 'publisher_submit.tpl';
        include_once $GLOBALS['xoops']->path('header.php');
        $xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
        $xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
        include_once PUBLISHER_ROOT_PATH . '/footer.php';

        $categoryObj = $publisher->getHandler('category')->get(XoopsRequest::getInt('categoryid', 0, 'POST'));

        $item                 = $itemObj->toArraySimple();
        $item['summary']      = $itemObj->body();
        $item['categoryPath'] = $categoryObj->getCategoryPath(true);
        $item['who_when']     = $itemObj->getWhoAndWhen();
        $item['comments']     = -1;
        $xoopsTpl->assign('item', $item);

        $xoopsTpl->assign('op', 'preview');
        $xoopsTpl->assign('module_home', publisherModuleHome());

        if ($itemId) {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroTitle', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroText', '');
        } else {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_SUB_SNEWNAME);
            $xoopsTpl->assign('langIntroTitle', sprintf(_MD_PUBLISHER_SUB_SNEWNAME, ucwords($publisher->getModule()->name())));
            $xoopsTpl->assign('langIntroText', $publisher->getConfig('submit_intro_msg'));
        }

        $sform = $itemObj->getForm($formtitle, true);
        $sform->assign($xoopsTpl);
        include_once $GLOBALS['xoops']->path('footer.php');
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
            redirect_header('javascript:history.go(-1)', 2, _MD_PUBLISHER_SUBMIT_ERROR);
            //            exit();
        }

        // attach file if any
        if ($itemUploadFile && $itemUploadFile['name'] != '') {
            $fileUploadResult = publisherUploadFile(false, true, $itemObj);
            if ($fileUploadResult !== true) {
                redirect_header('javascript:history.go(-1)', 3, $fileUploadResult);
                exit;
            }
        }

        // if autoapprove_submitted. This does not apply if we are editing an article
        if (!$itemId) {
            if ($itemObj->getVar('status') == PublisherConstants::PUBLISHER_STATUS_PUBLISHED /*$publisher->getConfig('perm_autoapprove'] ==  1*/) {
                // We do not not subscribe user to notification on publish since we publish it right away

                // Send notifications
                $itemObj->sendNotifications(array(PublisherConstants::PUBLISHER_NOTIFY_ITEM_PUBLISHED));

                $redirect_msg = _MD_PUBLISHER_ITEM_RECEIVED_AND_PUBLISHED;
                redirect_header($itemObj->getItemUrl(), 2, $redirect_msg);
            } else {
                // Subscribe the user to On Published notification, if requested
                if ($itemObj->getVar('notifypub')) {
                    include_once $GLOBALS['xoops']->path('include/notification_constants.php');
                    $notificationHandler = xoops_getHandler('notification');
                    $notificationHandler->subscribe('item', $itemObj->itemid(), 'approved', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
                }
                // Send notifications
                $itemObj->sendNotifications(array(PublisherConstants::PUBLISHER_NOTIFY_ITEM_SUBMITTED));

                $redirect_msg = _MD_PUBLISHER_ITEM_RECEIVED_NEED_APPROVAL;
            }
        } else {
            $redirect_msg = _MD_PUBLISHER_ITEMMODIFIED;
            redirect_header($itemObj->getItemUrl(), 2, $redirect_msg);
        }
        redirect_header('index.php', 2, $redirect_msg);
        //        exit();

        break;

    case 'add':
    default:
        $xoopsOption['template_main'] = 'publisher_submit.tpl';
        include_once $GLOBALS['xoops']->path('header.php');
        $GLOBALS['xoTheme']->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
        $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
        include_once PUBLISHER_ROOT_PATH . '/footer.php';

        //mb        $itemObj->setVarsFromRequest();

        $xoopsTpl->assign('module_home', publisherModuleHome());
        if ('clone' === XoopsRequest::getString('op', '', 'GET')) {
            $xoopsTpl->assign('categoryPath', _CO_PUBLISHER_CLONE);
            $xoopsTpl->assign('langIntroTitle', _CO_PUBLISHER_CLONE);
        } elseif ($itemId) {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroTitle', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroText', '');
        } else {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_SUB_SNEWNAME);
            $xoopsTpl->assign('langIntroTitle', sprintf(_MD_PUBLISHER_SUB_SNEWNAME, ucwords($publisher->getModule()->name())));
            $xoopsTpl->assign('langIntroText', $publisher->getConfig('submit_intro_msg'));
        }
        $sform = $itemObj->getForm($formtitle, true);
        $sform->assign($xoopsTpl);

        include_once $GLOBALS['xoops']->path('footer.php');
        break;
}
