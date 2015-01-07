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
 *  Publisher form class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Class
 * @subpackage      Forms
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: item.php 10645 2013-01-03 19:31:21Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit("XOOPS root path not defined");

include_once dirname(dirname(__DIR__)) . '/include/common.php';

xoops_load('XoopsFormLoader');
xoops_load('XoopsLists');
include_once $GLOBALS['xoops']->path('class/tree.php');
include_once PUBLISHER_ROOT_PATH . '/class/formdatetime.php';
include_once PUBLISHER_ROOT_PATH . '/class/themetabform.php';

/**
 * Class PublisherItemForm
 */
class PublisherItemForm extends PublisherThemeTabForm
{

    public $checkperm = true;
    public $tabs      = array(
        _CO_PUBLISHER_TAB_MAIN   => 'mainTab',
        _CO_PUBLISHER_TAB_IMAGES => 'imagesTab',
        _CO_PUBLISHER_TAB_FILES  => 'filesTab',
        _CO_PUBLISHER_TAB_OTHERS => 'othersTab'
    );

    public $mainTab = array(
        PublisherConstantsInterface::PUBLISHER_SUBTITLE,
        PublisherConstantsInterface::PUBLISHER_ITEM_SHORT_URL,
        PublisherConstantsInterface::PUBLISHER_ITEM_TAG,
        PublisherConstantsInterface::PUBLISHER_SUMMARY,
        PublisherConstantsInterface::PUBLISHER_DOHTML,
        PublisherConstantsInterface::PUBLISHER_DOSMILEY,
        PublisherConstantsInterface::PUBLISHER_DOXCODE,
        PublisherConstantsInterface::PUBLISHER_DOIMAGE,
        PublisherConstantsInterface::PUBLISHER_DOLINEBREAK,
        PublisherConstantsInterface::PUBLISHER_DATESUB,
        PublisherConstantsInterface::PUBLISHER_STATUS,
        PublisherConstantsInterface::PUBLISHER_AUTHOR_ALIAS,
        PublisherConstantsInterface::PUBLISHER_NOTIFY,
        PublisherConstantsInterface::PUBLISHER_AVAILABLE_PAGE_WRAP,
        PublisherConstantsInterface::PUBLISHER_UID
    );

    public $imagesTab = array(
        PublisherConstantsInterface::PUBLISHER_IMAGE_ITEM
    );

    public $filesTab = array(
        PublisherConstantsInterface::PUBLISHER_ITEM_UPLOAD_FILE
    );

    public $othersTab = array(
        PublisherConstantsInterface::PUBLISHER_ITEM_META_KEYWORDS,
        PublisherConstantsInterface::PUBLISHER_ITEM_META_DESCRIPTION,
        PublisherConstantsInterface::PUBLISHER_WEIGHT,
        PublisherConstantsInterface::PUBLISHER_ALLOWCOMMENTS
    );

