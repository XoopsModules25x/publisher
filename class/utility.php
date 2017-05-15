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
 * PublisherUtil Class
 *
 * @copyright   XOOPS Project (http://xoops.org)
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      XOOPS Development Team
 * @package     Publisher
 * @since       1.03
 *
 */

use Xmf\Request;

include_once dirname(__DIR__) . '/include/common.php';

//namespace Publisher;

/**
 * Class PublisherUtility
 */
class PublisherUtility
{
    /**
     * Check Xoops Version against a provided version
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param string $signal
     * @return bool
     */
    public static function checkXoopsVersion($x, $y, $z, $signal = '==')
    {
        $xv = explode('-', str_replace('XOOPS ', '', XOOPS_VERSION));

        list($a, $b, $c) = explode('.', $xv[0]);
        $xv = $a*10000 + $b*100 + $c;
        $mv = $x*10000 + $y*100 + $z;
        if ($signal === '>') {
            return $xv > $mv;
        }
        if ($signal === '>=') {
            return $xv >= $mv;
        }
        if ($signal === '<') {
            return $xv < $mv;
        }
        if ($signal === '<=') {
            return $xv <= $mv;
        }
        if ($signal === '==') {
            return $xv == $mv;
        }

        return false;
    }

    /**
     * Function responsible for checking if a directory exists, we can also write in and create an index.html file
     *
     * @param string $folder The full path of the directory to check
     *
     * @return void
     */
    public static function createFolder($folder)
    {
        try {
            if (!file_exists($folder)) {
                if (!mkdir($folder) && !is_dir($folder)) {
                    throw new \RuntimeException(sprintf('Unable to create the %s directory', $folder));
                } else {
                    file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n", '<br/>';
        }
    }

    /**
     * @param $file
     * @param $folder
     * @return bool
     */
    public static function copyFile($file, $folder)
    {
        return copy($file, $folder);
        //        try {
        //            if (!is_dir($folder)) {
        //                throw new \RuntimeException(sprintf('Unable to copy file as: %s ', $folder));
        //            } else {
        //                return copy($file, $folder);
        //            }
        //        } catch (Exception $e) {
        //            echo 'Caught exception: ', $e->getMessage(), "\n", "<br>";
        //        }
        //        return false;
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        //    @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
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
        //        $thePath = static::getUploadDir();

        if (static::getPathStatus('root', true) < 0) {
            $thePath = static::getUploadDir();
            $res     = static::mkdir($thePath);
            $msg     = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (static::getPathStatus('images', true) < 0) {
            $thePath = static::getImageDir();
            $res     = static::mkdir($thePath);

            if ($res) {
                $source = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
                $dest   = $thePath . 'blank.png';
                static::copyr($source, $dest);
            }
            $msg = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (static::getPathStatus('images/category', true) < 0) {
            $thePath = static::getImageDir('category');
            $res     = static::mkdir($thePath);

            if ($res) {
                $source = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
                $dest   = $thePath . 'blank.png';
                static::copyr($source, $dest);
            }
            $msg = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (static::getPathStatus('images/item', true) < 0) {
            $thePath = static::getImageDir('item');
            $res     = static::mkdir($thePath);

            if ($res) {
                $source = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
                $dest   = $thePath . 'blank.png';
                static::copyr($source, $dest);
            }
            $msg = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }

        if (static::getPathStatus('content', true) < 0) {
            $thePath = static::getUploadDir(true, 'content');
            $res     = static::mkdir($thePath);
            $msg     = $res ? _AM_PUBLISHER_DIRCREATED : _AM_PUBLISHER_DIRNOTCREATED;
        }
    }

    public static function buildTableItemTitleRow()
    {
        echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>";
        echo '<tr>';
        echo "<th width='40px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . '</strong></td>';
        echo "<th width='100px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMCAT . '</strong></td>';
        echo "<th class='bg3' align='center'><strong>" . _AM_PUBLISHER_TITLE . '</strong></td>';
        echo "<th width='100px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . '</strong></td>';

        echo "<th width='50px' class='bg3' align='center'><strong>" . _CO_PUBLISHER_WEIGHT . '</strong></td>';
        echo "<th width='50px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_HITS . '</strong></td>';
        echo "<th width='60px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_RATE . '</strong></td>';
        echo "<th width='50px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_VOTES . '</strong></td>';
        echo "<th width='60px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_COMMENTS_COUNT . '</strong></td>';

        echo "<th width='90px' class='bg3' align='center'><strong>" . _CO_PUBLISHER_STATUS . '</strong></td>';
        echo "<th width='90px' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
        echo '</tr>';
    }

    /**
     * @param PublisherCategory $categoryObj
     * @param int $level
     */
    public static function displayCategory(PublisherCategory $categoryObj, $level = 0)
    {
        $publisher = PublisherPublisher::getInstance();

        $description = $categoryObj->description();
        if (!XOOPS_USE_MULTIBYTES) {
            if (strlen($description) >= 100) {
                $description = substr($description, 0, 100 - 1) . '...';
            }
        }
        $modify = "<a href='category.php?op=mod&amp;categoryid="
                  . $categoryObj->categoryid()
                  . '&amp;parentid='
                  . $categoryObj->parentid()
                  . "'><img src='"
                  . PUBLISHER_URL
                  . "/assets/images/links/edit.gif' title='"
                  . _AM_PUBLISHER_EDITCOL
                  . "' alt='"
                  . _AM_PUBLISHER_EDITCOL
                  . "' /></a>";
        $delete = "<a href='category.php?op=del&amp;categoryid="
                  . $categoryObj->categoryid()
                  . "'><img src='"
                  . PUBLISHER_URL
                  . "/assets/images/links/delete.png' title='"
                  . _AM_PUBLISHER_DELETECOL
                  . "' alt='"
                  . _AM_PUBLISHER_DELETECOL
                  . "' /></a>";

        $spaces = '';
        for ($j = 0; $j < $level; ++$j) {
            $spaces .= '&nbsp;&nbsp;&nbsp;';
        }

        echo '<tr>';
        echo "<td class='even' align='center'>" . $categoryObj->categoryid() . '</td>';
        echo "<td class='even' align='left'>"
             . $spaces
             . "<a href='"
             . PUBLISHER_URL
             . '/category.php?categoryid='
             . $categoryObj->categoryid()
             . "'><img src='"
             . PUBLISHER_URL
             . "/assets/images/links/subcat.gif' alt='' />&nbsp;"
             . $categoryObj->name()
             . '</a></td>';
        echo "<td class='even' align='center'>" . $categoryObj->weight() . '</td>';
        echo "<td class='even' align='center'> $modify $delete </td>";
        echo '</tr>';
        $subCategoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $categoryObj->categoryid());
        if (count($subCategoriesObj) > 0) {
            ++$level;
            foreach ($subCategoriesObj as $key => $thiscat) {
                self::displayCategory($thiscat, $level);
            }
            unset($key, $thiscat);
        }
        //        unset($categoryObj);
    }

    /**
     * @param bool $showmenu
     * @param int  $categoryId
     * @param int  $nbSubCats
     * @param null $categoryObj
     */
    public static function editCategory($showmenu = false, $categoryId = 0, $nbSubCats = 4, $categoryObj = null)
    {
        $publisher = PublisherPublisher::getInstance();

        // if there is a parameter, and the id exists, retrieve data: we're editing a category
        /* @var  $categoryObj PublisherCategory */
        if ($categoryId != 0) {
            // Creating the category object for the selected category
            $categoryObj = $publisher->getHandler('category')->get($categoryId);
            if ($categoryObj->notLoaded()) {
                redirect_header('category.php', 1, _AM_PUBLISHER_NOCOLTOEDIT);
                //            exit();
            }
        } else {
            if (!$categoryObj) {
                $categoryObj = $publisher->getHandler('category')->create();
            }
        }

        if ($categoryId != 0) {
            echo "<br>\n";
            static::openCollapsableBar('edittable', 'edittableicon', _AM_PUBLISHER_EDITCOL, _AM_PUBLISHER_CATEGORY_EDIT_INFO);
        } else {
            static::openCollapsableBar('createtable', 'createtableicon', _AM_PUBLISHER_CATEGORY_CREATE, _AM_PUBLISHER_CATEGORY_CREATE_INFO);
        }

        $sform = $categoryObj->getForm($nbSubCats);
        $sform->display();

        if (!$categoryId) {
            static::closeCollapsableBar('createtable', 'createtableicon');
        } else {
            static::closeCollapsableBar('edittable', 'edittableicon');
        }

        //Added by fx2024
        if ($categoryId) {
            $selCat = $categoryId;

            static::openCollapsableBar('subcatstable', 'subcatsicon', _AM_PUBLISHER_SUBCAT_CAT, _AM_PUBLISHER_SUBCAT_CAT_DSC);
            // Get the total number of sub-categories
            $categoriesObj = $publisher->getHandler('category')->get($selCat);
            $totalsubs     = $publisher->getHandler('category')->getCategoriesCount($selCat);
            // creating the categories objects that are published
            $subcatsObj    = $publisher->getHandler('category')->getCategories(0, 0, $categoriesObj->categoryid());
            $totalSCOnPage = count($subcatsObj);
            echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
            echo '<tr>';
            echo "<td width='60' class='bg3' align='left'><strong>" . _AM_PUBLISHER_CATID . '</strong></td>';
            echo "<td width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_CATCOLNAME . '</strong></td>';
            echo "<td class='bg3' align='left'><strong>" . _AM_PUBLISHER_SUBDESCRIPT . '</strong></td>';
            echo "<td width='60' class='bg3' align='right'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
            echo '</tr>';
            if ($totalsubs > 0) {
                foreach ($subcatsObj as $subcat) {
                    $modify = "<a href='category.php?op=mod&amp;categoryid="
                              . $subcat->categoryid()
                              . "'><img src='"
                              . XOOPS_URL
                              . '/modules/'
                              . $publisher->getModule()->dirname()
                              . "/assets/images/links/edit.gif' title='"
                              . _AM_PUBLISHER_MODIFY
                              . "' alt='"
                              . _AM_PUBLISHER_MODIFY
                              . "' /></a>";
                    $delete = "<a href='category.php?op=del&amp;categoryid="
                              . $subcat->categoryid()
                              . "'><img src='"
                              . XOOPS_URL
                              . '/modules/'
                              . $publisher->getModule()->dirname()
                              . "/assets/images/links/delete.png' title='"
                              . _AM_PUBLISHER_DELETE
                              . "' alt='"
                              . _AM_PUBLISHER_DELETE
                              . "' /></a>";
                    echo '<tr>';
                    echo "<td class='head' align='left'>" . $subcat->categoryid() . '</td>';
                    echo "<td class='even' align='left'><a href='"
                         . XOOPS_URL
                         . '/modules/'
                         . $publisher->getModule()->dirname()
                         . '/category.php?categoryid='
                         . $subcat->categoryid()
                         . '&amp;parentid='
                         . $subcat->parentid()
                         . "'>"
                         . $subcat->name()
                         . '</a></td>';
                    echo "<td class='even' align='left'>" . $subcat->description() . '</td>';
                    echo "<td class='even' align='right'> {$modify} {$delete} </td>";
                    echo '</tr>';
                }
                //                unset($subcat);
            } else {
                echo '<tr>';
                echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOSUBCAT . '</td>';
                echo '</tr>';
            }
            echo "</table>\n";
            echo "<br>\n";
            static::closeCollapsableBar('subcatstable', 'subcatsicon');

            static::openCollapsableBar('bottomtable', 'bottomtableicon', _AM_PUBLISHER_CAT_ITEMS, _AM_PUBLISHER_CAT_ITEMS_DSC);
            $startitem = Request::getInt('startitem');
            // Get the total number of published ITEMS
            $totalitems = $publisher->getHandler('item')->getItemsCount($selCat, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED));
            // creating the items objects that are published
            $itemsObj         = $publisher->getHandler('item')->getAllPublished($publisher->getConfig('idxcat_perpage'), $startitem, $selCat);
            $totalitemsOnPage = count($itemsObj);
            $allcats          = $publisher->getHandler('category')->getObjects(null, true);
            echo "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
            echo '<tr>';
            echo "<td width='40' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ITEMID . '</strong></td>';
            echo "<td width='20%' class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMCOLNAME . '</strong></td>';
            echo "<td class='bg3' align='left'><strong>" . _AM_PUBLISHER_ITEMDESC . '</strong></td>';
            echo "<td width='90' class='bg3' align='center'><strong>" . _AM_PUBLISHER_CREATED . '</strong></td>';
            echo "<td width='60' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . '</strong></td>';
            echo '</tr>';
            if ($totalitems > 0) {
                for ($i = 0; $i < $totalitemsOnPage; ++$i) {
                    $categoryObj = $allcats[$itemsObj[$i]->categoryid()];
                    $modify      = "<a href='item.php?op=mod&amp;itemid="
                                   . $itemsObj[$i]->itemid()
                                   . "'><img src='"
                                   . XOOPS_URL
                                   . '/modules/'
                                   . $publisher->getModule()->dirname()
                                   . "/assets/images/links/edit.gif' title='"
                                   . _AM_PUBLISHER_EDITITEM
                                   . "' alt='"
                                   . _AM_PUBLISHER_EDITITEM
                                   . "' /></a>";
                    $delete      = "<a href='item.php?op=del&amp;itemid="
                                   . $itemsObj[$i]->itemid()
                                   . "'><img src='"
                                   . XOOPS_URL
                                   . '/modules/'
                                   . $publisher->getModule()->dirname()
                                   . "/assets/images/links/delete.png' title='"
                                   . _AM_PUBLISHER_DELETEITEM
                                   . "' alt='"
                                   . _AM_PUBLISHER_DELETEITEM
                                   . "'/></a>";
                    echo '<tr>';
                    echo "<td class='head' align='center'>" . $itemsObj[$i]->itemid() . '</td>';
                    echo "<td class='even' align='left'>" . $categoryObj->name() . '</td>';
                    echo "<td class='even' align='left'>" . $itemsObj[$i]->getitemLink() . '</td>';
                    echo "<td class='even' align='center'>" . $itemsObj[$i]->getDatesub('s') . '</td>';
                    echo "<td class='even' align='center'> $modify $delete </td>";
                    echo '</tr>';
                }
            } else {
                $itemid = -1;
                echo '<tr>';
                echo "<td class='head' align='center' colspan= '7'>" . _AM_PUBLISHER_NOITEMS . '</td>';
                echo '</tr>';
            }
            echo "</table>\n";
            echo "<br>\n";
            $parentid         = Request::getInt('parentid', 0, 'GET');
            $pagenavExtraArgs = "op=mod&categoryid=$selCat&parentid=$parentid";
            xoops_load('XoopsPageNav');
            $pagenav = new XoopsPageNav($totalitems, $publisher->getConfig('idxcat_perpage'), $startitem, 'startitem', $pagenavExtraArgs);
            echo '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';
            echo "<input type='button' name='button' onclick=\"location='item.php?op=mod&categoryid=" . $selCat . "'\" value='" . _AM_PUBLISHER_CREATEITEM . "'>&nbsp;&nbsp;";
            echo '</div>';
        }
        //end of fx2024 code
    }


    //======================== FUNCTIONS =================================

    /**
     * Includes scripts in HTML header
     *
     * @return void
     */
    public static function cpHeader()
    {
        xoops_cp_header();

        //cannot use xoTheme, some conflit with admin gui
        echo '<link type="text/css" href="' . XOOPS_URL . '/modules/system/css/ui/' . xoops_getModuleOption('jquery_theme', 'system') . '/ui.all.css" rel="stylesheet" />
    <link type="text/css" href="' . PUBLISHER_URL . '/assets/css/publisher.css" rel="stylesheet" />
    <script type="text/javascript" src="' . PUBLISHER_URL . '/assets/js/funcs.js"></script>
    <script type="text/javascript" src="' . PUBLISHER_URL . '/assets/js/cookies.js"></script>
    <script type="text/javascript" src="' . XOOPS_URL . '/browse.php?Frameworks/jquery/jquery.js"></script>
    <!-- <script type="text/javascript" src="' . XOOPS_URL . '/browse.php?Frameworks/jquery/jquery-migrate-1.2.1.js"></script> -->
    <script type="text/javascript" src="' . XOOPS_URL . '/browse.php?Frameworks/jquery/plugins/jquery.ui.js"></script>
    <script type="text/javascript" src="' . PUBLISHER_URL . '/assets/js/ajaxupload.3.9.js"></script>
    <script type="text/javascript" src="' . PUBLISHER_URL . '/assets/js/publisher.js"></script>
    ';
    }

    /**
     * Default sorting for a given order
     *
     * @param  string $sort
     * @return string
     */
    public static function getOrderBy($sort)
    {
        if ($sort === 'datesub') {
            return 'DESC';
        } elseif ($sort === 'counter') {
            return 'DESC';
        } elseif ($sort === 'weight') {
            return 'ASC';
        } elseif ($sort === 'votes') {
            return 'DESC';
        } elseif ($sort === 'rating') {
            return 'DESC';
        } elseif ($sort === 'comments') {
            return 'DESC';
        }

        return null;
    }

    /**
     * @credits Thanks to Mithandir
     * @param  string $str
     * @param  int    $start
     * @param  int    $length
     * @param  string $trimMarker
     * @return string
     */
    public static function substr($str, $start, $length, $trimMarker = '...')
    {
        // if the string is empty, let's get out ;-)
        if ($str == '') {
            return $str;
        }

        // reverse a string that is shortened with '' as trimmarker
        $reversedString = strrev(xoops_substr($str, $start, $length, ''));

        // find first space in reversed string
        $positionOfSpace = strpos($reversedString, ' ', 0);

        // truncate the original string to a length of $length
        // minus the position of the last space
        // plus the length of the $trimMarker
        $truncatedString = xoops_substr($str, $start, $length - $positionOfSpace + strlen($trimMarker), $trimMarker);

        return $truncatedString;
    }

    /**
     * @param  string $document
     * @return mixed
     */
    public static function html2text($document)
    {
        // PHP Manual:: function preg_replace
        // $document should contain an HTML document.
        // This will remove HTML tags, javascript sections
        // and white space. It will also convert some
        // common HTML entities to their text equivalent.
        // Credits : newbb2
        $search = array(
            "'<script[^>]*?>.*?</script>'si", // Strip out javascript
            "'<img.*?/>'si", // Strip out img tags
            "'<[\/\!]*?[^<>]*?>'si", // Strip out HTML tags
            "'([\r\n])[\s]+'", // Strip out white space
            "'&(quot|#34);'i", // Replace HTML entities
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i"
        ); // evaluate as php

        $replace = array(
            '',
            '',
            '',
            "\\1",
            '"',
            '&',
            '<',
            '>',
            ' ',
            chr(161),
            chr(162),
            chr(163),
            chr(169)
        );

        $text = preg_replace($search, $replace, $document);

        preg_replace_callback('/&#(\d+);/', function ($matches) {
            return chr($matches[1]);
        }, $document);

        return $text;
        //<?php
    }

    /**
     * @return array
     */
    public static function getAllowedImagesTypes()
    {
        return array('jpg/jpeg', 'image/bmp', 'image/gif', 'image/jpeg', 'image/jpg', 'image/x-png', 'image/png', 'image/pjpeg');
    }

    /**
     * @param  bool $withLink
     * @return string
     */
    public static function moduleHome($withLink = true)
    {
        $publisher = PublisherPublisher::getInstance();

        if (!$publisher->getConfig('format_breadcrumb_modname')) {
            return '';
        }

        if (!$withLink) {
            return $publisher->getModule()->getVar('name');
        } else {
            return '<a href="' . PUBLISHER_URL . '/">' . $publisher->getModule()->getVar('name') . '</a>';
        }
    }

    /**
     * Copy a file, or a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.0
     * @param  string $source The source
     * @param  string $dest   The destination
     * @return bool   Returns true on success, false on failure
     */
    public static function copyr($source, $dest)
    {
        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            // Deep copy directories
            if (($dest !== "$source/$entry") && is_dir("$source/$entry")) {
                static::copyr("$source/$entry", "$dest/$entry");
            } else {
                copy("$source/$entry", "$dest/$entry");
            }
        }

        // Clean up
        $dir->close();

        return true;
    }

    /**
     * .* @credits Thanks to the NewBB2 Development Team
     * @param  string $item
     * @param  bool   $getStatus
     * @return bool|int|string
     */
    public static function &getPathStatus($item, $getStatus = false)
    {
        $path = '';
        if ('root' !== $item) {
            $path = $item;
        }

        $thePath = static::getUploadDir(true, $path);

        if (empty($thePath)) {
            return false;
        }
        if (is_writable($thePath)) {
            $pathCheckResult = 1;
            $pathStatus      = _AM_PUBLISHER_AVAILABLE;
        } elseif (!@is_dir($thePath)) {
            $pathCheckResult = -1;
            $pathStatus      = _AM_PUBLISHER_NOTAVAILABLE . " <a href='" . PUBLISHER_ADMIN_URL . "/index.php?op=createdir&amp;path={$item}'>" . _AM_PUBLISHER_CREATETHEDIR . '</a>';
        } else {
            $pathCheckResult = -2;
            $pathStatus      = _AM_PUBLISHER_NOTWRITABLE . " <a href='" . PUBLISHER_ADMIN_URL . "/index.php?op=setperm&amp;path={$item}'>" . _AM_PUBLISHER_SETMPERM . '</a>';
        }
        if (!$getStatus) {
            return $pathStatus;
        } else {
            return $pathCheckResult;
        }
    }

    /**
     * @credits Thanks to the NewBB2 Development Team
     * @param  string $target
     * @return bool
     */
    public static function mkdir($target)
    {
        // http://www.php.net/manual/en/function.mkdir.php
        // saint at corenova.com
        // bart at cdasites dot com
        if (empty($target) || is_dir($target)) {
            return true; // best case check first
        }

        if (file_exists($target) && !is_dir($target)) {
            return false;
        }

        if (static::mkdir(substr($target, 0, strrpos($target, '/')))) {
            if (!file_exists($target)) {
                $res = mkdir($target, 0777); // crawl back up & create dir tree
                static::chmod($target);

                return $res;
            }
        }
        $res = is_dir($target);

        return $res;
    }

    /**
     * @credits Thanks to the NewBB2 Development Team
     * @param  string $target
     * @param  int    $mode
     * @return bool
     */
    public static function chmod($target, $mode = 0777)
    {
        return @chmod($target, $mode);
    }

    /**
     * @param  bool   $hasPath
     * @param  string $item
     * @return string
     */
    public static function getUploadDir($hasPath = true, $item = '')
    {
        if ('' !== $item) {
            if ($item === 'root') {
                $item = '';
            } else {
                $item .= '/';
            }
        }

        if ($hasPath) {
            return PUBLISHER_UPLOAD_PATH . '/' . $item;
        } else {
            return PUBLISHER_UPLOAD_URL . '/' . $item;
        }
    }

    /**
     * @param  string $item
     * @param  bool   $hasPath
     * @return string
     */
    public static function getImageDir($item = '', $hasPath = true)
    {
        if ($item) {
            $item = "images/{$item}";
        } else {
            $item = 'images';
        }

        return static::getUploadDir($hasPath, $item);
    }

    /**
     * @param  array $errors
     * @return string
     */
    public static function formatErrors($errors = array())
    {
        $ret = '';
        foreach ($errors as $key => $value) {
            $ret .= '<br> - ' . $value;
        }

        return $ret;
    }

    /**
     * Checks if a user is admin of Publisher
     *
     * @return boolean
     */
    public static function userIsAdmin()
    {
        $publisher = PublisherPublisher::getInstance();

        static $publisherIsAdmin;

        if (isset($publisherIsAdmin)) {
            return $publisherIsAdmin;
        }

        if (!$GLOBALS['xoopsUser']) {
            $publisherIsAdmin = false;
        } else {
            $publisherIsAdmin = $GLOBALS['xoopsUser']->isAdmin($publisher->getModule()->getVar('mid'));
        }

        return $publisherIsAdmin;
    }

    /**
     * Check is current user is author of a given article
     *
     * @param  XoopsObject $itemObj
     * @return bool
     */
    public static function userIsAuthor($itemObj)
    {
        return (is_object($GLOBALS['xoopsUser']) && is_object($itemObj) && ($GLOBALS['xoopsUser']->uid() == $itemObj->uid()));
    }

    /**
     * Check is current user is moderator of a given article
     *
     * @param  XoopsObject $itemObj
     * @return bool
     */
    public static function userIsModerator($itemObj)
    {
        $publisher         = PublisherPublisher::getInstance();
        $categoriesGranted = $publisher->getHandler('permission')->getGrantedItems('category_moderation');

        return (is_object($itemObj) && in_array($itemObj->categoryid(), $categoriesGranted));
    }

    /**
     * Saves permissions for the selected category
     *
     * @param  array   $groups     : group with granted permission
     * @param  integer $categoryId : categoryid on which we are setting permissions
     * @param  string  $permName   : name of the permission
     * @return boolean : TRUE if the no errors occured
     */
    public static function saveCategoryPermissions($groups, $categoryId, $permName)
    {
        $publisher = PublisherPublisher::getInstance();

        $result = true;

        $moduleId     = $publisher->getModule()->getVar('mid');
        /* @var  $gpermHandler XoopsGroupPermHandler */
        $gpermHandler = xoops_getHandler('groupperm');
        // First, if the permissions are already there, delete them
        $gpermHandler->deleteByModule($moduleId, $permName, $categoryId);

        // Save the new permissions
        if (count($groups) > 0) {
            foreach ($groups as $groupId) {
                $gpermHandler->addRight($permName, $categoryId, $groupId, $moduleId);
            }
        }

        return $result;
    }

    /**
     * @param  string $tablename
     * @param  string $iconname
     * @param  string $tabletitle
     * @param  string $tabledsc
     * @param  bool   $open
     * @return void
     */
    public static function openCollapsableBar($tablename = '', $iconname = '', $tabletitle = '', $tabledsc = '', $open = true)
    {
        $image   = 'open12.gif';
        $display = 'none';
        if ($open) {
            $image   = 'close12.gif';
            $display = 'block';
        }

        echo "<h3 style=\"color: #2F5376; font-weight: bold; font-size: 14px; margin: 6px 0 0 0; \"><a href='javascript:;' onclick=\"toggle('" . $tablename . "'); toggleIcon('" . $iconname . "')\">";
        echo "<img id='" . $iconname . "' src='" . PUBLISHER_URL . '/assets/images/links/' . $image . "' alt='' /></a>&nbsp;" . $tabletitle . '</h3>';
        echo "<div id='" . $tablename . "' style='display: " . $display . ";'>";
        if ($tabledsc != '') {
            echo '<span style="color: #567; margin: 3px 0 12px 0; font-size: small; display: block; ">' . $tabledsc . '</span>';
        }
    }

    /**
     * @param  string $name
     * @param  string $icon
     * @return void
     */
    public static function closeCollapsableBar($name, $icon)
    {
        echo '</div>';

        $urls = static::getCurrentUrls();
        $path = $urls['phpself'];

        $cookieName = $path . '_publisher_collaps_' . $name;
        $cookieName = str_replace('.', '_', $cookieName);
        $cookie     = static::getCookieVar($cookieName, '');

        if ($cookie === 'none') {
            echo '
        <script type="text/javascript"><!--
        toggle("' . $name . '"); toggleIcon("' . $icon . '");
        //-->
        </script>
        ';
        }
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  int    $time
     * @return void
     */
    public static function setCookieVar($name, $value, $time = 0)
    {
        if ($time == 0) {
            $time = time() + 3600 * 24 * 365;
        }
        setcookie($name, $value, $time, '/');
    }

    /**
     * @param  string $name
     * @param  string $default
     * @return string
     */
    public static function getCookieVar($name, $default = '')
    {
        //    if (isset($_COOKIE[$name]) && ($_COOKIE[$name] > '')) {
        //        return $_COOKIE[$name];
        //    } else {
        //        return $default;
        //    }
        return Request::getString('name', $default, 'COOKIE');
    }

    /**
     * @return array
     */
    public static function getCurrentUrls()
    {
        $http = strpos(XOOPS_URL, 'https://') === false ? 'http://' : 'https://';
        //    $phpself     = $_SERVER['PHP_SELF'];
        //    $httphost    = $_SERVER['HTTP_HOST'];
        //    $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $phpself     = Request::getString('PHP_SELF', '', 'SERVER');
        $httphost    = Request::getString('HTTP_HOST', '', 'SERVER');
        $querystring = Request::getString('QUERY_STRING', '', 'SERVER');

        if ($querystring != '') {
            $querystring = '?' . $querystring;
        }

        $currenturl = $http . $httphost . $phpself . $querystring;

        $urls                = array();
        $urls['http']        = $http;
        $urls['httphost']    = $httphost;
        $urls['phpself']     = $phpself;
        $urls['querystring'] = $querystring;
        $urls['full']        = $currenturl;

        return $urls;
    }

    /**
     * @return string
     */
    public static function getCurrentPage()
    {
        $urls = static::getCurrentUrls();

        return $urls['full'];
    }

    /**
     * @param  null|PublisherCategory $categoryObj
     * @param  int|array              $selectedid
     * @param  int                    $level
     * @param  string                 $ret
     * @return string
     */
    public static function addCategoryOption(PublisherCategory $categoryObj, $selectedid = 0, $level = 0, $ret = '')
    {
        $publisher = PublisherPublisher::getInstance();

        $spaces = '';
        for ($j = 0; $j < $level; ++$j) {
            $spaces .= '--';
        }

        $ret .= "<option value='" . $categoryObj->categoryid() . "'";
        if (is_array($selectedid) && in_array($categoryObj->categoryid(), $selectedid)) {
            $ret .= ' selected';
        } elseif ($categoryObj->categoryid() == $selectedid) {
            $ret .= ' selected';
        }
        $ret .= '>' . $spaces . $categoryObj->name() . "</option>\n";

        $subCategoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $categoryObj->categoryid());
        if (count($subCategoriesObj) > 0) {
            ++$level;
            foreach ($subCategoriesObj as $catID => $subCategoryObj) {
                $ret .= static::addCategoryOption($subCategoryObj, $selectedid, $level);
            }
        }

        return $ret;
    }

    /**
     * @param  int    $selectedid
     * @param  int    $parentcategory
     * @param  bool   $allCatOption
     * @param  string $selectname
     * @return string
     */
    public static function createCategorySelect($selectedid = 0, $parentcategory = 0, $allCatOption = true, $selectname = 'options[0]')
    {
        $publisher = PublisherPublisher::getInstance();

        $selectedid = explode(',', $selectedid);

        $ret = "<select name='" . $selectname . "[]' multiple='multiple' size='10'>";
        if ($allCatOption) {
            $ret .= "<option value='0'";
            if (in_array(0, $selectedid)) {
                $ret .= ' selected';
            }
            $ret .= '>' . _MB_PUBLISHER_ALLCAT . '</option>';
        }

        // Creating category objects
        $categoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $parentcategory);

        if (count($categoriesObj) > 0) {
            foreach ($categoriesObj as $catID => $categoryObj) {
                $ret .= static::addCategoryOption($categoryObj, $selectedid);
            }
        }
        $ret .= '</select>';

        return $ret;
    }

    /**
     * @param  int  $selectedid
     * @param  int  $parentcategory
     * @param  bool $allCatOption
     * @return string
     */
    public static function createCategoryOptions($selectedid = 0, $parentcategory = 0, $allCatOption = true)
    {
        $publisher = PublisherPublisher::getInstance();

        $ret = '';
        if ($allCatOption) {
            $ret .= "<option value='0'";
            $ret .= '>' . _MB_PUBLISHER_ALLCAT . "</option>\n";
        }

        // Creating category objects
        $categoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $parentcategory);
        if (count($categoriesObj) > 0) {
            foreach ($categoriesObj as $catID => $categoryObj) {
                $ret .= static::addCategoryOption($categoryObj, $selectedid);
            }
        }

        return $ret;
    }

    /**
     * @param  array  $errArray
     * @param  string $reseturl
     * @return void
     */
    public static function renderErrors(&$errArray, $reseturl = '')
    {
        if (is_array($errArray) && count($errArray) > 0) {
            echo '<div id="readOnly" class="errorMsg" style="border:1px solid #D24D00; background:#FEFECC url('
                 . PUBLISHER_URL
                 . '/assets/images/important-32.png) no-repeat 7px 50%;color:#333;padding-left:45px;">';

            echo '<h4 style="text-align:left;margin:0; padding-top:0;">' . _AM_PUBLISHER_MSG_SUBMISSION_ERR;

            if ($reseturl) {
                echo ' <a href="' . $reseturl . '">[' . _AM_PUBLISHER_TEXT_SESSION_RESET . ']</a>';
            }

            echo '</h4><ul>';

            foreach ($errArray as $key => $error) {
                if (is_array($error)) {
                    foreach ($error as $err) {
                        echo '<li><a href="#' . $key . '" onclick="var e = xoopsGetElementById(\'' . $key . '\'); e.focus();">' . htmlspecialchars($err) . '</a></li>';
                    }
                } else {
                    echo '<li><a href="#' . $key . '" onclick="var e = xoopsGetElementById(\'' . $key . '\'); e.focus();">' . htmlspecialchars($error) . '</a></li>';
                }
            }
            echo '</ul></div><br>';
        }
    }

    /**
     * Generate publisher URL
     *
     * @param  string $page
     * @param  array  $vars
     * @param  bool   $encodeAmp
     * @return string
     *
     * @credit : xHelp module, developped by 3Dev
     */
    public static function makeUri($page, $vars = array(), $encodeAmp = true)
    {
        $joinStr = '';

        $amp = ($encodeAmp ? '&amp;' : '&');

        if (!count($vars)) {
            return $page;
        }

        $qs = '';
        foreach ($vars as $key => $value) {
            $qs      .= $joinStr . $key . '=' . $value;
            $joinStr = $amp;
        }

        return $page . '?' . $qs;
    }

    /**
     * @param  string $subject
     * @return string
     */
    public static function tellAFriend($subject = '')
    {
        if (false !== strpos($subject, '%')) {
            $subject = rawurldecode($subject);
        }

        $targetUri = XOOPS_URL . Request::getString('REQUEST_URI', '', 'SERVER');

        return XOOPS_URL . '/modules/tellafriend/index.php?target_uri=' . rawurlencode($targetUri) . '&amp;subject=' . rawurlencode($subject);
    }

    /**
     * @param  bool        $another
     * @param  bool        $withRedirect
     * @param              $itemObj
     * @return bool|string
     */
    public static function uploadFile($another = false, $withRedirect = true, &$itemObj)
    {
        include_once PUBLISHER_ROOT_PATH . '/class/uploader.php';

        //    global $publisherIsAdmin;
        $publisher = PublisherPublisher::getInstance();

        $itemId  = Request::getInt('itemid', 0, 'POST');
        $uid     = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->uid() : 0;
        $session = PublisherSession::getInstance();
        $session->set('publisher_file_filename', Request::getString('item_file_name', '', 'POST'));
        $session->set('publisher_file_description', Request::getString('item_file_description', '', 'POST'));
        $session->set('publisher_file_status', Request::getInt('item_file_status', 1, 'POST'));
        $session->set('publisher_file_uid', $uid);
        $session->set('publisher_file_itemid', $itemId);

        if (!is_object($itemObj)) {
            $itemObj = $publisher->getHandler('item')->get($itemId);
        }

        $fileObj = $publisher->getHandler('file')->create();
        $fileObj->setVar('name', Request::getString('item_file_name', '', 'POST'));
        $fileObj->setVar('description', Request::getString('item_file_description', '', 'POST'));
        $fileObj->setVar('status', Request::getInt('item_file_status', 1, 'POST'));
        $fileObj->setVar('uid', $uid);
        $fileObj->setVar('itemid', $itemObj->getVar('itemid'));
        $fileObj->setVar('datesub', time());

        // Get available mimetypes for file uploading
        $allowedMimetypes = $publisher->getHandler('mimetype')->getArrayByType();
        // TODO : display the available mimetypes to the user
        $errors = array();
        if ($publisher->getConfig('perm_upload') && is_uploaded_file($_FILES['item_upload_file']['tmp_name'])) {
            if (!$ret = $fileObj->checkUpload('item_upload_file', $allowedMimetypes, $errors)) {
                $errorstxt = implode('<br>', $errors);

                $message = sprintf(_CO_PUBLISHER_MESSAGE_FILE_ERROR, $errorstxt);
                if ($withRedirect) {
                    redirect_header('file.php?op=mod&itemid=' . $itemId, 5, $message);
                } else {
                    return $message;
                }
            }
        }

        // Storing the file
        if (!$fileObj->store($allowedMimetypes)) {
            //        if ($withRedirect) {
            //            redirect_header("file.php?op=mod&itemid=" . $fileObj->itemid(), 3, _CO_PUBLISHER_FILEUPLOAD_ERROR . static::formatErrors($fileObj->getErrors()));
            //            exit;
            //        }
            try {
                if ($withRedirect) {
                    throw new Exception(_CO_PUBLISHER_FILEUPLOAD_ERROR . static::formatErrors($fileObj->getErrors()));
                }
            } catch (Exception $e) {
                redirect_header('file.php?op=mod&itemid=' . $fileObj->itemid(), 3, _CO_PUBLISHER_FILEUPLOAD_ERROR . static::formatErrors($fileObj->getErrors()));
            }
            //    } else {
            //        return _CO_PUBLISHER_FILEUPLOAD_ERROR . static::formatErrors($fileObj->getErrors());
        }

        if ($withRedirect) {
            $redirectPage = $another ? 'file.php' : 'item.php';
            redirect_header($redirectPage . '?op=mod&itemid=' . $fileObj->itemid(), 2, _CO_PUBLISHER_FILEUPLOAD_SUCCESS);
        } else {
            return true;
        }

        return null;
    }

    /**
     * @return string
     */
    public static function newFeatureTag()
    {
        $ret = '<span style="padding-right: 4px; font-weight: bold; color: red;">' . _CO_PUBLISHER_NEW_FEATURE . '</span>';

        return $ret;
    }

    /**
     * Smarty truncate_tagsafe modifier plugin
     *
     * Type:     modifier<br>
     * Name:     truncate_tagsafe<br>
     * Purpose:  Truncate a string to a certain length if necessary,
     *           optionally splitting in the middle of a word, and
     *           appending the $etc string or inserting $etc into the middle.
     *           Makes sure no tags are left half-open or half-closed
     *           (e.g. "Banana in a <a...")
     * @author   Monte Ohrt <monte at ohrt dot com>, modified by Amos Robinson
     *           <amos dot robinson at gmail dot com>
     * @param string
     * @param integer
     * @param string
     * @param boolean
     * @param boolean
     * @return string
     */
    public static function truncateTagSafe($string, $length = 80, $etc = '...', $breakWords = false)
    {
        if ($length == 0) {
            return '';
        }

        if (strlen($string) > $length) {
            $length -= strlen($etc);
            if (!$breakWords) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
                $string = preg_replace('/<[^>]*$/', '', $string);
                $string = static::closeTags($string);
            }

            return $string . $etc;
        } else {
            return $string;
        }
    }

    /**
     * @author   Monte Ohrt <monte at ohrt dot com>, modified by Amos Robinson
     *           <amos dot robinson at gmail dot com>
     * @param  string $string
     * @return string
     */
    public static function closeTags($string)
    {
        // match opened tags
        if (preg_match_all('/<([a-z\:\-]+)[^\/]>/', $string, $startTags)) {
            $startTags = $startTags[1];
            // match closed tags
            if (preg_match_all('/<\/([a-z]+)>/', $string, $endTags)) {
                $completeTags = array();
                $endTags      = $endTags[1];

                foreach ($startTags as $key => $val) {
                    $posb = array_search($val, $endTags);
                    if (is_int($posb)) {
                        unset($endTags[$posb]);
                    } else {
                        $completeTags[] = $val;
                    }
                }
            } else {
                $completeTags = $startTags;
            }

            $completeTags = array_reverse($completeTags);
            $elementCount = count($completeTags);
            for ($i = 0; $i < $elementCount; ++$i) {
                $string .= '</' . $completeTags[$i] . '>';
            }
        }

        return $string;
    }

    /**
     * @param  int $itemId
     * @return string
     */
    public static function ratingBar($itemId)
    {
        $publisher       = PublisherPublisher::getInstance();
        $ratingUnitWidth = 30;
        $units           = 5;

        $criteria   = new Criteria('itemid', $itemId);
        $ratingObjs = $publisher->getHandler('rating')->getObjects($criteria);
        unset($criteria);

        $uid           = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        $count         = count($ratingObjs);
        $currentRating = 0;
        $voted         = false;
        $ip            = getenv('REMOTE_ADDR');
        $rating1       = $rating2 = $ratingWidth = 0;

        foreach ($ratingObjs as $ratingObj) {
            $currentRating += $ratingObj->getVar('rate');
            if ($ratingObj->getVar('ip') == $ip || ($uid > 0 && $uid == $ratingObj->getVar('uid'))) {
                $voted = true;
            }
        }

        $tense = $count == 1 ? _MD_PUBLISHER_VOTE_VOTE : _MD_PUBLISHER_VOTE_VOTES; //plural form votes/vote

        // now draw the rating bar
        if ($count != 0) {
            $ratingWidth = number_format($currentRating / $count, 2) * $ratingUnitWidth;
            $rating1     = number_format($currentRating / $count, 1);
            $rating2     = number_format($currentRating / $count, 2);
        }
        $groups       = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        /* @var $gpermHandler XoopsGroupPermHandler  */
        $gpermHandler = $publisher->getHandler('groupperm');

        if (!$gpermHandler->checkRight('global', PublisherConstants::PUBLISHER_RATE, $groups, $publisher->getModule()->getVar('mid'))) {
            $staticRater   = array();
            $staticRater[] .= "\n" . '<div class="publisher_ratingblock">';
            $staticRater[] .= '<div id="unit_long' . $itemId . '">';
            $staticRater[] .= '<div id="unit_ul' . $itemId . '" class="publisher_unit-rating" style="width:' . $ratingUnitWidth * $units . 'px;">';
            $staticRater[] .= '<div class="publisher_current-rating" style="width:' . $ratingWidth . 'px;">' . _MD_PUBLISHER_VOTE_RATING . ' ' . $rating2 . '/' . $units . '</div>';
            $staticRater[] .= '</div>';
            $staticRater[] .= '<div class="publisher_static">'
                              . _MD_PUBLISHER_VOTE_RATING
                              . ': <strong> '
                              . $rating1
                              . '</strong>/'
                              . $units
                              . ' ('
                              . $count
                              . ' '
                              . $tense
                              . ') <br><em>'
                              . _MD_PUBLISHER_VOTE_DISABLE
                              . '</em></div>';
            $staticRater[] .= '</div>';
            $staticRater[] .= '</div>' . "\n\n";

            return implode("\n", $staticRater);
        } else {
            $rater = '';
            $rater .= '<div class="publisher_ratingblock">';
            $rater .= '<div id="unit_long' . $itemId . '">';
            $rater .= '<div id="unit_ul' . $itemId . '" class="publisher_unit-rating" style="width:' . $ratingUnitWidth * $units . 'px;">';
            $rater .= '<div class="publisher_current-rating" style="width:' . $ratingWidth . 'px;">' . _MD_PUBLISHER_VOTE_RATING . ' ' . $rating2 . '/' . $units . '</div>';

            for ($ncount = 1; $ncount <= $units; ++$ncount) { // loop from 1 to the number of units
                if (!$voted) { // if the user hasn't yet voted, draw the voting stars
                    $rater .= '<div><a href="'
                              . PUBLISHER_URL
                              . '/rate.php?itemid='
                              . $itemId
                              . '&amp;rating='
                              . $ncount
                              . '" title="'
                              . $ncount
                              . ' '
                              . _MD_PUBLISHER_VOTE_OUTOF
                              . ' '
                              . $units
                              . '" class="publisher_r'
                              . $ncount
                              . '-unit rater" rel="nofollow">'
                              . $ncount
                              . '</a></div>';
                }
            }

            $ncount = 0; // resets the count
            $rater  .= '  </div>';
            $rater  .= '  <div';

            if ($voted) {
                $rater .= ' class="publisher_voted"';
            }

            $rater .= '>' . _MD_PUBLISHER_VOTE_RATING . ': <strong> ' . $rating1 . '</strong>/' . $units . ' (' . $count . ' ' . $tense . ')';
            $rater .= '  </div>';
            $rater .= '</div>';
            $rater .= '</div>';

            return $rater;
        }
    }

    /**
     * @param  array $allowedEditors
     * @return array
     */
    public static function getEditors($allowedEditors = null)
    {
        $ret    = array();
        $nohtml = false;
        xoops_load('XoopsEditorHandler');
        $editorHandler = XoopsEditorHandler::getInstance();
        $editors       = array_flip($editorHandler->getList());//$editorHandler->getList($nohtml);
        foreach ($editors as $name => $title) {
            $key = static::stringToInt($name);
            if (is_array($allowedEditors)) {
                //for submit page
                if (in_array($key, $allowedEditors)) {
                    $ret[] = $name;
                }
            } else {
                //for admin permissions page
                $ret[$key]['name']  = $name;
                $ret[$key]['title'] = $title;
            }
        }

        return $ret;
    }

    /**
     * @param  string $string
     * @param  int    $length
     * @return int
     */
    public static function stringToInt($string = '', $length = 5)
    {
        $final  = '';
        $string = substr(md5($string), $length);
        for ($i = 0; $i < $length; ++$i) {
            $final .= (int)$string[$i];
        }

        return (int)$final;
    }

    /**
     * @param  string $item
     * @return string
     */
    public static function convertCharset($item)
    {
        if (_CHARSET !== 'windows-1256') {
            return utf8_encode($item);
        }

        if ($unserialize == unserialize($item)) {
            foreach ($unserialize as $key => $value) {
                $unserialize[$key] = @iconv('windows-1256', 'UTF-8', $value);
            }
            $serialize = serialize($unserialize);

            return $serialize;
        } else {
            return @iconv('windows-1256', 'UTF-8', $item);
        }
    }

    /**
     *
     * Verifies XOOPS version meets minimum requirements for this module
     * @static
     * @param XoopsModule $module
     *
     * @return bool true if meets requirements, false if not
     */
    public static function checkVerXoops(XoopsModule $module)
    {
        xoops_loadLanguage('admin', $module->dirname());
        //check for minimum XOOPS version
        $currentVer  = substr(XOOPS_VERSION, 6); // get the numeric part of string
        $currArray   = explode('.', $currentVer);
        $requiredVer = '' . $module->getInfo('min_xoops'); //making sure it's a string
        $reqArray    = explode('.', $requiredVer);
        $success     = true;
        foreach ($reqArray as $k => $v) {
            if (isset($currArray[$k])) {
                if ($currArray[$k] > $v) {
                    break;
                } elseif ($currArray[$k] == $v) {
                    continue;
                } else {
                    $success = false;
                    break;
                }
            } else {
                if ((int)$v > 0) { // handles things like x.x.x.0_RC2
                    $success = false;
                    break;
                }
            }
        }

        if (!$success) {
            $module->setErrors(sprintf(_AM_PUBLISHER_ERROR_BAD_XOOPS, $requiredVer, $currentVer));
        }

        return $success;
    }

    /**
     *
     * Verifies PHP version meets minimum requirements for this module
     * @static
     * @param XoopsModule $module
     *
     * @return bool true if meets requirements, false if not
     */
    public static function checkVerPhp(XoopsModule $module)
    {
        xoops_loadLanguage('admin', $module->dirname());
        // check for minimum PHP version
        $success = true;
        $verNum  = PHP_VERSION;
        $reqVer  =& $module->getInfo('min_php');
        if (false !== $reqVer && '' !== $reqVer) {
            if (version_compare($verNum, $reqVer, '<')) {
                $module->setErrors(sprintf(_AM_PUBLISHER_ERROR_BAD_PHP, $reqVer, $verNum));
                $success = false;
            }
        }

        return $success;
    }
}
