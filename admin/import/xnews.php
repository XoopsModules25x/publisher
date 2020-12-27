<?php

declare(strict_types=1);
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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @author          Marius Scurtescu <mariuss@romanians.bc.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher\{Constants,
    Category,
    File,
    Helper,
    Item,
    Utility
};
/** @var Helper $helper */

const DIRNAME = 'xnews';

require_once dirname(__DIR__) . '/admin_header.php';
$myts = \MyTextSanitizer::getInstance();

$importFromModuleName = 'xNews ' . Request::getString('xnews_version', '', 'POST');

$scriptname = DIRNAME . '.php';

$op = ('go' === Request::getString('op', '', 'POST')) ? 'go' : 'start';

if ('start' === $op) {
    xoops_load('XoopsFormLoader');

    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('xnewsimport', 'xnewsimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('nw_topics'));
    [$totalCat] = $GLOBALS['xoopsDB']->fetchRow($result);

    if (0 == $totalCat) {
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_IMPORT_NO_CATEGORY . '</span>';
    } else {
        require_once $GLOBALS['xoops']->path('class/xoopstree.php');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('nw_stories'));
        [$totalArticles] = $GLOBALS['xoopsDB']->fetchRow($result);

        if (0 == $totalArticles) {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . '</span>';
        } else {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . '</span>';

            $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");

            //---------- mb ------------------
            // add 'publisher' category to 'imagecategory' table

            //            if (!$GLOBALS['xoopsSecurity']->check()) {
            //                redirect_header('admin.php?fct=images', 3, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
            //            }

            $imageCategoryHandler = xoops_getHandler('imagecategory');
            $imagecategory        = $imageCategoryHandler->create();
            //            $imagecategory->setVar('imgcat_name', $imgcat_name);
            $imagecategory->setVar('imgcat_name', PUBLISHER_DIRNAME); //$imgcat_name);
            $imagecategory->setVar('imgcat_maxsize', $GLOBALS['xoopsModuleConfig']['maximum_filesize']); //$imgcat_maxsize);
            $imagecategory->setVar('imgcat_maxwidth', $GLOBALS['xoopsModuleConfig']['maximum_image_width']); //$imgcat_maxwidth);
            $imagecategory->setVar('imgcat_maxheight', $GLOBALS['xoopsModuleConfig']['maximum_image_height']); //$imgcat_maxheight);
            //            $imgcat_display = empty($imgcat_display) ? 0 : 1;
            $imagecategory->setVar('imgcat_display', 1); //$imgcat_display);
            $imagecategory->setVar('imgcat_weight', 0); //$imgcat_weight);
            $imagecategory->setVar('imgcat_storetype', 'file'); //$imgcat_storetype);
            $imagecategory->setVar('imgcat_type', 'C');

            try {
                $imageCategoryHandler->insert($imagecategory);
            } catch (\Throwable $e) {
                echo "Caught exception: : couldn't insert Image Category " . $e->getMessage() . 'n';
            }

            $newid                    = $imagecategory->getVar('imgcat_id');
            $imagecategorypermHandler = xoops_getHandler('groupperm');
            if (!isset($readgroup)) {
                $readgroup = [];
            }
            if (!in_array(XOOPS_GROUP_ADMIN, $readgroup, true)) {
                $readgroup[] = XOOPS_GROUP_ADMIN;
            }
            foreach ($readgroup as $rgroup) {
                $imagecategoryperm = $imagecategorypermHandler->create();
                $imagecategoryperm->setVar('gperm_groupid', $rgroup);
                $imagecategoryperm->setVar('gperm_itemid', $newid);
                $imagecategoryperm->setVar('gperm_name', 'imgcat_read');
                $imagecategoryperm->setVar('gperm_modid', 1);
                $imagecategorypermHandler->insert($imagecategoryperm);
                unset($imagecategoryperm);
            }
            //            unset($rgroup);

            if (!isset($writegroup)) {
                $writegroup = [];
            }
            if (!in_array(XOOPS_GROUP_ADMIN, $writegroup, true)) {
                $writegroup[] = XOOPS_GROUP_ADMIN;
            }
            foreach ($writegroup as $wgroup) {
                $imagecategoryperm = $imagecategorypermHandler->create();
                $imagecategoryperm->setVar('gperm_groupid', $wgroup);
                $imagecategoryperm->setVar('gperm_itemid', $newid);
                $imagecategoryperm->setVar('gperm_name', 'imgcat_write');
                $imagecategoryperm->setVar('gperm_modid', 1);
                $imagecategorypermHandler->insert($imagecategoryperm);
                unset($imagecategoryperm);
            }
            //            unset($wgroup);

            //---------- mb ------------------

            // Categories to be imported
            $sql = 'SELECT cat.topic_id, cat.topic_pid, cat.topic_title, COUNT(art.storyid) FROM ' . $GLOBALS['xoopsDB']->prefix('nw_topics') . ' AS cat INNER JOIN ' . $GLOBALS['xoopsDB']->prefix('nw_stories') . ' AS art ON cat.topic_id=art.topicid GROUP BY art.topicid';

            $result         = $GLOBALS['xoopsDB']->query($sql);
            $catCboxOptions = [];

            while (list($cid, $pid, $catTitle, $articleCount) = $GLOBALS['xoopsDB']->fetchRow($result)) {
                $catTitle             = $myts->displayTarea($catTitle);
                $catCboxOptions[$cid] = "$catTitle ($articleCount)";
            }

            $catLabel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode('<br>', $catCboxOptions));
            $catLabel->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($catLabel);

            // Publisher parent category
            $mytree = new \XoopsTree($GLOBALS['xoopsDB']->prefix($helper->getModule()->dirname() . '_categories'), 'categoryid', 'parentid');
            ob_start();
            $mytree->makeMySelBox('name', 'weight', $preset_id = 0, $none = 1, $sel_name = 'parent_category');

            $parent_cat_sel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            ob_end_clean();

            $form->addElement(new \XoopsFormHidden('op', 'go'));
            $form->addElement(new \XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new \XoopsFormHidden('from_module_version', Request::getString('xnews_version', '', 'POST')));

            $form->display();
        }
    }

    Utility::closeCollapsableBar('xnewsimport', 'xnewsimporticon');
    xoops_cp_footer();
}

