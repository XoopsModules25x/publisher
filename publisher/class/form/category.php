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

// defined('XOOPS_ROOT_PATH') || exit("XOOPS root path not defined");

include_once dirname(dirname(__DIR__)) . '/include/common.php';

xoops_load('XoopsFormLoader');
include_once $GLOBALS['xoops']->path('class/tree.php');

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

        $member_handler   = xoops_gethandler('member');
        $this->userGroups = $member_handler->getGroupList();

        parent::__construct(_AM_PUBLISHER_CATEGORY, "form", xoops_getenv('PHP_SELF'));
        $this->setExtra('enctype="multipart/form-data"');

        $this->createElements();
        $this->createButtons();
    }

    public function createElements()
    {

        // Category
        $criteria = new Criteria(null);
        $criteria->setSort('weight');
        $criteria->setOrder('ASC');
        $mytree     = new XoopsObjectTree($this->publisher->getHandler('category')->getObjects($criteria), "categoryid", "parentid");
        $cat_select = $mytree->makeSelBox('parentid', 'name', '--', $this->targetObject->parentid(), true);
        $this->addElement(new XoopsFormLabel(_AM_PUBLISHER_PARENT_CATEGORY_EXP, $cat_select));

        // Name
        $this->addElement(new XoopsFormText(_AM_PUBLISHER_CATEGORY, 'name', 50, 255, $this->targetObject->name('e')), true);

        // Description
        $this->addElement(new XoopsFormTextArea(_AM_PUBLISHER_COLDESCRIPT, 'description', $this->targetObject->description('e'), 7, 60));

        // EDITOR
        $groups          = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        $gperm_handler   = $this->publisher->getHandler('groupperm');
        $module_id       = $this->publisher->getModule()->mid();
        $allowedEditors = publisherGetEditors($gperm_handler->getItemIds('editors', $groups, $module_id));
        $nohtml          = false;
        if (count($allowedEditors) > 0) {
            $editor = XoopsRequest::getString('editor', '', 'POST');
            if (!empty($editor)) {
                publisherSetCookieVar('publisher_editor', $editor);
            } else {
                $editor = publisherGetCookieVar('publisher_editor');
                if (empty($editor) && is_object($GLOBALS['xoopsUser'])) {
                    $editor = (null !== ($GLOBALS['xoopsUser']->getVar('publisher_editor'))) ? $GLOBALS['xoopsUser']->getVar('publisher_editor') : ''; // Need set through user profile
                }
            }
            $editor      = (empty($editor) || !in_array($editor, $allowedEditors)) ? $this->publisher->getConfig('submit_editor') : $editor;
            $form_editor = new XoopsFormSelectEditor($this, 'editor', $editor, $nohtml, $allowedEditors);
            $this->addElement($form_editor);
        } else {
            $editor = $this->publisher->getConfig('submit_editor');
        }

        $editor_configs           = array();
        $editor_configs['rows']   = $this->publisher->getConfig('submit_editor_rows') == '' ? 35 : $this->publisher->getConfig('submit_editor_rows');
        $editor_configs['cols']   = $this->publisher->getConfig('submit_editor_cols') == '' ? 60 : $this->publisher->getConfig('submit_editor_cols');
        $editor_configs['width']  = $this->publisher->getConfig('submit_editor_width') == '' ? "100%" : $this->publisher->getConfig('submit_editor_width');
        $editor_configs['height'] = $this->publisher->getConfig('submit_editor_height') == '' ? "400px" : $this->publisher->getConfig('submit_editor_height');

        $editor_configs['name']  = 'header';
        $editor_configs['value'] = $this->targetObject->header('e');

        $text_header = new XoopsFormEditor(_AM_PUBLISHER_CATEGORY_HEADER, $editor, $editor_configs, $nohtml, $onfailure = null);
        $text_header->setDescription(_AM_PUBLISHER_CATEGORY_HEADER_DSC);
        $this->addElement($text_header);

        // IMAGE
        $image_array  = XoopsLists::getImgListAsArray(publisherGetImageDir('category'));
        $image_select = new XoopsFormSelect('', 'image', $this->targetObject->image());
        //$image_select -> addOption ('-1', '---------------');
        $image_select->addOptionArray($image_array);
        $image_select->setExtra("onchange='showImgSelected(\"image3\", \"image\", \"" . 'uploads/' . PUBLISHER_DIRNAME . '/images/category/' . "\", \"\", \"" . XOOPS_URL . "\")'");
        $image_tray = new XoopsFormElementTray(_AM_PUBLISHER_IMAGE, '&nbsp;');
        $image_tray->addElement($image_select);
        $image_tray->addElement(new XoopsFormLabel('', "<br /><br /><img src='" . publisherGetImageDir('category', false) . $this->targetObject->image() . "' name='image3' id='image3' alt='' />"));
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
        $groupsReadCheckbox = new XoopsFormCheckBox(_AM_PUBLISHER_PERMISSIONS_CAT_READ, 'groupsRead[]', $this->targetObject->getGroupsRead());
        foreach ($this->userGroups as $group_id => $group_name) {
            $groupsReadCheckbox->addOption($group_id, $group_name);
        }
        $this->addElement($groupsReadCheckbox);

        // SUBMIT PERMISSIONS
        $groupsSubmitCheckbox = new XoopsFormCheckBox(_AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT, 'groupsSubmit[]', $this->targetObject->getGroupsSubmit());
        $groupsSubmitCheckbox->setDescription(_AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT_DSC);
        foreach ($this->userGroups as $group_id => $group_name) {
            $groupsSubmitCheckbox->addOption($group_id, $group_name);
        }
        $this->addElement($groupsSubmitCheckbox);

        // MODERATION PERMISSIONS
        $groupsModerationCheckbox = new XoopsFormCheckBox(_AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR, 'groupsModeration[]', $this->targetObject->getGroupsModeration());
        $groupsModerationCheckbox->setDescription(_AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR_DSC);
        foreach ($this->userGroups as $group_id => $group_name) {
            $groupsModerationCheckbox->addOption($group_id, $group_name);
        }
        $this->addElement($groupsModerationCheckbox);

        $moderator = new XoopsFormSelectUser(_AM_PUBLISHER_CATEGORY_MODERATOR, 'moderator', true, $this->targetObject->moderator('e'), 1, false);
        $moderator->setDescription(_AM_PUBLISHER_CATEGORY_MODERATOR_DSC);
        $this->addElement($moderator);

        $cat_tray = new XoopsFormElementTray(_AM_PUBLISHER_SCATEGORYNAME, '<br /><br />');
        for ($i = 0; $i < $this->subCatsCount; ++$i) {

            if ($i < (($scname = XoopsRequest::getString('scname', '', 'POST'))) ? sizeof($scname) : 0) {
                $subname = ($scname = XoopsRequest::getString('scname', '', 'POST')) ? XoopsRequest::getArray('scname', array(), 'POST')[$i] : '';
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
