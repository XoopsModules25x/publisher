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
 */

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once dirname(__DIR__) . '/admin_header.php';
$myts = \MyTextSanitizer::getInstance();

$importFromModuleName = 'News ' . Request::getString('news_version', '', 'POST');

$scriptname = 'news.php';

$op = ('go' === Request::getString('op', '', 'POST')) ? 'go' : 'start';

if ('start' === $op) {
    xoops_load('XoopsFormLoader');

    Publisher\Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Publisher\Utility::openCollapsableBar('newsimport', 'newsimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('news_topics'));
    list($totalCat) = $GLOBALS['xoopsDB']->fetchRow($result);

    if (0 == $totalCat) {
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_IMPORT_NO_CATEGORY . '</span>';
    } else {
        require_once $GLOBALS['xoops']->path('class/xoopstree.php');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('news_stories'));
        list($totalArticles) = $GLOBALS['xoopsDB']->fetchRow($result);

        if (0 == $totalArticles) {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . '</span>';
        } else {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . '</span>';

            $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/{$scriptname}");

            // Categories to be imported
            $sql = 'SELECT cat.topic_id, cat.topic_pid, cat.topic_title, COUNT(art.storyid) FROM ' . $GLOBALS['xoopsDB']->prefix('news_topics') . ' AS cat INNER JOIN ' . $GLOBALS['xoopsDB']->prefix('news_stories') . ' AS art ON cat.topic_id=art.topicid GROUP BY art.topicid';

            $result           = $GLOBALS['xoopsDB']->query($sql);
            $cat_cbox_options = [];

            while (false !== (list($cid, $pid, $cat_title, $art_count) = $GLOBALS['xoopsDB']->fetchRow($result))) {
                $cat_title              = $myts->displayTarea($cat_title);
                $cat_cbox_options[$cid] = '$cat_title ($art_count)';
            }

            $cat_label = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode('<br>', $cat_cbox_options));
            $cat_label->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($cat_label);

            // Publisher parent category
            $mytree = new \XoopsTree($GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_categories'), 'categoryid', 'parentid');
            ob_start();
            $mytree->makeMySelBox('name', 'weight', $preset_id = 0, $none = 1, $sel_name = 'parent_category');

            $parent_cat_sel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            ob_end_clean();

            $form->addElement(new \XoopsFormHidden('op', 'go'));
            $form->addElement(new \XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new \XoopsFormHidden('from_module_version', Request::getString('news_version', '', 'POST')));

            $form->display();
        }
    }

    Publisher\Utility::closeCollapsableBar('newsimport', 'newsimporticon');
    xoops_cp_footer();
}

