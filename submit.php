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
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/header.php';
$helper->loadLanguage('admin');

// Get the total number of categories
$categoriesArray = $helper->getHandler('Category')->getCategoriesForSubmit();

if (!$categoriesArray) {
    redirect_header('index.php', 1, _MD_PUBLISHER_NEED_CATEGORY_ITEM);
    //    exit();
}

$groups       = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
/* @var $gpermHandler XoopsGroupPermHandler */
$gpermHandler = \XoopsModules\Publisher\Helper::getInstance()->getHandler('Groupperm'); //xoops_getModuleHandler('groupperm');
$moduleId     = $helper->getModule()->getVar('mid');

$itemId = Request::getInt('itemid', Request::getInt('itemid', 0, 'POST'), 'GET');
if (0 != $itemId) {
    // We are editing or deleting an article
    /* @var  $itemObj Item */
    $itemObj = $helper->getHandler('Item')->get($itemId);
    if (!(Publisher\Utility::userIsAdmin() || Publisher\Utility::userIsAuthor($itemObj) || Publisher\Utility::userIsModerator($itemObj))) {
        redirect_header('index.php', 1, _NOPERM);
        //        exit();
    }
    if (!Publisher\Utility::userIsAdmin() || !Publisher\Utility::userIsModerator($itemObj)) {
        if ('del' === Request::getString('op', '', 'GET') && !$helper->getConfig('perm_delete')) {
            redirect_header('index.php', 1, _NOPERM);
        //            exit();
        } elseif (!$helper->getConfig('perm_edit')) {
            redirect_header('index.php', 1, _NOPERM);
            //            exit();
        }
    }
    /* @var  $categoryObj Publisher\Category */
    $categoryObj = $itemObj->getCategory();
} else {
    // we are submitting a new article
    // if the user is not admin AND we don't allow user submission, exit
    if (!(Publisher\Utility::userIsAdmin() || (1 == $helper->getConfig('perm_submit') && (is_object($GLOBALS['xoopsUser']) || (1 == $helper->getConfig('perm_anon_submit')))))) {
        redirect_header('index.php', 1, _NOPERM);
        //        exit();
    }
    /* @var  $itemObj Item */
    $itemObj     = $helper->getHandler('Item')->create();
    /* @var  $categoryObj Publisher\Category */
    $categoryObj = $helper->getHandler('Category')->create();
}

if ('clone' === Request::getString('op', '', 'GET')) {
    $formtitle = _MD_PUBLISHER_SUB_CLONE;
    $itemObj->setNew();
    $itemObj->setVar('itemid', 0);
} else {
    $formtitle = _MD_PUBLISHER_SUB_SMNAME;
}

//$op = '';
$op = 'add';
if (Request::getString('additem', '', 'POST')) {
    $op = 'post';
} elseif (Request::getString('preview', '', 'POST')) {
    $op = 'preview';
}

$tokenError = false;
if ('POST' === Request::getMethod() && !$GLOBALS['xoopsSecurity']->check()) {
    if ('preview' !== $op) {
        $op = 'preview';
        $tokenError = true;
    }
}

$op = Request::getString('op', Request::getString('op', $op, 'POST'), 'GET');

$allowedEditors = Publisher\Utility::getEditors($gpermHandler->getItemIds('editors', $groups, $moduleId));
$formView       = $gpermHandler->getItemIds('form_view', $groups, $moduleId);

// This code makes sure permissions are not manipulated
$elements = [
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
    'author_alias'
];
foreach ($elements as $element) {
    $classname = '\XoopsModules\Publisher\Constants';
    if (Request::hasVar($element, 'POST') && !in_array(constant($classname .'::'. 'PUBLISHER_' . strtoupper($element)), $formView)) {
        redirect_header('index.php', 1, _MD_PUBLISHER_SUBMIT_ERROR);
        //        exit();
    }
}
//unset($element);

$itemUploadFile = Request::getArray('item_upload_file', [], 'FILES');

