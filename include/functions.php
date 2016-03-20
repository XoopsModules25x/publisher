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
 * @version         $Id: functions.php 10661 2013-01-04 19:22:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once __DIR__ . '/common.php';

/**
 * Includes scripts in HTML header
 *
 * @return void
 */
function publisherCpHeader()
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
function publisherGetOrderBy($sort)
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
 * @param  int $start
 * @param  int $length
 * @param  string $trimMarker
 * @return string
 */
function publisherSubstr($str, $start, $length, $trimMarker = '...')
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
function publisherHtml2text($document)
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
        "'&(copy|#169);'i",
        "'&#(\d+);'e"); // evaluate as php

    $replace = array(
        '',
        '',
        '',
        "\\1",
        "\"",
        '&',
        '<',
        '>',
        ' ',
        chr(161),
        chr(162),
        chr(163),
        chr(169),
        "chr(\\1)");

    $text = preg_replace($search, $replace, $document);

    return $text;
    //<?php
}

/**
 * @return array
 */
function publisherGetAllowedImagesTypes()
{
    return array('jpg/jpeg', 'image/bmp', 'image/gif', 'image/jpeg', 'image/jpg', 'image/x-png', 'image/png', 'image/pjpeg');
}

/**
 * @param  bool $withLink
 * @return string
 */
function publisherModuleHome($withLink = true)
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
function publisherCopyr($source, $dest)
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
            publisherCopyr("$source/$entry", "$dest/$entry");
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
 * @param  bool $getStatus
 * @return bool|int|string
 */
function &publisherGetPathStatus($item, $getStatus = false)
{
    $path = '';
    if ('root' !== $item) {
        $path = $item;
    }

    $thePath = publisherGetUploadDir(true, $path);

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
function publisherMkdir($target)
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

    if (publisherMkdir(substr($target, 0, strrpos($target, '/')))) {
        if (!file_exists($target)) {
            $res = mkdir($target, 0777); // crawl back up & create dir tree
            publisherChmod($target);

            return $res;
        }
    }
    $res = is_dir($target);

    return $res;
}

/**
 * @credits Thanks to the NewBB2 Development Team
 * @param  string $target
 * @param  int $mode
 * @return bool
 */
function publisherChmod($target, $mode = 0777)
{
    return @chmod($target, $mode);
}

/**
 * @param  bool $hasPath
 * @param  bool $item
 * @return string
 */
function publisherGetUploadDir($hasPath = true, $item = false)
{
    if ($item) {
        if ($item === 'root') {
            $item = '';
        } else {
            $item .= '/';
        }
    } else {
        $item = '';
    }

    if ($hasPath) {
        return PUBLISHER_UPLOAD_PATH . '/' . $item;
    } else {
        return PUBLISHER_UPLOAD_URL . '/' . $item;
    }
}

/**
 * @param  string $item
 * @param  bool $hasPath
 * @return string
 */
function publisherGetImageDir($item = '', $hasPath = true)
{
    if ($item) {
        $item = "images/{$item}";
    } else {
        $item = 'images';
    }

    return publisherGetUploadDir($hasPath, $item);
}

/**
 * @param  array $errors
 * @return string
 */
function publisherFormatErrors($errors = array())
{
    $ret = '';
    foreach ($errors as $key => $value) {
        $ret .= '<br /> - ' . $value;
    }

    return $ret;
}

/**
 * Checks if a user is admin of Publisher
 *
 * @return boolean
 */
function publisherUserIsAdmin()
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
 * @param  object $itemObj
 * @return bool
 */
function publisherUserIsAuthor($itemObj)
{
    return (is_object($GLOBALS['xoopsUser']) && is_object($itemObj) && ($GLOBALS['xoopsUser']->uid() == $itemObj->uid()));
}

/**
 * Check is current user is moderator of a given article
 *
 * @param  object $itemObj
 * @return bool
 */
function publisherUserIsModerator($itemObj)
{
    $publisher         = PublisherPublisher::getInstance();
    $categoriesGranted = $publisher->getHandler('permission')->getGrantedItems('category_moderation');

    return (is_object($itemObj) && in_array($itemObj->categoryid(), $categoriesGranted));
}