if ('go' === $op) {
    Publisher\Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Publisher\Utility::openCollapsableBar('newsimportgo', 'newsimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);
    /* @var  $moduleHandler XoopsModuleHandler */
    $moduleHandler  = xoops_getHandler('module');
    $moduleObj      = $moduleHandler->getByDirname('news');
    $news_module_id = $moduleObj->getVar('mid');
    /* @var  $gpermHandler XoopsGroupPermHandler */
    $gpermHandler = xoops_getHandler('groupperm');

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = Request::getInt('parent_category', 0, 'POST');

    $sql = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('news_topics');

    $resultCat = $GLOBALS['xoopsDB']->query($sql);

    $newCatArray     = [];
    $newArticleArray = [];

    $oldToNew = [];
    while (false !== ($arrCat = $GLOBALS['xoopsDB']->fetchArray($resultCat))) {
        $newCat           = [];
        $newCat['oldid']  = $arrCat['topic_id'];
        $newCat['oldpid'] = $arrCat['topic_pid'];
        /* @var  $categoryObj Publisher\Category */
        $categoryObj = $helper->getHandler('Category')->create();

        $categoryObj->setVar('parentid', $arrCat['topic_pid']);
        $categoryObj->setVar('weight', 0);
        $categoryObj->setVar('name', $arrCat['topic_title']);
        $categoryObj->setVar('description', $arrCat['topic_description']);

        // Category image
        if (('blank.gif' !== $arrCat['topic_imgurl']) && ('' !== $arrCat['topic_imgurl'])) {
            if (copy($GLOBALS['xoops']->path('modules/news/assets/images/topics/' . $arrCat['topic_imgurl']), $GLOBALS['xoops']->path('uploads/publisher/images/category/' . $arrCat['topic_imgurl']))) {
                $categoryObj->setVar('image', $arrCat['topic_imgurl']);
            }
        }

        if (!$helper->getHandler('Category')->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['topic_title']) . '<br>';
            continue;
        }

        $newCat['newid'] = $categoryObj->categoryid();
        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . '<br>';

        $sql            = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('news_stories') . ' WHERE topicid=' . $arrCat['topic_id'];
        $resultArticles = $GLOBALS['xoopsDB']->query($sql);
        while (false !== ($arrArticle = $GLOBALS['xoopsDB']->fetchArray($resultArticles))) {
            // insert article
            /** @var  Publisher\Item $itemObj */
            $itemObj = $helper->getHandler('Item')->create();

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
            $itemObj->setVar('status', Constants::PUBLISHER_STATUS_PUBLISHED);

            $itemObj->setVar('rating', $arrArticle['rating']);
            $itemObj->setVar('votes', $arrArticle['votes']);
            $itemObj->setVar('comments', $arrArticle['comments']);

            //            $itemObj->setVar('header', ''); //mb

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
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['title']) . '<br>';
                continue;
            } else {
                /*
                 // Linkes files
                 $sql = 'SELECT * FROM '.$GLOBALS['xoopsDB']->prefix('wfs_files').' WHERE articleid=' . $arrArticle['articleid'];
                 $resultFiles = $GLOBALS['xoopsDB']->query ($sql);
                 $allowed_mimetypes = '';
                 while (false !== ($arrFile = $GLOBALS['xoopsDB']->fetchArray ($resultFiles))) {

                 $filename = $GLOBALS['xoops']->path('modules/wfsection/cache/uploaded/' . $arrFile['filerealname']);
                 if (file_exists($filename)) {
                 if (copy($filename, $GLOBALS['xoops']->path('uploads/publisher/' . $arrFile['filerealname']))) {
                 $fileObj = $publisher_fileHandler->create();
                 $fileObj->setVar('name', $arrFile['fileshowname']);
                 $fileObj->setVar('description', $arrFile['filedescript']);
                 $fileObj->setVar('status', Constants::PUBLISHER_STATUS_FILE_ACTIVE);
                 $fileObj->setVar('uid', $arrArticle['uid']);
                 $fileObj->setVar('itemid', $itemObj->itemid());
                 $fileObj->setVar('mimetype', $arrFile['minetype']);
                 $fileObj->setVar('datesub', $arrFile['date']);
                 $fileObj->setVar('counter', $arrFile['counter']);
                 $fileObj->setVar('filename', $arrFile['filerealname']);

                 if ($fileObj->store($allowed_mimetypes, true, false)) {
                 echo '&nbsp;&nbsp;&nbsp;&nbsp;'  . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filerealname']) . '<br>';
                 }
                 }
                 }
                 }
                 */
                $newArticleArray[$arrArticle['storyid']] = $itemObj->itemid();
                echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . '<br>';
                ++$cnt_imported_articles;
            }
        }

        // Saving category permissions
        $groupsIds = $gpermHandler->getGroupIds('news_view', $arrCat['topic_id'], $news_module_id);
        Publisher\Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $gpermHandler->getGroupIds('news_submit', $arrCat['topic_id'], $news_module_id);
        Publisher\Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo '<br>';
    }

    // Looping through category to change the parentid to the new parentid
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
    unset($oldid, $newCat);

    // Looping through the comments to link them to the new articles and module
    echo _AM_PUBLISHER_IMPORT_COMMENTS . '<br>';

    $publisher_module_id = $helper->getModule()->mid();
    /** @var \XoopsCommentHandler $commentHandler */
    $commentHandler = xoops_getHandler('comment');
    $criteria       = new \CriteriaCompo();
    $criteria->add(new \Criteria('com_modid', $news_module_id));
    /** @var \XoopsComment $comment */
    $comments = $commentHandler->getObjects($criteria);
    foreach ($comments as $comment) {
        $comment->setVar('com_itemid', $newArticleArray[$comment->getVar('com_itemid')]);
        $comment->setVar('com_modid', $publisher_module_id);
        $comment->setNew();
        if (!$commentHandler->insert($comment)) {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT_ERROR, $comment->getVar('com_title')) . '<br>';
        } else {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT, $comment->getVar('com_title')) . '<br>';
        }
    }
    //    unset($comment);

    echo '<br><br>Done.<br>';
    echo sprintf(_AM_PUBLISHER_IMPORTED_CATEGORIES, $cnt_imported_cat) . '<br>';
    echo sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles) . '<br>';
    echo "<br><a href='" . PUBLISHER_URL . "/'>" . _AM_PUBLISHER_IMPORT_GOTOMODULE . '</a><br>';

    Publisher\Utility::closeCollapsableBar('newsimportgo', 'newsimportgoicon');
    xoops_cp_footer();
}
