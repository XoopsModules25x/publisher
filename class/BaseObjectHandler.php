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
 * BaseObjectHandler class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Nazar Aziz <nazar@panthersoftware.com>
 */
class BaseObjectHandler extends \XoopsPersistableObjectHandler
{
    /**
     * Database connection
     *
     * @var \XoopsDatabase
     */
    //mb    public $_db; //mb it is already declared in XoopsObjectHandler

    /**
     * Autoincrementing DB fieldname
     *
     * @var string
     */
    protected $idfield          = 'id';
    public    $helper           = null;
    public    $publisherIsAdmin = null;

    /**
     * @param \XoopsDatabase|null $db
     */
    public function init(\XoopsDatabase $db = null)
    {
        $this->db = $db;
    }

    /**
     * DB Table Name
     *
     * @var string
     */
    protected $dbtable = 'publisher_mimetypes';

    /**
     * create a new  object
     *
     * @param bool $isNew
     * @return \XoopsObject
     */
    public function create($isNew = true)
    {
        return new $this->className();
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     *
     * @param int|null   $id ID
     *
     * @param array|null $fields
     * @return mixed object if id exists, false if not
     */
    public function get($id = null, $fields = null)
    {
        $id = (int)$id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria($this->idfield, $id));
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
     * @param \Criteria|\CriteriaElement|null $criteria conditions to be met
     * @param bool                            $idAsKey  Should the department ID be used as array key
     *
     * @param bool                            $asObject
     * @return array array of objects
     */
    public function &getObjects($criteria = null, $idAsKey = false, $asObject = true) //&getObjects($criteria = null, $idAsKey = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        $id    = $this->idfield;
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        // if no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        // Add each returned record to the result array
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $obj = new $this->className($myrow);
            if ($idAsKey) {
                $ret[$obj->getVar($id)] = $obj;
            } else {
                $ret[] = $obj;
            }
            unset($obj);
        }

        return $ret;
    }

    /**
     * @param \XoopsObject $obj
     * @param bool         $force
     *
     * @return bool
     */
    public function insert(\XoopsObject $obj, $force = false)// insert($obj, $force = false)
    {
        // Make sure object is of correct type
        if (0 != \strcasecmp($this->className, \get_class($obj))) {
            return false;
        }
        // Make sure object needs to be stored in DB
        if (!$obj->isDirty()) {
            return true;
        }
        // Make sure object fields are filled with valid values
        if (!$obj->cleanVars()) {
            return false;
        }
        // Create query for DB update
        if ($obj->isNew()) {
            // Determine next auto-gen ID for table
            $this->db->genId($this->db->prefix($this->dbtable) . '_uid_seq');
            $sql = $this->insertQuery($obj);
        } else {
            $sql = $this->updateQuery($obj);
        }
        // Update DB
        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            $obj->setErrors('The query returned an error. ' . $this->db->error());

            return false;
        }
        //Make sure auto-gen ID is stored correctly in object
        if ($obj->isNew()) {
            $obj->assignVar($this->idfield, $this->db->getInsertId());
        }

        return true;
    }

    /**
     * Create a "select" SQL query
     *
     * @param \Criteria|null $criteria {@link \Criteria} to match
     *
     * @return string SQL query
     */
    private function selectQuery(\Criteria $criteria = null)
    {
        $sql = \sprintf('SELECT * FROM `%s`', $this->db->prefix($this->dbtable));
        if (null !== $criteria && $criteria instanceof \Criteria) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . '
                    ' . $criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * count objects matching a criteria
     *
     * @param \CriteriaElement|null $criteria {@link CriteriaElement}                                                  to match
     *
     * @return int count of objects
     */
    public function getCount(\CriteriaElement $criteria = null) //getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($this->dbtable);
        if (null !== $criteria && ($criteria instanceof \Criteria || $criteria instanceof \CriteriaCompo)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return 0;
        }
        [$count] = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * delete object based on id
     *
     * @param \XoopsObject $obj   {@link XoopsObject}
     *                            to delete
     * @param bool         $force override XOOPS delete protection
     *
     * @return bool deletion successful?
     */
    public function delete(\XoopsObject $obj, $force = false) //delete($obj, $force = false)
    {
        if (0 != \strcasecmp($this->className, \get_class($obj))) {
            return false;
        }
        $sql = $this->deleteQuery($obj);
        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete department matching a set of conditions
     *
     * @param \CriteriaElement|null $criteria {@link CriteriaElement}
     *
     * @param bool                  $force
     * @param bool                  $asObject
     * @return bool FALSE if deletion failed
     */
    public function deleteAll(\CriteriaElement $criteria = null, $force = true, $asObject = false) //deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix($this->dbtable);
        if (null !== $criteria && ($criteria instanceof \Criteria || $criteria instanceof \CriteriaCompo)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Assign a value to 1 field for tickets matching a set of conditions
     *
     * @param string                $fieldname
     * @param string                $fieldvalue
     * @param \Criteria|\CriteriaCompo|null $criteria
     *
     * @param bool                  $force
     * @return bool FALSE if update failed
     */
    public function updateAll($fieldname, $fieldvalue, $criteria = null, $force = false) //updateAll($fieldname, $fieldvalue, $criteria = null)
    {
        $setClause = \is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldvalue : $fieldname . ' = ' . $this->db->quoteString($fieldvalue);
        $sql       = 'UPDATE ' . $this->db->prefix($this->dbtable) . ' SET ' . $setClause;
        if (null !== $criteria && ($criteria instanceof \Criteria || $criteria instanceof \CriteriaCompo)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * @param \XoopsObject $obj
     *
     * @return bool
     */
    protected function insertQuery($obj)
    {
        return false;
    }

    /**
     * @param \XoopsObject $obj
     *
     * @return bool|string
     */
    protected function updateQuery($obj)
    {
        return false;
    }

    /**
     * @param \XoopsObject $obj
     *
     * @return bool
     */
    protected function deleteQuery($obj)
    {
        return false;
    }

    /**
     * Singleton - prevent multiple instances of this class
     *
     *
     * @param \XoopsDatabase|null $db
     * @return \XoopsObject {@link pagesCategoryHandler}
     */
    public function getInstance(\XoopsDatabase $db = null)
    {
        static $instance;
        if (null === $instance) {
            $className = $this->className . 'Handler';
            $instance  = new $className($db);
        }

        return $instance;
    }
}
