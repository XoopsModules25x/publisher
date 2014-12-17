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
 * @author          Marius Scurtescu <mariuss@romanians.bc.ca>
 * @version         $Id: xnews.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__DIR__) . '/admin_header.php';
$myts = MyTextSanitizer::getInstance();

$importFromModuleName = "xNews " . @$_POST['xnews_version'];

$scriptname = "xnews.php";

$op = 'start';

if (isset($_POST['op']) && ('go' == XoopsRequest::getString('op', '', 'POST'))) {
    $op = XoopsRequest::getString('op', '', 'POST');
}

/**
 * @param $src
 * @param $dst
 */
function recurse_copy($src, $dst)
{
    $dir = opendir($src);
//    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

if ($op == 'start') {
    xoops_load('XoopsFormLoader');

    publisher_cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    publisher_openCollapsableBar(
        'xnewsimport',
        'xnewsimporticon',
        sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName),
        _AM_PUBLISHER_IMPORT_INFO
    );

    $result = $xoopsDB->query("SELECT COUNT(*) FROM " . $xoopsDB->prefix("nw_topics"));
    list ($totalCat) = $xoopsDB->fetchRow($result);

    if ($totalCat == 0) {
        echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . _AM_PUBLISHER_IMPORT_NO_CATEGORY . "</span>";
    } else {
        include_once XOOPS_ROOT_PATH . '/class/xoopstree.php';

        $result = $xoopsDB->query("SELECT COUNT(*) FROM " . $xoopsDB->prefix("nw_stories"));
        list ($totalArticles) = $xoopsDB->fetchRow($result);

        if ($totalArticles == 0) {
            echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . sprintf(
                    _AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS,
                    $importFromModuleName,
                    $totalArticles
                ) . "</span>";
        } else {
            echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . sprintf(
                    _AM_PUBLISHER_IMPORT_MODULE_FOUND,
                    $importFromModuleName,
                    $totalArticles,
                    $totalCat
                ) . "</span>";

            $form = new XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");

//---------- mb ------------------
// add "publisher" category to "imagecategory" table

//            if (!$GLOBALS['xoopsSecurity']->check()) {
//                redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
//            }

            $imgcat_handler =& xoops_gethandler('imagecategory');
            $imagecategory  =& $imgcat_handler->create();
//            $imagecategory->setVar('imgcat_name', $imgcat_name);
            $imagecategory->setVar('imgcat_name', PUBLISHER_DIRNAME); //$imgcat_name);
            $imagecategory->setVar('imgcat_maxsize', $xoopsModuleConfig['maximum_filesize']); //$imgcat_maxsize);
            $imagecategory->setVar('imgcat_maxwidth', $xoopsModuleConfig['maximum_image_width']); //$imgcat_maxwidth);
            $imagecategory->setVar('imgcat_maxheight', $xoopsModuleConfig['maximum_image_height']); //$imgcat_maxheight);
//            $imgcat_display = empty($imgcat_display) ? 0 : 1;
            $imagecategory->setVar('imgcat_display', 1); //$imgcat_display);
            $imagecategory->setVar('imgcat_weight', 0); //$imgcat_weight);
            $imagecategory->setVar('imgcat_storetype', 'file'); //$imgcat_storetype);
            $imagecategory->setVar('imgcat_type', 'C');
            if (!$imgcat_handler->insert($imagecategory)) {
                exit();
            }

            $newid                     = $imagecategory->getVar('imgcat_id');
            $imagecategoryperm_handler =& xoops_gethandler('groupperm');
            if (!isset($readgroup)) {
                $readgroup = array();
            }
            if (!in_array(XOOPS_GROUP_ADMIN, $readgroup)) {
                array_push($readgroup, XOOPS_GROUP_ADMIN);
            }
            foreach ($readgroup as $rgroup) {
                $imagecategoryperm =& $imagecategoryperm_handler->create();
                $imagecategoryperm->setVar('gperm_groupid', $rgroup);
                $imagecategoryperm->setVar('gperm_itemid', $newid);
                $imagecategoryperm->setVar('gperm_name', 'imgcat_read');
                $imagecategoryperm->setVar('gperm_modid', 1);
                $imagecategoryperm_handler->insert($imagecategoryperm);
                unset($imagecategoryperm);
            }
            if (!isset($writegroup)) {
                $writegroup = array();
            }
            if (!in_array(XOOPS_GROUP_ADMIN, $writegroup)) {
                array_push($writegroup, XOOPS_GROUP_ADMIN);
            }
            foreach ($writegroup as $wgroup) {
                $imagecategoryperm =& $imagecategoryperm_handler->create();
                $imagecategoryperm->setVar('gperm_groupid', $wgroup);
                $imagecategoryperm->setVar('gperm_itemid', $newid);
                $imagecategoryperm->setVar('gperm_name', 'imgcat_write');
                $imagecategoryperm->setVar('gperm_modid', 1);
                $imagecategoryperm_handler->insert($imagecategoryperm);
                unset($imagecategoryperm);
            }

//---------- mb ------------------

            // Categories to be imported
            $sql = "SELECT cat.topic_id, cat.topic_pid, cat.topic_title, COUNT(art.storyid) FROM " . $xoopsDB->prefix("nw_topics")
                . " AS cat INNER JOIN " . $xoopsDB->prefix("nw_stories") . " AS art ON cat.topic_id=art.topicid GROUP BY art.topicid";

            $result           = $xoopsDB->query($sql);
            $cat_cbox_options = array();

            while (list ($cid, $pid, $cat_title, $art_count) = $xoopsDB->fetchRow($result)) {
                $cat_title              = $myts->displayTarea($cat_title);
                $cat_cbox_options[$cid] = "$cat_title ($art_count)";
            }

            $cat_label = new XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode("<br />", $cat_cbox_options));
            $cat_label->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($cat_label);

            // Publisher parent category
            $mytree = new XoopsTree($xoopsDB->prefix("publisher_categories"), "categoryid", "parentid");
            ob_start();
            $mytree->makeMySelBox("name", "weight", $preset_id = 0, $none = 1, $sel_name = "parent_category");

            $parent_cat_sel = new XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            ob_end_clean();

            $form->addElement(new XoopsFormHidden('op', 'go'));
            $form->addElement(new XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new XoopsFormHidden('from_module_version', XoopsRequest::getString('xnews_version', '', 'POST')));

            $form->display();
        }
    }

    publisher_closeCollapsableBar('xnewsimport', 'xnewsimporticon');
    xoops_cp_footer();
}

