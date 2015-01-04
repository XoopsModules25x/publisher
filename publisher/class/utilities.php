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
 * PedigreeBreadcrumb Class
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      XOOPS Development Team
 * @package     Publisher
 * @since       1.03
 * @version     $Id: breadcrumb.php 12277 2014-01-26 01:21:57Z beckmi $
 *
 */

include_once dirname(__DIR__) . '/include/common.php';

//namespace Publisher;


/**
 * Class PublisherUtilities
 */
class PublisherUtilities
{

    /**
     * Function responsible for checking if a directory exists, we can also write in and create an index.html file
     *
     * @param string $folder The full path of the directory to check
     *
     * @return void
     */
    public static function prepareFolder($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0777);
            file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
        }
        chmod($folder, 0777);
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
//    @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    // auto create folders----------------------------------------
//TODO rename this function? And exclude image folder?
    public static function createDir()
    {
        // auto crate folders
        $thePath = publisher_getUploadDir();

        if (publisher_getPathStatus('root', true) < 0) {
            $thePath = publisher_getUploadDir();
            $res     = publisher_mkdir($thePath);
            $msg     = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (publisher_getPathStatus('images', true) < 0) {
            $thePath = publisher_getImageDir();
            $res     = publisher_mkdir($thePath);

            if ($res) {
                $source = PUBLISHER_ROOT_PATH . "/assets/images/blank.png";
                $dest   = $thePath . "blank.png";
                publisher_copyr($source, $dest);
            }
            $msg = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (publisher_getPathStatus('images/category', true) < 0) {
            $thePath = publisher_getImageDir('category');
            $res     = publisher_mkdir($thePath);

            if ($res) {
                $source = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
                $dest   = $thePath . 'blank.png';
                publisher_copyr($source, $dest);
            }
            $msg = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (publisher_getPathStatus('images/item', true) < 0) {
            $thePath = publisher_getImageDir('item');
            $res     = publisher_mkdir($thePath);

            if ($res) {
                $source = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
                $dest   = $thePath . 'blank.png';
                publisher_copyr($source, $dest);
            }
            $msg = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (publisher_getPathStatus('content', true) < 0) {
            $thePath = publisher_getUploadDir(true, 'content');
            $res     = publisher_mkdir($thePath);
            $msg     = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }
    }

    public static function buildTableItemTitleRow()
    {
        echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>";
        echo "<tr>";
        echo "<th width='40px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . "</strong></td>";
        echo "<th width='100px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMCAT . "</strong></td>";
        echo "<th class='bg3' align='center'><strong>" . _AM_PUBLISHER_TITLE . "</strong></td>";
        echo "<th width='90px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . "</strong></td>";
        echo "<th width='90px' class='bg3' align='center'><strong>" . _CO_PUBLISHER_STATUS . "</strong></td>";
        echo "<th width='90px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
        echo "</tr>";
    }

    /**
     * @param     $categoryObj
     * @param int $level
     */
    public static function displayCategory(PublisherCategory $categoryObj, $level = 0)
    {
        $publisher = PublisherPublisher::getInstance();

        $description = $categoryObj->description();
        if (!XOOPS_USE_MULTIBYTES) {
            if (strlen($description) >= 100) {
                $description = substr($description, 0, (100 - 1)) . "...";
            }
        }
        $modify = "<a href='category.php?op=mod&amp;categoryid=" . $categoryObj->categoryid() . "&amp;parentid=" . $categoryObj->parentid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITCOL . "' alt='" . _AM_PUBLISHER_EDITCOL . "' /></a>";
        $delete = "<a href='category.php?op=del&amp;categoryid=" . $categoryObj->categoryid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETECOL . "' alt='" . _AM_PUBLISHER_DELETECOL . "' /></a>";

        $spaces = '';
        for ($j = 0; $j < $level; ++$j) {
            $spaces .= '&nbsp;&nbsp;&nbsp;';
        }

        echo "<tr>";
        echo "<td class='even' align='center'>" . $categoryObj->categoryid() . "</td>";
        echo "<td class='even' align='left'>" . $spaces . "<a href='" . PUBLISHER_URL . "/category.php?categoryid=" . $categoryObj->categoryid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/subcat.gif' alt='' />&nbsp;" . $categoryObj->name() . "</a></td>";
        echo "<td class='even' align='center'>" . $categoryObj->weight() . "</td>";
        echo "<td class='even' align='center'> $modify $delete </td>";
        echo "</tr>";
        $subCategoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $categoryObj->categoryid());
        if (count($subCategoriesObj) > 0) {
            ++$level;
            foreach ($subCategoriesObj as $key => $thiscat) {
                self::displayCategory($thiscat, $level);
            }
            unset($key, $thiscat);
        }
        unset($categoryObj);
    }

    /**
     * @param bool $showmenu
     * @param int $categoryid
     * @param int $nb_subcats
     * @param null $categoryObj
     */
    public static function editCategory($showmenu = false, $categoryid = 0, $nb_subcats = 4, $categoryObj = null)
    {
        $publisher = PublisherPublisher::getInstance();

        // if there is a parameter, and the id exists, retrieve data: we're editing a category
        if ($categoryid != 0) {
            // Creating the category object for the selected category
            $categoryObj = $publisher->getHandler('category')->get($categoryid);
            if ($categoryObj->notLoaded()) {
                redirect_header("category.php", 1, _AM_PUBLISHER_NOCOLTOEDIT);
//            exit();
            }
        } else {
            if (!$categoryObj) {
                $categoryObj = $publisher->getHandler('category')->create();
            }
        }

        if ($categoryid != 0) {
            if ($showmenu) {
                //publisher_adminMenu(1, _AM_PUBLISHER_CATEGORIES . " > " . _AM_PUBLISHER_EDITING);
            }
            echo "<br />\n";
            publisher_openCollapsableBar('edittable', 'edittableicon', _AM_PUBLISHER_EDITCOL, _AM_PUBLISHER_CATEGORY_EDIT_INFO);
        } else {
            if ($showmenu) {
                //publisher_adminMenu(1, _AM_PUBLISHER_CATEGORIES . " > " . _AM_PUBLISHER_CREATINGNEW);
            }
            publisher_openCollapsableBar('createtable', 'createtableicon', _AM_PUBLISHER_CATEGORY_CREATE, _AM_PUBLISHER_CATEGORY_CREATE_INFO);
        }

        $sform = $categoryObj->getForm($nb_subcats);
        $sform->display();

        if (!$categoryid) {
            publisher_closeCollapsableBar('createtable', 'createtableicon');
        } else {
            publisher_closeCollapsableBar('edittable', 'edittableicon');
        }

        //Added by fx2024
        if ($categoryid) {
            $sel_cat = $categoryid;

            publisher_openCollapsableBar('subcatstable', 'subcatsicon', _AM_PUBLISHER_SUBCAT_CAT, _AM_PUBLISHER_SUBCAT_CAT_DSC);
            // Get the total number of sub-categories
            $categoriesObj = $publisher->getHandler('category')->get($sel_cat);
            $totalsubs     = $publisher->getHandler('category')->getCategoriesCount($sel_cat);
            // creating the categories objects that are published
            $subcatsObj    = $publisher->getHandler('category')->getCategories(0, 0, $categoriesObj->categoryid());
            $totalSCOnPage = count($subcatsObj);
            echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
            echo "<tr>";
            echo "<td width='60' class='bg3' align='left'><strong>" . _AM_PUBLISHER_CATID . "</strong></td>";
            echo "<td width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_CATCOLNAME . "</strong></td>";
            echo "<td class='bg3' align='left'><strong>" . _AM_PUBLISHER_SUBDESCRIPT . "</strong></td>";
            echo "<td width='60' class='bg3' align='right'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
            echo "</tr>";
            if ($totalsubs > 0) {
                foreach ($subcatsObj as $subcat) {
                    $modify = "<a href='category.php?op=mod&amp;categoryid=" . $subcat->categoryid() . "'><img src='" . XOOPS_URL . "/modules/" . $publisher->getModule()->dirname() . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_MODIFY . "' alt='" . _AM_PUBLISHER_MODIFY . "' /></a>";
                    $delete = "<a href='category.php?op=del&amp;categoryid=" . $subcat->categoryid() . "'><img src='" . XOOPS_URL . "/modules/" . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETE . "' alt='" . _AM_PUBLISHER_DELETE . "' /></a>";
                    echo "<tr>";
                    echo "<td class='head' align='left'>" . $subcat->categoryid() . "</td>";
                    echo "<td class='even' align='left'><a href='" . XOOPS_URL . "/modules/" . $publisher->getModule()->dirname() . "/category.php?categoryid=" . $subcat->categoryid() . "&amp;parentid=" . $subcat->parentid() . "'>" . $subcat->name() . "</a></td>";
                    echo "<td class='even' align='left'>" . $subcat->description() . "</td>";
                    echo "<td class='even' align='right'> {$modify} {$delete} </td>";
                    echo "</tr>";
                }
                unset($subcat);
            } else {
                echo "<tr>";
                echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOSUBCAT . "</td>";
                echo "</tr>";
            }
            echo "</table>\n";
            echo "<br />\n";
            publisher_closeCollapsableBar('subcatstable', 'subcatsicon');

            publisher_openCollapsableBar('bottomtable', 'bottomtableicon', _AM_PUBLISHER_CAT_ITEMS, _AM_PUBLISHER_CAT_ITEMS_DSC);
            $startitem = XoopsRequest::getInt('startitem');
            // Get the total number of published ITEMS
            $totalitems = $publisher->getHandler('item')->getItemsCount($sel_cat, array(PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED));
            // creating the items objects that are published
            $itemsObj         = $publisher->getHandler('item')->getAllPublished($publisher->getConfig('idxcat_perpage'), $startitem, $sel_cat);
            $totalitemsOnPage = count($itemsObj);
            $allcats          = $publisher->getHandler('category')->getObjects(null, true);
            echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
            echo "<tr>";
            echo "<td width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . "</strong></td>";
            echo "<td width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCOLNAME . "</strong></td>";
            echo "<td class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMDESC . "</strong></td>";
            echo "<td width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . "</strong></td>";
            echo "<td width='60' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
            echo "</tr>";
            if ($totalitems > 0) {
                for ($i = 0; $i < $totalitemsOnPage; ++$i) {
                    $categoryObj = $allcats[$itemsObj[$i]->categoryid()];
                    $modify      = "<a href='item.php?op=mod&amp;itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . "/modules/" . $publisher->getModule()->dirname() . "/assets/images/links/edit.gif' title='" . _AM_PUBLISHER_EDITITEM . "' alt='" . _AM_PUBLISHER_EDITITEM . "' /></a>";
                    $delete      = "<a href='item.php?op=del&amp;itemid=" . $itemsObj[$i]->itemid() . "'><img src='" . XOOPS_URL . "/modules/" . $publisher->getModule()->dirname() . "/assets/images/links/delete.png' title='" . _AM_PUBLISHER_DELETEITEM . "' alt='" . _AM_PUBLISHER_DELETEITEM . "'/></a>";
                    echo "<tr>";
                    echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . "</td>";
                    echo "<td class='even' align='left'>" . $categoryObj->name() . "</td>";
                    echo "<td class='even' align='left'>" . $itemsObj[$i]->getitemLink() . "</td>";
                    echo "<td class='even' align='center'>" . $itemsObj[$i]->datesub('s') . "</td>";
                    echo "<td class='even' align='center'> $modify $delete </td>";
                    echo "</tr>";
                }
            } else {
                $itemid = -1;
                echo "<tr>";
                echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS . "</td>";
                echo "</tr>";
            }
            echo "</table>\n";
            echo "<br />\n";
            $parentid           = XoopsRequest::getInt('parentid', 0, 'GET');
            $pagenav_extra_args = "op=mod&categoryid=$sel_cat&parentid=$parentid";
            xoops_load('XoopsPageNav');
            $pagenav = new XoopsPageNav($totalitems, $publisher->getConfig('idxcat_perpage'), $startitem, 'startitem', $pagenav_extra_args);
            echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';
            echo "<input type='button' name='button' onclick=\"location='item.php?op=mod&categoryid=" . $sel_cat . "'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
            echo "</div>";
        }
        //end of fx2024 code
    }
}
