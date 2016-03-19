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
 * @version         $Id: category.php 10661 2013-01-04 19:22:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';

$op = XoopsRequest::getString('op', XoopsRequest::getString('op', '', 'POST'), 'GET');

$op = XoopsRequest::getString('editor', '', 'POST') ? 'mod' : $op;
$op = XoopsRequest::getString('addcategory', '', 'POST') ? 'addcategory' : $op;

// Where do we start ?
$startcategory = XoopsRequest::getInt('startcategory', 0, 'GET');
$categoryid    = XoopsRequest::getInt('categoryid');

switch ($op) {
    case 'del':
        $categoryObj = $publisher->getHandler('category')->get($categoryid);
        $confirm     = XoopsRequest::getInt('confirm', '', 'POST');
        $name        = XoopsRequest::getString('name', '', 'POST');
        if ($confirm) {
            if (!$publisher->getHandler('category')->delete($categoryObj)) {
                redirect_header('category.php', 1, _AM_PUBLISHER_DELETE_CAT_ERROR);
                //                exit();
            }
            redirect_header('category.php', 1, sprintf(_AM_PUBLISHER_COLISDELETED, $name));
            //            exit();
        } else {
            xoops_cp_header();
            xoops_confirm(array('op' => 'del', 'categoryid' => $categoryObj->categoryid(), 'confirm' => 1, 'name' => $categoryObj->name()), 'category.php', _AM_PUBLISHER_DELETECOL . " '" . $categoryObj->name() . "'. <br /> <br />" . _AM_PUBLISHER_DELETE_CAT_CONFIRM, _AM_PUBLISHER_DELETE);
            xoops_cp_footer();
        }
        break;

    case 'mod':
        //Added by fx2024
        $nb_subcats = XoopsRequest::getInt('nb_subcats', 0, 'POST');
        $nb_subcats += XoopsRequest::getInt('nb_sub_yet', 4, 'POST');
        //end of fx2024 code

        publisherCpHeader();
        PublisherUtilities::editCategory(true, $categoryid, $nb_subcats);
        break;

    case 'addcategory':
        global $modify;

        $parentid = XoopsRequest::getInt('parentid');

        if ($categoryid != 0) {
            $categoryObj = $publisher->getHandler('category')->get($categoryid);
        } else {
            $categoryObj = $publisher->getHandler('category')->create();
        }

        // Uploading the image, if any
        // Retreive the filename to be uploaded
        $temp = XoopsRequest::getArray('image_file', '', 'FILES');
        if ($image_file = $temp['name']) {
            //            $filename = XoopsRequest::getArray('xoops_upload_file', array(), 'POST')[0];
            $temp2 = XoopsRequest::getArray('xoops_upload_file', array(), 'POST');
            if ($filename = $temp2[0]) {
                // TODO : implement publisher mimetype management
                $max_size          = $publisher->getConfig('maximum_filesize');
                $max_imgwidth      = $publisher->getConfig('maximum_image_width');
                $max_imgheight     = $publisher->getConfig('maximum_image_height');
                $allowed_mimetypes = publisherGetAllowedImagesTypes();
                if (($temp['tmp_name'] == '') || !is_readable($temp['tmp_name'])) {
                    redirect_header('javascript:history.go(-1)', 2, _AM_PUBLISHER_FILEUPLOAD_ERROR);
                    //                    exit();
                }

                xoops_load('XoopsMediaUploader');
                $uploader = new XoopsMediaUploader(publisherGetImageDir('category'), $allowed_mimetypes, $max_size, $max_imgwidth, $max_imgheight);
                if ($uploader->fetchMedia($filename) && $uploader->upload()) {
                    $categoryObj->setVar('image', $uploader->getSavedFileName());
                } else {
                    redirect_header('javascript:history.go(-1)', 2, _AM_PUBLISHER_FILEUPLOAD_ERROR . $uploader->getErrors());
                    //                    exit();
                }
            }
        } else {
            $categoryObj->setVar('image', XoopsRequest::getString('image', '', 'POST'));
        }
        $categoryObj->setVar('parentid', XoopsRequest::getInt('parentid', 0, 'POST'));

        $applyall = XoopsRequest::getInt('applyall', 0, 'POST');
        $categoryObj->setVar('weight', XoopsRequest::getInt('weight', 1, 'POST'));

        // Groups and permissions
        $grpread       = XoopsRequest::getArray('groupsRead', array(), 'POST');
        $grpsubmit     = XoopsRequest::getArray('groupsSubmit', array(), 'POST');
        $grpmoderation = XoopsRequest::getArray('groupsModeration', array(), 'POST');

        $categoryObj->setVar('name', XoopsRequest::getString('name', '', 'POST'));

        //Added by skalpa: custom template support
        $categoryObj->setVar('template', XoopsRequest::getString('template', '', 'POST'));
        $categoryObj->setVar('meta_description', XoopsRequest::getString('meta_description', '', 'POST'));
        $categoryObj->setVar('meta_keywords', XoopsRequest::getString('meta_keywords', '', 'POST'));
        $categoryObj->setVar('short_url', XoopsRequest::getString('short_url', '', 'POST'));
        $categoryObj->setVar('moderator', XoopsRequest::getInt('moderator', 0, 'POST'));
        $categoryObj->setVar('description', XoopsRequest::getString('description', '', 'POST'));
        $categoryObj->setVar('header', XoopsRequest::getString('header', '', 'POST'));

        if ($categoryObj->isNew()) {
            $redirect_msg = _AM_PUBLISHER_CATCREATED;
            $redirect_to  = 'category.php?op=mod';
        } else {
            $redirect_msg = _AM_PUBLISHER_COLMODIFIED;
            $redirect_to  = 'category.php';
        }

        if (!$categoryObj->store()) {
            redirect_header('javascript:history.go(-1)', 3, _AM_PUBLISHER_CATEGORY_SAVE_ERROR . publisherFormatErrors($categoryObj->getErrors()));
            //            exit;
        }
        // TODO : put this function in the category class
        publisherSaveCategoryPermissions($grpread, $categoryObj->categoryid(), 'category_read');
        publisherSaveCategoryPermissions($grpsubmit, $categoryObj->categoryid(), 'item_submit');
        publisherSaveCategoryPermissions($grpmoderation, $categoryObj->categoryid(), 'category_moderation');

        //Added by fx2024
        $parentCat = $categoryObj->categoryid();
        $sizeof    = count(XoopsRequest::getString('scname', '', 'POST'));
        for ($i = 0; $i < $sizeof; ++$i) {
            $temp = XoopsRequest::getArray('scname', array(), 'POST');
            if ($temp[$i] != '') {
                $categoryObj = $publisher->getHandler('category')->create();
                $temp2       = XoopsRequest::getArray('scname', array(), 'POST');
                $categoryObj->setVar('name', $temp2[$i]);
                $categoryObj->setVar('parentid', $parentCat);

                if (!$categoryObj->store()) {
                    redirect_header('javascript:history.go(-1)', 3, _AM_PUBLISHER_SUBCATEGORY_SAVE_ERROR . publisherFormatErrors($categoryObj->getErrors()));
                    //                    exit;
                }
                // TODO : put this function in the category class
                publisherSaveCategoryPermissions($grpread, $categoryObj->categoryid(), 'category_read');
                publisherSaveCategoryPermissions($grpsubmit, $categoryObj->categoryid(), 'item_submit');
                publisherSaveCategoryPermissions($grpmoderation, $categoryObj->categoryid(), 'category_moderation');
            }
        }
        //end of fx2024 code
        redirect_header($redirect_to, 2, $redirect_msg);
        //        exit();
        break;

    //Added by fx2024

    case 'addsubcats':
        $categoryid = 0;
        $nb_subcats = XoopsRequest::getInt('nb_subcats', 0, 'POST') + XoopsRequest::getInt('nb_sub_yet', 0, 'POST');

        $categoryObj = $publisher->getHandler('category')->create();
        $categoryObj->setVar('name', XoopsRequest::getString('name', '', 'POST'));
        $categoryObj->setVar('description', XoopsRequest::getString('description', '', 'POST'));
        $categoryObj->setVar('weight', XoopsRequest::getInt('weight', 0, 'POST'));
        if (isset($parentCat)) {
            $categoryObj->setVar('parentid', $parentCat);
        }

        publisherCpHeader();
        PublisherUtilities::editCategory(true, $categoryid, $nb_subcats, $categoryObj);
        exit();
        break;
    //end of fx2024 code

    case 'cancel':
        redirect_header('category.php', 1, sprintf(_AM_PUBLISHER_BACK2IDX, ''));
        //        exit();
        break;
    case 'default':
    default:
        publisherCpHeader();
        //publisher_adminMenu(1, _AM_PUBLISHER_CATEGORIES);

        echo "<br />\n";
        echo "<form><div style=\"margin-bottom: 12px;\">";
        echo "<input type='button' name='button' onclick=\"location='category.php?op=mod'\" value='" . _AM_PUBLISHER_CATEGORY_CREATE . "'>&nbsp;&nbsp;";
        //echo "<input type='button' name='button' onclick=\"location='item.php?op=mod'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
        echo '</div></form>';

        // Creating the objects for top categories
        $categoriesObj = $publisher->getHandler('category')->getCategories($publisher->getConfig('idxcat_perpage'), $startcategory, 0);

        publisherOpenCollapsableBar('createdcategories', 'createdcategoriesicon', _AM_PUBLISHER_CATEGORIES_TITLE, _AM_PUBLISHER_CATEGORIES_DSC);

        echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
        echo '<tr>';
        echo "<th width='20' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMCATEGORY_ID . '</strong></td>';
        echo "<th class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCATEGORYNAME . '</strong></td>';
        echo "<th width='60' class='bg3' width='65' align='center'><strong>" . _CO_PUBLISHER_WEIGHT . '</strong></td>';
        echo "<th width='60' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
        echo '</tr>';
        $totalCategories = $publisher->getHandler('category')->getCategoriesCount(0);
        if (count($categoriesObj) > 0) {
            foreach ($categoriesObj as $key => $thiscat) {
                PublisherUtilities::displayCategory($thiscat);
            }
            unset($key, $thiscat);
        } else {
            echo '<tr>';
            echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOCAT . '</td>';
            echo '</tr>';
            $categoryid = '0';
        }
        echo "</table>\n";
        include_once $GLOBALS['xoops']->path('class/pagenav.php');
        $pagenav = new XoopsPageNav($totalCategories, $publisher->getConfig('idxcat_perpage'), $startcategory, 'startcategory');
        echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';
        echo '<br />';
        publisherCloseCollapsableBar('createdcategories', 'createdcategoriesicon');
        echo '<br>';
        //editcat(false);
        break;
}

include_once __DIR__ . '/admin_footer.php';
