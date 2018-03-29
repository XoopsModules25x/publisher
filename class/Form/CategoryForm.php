<?php namespace XoopsModules\Publisher\Form;

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
 */

use Xmf\Request;
use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || exit("XOOPS root path not defined");

require_once __DIR__ . '/../../include/common.php';

xoops_load('XoopsFormLoader');
require_once $GLOBALS['xoops']->path('class/tree.php');

/**
 * Class Publisher\CategoryForm
 */
class CategoryForm extends \XoopsThemeForm
{
    /**
     * @var Publisher
     * @access public
     */
    public $helper;

    public $targetObject;

    public $subCatsCount = 4;

    public $userGroups = [];

    /**
     * @param     $target
     * @param int $subCatsCount
     */
    public function __construct(&$target, $subCatsCount = 4)
    {
        $this->helper = Publisher\Helper::getInstance();

        $this->targetObject =& $target;
        $this->subCatsCount = $subCatsCount;

        $memberHandler    = xoops_getHandler('member');
        $this->userGroups = $memberHandler->getGroupList();

        parent::__construct(_AM_PUBLISHER_CATEGORY, 'form', xoops_getenv('PHP_SELF'), 'post', true);
        $this->setExtra('enctype="multipart/form-data"');

        $this->createElements();
        $this->createButtons();
    }

