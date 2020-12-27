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
 *  Publisher class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * Class MimetypeHandler
 */
class MimetypeHandler extends BaseObjectHandler
{
    /**
     * Constructor
     * @param \XoopsDatabase|null $db
     * @param Helper|null         $helper
     */
    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        /** @var Helper $this->helper */
        $this->helper = $helper ?? Helper::getInstance();

        $this->publisherIsAdmin = $this->helper->isUserAdmin();
        $this->db               = $db;
        $this->className        = Mimetype::class;
    }

    /**
     * retrieve a mimetype object from the database
     *
     * @param int|null   $id ID of mimetype
     *
     * @param array|null $fields
     * @return bool|Mimetype
     */
    public function get($id = null, $fields = null)
    {
        $id = (int)$id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria('mime_id', $id));
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $numrows = $this->db->getRowsNum($result);
            if (1 == $numrows) {
                $obj = new $this->className($this->db->fetchArray($result));

                return $obj;
            }
        }

        return false;
    }

    /**
     * retrieve objects from the database
     *
     * @param \Criteria|\CriteriaCompo|null $criteria conditions to be met
     *
     * @param bool                          $id_as_key
     * @param bool                          $as_object
     * @return array array of <a href='psi_element://Mimetype'>Mimetype</a> objects
     *                                                objects
     */
    public function &getObjects($criteria = null, $id_as_key = false, $as_object = true) //&getObjects($criteria = null)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        //echo "<br>$sql<br>";
        $result = $this->db->query($sql, $limit, $start);
        // if no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        // Add each returned record to the result array
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $obj   = new $this->className($myrow);
            $ret[] = $obj;
            unset($obj);
        }

        return $ret;
    }

    /**
     * Format mime_types into array
     *
     * @param mixed|null $mimeExt
     *
     * @return array array of mime_types
     */
    public function getArray($mimeExt = null)
    {
        //        global $publisherIsAdmin;
        $ret = [];
        if ($GLOBALS['xoopsUser'] && !$this->publisherIsAdmin) {
            // For user uploading
            $crit = new \CriteriaCompo(new \Criteria('mime_user', 1)); //$sql = sprintf("SELECT * FROM `%s` WHERE mime_user=1", $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_mimetypes'));
        } elseif ($GLOBALS['xoopsUser'] && $this->publisherIsAdmin) {
            // For admin uploading
            $crit = new \CriteriaCompo(new \Criteria('mime_admin', 1)); //$sql = sprintf("SELECT * FROM `%s` WHERE mime_admin=1", $GLOBALS['xoopsDB']->prefix($module->getVar('dirname', 'n') . '_mimetypes'));
        } else {
            return $ret;
        }
        if ($mimeExt) {
            $crit->add(new \Criteria('mime_ext', $mimeExt));
        }
        $result = $this->getObjects($crit);
        // if no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        foreach ($result as $mime) {
            $line = \explode(' ', $mime->getVar('mime_types'));
            foreach ($line as $row) {
                $ret[] = ['type' => $row, 'ext' => $mime->getVar('mime_ext')];
            }
        }

        return $ret;
    }

    /**
     * Checks to see if the user uploading the file has permissions to upload this mimetype
     *
     * @param string $postField file being uploaded
     *
     * @return bool false if no permission, return mimetype if has permission
     */
    public function checkMimeTypes($postField)
    {
        $ret              = false;
        $allowedMimetypes = $this->getArrayByType();
        if (empty($allowedMimetypes)) {
            return $ret;
        }
        foreach ($allowedMimetypes as $mime) {
            if ($mime == $_FILES[$postField]['type']) {
                $ret = $mime;
                break;
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getArrayByType()
    {
        static $array = [];
        if (empty($array)) {
            $items = $this->getArray();
            foreach ($items as $item) {
                $array[] = $item['type'];
            }
        }

        return $array;
    }

    /**
     * Create a "select" SQL query
     *
     * @param \Criteria|\CriteriaCompo|null $criteria to match
     * @param bool                          $join
     *
     * @return string string SQL query
     */
    private function selectQuery($criteria = null, $join = false)
    {
        //        if (!$join) {
        //            $sql = sprintf('SELECT * FROM `%s`', $this->db->prefix($this->dbtable));
        //        } else {
        //            echo "no need for join...";
        //            exit;
        //        }

        try {
            if ($join) {
                throw new \RuntimeException('no need for join...');
            }
        } catch (\Throwable $e) {
            $helper = Helper::getInstance();
            $helper->addLog($e);
            echo 'no need for join...';
        }

        $sql = \sprintf('SELECT * FROM `%s`', $this->db->prefix($this->dbtable));

        if (null !== $criteria && $criteria instanceof \Criteria) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * @param Mimetype $obj
     *
     * @return bool|string
     */
    protected function insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        $sql = \sprintf(
            'INSERT INTO `%s` (mime_id, mime_ext, mime_types, mime_name, mime_admin, mime_user) VALUES
            (%u, %s, %s, %s, %u, %u)',
            $this->db->prefix($this->dbtable),
            $obj->getVar('mime_id'),
            $this->db->quoteString($obj->getVar('mime_ext')),
            $this->db->quoteString($obj->getVar('mime_types')),
            $this->db->quoteString($obj->getVar('mime_name')),
            $obj->getVar('mime_admin'),
            $obj->getVar('mime_user')
        );

        return $sql;
    }

    /**
     * @param Mimetype $obj
     *
     * @return bool|string
     */
    protected function updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        $sql = \sprintf(
            'UPDATE `%s` SET mime_ext = %s, mime_types = %s, mime_name = %s, mime_admin = %u, mime_user = %u WHERE
            mime_id = %u',
            $this->db->prefix($this->dbtable),
            $this->db->quoteString($obj->getVar('mime_ext')),
            $this->db->quoteString($obj->getVar('mime_types')),
            $this->db->quoteString($obj->getVar('mime_name')),
            $obj->getVar('mime_admin'),
            $obj->getVar('mime_user'),
            $obj->getVar('mime_id')
        );

        return $sql;
    }

    /**
     * @param Mimetype $obj
     *
     * @return bool|string
     */
    protected function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE mime_id = %u', $this->db->prefix($this->dbtable), $obj->getVar('mime_id'));

        return $sql;
    }
}