/**
 * Saves permissions for the selected category
 *
 * @param  array $groups       : group with granted permission
 * @param  integer $categoryId : categoryid on which we are setting permissions
 * @param  string $permName    : name of the permission
 * @return boolean : TRUE if the no errors occured
 */
function publisherSaveCategoryPermissions($groups, $categoryId, $permName)
{
    $publisher = PublisherPublisher::getInstance();

    $result = true;

    $moduleId     = $publisher->getModule()->getVar('mid');
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
 * @param  bool $open
 * @return void
 */
function publisherOpenCollapsableBar($tablename = '', $iconname = '', $tabletitle = '', $tabledsc = '', $open = true)
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
        echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . $tabledsc . '</span>';
    }
}

/**
 * @param  string $name
 * @param  string $icon
 * @return void
 */
function publisherCloseCollapsableBar($name, $icon)
{
    echo '</div>';

    $urls = publisherGetCurrentUrls();
    $path = $urls['phpself'];

    $cookieName = $path . '_publisher_collaps_' . $name;
    $cookieName = str_replace('.', '_', $cookieName);
    $cookie     = publisherGetCookieVar($cookieName, '');

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
 * @param  int $time
 * @return void
 */
function publisherSetCookieVar($name, $value, $time = 0)
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
function publisherGetCookieVar($name, $default = '')
{
    //    if (isset($_COOKIE[$name]) && ($_COOKIE[$name] > '')) {
    //        return $_COOKIE[$name];
    //    } else {
    //        return $default;
    //    }
    return XoopsRequest::getString('name', $default, 'COOKIE');
}

/**
 * @return array
 */
function publisherGetCurrentUrls()
{
    $http = strpos(XOOPS_URL, 'https://') === false ? 'http://' : 'https://';
    //    $phpself     = $_SERVER['PHP_SELF'];
    //    $httphost    = $_SERVER['HTTP_HOST'];
    //    $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    $phpself     = XoopsRequest::getString('PHP_SELF', '', 'SERVER');
    $httphost    = XoopsRequest::getString('HTTP_HOST', '', 'SERVER');
    $querystring = XoopsRequest::getString('QUERY_STRING', '', 'SERVER');

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
function publisherGetCurrentPage()
{
    $urls = publisherGetCurrentUrls();

    return $urls['full'];
}

/**
 * @param  null|PublisherCategory $categoryObj
 * @param  int $selectedid
 * @param  int $level
 * @param  string $ret
 * @return string
 */
function publisherAddCategoryOption(PublisherCategory $categoryObj, $selectedid = 0, $level = 0, $ret = '')
{
    $publisher = PublisherPublisher::getInstance();

    $spaces = '';
    for ($j = 0; $j < $level; ++$j) {
        $spaces .= '--';
    }

    $ret .= "<option value='" . $categoryObj->categoryid() . "'";
    if (is_array($selectedid) && in_array($categoryObj->categoryid(), $selectedid)) {
        $ret .= " selected='selected'";
    } elseif ($categoryObj->categoryid() == $selectedid) {
        $ret .= " selected='selected'";
    }
    $ret .= '>' . $spaces . $categoryObj->name() . "</option>\n";

    $subCategoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $categoryObj->categoryid());
    if (count($subCategoriesObj) > 0) {
        ++$level;
        foreach ($subCategoriesObj as $catID => $subCategoryObj) {
            $ret .= publisherAddCategoryOption($subCategoryObj, $selectedid, $level);
        }
    }

    return $ret;
}

/**
 * @param  int $selectedid
 * @param  int $parentcategory
 * @param  bool $allCatOption
 * @param  string $selectname
 * @return string
 */
function publisherCreateCategorySelect($selectedid = 0, $parentcategory = 0, $allCatOption = true, $selectname = 'options[0]')
{
    $publisher = PublisherPublisher::getInstance();

    $selectedid = explode(',', $selectedid);

    $ret = "<select name='" . $selectname . "[]' multiple='multiple' size='10'>";
    if ($allCatOption) {
        $ret .= "<option value='0'";
        if (in_array(0, $selectedid)) {
            $ret .= " selected='selected'";
        }
        $ret .= '>' . _MB_PUBLISHER_ALLCAT . '</option>';
    }

    // Creating category objects
    $categoriesObj = $publisher->getHandler('category')->getCategories(0, 0, $parentcategory);

    if (count($categoriesObj) > 0) {
        foreach ($categoriesObj as $catID => $categoryObj) {
            $ret .= publisherAddCategoryOption($categoryObj, $selectedid);
        }
    }
    $ret .= '</select>';

    return $ret;
}

/**
 * @param  int $selectedid
 * @param  int $parentcategory
 * @param  bool $allCatOption
 * @return string
 */
function publisherCreateCategoryOptions($selectedid = 0, $parentcategory = 0, $allCatOption = true)
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
            $ret .= publisherAddCategoryOption($categoryObj, $selectedid);
        }
    }

    return $ret;
}

