<?php namespace XoopsModules\Publisher;

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
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * BaseObjectHandler class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Nazar Aziz <nazar@panthersoftware.com>
 */
class BaseObjectHandler extends \XoopsPersistableObjectHandler
{
    /**
     * Database connection
     *
     * @var XoopsDatabase
     */
    //mb    public $_db; //mb it is already declared in XoopsObjectHandler

    /**
     * Autoincrementing DB fieldname
     *
     * @var string
     */
    protected $idfield = 'id';

    /**
     * @param XoopsDatabase $db
     */
    public function init(\XoopsDatabase $db)
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
     * @param  bool $isNew
     * @return XoopsObject
     * @access public
     */
    public function create($isNew = true)
    {
        return new $this->className();
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     *
     * @param int   $id ID
     *
     * @param  null $fields
     * @return mixed object if id exists, false if not
     * @access public
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
     * @param CriteriaElement $criteria {@link CriteriaElement}
     *                                  conditions to be met
     * @param bool            $idAsKey  Should the department ID be used as array key
     *
     * @param  bool           $asObject
     * @return array array of objects
     * @access  public
     */
    public function &getObjects(\CriteriaElement $criteria = null, $idAsKey = false, $asObject = true) //&getObjects($criteria = null, $idAsKey = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        $id    = $this->idfield;
        if (isset($criteria)) {
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
            if (!$idAsKey) {
                $ret[] = $obj;
            } else {
                $ret[$obj->getVar($id)] = $obj;
            }
            unset($obj);
        }

        return $ret;
    }

    /**
     * @param XoopsObject $obj
     * @param bool        $force
     *
     * @return bool|void
     */
    public function insert(\XoopsObject $obj, $force = false)// insert($obj, $force = false)
    {
        // Make sure object is of correct type
        if (0 != strcasecmp($this->className, get_class($obj))) {
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
        if (false !== $force) {
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
     * @param CriteriaElement $criteria {@link CriteriaElement} to match
     *
     * @return string SQL query
     * @access private
     */
    private function selectQuery($criteria = null)
    {
        $sql = sprintf('SELECT * FROM %s', $this->db->prefix($this->dbtable));
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
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
     * @param CriteriaElement $criteria {@link CriteriaElement} to match
     *
     * @return int count of objects
     * @access public
     */
    public function getCount(\CriteriaElement $criteria = null) //getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($this->dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * delete object based on id
     *
     * @param XoopsObject $obj   {@link XoopsObject} to delete
     * @param bool        $force override XOOPS delete protection
     *
     * @return bool deletion successful?
     * @access public
     */
    public function delete(\XoopsObject $obj, $force = false) //delete($obj, $force = false)
    {
        if (0 != strcasecmp($this->className, get_class($obj))) {
            return false;
        }
        $sql = $this->deleteQuery($obj);
        if (false !== $force) {
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
     * @param CriteriaElement $criteria {@link CriteriaElement}
     *
     * @param  bool           $force
     * @param  bool           $asObject
     * @return bool FALSE if deletion failed
     * @access    public
     */
    public function deleteAll(\CriteriaElement $criteria = null, $force = true, $asObject = false) //deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix($this->dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
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
     * @param string          $fieldname
     * @param string          $fieldvalue
     * @param \CriteriaElement $criteria {@link \CriteriaElement}
     *
     * @param  bool           $force
     * @return bool FALSE if update failed
     * @access    public
     */
    public function updateAll($fieldname, $fieldvalue, \CriteriaElement $criteria = null, $force = false) //updateAll($fieldname, $fieldvalue, $criteria = null)
    {
        $setClause = is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldvalue : $fieldname . ' = ' . $this->db->quoteString($fieldvalue);
        $sql       = 'UPDATE ' . $this->db->prefix($this->dbtable) . ' SET ' . $setClause;
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * @param $obj
     *
     * @return bool
     */
    protected function insertQuery($obj)
    {
        return false;
    }

    /**
     * @param $obj
     *
     * @return bool|string
     */
    protected function updateQuery($obj)
    {
        return false;
    }

    /**
     * @param $obj
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
     * @param XoopsDatabase $db
     *
     * @return XoopsObject {@link pagesCategoryHandler}
     * @access public
     */
    public function getInstance(\XoopsDatabase $db)
    {
        static $instance;
        if (null === $instance) {
            $className = $this->className . 'Handler';
            $instance  = new $className($db);
        }

        return $instance;
    }
}
