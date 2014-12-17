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
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: category.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || die("XOOPS root path not defined");

include_once dirname(dirname(__DIR__)) . '/include/common.php';

xoops_load('XoopsFormLoader');
include_once XOOPS_ROOT_PATH . '/class/tree.php';

/**
 * Class PublisherCategoryForm
 */
class PublisherCategoryForm extends XoopsThemeForm
{
    /**
     * @var PublisherPublisher
     * @access public
     */
    public $publisher = null;

    public $targetObject = null;

    public $subCatsCount = 4;

    public $userGroups = array();

    /**
     * @param     $target
     * @param int $subCatsCount
     */
    public function __construct(&$target, $subCatsCount = 4)
    {
        $this->publisher = PublisherPublisher::getInstance();

        $this->targetObject = $target;
        $this->subCatsCount = $subCatsCount;

        $member_handler = xoops_gethandler('member');
        $this->userGroups = $member_handler->getGroupList();

        parent::__construct(_AM_PUBLISHER_CATEGORY, "form", xoops_getenv('PHP_SELF'));
        $this->setExtra('enctype="multipart/form-data"');

        $this->createElements();
        $this->createButtons();
    }

    public function createElements()
    {
        global $xoopsUser;

        // Category
        $criteria = new Criteria(null);
        $criteria->setSort('weight');
        $criteria->setOrder('ASC');
        $mytree = new XoopsObjectTree($this->publisher->getHandler('category')->getObjects($criteria), "categoryid", "parentid");
        $cat_select = $mytree->makeSelBox('parentid', 'name', '--', $this->targetObject->parentid(), true);
        $this->addElement(new XoopsFormLabel(_AM_PUBLISHER_PARENT_CATEGORY_EXP, $cat_select));

        // Name
        $this->addElement(new XoopsFormText(_AM_PUBLISHER_CATEGORY, 'name', 50, 255, $this->targetObject->name('e')), true);

        // Description
        $this->addElement(new XoopsFormTextArea(_AM_PUBLISHER_COLDESCRIPT, 'description', $this->targetObject->description('e'), 7, 60));

        // EDITOR
        $groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
        $gperm_handler = $this->publisher->getHandler('groupperm');
        $module_id = $this->publisher->getModule()->mid();
        $allowed_editors = publisher_getEditors($gperm_handler->getItemIds('editors', $groups, $module_id));
        $nohtml = false;
        if (count($allowed_editors) > 0) {
            $editor = @$_POST['editor'];
            if (!empty($editor)) {
                publisher_setCookieVar('publisher_editor', $editor);
            } else {
                $editor = publisher_getCookieVar('publisher_editor');
                if (empty($editor) && is_object($xoopsUser)) {
                    $editor = @ $xoopsUser->getVar('publisher_editor'); // Need set through user profile
                }
            }
            $editor = (empty($editor) || !in_array($editor, $allowed_editors)) ? $this->publisher->getConfig('submit_editor') : $editor;
            $form_editor = new XoopsFormSelectEditor($this, 'editor', $editor, $nohtml, $allowed_editors);
            $this->addElement($form_editor);
        } else {
            $editor = $this->publisher->getConfig('submit_editor');
        }

        $editor_configs = array();
        $editor_configs['rows'] = $this->publisher->getConfig('submit_editor_rows') == '' ? 35 : $this->publisher->getConfig('submit_editor_rows');
        $editor_configs['cols'] = $this->publisher->getConfig('submit_editor_cols') == '' ? 60 : $this->publisher->getConfig('submit_editor_cols');
        $editor_configs['width'] = $this->publisher->getConfig('submit_editor_width') == '' ? "100%" : $this->publisher->getConfig('submit_editor_width');
        $editor_configs['height'] = $this->publisher->getConfig('submit_editor_height') == '' ? "400px" : $this->publisher->getConfig('submit_editor_height');

        $editor_configs['name'] = 'header';
        $editor_configs['value'] = $this->targetObject->header('e');

        $text_header = new XoopsFormEditor(_AM_PUBLISHER_CATEGORY_HEADER, $editor, $editor_configs, $nohtml, $onfailure = null);
        $text_header->setDescription(_AM_PUBLISHER_CATEGORY_HEADER_DSC);
        $this->addElement($text_header);

        // IMAGE
        $image_array = XoopsLists::getImgListAsArray(publisher_getImageDir('category'));
        $image_select = new XoopsFormSelect('', 'image', $this->targetObject->image());
        //$image_select -> addOption ('-1', '---------------');
        $image_select->addOptionArray($image_array);
        $image_select->setExtra("onchange='showImgSelected(\"image3\", \"image\", \"" . 'uploads/' . PUBLISHER_DIRNAME . '/images/category/' . "\", \"\", \"" . XOOPS_URL . "\")'");
        $image_tray = new XoopsFormElementTray(_AM_PUBLISHER_IMAGE, '&nbsp;');
        $image_tray->addElement($image_select);
        $image_tray->addElement(new XoopsFormLabel('', "<br /><br /><img src='" . publisher_getImageDir('category', false) . $this->targetObject->image() . "' name='image3' id='image3' alt='' />"));
        $image_tray->setDescription(_AM_PUBLISHER_IMAGE_DSC);
        $this->addElement($image_tray);

        // IMAGE UPLOAD
        $max_size = 5000000;
        $file_box = new XoopsFormFile(_AM_PUBLISHER_IMAGE_UPLOAD, "image_file", $max_size);
        $file_box->setExtra("size ='45'");
        $file_box->setDescription(_AM_PUBLISHER_IMAGE_UPLOAD_DSC);
        $this->addElement($file_box);

        // Short url
        $text_short_url = new XoopsFormText(_AM_PUBLISHER_CATEGORY_SHORT_URL, 'short_url', 50, 255, $this->targetObject->short_url('e'));
        $text_short_url->setDescription(_AM_PUBLISHER_CATEGORY_SHORT_URL_DSC);
        $this->addElement($text_short_url);

        // Meta Keywords
        $text_meta_keywords = new XoopsFormTextArea(_AM_PUBLISHER_CATEGORY_META_KEYWORDS, 'meta_keywords', $this->targetObject->meta_keywords('e'), 7, 60);
        $text_meta_keywords->setDescription(_AM_PUBLISHER_CATEGORY_META_KEYWORDS_DSC);
        $this->addElement($text_meta_keywords);

        // Meta Description
        $text_meta_description = new XoopsFormTextArea(_AM_PUBLISHER_CATEGORY_META_DESCRIPTION, 'meta_description', $this->targetObject->meta_description('e'), 7, 60);
        $text_meta_description->setDescription(_AM_PUBLISHER_CATEGORY_META_DESCRIPTION_DSC);
        $this->addElement($text_meta_description);

        // Weight
        $this->addElement(new XoopsFormText(_AM_PUBLISHER_COLPOSIT, 'weight', 4, 4, $this->targetObject->weight()));

        // Added by skalpa: custom template support
        //todo, check this
        $this->addElement(new XoopsFormText("Custom template", 'template', 50, 255, $this->targetObject->template('e')), false);

        // READ PERMISSIONS
        $groups_read_checkbox = new XoopsFormCheckBox(_AM_PUBLISHER_PERMISSIONS_CAT_READ, 'groups_read[]', $this->targetObject->getGroups_read());
        foreach ($this->userGroups as $group_id => $group_name) {
            $groups_read_checkbox->addOption($group_id, $group_name);
        }
        $this->addElement($groups_read_checkbox);

        // SUBMIT PERMISSIONS
        $groups_submit_checkbox = new XoopsFormCheckBox(_AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT, 'groups_submit[]', $this->targetObject->getGroups_submit());
        $groups_submit_checkbox->setDescription(_AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT_DSC);
        foreach ($this->userGroups as $group_id => $group_name) {
            $groups_submit_checkbox->addOption($group_id, $group_name);
        }
        $this->addElement($groups_submit_checkbox);

        // MODERATION PERMISSIONS
        $groups_moderation_checkbox = new XoopsFormCheckBox(_AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR, 'groups_moderation[]', $this->targetObject->getGroups_moderation());
        $groups_moderation_checkbox->setDescription(_AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR_DSC);
        foreach ($this->userGroups as $group_id => $group_name) {
            $groups_moderation_checkbox->addOption($group_id, $group_name);
        }
        $this->addElement($groups_moderation_checkbox);

        $moderator = new XoopsFormSelectUser(_AM_PUBLISHER_CATEGORY_MODERATOR, 'moderator', true, $this->targetObject->moderator('e'), 1, false);
        $moderator->setDescription(_AM_PUBLISHER_CATEGORY_MODERATOR_DSC);
        $this->addElement($moderator);

        $cat_tray = new XoopsFormElementTray(_AM_PUBLISHER_SCATEGORYNAME, '<br /><br />');
        for ($i = 0; $i < $this->subCatsCount; ++$i) {
            if ($i < (isset($_POST['scname']) ? sizeof($_POST['scname']) : 0)) {
                $subname = isset($_POST['scname']) ? XoopsRequest::getArray('scname', array(), 'POST')[$i] : '';
            } else {
                $subname = '';
            }
            $cat_tray->addElement(new XoopsFormText('', 'scname[' . $i . ']', 50, 255, $subname));

        }
        $t = new XoopsFormText('', 'nb_subcats', 3, 2);
        $l = new XoopsFormLabel('', sprintf(_AM_PUBLISHER_ADD_OPT, $t->render()));
        $b = new XoopsFormButton('', 'submit_subcats', _AM_PUBLISHER_ADD_OPT_SUBMIT, 'submit');

        if (!$this->targetObject->categoryid()) {
            $b->setExtra('onclick="this.form.elements.op.value=\'addsubcats\'"');
        } else {
            $b->setExtra('onclick="this.form.elements.op.value=\'mod\'"');
        }

        $r = new XoopsFormElementTray('');
        $r->addElement($l);
        $r->addElement($b);
        $cat_tray->addElement($r);
        $this->addElement($cat_tray);

        $this->addElement(new XoopsFormHidden('categoryid', $this->targetObject->categoryid()));
        $this->addElement(new XoopsFormHidden('nb_sub_yet', $this->subCatsCount));
    }

    public function createButtons()
    {
        // Action buttons tray
        $button_tray = new XoopsFormElementTray('', '');

        // No ID for category -- then it's new category, button says 'Create'
        if (!$this->targetObject->categoryid()) {

            $button_tray->addElement(new XoopsFormButton('', 'addcategory', _AM_PUBLISHER_CREATE, 'submit'));

            $butt_clear = new XoopsFormButton('', '', _AM_PUBLISHER_CLEAR, 'reset');
            $button_tray->addElement($butt_clear);

            $butt_cancel = new XoopsFormButton('', '', _AM_PUBLISHER_CANCEL, 'button');
            $butt_cancel->setExtra('onclick="history.go(-1)"');
            $button_tray->addElement($butt_cancel);

            $this->addElement($button_tray);
        } else {

            $button_tray->addElement(new XoopsFormButton('', 'addcategory', _AM_PUBLISHER_MODIFY, 'submit'));

            $butt_cancel = new XoopsFormButton('', '', _AM_PUBLISHER_CANCEL, 'button');
            $butt_cancel->setExtra('onclick="history.go(-1)"');
            $button_tray->addElement($butt_cancel);

            $this->addElement($button_tray);
        }
    }
}
