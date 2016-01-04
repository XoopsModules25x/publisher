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
 * @version         $Id: mimetypes.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');

//$start = $limit = 0;
//if (isset($_GET['limit'])) {
//    $limit = XoopsRequest::getInt('limit', 0, 'GET');
//} elseif (isset($_POST['limit'])) {
//    $limit = XoopsRequest::getInt('limit', 0, 'POST');
//} else {
//    $limit = 15;
//}

$start = XoopsRequest::getInt('start', 0, 'GET');
$limit = XoopsRequest::getInt('limit', XoopsRequest::getInt('limit', 15, 'GET'), 'POST');

$aSortBy   = array(
    'mime_id'    => _AM_PUBLISHER_MIME_ID,
    'mime_name'  => _AM_PUBLISHER_MIME_NAME,
    'mime_ext'   => _AM_PUBLISHER_MIME_EXT,
    'mime_admin' => _AM_PUBLISHER_MIME_ADMIN,
    'mime_user'  => _AM_PUBLISHER_MIME_USER);
$aOrderBy  = array('ASC' => _AM_PUBLISHER_TEXT_ASCENDING, 'DESC' => _AM_PUBLISHER_TEXT_DESCENDING);
$aLimitBy  = array('10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100);
$aSearchBy = array('mime_id' => _AM_PUBLISHER_MIME_ID, 'mime_name' => _AM_PUBLISHER_MIME_NAME, 'mime_ext' => _AM_PUBLISHER_MIME_EXT);

$error = array();

$op = XoopsRequest::getString('op', 'default', 'GET');

switch ($op) {
    case 'add':
        PublisherMimetypesUtilities::add();
        break;

    case 'delete':
        delete();
        break;

    case 'edit':
        PublisherMimetypesUtilities::edit();
        break;

    case 'search':
        PublisherMimetypesUtilities::search();
        break;

    case 'updateMimeValue':
        PublisherMimetypesUtilities::updateMimeValue();
        break;

    case 'clearAddSession':
        PublisherMimetypesUtilities::clearAddSession();
        break;

    case 'clearEditSession':
        PublisherMimetypesUtilities::clearEditSession();
        break;

    case 'manage':
    default:
        PublisherMimetypesUtilities::manage();
        break;
}


/**
 * Class PublisherMimetypesUtilities
 */
class PublisherMimetypesUtilities
{
    public static function add()
    {
        $publisher =& PublisherPublisher::getInstance();
        global $limit, $start;
        $error = array();
        if (!(XoopsRequest::getString('add_mime', '', 'POST'))) {
            publisherCpHeader();
            //publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES);

            publisherOpenCollapsableBar('mimemaddtable', 'mimeaddicon', _AM_PUBLISHER_MIME_ADD_TITLE);

            $session    =& PublisherSession::getInstance();
            $mimeType   = $session->get('publisher_addMime');
            $mimeErrors = $session->get('publisher_addMimeErr');

            //Display any form errors
            if (!$mimeErrors === false) {
                publisherRenderErrors($mimeErrors, publisherMakeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', array('op' => 'clearAddSession')));
            }

            if ($mimeType === false) {
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
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_MIME_CREATEF . '</th></tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_EXTF . "</td>
        <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mimeExt' size='5' /></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_NAMEF . "</td>
        <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mimeName' /></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_TYPEF . "</td>
        <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mimeTypes</textarea></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_ADMINF . "</td>
        <td class='even'>";
            echo "<input type='radio' name='mime_admin' value='1' " . ($mimeAdmin == 1 ? "checked='checked'" : '') . ' />' . _YES;
            echo "<input type='radio' name='mime_admin' value='0' " . ($mimeAdmin == 0 ? "checked='checked'" : '') . ' />' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_USERF . "</td>
        <td class='even'>";
            echo "<input type='radio' name='mime_user' value='1'" . ($mimeUser == 1 ? "checked='checked'" : '') . ' />' . _YES;
            echo "<input type='radio' name='mime_user' value='0'" . ($mimeUser == 0 ? "checked='checked'" : '') . '/>' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_MANDATORY_FIELD . "</td>
        <td class='even'>
        <input type='submit' name='add_mime' id='add_mime' value='" . _AM_PUBLISHER_BUTTON_SUBMIT . "' class='formButton' />
        <input type='button' name='cancel' value='" . _AM_PUBLISHER_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton' />
        </td>
        </tr>";
            echo '</table></form>';
            // end of add form

            // Find new mimetypes table
            echo "<form action='http://www.filext.com' method='post'>";
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_MIME_FINDMIMETYPE . '</th></tr>';

            echo "<tr class='foot'>
        <td colspan='2'><input type='submit' name='find_mime' id='find_mime' value='" . _AM_PUBLISHER_MIME_FINDIT . "' class='formButton' /></td>
        </tr>";

            echo '</table></form>';

            publisherCloseCollapsableBar('mimeaddtable', 'mimeaddicon');

            xoops_cp_footer();
        } else {
            $hasErrors = false;
            $mimeExt   = XoopsRequest::getString('mime_ext', '', 'POST');
            $mimeName  = XoopsRequest::getString('mime_name', '', 'POST');
            $mimeTypes = XoopsRequest::getText('mime_types', '', 'POST');
            $mimeAdmin = XoopsRequest::getInt('mime_admin', 0, 'POST');
            $mimeUser  = XoopsRequest::getInt('mime_user', 0, 'POST');

            //Validate Mimetype entry
            if ('' === (trim($mimeExt))) {
                $hasErrors           = true;
                $error['mime_ext'][] = _AM_PUBLISHER_VALID_ERR_MIME_EXT;
            }

            if ('' === (trim($mimeName))) {
                $hasErrors            = true;
                $error['mime_name'][] = _AM_PUBLISHER_VALID_ERR_MIME_NAME;
            }

            if ('' === (trim($mimeTypes))) {
                $hasErrors             = true;
                $error['mime_types'][] = _AM_PUBLISHER_VALID_ERR_MIME_TYPES;
            }

            if ($hasErrors) {
                $session            =& PublisherSession::getInstance();
                $mime               = array();
                $mime['mime_ext']   = $mimeExt;
                $mime['mime_name']  = $mimeName;
                $mime['mime_types'] = $mimeTypes;
                $mime['mime_admin'] = $mimeAdmin;
                $mime['mime_user']  = $mimeUser;
                $session->set('publisher_addMime', $mime);
                $session->set('publisher_addMimeErr', $error);
                header('Location: ' . publisherMakeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', array('op' => 'add'), false));
            }

            $mimeType =& $publisher->getHandler('mimetype')->create();
            $mimeType->setVar('mime_ext', $mimeExt);
            $mimeType->setVar('mime_name', $mimeName);
            $mimeType->setVar('mime_types', $mimeTypes);
            $mimeType->setVar('mime_admin', $mimeAdmin);
            $mimeType->setVar('mime_user', $mimeUser);

            if (!$publisher->getHandler('mimetype')->insert($mimeType)) {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_ADD_MIME_ERROR);
            } else {
                self::clearAddSessionVars();
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
            }
        }
    }

    public static function delete()
    {
        $publisher =& PublisherPublisher::getInstance();
        global $start, $limit;
        $mimeId = 0;
        if (0 == XoopsRequest::getInt('id', 0, 'GET')) {
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php', 3, _AM_PUBLISHER_MESSAGE_NO_ID);
        } else {
            $mimeId = XoopsRequest::getInt('id', 0, 'GET');
        }
        $mimeType =& $publisher->getHandler('mimetype')->get($mimeId); // Retrieve mimetype object
        if (!$publisher->getHandler('mimetype')->delete($mimeType, true)) {
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&id=$mimeId&limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_DELETE_MIME_ERROR);
        } else {
            header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
        }
    }

    public static function edit()
    {
        $publisher =& PublisherPublisher::getInstance();
        global $start, $limit;
        $mimeId    = 0;
        $error     = array();
        $hasErrors = false;
        if (0 == XoopsRequest::getInt('id', 0, 'GET')) {
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php', 3, _AM_PUBLISHER_MESSAGE_NO_ID);
        } else {
            $mimeId = XoopsRequest::getInt('id', 0, 'GET');
        }
        $mimeTypeObj =& $publisher->getHandler('mimetype')->get($mimeId); // Retrieve mimetype object

        if (!(XoopsRequest::getString('edit_mime', '', 'POST'))) {
            $session    =& PublisherSession::getInstance();
            $mimeType   = $session->get('publisher_editMime_' . $mimeId);
            $mimeErrors = $session->get('publisher_editMimeErr_' . $mimeId);

            // Display header
            publisherCpHeader();
            //publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES . " > " . _AM_PUBLISHER_BUTTON_EDIT);

            publisherOpenCollapsableBar('mimemedittable', 'mimeediticon', _AM_PUBLISHER_MIME_EDIT_TITLE);

            //Display any form errors
            if (!$mimeErrors === false) {
                publisherRenderErrors($mimeErrors, publisherMakeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', array('op' => 'clearEditSession', 'id' => $mimeId)));
            }

            if ($mimeType === false) {
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
            echo "<input type='hidden' name='limit' value='" . $limit . "' />";
            echo "<input type='hidden' name='start' value='" . $start . "' />";
            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><th colspan='2'>" . _AM_PUBLISHER_MIME_MODIFYF . '</th></tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_EXTF . "</td>
        <td class='even'><input type='text' name='mime_ext' id='mime_ext' value='$mimeExt' size='5' /></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_NAMEF . "</td>
        <td class='even'><input type='text' name='mime_name' id='mime_name' value='$mimeName' /></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_TYPEF . "</td>
        <td class='even'><textarea name='mime_types' id='mime_types' cols='60' rows='5'>$mimeTypes</textarea></td>
        </tr>";
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_ADMINF . "</td>
        <td class='even'>
        <input type='radio' name='mime_admin' value='1' " . ($mimeAdmin == 1 ? "checked='checked'" : '') . ' />' . _YES . "
        <input type='radio' name='mime_admin' value='0' " . ($mimeAdmin == 0 ? "checked='checked'" : '') . ' />' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'>" . _AM_PUBLISHER_MIME_USERF . "</td>
        <td class='even'>
        <input type='radio' name='mime_user' value='1' " . ($mimeUser == 1 ? "checked='checked'" : '') . ' />' . _YES . "
        <input type='radio' name='mime_user' value='0' " . ($mimeUser == 0 ? "checked='checked'" : '') . ' />' . _NO . '
        </td>
        </tr>';
            echo "<tr valign='top'>
        <td class='head'></td>
        <td class='even'>
        <input type='submit' name='edit_mime' id='edit_mime' value='" . _AM_PUBLISHER_BUTTON_UPDATE . "' class='formButton' />
        <input type='button' name='cancel' value='" . _AM_PUBLISHER_BUTTON_CANCEL . "' onclick='history.go(-1)' class='formButton' />
        </td>
        </tr>";
            echo '</table></form>';
            // end of edit form
            publisherCloseCollapsableBar('mimeedittable', 'mimeediticon');
//            xoops_cp_footer();
            include_once __DIR__ . '/admin_footer.php';

        } else {
            $mimeAdmin = 0;
            $mimeUser  = 0;
            if (1 == XoopsRequest::getInt('mime_admin', 0, 'POST')) {
                $mimeAdmin = 1;
            }
            if (1 == XoopsRequest::getInt('mime_user', 0, 'POST')) {
                $mimeUser = 1;
            }

            //Validate Mimetype entry
            if ('' === (XoopsRequest::getString('mime_ext', '', 'POST'))) {
                $hasErrors           = true;
                $error['mime_ext'][] = _AM_PUBLISHER_VALID_ERR_MIME_EXT;
            }

            if ('' === (XoopsRequest::getString('mime_name', '', 'POST'))) {
                $hasErrors            = true;
                $error['mime_name'][] = _AM_PUBLISHER_VALID_ERR_MIME_NAME;
            }

            if ('' === (XoopsRequest::getString('mime_types', '', 'POST'))) {
                $hasErrors             = true;
                $error['mime_types'][] = _AM_PUBLISHER_VALID_ERR_MIME_TYPES;
            }

            if ($hasErrors) {
                $session            =& PublisherSession::getInstance();
                $mime               = array();
                $mime['mime_ext']   = XoopsRequest::getString('mime_ext', '', 'POST');
                $mime['mime_name']  = XoopsRequest::getString('mime_name', '', 'POST');
                $mime['mime_types'] = XoopsRequest::getText('mime_types', '', 'POST');
                $mime['mime_admin'] = $mimeAdmin;
                $mime['mime_user']  = $mimeUser;
                $session->set('publisher_editMime_' . $mimeId, $mime);
                $session->set('publisher_editMimeErr_' . $mimeId, $error);
                header('Location: ' . publisherMakeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', array('op' => 'edit', 'id' => $mimeId), false));
            }

            $mimeTypeObj->setVar('mime_ext', XoopsRequest::getString('mime_ext', '', 'POST'));
            $mimeTypeObj->setVar('mime_name', XoopsRequest::getString('mime_name', '', 'POST'));
            $mimeTypeObj->setVar('mime_types', XoopsRequest::getText('mime_types', '', 'POST'));
            $mimeTypeObj->setVar('mime_admin', $mimeAdmin);
            $mimeTypeObj->setVar('mime_user', $mimeUser);

            if (!$publisher->getHandler('mimetype')->insert($mimeTypeObj, true)) {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=edit&id=$mimeId", 3, _AM_PUBLISHER_MESSAGE_EDIT_MIME_ERROR);
            } else {
                self::clearEditSessionVars($mimeId);
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage&limit=$limit&start=$start");
            }
        }
    }

    public static function manage()
    {
        $publisher =& PublisherPublisher::getInstance();
        global $imagearray, $start, $limit, $aSortBy, $aOrderBy, $aLimitBy, $aSearchBy;

        if ((XoopsRequest::getString('deleteMimes', '', 'POST'))) {
            $aMimes = XoopsRequest::getArray('mimes', array(), 'POST');

            $crit = new Criteria('mime_id', '(' . implode($aMimes, ',') . ')', 'IN');

            if ($publisher->getHandler('mimetype')->deleteAll($crit)) {
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
            } else {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_DELETE_MIME_ERROR);
            }
        }
        if ((XoopsRequest::getString('add_mime', '', 'POST'))) {
            //        header("Location: " . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit");
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit", 3, _AM_PUBLISHER_MIME_CREATEF);
            //        exit();
        }
        if ((XoopsRequest::getString('mime_search', '', 'POST'))) {
            //        header("Location: " . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search");
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php?op=search', 3, _AM_PUBLISHER_MIME_SEARCH);
            //        exit();
        }

        publisherCpHeader();
        ////publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES);
        publisherOpenCollapsableBar('mimemanagetable', 'mimemanageicon', _AM_PUBLISHER_MIME_MANAGE_TITLE, _AM_PUBLISHER_MIME_INFOTEXT);
        $crit  = new CriteriaCompo();
        $order = XoopsRequest::getString('order', 'ASC', 'POST');
        $sort  = XoopsRequest::getString('sort', 'mime_ext', 'POST');

        $crit->setOrder($order);
        $crit->setStart($start);
        $crit->setLimit($limit);
        $crit->setSort($sort);
        $mimetypes =& $publisher->getHandler('mimetype')->getObjects($crit); // Retrieve a list of all mimetypes
        $mimeCount =& $publisher->getHandler('mimetype')->getCount();
        $nav       = new XoopsPageNav($mimeCount, $limit, $start, 'start', "op=manage&amp;limit=$limit");

        echo "<table width='100%' cellspacing='1' class='outer'>";
        echo "<tr><td colspan='6' align='right'>";
        echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
        echo '<table>';
        echo '<tr>';
        echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_BY . '</td>';
        echo "<td align='left'><select name='search_by'>";
        foreach ($aSearchBy as $value => $text) {
            ($sort == $value) ? $selected = "selected='selected'" : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo '</select></td>';
        echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_TEXT . '</td>';
        echo "<td align='left'><input type='text' name='search_text' id='search_text' value='' /></td>";
        echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_PUBLISHER_BUTTON_SEARCH . "' /></td>";
        echo '</tr></table></form></td></tr>';

        echo "<tr><td colspan='6'>";
        echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=manage' style='margin:0; padding:0;' method='post'>";
        echo "<table width='100%'>";
        echo "<tr><td align='right'>" . _AM_PUBLISHER_TEXT_SORT_BY . "
    <select name='sort'>";
        foreach ($aSortBy as $value => $text) {
            ($sort == $value) ? $selected = "selected='selected'" : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo '</select>
    &nbsp;&nbsp;&nbsp;
    ' . _AM_PUBLISHER_TEXT_ORDER_BY . "
    <select name='order'>";
        foreach ($aOrderBy as $value => $text) {
            ($order == $value) ? $selected = "selected='selected'" : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo '</select>
    &nbsp;&nbsp;&nbsp;
    ' . _AM_PUBLISHER_TEXT_NUMBER_PER_PAGE . "
    <select name='limit'>";
        foreach ($aLimitBy as $value => $text) {
            ($limit == $value) ? $selected = "selected='selected'" : $selected = '';
            echo "<option value='$value' $selected>$text</option>";
        }
        unset($value, $text);
        echo "</select>
    <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_PUBLISHER_BUTTON_SUBMIT . "' />
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
        <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "' />" . $mime->getVar('mime_id') . '</td>
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
    <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);' />
    <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_PUBLISHER_BUTTON_DELETE . "' />
    <input type='submit' name='add_mime' id='add_mime' value='" . _AM_PUBLISHER_MIME_CREATEF . "' class='formButton' />
    </td>
    </tr>";
        echo '</table>';
        echo "<div id='staff_nav'>" . $nav->renderNav() . '</div><br/>';

        publisherCloseCollapsableBar('mimemanagetable', 'mimemanageicon');

//        xoops_cp_footer();
        include_once __DIR__ . '/admin_footer.php';
    }

    public static function search()
    {
        $publisher =& PublisherPublisher::getInstance();
        global $limit, $start, $imagearray, $aSearchBy, $aOrderBy, $aLimitBy, $aSortBy;

        if ((XoopsRequest::getString('deleteMimes', '', 'POST'))) {
            $aMimes = XoopsRequest::getArray('mimes', array(), 'POST');

            $crit = new Criteria('mime_id', '(' . implode($aMimes, ',') . ')', 'IN');

            if ($publisher->getHandler('mimetype')->deleteAll($crit)) {
                header('Location: ' . PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start");
            } else {
                redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?limit=$limit&start=$start", 3, _AM_PUBLISHER_MESSAGE_DELETE_MIME_ERROR);
            }
        }
        if ((XoopsRequest::getString('add_mime', '', 'POST'))) {
            //        header("Location: " . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit");
            redirect_header(PUBLISHER_ADMIN_URL . "/mimetypes.php?op=add&start=$start&limit=$limit", 3, _AM_PUBLISHER_MIME_CREATEF);
            //        exit();
        }

        $order = XoopsRequest::getString('order', 'ASC', 'POST');
        $sort  = XoopsRequest::getString('sort', 'mime_name', 'POST');

        publisherCpHeader();
        //publisher_adminMenu(4, _AM_PUBLISHER_MIMETYPES . " > " . _AM_PUBLISHER_BUTTON_SEARCH);

        publisherOpenCollapsableBar('mimemsearchtable', 'mimesearchicon', _AM_PUBLISHER_MIME_SEARCH);

        if (!(XoopsRequest::getString('mime_search', '', 'POST'))) {
            echo "<form action='mimetypes.php?op=search' method='post'>";
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
        <input type='text' name='search_text' id='search_text' value='' />
        </td>
        </tr>";
            echo "<tr class='foot'>
        <td colspan='2'>
        <input type='submit' name='mime_search' id='mime_search' value='" . _AM_PUBLISHER_BUTTON_SEARCH . "' />
        </td>
        </tr>";
            echo '</table></form>';
        } else {
            $searchField = XoopsRequest::getString('search_by', '', 'POST');
            $searchText  = XoopsRequest::getString('search_text', '', 'POST');

            $crit = new Criteria($searchField, "%$searchText%", 'LIKE');
            $crit->setSort($sort);
            $crit->setOrder($order);
            $crit->setLimit($limit);
            $crit->setStart($start);
            $mimeCount =& $publisher->getHandler('mimetype')->getCount($crit);
            $mimetypes =& $publisher->getHandler('mimetype')->getObjects($crit);
            $nav       = new XoopsPageNav($mimeCount, $limit, $start, 'start', "op=search&amp;limit=$limit&amp;order=$order&amp;sort=$sort&amp;mime_search=1&amp;search_by=$searchField&amp;search_text=$searchText");
            // Display results
            echo '<script type="text/javascript" src="' . PUBLISHER_URL . '/include/functions.js"></script>';

            echo "<table width='100%' cellspacing='1' class='outer'>";
            echo "<tr><td colspan='6' align='right'>";
            echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
            echo '<table>';
            echo '<tr>';
            echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_BY . '</td>';
            echo "<td align='left'><select name='search_by'>";
            foreach ($aSearchBy as $value => $text) {
                ($searchField == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo '</select></td>';
            echo "<td align='right'>" . _AM_PUBLISHER_TEXT_SEARCH_TEXT . '</td>';
            echo "<td align='left'><input type='text' name='search_text' id='search_text' value='$searchText' /></td>";
            echo "<td><input type='submit' name='mime_search' id='mime_search' value='" . _AM_PUBLISHER_BUTTON_SEARCH . "' /></td>";
            echo '</tr></table></form></td></tr>';

            echo "<tr><td colspan='6'>";
            echo "<form action='" . PUBLISHER_ADMIN_URL . "/mimetypes.php?op=search' style='margin:0; padding:0;' method='post'>";
            echo "<table width='100%'>";
            echo "<tr><td align='right'>" . _AM_PUBLISHER_TEXT_SORT_BY . "
        <select name='sort'>";
            foreach ($aSortBy as $value => $text) {
                ($sort == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo '</select>
        &nbsp;&nbsp;&nbsp;
        ' . _AM_PUBLISHER_TEXT_ORDER_BY . "
        <select name='order'>";
            foreach ($aOrderBy as $value => $text) {
                ($order == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo '</select>
        &nbsp;&nbsp;&nbsp;
        ' . _AM_PUBLISHER_TEXT_NUMBER_PER_PAGE . "
        <select name='limit'>";
            foreach ($aLimitBy as $value => $text) {
                ($limit == $value) ? $selected = "selected='selected'" : $selected = '';
                echo "<option value='$value' $selected>$text</option>";
            }
            unset($value, $text);
            echo "</select>
        <input type='submit' name='mime_sort' id='mime_sort' value='" . _AM_PUBLISHER_BUTTON_SUBMIT . "' />
        <input type='hidden' name='mime_search' id='mime_search' value='1' />
        <input type='hidden' name='search_by' id='search_by' value='$searchField' />
        <input type='hidden' name='search_text' id='search_text' value='$searchText' />
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
                <td><input type='checkbox' name='mimes[]' value='" . $mime->getVar('mime_id') . "' />" . $mime->getVar('mime_id') . '</td>
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
            <input type='checkbox' name='checkAllMimes' value='0' onclick='selectAll(this.form,\"mimes[]\",this.checked);' />
            <input type='submit' name='deleteMimes' id='deleteMimes' value='" . _AM_PUBLISHER_BUTTON_DELETE . "' />
            <input type='submit' name='add_mime' id='add_mime' value='" . _AM_PUBLISHER_MIME_CREATEF . "' class='formButton' />
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
        publisherCloseCollapsableBar('mimesearchtable', 'mimesearchicon');
//        include_once __DIR__ . '/admin_footer.php';
        xoops_cp_footer();
    }

    public static function updateMimeValue()
    {
        $mimeId    = 0;
        $publisher =& PublisherPublisher::getInstance();

        $limit = XoopsRequest::getInt('limit', 0, 'GET');
        $start = XoopsRequest::getInt('start', 0, 'GET');

        if (!(XoopsRequest::getString('id', '', 'GET'))) {
            redirect_header(PUBLISHER_ADMIN_URL . '/mimetypes.php', 3, _AM_PUBLISHER_MESSAGE_NO_ID);
        } else {
            $mimeId = XoopsRequest::getInt('id', 0, 'GET');
        }

        $mimeTypeObj =& $publisher->getHandler('mimetype')->get($mimeId);

        if (('' !== ($mimeAdmin = XoopsRequest::getString('mime_admin', '', 'GET')))) {
            //            $mimeAdmin = XoopsRequest::getInt('mime_admin', 0, 'GET');
            $mimeAdmin = self::changeMimeValue($mimeAdmin);
            $mimeTypeObj->setVar('mime_admin', $mimeAdmin);
        }
        if (('' !== ($mimeUser = XoopsRequest::getString('mime_user', '', 'GET')))) {
            //            $mimeUser = XoopsRequest::getInt('mime_user', 0, 'GET');
            $mimeUser = self::changeMimeValue($mimeUser);
            $mimeTypeObj->setVar('mime_user', $mimeUser);
        }
        if ($publisher->getHandler('mimetype')->insert($mimeTypeObj, true)) {
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
        if ((int)$mimeValue === 1) {
            $mimeValue = 0;
        } else {
            $mimeValue = 1;
        }

        return $mimeValue;
    }

    protected static function clearAddSessionVars()
    {
        $session =& PublisherSession::getInstance();
        $session->del('publisher_addMime');
        $session->del('publisher_addMimeErr');
    }

    public static function clearAddSession()
    {
        self::clearAddSessionVars();
        header('Location: ' . publisherMakeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', array('op' => 'add'), false));
    }

    /**
     * @param $id
     */
    public static function clearEditSessionVars($id)
    {
        $id      = (int)($id);
        $session =& PublisherSession::getInstance();
        $session->del("publisher_editMime_$id");
        $session->del("publisher_editMimeErr_$id");
    }

    public static function clearEditSession()
    {
        $mimeid = XoopsRequest::getInt('id', '', 'GET');
        self::clearEditSessionVars($mimeid);
        header('Location: ' . publisherMakeUri(PUBLISHER_ADMIN_URL . '/mimetypes.php', array('op' => 'edit', 'id' => $mimeid), false));
    }
}
