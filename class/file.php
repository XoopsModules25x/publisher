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
 * @version         $Id: file.php 10374 2012-12-12 23:39:48Z trabis $
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once dirname(__DIR__) . '/include/common.php';

// File status
//define("_PUBLISHER_STATUS_FILE_NOTSET", -1);
//define("_PUBLISHER_STATUS_FILE_ACTIVE", 1);
//define("_PUBLISHER_STATUS_FILE_INACTIVE", 2);

/**
 * Class PublisherFile
 */
class PublisherFile extends XoopsObject
{
    /**
     * @var PublisherPublisher
     * @access public
     */
    public $publisher;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->publisher = PublisherPublisher::getInstance();
        $this->db        = XoopsDatabaseFactory::getDatabaseConnection();
        $this->initVar('fileid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('itemid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('description', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('filename', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('mimetype', XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('datesub', XOBJ_DTYPE_INT, null, false);
        $this->initVar('status', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('notifypub', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('counter', XOBJ_DTYPE_INT, null, false);
        if (isset($id)) {
            $file = $this->publisher->getHandler('file')->get($id);
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
        $arg = isset($args[0]) ? $args[0] : null;

        return $this->getVar($method, $arg);
    }

    /**
     * @param string $postField
     * @param array  $allowedMimetypes
     * @param array  $errors
     *
     * @return bool
     */
    public function checkUpload($postField, $allowedMimetypes = array(), &$errors)
    {
        $errors = array();
        if (!$this->publisher->getHandler('mimetype')->checkMimeTypes($postField)) {
            $errors[] = _CO_PUBLISHER_MESSAGE_WRONG_MIMETYPE;

            return false;
        }
        if (0 === count($allowedMimetypes)) {
            $allowedMimetypes = $this->publisher->getHandler('mimetype')->getArrayByType();
        }
        $maxfilesize   = $this->publisher->getConfig('maximum_filesize');
        $maxfilewidth  = $this->publisher->getConfig('maximum_image_width');
        $maxfileheight = $this->publisher->getConfig('maximum_image_height');
        xoops_load('XoopsMediaUploader');
        $uploader = new XoopsMediaUploader(publisherGetUploadDir(), $allowedMimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($postField)) {
            return true;
        } else {
            $errors = array_merge($errors, $uploader->getErrors(false));

            return false;
        }
    }

    /**
     * @param string $postField
     * @param array  $allowedMimetypes
     * @param array  $errors
     *
     * @return bool
     */
    public function storeUpload($postField, $allowedMimetypes = array(), &$errors)
    {
        $itemid = $this->getVar('itemid');
        if (0 === count($allowedMimetypes)) {
            $allowedMimetypes = $this->publisher->getHandler('mimetype')->getArrayByType();
        }
        $maxfilesize   = $this->publisher->getConfig('maximum_filesize');
        $maxfilewidth  = $this->publisher->getConfig('maximum_image_width');
        $maxfileheight = $this->publisher->getConfig('maximum_image_height');
        if (!is_dir(publisherGetUploadDir())) {
            mkdir(publisherGetUploadDir(), 0757);
        }
        xoops_load('XoopsMediaUploader');
        $uploader = new XoopsMediaUploader(publisherGetUploadDir() . '/', $allowedMimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($postField)) {
            $uploader->setTargetFileName($itemid . '_' . $uploader->getMediaName());
            if ($uploader->upload()) {
                $this->setVar('filename', $uploader->getSavedFileName());
                if ($this->getVar('name') == '') {
                    $this->setVar('name', $this->getNameFromFilename());
                }
                $this->setVar('mimetype', $uploader->getMediaType());

                return true;
            } else {
                $errors = array_merge($errors, $uploader->getErrors(false));

                return false;
            }
        } else {
            $errors = array_merge($errors, $uploader->getErrors(false));

            return false;
        }
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
            $errors = array();
            $ret = true;
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

        return $this->publisher->getHandler('file')->insert($this, $force);
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
        return formatTimestamp($this->getVar('datesub', $format), $dateFormat);
    }

    /**
     * @return bool
     */
    public function notLoaded()
    {
        return ($this->getVar('itemid') == 0);
    }

    /**
     * @return string
     */
    public function getFileUrl()
    {
        return publisherGetUploadDir(false) . $this->filename();
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return publisherGetUploadDir() . $this->filename();
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
        if (!defined('MYTEXTSANITIZER_EXTENDED_MEDIA')) {
            include_once PUBLISHER_ROOT_PATH . '/include/media.textsanitizer.php';
        }
        $mediaTs = MyTextSanitizerExtension::getInstance();

        return $mediaTs->displayFlash($this->getFileUrl());
    }

    /**
     * @return string
     */
    public function getNameFromFilename()
    {
        $ret    = $this->filename();
        $sepPos = strpos($ret, '_');
        $ret    = substr($ret, $sepPos + 1, strlen($ret) - $sepPos);

        return $ret;
    }

    /**
     * @return PublisherFileForm
     */
    public function getForm()
    {
        include_once $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME . '/class/form/file.php');
        $form = new PublisherFileForm($this);

        return $form;
    }
}

/**
 * Files handler class.
 * This class is responsible for providing data access mechanisms to the data source
 * of File class objects.
 *
 * @author  marcan <marcan@notrevie.ca>
 * @package Publisher
 */
class PublisherFileHandler extends XoopsPersistableObjectHandler
{
    public $table_link   = '';
    public $field_object = '';
    public $field_link   = '';

    /**
     * @param null|XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'publisher_files', 'PublisherFile', 'fileid', 'name');
    }

    /**
     * delete a file from the database
     *
     * @param object $file  reference to the file to delete
     * @param bool   $force
     *
     * @return bool FALSE if failed.
     */
    public function delete(&$file, $force = false)
    {
        $ret = false;
        // Delete the actual file
        if (is_file($file->getFilePath()) && unlink($file->getFilePath())) {
            $ret = parent::delete($file, $force);
        }

        return $ret;
    }

    /**
     * delete files related to an item from the database
     *
     * @param object $itemObj reference to the item which files to delete
     *
     * @return bool
     */
    public function deleteItemFiles(&$itemObj)
    {
        if (strtolower(get_class($itemObj)) !== 'publisheritem') {
            return false;
        }
        $files  =& $this->getAllFiles($itemObj->itemid());
        $result = true;
        foreach ($files as $file) {
            if (!$this->delete($file)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * retrieve all files
     *
     * @param int    $itemid
     * @param int    $status
     * @param int    $limit
     * @param int    $start
     * @param string $sort
     * @param string $order
     * @param array  $category
     *
     * @return array array of {@link PublisherFile} objects
     */
    public function &getAllFiles($itemid = 0, $status = -1, $limit = 0, $start = 0, $sort = 'datesub', $order = 'DESC', $category = array())
    {
        global $xoopsDB;
        $files = array();

        $this->table_link = $this->db->prefix('publisher_items');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $this->db->prefix('publisher_files'));
        list($count) = $GLOBALS['xoopsDB']->fetchRow($result);
        if ($count > 0) {
            $this->field_object = 'itemid';
            $this->field_link   = 'itemid';
            $hasStatusCriteria  = false;
            $criteriaStatus     = new CriteriaCompo();
            if (is_array($status)) {
                $hasStatusCriteria = true;
                foreach ($status as $v) {
                    $criteriaStatus->add(new Criteria('o.status', $v), 'OR');
                }
            } elseif ($status != -1) {
                $hasStatusCriteria = true;
                $criteriaStatus->add(new Criteria('o.status', $status), 'OR');
            }
            $hasCategoryCriteria = false;
            $criteriaCategory    = new CriteriaCompo();
            $category            = (array)$category;
            if ($category[0] != 0 && count($category) > 0) {
                $hasCategoryCriteria = true;
                foreach ($category as $cat) {
                    $criteriaCategory->add(new Criteria('l.categoryid', $cat), 'OR');
                }
            }
            $criteriaItemid = new Criteria('o.itemid', $itemid);
            $criteria       = new CriteriaCompo();
            if ($itemid != 0) {
                $criteria->add($criteriaItemid);
            }
            if ($hasStatusCriteria) {
                $criteria->add($criteriaStatus);
            }
            if ($hasCategoryCriteria) {
                $criteria->add($criteriaCategory);
            }
            $criteria->setSort($sort);
            $criteria->setOrder($order);
            $criteria->setLimit($limit);
            $criteria->setStart($start);
            $files =& $this->getByLink($criteria, array('o.*'), true);

            //            return $files;
        }

        return $files;
    }
}
