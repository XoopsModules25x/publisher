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
 * @package         Admin
 * @subpackage      Action
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');

$start = Request::getInt('start', 0, 'GET');
$limit = Request::getInt('limit', Request::getInt('limit', 15, 'GET'), 'POST');

$aSortBy   = [
    'mime_id'    => _AM_PUBLISHER_MIME_ID,
    'mime_name'  => _AM_PUBLISHER_MIME_NAME,
    'mime_ext'   => _AM_PUBLISHER_MIME_EXT,
    'mime_admin' => _AM_PUBLISHER_MIME_ADMIN,
    'mime_user'  => _AM_PUBLISHER_MIME_USER
];
$aOrderBy  = ['ASC' => _AM_PUBLISHER_TEXT_ASCENDING, 'DESC' => _AM_PUBLISHER_TEXT_DESCENDING];
$aLimitBy  = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];
$aSearchBy = ['mime_id' => _AM_PUBLISHER_MIME_ID, 'mime_name' => _AM_PUBLISHER_MIME_NAME, 'mime_ext' => _AM_PUBLISHER_MIME_EXT];

$error = [];

$op = Request::getString('op', 'default', 'GET');

// all post requests should have a valid token
if ('POST' === Request::getMethod() && !$GLOBALS['xoopsSecurity']->check()) {
    redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php?op=manage', 3, _CO_PUBLISHER_BAD_TOKEN);
}

switch ($op) {
    case 'add':
        MimetypesUtility::add();
        break;

    case 'delete':
        MimetypesUtility::delete();
        break;

    case 'edit':
        MimetypesUtility::edit();
        break;

    case 'search':
        MimetypesUtility::search();
        break;

    case 'updateMimeValue':
        MimetypesUtility::updateMimeValue();
        break;

    case 'confirmUpdateMimeValue':
        MimetypesUtility::confirmUpdateMimeValue();
        break;

    case 'clearAddSession':
        MimetypesUtility::clearAddSession();
        break;

    case 'clearEditSession':
        MimetypesUtility::clearEditSession();
        break;

    case 'manage':
    default:
        MimetypesUtility::manage();
        break;
}

/**
 * Class MimetypesUtility
 */