/**
 * @param  array $errArray
 * @param  string $reseturl
 * @return void
 */
function publisherRenderErrors(&$errArray, $reseturl = '')
{
    if (is_array($errArray) && count($errArray) > 0) {
        echo '<div id="readOnly" class="errorMsg" style="border:1px solid #D24D00; background:#FEFECC url(' . PUBLISHER_URL . '/assets/images/important-32.png) no-repeat 7px 50%;color:#333;padding-left:45px;">';

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
        echo '</ul></div><br />';
    }
}

/**
 * Generate publisher URL
 *
 * @param  string $page
 * @param  array $vars
 * @param  bool $encodeAmp
 * @return string
 *
 * @credit : xHelp module, developped by 3Dev
 */
function publisherMakeUri($page, $vars = array(), $encodeAmp = true)
{
    $joinStr = '';

    $amp = ($encodeAmp ? '&amp;' : '&');

    if (!count($vars)) {
        return $page;
    }

    $qs = '';
    foreach ($vars as $key => $value) {
        $qs .= $joinStr . $key . '=' . $value;
        $joinStr = $amp;
    }

    return $page . '?' . $qs;
}

/**
 * @param  string $subject
 * @return string
 */
function publisherTellAFriend($subject = '')
{
    if (false !== strpos($subject, '%')) {
        $subject = rawurldecode($subject);
    }

    $targetUri = XOOPS_URL . XoopsRequest::getString('REQUEST_URI', '', 'SERVER');

    return XOOPS_URL . '/modules/tellafriend/index.php?target_uri=' . rawurlencode($targetUri) . '&amp;subject=' . rawurlencode($subject);
}

/**
 * @param  bool $another
 * @param  bool $withRedirect
 * @param              $itemObj
 * @return bool|string
 */