if ('go' === $op) {
    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    // require_once  dirname(dirname(__DIR__)) . '/include/common.php';
    Utility::openCollapsableBar('xnewsimportgo', 'xnewsimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);
    $moduleId         = $helper->getModule()->getVar('mid');
    
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = Request::getInt('parent_category', 0, 'POST');

    $sql = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('nw_topics');

    $resultCat = $GLOBALS['xoopsDB']->query($sql);

    $newCatArray     = [];
    $newArticleArray = [];
    /** @var XoopsImagecategoryHandler $imageCategoryHandler */
    $imageCategoryHandler = xoops_getHandler('imagecategory');
    //    $criteria = new \CriteriaCombo;

    //     get the total number of subcats for this category
    //     $criteria = new \CriteriaCompo();
    //     $criteria->add(new \Criteria('imagecategory', $catObj->getVar('cid'), '='));
    //     $childCount = (int)($mylinksCatHandler->getCount($criteria));

    $criteria        = new \Criteria('imgcat_name', PUBLISHER_DIRNAME);
    $imageCategoryId = $imageCategoryHandler->getObjects($criteria);

    //    $criteria = new \CriteriaCompo();
    //    $criteria->add(new \Criteria('imagecategory', PUBLISHER_DIRNAME, '='));

    //    $newid = $imageCategoryId->getVar('imgcat_id');
    $newid = $imageCategoryId[0]->vars['imgcat_id']['value'];
    //    $newid = $imageCategoryId[0]->vars['imgcat_id'];

    //    $select_form = new \XoopsFormSelect('', $name_current, array(), 1);
    //    $select_form->addOption('', _SELECT);
    //    $select_form->addOptionArray($writerHandler->getList($criteria));

    //    $sql = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('imagecategory') . ' WHERE imgcat_name=' . PUBLISHER_DIRNAME;
    //            $resultImageCategory = $GLOBALS['xoopsDB']->query($sql);
    //
    //
    //    $newid = $resultImageCategory->getVar('imgcat_id');

    $oldToNew = [];
    while (false !== ($arrCat = $GLOBALS['xoopsDB']->fetchArray($resultCat))) {
        $newCat           = [];
        $newCat['oldid']  = $arrCat['topic_id'];
        $newCat['oldpid'] = $arrCat['topic_pid'];

        /** @var Category $categoryObj */
        $categoryObj = $helper->getHandler('Category')->create();

        $categoryObj->setVar('parentid', $arrCat['topic_pid']);
        $categoryObj->setVar('image', $arrCat['topic_imgurl']);
        $categoryObj->setVar('weight', $arrCat['topic_weight']);
        $categoryObj->setVar('name', $arrCat['topic_title']);
        $categoryObj->setVar('description', $arrCat['topic_description']);
        $categoryObj->setVar('moderator', 1);

        // Category image: copying to Publisher category uploads
        if (('blank.gif' !== $arrCat['topic_imgurl']) && ('' !== $arrCat['topic_imgurl'])) {
            if (copy($GLOBALS['xoops']->path('uploads/xnews/topics/' . $arrCat['topic_imgurl']), $GLOBALS['xoops']->path('uploads/' . PUBLISHER_DIRNAME . '/images/category/' . $arrCat['topic_imgurl']))) {
                $categoryObj->setVar('image', $arrCat['topic_imgurl']);

                //======== there is no need to add the category images to Image Manager, because they are handled directly from /images/category/ folder

                /*

                  $imageHandler = xoops_getHandler('image');
                  $image = $imageHandler->create();
                  $image->setVar('image_name', $arrCat['topic_imgurl']);//'images/' . $uploader->getSavedFileName());
                  $image->setVar('image_nicename', mb_substr($arrCat['topic_imgurl'],-13)); //$imageNiceName);
                  $image->setVar('image_mimetype', 'image/'. mb_substr($arrCat['topic_imgurl'],-3));//$uploader->getMediaType());
  //                $image->setVar('image_created', time());
  //                $image_display = empty($image_display) ? 0 : 1;
                  $image->setVar('image_display', 1); //$image_display);
                  $image->setVar('image_weight', 0);//$image_weight);
                  $image->setVar('imgcat_id', $newid );//$imgcatId);
                  if (!$imageHandler->insert($image)) {
                      $err[] = sprintf(_FAILSAVEIMG, $image->getVar('image_nicename'));
                  }

                  */
                //============================
            }
        }

        if (!$helper->getHandler('Category')->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['topic_title']) . '<br>';
            continue;
        }

        //copy all images to Image Manager
        $src = $GLOBALS['xoops']->path('uploads/xnews/topics/');
        $dst = $GLOBALS['xoops']->path('uploads');
        Utility::recurseCopy($src, $dst);

        //populate the Image Manager with images from xNews articles (by Bleekk)

        $sql = 'INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('image') . "(`image_name`, `image_nicename`, `image_mimetype`, `image_display`, `image_weight`, `imgcat_id`)
       SELECT
       s.picture,
       RIGHT(s.picture,13) AS nicename,
       CONCAT('image/', RIGHT(s.picture,3)) AS `mimetype`,
       1 AS image_display,
       0 AS image_weight,
       c.imgcat_id
       FROM " . $GLOBALS['xoopsDB']->prefix('nw_stories') . ' s, ' . $GLOBALS['xoopsDB']->prefix('imagecategory') . " c
       WHERE s.picture <> ''
       AND c.imgcat_name = '" . PUBLISHER_DIRNAME . "'";

        $resultPictures = $GLOBALS['xoopsDB']->query($sql);

        $newCat['newid'] = $categoryObj->categoryid();
        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . '<br>';

        $sql            = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('nw_stories') . ' WHERE topicid=' . $arrCat['topic_id'];
        $resultArticles = $GLOBALS['xoopsDB']->query($sql);
        while (false !== ($arrArticle = $GLOBALS['xoopsDB']->fetchArray($resultArticles))) {
            // insert article

            /** @var Item $itemObj */
            $itemObj = $helper->getHandler('Item')->create();

            $itemObj->setVar('categoryid', $categoryObj->categoryid());
            $itemObj->setVar('title', $arrArticle['title']);
            $itemObj->setVar('uid', $arrArticle['uid']);
            $itemObj->setVar('summary', $arrArticle['hometext']);
            $itemObj->setVar('body', $arrArticle['bodytext']);
            $itemObj->setVar('counter', $arrArticle['counter']);
            $itemObj->setVar('datesub', $arrArticle['created']);
            $itemObj->setVar('dateexpire', $arrArticle['expired']);
            $itemObj->setVar('dohtml', !$arrArticle['nohtml']);
            $itemObj->setVar('dosmiley', !$arrArticle['nosmiley']);
            $itemObj->setVar('weight', 0);
            $itemObj->setVar('status', Constants::PUBLISHER_STATUS_PUBLISHED);

            $itemObj->setVar('dobr', !$arrArticle['dobr']);
            $itemObj->setVar('item_tag', $arrArticle['tags']);
            $itemObj->setVar('notifypub', $arrArticle['notifypub']);
            //-------- image

            /** @var \XoopsImageHandler $imgHandler */
            $imgHandler = xoops_getHandler('image');

            $criteria   = new \Criteria('image_name', $arrArticle['picture']);
            $imageId    = $imgHandler->getObjects($criteria);
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
             $pagewrap_filename = $GLOBALS['xoops']->path('modules/wfsection/html/' .$arrArticle['htmlpage']);
             if (file_exists($pagewrap_filename)) {
             if (copy($pagewrap_filename, $GLOBALS['xoops']->path('uploads/publisher/content/' . $arrArticle['htmlpage']))) {
             $itemObj->setVar('body', '[pagewrap=' . $arrArticle['htmlpage'] . ']');
             echo sprintf('&nbsp;&nbsp;&nbsp;&nbsp;' . _AM_PUBLISHER_IMPORT_ARTICLE_WRAP, $arrArticle['htmlpage']) . '<br>';
             }
             }
             }
             */

            if (!$itemObj->store()) {
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['storyid'] . ' ' . $arrArticle['title']) . '<br>';
                continue;
            }
            //--------------------------------------------
            // Linkes files
            $sql              = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('nw_stories_files') . ' WHERE storyid=' . $arrArticle['storyid'];
            $resultFiles      = $GLOBALS['xoopsDB']->query($sql);
            $allowedMimetypes = '';
            while (false !== ($arrFile = $GLOBALS['xoopsDB']->fetchArray($resultFiles))) {
                $filename = $GLOBALS['xoops']->path('uploads/xnews/attached/' . $arrFile['downloadname']);
                if (file_exists($filename)) {
                    if (copy($filename, $GLOBALS['xoops']->path('uploads/publisher/' . $arrFile['filerealname']))) {
                        /** @var File $fileObj */
                        $fileObj = $helper->getHandler('File')->create();
                        $fileObj->setVar('name', $arrFile['filerealname']);
                        $fileObj->setVar('description', $arrFile['filerealname']);
                        $fileObj->setVar('status', Constants::PUBLISHER_STATUS_FILE_ACTIVE);
                        $fileObj->setVar('uid', $arrArticle['uid']);
                        $fileObj->setVar('itemid', $itemObj->itemid());
                        $fileObj->setVar('mimetype', $arrFile['mimetype']);
                        $fileObj->setVar('datesub', $arrFile['date']);
                        $fileObj->setVar('counter', $arrFile['counter']);
                        $fileObj->setVar('filename', $arrFile['filerealname']);
                        $fileObj->setVar('short_url', $arrFile['filerealname']);

                        if ($fileObj->store($allowedMimetypes, true, false)) {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filerealname']) . '<br>';
                        }
                    }
                }
            }

            //------------------------

            $newArticleArray[$arrArticle['storyid']] = $itemObj->itemid();
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . '<br>';
            ++$cnt_imported_articles;
        }

        // Saving category permissions
        $groupsIds = $grouppermHandler->getGroupIds('nw_view', $arrCat['topic_id'], $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $grouppermHandler->getGroupIds('nw_submit', $arrCat['topic_id'], $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        $groupsIds = $grouppermHandler->getGroupIds('nw_approve', $arrCat['topic_id'], $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_moderation');

        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo '<br>';
    }

    // Looping through category to change the parentID to the new parentID
    foreach ($newCatArray as $oldid => $newCat) {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('categoryid', $newCat['newid']));
        $oldpid = $newCat['oldpid'];
        if (0 == $oldpid) {
            $newpid = $parentId;
        } else {
            $newpid = $newCatArray[$oldpid]['newid'];
        }
        $helper->getHandler('Category')->updateAll('parentid', $newpid, $criteria);
        unset($criteria);
    }
    unset($oldid);

    // Looping through the comments to link them to the new articles and module
    echo _AM_PUBLISHER_IMPORT_COMMENTS . '<br>';

    $publisher_module_id = $helper->getModule()->mid();

    /** @var XoopsCommentHandler $commentHandler */
    $commentHandler = xoops_getHandler('comment');
    $criteria       = new \CriteriaCompo();
    $criteria->add(new \Criteria('com_modid', $moduleId));
    $comments = $commentHandler->getObjects($criteria);
    /** @var  XoopsComment $comment */
    foreach ($comments as $comment) {
        $comment->setVar('com_itemid', $newArticleArray[$comment->getVar('com_itemid')]);
        $comment->setVar('com_modid', $publisher_module_id);
        $comment->setNew();
        if ($commentHandler->insert($comment)) {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT, $comment->getVar('com_title')) . '<br>';
        } else {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT_ERROR, $comment->getVar('com_title')) . '<br>';
        }
    }
    //    unset($comment);

    echo '<br><br>Done.<br>';
    echo sprintf(_AM_PUBLISHER_IMPORTED_CATEGORIES, $cnt_imported_cat) . '<br>';
    echo sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles) . '<br>';
    echo "<br><a href='" . PUBLISHER_URL . "/'>" . _AM_PUBLISHER_IMPORT_GOTOMODULE . '</a><br>';

    Utility::closeCollapsableBar('xnewsimportgo', 'xnewsimportgoicon');
    xoops_cp_footer();
}
