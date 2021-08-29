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
 * @since           1.02 Beta 4
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @author          Marius Scurtescu <mariuss@romanians.bc.ca>
 * @author          ZySpec <zyspec@yahoo.com>
 */

use Xmf\Request;
use XoopsModules\Publisher\{
    Category,
    Constants,
    Helper,
    Item,
    Utility
};

/** @var Helper $helper */

const DIRNAME = 'fmcontent';

/** @var \XoopsPersistableObjectHandler $fmContentHandler */
/** @var \XoopsPersistableObjectHandler $fmTopicHandler */

require_once \dirname(__DIR__) . '/admin_header.php';
$myts = \MyTextSanitizer::getInstance();

$importFromModuleName = 'FmContent ' . Request::getString('fmcontent_version', '', 'POST');

$scriptname = DIRNAME . '.php';

$op = ('go' === Request::getString('op', '', 'POST')) ? 'go' : 'start';

if ('start' === $op) {
    xoops_load('XoopsFormLoader');

    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('fmimport', 'fmimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);
    $moduleId = $helper->getModule()->getVar('mid');

    $fmTopicHandler = xoops_getModuleHandler('topic', 'fmcontent');
    $fmTopicCount   = $fmTopicHandler->getCount(new \Criteria('topic_modid', $moduleId));

    if (empty($fmTopicCount)) {
        echo "<span style='color: #567; margin: 3px 0 12px 0; font-size: small; display: block;'>" . _AM_PUBLISHER_IMPORT_NO_CATEGORY . '</span>';
    } else {
        require_once $GLOBALS['xoops']->path('www/class/xoopstree.php');
        $fmContentHandler = xoops_getModuleHandler('page', 'fmcontent');
        $fmContentCount   = $fmContentHandler->getCount(new \Criteria('content_modid', $moduleId));

        if (empty($fmContentCount)) {
            echo "<span style='color: #567; margin: 3px 0 12px 0; font-size: small; display: block;'>" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $fmContentCount) . '</span>';
        } else {
            /*
                        echo "<span style='color: #567; margin: 3px 0 12px 0; font-size: small; display: block;'>" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $fmContentCount, $fmTopicCount) . "</span>";
                        $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");
            */
            // Categories to be imported
            $sql = 'SELECT cat.topic_id, cat.topic_pid, cat.topic_title, COUNT(art.content_id) FROM '
                   . $GLOBALS['xoopsDB']->prefix('fmcontent_topic')
                   . ' AS cat INNER JOIN '
                   . $GLOBALS['xoopsDB']->prefix('fmcontent_content')
                   . " AS art ON ((cat.topic_id=art.content_topic) AND (cat.topic_modid=art.content_modid)) WHERE cat.topic_modid={$moduleId} GROUP BY art.content_topic";

            $result         = $GLOBALS['xoopsDB']->query($sql);
            $catCboxOptions = [];

            while (list($cid, $pid, $catTitle, $articleCount) = $GLOBALS['xoopsDB']->fetchRow($result)) {
                $catTitle             = $myts->displayTarea($catTitle);
                $catCboxOptions[$cid] = "{$catTitle} ($articleCount)";
            }
            // now get articles in the top level category (content_topic=0)
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('content_modid', $moduleId));
            $criteria->add(new \Criteria('content_topic', 0));
            $cnt_tla_contents = $fmContentHandler->getCount($criteria);
            if ($cnt_tla_contents) {
                $catCboxOptions[0] = _AM_PUBLISHER_IMPORT_FMCONTENT_NAME . " ({$cnt_tla_contents})";
            }
            natcasesort($catCboxOptions); //put them in "alphabetical" order

            echo "<span style='color: #567; margin: 3px 0 12px 0; font-size: small; display: block;'>" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $fmContentCount, count($catCboxOptions)) . '</span>';
            $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");

            $catLabel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode('<br>', $catCboxOptions));
            $catLabel->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($catLabel);

            // Publisher parent category
            xoops_load('tree');
            $categoryHandler = $helper->getHandler('Category');
            $catObjs         = $categoryHandler->getAll();
            $myObjTree       = new \XoopsObjectTree($catObjs, 'categoryid', 'parentid');
            $moduleDirName   = \basename(\dirname(__DIR__));
            $module          = \XoopsModule::getByDirname($moduleDirName);
            $catSelBox       = $myObjTree->makeSelectElement('parent_category', 'name', '-', 0, true, 0, '', '')->render();
            //$form->addElement($catSelBox);

            $parent_cat_sel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, $catSelBox);
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            /*
                        $mytree = new \XoopsTree($GLOBALS['xoopsDB']->prefix("publisher_categories"), "categoryid", "parentid");
                        ob_start();
                        $mytree->makeMySelBox("name", "weight", $preset_id = 0, $none = 1, $sel_name = "parent_category");

                        $parent_cat_sel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
                        $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
                        $form->addElement($parent_cat_sel);
                        ob_end_clean();
            */
            $form->addElement(new \XoopsFormHidden('op', 'go'));
            $form->addElement(new \XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new \XoopsFormHidden('from_module_version', Request::getString('news_version', '', 'POST')));

            $form->display();
        }
    }

    Utility::closeCollapsableBar('fmimport', 'fmimporticon');
    xoops_cp_footer();
}