if ($op == 'go') {
    publisher_cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    include_once (dirname(dirname(__DIR__))) . '/include/common.php';
    publisher_openCollapsableBar(
        'xnewsimportgo',
        'xnewsimportgoicon',
        sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName),
        _AM_PUBLISHER_IMPORT_RESULT
    );

    $module_handler  = xoops_gethandler('module');
    $moduleObj       = $module_handler->getByDirname('xnews');
    $xnews_module_id = $moduleObj->getVar('mid');

    $gperm_handler = xoops_gethandler('groupperm');

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = XoopsRequest::getInt('parent_category', 0, 'POST');

    $sql = "SELECT * FROM " . $xoopsDB->prefix('nw_topics');

    $resultCat = $xoopsDB->query($sql);

    $newCatArray     = array();
    $newArticleArray = array();

    $imgcat_handler =& xoops_gethandler('imagecategory');
//    $criteria = new criteriaCombo;

//     get the total number of subcats for this category
//     $criteria = new CriteriaCompo();
//     $criteria->add(new Criteria('imagecategory', $catObj->getVar('cid'), '='));
//     $childCount = intval($mylinksCatHandler->getCount($criteria));

    $criteria        = new Criteria('imgcat_name', PUBLISHER_DIRNAME);
    $imageCategoryId = $imgcat_handler->getObjects($criteria);

