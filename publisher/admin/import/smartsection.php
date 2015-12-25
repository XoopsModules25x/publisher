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
 * @version         $Id: smartsection.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__DIR__) . '/admin_header.php';
$myts = MyTextSanitizer::getInstance();

$importFromModuleName = 'Smartsection ' . XoopsRequest::getString('smartsection_version', '', 'POST');

$scriptname = 'smartsection.php';

$op = ('go' === XoopsRequest::getString('op', '', 'POST')) ? 'go' : 'start';

if ($op === 'start') {
    xoops_load('XoopsFormLoader');

    publisherCpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    publisherOpenCollapsableBar('newsimport', 'newsimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_categories'));
    list($totalCat) = $GLOBALS['xoopsDB']->fetchRow($result);

    if ($totalCat == 0) {
        echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . _AM_PUBLISHER_IMPORT_NO_CATEGORY . '</span>';
    } else {
        include_once $GLOBALS['xoops']->path('class/xoopstree.php');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_items'));
        list($totalArticles) = $GLOBALS['xoopsDB']->fetchRow($result);

        if ($totalArticles == 0) {
            echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . '</span>';
        } else {
            echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . '</span>';

            $form = new XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/{$scriptname}");

            // Categories to be imported
            $sql = 'SELECT cat.categoryid, cat.parentid, cat.name, COUNT(art.itemid) FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_categories') . ' AS cat INNER JOIN ' . $GLOBALS['xoopsDB']->prefix('smartsection_items') . ' AS art ON cat.categoryid=art.categoryid GROUP BY art.categoryid';

            $result           = $GLOBALS['xoopsDB']->query($sql);
            $cat_cbox_options = array();

            while ((list($cid, $pid, $cat_title, $art_count) = $GLOBALS['xoopsDB']->fetchRow($result)) !== false) {
                $cat_title              = $myts->displayTarea($cat_title);
                $cat_cbox_options[$cid] = "$cat_title ($art_count)";
            }

            $cat_label = new XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode('<br />', $cat_cbox_options));
            $cat_label->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($cat_label);

            // Publisher parent category
            $mytree = new XoopsTree($GLOBALS['xoopsDB']->prefix('publisher_categories'), 'categoryid', 'parentid');
            ob_start();
            $mytree->makeMySelBox('name', 'weight', $preset_id = 0, $none = 1, $sel_name = 'parent_category');

            $parent_cat_sel = new XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            ob_end_clean();

            $form->addElement(new XoopsFormHidden('op', 'go'));
            $form->addElement(new XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new XoopsFormHidden('from_module_version', XoopsRequest::getString('news_version', '', 'POST')));

            $form->display();
        }
    }

    publisherCloseCollapsableBar('newsimport', 'newsimporticon');
    xoops_cp_footer();
}

