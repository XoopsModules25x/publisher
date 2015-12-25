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
 *  Publisher class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: mimetype.php 10374 2012-12-12 23:39:48Z trabis $
 */
// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * PublisherBaseObjectHandler class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Nazar Aziz <nazar@panthersoftware.com>
 * @version         $Id: mimetype.php 10374 2012-12-12 23:39:48Z trabis $
 */
class PublisherBaseObjectHandler extends XoopsPersistableObjectHandler
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
    public function init(XoopsDatabase $db)
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
     * @return object {@link publisherBaseObject}
     * @access public
     */
    public function &create()
    {
        return new $this->className();
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     *
     * @param int $id ID
     *
     * @return mixed object if id exists, false if not
     * @access public
     */
    public function &get($id)
    {
        $id = (int)($id);
        if ($id > 0) {
            $sql = $this->selectQuery(new Criteria($this->idfield, $id));
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $numrows = $this->db->getRowsNum($result);
            if ($numrows == 1) {
                $obj = new $this->className($this->db->fetchArray($result));

                return $obj;
            }
        }

        return false;
    }

    /**
     * retrieve objects from the database
     *
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool   $idAsKey  Should the department ID be used as array key
     *
     * @return array array of objects
     * @access  public
     */
    public function &getObjects($criteria = null, $idAsKey = false)
    {
        $ret   = array();
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
        while (($myrow = $this->db->fetchArray($result)) !== false) {
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
     * @param object $obj
     * @param bool   $force
     *
     * @return bool|void
     */
    public function insert(&$obj, $force = false)
    {
        // Make sure object is of correct type
        if (strcasecmp($this->className, get_class($obj)) != 0) {
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
     * @param object $criteria {@link CriteriaElement} to match
     *
     * @return string SQL query
     * @access private
     */
    private function selectQuery($criteria = null)
    {
        $sql = sprintf('SELECT * FROM %s', $this->db->prefix($this->dbtable));
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
            if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY ' . $criteria->getSort() . '
                    ' . $criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * count objects matching a criteria
     *
     * @param object $criteria {@link CriteriaElement} to match
     *
     * @return int count of objects
     * @access public
     */
    public function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($this->dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
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
     * @param object $obj   {@link XoopsObject} to delete
     * @param bool   $force override XOOPS delete protection
     *
     * @return bool deletion successful?
     * @access public
     */
    public function delete(&$obj, $force = false)
    {
        if (strcasecmp($this->className, get_class($obj)) != 0) {
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
     * @param object $criteria {@link CriteriaElement}
     *
     * @return bool FALSE if deletion failed
     * @access    public
     */
    public function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix($this->dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
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
     * @param string $fieldname
     * @param string $fieldvalue
     * @param object $criteria {@link CriteriaElement}
     *
     * @return bool FALSE if update failed
     * @access    public
     */
    public function updateAll($fieldname, $fieldvalue, $criteria = null)
    {
        $setClause = is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldvalue : $fieldname . ' = ' . $this->db->quoteString($fieldvalue);
        $sql       = 'UPDATE ' . $this->db->prefix($this->dbtable) . ' SET ' . $setClause;
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
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
    protected function insertQuery(&$obj)
    {
        return false;
    }

    /**
     * @param $obj
     *
     * @return bool
     */
    protected function updateQuery(&$obj)
    {
        return false;
    }

    /**
     * @param $obj
     *
     * @return bool
     */
    protected function deleteQuery(&$obj)
    {
        return false;
    }

    /**
     * Singleton - prevent multiple instances of this class
     *
     * @param object &$db {@link XoopsHandlerFactory}
     *
     * @return object {@link pagesCategoryHandler}
     * @access public
     */
    public function &getInstance(&$db)
    {
        static $instance;
        if (!isset($instance)) {
            $className = $this->className . 'Handler';
            $instance  = new $className($db);
        }

        return $instance;
    }
}

/**
 * PublisherMimetype class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package publisher
 */
class PublisherMimetype extends XoopsObject
{
    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->initVar('mime_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('mime_ext', XOBJ_DTYPE_TXTBOX, null, true, 60);
        $this->initVar('mime_types', XOBJ_DTYPE_TXTAREA, null, false, 1024);
        $this->initVar('mime_name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('mime_admin', XOBJ_DTYPE_INT, null, false);
        $this->initVar('mime_user', XOBJ_DTYPE_INT, null, false);
        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}

/**
 * Class PublisherMimetypeHandler
 */
class PublisherMimetypeHandler extends PublisherBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $className = 'publishermimetype';

    /**
     * Constructor
     *
     * @param null|XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::init($db);
    }

    /**
     * retrieve a mimetype object from the database
     *
     * @param int $id ID of mimetype
     *
     * @return object {@link PublisherMimetype}
     * @access    public
     */
    public function &get($id)
    {
        $id = (int)($id);
        if ($id > 0) {
            $sql = $this->selectQuery(new Criteria('mime_id', $id));
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $numrows = $this->db->getRowsNum($result);
            if ($numrows == 1) {
                $obj = new $this->className($this->db->fetchArray($result));

                return $obj;
            }
        }

        return false;
    }

    /**
     * retrieve objects from the database
     *
     * @param object $criteria {@link CriteriaElement} conditions to be met
     *
     * @return array array of {@link PublisherMimetype} objects
     * @access    public
     */
    public function &getObjects($criteria = null)
    {
        $ret   = array();
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        if (isset($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        //echo "<br />$sql<br />";
        $result = $this->db->query($sql, $limit, $start);
        // if no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        // Add each returned record to the result array
        while (($myrow = $this->db->fetchArray($result)) !== false) {
            $obj   = new $this->className($myrow);
            $ret[] = $obj;
            unset($obj);
        }

        return $ret;
    }

    /**
     * Format mime_types into array
     *
     * @param null $mimeExt
     *
     * @return array array of mime_types
     */
    public function getArray($mimeExt = null)
    {
        //        global $publisherIsAdmin;
        $ret = array();
        if ($GLOBALS['xoopsUser'] && !$GLOBALS['publisherIsAdmin']) {
            // For user uploading
            $crit = new CriteriaCompo(new Criteria('mime_user', 1)); //$sql = sprintf("SELECT * FROM %s WHERE mime_user=1", $GLOBALS['xoopsDB']->prefix('publisher_mimetypes'));
        } elseif ($GLOBALS['xoopsUser'] && $GLOBALS['publisherIsAdmin']) {
            // For admin uploading
            $crit = new CriteriaCompo(new Criteria('mime_admin', 1)); //$sql = sprintf("SELECT * FROM %s WHERE mime_admin=1", $GLOBALS['xoopsDB']->prefix('publisher_mimetypes'));
        } else {
            return $ret;
        }
        if ($mimeExt) {
            $crit->add(new Criteria('mime_ext', $mimeExt));
        }
        $result =& $this->getObjects($crit);
        // if no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        foreach ($result as $mime) {
            $line = explode(' ', $mime->getVar('mime_types'));
            foreach ($line as $row) {
                $ret[] = array('type' => $row, 'ext' => $mime->getVar('mime_ext'));
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
     * @access public
     */
    public function checkMimeTypes($postField)
    {
        $ret               = false;
        $allowed_mimetypes = $this->getArrayByType();
        if (empty($allowed_mimetypes)) {
            return $ret;
        }
        foreach ($allowed_mimetypes as $mime) {
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
        static $array = array();
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
     * @param null|object $criteria {@link CriteriaElement} to match
     * @param bool        $join
     *
     * @return string string SQL query
     * @access    private
     */
    private function selectQuery($criteria = null, $join = false)
    {
        //        if (!$join) {
        //            $sql = sprintf('SELECT * FROM %s', $this->db->prefix($this->dbtable));
        //        } else {
        //            echo "no need for join...";
        //            exit;
        //        }

        try {
            if ($join) {
                throw new Exception('no need for join...');
            }
        } catch (Exception $e) {
            echo 'no need for join...';
        };

        $sql = sprintf('SELECT * FROM %s', $this->db->prefix($this->dbtable));

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
            if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * @param $obj
     *
     * @return bool|string
     */
    protected function insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        $sql = sprintf('INSERT INTO %s (mime_id, mime_ext, mime_types, mime_name, mime_admin, mime_user) VALUES
            (%u, %s, %s, %s, %u, %u)', $this->db->prefix($this->dbtable), $obj->getVar('mime_id'), $this->db->quoteString($obj->getVar('mime_ext')), $this->db->quoteString($obj->getVar('mime_types')), $this->db->quoteString($obj->getVar('mime_name')), $obj->getVar('mime_admin'), $obj->getVar('mime_user'));

        return $sql;
    }

    /**
     * @param $obj
     *
     * @return bool|string
     */
    protected function updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        $sql = sprintf('UPDATE %s SET mime_ext = %s, mime_types = %s, mime_name = %s, mime_admin = %u, mime_user = %u WHERE
            mime_id = %u', $this->db->prefix($this->dbtable), $this->db->quoteString($obj->getVar('mime_ext')), $this->db->quoteString($obj->getVar('mime_types')), $this->db->quoteString($obj->getVar('mime_name')), $obj->getVar('mime_admin'), $obj->getVar('mime_user'), $obj->getVar('mime_id'));

        return $sql;
    }

    /**
     * @param $obj
     *
     * @return bool|string
     */
    protected function deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE mime_id = %u', $this->db->prefix($this->dbtable), $obj->getVar('mime_id'));

        return $sql;
    }
}