//    $criteria = new CriteriaCompo();
//    $criteria->add(new Criteria("imagecategory", PUBLISHER_DIRNAME, "="));

//    $newid = $imageCategoryId->getVar('imgcat_id');
    $newid = $imageCategoryId[0]->vars['imgcat_id']['value'];
//    $newid = $imageCategoryId[0]->vars['imgcat_id'];

//    $select_form = new XoopsFormSelect("", $name_current, array(), 1);
//    $select_form->addOption("", _SELECT);
//    $select_form->addOptionArray($writer_handler->getList($criteria));

//    $sql = "SELECT * FROM " . $xoopsDB->prefix('imagecategory') . " WHERE imgcat_name=" . PUBLISHER_DIRNAME;
//            $resultImageCategory = $xoopsDB->query($sql);
//
//
//    $newid = $resultImageCategory->getVar('imgcat_id');

    $oldToNew = array();
    while ($arrCat = $xoopsDB->fetchArray($resultCat)) {

        $newCat           = array();
        $newCat['oldid']  = $arrCat['topic_id'];
        $newCat['oldpid'] = $arrCat['topic_pid'];

        $categoryObj = $publisher->getHandler('category')->create();

        $categoryObj->setVar('parentid', $arrCat['topic_pid']);
        $categoryObj->setVar('image', $arrCat['topic_imgurl']);
        $categoryObj->setVar('weight', $arrCat['topic_weight']);
        $categoryObj->setVar('name', $arrCat['topic_title']);
        $categoryObj->setVar('description', $arrCat['topic_description']);
        $categoryObj->setVar('moderator', 1);

        // Category image: copying to Publisher category uploads
        if (($arrCat['topic_imgurl'] != 'blank.gif') && ($arrCat['topic_imgurl'] != '')) {
            if (copy(
                XOOPS_ROOT_PATH . "/uploads/xnews/topics/" . $arrCat['topic_imgurl'],
                XOOPS_ROOT_PATH . "/uploads/" . PUBLISHER_DIRNAME . "/images/category/" . $arrCat['topic_imgurl']
            )
            ) {
                $categoryObj->setVar('image', $arrCat['topic_imgurl']);

//======== there is no need to add the category images to Image Manager, because they are handled directly from /images/category/ folder

                /*

                  $image_handler =& xoops_gethandler('image');
                  $image =& $image_handler->create();
                  $image->setVar('image_name', $arrCat['topic_imgurl']);//'images/' . $uploader->getSavedFileName());
                  $image->setVar('image_nicename', substr($arrCat['topic_imgurl'],-13)); //$image_nicename);
                  $image->setVar('image_mimetype', 'image/'. substr($arrCat['topic_imgurl'],-3));//$uploader->getMediaType());
  //                $image->setVar('image_created', time());
  //                $image_display = empty($image_display) ? 0 : 1;
                  $image->setVar('image_display', 1); //$image_display);
                  $image->setVar('image_weight', 0);//$image_weight);
                  $image->setVar('imgcat_id', $newid );//$imgcat_id);
                  if (!$image_handler->insert($image)) {
                      $err[] = sprintf(_FAILSAVEIMG, $image->getVar('image_nicename'));
                  }

                  */

//============================

            }
        }

        if (!$publisher->getHandler('category')->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['topic_title']) . "<br/>";
            continue;
        }

        //copy all images to Image Manager

        $src = XOOPS_ROOT_PATH . "/uploads/xnews/topics/";
        $dst = XOOPS_ROOT_PATH . "/uploads";
        recurse_copy($src, $dst);

        //populate the Image Manager with images from xNews articles (by Bleekk)

        $sql = "INSERT INTO " . $xoopsDB->prefix("image") . "(`image_name`, `image_nicename`, `image_mimetype`, `image_display`, `image_weight`, `imgcat_id`)
       SELECT
       s.picture,
       RIGHT(s.picture,13) AS nicename,
       CONCAT('image/', RIGHT(s.picture,3)) AS `mimetype`,
       1 AS image_display,
       0 AS image_weight,
       c.imgcat_id
       FROM " . $xoopsDB->prefix("nw_stories") . " s, " . $xoopsDB->prefix("imagecategory") . " c
       WHERE s.picture <> ''
       AND c.imgcat_name = '" . PUBLISHER_DIRNAME . "'";

        $resultPictures = $xoopsDB->query($sql);

        $newCat['newid'] = $categoryObj->categoryid();
        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . "<br/>";

        $sql            = "SELECT * FROM " . $xoopsDB->prefix('nw_stories') . " WHERE topicid=" . $arrCat['topic_id'];
        $resultArticles = $xoopsDB->query($sql);
        while ($arrArticle = $xoopsDB->fetchArray($resultArticles)) {
            // insert article
            $itemObj = $publisher->getHandler('item')->create();

            $itemObj->setVar('categoryid', $categoryObj->categoryid());
            $itemObj->setVar('title', $arrArticle['title']);
            $itemObj->setVar('uid', $arrArticle['uid']);
            $itemObj->setVar('summary', $arrArticle['hometext']);
            $itemObj->setVar('body', $arrArticle['bodytext']);
            $itemObj->setVar('counter', $arrArticle['counter']);
            $itemObj->setVar('datesub', $arrArticle['created']);
            $itemObj->setVar('dohtml', !$arrArticle['nohtml']);
            $itemObj->setVar('dosmiley', !$arrArticle['nosmiley']);
            $itemObj->setVar('weight', 0);
            $itemObj->setVar('status', PublisherConstants::_PUBLISHER_STATUS_PUBLISHED);

            $itemObj->setVar('dobr', !$arrArticle['dobr']);
            $itemObj->setVar('item_tag', $arrArticle['tags']);
            $itemObj->setVar('notifypub', $arrArticle['notifypub']);
//-------- image

            $img_handler =& xoops_gethandler('image');

            $criteria   = new Criteria('image_name', $arrArticle['picture']);
            $imageId    = $img_handler->getObjects($criteria);
            $newImageId = $imageId[0]->vars['image_id']['value'];
            $itemObj->setVar('image', $newImageId);
            $itemObj->setVar('images', $newImageId);

//--------------

            $itemObj->setVar('rating', $arrArticle['rating']);
            $itemObj->setVar('votes', $arrArticle['votes']);
            $itemObj->setVar('comments', $arrArticle['comments']);
            $itemObj->setVar('meta_keywords', $arrArticle['keywords']);
            $itemObj->setVar('meta_description', $arrArticle['description']);

            /*
             // HTML Wrap
             if ($arrArticle['htmlpage']) {
             $pagewrap_filename	= XOOPS_ROOT_PATH . "/modules/wfsection/html/" .$arrArticle['htmlpage'];
             if (file_exists($pagewrap_filename)) {
             if (copy($pagewrap_filename, XOOPS_ROOT_PATH . "/uploads/publisher/content/" . $arrArticle['htmlpage'])) {
             $itemObj->setVar('body', "[pagewrap=" . $arrArticle['htmlpage'] . "]");
             echo sprintf("&nbsp;&nbsp;&nbsp;&nbsp;" . _AM_PUBLISHER_IMPORT_ARTICLE_WRAP, $arrArticle['htmlpage']) . "<br/>";
             }
             }
             }
             */

            if (!$itemObj->store()) {
                echo sprintf("  " . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['storyid'] . ' ' . $arrArticle['title']) . "<br/>";
                continue;
            } else {
//--------------------------------------------
                // Linkes files
                $sql               = "SELECT * FROM " . $xoopsDB->prefix("nw_stories_files") . " WHERE storyid=" . $arrArticle['storyid'];
                $resultFiles       = $xoopsDB->query($sql);
                $allowed_mimetypes = '';
                while ($arrFile = $xoopsDB->fetchArray($resultFiles)) {

                    $filename = XOOPS_ROOT_PATH . "/uploads/xnews/attached/" . $arrFile['downloadname'];
                    if (file_exists($filename)) {
                        if (copy($filename, XOOPS_ROOT_PATH . "/uploads/publisher/" . $arrFile['filerealname'])) {
                            $fileObj = $publisher->getHandler('file')->create();
                            $fileObj->setVar('name', $arrFile['filerealname']);
                            $fileObj->setVar('description', $arrFile['filerealname']);
                            $fileObj->setVar('status', PublisherConstants::_PUBLISHER_STATUS_FILE_ACTIVE);
                            $fileObj->setVar('uid', $arrArticle['uid']);
                            $fileObj->setVar('itemid', $itemObj->itemid());
                            $fileObj->setVar('mimetype', $arrFile['mimetype']);
                            $fileObj->setVar('datesub', $arrFile['date']);
                            $fileObj->setVar('counter', $arrFile['counter']);
                            $fileObj->setVar('filename', $arrFile['filerealname']);
                            $fileObj->setVar('short_url', $arrFile['filerealname']);

                            if ($fileObj->store($allowed_mimetypes, true, false)) {
                                echo "&nbsp;&nbsp;&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filerealname']) . "<br />";
                            }
                        }
                    }
                }

//------------------------

                $newArticleArray[$arrArticle['storyid']] = $itemObj->itemid();
                echo "&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->title()) . "<br />";
                ++$cnt_imported_articles;
            }
        }

        // Saving category permissions
        $groupsIds = $gperm_handler->getGroupIds('nw_view', $arrCat['topic_id'], $xnews_module_id);
        publisher_saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $gperm_handler->getGroupIds('nw_submit', $arrCat['topic_id'], $xnews_module_id);
        publisher_saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        $groupsIds = $gperm_handler->getGroupIds('nw_approve', $arrCat['topic_id'], $xnews_module_id);
        publisher_saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_moderation');

        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo "<br/>";
    }

    // Looping through category to change the parentID to the new parentID
    foreach ($newCatArray as $oldid => $newCat) {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('categoryid', $newCat['newid']));
        $oldpid = $newCat['oldpid'];
        if ($oldpid == 0) {
            $newpid = $parentId;
        } else {
            $newpid = $newCatArray[$oldpid]['newid'];
        }
        $publisher->getHandler('category')->updateAll('parentid', $newpid, $criteria);
        unset($criteria);
    }

    // Looping through the comments to link them to the new articles and module
    echo _AM_PUBLISHER_IMPORT_COMMENTS . "<br />";

    $publisher_module_id = $publisher->getModule()->mid();

    $comment_handler = xoops_gethandler('comment');
    $criteria        = new CriteriaCompo();
    $criteria->add(new Criteria('com_modid', $xnews_module_id));
    $comments = $comment_handler->getObjects($criteria);
    foreach ($comments as $comment) {
        $comment->setVar('com_itemid', $newArticleArray[$comment->getVar('com_itemid')]);
        $comment->setVar('com_modid', $publisher_module_id);
        $comment->setNew();
        if (!$comment_handler->insert($comment)) {
            echo "&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT_ERROR, $comment->getVar('com_title')) . "<br />";
        } else {
            echo "&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT, $comment->getVar('com_title')) . "<br />";
        }

    }

    echo "<br/><br/>Done.<br/>";
    echo sprintf(_AM_PUBLISHER_IMPORTED_CATEGORIES, $cnt_imported_cat) . "<br/>";
    echo sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles) . "<br/>";
    echo "<br/><a href='" . PUBLISHER_URL . "/'>" . _AM_PUBLISHER_IMPORT_GOTOMODULE . "</a><br/>";

    publisher_closeCollapsableBar('xnewsimportgo', 'xnewsimportgoicon');
    xoops_cp_footer();
}