    /**
     * @param $checkperm
     */
    public function setCheckPermissions($checkperm)
    {
        $this->checkperm = (bool)$checkperm;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public function isGranted($item)
    {
        $publisher = PublisherPublisher::getInstance();
        $ret       = false;
        if (!$this->checkperm || $publisher->getHandler('permission')->isGranted('form_view', $item)) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @param $tab
     *
     * @return bool
     */
    public function hasTab($tab)
    {
        if (!isset($tab) || !isset($this->tabs[$tab])) {
            return false;
        }

        $tabRef = $this->tabs[$tab];
        $items  = $this->$tabRef;
        foreach ($items as $item) {
            if ($this->isGranted($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $obj
     *
     * @return $this
     */
    public function createElements($obj)
    {

        $publisher = PublisherPublisher::getInstance();

        $allowedEditors = publisher_getEditors($publisher->getHandler('permission')->getGrantedItems('editors'));

        if (!is_object($GLOBALS['xoopsUser'])) {
            $group = array(XOOPS_GROUP_ANONYMOUS);
        } else {
            $group = $GLOBALS['xoopsUser']->getGroups();
        }

        $this->setExtra('enctype="multipart/form-data"');

        $this->startTab(_CO_PUBLISHER_TAB_MAIN);

        // Category
        $category_select = new XoopsFormSelect(_CO_PUBLISHER_CATEGORY, 'categoryid', $obj->getVar('categoryid', 'e'));
        $category_select->setDescription(_CO_PUBLISHER_CATEGORY_DSC);
        $category_select->addOptionArray($publisher->getHandler('category')->getCategoriesForSubmit());
        $this->addElement($category_select);

        // ITEM TITLE
        $this->addElement(new XoopsFormText(_CO_PUBLISHER_TITLE, 'title', 50, 255, $obj->getVar('title', 'e')), true);

        // SUBTITLE
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_SUBTITLE)) {
            $this->addElement(new XoopsFormText(_CO_PUBLISHER_SUBTITLE, 'subtitle', 50, 255, $obj->getVar('subtitle', 'e')));
        }

        // SHORT URL
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_ITEM_SHORT_URL)) {
            $text_short_url = new XoopsFormText(_CO_PUBLISHER_ITEM_SHORT_URL, 'item_short_url', 50, 255, $obj->short_url('e'));
            $text_short_url->setDescription(_CO_PUBLISHER_ITEM_SHORT_URL_DSC);
            $this->addElement($text_short_url);
        }

        // TAGS
        if (xoops_isActiveModule('tag') && $this->isGranted(PublisherConstantsInterface::PUBLISHER_ITEM_TAG)) {
            include_once $GLOBALS['xoops']->path('modules/tag/include/formtag.php');
            $text_tags = new XoopsFormTag('item_tag', 60, 255, $obj->getVar('item_tag', 'e'), 0);
            $this->addElement($text_tags);
        }

        // SELECT EDITOR
        $nohtml = !$obj->dohtml();
        if (count($allowedEditors) == 1) {
            $editor = $allowedEditors[0];
        } elseif (count($allowedEditors) > 0) {
            $editor = XoopsRequest::getString('editor', '', 'POST');
            if (!empty($editor)) {
                publisher_setCookieVar('publisher_editor', $editor);
            } else {
                $editor = publisher_getCookieVar('publisher_editor');
                if (empty($editor) && is_object($GLOBALS['xoopsUser'])) {
//                    $editor = @ $GLOBALS['xoopsUser']->getVar('publisher_editor'); // Need set through user profile
                    $editor = (null !== ($GLOBALS['xoopsUser']->getVar('publisher_editor'))) ? $GLOBALS['xoopsUser']->getVar('publisher_editor') : ''; // Need set through user profile
                }
            }
            $editor = (empty($editor) || !in_array($editor, $allowedEditors)) ? $publisher->getConfig('submit_editor') : $editor;

            $form_editor = new XoopsFormSelectEditor($this, 'editor', $editor, $nohtml, $allowedEditors);
            $this->addElement($form_editor);
        } else {
            $editor = $publisher->getConfig('submit_editor');
        }

        $editor_configs           = array();
        $editor_configs["rows"]   = !$publisher->getConfig('submit_editor_rows') ? 35 : $publisher->getConfig('submit_editor_rows');
        $editor_configs["cols"]   = !$publisher->getConfig('submit_editor_cols') ? 60 : $publisher->getConfig('submit_editor_cols');
        $editor_configs["width"]  = !$publisher->getConfig('submit_editor_width') ? "100%" : $publisher->getConfig('submit_editor_width');
        $editor_configs["height"] = !$publisher->getConfig('submit_editor_height') ? "400px" : $publisher->getConfig('submit_editor_height');

        // SUMMARY
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_SUMMARY)) {
            // Description
            //$summary_text = new XoopsFormTextArea(_CO_PUBLISHER_SUMMARY, 'summary', $obj->getVar('summary', 'e'), 7, 60);
            $editor_configs["name"]  = "summary";
            $editor_configs["value"] = $obj->getVar('summary', 'e');
            $summary_text            = new XoopsFormEditor(_CO_PUBLISHER_SUMMARY, $editor, $editor_configs, $nohtml, $onfailure = null);
            $summary_text->setDescription(_CO_PUBLISHER_SUMMARY_DSC);
            $this->addElement($summary_text);
        }

        // BODY
        $editor_configs["name"]  = "body";
        $editor_configs["value"] = $obj->getVar('body', 'e');
        $body_text               = new XoopsFormEditor(_CO_PUBLISHER_BODY, $editor, $editor_configs, $nohtml, $onfailure = null);
        $body_text->setDescription(_CO_PUBLISHER_BODY_DSC);
        $this->addElement($body_text);

        // VARIOUS OPTIONS
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DOHTML) ||
            $this->isGranted(PublisherConstantsInterface::PUBLISHER_DOSMILEY) ||
            $this->isGranted(PublisherConstantsInterface::PUBLISHER_DOXCODE) ||
            $this->isGranted(PublisherConstantsInterface::PUBLISHER_DOIMAGE) ||
            $this->isGranted(PublisherConstantsInterface::PUBLISHER_DOLINEBREAK)
        ) {
            if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DOHTML)) {
                $html_radio = new XoopsFormRadioYN(_CO_PUBLISHER_DOHTML, 'dohtml', $obj->dohtml(), _YES, _NO);
                $this->addElement($html_radio);
            }
            if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DOSMILEY)) {
                $smiley_radio = new XoopsFormRadioYN(_CO_PUBLISHER_DOSMILEY, 'dosmiley', $obj->dosmiley(), _YES, _NO);
                $this->addElement($smiley_radio);
            }
            if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DOXCODE)) {
                $xcode_radio = new XoopsFormRadioYN(_CO_PUBLISHER_DOXCODE, 'doxcode', $obj->doxcode(), _YES, _NO);
                $this->addElement($xcode_radio);
            }
            if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DOIMAGE)) {
                $image_radio = new XoopsFormRadioYN(_CO_PUBLISHER_DOIMAGE, 'doimage', $obj->doimage(), _YES, _NO);
                $this->addElement($image_radio);
            }
            if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DOLINEBREAK)) {
                $linebreak_radio = new XoopsFormRadioYN(_CO_PUBLISHER_DOLINEBREAK, 'dolinebreak', $obj->dobr(), _YES, _NO);
                $this->addElement($linebreak_radio);
            }
        }

        // Available pages to wrap
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_AVAILABLE_PAGE_WRAP)) {
            $wrap_pages                = XoopsLists::getHtmlListAsArray(publisher_getUploadDir(true, 'content'));
            $available_wrap_pages_text = array();
            foreach ($wrap_pages as $page) {
                $available_wrap_pages_text[] = "<span onclick='publisherPageWrap(\"body\", \"[pagewrap=$page] \");' onmouseover='style.cursor=\"pointer\"'>$page</span>";
            }
            $available_wrap_pages = new XoopsFormLabel(_CO_PUBLISHER_AVAILABLE_PAGE_WRAP, implode(', ', $available_wrap_pages_text));
            $available_wrap_pages->setDescription(_CO_PUBLISHER_AVAILABLE_PAGE_WRAP_DSC);
            $this->addElement($available_wrap_pages);
        }

        // Uid
        /*  We need to retreive the users manually because for some reason, on the frxoops.org server,
         the method users::getobjects encounters a memory error
         */
        // Trabis : well, maybe is because you are getting 6000 objects into memory , no??? LOL
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_UID)) {
            $uid_select = new XoopsFormSelect(_CO_PUBLISHER_UID, 'uid', $obj->uid(), 1, false);
            $uid_select->setDescription(_CO_PUBLISHER_UID_DSC);
            $sql            = "SELECT uid, uname FROM " . $obj->db->prefix('users') . " ORDER BY uname ASC";
            $result         = $obj->db->query($sql);
            $users_array    = array();
            $users_array[0] = $GLOBALS['xoopsConfig']['anonymous'];
            while (($myrow = $obj->db->fetchArray($result)) !== false) {
                $users_array[$myrow['uid']] = $myrow['uname'];
            }
            $uid_select->addOptionArray($users_array);
            $this->addElement($uid_select);
        }
        /* else {
        $hidden = new XoopsFormHidden('uid', $obj->uid());
        $this->addElement($hidden);
        unset($hidden);
        }*/

        // Author ALias
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_AUTHOR_ALIAS)) {
            $element = new XoopsFormText(_CO_PUBLISHER_AUTHOR_ALIAS, 'author_alias', 50, 255, $obj->getVar('author_alias', 'e'));
            $element->setDescription(_CO_PUBLISHER_AUTHOR_ALIAS_DSC);
            $this->addElement($element);
            unset($element);
        }

        // STATUS
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_STATUS)) {
            $options       = array(
                PublisherConstantsInterface::PUBLISHER_STATUS_PUBLISHED => _CO_PUBLISHER_PUBLISHED,
                PublisherConstantsInterface::PUBLISHER_STATUS_OFFLINE   => _CO_PUBLISHER_OFFLINE,
                PublisherConstantsInterface::PUBLISHER_STATUS_SUBMITTED => _CO_PUBLISHER_SUBMITTED,
                PublisherConstantsInterface::PUBLISHER_STATUS_REJECTED  => _CO_PUBLISHER_REJECTED
            );
            $status_select = new XoopsFormSelect(_CO_PUBLISHER_STATUS, 'status', $obj->getVar('status'));
            $status_select->addOptionArray($options);
            $status_select->setDescription(_CO_PUBLISHER_STATUS_DSC);
            $this->addElement($status_select);
            unset($status_select);
        }

        // Datesub
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_DATESUB)) {
            $datesub          = ($obj->getVar('datesub') == 0) ? time() : $obj->getVar('datesub');
            $datesub_datetime = new PublisherFormDateTime(_CO_PUBLISHER_DATESUB, 'datesub', $size = 15, $datesub);
            $datesub_datetime->setDescription(_CO_PUBLISHER_DATESUB_DSC);
            $this->addElement($datesub_datetime);
        }

        // NOTIFY ON PUBLISH
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_NOTIFY)) {
            $notify_radio = new XoopsFormRadioYN(_CO_PUBLISHER_NOTIFY, 'notify', $obj->notifypub(), _YES, _NO);
            $this->addElement($notify_radio);
        }

        if ($this->hasTab(_CO_PUBLISHER_TAB_IMAGES)) {
            $this->startTab(_CO_PUBLISHER_TAB_IMAGES);
        }

        // IMAGE
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_IMAGE_ITEM)) {
            $objimages      = $obj->getImages();
            $mainarray      = is_object($objimages['main']) ? array($objimages['main']) : array();
            $mergedimages   = array_merge($mainarray, $objimages['others']);
            $objimage_array = array();
            foreach ($mergedimages as $imageObj) {
                $objimage_array[$imageObj->getVar('image_name')] = $imageObj->getVar('image_nicename');
            }

            $imgcat_handler = xoops_gethandler('imagecategory');
            if (method_exists($imgcat_handler, 'getListByPermission')) {
                $catlist = $imgcat_handler->getListByPermission($group, 'imgcat_read', 1);
            } else {
                $catlist = $imgcat_handler->getList($group, 'imgcat_read', 1);
            }
            $catids = array_keys($catlist);

            $imageObjs = array();
            if (!empty($catids)) {
                $image_handler = xoops_gethandler('image');
                $criteria      = new CriteriaCompo(new Criteria('imgcat_id', '(' . implode(',', $catids) . ')', 'IN'));
                $criteria->add(new Criteria('image_display', 1));
                $criteria->setSort('image_nicename');
                $criteria->setOrder('ASC');
                $imageObjs = $image_handler->getObjects($criteria, true);
                unset($criteria);
            }
            $image_array = array();
            foreach ($imageObjs as $imageObj) {
                $image_array[$imageObj->getVar('image_name')] = $imageObj->getVar('image_nicename');
            }

            $image_array = array_diff($image_array, $objimage_array);

            $image_select = new XoopsFormSelect('', 'image_notused', '', 5);
            $image_select->addOptionArray($image_array);
            $image_select->setExtra("onchange='showImgSelected(\"image_display\", \"image_notused\", \"uploads/\", \"\", \"" . XOOPS_URL . "\")'");
            //$image_select->setExtra( "onchange='appendMySelectOption(\"image_notused\", \"image_item\")'");
            unset($image_array);

            $image_select2 = new XoopsFormSelect('', 'image_item', '', 5, true);
            $image_select2->addOptionArray($objimage_array);
            $image_select2->setExtra("onchange='publisher_updateSelectOption(\"image_item\", \"image_featured\"), showImgSelected(\"image_display\", \"image_item\", \"uploads/\", \"\", \"" . XOOPS_URL . "\")'");

            $buttonadd = new XoopsFormButton('', 'buttonadd', _CO_PUBLISHER_ADD);
            $buttonadd->setExtra("onclick='publisher_appendSelectOption(\"image_notused\", \"image_item\"), publisher_updateSelectOption(\"image_item\", \"image_featured\")'");

            $buttonremove = new XoopsFormButton('', 'buttonremove', _CO_PUBLISHER_REMOVE);
            $buttonremove->setExtra("onclick='publisher_appendSelectOption(\"image_item\", \"image_notused\"), publisher_updateSelectOption(\"image_item\", \"image_featured\")'");

            $opentable  = new XoopsFormLabel('', "<table><tr><td>");
            $addcol     = new XoopsFormLabel('', "</td><td>");
            $addbreak   = new XoopsFormLabel('', "<br />");
            $closetable = new XoopsFormLabel('', "</td></tr></table>");

            $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/ajaxupload.3.9.js');
            $js_data  = new XoopsFormLabel('', '
<script type= "text/javascript">/*<![CDATA[*/
$publisher(document).ready(function () {
    var button = $publisher("#publisher_upload_button"), interval;
    new AjaxUpload(button,{
        action: "' . PUBLISHER_URL . '/include/ajax_upload.php", // I disabled uploads in this example for security reasons
        responseType: "text/html",
        name: "publisher_upload_file",
        onSubmit : function (file, ext) {
            // change button text, when user selects file
            $publisher("#publisher_upload_message").html(" ");
            button.html("<img src=\'' . PUBLISHER_URL . '/assets/images/loadingbar.gif\'/>"); this.setData({
                "image_nicename": $publisher("#image_nicename").val(),
                "imgcat_id" : $publisher("#imgcat_id").val()
            });
            // If you want to allow uploading only 1 file at time,
            // you can disable upload button
            this.disable();
            interval = window.setInterval(function () {
            }, 200);
        },
        onComplete: function (file, response) {
            button.text("' . _CO_PUBLISHER_IMAGE_UPLOAD_NEW . '");
            window.clearInterval(interval);
            // enable upload button
            this.enable();
            // add file to the list
            var result = eval(response);
            if (result[0] == "success") {
                 $publisher("#image_item").append("<option value=\'" + result[1] + "\' selected=\'selected\'>" + result[2] + "</option>");
                 publisher_updateSelectOption(\'image_item\', \'image_featured\');
                 showImgSelected(\'image_display\', \'image_item\', \'uploads/\', \'\', \'' . XOOPS_URL . '\')
            } else {
                 $publisher("#publisher_upload_message").html("<div class=\'errorMsg\'>" + result[1] + "</div>");
            }
        }
    });
});
/*]]>*/</script>
');
            $messages = new XoopsFormLabel('', "<div id='publisher_upload_message'></div>");
            $button   = new XoopsFormLabel('', "<div id='publisher_upload_button'>" . _CO_PUBLISHER_IMAGE_UPLOAD_NEW . "</div>");
            $nicename = new XoopsFormText('', 'image_nicename', 30, 30, _CO_PUBLISHER_IMAGE_NICENAME);

            $imgcat_handler = xoops_gethandler('imagecategory');
            if (method_exists($imgcat_handler, 'getListByPermission')) {
                $catlist = $imgcat_handler->getListByPermission($group, 'imgcat_read', 1);
            } else {
                $catlist = $imgcat_handler->getList($group, 'imgcat_read', 1);
            }
            $imagecat = new XoopsFormSelect('', 'imgcat_id', '', 1);
            $imagecat->addOptionArray($catlist);

            $image_upload_tray = new XoopsFormElementTray(_CO_PUBLISHER_IMAGE_UPLOAD, '');
            $image_upload_tray->addElement($js_data);
            $image_upload_tray->addElement($messages);
            $image_upload_tray->addElement($opentable);

            $image_upload_tray->addElement($imagecat);

            $image_upload_tray->addElement($addbreak);

            $image_upload_tray->addElement($nicename);

            $image_upload_tray->addElement($addbreak);

            $image_upload_tray->addElement($button);

            $image_upload_tray->addElement($closetable);
            $this->addElement($image_upload_tray);

            $image_tray = new XoopsFormElementTray(_CO_PUBLISHER_IMAGE_ITEMS, '');
            $image_tray->addElement($opentable);

            $image_tray->addElement($image_select);
            $image_tray->addElement($addbreak);
            $image_tray->addElement($buttonadd);

            $image_tray->addElement($addcol);

            $image_tray->addElement($image_select2);
            $image_tray->addElement($addbreak);
            $image_tray->addElement($buttonremove);

            $image_tray->addElement($closetable);
            $image_tray->setDescription(_CO_PUBLISHER_IMAGE_ITEMS_DSC);
            $this->addElement($image_tray);

            $imagename    = is_object($objimages['main']) ? $objimages['main']->getVar('image_name') : '';
            $imageforpath = ($imagename != '') ? $imagename : 'blank.gif';

            $image_select3 = new XoopsFormSelect(_CO_PUBLISHER_IMAGE_ITEM, 'image_featured', $imagename, 1);
            $image_select3->addOptionArray($objimage_array);
            $image_select3->setExtra("onchange='showImgSelected(\"image_display\", \"image_featured\", \"uploads/\", \"\", \"" . XOOPS_URL . "\")'");
            $image_select3->setDescription(_CO_PUBLISHER_IMAGE_ITEM_DSC);
            $this->addElement($image_select3);

            $image_preview = new XoopsFormLabel(_CO_PUBLISHER_IMAGE_PREVIEW, "<img width='500' src='" . XOOPS_URL . "/uploads/" . $imageforpath . "' name='image_display' id='image_display' alt='' />");
            $this->addElement($image_preview);
        }

        if ($this->hasTab(_CO_PUBLISHER_TAB_FILES)) {
            $this->startTab(_CO_PUBLISHER_TAB_FILES);
        }
        // File upload UPLOAD
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_ITEM_UPLOAD_FILE)) {
            // NAME
            $name_text = new XoopsFormText(_CO_PUBLISHER_FILENAME, 'item_file_name', 50, 255, '');
            $name_text->setDescription(_CO_PUBLISHER_FILE_NAME_DSC);
            $this->addElement($name_text);
            unset($name_text);

            // DESCRIPTION
            $description_text = new XoopsFormTextArea(_CO_PUBLISHER_FILE_DESCRIPTION, 'item_file_description', '');
            $description_text->setDescription(_CO_PUBLISHER_FILE_DESCRIPTION_DSC);
            $this->addElement($description_text);
            unset($description_text);

            $status_select = new XoopsFormRadioYN(_CO_PUBLISHER_FILE_STATUS, 'item_file_status', 1); //1 - active
            $status_select->setDescription(_CO_PUBLISHER_FILE_STATUS_DSC);
            $this->addElement($status_select);
            unset($status_select);

            $file_box = new XoopsFormFile(_CO_PUBLISHER_ITEM_UPLOAD_FILE, "item_upload_file", 0);
            $file_box->setDescription(_CO_PUBLISHER_ITEM_UPLOAD_FILE_DSC);
            $file_box->setExtra("size ='50'");
            $this->addElement($file_box);
            unset($file_box);

            if (!$obj->isNew()) {
                $filesObj = $publisher->getHandler('file')->getAllFiles($obj->itemid());
                if (count($filesObj) > 0) {
                    $table = '';
                    $table .= "<table width='100%' cellspacing=1 cellpadding=3 border=0 class = outer>";
                    $table .= "<tr>";
                    $table .= "<td width='50' class='bg3' align='center'><strong>ID</strong></td>";
                    $table .= "<td width='150' class='bg3' align='left'><strong>" . _AM_PUBLISHER_FILENAME . "</strong></td>";
                    $table .= "<td class='bg3' align='left'><strong>" . _AM_PUBLISHER_DESCRIPTION . "</strong></td>";
                    $table .= "<td width='60' class='bg3' align='center'><strong>" . _AM_PUBLISHER_HITS . "</strong></td>";
                    $table .= "<td width='100' class='bg3' align='center'><strong>" . _AM_PUBLISHER_UPLOADED_DATE . "</strong></td>";
                    $table .= "<td width='60' class='bg3' align='center'><strong>" . _AM_PUBLISHER_ACTION . "</strong></td>";
                    $table .= "</tr>";

                    foreach ($filesObj as $fileObj) {
                        $modify = "<a href='file.php?op=mod&fileid=" . $fileObj->fileid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif' title='" . _CO_PUBLISHER_EDITFILE . "' alt='" . _CO_PUBLISHER_EDITFILE . "' /></a>";
                        $delete = "<a href='file.php?op=del&fileid=" . $fileObj->fileid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png' title='" . _CO_PUBLISHER_DELETEFILE . "' alt='" . _CO_PUBLISHER_DELETEFILE . "'/></a>";
                        if ($fileObj->status() == 0) {
                            $not_visible = "<img src='" . PUBLISHER_URL . "/assets/images/no.gif'/>";
                        } else {
                            $not_visible = '';
                        }
                        $table .= "<tr>";
                        $table .= "<td class='head' align='center'>" . $fileObj->getVar('fileid') . "</td>";
                        $table .= "<td class='odd' align='left'>" . $not_visible . $fileObj->getFileLink() . "</td>";
                        $table .= "<td class='even' align='left'>" . $fileObj->description() . "</td>";
                        $table .= "<td class='even' align='center'>" . $fileObj->counter() . "";
                        $table .= "<td class='even' align='center'>" . $fileObj->datesub() . "</td>";
                        $table .= "<td class='even' align='center'> $modify $delete </td>";
                        $table .= "</tr>";
                    }
                    $table .= "</table>";

                    $files_box = new XoopsFormLabel(_CO_PUBLISHER_FILES_LINKED, $table);
                    $this->addElement($files_box);
                    unset($files_box, $filesObj, $fileObj);
                }

            }
        }

        if ($this->hasTab(_CO_PUBLISHER_TAB_OTHERS)) {
            $this->startTab(_CO_PUBLISHER_TAB_OTHERS);
        }
        //$this->startTab(_CO_PUBLISHER_TAB_META);
        // Meta Keywords
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_ITEM_META_KEYWORDS)) {
            $text_meta_keywords = new XoopsFormTextArea(_CO_PUBLISHER_ITEM_META_KEYWORDS, 'item_meta_keywords', $obj->meta_keywords('e'), 7, 60);
            $text_meta_keywords->setDescription(_CO_PUBLISHER_ITEM_META_KEYWORDS_DSC);
            $this->addElement($text_meta_keywords);
        }

        // Meta Description
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_ITEM_META_DESCRIPTION)) {
            $text_meta_description = new XoopsFormTextArea(_CO_PUBLISHER_ITEM_META_DESCRIPTION, 'item_meta_description', $obj->meta_description('e'), 7, 60);
            $text_meta_description->setDescription(_CO_PUBLISHER_ITEM_META_DESCRIPTION_DSC);
            $this->addElement($text_meta_description);
        }

        //$this->startTab(_CO_PUBLISHER_TAB_PERMISSIONS);

        // COMMENTS
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_ALLOWCOMMENTS)) {
            $addcomments_radio = new XoopsFormRadioYN(_CO_PUBLISHER_ALLOWCOMMENTS, 'allowcomments', $obj->cancomment(), _YES, _NO);
            $this->addElement($addcomments_radio);
        }

        // WEIGHT
        if ($this->isGranted(PublisherConstantsInterface::PUBLISHER_WEIGHT)) {
            $this->addElement(new XoopsFormText(_CO_PUBLISHER_WEIGHT, 'weight', 5, 5, $obj->weight()));
        }

        $this->endTabs();

        //COMMON TO ALL TABS

        $button_tray = new XoopsFormElementTray('', '');

        if (!$obj->isNew()) {
            $button_tray->addElement(new XoopsFormButton('', 'additem', _SUBMIT, 'submit')); //orclone

        } else {
            $button_tray->addElement(new XoopsFormButton('', 'additem', _CO_PUBLISHER_CREATE, 'submit'));
            $button_tray->addElement(new XoopsFormButton('', '', _CO_PUBLISHER_CLEAR, 'reset'));
        }

        $button_tray->addElement(new XoopsFormButton('', 'preview', _CO_PUBLISHER_PREVIEW, 'submit'));

        $butt_cancel = new XoopsFormButton('', '', _CO_PUBLISHER_CANCEL, 'button');
        $butt_cancel->setExtra('onclick="history.go(-1)"');
        $button_tray->addElement($butt_cancel);

        $this->addElement($button_tray);

        $hidden = new XoopsFormHidden('itemid', $obj->itemid());
        $this->addElement($hidden);
        unset($hidden);

        return $this;
    }
}
