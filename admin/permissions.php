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
 * @version         $Id: permissions.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';
include_once $GLOBALS['xoops']->path('class/xoopsform/grouppermform.php');
$myts = MyTextSanitizer::getInstance();

publisherCpHeader();
//publisher_adminMenu(3, _AM_PUBLISHER_PERMISSIONS);

// View Categories permissions
$item_list_view = array();
$block_view     = array();
publisherOpenCollapsableBar('permissionstable_view', 'permissionsicon_view', _AM_PUBLISHER_PERMISSIONSVIEWMAN, _AM_PUBLISHER_VIEW_CATS);

$result_view = $GLOBALS['xoopsDB']->query('SELECT categoryid, name FROM ' . $GLOBALS['xoopsDB']->prefix('publisher_categories') . ' ');
if ($GLOBALS['xoopsDB']->getRowsNum($result_view)) {
    $form_submit = new XoopsGroupPermForm('', $publisher->getModule()->mid(), 'category_read', '', 'admin/permissions.php');
    while (($myrow_view = $GLOBALS['xoopsDB']->fetcharray($result_view)) !== false) {
        $form_submit->addItem($myrow_view['categoryid'], $myts->displayTarea($myrow_view['name']));
    }
    echo $form_submit->render();
} else {
    echo _AM_PUBLISHER_NOPERMSSET;
}
publisherCloseCollapsableBar('permissionstable_view', 'permissionsicon_view');

// Submit Categories permissions
echo "<br />\n";
publisherOpenCollapsableBar('permissionstable_submit', 'permissionsicon_submit', _AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT, _AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT_DSC);
$result_view = $GLOBALS['xoopsDB']->query('SELECT categoryid, name FROM ' . $GLOBALS['xoopsDB']->prefix('publisher_categories') . ' ');
if ($GLOBALS['xoopsDB']->getRowsNum($result_view)) {
    $form_submit = new XoopsGroupPermForm('', $publisher->getModule()->mid(), 'item_submit', '', 'admin/permissions.php');
    while (($myrow_view = $GLOBALS['xoopsDB']->fetcharray($result_view)) !== false) {
        $form_submit->addItem($myrow_view['categoryid'], $myts->displayTarea($myrow_view['name']));
    }
    echo $form_submit->render();
} else {
    echo _AM_PUBLISHER_NOPERMSSET;
}
publisherCloseCollapsableBar('permissionstable_submit', 'permissionsicon_submit');

// Moderators Categories permissions
echo "<br />\n";
publisherOpenCollapsableBar('permissionstable_moderation', 'permissionsicon_moderation', _AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR, _AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR_DSC);
$result_view = $GLOBALS['xoopsDB']->query('SELECT categoryid, name FROM ' . $GLOBALS['xoopsDB']->prefix('publisher_categories') . ' ');
if ($GLOBALS['xoopsDB']->getRowsNum($result_view)) {
    $form_submit = new XoopsGroupPermForm('', $publisher->getModule()->mid(), 'category_moderation', '', 'admin/permissions.php');
    while (($myrow_view = $GLOBALS['xoopsDB']->fetcharray($result_view)) !== false) {
        $form_submit->addItem($myrow_view['categoryid'], $myts->displayTarea($myrow_view['name']));
    }
    echo $form_submit->render();
} else {
    echo _AM_PUBLISHER_NOPERMSSET;
}
publisherCloseCollapsableBar('permissionstable_moderation', 'permissionsicon_moderation');