if ('go' === $op) {
    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('fmimportgo', 'fmimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);
    $moduleId = $helper->getModule()->getVar('mid');
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = Request::getInt('parent_category', 0, 'POST');

    // get all FmContent Content items without a category (content_topic=0)
    $fmContentHandler = xoops_getModuleHandler('page', 'fmcontent');

    $criteria = new \CriteriaCompo();
    $criteria->add(new \Criteria('content_modid', $moduleId));
    $criteria->add(new \Criteria('content_topic', 0));
    $fmContentObjs = $fmContentHandler->getAll($criteria);

    if ($fmContentObjs && is_array($fmContentObjs)) {
        ++$cnt_imported_cat; //count category if there was content to import

        // create Publsher category to hold FmContent Content items with no Topic (content_topic=0)
        /** @var Category $categoryObj */
        $categoryObj = $helper->getHandler('Category')->create();
        $categoryObj->setVars([
                                  'parentid'    => $parentId,
                                  'name'        => _AM_PUBLISHER_IMPORT_FMCONTENT_NAME,
                                  'description' => _AM_PUBLISHER_IMPORT_FMCONTENT_TLT,
                                  'image'       => '',
                                  'total'       => 0,
                                  'weight'      => 1,
                                  'created'     => time(),
                                  'moderator',
                                  $GLOBALS['xoopsUser']->getVar('uid'),
                              ]);
        $categoryObj->store();

        $fmTopicHandler = xoops_getModuleHandler('topic', 'fmcontent');

        // insert articles for this category
        foreach ($fmContentObjs as $thisFmContentObj) {
            $itemObj = $helper->getHandler('Item')->create();
            $itemObj->setVars([
                                  'categoryid'       => $categoryObj->categoryid(),
                                  'title'            => $thisFmContentObj->getVar('content_title'),
                                  'uid'              => $thisFmContentObj->getVar('content_uid'),
                                  'summary'          => $thisFmContentObj->getVar('content_short'),
                                  'body'             => $thisFmContentObj->getVar('content_text'),
                                  'datesub'          => $thisFmContentObj->getVar('content_create'),
                                  'dohtml'           => $thisFmContentObj->getVar('dohtml'),
                                  'dosmiley'         => $thisFmContentObj->getVar('dosmiley'),
                                  'doxcode'          => $thisFmContentObj->getVar('doxcode'),
                                  'doimage'          => $thisFmContentObj->getVar('doimage'),
                                  'dobr'             => $thisFmContentObj->getVar('dobr'),
                                  'weight'           => $thisFmContentObj->getVar('content_order'),
                                  'status'           => $thisFmContentObj->getVar('content_status') ? Constants::PUBLISHER_STATUS_PUBLISHED : Constants::PUBLISHER_STATUS_OFFLINE,
                                  'counter'          => $thisFmContentObj->getVar('content_hits'),
                                  'rating'           => 0,
                                  'votes'            => 0,
                                  'comments'         => $thisFmContentObj->getVar('content_comments'),
                                  'meta_keywords'    => $thisFmContentObj->getVar('content_words'),
                                  'meta_description' => $thisFmContentObj->getVar('content_desc'),
                              ]);
            $contentImg = $thisFmContentObj->getVar('content_img');
            if (!empty($contentImg)) {
                $itemObj->setVars([
                                      'images' => 1,
                                      'image'  => $thisFmContentObj->getVar('content_img'),
                                  ]);
            }

            if (!$itemObj->store()) {
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $thisFmContentObj->getVar('title')) . "<br>\n";
                continue;
            }
            $newArticleArray[$thisFmContentObj->getVar('storyid')] = $itemObj->itemid();
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . "<br>\n";
            ++$cnt_imported_articles;
        }

        // Saving category permissions
        $groupsIds = $grouppermHandler->getGroupIds('fmcontent_view', $thisFmContentObj->getVar('topic_id'), $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $grouppermHandler->getGroupIds('fmcontent_submit', $thisFmContentObj->getVar('topic_id'), $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        unset($fmContentObjs, $itemObj, $categoryObj, $thisFmContentObj);
        echo "<br>\n";
    }

    // Process all "normal" Topics (categories) from FmContent
    $newCatArray     = [];
    $newArticleArray = [];
    $oldToNew        = [];

    $fmTopicObjs = $fmTopicHandler->getAll(new \Criteria('topic_modid', $moduleId));

    // first create FmContent Topics as Publisher Categories
    foreach ($fmTopicObjs as $thisFmTopicObj) {
        $catIds = [
            'oldid'  => $thisFmTopicObj->getVar('topic_id'),
            'oldpid' => $thisFmTopicObj->getVar('topic_pid'),
        ];

        $categoryObj = $helper->getHandler('Category')->create();

        $categoryObj->setVars([
                                  'parentid'    => $thisFmTopicObj->getVar('topic_pid'),
                                  'weight'      => $thisFmTopicObj->getVar('topic_weight'),
                                  'name'        => $thisFmTopicObj->getVar('topic_title'),
                                  'description' => $thisFmTopicObj->getVar('topic_desc'),
                              ]);

        // Category image
        if (!in_array($thisFmTopicObj->getVar('topic_img'), ['blank.gif', ''])) {
            if (copy($GLOBALS['xoops']->path('www/uploads/fmcontent/img/' . $thisFmTopicObj->getVar('topic_img')), $GLOBALS['xoops']->path('www/uploads/publisher/images/category/' . $thisFmTopicObj->getVar('topic_img')))) {
                $categoryObj->setVar('image', $thisFmTopicObj->getVar('topic_img'));
            }
        }
        if (!$helper->getHandler('Category')->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $thisFmTopicObj->getVar('topic_title')) . "<br>\n";
            continue;
        }

        $catIds['newid'] = $categoryObj->categoryid();
        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . "<br>\n";

        // retrieve all articles (content) for this category
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('content_modid', $moduleId));  //only for this instance of fmcontent
        $criteria->add(new \Criteria('content_topic', $thisFmTopicObj->getVar('topic_id'))); //for this category
        $fmContentObjs = $fmContentHandler->getAll($criteria);

        // insert articles for this category
        /** @var Item $itemObj */
        foreach ($fmContentObjs as $thisFmContentObj) {
            $itemObj = $helper->getHandler('Item')->create();
            $itemObj->setVars([
                                  'categoryid'       => $catIds['newid'],
                                  'title'            => $thisFmContentObj->getVar('content_title'),
                                  'uid'              => $thisFmContentObj->getVar('content_uid'),
                                  'summary'          => $thisFmContentObj->getVar('content_short'),
                                  'body'             => $thisFmContentObj->getVar('content_text'),
                                  'counter'          => $thisFmContentObj->getVar('content_hits'),
                                  'datesub'          => $thisFmContentObj->getVar('content_create'),
                                  'dohtml'           => $thisFmContentObj->getVar('dohtml'),
                                  'dosmiley'         => $thisFmContentObj->getVar('dosmiley'),
                                  'doxcode'          => $thisFmContentObj->getVar('doxcode'),
                                  'doimage'          => $thisFmContentObj->getVar('doimage'),
                                  'dobr'             => $thisFmContentObj->getVar('dobr'),
                                  'weight'           => $thisFmContentObj->getVar('content_order'),
                                  'status'           => $thisFmContentObj->getVar('content_status') ? Constants::PUBLISHER_STATUS_PUBLISHED : Constants::PUBLISHER_STATUS_OFFLINE,
                                  'rating'           => 0,
                                  'votes'            => 0,
                                  'comments'         => $thisFmContentObj->getVar('content_comments'),
                                  'meta_keywords'    => $thisFmContentObj->getVar('content_words'),
                                  'meta_description' => $thisFmContentObj->getVar('content_desc'),
                              ]);
            $contentImg = $thisFmContentObj->getVar('content_img');
            if (!empty($contentImg)) {
                $itemObj->setVar('images', 1);
                $itemObj->setVar('image', $thisFmContentObj->getVar('content_img'));
            }

            if (!$itemObj->store()) {
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $thisFmContentObj->getVar('title')) . "<br>\n";
                continue;
            }
            $newArticleArray[$thisFmContentObj->getVar('storyid')] = $itemObj->itemid();
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . "<br>\n";
            ++$cnt_imported_articles;
        }

        // Saving category permissions
        $groupsIds = $grouppermHandler->getGroupIds('fmcontent_view', $thisFmContentObj->getVar('topic_id'), $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $grouppermHandler->getGroupIds('fmcontent_submit', $thisFmContentObj->getVar('topic_id'), $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        $newCatArray[$catIds['oldid']] = $catIds;
        unset($catIds, $thisFmContentObj);
        echo "<br>\n";
    }
    //    unset($thisFmTopicObj);

    // Looping through cat to change the parentid to the new parentid
    foreach ($newCatArray as $oldid => $catIds) {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('categoryid', $catIds['newid']));
        $oldpid = $catIds['oldpid'];
        $newpid = (0 == $oldpid) ? $parentId : $newCatArray[$oldpid]['newid'];
        $helper->getHandler('Category')->updateAll('parentid', $newpid, $criteria);
        unset($criteria);
    }
    unset($oldid);

    // Looping through the comments to link them to the new articles and module
    echo _AM_PUBLISHER_IMPORT_COMMENTS . "<br>\n";

    $publisher_module_id = $helper->getModule()->mid();
    /** @var \XoopsCommentHandler $commentHandler */
    $commentHandler = xoops_getHandler('comment');
    $criteria       = new \CriteriaCompo();
    $criteria->add(new \Criteria('com_modid', $moduleId));
    /** @var \XoopsComment $comment */
    $comments = $commentHandler->getObjects($criteria);
    foreach ($comments as $comment) {
        $comment->setVar('com_itemid', $newArticleArray[$comment->getVar('com_itemid')]);
        $comment->setVar('com_modid', $publisher_module_id);
        $comment->setNew();
        if ($commentHandler->insert($comment)) {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT, $comment->getVar('com_title')) . "<br>\n";
        } else {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT_ERROR, $comment->getVar('com_title')) . "<br>\n";
        }
    }
    //    unset($comment);

    echo '<br><br>'
         . _AM_PUBLISHER_IMPORT_DONE
         . "<br>\n"
         . ''
         . sprintf(_AM_PUBLISHER_IMPORTED_CATEGORIES, $cnt_imported_cat)
         . "<br>\n"
         . ''
         . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles)
         . "<br>\n"
         . "<br>\n<a href='"
         . PUBLISHER_URL
         . "/'>"
         . _AM_PUBLISHER_IMPORT_GOTOMODULE
         . "</a><br>\n";

    Utility::closeCollapsableBar('fmimportgo', 'fmimportgoicon');
    xoops_cp_footer();
}
