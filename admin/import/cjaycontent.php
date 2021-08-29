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
use XoopsModules\Publisher\{
    Constants,
    Item,
    Utility
};

/** @var Helper $helper */

const DIRNAME = 'cjaycontent';

require_once \dirname(__DIR__) . '/admin_header.php';
$myts = \MyTextSanitizer::getInstance();

$importFromModuleName = 'cjaycontent ' . Request::getString('cjaycontent_version', '', 'POST');

$scriptname = DIRNAME . '.php';

$op = ('go' === Request::getString('op', '', 'POST')) ? 'go' : 'start';

/**
 * @param $src
 * @param $dst
 */
//function recurseCopy($src, $dst)
//{
//    $dir = opendir($src);
////    @mkdir($dst);
//    while (false !== ($file = readdir($dir))) {
//        if (($file != '.') && ($file != '..')) {
//            if (is_dir($src . '/' . $file)) {
//                recurseCopy($src . '/' . $file, $dst . '/' . $file);
//            } else {
//                copy($src . '/' . $file, $dst . '/' . $file);
//            }
//        }
//    }
//    closedir($dir);
//}

if ('start' === $op) {
    xoops_load('XoopsFormLoader');

    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    Utility::openCollapsableBar('cjaycontentimport', 'cjaycontentimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $GLOBALS['xoopsDB']->prefix('cjaycontent'));
    [$totalArticles] = $GLOBALS['xoopsDB']->fetchRow($result);

    if (0 == $totalArticles) {
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . '</span>';
    } else {
        echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . '</span>';

        $form = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");

        ob_end_clean();

        $form->addElement(new \XoopsFormHidden('op', 'go'));
        $form->addElement(new \XoopsFormButton('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

        $form->addElement(new \XoopsFormHidden('from_module_version', Request::getString('cjaycontent_version', '', 'POST')));

        $form->display();
    }
    //    }

    Utility::closeCollapsableBar('cjaycontentimport', 'cjaycontentimporticon');
    xoops_cp_footer();
}

if ('go' === $op) {
    Utility::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    // require_once \dirname(__DIR__, 2) . '/include/common.php';
    Utility::openCollapsableBar('cjaycontentimportgo', 'cjaycontentimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);
    $moduleId = $helper->getModule()->getVar('mid');
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    $cnt_imported_articles = 0;

    $newArticleArray = [];

    $oldToNew = [];

    $sql            = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('cjaycontent');
    $resultArticles = $GLOBALS['xoopsDB']->query($sql);
    while (false !== ($arrArticle = $GLOBALS['xoopsDB']->fetchArray($resultArticles))) {
        // insert article
        /** @var Item $itemObj */
        $itemObj = $helper->getHandler('Item')->create();
        $itemObj->setVar('itemid', $arrArticle['id']);
        //      $itemObj->setVar('categoryid', $categoryObj->categoryid());
        $itemObj->setVar('title', $arrArticle['title']);
        $itemObj->setVar('uid', $arrArticle['submitter']);
        $itemObj->setVar('summary', $arrArticle['comment']);
        $itemObj->setVar('body', $arrArticle['content']);
        $itemObj->setVar('counter', $arrArticle['hits']);
        $itemObj->setVar('datesub', $arrArticle['date']);
        //            $itemObj->setVar('dohtml', !$arrArticle['nohtml']);
        //            $itemObj->setVar('dosmiley', !$arrArticle['nosmiley']);
        $itemObj->setVar('weight', $arrArticle['weight']);
        $itemObj->setVar('status', Constants::PUBLISHER_STATUS_PUBLISHED);

        //            $itemObj->setVar('dobr', !$arrArticle['dobr']);
        //            $itemObj->setVar('item_tag', $arrArticle['tags']);
        //            $itemObj->setVar('notifypub', $arrArticle['notifypub']);

        $itemObj->setVar('image', $arrArticle['image']);
        //            $itemObj->setVar('rating', $arrArticle['rating']);
        //            $itemObj->setVar('votes', $arrArticle['votes']);
        //            $itemObj->setVar('comments', $arrArticle['comments']);
        //            $itemObj->setVar('meta_keywords', $arrArticle['keywords']);
        //            $itemObj->setVar('meta_description', $arrArticle['description']);

        /*
         // HTML Wrap
         if ($arrArticle['htmlpage']) {
         $pagewrap_filename = $GLOBALS['xoops']->path("modules/wfsection/html/" .$arrArticle['htmlpage']);
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
        $newArticleArray[$arrArticle['id']] = $itemObj->itemid();
        echo '&nbsp;&nbsp;' . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->getTitle()) . '<br>';
        ++$cnt_imported_articles;
    }

    echo '<br>';

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

    echo sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles) . '<br>';
    echo "<br><a href='" . PUBLISHER_URL . "/'>" . _AM_PUBLISHER_IMPORT_GOTOMODULE . '</a><br>';

    Utility::closeCollapsableBar('cjaycontentimportgo', 'cjaycontentimportgoicon');
    xoops_cp_footer();
}