// Form permissions
echo "<br />\n";
publisherOpenCollapsableBar('permissionstable_form', 'permissionsicon_form', _AM_PUBLISHER_PERMISSIONS_FORM, _AM_PUBLISHER_PERMISSIONS_FORM_DSC);
$form_options = array(
    PublisherConstants::PUBLISHER_SUMMARY               => _AM_PUBLISHER_SUMMARY,
    //PublisherConstants::PUBLISHER_DISPLAY_SUMMARY        => _CO_PUBLISHER_DISPLAY_SUMMARY,
    PublisherConstants::PUBLISHER_AVAILABLE_PAGE_WRAP   => _CO_PUBLISHER_AVAILABLE_PAGE_WRAP,
    PublisherConstants::PUBLISHER_ITEM_TAG              => _AM_PUBLISHER_ITEM_TAG,
    PublisherConstants::PUBLISHER_IMAGE_ITEM            => _AM_PUBLISHER_IMAGE_ITEM,
    //_PUBLISHER_IMAGE_UPLOAD           => _AM_PUBLISHER_IMAGE_UPLOAD,
    PublisherConstants::PUBLISHER_ITEM_UPLOAD_FILE      => _CO_PUBLISHER_ITEM_UPLOAD_FILE,
    PublisherConstants::PUBLISHER_UID                   => _CO_PUBLISHER_UID,
    PublisherConstants::PUBLISHER_DATESUB               => _CO_PUBLISHER_DATESUB,
    PublisherConstants::PUBLISHER_STATUS                => _CO_PUBLISHER_STATUS,
    PublisherConstants::PUBLISHER_ITEM_SHORT_URL        => _CO_PUBLISHER_ITEM_SHORT_URL,
    PublisherConstants::PUBLISHER_ITEM_META_KEYWORDS    => _CO_PUBLISHER_ITEM_META_KEYWORDS,
    PublisherConstants::PUBLISHER_ITEM_META_DESCRIPTION => _CO_PUBLISHER_ITEM_META_DESCRIPTION,
    PublisherConstants::PUBLISHER_WEIGHT                => _CO_PUBLISHER_WEIGHT,
    PublisherConstants::PUBLISHER_ALLOWCOMMENTS         => _CO_PUBLISHER_ALLOWCOMMENTS,
    //PublisherConstants::PUBLISHER_PERMISSIONS_ITEM => _CO_PUBLISHER_PERMISSIONS_ITEM,
    // PublisherConstants::PUBLISHER_PARTIAL_VIEW => _CO_PUBLISHER_PARTIAL_VIEW,
    PublisherConstants::PUBLISHER_DOHTML                => _CO_PUBLISHER_DOHTML,
    PublisherConstants::PUBLISHER_DOSMILEY              => _CO_PUBLISHER_DOSMILEY,
    PublisherConstants::PUBLISHER_DOXCODE               => _CO_PUBLISHER_DOXCODE,
    PublisherConstants::PUBLISHER_DOIMAGE               => _CO_PUBLISHER_DOIMAGE,
    PublisherConstants::PUBLISHER_DOLINEBREAK           => _CO_PUBLISHER_DOLINEBREAK,
    PublisherConstants::PUBLISHER_NOTIFY                => _AM_PUBLISHER_NOTIFY,
    PublisherConstants::PUBLISHER_SUBTITLE              => _CO_PUBLISHER_SUBTITLE,
    PublisherConstants::PUBLISHER_AUTHOR_ALIAS          => _CO_PUBLISHER_AUTHOR_ALIAS);
$form_submit  = new XoopsGroupPermForm('', $publisher->getModule()->mid(), 'form_view', '', 'admin/permissions.php');
foreach ($form_options as $key => $value) {
    $form_submit->addItem($key, $value);
}
unset($key, $value);
echo $form_submit->render();
publisherCloseCollapsableBar('permissionstable_form', 'permissionsicon_form');

// Editors permissions
echo "<br />\n";
publisherOpenCollapsableBar('permissionstable_editors', 'permissions_editors', _AM_PUBLISHER_PERMISSIONS_EDITORS, _AM_PUBLISHER_PERMISSIONS_EDITORS_DSC);
$editors     = publisherGetEditors();
$form_submit = new XoopsGroupPermForm('', $publisher->getModule()->mid(), 'editors', '', 'admin/permissions.php');
foreach ($editors as $key => $value) {
    $form_submit->addItem($key, $value['title']);
}
unset($key, $value);
echo $form_submit->render();
publisherCloseCollapsableBar('permissionstable_editors', 'permissionsicon_editors');

// Global permissions
echo "<br />\n";
publisherOpenCollapsableBar('permissionstable_global', 'permissionsicon_global', _AM_PUBLISHER_PERMISSIONS_GLOBAL, _AM_PUBLISHER_PERMISSIONS_GLOBAL_DSC);
$form_options = array(
    PublisherConstants::PUBLISHER_SEARCH => _AM_PUBLISHER_SEARCH,
    PublisherConstants::PUBLISHER_RATE   => _AM_PUBLISHER_RATE);
$form_submit  = new XoopsGroupPermForm('', $publisher->getModule()->mid(), 'global', '', 'admin/permissions.php');
foreach ($form_options as $key => $value) {
    $form_submit->addItem($key, $value);
}
unset($key, $value);
echo $form_submit->render();
publisherCloseCollapsableBar('permissionstable_global', 'permissionsicon_global');

include_once __DIR__ . '/admin_footer.php';
