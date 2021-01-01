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
use XoopsModules\Publisher\{Category,
    File,
    Item,
    Helper,
    Utility
};
/** @var Helper $helper */

const CATEGORY = 'smartsection_categories';
const ITEMID = 'itemid';
const DIRNAME = 'smartsection';

require_once dirname(__DIR__) . '/admin_header.php';
$myts = \MyTextSanitizer::getInstance();

$importFromModuleName = 'Smartsection ' . Request::getString('smartsection_version', '', 'POST');

$scriptname = DIRNAME . '.php';

$op = ('go' === Request::getString('op', '', 'POST')) ? 'go' : 'start';

if ('start' === $op) {
    xoops_load('XoopsFormLoader');

    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('newsimport', 'newsimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix(CATEGORY));
    [$totalCat] = $GLOBALS['xoopsDB']->fetchRow($result);

    if (0 == $totalCat) {
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_IMPORT_NO_CATEGORY . '</span>';
    } else {
        require_once $GLOBALS['xoops']->path('class/xoopstree.php');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_items'));
        [$totalArticles] = $GLOBALS['xoopsDB']->fetchRow($result);

        if (0 == $totalArticles) {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . '</span>';
        } else {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . '</span>';

            $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/{$scriptname}");

            // Categories to be imported
            $sql = 'SELECT cat.categoryid, cat.parentid, cat.name, COUNT(art.itemid) FROM ' . $GLOBALS['xoopsDB']->prefix(CATEGORY) . ' AS cat INNER JOIN ' . $GLOBALS['xoopsDB']->prefix('smartsection_items') . ' AS art ON cat.categoryid=art.categoryid GROUP BY art.categoryid';

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

            $form->addElement(new \XoopsFormHidden('from_module_version', Request::getString('news_version', '', 'POST')));

            $form->display();
        }
    }

    Utility::closeCollapsableBar('newsimport', 'newsimporticon');
    xoops_cp_footer();
}

if ('go' === $op) {
    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('newsimportgo', 'newsimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);
    $moduleId         = $helper->getModule()->getVar('mid');
    
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = Request::getInt('parent_category', 0, 'POST');

    $sql = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix(CATEGORY);

    $resultCat = $GLOBALS['xoopsDB']->query($sql);

    $newCatArray     = [];
    $newArticleArray = [];

    $oldToNew = [];
    while (false !== ($arrCat = $GLOBALS['xoopsDB']->fetchArray($resultCat))) {
        $newCat           = [];
        $newCat['oldid']  = $arrCat['categoryid'];
        $newCat['oldpid'] = $arrCat['parentid'];
        /** @var Category $categoryObj */
        $categoryObj = $helper->getHandler('Category')->create();

        $categoryObj->setVars($arrCat);
        $categoryObj->setVar('categoryid', 0);

        // Copy category image
        if (('blank.gif' !== $arrCat['image']) && ('' !== $arrCat['image'])) {
            copy($GLOBALS['xoops']->path('uploads/smartsection/images/category/' . $arrCat['image']), $GLOBALS['xoops']->path('uploads/publisher/images/category/' . $arrCat['image']));
        }

        if (!$helper->getHandler('Category')->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['name']) . '<br>';
            continue;
        }

        $newCat['newid'] = $categoryObj->categoryid();
        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . '<br\>';

        $sql            = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_items') . ' WHERE categoryid=' . $arrCat['categoryid'];
        $resultArticles = $GLOBALS['xoopsDB']->query($sql);

        while (false !== ($arrArticle = $GLOBALS['xoopsDB']->fetchArray($resultArticles))) {
            // insert article
            /** @var Item $itemObj */
            $itemObj = $helper->getHandler('Item')->create();

            $itemObj->setVars($arrArticle);
            $itemObj->setVar(ITEMID, 0);
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
            echo sprintf("&nbsp;&nbsp;&nbsp;&nbsp;" . _AM_PUBLISHER_IMPORT_ARTICLE_WRAP, $arrArticle['htmlpage']) . "<br>";
            }
            }
            }
            */

            if (!$itemObj->store()) {
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['title']) . '<br>';
                continue;
            }
            // Linkes files
            $sql              = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('smartsection_files') . ' WHERE itemid=' . $arrArticle[ITEMID];
            $resultFiles      = $GLOBALS['xoopsDB']->query($sql);
            $allowedMimetypes = null;
            while (false !== ($arrFile = $GLOBALS['xoopsDB']->fetchArray($resultFiles))) {
                $filename = $GLOBALS['xoops']->path('uploads/smartsection/' . $arrFile['filename']);
                if (file_exists($filename)) {
                    if (copy($filename, $GLOBALS['xoops']->path('uploads/publisher/' . $arrFile['filename']))) {
                        /** @var File $fileObj */
                        $fileObj = $helper->getHandler('File')->create();
                        $fileObj->setVars($arrFile);
                        $fileObj->setVar('fileid', 0);

                        if ($fileObj->store($allowedMimetypes, true, false)) {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filename']) . '<br>';
                        }
                    }
                }
            }

            $newArticleArray[$arrArticle[ITEMID]] = $itemObj->itemid();
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . '<br>';
            ++$cnt_imported_articles;
        }

        // Saving category permissions
        $groupsIds = $grouppermHandler->getGroupIds('category_read', $arrCat['categoryid'], $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'category_read');
        $groupsIds = $grouppermHandler->getGroupIds('item_submit', $arrCat['categoryid'], $moduleId);
        Utility::saveCategoryPermissions($groupsIds, $categoryObj->categoryid(), 'item_submit');

        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo '<br>';
    }

    // Looping through cat to change the parentid to the new parentid
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

    Utility::closeCollapsableBar('newsimportgo', 'newsimportgoicon');
    xoops_cp_footer();
}