class MimetypesUtility
{
    public static function add()
    {
        $helper = Publisher\Helper::getInstance();
        global $limit, $start;
        $error = [];
        if (!Request::getString('add_mime', '', 'POST')) {
            Publisher\Utility::cpHeader();
            //publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES);

            Publisher\Utility::openCollapsableBar('mimemaddtable', 'mimeaddicon', _AM_PUBLISHER_MIME_ADD_TITLE);

            $session    = Session::getInstance();
            $mimeType   = $session->get('publisher_addMime');
            $mimeErrors = $session->get('publisher_addMimeErr');

            //Display any form errors
            if (false === !$mimeErrors) {
                Publisher\Utility::renderErrors($mimeErrors, Publisher\Utility::makeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', ['op' => 'clearAddSession']));
            }

            if (false === $mimeType) {
                $mimeExt   = '';
                $mimeName  = '';
                $mimeTypes = '';
                $mimeAdmin = 1;
                $mimeUser  = 1;
            } else {
                $mimeExt   = $mimeType['mime_ext'];
                $mimeName  = $mimeType['mime_name'];
                $mimeTypes = $mimeType['mime_types'];
                $mimeAdmin = $mimeType['mime_admin'];
                $mimeUser  = $mimeType['mime_user'];
            }

            // Display add form
            echo "<form action='mimetypes.php?op=add' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_MIME_CREATEF . '</th></tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_EXTF . "</td>
        <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mimeExt' size='5'></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_NAMEF . "</td>
        <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mimeName'></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_TYPEF . "</td>
        <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mimeTypes</textarea></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_ADMINF . "</td>
        <td class='even'>";
            echo "<input type='radio' name='mime_admin' value='1' " . (1 == $mimeAdmin ? 'checked' : '') . '>' . _YES;
            echo "<input type='radio' name='mime_admin' value='0' " . (0 == $mimeAdmin ? 'checked' : '') . '>' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_USERF . "</td>
        <td class='even'>";
            echo "<input type='radio' name='mime_user' value='1'" . (1 == $mimeUser ? 'checked' : '') . '>' . _YES;
            echo "<input type='radio' name='mime_user' value='0'" . (0 == $mimeUser ? 'checked' : '') . '>' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_MANDATORY_FIELD . "</td>
        <td class='even'>
        <input type='submit' name='add_mime' id='add_mime' value='" . _AM_PUBLISHER_BUTTON_SUBMIT . "' class='formButton'>
        <input type='button' name='cancel' value='" . _AM_PUBLISHER_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
        </td>
        </tr>";
            echo '</table></form>';
            // end of add form

            // Find new mimetypes table
            echo "<form action='http://www.filext.com' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_MIME_FINDMIMETYPE . '</th></tr>';

            echo "<tr class='foot'>
        <td colspan='2'><input type='submit' name='find_mime' id='find_mime' value='" . _AM_PUBLISHER_MIME_FINDIT . "' class='formButton'></td>
        </tr>";

            echo '</table></form>';

            Publisher\Utility::closeCollapsableBar('mimeaddtable', 'mimeaddicon');

            xoops_cp_footer();
        } else {
            $hasErrors = false;
            $mimeExt   = Request::getString('mime_ext', '', 'POST');
            $mimeName  = Request::getString('mime_name', '', 'POST');
            $mimeTypes = Request::getText('mime_types', '', 'POST');
            $mimeAdmin = Request::getInt('mime_admin', 0, 'POST');
            $mimeUser  = Request::getInt('mime_user', 0, 'POST');

            //Validate Mimetype entry
            if ('' === trim($mimeExt)) {
                $hasErrors           = true;
                $error['mime_ext'][] = _AM_PUBLISHER_VALID_ERR_MIME_EXT;
            }

            if ('' === trim($mimeName)) {
                $hasErrors            = true;
                $error['mime_name'][] = _AM_PUBLISHER_VALID_ERR_MIME_NAME;
            }

            if ('' === trim($mimeTypes)) {
                $hasErrors             = true;
                $error['mime_types'][] = _AM_PUBLISHER_VALID_ERR_MIME_TYPES;
            }

            if ($hasErrors) {
                $session            = Session::getInstance();
                $mime               = [];
                $mime['mime_ext']   = $mimeExt;
                $mime['mime_name']  = $mimeName;
                $mime['mime_types'] = $mimeTypes;
                $mime['mime_admin'] = $mimeAdmin;
                $mime['mime_user']  = $mimeUser;
                $session->set('publisher_addMime', $mime);
                $session->set('publisher_addMimeErr', $error);
                header('Location: ' . Publisher\Utility::makeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', ['op' => 'add'], false));
            }

            $mimeType = $helper->getHandler('Mimetype')->create();
            $mimeType->setVar('mime_ext', $mimeExt);
            $mimeType->setVar('mime_name', $mimeName);
            $mimeType->setVar('mime_types', $mimeTypes);
            $mimeType->setVar('mime_admin', $mimeAdmin);
            $mimeType->setVar('mime_user', $mimeUser);

            if (!$helper->getHandler('Mimetype')->insert($mimeType)) {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_ADD_MIME_ERROR);
            } else {
                self::clearAddSessionVars();
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
            }
        }
    }

    public static function delete()
    {
        $helper = Publisher\Helper::getInstance();
        global $start, $limit;
        $mimeId = 0;
        if (0 == Request::getInt('id', 0, 'GET')) {
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php', 3, _AM_PUBLISHER_MESSAGE_NO_ID);
        } else {
            $mimeId = Request::getInt('id', 0, 'GET');
        }
        $mimeType = $helper->getHandler('Mimetype')->get($mimeId); // Retrieve mimetype object
        if (!$helper->getHandler('Mimetype')->delete($mimeType, true)) {
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&id=$mimeId&limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_DELETE_MIME_ERROR);
        } else {
            header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
        }
    }

