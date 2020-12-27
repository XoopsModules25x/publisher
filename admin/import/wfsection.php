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

const CATEGORY = 'wfs_category';
const ITEMID = 'itemid';
const ARTICLEID = 'articleid';
const DIRNAME = 'wfsection';

require_once dirname(__DIR__) . '/admin_header.php';
$myts = \MyTextSanitizer::getInstance();

$importFromModuleName = 'WF-Section ' . Request::getString('wfs_version', '', 'POST');

$scriptname = DIRNAME . '.php';

$op = ('go' === Request::getString('op', '', 'POST')) ? 'go' : 'start';

if ('start' === $op) {
    xoops_load('XoopsFormLoader');

    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('wfsectionimport', 'wfsectionimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix(CATEGORY));
    [$totalCat] = $GLOBALS['xoopsDB']->fetchRow($result);

    if (0 == $totalCat) {
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . _AM_PUBLISHER_IMPORT_NOCATSELECTED . '</span>';
    } else {
        require_once $GLOBALS['xoops']->path('class/xoopstree.php');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('wfs_article'));
        [$totalArticles] = $GLOBALS['xoopsDB']->fetchRow($result);

        if (0 == $totalArticles) {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . '</span>';
        } else {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . '</span>';

            $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");

            // Categories to be imported
            $sql             = 'SELECT cat.id, cat.pid, cat.title, COUNT(art.articleid) FROM ' . $GLOBALS['xoopsDB']->prefix(CATEGORY) . ' AS cat INNER JOIN ' . $GLOBALS['xoopsDB']->prefix('wfs_article') . ' AS art ON cat.id=art.categoryid GROUP BY art.categoryid';
            $result          = $GLOBALS['xoopsDB']->query($sql);
            $cat_cbox_values = [];
            $catCboxOptions  = [];
            while (list($cid, $pid, $catTitle, $articleCount) = $GLOBALS['xoopsDB']->fetchRow($result)) {
                $catTitle             = $myts->displayTarea($catTitle);
                $catCboxOptions[$cid] = "$catTitle ($articleCount)";
            }
            $catLabel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode('<br>', $catCboxOptions));
            $catLabel->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($catLabel);

            // SmartFAQ parent category
            $mytree = new \XoopsTree($GLOBALS['xoopsDB']->prefix($helper->getModule()->dirname() . '_categories'), 'categoryid', 'parentid');
            ob_start();
            $mytree->makeMySelBox('name', 'weight', $preset_id = 0, $none = 1, $sel_name = 'parent_category');

            $parent_cat_sel = new \XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            ob_end_clean();

            $form->addElement(new \XoopsFormHidden('op', 'go'));
            $form->addElement(new \XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new \XoopsFormHidden('from_module_version', Request::getString('wfs_version', '', 'POST')));

            $form->display();
        }
    }

    Utility::closeCollapsableBar('wfsectionimport', 'wfsectionimporticon');
    xoops_cp_footer();
}