function publisherUploadFile($another = false, $withRedirect = true, &$itemObj)
{
    include_once PUBLISHER_ROOT_PATH . '/class/uploader.php';

    //    global $publisherIsAdmin;
    $publisher = PublisherPublisher::getInstance();

    $itemId  = XoopsRequest::getInt('itemid', 0, 'POST');
    $uid     = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->uid() : 0;
    $session = PublisherSession::getInstance();
    $session->set('publisher_file_filename', XoopsRequest::getString('item_file_name', '', 'POST'));
    $session->set('publisher_file_description', XoopsRequest::getString('item_file_description', '', 'POST'));
    $session->set('publisher_file_status', XoopsRequest::getInt('item_file_status', 1, 'POST'));
    $session->set('publisher_file_uid', $uid);
    $session->set('publisher_file_itemid', $itemId);

    if (!is_object($itemObj)) {
        $itemObj = $publisher->getHandler('item')->get($itemId);
    }

    $fileObj = $publisher->getHandler('file')->create();
    $fileObj->setVar('name', XoopsRequest::getString('item_file_name', '', 'POST'));
    $fileObj->setVar('description', XoopsRequest::getString('item_file_description', '', 'POST'));
    $fileObj->setVar('status', XoopsRequest::getInt('item_file_status', 1, 'POST'));
    $fileObj->setVar('uid', $uid);
    $fileObj->setVar('itemid', $itemObj->getVar('itemid'));
    $fileObj->setVar('datesub', time());

    // Get available mimetypes for file uploading
    $allowedMimetypes = $publisher->getHandler('mimetype')->getArrayByType();
    // TODO : display the available mimetypes to the user
    $errors = array();
    if ($publisher->getConfig('perm_upload') && is_uploaded_file($_FILES['item_upload_file']['tmp_name'])) {
        if (!$ret = $fileObj->checkUpload('item_upload_file', $allowedMimetypes, $errors)) {
            $errorstxt = implode('<br />', $errors);

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
        //            redirect_header("file.php?op=mod&itemid=" . $fileObj->itemid(), 3, _CO_PUBLISHER_FILEUPLOAD_ERROR . publisherFormatErrors($fileObj->getErrors()));
        //            exit;
        //        }
        try {
            if ($withRedirect) {
                throw new Exception(_CO_PUBLISHER_FILEUPLOAD_ERROR . publisherFormatErrors($fileObj->getErrors()));
            }
        } catch (Exception $e) {
            redirect_header('file.php?op=mod&itemid=' . $fileObj->itemid(), 3, _CO_PUBLISHER_FILEUPLOAD_ERROR . publisherFormatErrors($fileObj->getErrors()));
        }
        //    } else {
        //        return _CO_PUBLISHER_FILEUPLOAD_ERROR . publisherFormatErrors($fileObj->getErrors());
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
function publisherNewFeatureTag()
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
function publisherTruncateTagSafe($string, $length = 80, $etc = '...', $breakWords = false)
{
    if ($length == 0) {
        return '';
    }

    if (strlen($string) > $length) {
        $length -= strlen($etc);
        if (!$breakWords) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            $string = preg_replace('/<[^>]*$/', '', $string);
            $string = publisherCloseTags($string);
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
function publisherCloseTags($string)
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
function publisherRatingBar($itemId)
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
    $gpermHandler = $publisher->getHandler('groupperm');

    if (!$gpermHandler->checkRight('global', PublisherConstants::PUBLISHER_RATE, $groups, $publisher->getModule()->getVar('mid'))) {
        $staticRater = array();
        $staticRater[] .= "\n" . '<div class="publisher_ratingblock">';
        $staticRater[] .= '<div id="unit_long' . $itemId . '">';
        $staticRater[] .= '<div id="unit_ul' . $itemId . '" class="publisher_unit-rating" style="width:' . $ratingUnitWidth * $units . 'px;">';
        $staticRater[] .= '<div class="publisher_current-rating" style="width:' . $ratingWidth . 'px;">' . _MD_PUBLISHER_VOTE_RATING . ' ' . $rating2 . '/' . $units . '</div>';
        $staticRater[] .= '</div>';
        $staticRater[] .= '<div class="publisher_static">' . _MD_PUBLISHER_VOTE_RATING . ': <strong> ' . $rating1 . '</strong>/' . $units . ' (' . $count . ' ' . $tense . ') <br /><em>' . _MD_PUBLISHER_VOTE_DISABLE . '</em></div>';
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
                $rater .= '<div><a href="' . PUBLISHER_URL . '/rate.php?itemid=' . $itemId . '&amp;rating=' . $ncount . '" title="' . $ncount . ' ' . _MD_PUBLISHER_VOTE_OUTOF . ' ' . $units . '" class="publisher_r' . $ncount . '-unit rater" rel="nofollow">' . $ncount . '</a></div>';
            }
        }

        $ncount = 0; // resets the count
        $rater .= '  </div>';
        $rater .= '  <div';

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
function publisherGetEditors($allowedEditors = null)
{
    $ret    = array();
    $nohtml = false;
    xoops_load('XoopsEditorHandler');
    $editorHandler = XoopsEditorHandler::getInstance();
    $editors       = $editorHandler->getList($nohtml);
    foreach ($editors as $name => $title) {
        $key = publisherStringToInt($name);
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
 * @param  int $length
 * @return int
 */
function publisherStringToInt($string = '', $length = 5)
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
function publisherConvertCharset($item)
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
