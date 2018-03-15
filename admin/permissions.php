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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../include/common.php';
require_once $GLOBALS['xoops']->path('class/xoopsform/grouppermform.php');
$myts = \MyTextSanitizer::getInstance();

Publisher\Utility::cpHeader();
//publisher_adminMenu(3, _AM_PUBLISHER_PERMISSIONS);
$helper = Publisher\Helper::getInstance();

// View Categories permissions
$item_list_view = [];
$block_view     = [];
Publisher\Utility::openCollapsableBar('permissionstable_view', 'permissionsicon_view', _AM_PUBLISHER_PERMISSIONSVIEWMAN, _AM_PUBLISHER_VIEW_CATS);

$result_view = $GLOBALS['xoopsDB']->query('SELECT categoryid, name FROM ' . $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_categories') . ' ');
if ($GLOBALS['xoopsDB']->getRowsNum($result_view)) {
    $form_submit = new \XoopsGroupPermForm('', $helper->getModule()->mid(), 'category_read', '', 'admin/permissions.php');
    while (false !== ($myrow_view = $GLOBALS['xoopsDB']->fetcharray($result_view))) {
        $form_submit->addItem($myrow_view['categoryid'], $myts->displayTarea($myrow_view['name']));
    }
    echo $form_submit->render();
} else {
    echo _AM_PUBLISHER_NOPERMSSET;
}
Publisher\Utility::closeCollapsableBar('permissionstable_view', 'permissionsicon_view');

// Submit Categories permissions
echo "<br>\n";
Publisher\Utility::openCollapsableBar('permissionstable_submit', 'permissionsicon_submit', _AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT, _AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT_DSC);
$result_view = $GLOBALS['xoopsDB']->query('SELECT categoryid, name FROM ' . $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_categories') . ' ');
if ($GLOBALS['xoopsDB']->getRowsNum($result_view)) {
    $form_submit = new \XoopsGroupPermForm('', $helper->getModule()->mid(), 'item_submit', '', 'admin/permissions.php');
    while (false !== ($myrow_view = $GLOBALS['xoopsDB']->fetcharray($result_view))) {
        $form_submit->addItem($myrow_view['categoryid'], $myts->displayTarea($myrow_view['name']));
    }
    echo $form_submit->render();
} else {
    echo _AM_PUBLISHER_NOPERMSSET;
}
Publisher\Utility::closeCollapsableBar('permissionstable_submit', 'permissionsicon_submit');

// Moderators Categories permissions
echo "<br>\n";
Publisher\Utility::openCollapsableBar('permissionstable_moderation', 'permissionsicon_moderation', _AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR, _AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR_DSC);
$result_view = $GLOBALS['xoopsDB']->query('SELECT categoryid, name FROM ' . $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_categories') . ' ');
if ($GLOBALS['xoopsDB']->getRowsNum($result_view)) {
    $form_submit = new \XoopsGroupPermForm('', $helper->getModule()->mid(), 'category_moderation', '', 'admin/permissions.php');
    while (false !== ($myrow_view = $GLOBALS['xoopsDB']->fetcharray($result_view))) {
        $form_submit->addItem($myrow_view['categoryid'], $myts->displayTarea($myrow_view['name']));
    }
    echo $form_submit->render();
} else {
    echo _AM_PUBLISHER_NOPERMSSET;
}
Publisher\Utility::closeCollapsableBar('permissionstable_moderation', 'permissionsicon_moderation');

// Form permissions
echo "<br>\n";
Publisher\Utility::openCollapsableBar('permissionstable_form', 'permissionsicon_form', _AM_PUBLISHER_PERMISSIONS_FORM, _AM_PUBLISHER_PERMISSIONS_FORM_DSC);
$form_options = [
    Constants::PUBLISHER_SUMMARY               => _AM_PUBLISHER_SUMMARY,
    //Constants::PUBLISHER_DISPLAY_SUMMARY        => _CO_PUBLISHER_DISPLAY_SUMMARY,
    Constants::PUBLISHER_AVAILABLE_PAGE_WRAP   => _CO_PUBLISHER_AVAILABLE_PAGE_WRAP,
    Constants::PUBLISHER_ITEM_TAG              => _AM_PUBLISHER_ITEM_TAG,
    Constants::PUBLISHER_IMAGE_ITEM            => _AM_PUBLISHER_IMAGE_ITEM,
    //_PUBLISHER_IMAGE_UPLOAD           => _AM_PUBLISHER_IMAGE_UPLOAD,
    Constants::PUBLISHER_ITEM_UPLOAD_FILE      => _CO_PUBLISHER_ITEM_UPLOAD_FILE,
    Constants::PUBLISHER_UID                   => _CO_PUBLISHER_UID,
    Constants::PUBLISHER_DATESUB               => _CO_PUBLISHER_DATESUB,
    Constants::PUBLISHER_STATUS                => _CO_PUBLISHER_STATUS,
    Constants::PUBLISHER_ITEM_SHORT_URL        => _CO_PUBLISHER_ITEM_SHORT_URL,
    Constants::PUBLISHER_ITEM_META_KEYWORDS    => _CO_PUBLISHER_ITEM_META_KEYWORDS,
    Constants::PUBLISHER_ITEM_META_DESCRIPTION => _CO_PUBLISHER_ITEM_META_DESCRIPTION,
    Constants::PUBLISHER_WEIGHT                => _CO_PUBLISHER_WEIGHT,
    Constants::PUBLISHER_ALLOWCOMMENTS         => _CO_PUBLISHER_ALLOWCOMMENTS,
    //Constants::PUBLISHER_PERMISSIONS_ITEM => _CO_PUBLISHER_PERMISSIONS_ITEM,
    //Constants::PUBLISHER_PERMISSIONS_ITEM_DSC => _CO_PUBLISHER_PERMISSIONS_ITEM_DSC,
    // Constants::PUBLISHER_PARTIAL_VIEW => _CO_PUBLISHER_PARTIAL_VIEW,
    Constants::PUBLISHER_DOHTML                => _CO_PUBLISHER_DOHTML,
    Constants::PUBLISHER_DOSMILEY              => _CO_PUBLISHER_DOSMILEY,
    Constants::PUBLISHER_DOXCODE               => _CO_PUBLISHER_DOXCODE,
    Constants::PUBLISHER_DOIMAGE               => _CO_PUBLISHER_DOIMAGE,
    Constants::PUBLISHER_DOLINEBREAK           => _CO_PUBLISHER_DOLINEBREAK,
    Constants::PUBLISHER_NOTIFY                => _AM_PUBLISHER_NOTIFY,
    Constants::PUBLISHER_SUBTITLE              => _CO_PUBLISHER_SUBTITLE,
    Constants::PUBLISHER_AUTHOR_ALIAS          => _CO_PUBLISHER_AUTHOR_ALIAS
];
$form_submit  = new \XoopsGroupPermForm('', $helper->getModule()->mid(), 'form_view', '', 'admin/permissions.php');
foreach ($form_options as $key => $value) {
    $form_submit->addItem($key, $value);
}
unset($key, $value);
echo $form_submit->render();
Publisher\Utility::closeCollapsableBar('permissionstable_form', 'permissionsicon_form');

// Editors permissions
echo "<br>\n";
Publisher\Utility::openCollapsableBar('permissionstable_editors', 'permissions_editors', _AM_PUBLISHER_PERMISSIONS_EDITORS, _AM_PUBLISHER_PERMISSIONS_EDITORS_DSC);
$editors     = Publisher\Utility::getEditors();
$form_submit = new \XoopsGroupPermForm('', $helper->getModule()->mid(), 'editors', '', 'admin/permissions.php');
foreach ($editors as $key => $value) {
    $form_submit->addItem($key, $value['title']);
}
unset($key, $value);
echo $form_submit->render();
Publisher\Utility::closeCollapsableBar('permissionstable_editors', 'permissionsicon_editors');

// Global permissions
echo "<br>\n";
Publisher\Utility::openCollapsableBar('permissionstable_global', 'permissionsicon_global', _AM_PUBLISHER_PERMISSIONS_GLOBAL, _AM_PUBLISHER_PERMISSIONS_GLOBAL_DSC);
$form_options = [
    Constants::PUBLISHER_SEARCH => _AM_PUBLISHER_SEARCH,
    Constants::PUBLISHER_RATE   => _AM_PUBLISHER_RATE
];
$form_submit  = new \XoopsGroupPermForm('', $helper->getModule()->mid(), 'global', '', 'admin/permissions.php');
foreach ($form_options as $key => $value) {
    $form_submit->addItem($key, $value);
}
unset($key, $value);
echo $form_submit->render();
Publisher\Utility::closeCollapsableBar('permissionstable_global', 'permissionsicon_global');

require_once __DIR__ . '/admin_footer.php';