if ('go' === $op) {
    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('wfsectionimportgo', 'wfsectionimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);

    $cnt_imported_cat      = 0;
    $cnt_imported_articles = 0;

    $parentId = Request::getInt('parent_category', 0, 'POST');
    //added to support 2.0.7
    $orders = 'orders';
    if ('2.07' === Request::getString('from_module_version', '', 'POST') || '2.06' === Request::getString('from_module_version', '', 'POST')) {
        $orders = 'weight';
    }
    //$sql = "SELECT * FROM ".$GLOBALS['xoopsDB']->prefix("wfs_category")." ORDER by orders";
    $sql = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix(CATEGORY) . " ORDER by $orders";
    //end added to support 2.0.7
    $resultCat = $GLOBALS['xoopsDB']->query($sql);

    $newCatArray = [];
    while (false !== ($arrCat = $GLOBALS['xoopsDB']->fetchArray($resultCat))) {
        /** @var Category $categoryObj */
        $categoryObj = $helper->getHandler('Category')->create();

        $newCat = [];

        $newCat['oldid']  = $arrCat['id'];
        $newCat['oldpid'] = $arrCat['pid'];

        $categoryObj->setVar('parentid', $arrCat['pid']);
        //added to support 2.0.7
        //$categoryObj->setVar ('weight', $arrCat['orders']);
        $categoryObj->setVar('weight', $arrCat[$orders]);
        //added to support 2.0.7
        $categoryObj->setGroupsRead(explode(' ', trim($arrCat['groupid'])));
        $categoryObj->setGroupsSubmit(explode(' ', trim($arrCat['editaccess'])));
        $categoryObj->setVar('name', $arrCat['title']);
        $categoryObj->setVar('description', $arrCat['description']);

        // Category image
        if (('blank.gif' !== $arrCat['imgurl']) && $arrCat['imgurl']) {
            if (copy($GLOBALS['xoops']->path('modules/wfsection/images/category/' . $arrCat['imgurl']), PUBLISHER_UPLOAD_PATH . '/images/category/' . $arrCat['imgurl'])) {
                $categoryObj->setVar('image', $arrCat['imgurl']);
            }
        }

        if (!$categoryObj->store(false)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['title']) . '<br>';
            continue;
        }

        $newCat['newid'] = $categoryObj->categoryid();
        // Saving category permissions
        Utility::saveCategoryPermissions($categoryObj->getGroupsRead(), $categoryObj->categoryid(), 'category_read');
        Utility::saveCategoryPermissions($categoryObj->getGroupsSubmit(), $categoryObj->categoryid(), 'item_submit');

        ++$cnt_imported_cat;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->name()) . '<br\>';

        $sql            = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('wfs_article') . ' WHERE categoryid=' . $arrCat['id'] . ' ORDER BY weight';
        $resultArticles = $GLOBALS['xoopsDB']->query($sql);
        while (false !== ($arrArticle = $GLOBALS['xoopsDB']->fetchArray($resultArticles))) {
            // insert article
            /** @var Item $itemObj */
            $itemObj = $helper->getHandler('Item')->create();

            $itemObj->setVar('categoryid', $categoryObj->categoryid());
            $itemObj->setVar('title', $arrArticle['title']);
            $itemObj->setVar('uid', $arrArticle['uid']);
            $itemObj->setVar('summary', $arrArticle['summary']);
            $itemObj->setVar('body', $arrArticle['maintext']);
            $itemObj->setVar('counter', $arrArticle['counter']);
            $itemObj->setVar('datesub', $arrArticle['created']);
            $itemObj->setVar('dohtml', !$arrArticle['nohtml']);
            $itemObj->setVar('dosmiley', !$arrArticle['nosmiley']);
            $itemObj->setVar('dobr', $arrArticle['nobreaks']);
            $itemObj->setVar('weight', $arrArticle['weight']);
            $itemObj->setVar('status', Constants::PUBLISHER_STATUS_PUBLISHED);
            $itemObj->setGroupsRead(explode(' ', trim($arrArticle['groupid'])));

            // HTML Wrap
            if ($arrArticle['htmlpage']) {
                $pagewrap_filename = $GLOBALS['xoops']->path('modules/wfsection/html/' . $arrArticle['htmlpage']);
                if (file_exists($pagewrap_filename)) {
                    if (copy($pagewrap_filename, PUBLISHER_UPLOAD_PATH . '/content/' . $arrArticle['htmlpage'])) {
                        $itemObj->setVar('body', '[pagewrap=' . $arrArticle['htmlpage'] . ']');
                        echo sprintf('&nbsp;&nbsp;&nbsp;&nbsp;' . _AM_PUBLISHER_IMPORT_ARTICLE_WRAP, $arrArticle['htmlpage']) . '<br>';
                    }
                }
            }

            if (!$itemObj->store()) {
                echo sprintf('  ' . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['title']) . '<br>';
                continue;
            }
            // Linkes files

            $sql              = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('wfs_files') . ' WHERE articleid=' . $arrArticle[ARTICLEID];
            $resultFiles      = $GLOBALS['xoopsDB']->query($sql);
            $allowedMimetypes = '';
            while (false !== ($arrFile = $GLOBALS['xoopsDB']->fetchArray($resultFiles))) {
                $filename = $GLOBALS['xoops']->path('modules/wfsection/cache/uploaded/' . $arrFile['filerealname']);
                if (file_exists($filename)) {
                    if (copy($filename, PUBLISHER_UPLOAD_PATH . '/' . $arrFile['filerealname'])) {
                        /** @var File $fileObj */
                        $fileObj = $helper->getHandler('File')->create();
                        $fileObj->setVar('name', $arrFile['fileshowname']);
                        $fileObj->setVar('description', $arrFile['filedescript']);
                        $fileObj->setVar('status', Constants::PUBLISHER_STATUS_FILE_ACTIVE);
                        $fileObj->setVar('uid', $arrArticle['uid']);
                        $fileObj->setVar(ITEMID, $itemObj->itemid());
                        $fileObj->setVar('mimetype', $arrFile['minetype']);
                        $fileObj->setVar('datesub', $arrFile['date']);
                        $fileObj->setVar('counter', $arrFile['counter']);
                        $fileObj->setVar('filename', $arrFile['filerealname']);

                        if ($fileObj->store($allowedMimetypes, true, false)) {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filerealname']) . '<br>';
                        }
                    }
                }
            }

            $newArticleArray[$arrArticle[ARTICLEID]] = $itemObj->itemid();
            echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . '<br>';
            ++$cnt_imported_articles;
        }
        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo '<br>';
    }
    // Looping through cat to change the pid to the new pid
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
    $moduleId         = $helper->getModule()->getVar('mid');
    
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

    Utility::closeCollapsableBar('wfsectionimportgo', 'wfsectionimportgoicon');
    xoops_cp_footer();
}
