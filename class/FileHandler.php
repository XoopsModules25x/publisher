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

require_once \dirname(__DIR__) . '/include/common.php';

// File status
//define("_PUBLISHER_STATUS_FILE_NOTSET", -1);
//define("_PUBLISHER_STATUS_FILE_ACTIVE", 1);
//define("_PUBLISHER_STATUS_FILE_INACTIVE", 2);

/**
 * Files handler class.
 * This class is responsible for providing data access mechanisms to the data source
 * of File class objects.
 *
 * @author  marcan <marcan@notrevie.ca>
 */
class FileHandler extends \XoopsPersistableObjectHandler
{
    private const TABLE = 'publisher_files';
    private const ENTITY = File::class;
    private const ENTITYNAME = 'File';
    private const KEYNAME = 'fileid';
    private const IDENTIFIER = 'name';

    public $table_link = '';
    /**
     * @var Helper
     */
    public $helper;

    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        /** @var Helper $this->helper */
        $this->helper = $helper ?? Helper::getInstance();
        $this->db = $db;
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * delete a file from the database
     *
     * @param \XoopsObject|File $file reference to the file to delete
     * @param bool         $force
     *
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $file, $force = false) //delete(&$file, $force = false)
    {
        $ret = false;
        // Delete the actual file
        if (\is_file($file->getFilePath()) && \unlink($file->getFilePath())) {
            $ret = parent::delete($file, $force);
        }

        return $ret;
    }

    /**
     * delete files related to an item from the database
     *
     * @param \XoopsObject $itemObj reference to the item which files to delete
     *
     * @return bool
     */
    public function deleteItemFiles(\XoopsObject $itemObj)
    {
        if ('publisheritem' !== \mb_strtolower(\get_class($itemObj))) {
            return false;
        }
        $files  = $this->getAllFiles($itemObj->itemid());
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
     * @param int       $itemId
     * @param int|array $status
     * @param int       $limit
     * @param int       $start
     * @param string    $sort
     * @param string    $order
     * @param array     $category
     *
     * @return array array of {@link File} objects
     */
    public function getAllFiles($itemId = 0, $status = -1, $limit = 0, $start = 0, $sort = 'datesub', $order = 'DESC', $category = [])
    {
        $files = [];

        $this->table_link = $this->db->prefix($this->helper->getDirname() . '_items');

        $result = $GLOBALS['xoopsDB']->query('SELECT COUNT(*) FROM ' . $this->db->prefix($this->helper->getDirname() . '_files'));
        [$count] = $GLOBALS['xoopsDB']->fetchRow($result);
        if ($count > 0) {
            $this->field_object = 'itemid';
            $this->field_link   = 'itemid';
            $hasStatusCriteria  = false;
            $criteriaStatus     = new \CriteriaCompo();
            if (\is_array($status)) {
                $hasStatusCriteria = true;
                foreach ($status as $v) {
                    $criteriaStatus->add(new \Criteria('o.status', $v), 'OR');
                }
            } elseif (-1 != $status) {
                $hasStatusCriteria = true;
                $criteriaStatus->add(new \Criteria('o.status', $status), 'OR');
            }
            $hasCategoryCriteria = false;
            $criteriaCategory    = new \CriteriaCompo();
            $category            = (array)$category;
            if (isset($category[0]) && 0 != $category[0] && \count($category) > 0) {
                $hasCategoryCriteria = true;
                foreach ($category as $cat) {
                    $criteriaCategory->add(new \Criteria('l.categoryid', $cat), 'OR');
                }
            }
            $criteriaItemid = new \Criteria('o.itemid', $itemId);
            $criteria       = new \CriteriaCompo();
            if (0 != $itemId) {
                $criteria->add($criteriaItemid);
            }
            if ($hasStatusCriteria) {
                $criteria->add($criteriaStatus);
            }
            if ($hasCategoryCriteria) {
                $criteria->add($criteriaCategory);
            }
            $criteria->setSort($sort);
            $criteria->order = $order; // patch for XOOPS <= 2.5.10, does not set order correctly using setOrder() method
            $criteria->setLimit($limit);
            $criteria->setStart($start);
            $files = $this->getByLink($criteria, ['o.*'], true);
            //            return $files;
        }

        return $files;
    }
}
