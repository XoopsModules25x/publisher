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
 * @version         $Id: file.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit("XOOPS root path not defined");

include_once dirname(dirname(__DIR__)) . '/include/common.php';

xoops_load('XoopsFormLoader');
//todo: move to admin?
xoops_loadLanguage('main', 'publisher');

/**
 * Class PublisherFileForm
 */
class PublisherFileForm extends XoopsThemeForm
{
    /**
     * @var PublisherPublisher
     * @access public
     */
    public $publisher = null;

    public $targetObject = null;

    /**
     * @param $target
     */
    public function __construct(&$target)
    {
        $this->publisher    = PublisherPublisher::getInstance();
        $this->targetObject = $target;

        parent::__construct(_AM_PUBLISHER_UPLOAD_FILE, "form", xoops_getenv('PHP_SELF'));
        $this->setExtra('enctype="multipart/form-data"');

        $this->createElements();
        $this->createButtons();
    }

    public function createElements()
    {
        // NAME
        $name_text = new XoopsFormText(_CO_PUBLISHER_FILENAME, 'name', 50, 255, $this->targetObject->name());
        $name_text->setDescription(_CO_PUBLISHER_FILE_NAME_DSC);
        $this->addElement($name_text, true);

        // DESCRIPTION
        $description_text = new XoopsFormTextArea(_CO_PUBLISHER_FILE_DESCRIPTION, 'description', $this->targetObject->description());
        $description_text->setDescription(_CO_PUBLISHER_FILE_DESCRIPTION_DSC);
        $this->addElement($description_text);

        // FILE TO UPLOAD
        //if (!$this->targetObject->fileid()) {
        $file_box = new XoopsFormFile(_CO_PUBLISHER_FILE_TO_UPLOAD, "item_upload_file", 0);
        $file_box->setExtra("size ='50'");
        $this->addElement($file_box);
        //}

        $status_select = new XoopsFormRadioYN(_CO_PUBLISHER_FILE_STATUS, 'file_status', PublisherConstants::_PUBLISHER_STATUS_FILE_ACTIVE);
        $status_select->setDescription(_CO_PUBLISHER_FILE_STATUS_DSC);
        $this->addElement($status_select);

        // fileid
        $this->addElement(new XoopsFormHidden('fileid', $this->targetObject->fileid()));

        // itemid
        $this->addElement(new XoopsFormHidden('itemid', $this->targetObject->itemid()));
    }

    public function createButtons()
    {
        $files_button_tray = new XoopsFormElementTray('', '');
        $files_hidden      = new XoopsFormHidden('op', 'uploadfile');
        $files_button_tray->addElement($files_hidden);

        if (!$this->targetObject->fileid()) {
            $files_butt_create = new XoopsFormButton('', '', _MD_PUBLISHER_UPLOAD, 'submit');
            $files_butt_create->setExtra('onclick="this.form.elements.op.value=\'uploadfile\'"');
            $files_button_tray->addElement($files_butt_create);

            $files_butt_another = new XoopsFormButton('', '', _CO_PUBLISHER_FILE_UPLOAD_ANOTHER, 'submit');
            $files_butt_another->setExtra('onclick="this.form.elements.op.value=\'uploadanother\'"');
            $files_button_tray->addElement($files_butt_another);
        } else {
            $files_butt_create = new XoopsFormButton('', '', _MD_PUBLISHER_MODIFY, 'submit');
            $files_butt_create->setExtra('onclick="this.form.elements.op.value=\'modify\'"');
            $files_button_tray->addElement($files_butt_create);
        }

        $files_butt_clear = new XoopsFormButton('', '', _MD_PUBLISHER_CLEAR, 'reset');
        $files_button_tray->addElement($files_butt_clear);

        $butt_cancel = new XoopsFormButton('', '', _MD_PUBLISHER_CANCEL, 'button');
        $butt_cancel->setExtra('onclick="history.go(-1)"');
        $files_button_tray->addElement($butt_cancel);

        $this->addElement($files_button_tray);
    }
}