//stripcslashes
switch ($op) {
    case 'del':
        $confirm = Request::getInt('confirm', '', 'POST');

        if ($confirm) {
            if (!$helper->getHandler('Item')->delete($itemObj)) {
                redirect_header('index.php', 2, _AM_PUBLISHER_ITEM_DELETE_ERROR . Publisher\Utility::formatErrors($itemObj->getErrors()));
                //                exit();
            }
            redirect_header('index.php', 2, sprintf(_AM_PUBLISHER_ITEMISDELETED, $itemObj->getTitle()));
        //            exit();
        } else {
            require_once $GLOBALS['xoops']->path('header.php');
            xoops_confirm(
                ['op' => 'del', 'itemid' => $itemObj->itemid(), 'confirm' => 1, 'name' => $itemObj->getTitle()],
                'submit.php',
                          _AM_PUBLISHER_DELETETHISITEM . " <br>'" . $itemObj->getTitle() . "'. <br> <br>",
                _AM_PUBLISHER_DELETE
            );
            require_once $GLOBALS['xoops']->path('footer.php');
        }
        exit();
        break;
    case 'preview':
        // Putting the values about the ITEM in the ITEM object
        $itemObj->setVarsFromRequest();

        $GLOBALS['xoopsOption']['template_main'] = 'publisher_submit.tpl';
        require_once $GLOBALS['xoops']->path('header.php');
        $xoTheme->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
        $xoTheme->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
        require_once PUBLISHER_ROOT_PATH . '/footer.php';

        $categoryObj = $helper->getHandler('Category')->get(Request::getInt('categoryid', 0, 'POST'));

        $item                 = $itemObj->toArraySimple();
        $item['summary']      = $itemObj->body();
        $item['categoryPath'] = $categoryObj->getCategoryPath(true);
        $item['who_when']     = $itemObj->getWhoAndWhen();
        $item['comments']     = -1;
        $xoopsTpl->assign('item', $item);

        $xoopsTpl->assign('op', 'preview');
        $xoopsTpl->assign('module_home', Publisher\Utility::moduleHome());

        if ($itemId) {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroTitle', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroText', '');
        } else {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_SUB_SNEWNAME);
            $xoopsTpl->assign('langIntroTitle', sprintf(_MD_PUBLISHER_SUB_SNEWNAME, ucwords($helper->getModule()->name())));
            $xoopsTpl->assign('langIntroText', $helper->getConfig('submit_intro_msg'));
        }
        if ($tokenError) {
            $xoopsTpl->assign('langIntroText', _CO_PUBLISHER_BAD_TOKEN);
        }

        $sform = $itemObj->getForm($formtitle, true);
        $sform->assign($xoopsTpl);
        require_once $GLOBALS['xoops']->path('footer.php');
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
        if ($itemUploadFile && '' != $itemUploadFile['name']) {
            $fileUploadResult = Publisher\Utility::uploadFile(false, true, $itemObj);
            if (true !== $fileUploadResult) {
                redirect_header('javascript:history.go(-1)', 3, $fileUploadResult);
            }
        }

        // if autoapprove_submitted. This does not apply if we are editing an article
        if (!$itemId) {
            if ($itemObj->getVar('status') == Constants::PUBLISHER_STATUS_PUBLISHED /*$helper->getConfig('perm_autoapprove'] ==  1*/) {
                // We do not not subscribe user to notification on publish since we publish it right away

                // Send notifications
                $itemObj->sendNotifications([Constants::PUBLISHER_NOTIFY_ITEM_PUBLISHED]);

                $redirect_msg = _MD_PUBLISHER_ITEM_RECEIVED_AND_PUBLISHED;
                redirect_header($itemObj->getItemUrl(), 2, $redirect_msg);
            } else {
                // Subscribe the user to On Published notification, if requested
                if ($itemObj->getVar('notifypub')) {
                    require_once $GLOBALS['xoops']->path('include/notification_constants.php');
                    $notificationHandler = xoops_getHandler('notification');
                    $notificationHandler->subscribe('item', $itemObj->itemid(), 'approved', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
                }
                // Send notifications
                $itemObj->sendNotifications([Constants::PUBLISHER_NOTIFY_ITEM_SUBMITTED]);

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
        $GLOBALS['xoopsOption']['template_main'] = 'publisher_submit.tpl';
        require_once $GLOBALS['xoops']->path('header.php');
        $GLOBALS['xoTheme']->addScript(XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js');
        $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/publisher.js');
        require_once PUBLISHER_ROOT_PATH . '/footer.php';

        //mb        $itemObj->setVarsFromRequest();

        $xoopsTpl->assign('module_home', Publisher\Utility::moduleHome());
        if ('clone' === Request::getString('op', '', 'GET')) {
            $xoopsTpl->assign('categoryPath', _CO_PUBLISHER_CLONE);
            $xoopsTpl->assign('langIntroTitle', _CO_PUBLISHER_CLONE);
        } elseif ($itemId) {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroTitle', _MD_PUBLISHER_EDIT_ARTICLE);
            $xoopsTpl->assign('langIntroText', '');
        } else {
            $xoopsTpl->assign('categoryPath', _MD_PUBLISHER_SUB_SNEWNAME);
            $xoopsTpl->assign('langIntroTitle', sprintf(_MD_PUBLISHER_SUB_SNEWNAME, ucwords($helper->getModule()->name())));
            $xoopsTpl->assign('langIntroText', $helper->getConfig('submit_intro_msg'));
        }
        $sform = $itemObj->getForm($formtitle, true);
        $sform->assign($xoopsTpl);

        require_once $GLOBALS['xoops']->path('footer.php');
        break;
}
