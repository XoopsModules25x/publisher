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

use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

// defined('XOOPS_ROOT_PATH') || exit("XOOPS root path not defined");

require_once __DIR__ . '/../../include/common.php';

xoops_load('XoopsFormLoader');
//todo: move to admin?
//xoops_loadLanguage('main', 'publisher');
$helper = Publisher\Helper::getInstance();
$helper->loadLanguage('main');

/**
 * Class FileForm
 */
class FileForm extends \XoopsThemeForm
{
    /**
     * @var Publisher
     * @access public
     */
    public $helper;

    public $targetObject;

    /**
     * @param $target
     */
    public function __construct(&$target)
    {
        $this->helper    = Publisher\Helper::getInstance();
        $this->targetObject =& $target;

        parent::__construct(_AM_PUBLISHER_UPLOAD_FILE, 'form', xoops_getenv('PHP_SELF'), 'post', true);
        $this->setExtra('enctype="multipart/form-data"');

        $this->createElements();
        $this->createButtons();
    }

    public function createElements()
    {
        // NAME
        $nameText = new \XoopsFormText(_CO_PUBLISHER_FILENAME, 'name', 50, 255, $this->targetObject->name());
        $nameText->setDescription(_CO_PUBLISHER_FILE_NAME_DSC);
        $this->addElement($nameText, true);

        // DESCRIPTION
        $descriptionText = new \XoopsFormTextArea(_CO_PUBLISHER_FILE_DESCRIPTION, 'description', $this->targetObject->description());
        $descriptionText->setDescription(_CO_PUBLISHER_FILE_DESCRIPTION_DSC);
        $this->addElement($descriptionText);

        // FILE TO UPLOAD
        //if (!$this->targetObject->fileid()) {
        $fileBox = new \XoopsFormFile(_CO_PUBLISHER_FILE_TO_UPLOAD, 'item_upload_file', 0);
        $fileBox->setExtra("size ='50'");
        $this->addElement($fileBox);
        //}

        $statusSelect = new \XoopsFormRadioYN(_CO_PUBLISHER_FILE_STATUS, 'file_status', Constants::PUBLISHER_STATUS_FILE_ACTIVE);
        $statusSelect->setDescription(_CO_PUBLISHER_FILE_STATUS_DSC);
        $this->addElement($statusSelect);

        // fileid
        $this->addElement(new \XoopsFormHidden('fileid', $this->targetObject->fileid()));

        // itemid
        $this->addElement(new \XoopsFormHidden('itemid', $this->targetObject->itemid()));
    }

    public function createButtons()
    {
        $filesButtonTray = new \XoopsFormElementTray('', '');
        $filesHidden     = new \XoopsFormHidden('op', 'uploadfile');
        $filesButtonTray->addElement($filesHidden);

        if (!$this->targetObject->fileid()) {
            $filesButtonCreate = new \XoopsFormButton('', '', _MD_PUBLISHER_UPLOAD, 'submit');
            $filesButtonCreate->setExtra('onclick="this.form.elements.op.value=\'uploadfile\'"');
            $filesButtonTray->addElement($filesButtonCreate);

            $filesButtonAnother = new \XoopsFormButton('', '', _CO_PUBLISHER_FILE_UPLOAD_ANOTHER, 'submit');
            $filesButtonAnother->setExtra('onclick="this.form.elements.op.value=\'uploadanother\'"');
            $filesButtonTray->addElement($filesButtonAnother);
        } else {
            $filesButtonCreate = new \XoopsFormButton('', '', _MD_PUBLISHER_MODIFY, 'submit');
            $filesButtonCreate->setExtra('onclick="this.form.elements.op.value=\'modify\'"');
            $filesButtonTray->addElement($filesButtonCreate);
        }

        $filesButtonClear = new \XoopsFormButton('', '', _MD_PUBLISHER_CLEAR, 'reset');
        $filesButtonTray->addElement($filesButtonClear);

        $buttonCancel = new \XoopsFormButton('', '', _MD_PUBLISHER_CANCEL, 'button');
        $buttonCancel->setExtra('onclick="history.go(-1)"');
        $filesButtonTray->addElement($buttonCancel);

        $this->addElement($filesButtonTray);
    }
}
