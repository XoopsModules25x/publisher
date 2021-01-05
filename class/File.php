<?php

declare(strict_types=1);

namespace XoopsModules\Publisher;

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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher\{Form
};

require_once \dirname(__DIR__) . '/include/common.php';

// File status
//define("_PUBLISHER_STATUS_FILE_NOTSET", -1);
//define("_PUBLISHER_STATUS_FILE_ACTIVE", 1);
//define("_PUBLISHER_STATUS_FILE_INACTIVE", 2);

/**
 * Class File
 */
class File extends \XoopsObject
{
    /**
     * @var Helper
     */
    public $helper;
    /** @var \XoopsMySQLDatabase */
    public $db;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        /** @var Helper $this->helper */
        $this->helper = Helper::getInstance();
        /** @var \XoopsMySQLDatabase $db */
        $this->db = \XoopsDatabaseFactory::getDatabaseConnection();

        $this->initVar('fileid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('itemid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('name', \XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('description', \XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('filename', \XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('mimetype', \XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('uid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('datesub', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('status', \XOBJ_DTYPE_INT, 1, false);
        $this->initVar('notifypub', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('counter', \XOBJ_DTYPE_INT, null, false);
        if (null !== $id) {
            $file = $this->helper->getHandler('File')->get($id);
            foreach ($file->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $arg = $args[0] ?? null;

        return $this->getVar($method, $arg);
    }

    /**
     * @param string $postField
     * @param array  $allowedMimetypes
     * @param array  $errors
     *
     * @return bool
     */
    public function checkUpload($postField, $allowedMimetypes, &$errors)
    {
        /** @var MimetypeHandler $mimetypeHandler */
        $mimetypeHandler = $this->helper->getHandler('Mimetype');
        $errors          = [];
        if (!$mimetypeHandler->checkMimeTypes($postField)) {
            $errors[] = \_CO_PUBLISHER_MESSAGE_WRONG_MIMETYPE;

            return false;
        }
        if (0 === \count($allowedMimetypes)) {
            $allowedMimetypes = $mimetypeHandler->getArrayByType();
        }
        $maxfilesize   = $this->helper->getConfig('maximum_filesize');
        $maxfilewidth  = $this->helper->getConfig('maximum_image_width');
        $maxfileheight = $this->helper->getConfig('maximum_image_height');
        \xoops_load('XoopsMediaUploader');
        $uploader = new \XoopsMediaUploader(Utility::getUploadDir(), $allowedMimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($postField)) {
            return true;
        }
        $errors = \array_merge($errors, $uploader->getErrors(false));

        return false;
    }

    /**
     * @param string $postField
     * @param array  $allowedMimetypes
     * @param array  $errors
     *
     * @return bool
     */
    public function storeUpload($postField, $allowedMimetypes, &$errors)
    {
        /** @var MimetypeHandler $mimetypeHandler */
        $mimetypeHandler = $this->helper->getHandler('Mimetype');
        $itemId          = $this->getVar('itemid');
        if (0 === \count($allowedMimetypes)) {
            $allowedMimetypes = $mimetypeHandler->getArrayByType();
        }
        $maxfilesize   = $this->helper->getConfig('maximum_filesize');
        $maxfilewidth  = $this->helper->getConfig('maximum_image_width');
        $maxfileheight = $this->helper->getConfig('maximum_image_height');
        if (!\is_dir(Utility::getUploadDir())) {
            if (!\mkdir($concurrentDirectory = Utility::getUploadDir(), 0757) && !\is_dir($concurrentDirectory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        \xoops_load('XoopsMediaUploader');
        $uploader = new \XoopsMediaUploader(Utility::getUploadDir() . '/', $allowedMimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($postField)) {
            $uploader->setTargetFileName($itemId . '_' . $uploader->getMediaName());
            if ($uploader->upload()) {
                $this->setVar('filename', $uploader->getSavedFileName());
                if ('' == $this->getVar('name')) {
                    $this->setVar('name', $this->getNameFromFilename());
                }
                $this->setVar('mimetype', $uploader->getMediaType());

                return true;
            }
            $errors = \array_merge($errors, $uploader->getErrors(false));

            return false;
        }
        $errors = \array_merge($errors, $uploader->getErrors(false));

        return false;
    }

    /**
     * @param null|array $allowedMimetypes
     * @param bool       $force
     * @param bool       $doupload
     *
     * @return bool
     */
    public function store($allowedMimetypes = null, $force = true, $doupload = true)
    {
        if ($this->isNew()) {
            $errors = [];
            $ret    = true;
            if ($doupload) {
                $ret = $this->storeUpload('item_upload_file', $allowedMimetypes, $errors);
            }
            if (!$ret) {
                foreach ($errors as $error) {
                    $this->setErrors($error);
                }

                return false;
            }
        }

        return $this->helper->getHandler('File')->insert($this, $force);
    }

    /**
     * @param string $dateFormat
     * @param string $format
     *
     * @return string
     */
    public function getDatesub($dateFormat = 's', $format = 'S')
    {
        //mb        xoops_load('XoopsLocal');
        //mb        return XoopsLocal::formatTimestamp($this->getVar('datesub', $format), $dateFormat);
        return \formatTimestamp($this->getVar('datesub', $format), $dateFormat);
    }

    /**
     * @return bool
     */
    public function notLoaded()
    {
        return (0 === $this->getVar('itemid'));
    }

    /**
     * @return string
     */
    public function getFileUrl()
    {
        return Utility::getUploadDir(false) . $this->filename();
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return Utility::getUploadDir() . $this->filename();
    }

    /**
     * @return string
     */
    public function getFileLink()
    {
        return "<a href='" . PUBLISHER_URL . '/visit.php?fileid=' . $this->fileid() . "'>" . $this->name() . '</a>';
    }

    /**
     * @return string
     */
    public function getItemLink()
    {
        return "<a href='" . PUBLISHER_URL . '/item.php?itemid=' . $this->itemid() . "'>" . $this->name() . '</a>';
    }

    /**
     * Update Counter
     */
    public function updateCounter()
    {
        $this->setVar('counter', $this->counter() + 1);
        $this->store();
    }

    /**
     * @return string
     */
    public function displayFlash()
    {
        //        if (!defined('MYTEXTSANITIZER_EXTENDED_MEDIA')) {
        //            require_once PUBLISHER_ROOT_PATH . '/include/media.textsanitizer.php';
        //        }
        $mediaTs = MyTextSanitizerExtension::getInstance();

        return $mediaTs->displayFlash($this->getFileUrl());
    }

    /**
     * @return string
     */
    public function getNameFromFilename()
    {
        $ret    = $this->filename();
        $sepPos = \mb_strpos($ret, '_');
        $ret    = \mb_substr($ret, $sepPos + 1);

        return $ret;
    }

    /**
     * @return Form\FileForm
     */
    public function getForm()
    {
        $form = new Form\FileForm($this);

        return $form;
    }
}