    public function createElements()
    {
        require_once __DIR__ . '/../../include/common.php';
        // Category
        $criteria = new \Criteria(null);
        $criteria->setSort('weight');
        $criteria->setOrder('ASC');
        $myTree        = new \XoopsObjectTree($this->helper->getHandler('Category')->getObjects($criteria), 'categoryid', 'parentid');
        $moduleDirName = basename(dirname(__DIR__));
        $module        = \XoopsModule::getByDirname($moduleDirName);
        if (Publisher\Utility::checkVerXoops($GLOBALS['xoopsModule'], '2.5.9')) {
            $catSelect = $myTree->makeSelectElement('parentid', 'name', '--', $this->targetObject->parentid(), true, 0, '', _AM_PUBLISHER_PARENT_CATEGORY_EXP);
            $this->addElement($catSelect);
        } else {
            $catSelect = $myTree->makeSelBox('parentid', 'name', '--', $this->targetObject->parentid(), true);
            $this->addElement(new \XoopsFormLabel(_AM_PUBLISHER_PARENT_CATEGORY_EXP, $catSelect));
        }

        // Name
        $this->addElement(new \XoopsFormText(_AM_PUBLISHER_CATEGORY, 'name', 50, 255, $this->targetObject->name('e')), true);

        // Description
        $this->addElement(new \XoopsFormTextArea(_AM_PUBLISHER_COLDESCRIPT, 'description', $this->targetObject->description('e'), 7, 60));

        // EDITOR
        $groups         = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        $gpermHandler   = $this->helper->getHandler('Groupperm');
        $moduleId       = $this->helper->getModule()->mid();
        $allowedEditors = Publisher\Utility::getEditors($gpermHandler->getItemIds('editors', $groups, $moduleId));
        $nohtml         = false;
        if (count($allowedEditors) > 0) {
            $editor = Request::getString('editor', '', 'POST');
            if (!empty($editor)) {
                Publisher\Utility::setCookieVar('publisher_editor', $editor);
            } else {
                $editor = Publisher\Utility::getCookieVar('publisher_editor');
                if (empty($editor) && is_object($GLOBALS['xoopsUser'])) {
                    $editor = (null !== $GLOBALS['xoopsUser']->getVar('publisher_editor')) ? $GLOBALS['xoopsUser']->getVar('publisher_editor') : ''; // Need set through user profile
                }
            }
            $editor     = (empty($editor) || !in_array($editor, $allowedEditors)) ? $this->helper->getConfig('submit_editor') : $editor;
            $formEditor = new \XoopsFormSelectEditor($this, 'editor', $editor, $nohtml, $allowedEditors);
            $this->addElement($formEditor);
        } else {
            $editor = $this->helper->getConfig('submit_editor');
        }

        $editorConfigs           = [];
        $editorConfigs['rows']   = '' == $this->helper->getConfig('submit_editor_rows') ? 35 : $this->helper->getConfig('submit_editor_rows');
        $editorConfigs['cols']   = '' == $this->helper->getConfig('submit_editor_cols') ? 60 : $this->helper->getConfig('submit_editor_cols');
        $editorConfigs['width']  = '' == $this->helper->getConfig('submit_editor_width') ? '100%' : $this->helper->getConfig('submit_editor_width');
        $editorConfigs['height'] = '' == $this->helper->getConfig('submit_editor_height') ? '400px' : $this->helper->getConfig('submit_editor_height');

        $editorConfigs['name']  = 'header';
        $editorConfigs['value'] = $this->targetObject->header('e');

        $textHeader = new \XoopsFormEditor(_AM_PUBLISHER_CATEGORY_HEADER, $editor, $editorConfigs, $nohtml, $onfailure = null);
        $textHeader->setDescription(_AM_PUBLISHER_CATEGORY_HEADER_DSC);
        $this->addElement($textHeader);

        // IMAGE
        $imageArray  = \XoopsLists::getImgListAsArray(Publisher\Utility::getImageDir('category'));
        $imageSelect = new \XoopsFormSelect('', 'image', $this->targetObject->getImage());
        //$imageSelect -> addOption ('-1', '---------------');
        $imageSelect->addOptionArray($imageArray);
        $imageSelect->setExtra("onchange='showImgSelected(\"image3\", \"image\", \"" . 'uploads/' . PUBLISHER_DIRNAME . '/images/category/' . '", "", "' . XOOPS_URL . "\")'");
        $imageTray = new \XoopsFormElementTray(_AM_PUBLISHER_IMAGE, '&nbsp;');
        $imageTray->addElement($imageSelect);
        $imageTray->addElement(new \XoopsFormLabel('', "<br><br><img src='" . Publisher\Utility::getImageDir('category', false) . $this->targetObject->getImage() . "' name='image3' id='image3' alt=''>"));
        $imageTray->setDescription(_AM_PUBLISHER_IMAGE_DSC);
        $this->addElement($imageTray);

        // IMAGE UPLOAD
        $max_size = 5000000;
        $fileBox  = new \XoopsFormFile(_AM_PUBLISHER_IMAGE_UPLOAD, 'image_file', $max_size);
        $fileBox->setExtra("size ='45'");
        $fileBox->setDescription(_AM_PUBLISHER_IMAGE_UPLOAD_DSC);
        $this->addElement($fileBox);

        // Short url
        $textShortUrl = new \XoopsFormText(_AM_PUBLISHER_CATEGORY_SHORT_URL, 'short_url', 50, 255, $this->targetObject->short_url('e'));
        $textShortUrl->setDescription(_AM_PUBLISHER_CATEGORY_SHORT_URL_DSC);
        $this->addElement($textShortUrl);

        // Meta Keywords
        $textMetaKeywords = new \XoopsFormTextArea(_AM_PUBLISHER_CATEGORY_META_KEYWORDS, 'meta_keywords', $this->targetObject->meta_keywords('e'), 7, 60);
        $textMetaKeywords->setDescription(_AM_PUBLISHER_CATEGORY_META_KEYWORDS_DSC);
        $this->addElement($textMetaKeywords);

        // Meta Description
        $textMetaDescription = new \XoopsFormTextArea(_AM_PUBLISHER_CATEGORY_META_DESCRIPTION, 'meta_description', $this->targetObject->meta_description('e'), 7, 60);
        $textMetaDescription->setDescription(_AM_PUBLISHER_CATEGORY_META_DESCRIPTION_DSC);
        $this->addElement($textMetaDescription);

        // Weight
        $this->addElement(new \XoopsFormText(_AM_PUBLISHER_COLPOSIT, 'weight', 4, 4, $this->targetObject->weight()));

        // Added by skalpa: custom template support
        //todo, check this
        $this->addElement(new \XoopsFormText('Custom template', 'template', 50, 255, $this->targetObject->template('e')), false);

        // READ PERMISSIONS
        $readPermissionsTray   = new \XoopsFormElementTray(_AM_PUBLISHER_PERMISSIONS_CAT_READ, '');
        $selectAllReadCheckbox = new \XoopsFormCheckBox('', 'adminbox', 1);
        $selectAllReadCheckbox->addOption('allbox', _AM_SYSTEM_ALL);
        $selectAllReadCheckbox->setExtra(" onclick='xoopsCheckGroup(\"form\", \"adminbox\" , \"groupsRead[]\");' ");
        $selectAllReadCheckbox->setClass('xo-checkall');
        $readPermissionsTray->addElement($selectAllReadCheckbox);

        $groupsReadCheckbox = new \XoopsFormCheckBox('', 'groupsRead[]', $this->targetObject->getGroupsRead());

        foreach ($this->userGroups as $group_id => $group_name) {
            $groupsReadCheckbox->addOption($group_id, $group_name);
        }
        $readPermissionsTray->addElement($groupsReadCheckbox);
        $this->addElement($readPermissionsTray);

        // SUBMIT PERMISSIONS
        $submitPermissionsTray = new \XoopsFormElementTray(_AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT, '');
        $submitPermissionsTray->setDescription(_AM_PUBLISHER_PERMISSIONS_CAT_SUBMIT_DSC);

        $selectAllSubmitCheckbox = new \XoopsFormCheckBox('', 'adminbox2', 1);
        $selectAllSubmitCheckbox->addOption('allbox', _AM_SYSTEM_ALL);
        $selectAllSubmitCheckbox->setExtra(" onclick='xoopsCheckGroup(\"form\", \"adminbox2\" , \"groupsSubmit[]\");' ");
        $selectAllSubmitCheckbox->setClass('xo-checkall');
        $submitPermissionsTray->addElement($selectAllSubmitCheckbox);

        $groupsSubmitCheckbox = new \XoopsFormCheckBox('', 'groupsSubmit[]', $this->targetObject->getGroupsSubmit());
        foreach ($this->userGroups as $group_id => $group_name) {
            $groupsSubmitCheckbox->addOption($group_id, $group_name);
        }
        $submitPermissionsTray->addElement($groupsSubmitCheckbox);
        $this->addElement($submitPermissionsTray);

        // MODERATION PERMISSIONS
        $moderatePermissionsTray = new \XoopsFormElementTray(_AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR, '');
        $moderatePermissionsTray->setDescription(_AM_PUBLISHER_PERMISSIONS_CAT_MODERATOR_DSC);

        $selectAllModerateCheckbox = new \XoopsFormCheckBox('', 'adminbox3', 1);
        $selectAllModerateCheckbox->addOption('allbox', _AM_SYSTEM_ALL);
        $selectAllModerateCheckbox->setExtra(" onclick='xoopsCheckGroup(\"form\", \"adminbox3\" , \"groupsModeration[]\");' ");
        $selectAllModerateCheckbox->setClass('xo-checkall');
        $moderatePermissionsTray->addElement($selectAllModerateCheckbox);

        $groupsModerationCheckbox = new \XoopsFormCheckBox('', 'groupsModeration[]', $this->targetObject->getGroupsModeration());

        foreach ($this->userGroups as $group_id => $group_name) {
            $groupsModerationCheckbox->addOption($group_id, $group_name);
        }
        $moderatePermissionsTray->addElement($groupsModerationCheckbox);
        $this->addElement($moderatePermissionsTray);

        $moderator = new \XoopsFormSelectUser(_AM_PUBLISHER_CATEGORY_MODERATOR, 'moderator', true, $this->targetObject->moderator('e'), 1, false);
        $moderator->setDescription(_AM_PUBLISHER_CATEGORY_MODERATOR_DSC);
        $this->addElement($moderator);

        //SUBCATEGORY
        $catTray = new \XoopsFormElementTray(_AM_PUBLISHER_SCATEGORYNAME, '<br><br>');
        for ($i = 0; $i < $this->subCatsCount; ++$i) {
            $subname = '';
            if ($i < (($scname = Request::getArray('scname', [], 'POST')) ? count($scname) : 0)) {
                $temp    = Request::getArray('scname', [], 'POST');
                $subname = ($scname = Request::getArray('scname', '', 'POST')) ? $temp[$i] : '';
            }
            $catTray->addElement(new \XoopsFormText('', 'scname[' . $i . ']', 50, 255, $subname));
        }
        $t = new \XoopsFormText('', 'nb_subcats', 3, 2);
        $l = new \XoopsFormLabel('', sprintf(_AM_PUBLISHER_ADD_OPT, $t->render()));
        $b = new \XoopsFormButton('', 'submit_subcats', _AM_PUBLISHER_ADD_OPT_SUBMIT, 'submit');

        if (!$this->targetObject->categoryid()) {
            $b->setExtra('onclick="this.form.elements.op.value=\'addsubcats\'"');
        } else {
            $b->setExtra('onclick="this.form.elements.op.value=\'mod\'"');
        }

        $r = new \XoopsFormElementTray('');
        $r->addElement($l);
        $r->addElement($b);
        $catTray->addElement($r);
        $this->addElement($catTray);

        $this->addElement(new \XoopsFormHidden('categoryid', $this->targetObject->categoryid()));
        $this->addElement(new \XoopsFormHidden('nb_sub_yet', $this->subCatsCount));
    }

    public function createButtons()
    {
        // Action buttons tray
        $buttonTray = new \XoopsFormElementTray('', '');

        // No ID for category -- then it's new category, button says 'Create'
        if (!$this->targetObject->categoryid()) {
            $buttonTray->addElement(new \XoopsFormButton('', 'addcategory', _AM_PUBLISHER_CREATE, 'submit'));

            $buttClear = new \XoopsFormButton('', '', _AM_PUBLISHER_CLEAR, 'reset');
            $buttonTray->addElement($buttClear);

            $buttCancel = new \XoopsFormButton('', '', _AM_PUBLISHER_CANCEL, 'button');
            $buttCancel->setExtra('onclick="history.go(-1)"');
            $buttonTray->addElement($buttCancel);

            $this->addElement($buttonTray);
        } else {
            $buttonTray->addElement(new \XoopsFormButton('', 'addcategory', _AM_PUBLISHER_MODIFY, 'submit'));
            $buttCancel = new \XoopsFormButton('', '', _AM_PUBLISHER_CANCEL, 'button');
            $buttCancel->setExtra('onclick="history.go(-1)"');
            $buttonTray->addElement($buttCancel);

            $this->addElement($buttonTray);
        }
    }
}