if ($op === 'go') {
    publisherCpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    publisherOpenCollapsableBar('newsimportgo', 'newsimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);

    $moduleHandler          =& xoops_getHandler('module');
    $moduleObj              = $moduleHandler->getByDirname('smartsection');
    $smartsection_module_id = $moduleObj->getVar('mid');

    $gpermHandler =& xoops_getHandler('groupperm');

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = XoopsRequest::getInt('parent_category', 0, 'POST');

    $sql = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_categories');

    $resultCat = $GLOBALS['xoopsDB']->query($sql);

    $newCatArray     = array();
    $newArticleArray = array();

    $oldToNew = array();
    while (($arrCat = $GLOBALS['xoopsDB']->fetchArray($resultCat)) !== false) {
        $newCat           = array();
        $newCat['oldid']  = $arrCat['categoryid'];
        $newCat['oldpid'] = $arrCat['parentid'];

        $categoryObj =& $publisher->getHandler('category')->create();

        $categoryObj->setVars($arrCat);
        $categoryObj->setVar('categoryid', 0);

        // Copy category image
        if (($arrCat['image'] !== 'blank.gif') && ($arrCat['image'] !== '')) {
            copy($GLOBALS['xoops']->path('uploads/smartsection/images/category/' . $arrCat['image']), $GLOBALS['xoops']->path('uploads/publisher/images/category/' . $arrCat['image']));
        }

        if (!$publisher->getHandler('category')->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['name']) . '<br/>';
            continue;
        }

        $newCat['newid'] = $categoryObj->categoryid();
        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . "<br\>";

        $sql            = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_items') . ' WHERE categoryid=' . $arrCat['categoryid'];
        $resultArticles = $GLOBALS['xoopsDB']->query($sql);

        while (($arrArticle = $GLOBALS['xoopsDB']->fetchArray($resultArticles)) !== false) {
            // insert article
            $itemObj =& $publisher->getHandler('item')->create();

            $itemObj->setVars($arrArticle);
            $itemObj->setVar('itemid', 0);
            $itemObj->setVar('categoryid', $categoryObj->categoryid());

            // TODO: move article images to image manager

            // HTML Wrap
            // TODO: copy contents folder
            /*
            if ($arrArticle['htmlpage']) {
            $pagewrap_filename  = $GLOBALS['xoops']->path("modules/wfsection/html/" .$arrArticle['htmlpage']);
            if (file_exists($pagewrap_filename)) {
            if (copy($pagewrap_filename, $GLOBALS['xoops']->path("uploads/publisher/content/" . $arrArticle['htmlpage']))) {
            $itemObj->setVar('body', "[pagewrap=" . $arrArticle['htmlpage'] . "]");
            echo sprintf("&nbsp;&nbsp;&nbsp;&nbsp;" . _AM_PUBLISHER_IMPORT_ARTICLE_WRAP, $arrArticle['htmlpage']) . "<br/>";
            }
            }
            }
            */

            if (!$itemObj->store()) {
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['title']) . '<br/>';
                continue;
            } else {
                // Linkes files
                $sql               = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_files') . ' WHERE itemid=' . $arrArticle['itemid'];
                $resultFiles       = $GLOBALS['xoopsDB']->query($sql);
                $allowed_mimetypes = null;
                while (($arrFile = $GLOBALS['xoopsDB']->fetchArray($resultFiles)) !== false) {
                    $filename = $GLOBALS['xoops']->path('uploads/smartsection/' . $arrFile['filename']);
                    if (file_exists($filename)) {
                        if (copy($filename, $GLOBALS['xoops']->path('uploads/publisher/' . $arrFile['filename']))) {
                            $fileObj =& $publisher->getHandler('file')->create();
                            $fileObj->setVars($arrFile);
                            $fileObj->setVar('fileid', 0);

                            if ($fileObj->store($allowed_mimetypes, true, false)) {
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filename']) . '<br />';
                            }
                        }
                    }
                }

                $newArticleArray[$arrArticle['itemid']] = $itemObj->itemid();
                echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . '<br />';
                ++$cnt_imported_articles;
            }
        }

        // Saving category permissions
        $groupsIds = $gpermHandler->getGroupIds('category_read', $arrCat['categoryid'], $smartsection_module_id);
        publisherSaveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $gpermHandler->getGroupIds('item_submit', $arrCat['categoryid'], $smartsection_module_id);
        publisherSaveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo '<br/>';
    }

    // Looping through cat to change the parentid to the new parentid
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
    unset($oldid, $newCat);

    // Looping through the comments to link them to the new articles and module
    echo _AM_PUBLISHER_IMPORT_COMMENTS . '<br />';

    $publisher_module_id = $publisher->getModule()->mid();

    $commentHandler =& xoops_getHandler('comment');
    $criteria       = new CriteriaCompo();
    $criteria->add(new Criteria('com_modid', $smartsection_module_id));
    $comments = $commentHandler->getObjects($criteria);
    foreach ($comments as $comment) {
        $comment->setVar('com_itemid', $newArticleArray[$comment->getVar('com_itemid')]);
        $comment->setVar('com_modid', $publisher_module_id);
        $comment->setNew();
        if (!$commentHandler->insert($comment)) {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT_ERROR, $comment->getVar('com_title')) . '<br />';
        } else {
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT, $comment->getVar('com_title')) . '<br />';
        }
    }
    //    unset($comment);

    echo '<br/><br/>Done.<br/>';
    echo sprintf(_AM_PUBLISHER_IMPORTED_CATEGORIES, $cnt_imported_cat) . '<br/>';
    echo sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles) . '<br/>';
    echo "<br/><a href='" . PUBLISHER_URL . "/'>" . _AM_PUBLISHER_IMPORT_GOTOMODULE . '</a><br/>';

    publisherCloseCollapsableBar('newsimportgo', 'newsimportgoicon');
    xoops_cp_footer();
}