    public static function edit()
    {
        $helper = Publisher\Helper::getInstance();
        global $start, $limit;
        $mimeId    = 0;
        $error     = [];
        $hasErrors = false;
        if (0 == Request::getInt('id', 0, 'GET')) {
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php', 3, _AM_PUBLISHER_MESSAGE_NO_ID);
        } else {
            $mimeId = Request::getInt('id', 0, 'GET');
        }
        $mimeTypeObj = $helper->getHandler('Mimetype')->get($mimeId); // Retrieve mimetype object

        if (!Request::getString('edit_mime', '', 'POST')) {
            $session    = Session::getInstance();
            $mimeType   = $session->get('publisher_editMime_' . $mimeId);
            $mimeErrors = $session->get('publisher_editMimeErr_' . $mimeId);

            // Display header
            Publisher\Utility::cpHeader();
            //publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES . " > " . _AM_PUBLISHER_BUTTON_EDIT);

            Publisher\Utility::openCollapsableBar('mimemedittable', 'mimeediticon', _AM_PUBLISHER_MIME_EDIT_TITLE);

            //Display any form errors
            if (false === !$mimeErrors) {
                Publisher\Utility::renderErrors($mimeErrors, Publisher\Utility::makeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', ['op' => 'clearEditSession', 'id' => $mimeId]));
            }

            if (false === $mimeType) {
                $mimeExt   = $mimeTypeObj->getVar('mime_ext');
                $mimeName  = $mimeTypeObj->getVar('mime_name', 'e');
                $mimeTypes = $mimeTypeObj->getVar('mime_types', 'e');
                $mimeAdmin = $mimeTypeObj->getVar('mime_admin');
                $mimeUser  = $mimeTypeObj->getVar('mime_user');
            } else {
                $mimeExt   = $mimeType['mime_ext'];
                $mimeName  = $mimeType['mime_name'];
                $mimeTypes = $mimeType['mime_types'];
                $mimeAdmin = $mimeType['mime_admin'];
                $mimeUser  = $mimeType['mime_user'];
            }

            // Display edit form
            echo "<form action='mimetypes.php?op=edit&amp;id=" . $mimeId . "' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<input type='hidden' name='limit' value='" . $limit . "'>";
            echo "<input type='hidden' name='start' value='" . $start . "'>";
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_MIME_MODIFYF . '</th></tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_EXTF . "</td>
        <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mimeExt' size='5'></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_NAMEF . "</td>
        <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mimeName'></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_TYPEF . "</td>
        <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mimeTypes</textarea></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_ADMINF . "</td>
        <td class='even'>
        <input type='radio' name='mime_admin' value='1' " . (1 == $mimeAdmin ? 'checked' : '') . '>' . _YES . "
        <input type='radio' name='mime_admin' value='0' " . (0 == $mimeAdmin ? 'checked' : '') . '>' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_USERF . "</td>
        <td class='even'>
        <input type='radio' name='mime_user' value='1' " . (1 == $mimeUser ? 'checked' : '') . '>' . _YES . "
        <input type='radio' name='mime_user' value='0' " . (0 == $mimeUser ? 'checked' : '') . '>' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'></td>
        <td class='even'>
        <input type='submit' name='edit_mime' id='edit_mime' value='" . _AM_PUBLISHER_BUTTON_UPDATE . "' class='formButton'>
        <input type='button' name='cancel' value='" . _AM_PUBLISHER_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton'>
        </td>
        </tr>";
            echo '</table></form>';
            // end of edit form
            Publisher\Utility::closeCollapsableBar('mimeedittable', 'mimeediticon');
            //            xoops_cp_footer();
            require_once __DIR__ . '/admin_footer.php';
        } else {
            $mimeAdmin = 0;
            $mimeUser  = 0;
            if (1 == Request::getInt('mime_admin', 0, 'POST')) {
                $mimeAdmin = 1;
            }
            if (1 == Request::getInt('mime_user', 0, 'POST')) {
                $mimeUser = 1;
            }

            //Validate Mimetype entry
            if ('' === Request::getString('mime_ext', '', 'POST')) {
                $hasErrors           = true;
                $error['mime_ext'][] = _AM_PUBLISHER_VALID_ERR_MIME_EXT;
            }

            if ('' === Request::getString('mime_name', '', 'POST')) {
                $hasErrors            = true;
                $error['mime_name'][] = _AM_PUBLISHER_VALID_ERR_MIME_NAME;
            }

            if ('' === Request::getString('mime_types', '', 'POST')) {
                $hasErrors             = true;
                $error['mime_types'][] = _AM_PUBLISHER_VALID_ERR_MIME_TYPES;
            }

            if ($hasErrors) {
                $session            = Session::getInstance();
                $mime               = [];
                $mime['mime_ext']   = Request::getString('mime_ext', '', 'POST');
                $mime['mime_name']  = Request::getString('mime_name', '', 'POST');
                $mime['mime_types'] = Request::getText('mime_types', '', 'POST');
                $mime['mime_admin'] = $mimeAdmin;
                $mime['mime_user']  = $mimeUser;
                $session->set('publisher_editMime_' . $mimeId, $mime);
                $session->set('publisher_editMimeErr_' . $mimeId, $error);
                header('Location: ' . Publisher\Utility::makeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', ['op' => 'edit', 'id' => $mimeId], false));
            }

            $mimeTypeObj->setVar('mime_ext', Request::getString('mime_ext', '', 'POST'));
            $mimeTypeObj->setVar('mime_name', Request::getString('mime_name', '', 'POST'));
            $mimeTypeObj->setVar('mime_types', Request::getText('mime_types', '', 'POST'));
            $mimeTypeObj->setVar('mime_admin', $mimeAdmin);
            $mimeTypeObj->setVar('mime_user', $mimeUser);

            if (!$helper->getHandler('Mimetype')->insert($mimeTypeObj, true)) {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=edit&id=$mimeId", 3, _AM_PUBLISHER_MESSAGE_EDIT_MIME_ERROR);
            } else {
                self::clearEditSessionVars($mimeId);
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
            }
        }
    }

    public static function manage()
    {
        $helper = Publisher\Helper::getInstance();
        /** @var Publisher\Utility $utility */
        $utility = new Publisher\Utility();
        global $imagearray, $start, $limit, $aSortBy, $aOrderBy, $aLimitBy, $aSearchBy;

        if (Request::getString('deleteMimes', '', 'POST')) {
            $aMimes = Request::getArray('mimes', [], 'POST');

            $crit = new \Criteria('mime_id', '(' . implode($aMimes, ',') . ')', 'IN');

            if ($helper->getHandler('Mimetype')->deleteAll($crit)) {
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
            } else {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_DELETE_MIME_ERROR);
            }
        }
        if (Request::getString('add_mime', '', 'POST')) {
            //        header("Location: " . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit");
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit", 3, _AM_PUBLISHER_MIME_CREATEF);
            //        exit();
        }
        if (Request::getString('mime_search', '', 'POST')) {
            //        header("Location: " . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search");
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php?op=search', 3, _AM_PUBLISHER_MIME_SEARCH);
            //        exit();
        }

        Publisher\Utility::cpHeader();
        ////publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES);
        Publisher\Utility::openCollapsableBar('mimemanagetable', 'mimemanageicon', _AM_PUBLISHER_MIME_MANAGE_TITLE, _AM_PUBLISHER_MIME_INFOTEXT);
        $crit  = new \CriteriaCompo();
        $order = Request::getString('order', 'ASC', 'POST');
        $sort  = Request::getString('sort', 'mime_ext', 'POST');

        $crit->setOrder($order);
        $crit->setStart($start);
        $crit->setLimit($limit);
        $crit->setSort($sort);
        $mimetypes = $helper->getHandler('Mimetype')->getObjects($crit); // Retrieve a list of all mimetypes
        $mimeCount = $helper->getHandler('Mimetype')->getCount();
        $nav       = new \XoopsPageNav($mimeCount, $limit, $start, 'start', "op=manage&amp;limit=$limit");

        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><td colspan='6' align='right'>";
        echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo '<table>';
        echo '<tr>';
        echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_BY . '</td>';
        echo "<td align='left'><select name='search_by'>";
        foreach ($aSearchBy as $value => $text) {
            ($sort == $value) ? $selected = 'selected' : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo '</select></td>';
        echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_TEXT . '</td>';
        echo "<td align='left'><input type='text' name='search_text' id='search_text' value=''></td>";
        echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_PUBLISHER_BUTTON_SEARCH . "'></td>";
        echo '</tr></table></form></td></tr>';

        echo "<tr><td colspan='6'>";
        echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage' style='margin:0; padding:0;' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%'>";
        echo "<tr><td align='right'>" . _AM_PUBLISHER_TEXT_SORT_BY . "
    <select name='sort'>";
        foreach ($aSortBy as $value => $text) {
            ($sort == $value) ? $selected = 'selected' : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo '</select>
    &nbsp;&nbsp;&nbsp;
    ' . _AM_PUBLISHER_TEXT_ORDER_BY . "
    <select name='order'>";
        foreach ($aOrderBy as $value => $text) {
            ($order == $value) ? $selected = 'selected' : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo '</select>
    &nbsp;&nbsp;&nbsp;
    ' . _AM_PUBLISHER_TEXT_NUMBER_PER_PAGE . "
    <select name='limit'>";
        foreach ($aLimitBy as $value => $text) {
            ($limit == $value) ? $selected = 'selected' : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo "</select>
    <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_PUBLISHER_BUTTON_SUBMIT . "'>
    </td>
    </tr>";
        echo '</table>';
        echo '</td></tr>';
        echo "<tr><th colspan='6'>" . _AM_PUBLISHER_MIME_MANAGE_TITLE . '</th></tr>';
        echo "<tr class='head'>
    <td>" . _AM_PUBLISHER_MIME_ID . '</td>
    <td>' . _AM_PUBLISHER_MIME_NAME . "</td>
    <td align='center'>" . _AM_PUBLISHER_MIME_EXT . "</td>
    <td align='center'>" . _AM_PUBLISHER_MIME_ADMIN . "</td>
    <td align='center'>" . _AM_PUBLISHER_MIME_USER . "</td>
    <td align='center'>" . _AM_PUBLISHER_MINDEX_ACTION . '</td>
    </tr>';
        foreach ($mimetypes as $mime) {
            echo "<tr class='even'>
        <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "'>" . $mime->getVar('mime_id') . '</td>
        <td>' . $mime->getVar('mime_name') . "</td>
        <td align='center'>" . $mime->getVar('mime_ext') . "</td>
        <td align='center'>
        <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_admin=' . $mime->getVar('mime_admin') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
        " . ($mime->getVar('mime_admin') ? $imagearray['online'] : $imagearray['offline']) . "</a>
        </td>
        <td align='center'>
        <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_user=' . $mime->getVar('mime_user') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
        " . ($mime->getVar('mime_user') ? $imagearray['online'] : $imagearray['offline']) . "</a>
        </td>
        <td align='center'>
        <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=edit&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['editimg'] . "</a>
        <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=delete&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['deleteimg'] . '</a>
        </td>
        </tr>';
        }
        //        unset($mime);
        echo "<tr class='foot'>
    <td colspan='6' valign='top'>
    <a href='http://www.filext.com' style='float: right;' target='_blank'>" . _AM_PUBLISHER_MIME_FINDMIMETYPE . "</a>
    <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);'>
    <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_PUBLISHER_BUTTON_DELETE . "'>
    <input type='submit' name='add_mime' id='add_mime' value='" . _AM_PUBLISHER_MIME_CREATEF . "' class='formButton'>
    </td>
    </tr>";
        echo '</table>';
        echo "<div id='staff_nav'>" . $nav->renderNav() . '</div><br>';

        Publisher\Utility::closeCollapsableBar('mimemanagetable', 'mimemanageicon');

        //        xoops_cp_footer();
        require_once __DIR__ . '/admin_footer.php';
    }

    public static function search()
    {
        $helper = Publisher\Helper::getInstance();
        global $limit, $start, $imagearray, $aSearchBy, $aOrderBy, $aLimitBy, $aSortBy;

        if (Request::getString('deleteMimes', '', 'POST')) {
            $aMimes = Request::getArray('mimes', [], 'POST');

            $crit = new \Criteria('mime_id', '(' . implode($aMimes, ',') . ')', 'IN');

            if ($helper->getHandler('Mimetype')->deleteAll($crit)) {
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
            } else {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_DELETE_MIME_ERROR);
            }
        }
        if (Request::getString('add_mime', '', 'POST')) {
            //        header("Location: " . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit");
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit", 3, _AM_PUBLISHER_MIME_CREATEF);
            //        exit();
        }

        $order = Request::getString('order', 'ASC');
        $sort  = Request::getString('sort', 'mime_name');

        Publisher\Utility::cpHeader();
        //publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES . " > " . _AM_PUBLISHER_BUTTON_SEARCH);

        Publisher\Utility::openCollapsableBar('mimemsearchtable', 'mimesearchicon', _AM_PUBLISHER_MIME_SEARCH);

        if (!Request::hasVar('mime_search')) {
            echo "<form action='mimetypes.php?op=search' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_TEXT_SEARCH_MIME . '</th></tr>';
            echo "<tr><td class='head' width='20%'>" . _AM_PUBLISHER_TEXT_SEARCH_BY . "</td>
        <td class='even'>
        <select name='search_by'>";
            foreach ($aSortBy as $value => $text) {
                echo "<option value='$value'>$text</option>";
            }
            unset($value, $text);
            echo '</select>
        </td>
        </tr>';
            echo "<tr><td class='head'>" . _AM_PUBLISHER_TEXT_SEARCH_TEXT . "</td>
        <td class='even'>
        <input type='text' name='search_text' id='search_text' value=''>
        </td>
        </tr>";
            echo "<tr class='foot'>
        <td colspan='2'>
        <input type='submit' name='mime_search' id='mime_search' value='" . _AM_PUBLISHER_BUTTON_SEARCH . "'>
        </td>
        </tr>";
            echo '</table></form>';
        } else {
            $searchField = Request::getString('search_by', '');
            $searchField = isset($aSearchBy[$searchField]) ? $searchField : 'mime_ext';
            $searchText  = Request::getString('search_text', '');

            $crit = new \Criteria($searchField, '%' . $GLOBALS['xoopsDB']->escape($searchText) . '%', 'LIKE');
            $crit->setSort($sort);
            $crit->setOrder($order);
            $crit->setLimit($limit);
            $crit->setStart($start);
            $mimeCount = $helper->getHandler('Mimetype')->getCount($crit);
            $mimetypes = $helper->getHandler('Mimetype')->getObjects($crit);
            $nav       = new \XoopsPageNav($mimeCount, $limit, $start, 'start', "op=search&amp;limit=$limit&amp;order=$order&amp;sort=$sort&amp;mime_search=1&amp;search_by=$searchField&amp;search_text=" . htmlentities($searchText, ENT_QUOTES));
            // Display results
            echo '<script type="text/javascript" src="' . PUBLISHER_URL . '/include/functions.js"></script>';

            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><td colspan='6' align='right'>";
            echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo '<table>';
            echo '<tr>';
            echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_BY . '</td>';
            echo "<td align='left'><select name='search_by'>";
            foreach ($aSearchBy as $value => $text) {
                ($searchField == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo '</select></td>';
            echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_TEXT . '</td>';
            echo "<td align='left'><input type='text' name='search_text' id='search_text' value='" . htmlentities($searchText, ENT_QUOTES) . "'></td>";
            echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_PUBLISHER_BUTTON_SEARCH . "'></td>";
            echo '</tr></table></form></td></tr>';

            echo "<tr><td colspan='6'>";
            echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<table width='100%'>";
            echo "<tr><td align='right'>" . _AM_PUBLISHER_TEXT_SORT_BY . "
        <select name='sort'>";
            foreach ($aSortBy as $value => $text) {
                ($sort == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo '</select>
        &nbsp;&nbsp;&nbsp;
        ' . _AM_PUBLISHER_TEXT_ORDER_BY . "
        <select name='order'>";
            foreach ($aOrderBy as $value => $text) {
                ($order == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo '</select>
        &nbsp;&nbsp;&nbsp;
        ' . _AM_PUBLISHER_TEXT_NUMBER_PER_PAGE . "
        <select name='limit'>";
            foreach ($aLimitBy as $value => $text) {
                ($limit == $value) ? $selected = 'selected' : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo "</select>
        <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_PUBLISHER_BUTTON_SUBMIT . "'>
        <input type='hidden' name='mime_search' id='mime_search' value='1'>
        <input type='hidden' name='search_by' id='search_by' value='$searchField'>
        <input type='hidden' name='search_text' id='search_text' value='" . htmlentities($searchText, ENT_QUOTES) . "'>
        </td>
        </tr>";
            echo '</table>';
            echo '</td></tr>';
            if (count($mimetypes) > 0) {
                echo "<tr><th colspan='6'>" . _AM_PUBLISHER_TEXT_SEARCH_MIME . '</th></tr>';
                echo "<tr class='head'>
            <td>" . _AM_PUBLISHER_MIME_ID . '</td>
            <td>' . _AM_PUBLISHER_MIME_NAME . "</td>
            <td align='center'>" . _AM_PUBLISHER_MIME_EXT . "</td>
            <td align='center'>" . _AM_PUBLISHER_MIME_ADMIN . "</td>
            <td align='center'>" . _AM_PUBLISHER_MIME_USER . "</td>
            <td align='center'>" . _AM_PUBLISHER_MINDEX_ACTION . '</td>
            </tr>';
                foreach ($mimetypes as $mime) {
                    echo "<tr class='even'>
                <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "'>" . $mime->getVar('mime_id') . '</td>
                <td>' . $mime->getVar('mime_name') . "</td>
                <td align='center'>" . $mime->getVar('mime_ext') . "</td>
                <td align='center'>
                <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_admin=' . $mime->getVar('mime_admin') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
                " . ($mime->getVar('mime_admin') ? $imagearray['online'] : $imagearray['offline']) . "</a>
                </td>
                <td align='center'>
                <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=updateMimeValue&amp;id=' . $mime->getVar('mime_id') . '&amp;mime_user=' . $mime->getVar('mime_user') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>
                " . ($mime->getVar('mime_user') ? $imagearray['online'] : $imagearray['offline']) . "</a>
                </td>
                <td align='center'>
                <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=edit&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['editimg'] . "</a>
                <a href='" . PUBLISHER_ADMIN_URL . '/mimetypes.php?op=delete&amp;id=' . $mime->getVar('mime_id') . '&amp;limit=' . $limit . '&amp;start=' . $start . "'>" . $imagearray['deleteimg'] . '</a>
                </td>
                </tr>';
                }
                //                unset($mime);
                echo "<tr class='foot'>
            <td colspan='6' valign='top'>
            <a href='http://www.filext.com' style='float: right;' target='_blank'>" . _AM_PUBLISHER_MIME_FINDMIMETYPE . "</a>
            <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);'>
            <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_PUBLISHER_BUTTON_DELETE . "'>
            <input type='submit' name='add_mime' id='add_mime' value='" . _AM_PUBLISHER_MIME_CREATEF . "' class='formButton'>
            </td>
            </tr>";
            } else {
                echo '<tr><th>' . _AM_PUBLISHER_TEXT_SEARCH_MIME . '</th></tr>';
                echo "<tr class='even'>
            <td>" . _AM_PUBLISHER_TEXT_NO_RECORDS . '</td>
            </tr>';
            }
            echo '</table>';
            echo "<div id='pagenav'>" . $nav->renderNav() . '</div>';
        }
        Publisher\Utility::closeCollapsableBar('mimesearchtable', 'mimesearchicon');
        //        require_once __DIR__ . '/admin_footer.php';
        xoops_cp_footer();
    }

    /**
     * confirm update to mime access, resubmit as POST, including TOKEN
     */
    public static function updateMimeValue()
    {
        // op=updateMimeValue&id=65&mime_admin=0&limit=15&start=0
        Publisher\Utility::cpHeader();
        $hiddens = [
            'id'    => Request::getInt('id', 0, 'GET'),
            'start' => Request::getInt('start', 0, 'GET'),
            'limit' => Request::getInt('limit', 15, 'GET'),
        ];

        $helper   = Publisher\Helper::getInstance();
        $mimeTypeObj = $helper->getHandler('Mimetype')->get($hiddens['id']);
        if (Request::hasVar('mime_admin')) {
            $hiddens['mime_admin'] = Request::getInt('mime_admin', 0, 'GET');
            $msg                   = sprintf(_AM_PUBLISHER_MIME_ACCESS_CONFIRM_ADMIN, $mimeTypeObj->getVar('mime_name'));
        } else {
            $hiddens['mime_user'] = Request::getInt('mime_user', 0, 'GET');
            $msg                  = sprintf(_AM_PUBLISHER_MIME_ACCESS_CONFIRM_USER, $mimeTypeObj->getVar('mime_name'));
        }

        $action = PUBLISHER_ADMIN_URL . '/mimetypes.php?op=confirmUpdateMimeValue';
        $submit = _AM_PUBLISHER_MIME_ACCESS_CONFIRM;

        xoops_confirm($hiddens, $action, $msg, $submit, true);
        xoops_cp_footer();
    }

    public static function confirmUpdateMimeValue()
    {
        $helper = Publisher\Helper::getInstance();

        $limit  = Request::getInt('limit', 0, 'POST');
        $start  = Request::getInt('start', 0, 'POST');
        $mimeId = Request::getInt('id', 0, 'POST');
        if (0 === $mimeId) {
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php', 3, _AM_PUBLISHER_MESSAGE_NO_ID);
        }

        $mimeTypeObj = $helper->getHandler('Mimetype')->get($mimeId);

        if (-1 !== ($mimeAdmin = Request::getInt('mime_admin', -1, 'POST'))) {
            $mimeAdmin = self::changeMimeValue($mimeAdmin);
            $mimeTypeObj->setVar('mime_admin', $mimeAdmin);
        } elseif (-1 !== ($mimeUser = Request::getInt('mime_user', -1, 'POST'))) {
            $mimeUser = self::changeMimeValue($mimeUser);
            $mimeTypeObj->setVar('mime_user', $mimeUser);
        }
        if ($helper->getHandler('Mimetype')->insert($mimeTypeObj, true)) {
            header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
        } else {
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3);
        }
    }

    /**
     * @param $mimeValue
     *
     * @return int
     */
    protected static function changeMimeValue($mimeValue)
    {
        if (1 === (int)$mimeValue) {
            $mimeValue = 0;
        } else {
            $mimeValue = 1;
        }

        return $mimeValue;
    }

    protected static function clearAddSessionVars()
    {
        $session = Session::getInstance();
        $session->del('publisher_addMime');
        $session->del('publisher_addMimeErr');
    }

    public static function clearAddSession()
    {
        self::clearAddSessionVars();
        header('Location: ' . Publisher\Utility::makeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', ['op' => 'add'], false));
    }

    /**
     * @param $id
     */
    public static function clearEditSessionVars($id)
    {
        $id      = (int)$id;
        $session = Session::getInstance();
        $session->del("publisher_editMime_$id");
        $session->del("publisher_editMimeErr_$id");
    }

    public static function clearEditSession()
    {
        $mimeid = Request::getInt('id', '', 'GET');
        self::clearEditSessionVars($mimeid);
        header('Location: ' . Publisher\Utility::makeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', ['op' => 'edit', 'id' => $mimeid], false));
    }
}
