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

/** @var Helper $this->helper */
/** @var Item $obj */

require_once \dirname(__DIR__) . '/include/common.php';

/**
 * Items handler class.
 * This class is responsible for providing data access mechanisms to the data source
 * of Q&A class objects.
 *
 * @author  marcan <marcan@notrevie.ca>
 */
class ItemHandler extends \XoopsPersistableObjectHandler
{
    private const TABLE = 'publisher_items';
    private const ENTITY = Item::class;
    private const ENTITYNAME = 'Item';
    private const KEYNAME = 'itemid';
    private const IDENTIFIER = 'title';

    /**
     * @var Helper
     */
    public    $helper;
    public    $publisherIsAdmin;
    protected $resultCatCounts = [];

    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        $this->helper = $helper ?? Helper::getInstance();
        $this->db = $db;
        $this->publisherIsAdmin = $this->helper->isUserAdmin();
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * @param bool $isNew
     *
     * @return \XoopsObject
     */
    public function create($isNew = true)
    {
        /** @var Item $obj */
        $obj = parent::create($isNew);
        if ($isNew) {
            $obj->setDefaultPermissions();
        }
        $obj->helper = $this->helper;

        return $obj;
    }

    /**
     * retrieve an item
     *
     * @param null|int   $id itemid of the user
     *
     * @param array|null $fields
     * @return mixed reference to the <a href='psi_element://Item'>Item</a> object, FALSE if failed
     */
    public function get($id = null, $fields = null)
    {
        $obj = parent::get($id);
        if (\is_object($obj)) {
            $obj->assignOtherProperties();
        }

        return $obj;
    }


    /**
     * insert a new item in the database
     *
     * @param \XoopsObject $item reference to the {@link Item}
     *                           object
     * @param bool         $force
     *
     * @return bool FALSE if failed, TRUE if already present and unchanged or successful
     */
    public function insert(\XoopsObject $item, $force = false)  //insert(&$item, $force = false)
    {
        if (!$item->meta_keywords() || !$item->meta_description() || !$item->short_url()) {
            $publisherMetagen = new Metagen($item->getTitle(), $item->getVar('meta_keywords'), $item->getVar('summary'));
            // Auto create meta tags if empty
            if (!$item->meta_keywords()) {
                $item->setVar('meta_keywords', $publisherMetagen->keywords);
            }
            if (!$item->meta_description()) {
                $item->setVar('meta_description', $publisherMetagen->description);
            }
            // Auto create short_url if empty
            if (!$item->short_url()) {
                $item->setVar('short_url', \mb_substr(Metagen::generateSeoTitle($item->getVar('title', 'n'), false), 0, 254));
            }
        }
        if (!parent::insert($item, $force)) {
            return false;
        }
        if (\xoops_isActiveModule('tag')) {
            // Storing tags information
            /** @var \XoopsModules\Tag\TagHandler $tagHandler */
            $tagHandler = \XoopsModules\Tag\Helper::getInstance()->getHandler('Tag'); // xoops_getModuleHandler('tag', 'tag');
            $tagHandler->updateByItem($item->getVar('item_tag'), $item->getVar('itemid'), $this->helper->getDirname(), 0);
        }

        return true;
    }

    /**
     * delete an item from the database
     *
     * @param \XoopsObject $item reference to the ITEM to delete
     * @param bool         $force
     *
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $item, $force = false)
    {
        // Deleting the files
        if (!$this->helper->getHandler('File')->deleteItemFiles($item)) {
            $item->setErrors(\_AM_PUBLISHER_FILE_DELETE_ERROR);
        }
        if (!parent::delete($item, $force)) {
            $item->setErrors(\_AM_PUBLISHER_ITEM_DELETE_ERROR);

            return false;
        }
        // Removing tags information
        if (\xoops_isActiveModule('tag')) {
            /** @var \XoopsModules\Tag\TagHandler $tagHandler */
            $tagHandler = \XoopsModules\Tag\Helper::getInstance()->getHandler('Tag'); // xoops_getModuleHandler('tag', 'tag');
            $tagHandler->updateByItem('', $item->getVar('itemid'), $this->helper->getDirname(), 0);
        }

        return true;
    }

    /**
     * retrieve items from the database
     *
     * @param \CriteriaElement|null $criteria           {@link CriteriaElement}
     *                                                  conditions to be met
     * @param bool|string           $idKey              what shall we use as array key ? none, itemid, categoryid
     * @param bool                  $as_object
     * @param string|null           $notNullFields
     * @return array           array of <a href='psi_element://Item'>Item</a> objects
     */
    public function &getObjects(\CriteriaElement $criteria = null, $idKey = 'none', $as_object = true, $notNullFields = null)
    {
        $limit         = $start = 0;
        $ret           = [];
        $notNullFields = (null !== $notNullFields) ?: '';

        $sql = 'SELECT * FROM ' . $this->db->prefix($this->helper->getDirname() . '_items');
        if (null !== $criteria && ($criteria instanceof \Criteria || $criteria instanceof \CriteriaCompo)) {
            $whereClause = $criteria->renderWhere();
            if ('WHERE ()' !== $whereClause) {
                $sql .= ' ' . $criteria->renderWhere();
                if (!empty($notNullFields)) {
                    $sql .= $this->notNullFieldClause($notNullFields, true);
                }
            } elseif (!empty($notNullFields)) {
                $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
            }
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        } elseif (!empty($notNullFields)) {
            $sql .= $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result || 0 === $GLOBALS['xoopsDB']->getRowsNum($result)) {
            return $ret;
        }
        $theObjects = [];
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            //            $item = new Item();
            $item = $this->create();
            $item->assignVars($myrow);
            $theObjects[$myrow['itemid']] = $item;
            unset($item);
        }
        foreach ($theObjects as $theObject) {
            if ('none' === $idKey) {
                $ret[] = $theObject;
            } elseif ('itemid' === $idKey) {
                $ret[$theObject->itemid()] = $theObject;
            } else {
                $ret[$theObject->getVar($idKey)][$theObject->itemid()] = $theObject;
            }
            unset($theObject);
        }

        return $ret;
    }

    /**
     * count items matching a condition
     *
     * @param \CriteriaElement|null $criteria   Criteria to match
     * @param string|null           $notNullFields
     *
     * @return int count of items
     */
    public function getCount(\CriteriaElement $criteria = null, $notNullFields = null)
    {
//        $notNullFields = $notNullFields ?? null;
        $sql           = 'SELECT COUNT(*) FROM ' . $this->db->prefix($this->helper->getDirname() . '_items');
        if (null !== $criteria && ($criteria instanceof \Criteria || $criteria instanceof \CriteriaCompo)) {
            $whereClause = $criteria->renderWhere();
            if ('WHERE ()' !== $whereClause) {
                $sql .= ' ' . $criteria->renderWhere();
                if (!empty($notNullFields)) {
                    $sql .= $this->notNullFieldClause($notNullFields, true);
                }
            } elseif (!empty($notNullFields)) {
                $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
            }
        } elseif (!empty($notNullFields)) {
            $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
        }
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        [$count] = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * @param int                 $categoryid
     * @param string|array        $status
     * @param string|null         $notNullFields
     * @param null|\CriteriaCompo $criteriaPermissions
     * @return \CriteriaCompo
     */
    private function getItemsCriteria($categoryid = -1, $status = '', $notNullFields = null, $criteriaPermissions = null)
    {
        //        $notNullFields = (null !== $notNullFields) ?: '';
        //        global $publisherIsAdmin;
        //        $ret = 0;
        //        if (!$publisherIsAdmin) {
        //            $criteriaPermissions = new \CriteriaCompo();
        //            // Categories for which user has access
        //            $categoriesGranted = $this->helper->getHandler('Permission')->getGrantedItems('category_read');
        //            if (!empty($categoriesGranted)) {
        //                $grantedCategories = new \Criteria('categoryid', "(" . implode(',', $categoriesGranted) . ")", 'IN');
        //                $criteriaPermissions->add($grantedCategories, 'AND');
        //            } else {
        //                return $ret;
        //            }
        //        }

        $criteriaCategory = null;
        if (isset($categoryid) && -1 !== $categoryid) {
            $criteriaCategory = new \Criteria('categoryid', $categoryid);
        }

        $criteriaStatus = new \CriteriaCompo();
        if (!empty($status) && \is_array($status)) {
            foreach ($status as $v) {
                $criteriaStatus->add(new \Criteria('status', $v), 'OR');
            }
        } elseif (!empty($status) && -1 !== $status) {
            $criteriaStatus->add(new \Criteria('status', $status), 'OR');
        }

        $criteria = new \CriteriaCompo();
        if (null !== $criteriaCategory) {
            $criteria->add($criteriaCategory);
        }

        if (null !== $criteriaPermissions) {
            $criteria->add($criteriaPermissions);
        }

        if (null !== $criteriaStatus) {
            $criteria->add($criteriaStatus);
        }

        return $criteria;
    }

    /**
     * @param             $categoryid
     * @param string      $status
     * @param string|null $notNullFields
     *
     * @return int
     */
    public function getItemsCount($categoryid = -1, $status = '', $notNullFields = null)
    {
//        $notNullFields       = $notNullFields ?? null;
        $criteriaPermissions = null;
        if (!$this->publisherIsAdmin) {
            $criteriaPermissions = new \CriteriaCompo();
            // Categories for which user has access
            $categoriesGranted = $this->helper->getHandler('Permission')->getGrantedItems('category_read');
            if (!empty($categoriesGranted)) {
                $grantedCategories = new \Criteria('categoryid', '(' . \implode(',', $categoriesGranted) . ')', 'IN');
                $criteriaPermissions->add($grantedCategories, 'AND');
            } else {
                return 0;
            }
        }
        //        $ret = [];
        $criteria = $this->getItemsCriteria($categoryid, $status, $notNullFields, $criteriaPermissions);

        $ret = $this->getCount($criteria, $notNullFields);

        return $ret;
    }

    /**
     * @param int         $limit
     * @param int         $start
     * @param int         $categoryid
     * @param string      $sort
     * @param string      $order
     * @param string|null $notNullFields
     * @param bool        $asObject
     * @param string      $idKey
     * @param bool        $excludeExpired
     *
     * @return array
     */
    public function getAllPublished($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = null, $asObject = true, $idKey = 'none', $excludeExpired = true)
    {
        $notNullFields = (null !== $notNullFields) ?: '';
        $otherCriteria = new \CriteriaCompo();
        if (!$this->publisherIsAdmin) {
            $criteriaDateSub = new \Criteria('datesub', \time(), '<=');
            $otherCriteria->add($criteriaDateSub);
        }
        if ($excludeExpired) {
            // by default expired items are excluded from list of published items
            $criteriaExpire = new \CriteriaCompo();
            $criteriaExpire->add(new \Criteria('dateexpire', '0'), 'OR');
            $criteriaExpire->add(new \Criteria('dateexpire', \time(), '>='), 'OR');
            $otherCriteria->add($criteriaExpire);
        }

        return $this->getItems($limit, $start, [Constants::PUBLISHER_STATUS_PUBLISHED], $categoryid, $sort, $order, $notNullFields, $asObject, $otherCriteria, $idKey);
    }

    /**
     * @param int         $limit
     * @param int         $start
     * @param int         $categoryid
     * @param string      $sort
     * @param string      $order
     * @param string|null $notNullFields
     * @param bool        $asObject
     * @param string      $idKey
     *
     * @return array
     */
    public function getAllExpired($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = null, $asObject = true, $idKey = 'none')
    {
        $notNullFields = (null !== $notNullFields) ?: '';
        $otherCriteria = new \CriteriaCompo();
        $otherCriteria->add(new \Criteria('dateexpire', \time(), '<='));
        $otherCriteria->add(new \Criteria('dateexpire', 0, '>'));

        return $this->getItems($limit, $start, -1, $categoryid, $sort, $order, $notNullFields, $asObject, $otherCriteria, $idKey);
    }

    /**
     * @param Item $obj
     *
     * @return bool
     */
    public function getPreviousPublished($obj)
    {
        $ret           = false;
        $otherCriteria = new \CriteriaCompo();
        $otherCriteria->add(new \Criteria('datesub', $obj->getVar('datesub'), '<'));
        $objs = $this->getItems(1, 0, [Constants::PUBLISHER_STATUS_PUBLISHED], $obj->getVar('categoryid'), 'datesub', 'DESC', '', true, $otherCriteria, 'none');
        if (\count($objs) > 0) {
            $ret = $objs[0];
        }

        return $ret;
    }

    /**
     * @param Item $obj
     *
     * @return bool
     */
    public function getNextPublished($obj)
    {
        $ret           = false;
        $otherCriteria = new \CriteriaCompo();
        $otherCriteria->add(new \Criteria('datesub', $obj->getVar('datesub'), '>'));
        $otherCriteria->add(new \Criteria('datesub', \time(), '<='));
        $objs = $this->getItems(1, 0, [Constants::PUBLISHER_STATUS_PUBLISHED], $obj->getVar('categoryid'), 'datesub', 'ASC', '', true, $otherCriteria, 'none');
        if (\count($objs) > 0) {
            $ret = $objs[0];
        }

        return $ret;
    }

    /**
     * @param int         $limit
     * @param int         $start
     * @param int         $categoryid
     * @param string      $sort
     * @param string      $order
     * @param string|null $notNullFields
     * @param bool        $asObject
     * @param string      $idKey
     *
     * @return array
     */
    public function getAllSubmitted($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = null, $asObject = true, $idKey = 'none')
    {
        $notNullFields = (null !== $notNullFields) ?: '';
        return $this->getItems($limit, $start, [Constants::PUBLISHER_STATUS_SUBMITTED], $categoryid, $sort, $order, $notNullFields, $asObject, null, $idKey);
    }

    /**
     * @param int    $limit
     * @param int    $start
     * @param int    $categoryid
     * @param string $sort
     * @param string $order
     * @param string $notNullFields
     * @param bool   $asObject
     * @param string $idKey
     *
     * @return array
     */
    public function getAllOffline($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $idKey = 'none')
    {
        return $this->getItems($limit, $start, [Constants::PUBLISHER_STATUS_OFFLINE], $categoryid, $sort, $order, $notNullFields, $asObject, null, $idKey);
    }

    /**
     * @param int    $limit
     * @param int    $start
     * @param int    $categoryid
     * @param string $sort
     * @param string $order
     * @param string $notNullFields
     * @param bool   $asObject
     * @param string $idKey
     *
     * @return array
     */
    public function getAllRejected($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $idKey = 'none')
    {
        return $this->getItems($limit, $start, [Constants::PUBLISHER_STATUS_REJECTED], $categoryid, $sort, $order, $notNullFields, $asObject, null, $idKey);
    }

    /**
     * @param int                 $limit
     * @param int                 $start
     * @param array|string        $status
     * @param int                 $categoryid
     * @param string              $sort
     * @param string              $order
     * @param string|null         $notNullFields
     * @param bool                $asObject
     * @param null|\CriteriaCompo $otherCriteria
     * @param bool|string         $idKey
     * @return array
     * @internal param bool $asObject
     */
    public function getItems($limit = 0, $start = 0, $status = '', $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = null, $asObject = true, $otherCriteria = null, $idKey = 'none')
    {
        $notNullFields       = (null !== $notNullFields) ?: '';
        $criteriaPermissions = null;
        if (!$this->publisherIsAdmin) {
            $criteriaPermissions = new \CriteriaCompo();
            // Categories for which user has access
            $categoriesGranted = $this->helper->getHandler('Permission')->getGrantedItems('category_read');
            if (!empty($categoriesGranted)) {
                $grantedCategories = new \Criteria('categoryid', '(' . \implode(',', $categoriesGranted) . ')', 'IN');
                $criteriaPermissions->add($grantedCategories, 'AND');
            } else {
                return [];
            }
        }

        $criteria = $this->getItemsCriteria($categoryid, $status, $notNullFields, $criteriaPermissions);
        /*
                if (isset($categoryid) && $categoryid != -1) {
                    $criteriaCategory = new \Criteria('categoryid', $categoryid);
                }
                $criteriaStatus = new \CriteriaCompo();
                if (!empty($status) && is_array($status)) {
                    foreach ($status as $v) {
                        $criteriaStatus->add(new \Criteria('status', $v), 'OR');
                    }
                } elseif (!empty($status) && $status != -1) {
                    $criteriaStatus->add(new \Criteria('status', $status), 'OR');
                }
                $criteria = new \CriteriaCompo();
                if (!empty($criteriaCategory)) {
                    $criteria->add($criteriaCategory);
                }
                if (!empty($criteriaPermissions)) {
                    $criteria->add($criteriaPermissions);
                }
                if (!empty($criteriaStatus)) {
                    $criteria->add($criteriaStatus);
                }
        */
        //        $ret  = [];

        if (null !== $otherCriteria) {
            $criteria->add($otherCriteria);
        }
        $criteria->setLimit($limit);
        $criteria->setStart($start);
        $criteria->setSort($sort);
        $criteria->order = $order; // patch for XOOPS <= 2.5.10 does not set order correctly using setOrder() method
        $ret             = &$this->getObjects($criteria, $idKey, true);

        return $ret;
    }

    /**
     * @param string $field
     * @param string $status
     * @param int    $categoryid
     *
     * @return bool
     * @throws \Exception
     */
    public function getRandomItem($field = '', $status = '', $categoryid = -1)
    {
        $ret           = false;
        $notNullFields = $field;
        // Getting the number of published Items
        $totalItems = $this->getItemsCount($categoryid, $status, $notNullFields);
        if ($totalItems > 0) {
            --$totalItems;
            $entryNumber = \random_int(0, $totalItems);
            //            $entryNumber2 = random_int(0, $totalItems);

            $item = $this->getItems(1, $entryNumber, $status, $categoryid, $sort = 'datesub', $order = 'DESC', $notNullFields);
            if ($item) {
                $ret = $item[0];
            }
        }

        return $ret;
    }

    /**
     * delete Items matching a set of conditions
     *
     * @param \CriteriaElement|null $criteria {@link CriteriaElement}
     *
     * @param bool                  $force
     * @param bool                  $asObject
     * @return bool FALSE if deletion failed
     */
    public function deleteAll(\CriteriaElement $criteria = null, $force = true, $asObject = false) //deleteAll($criteria = null)
    {
        //todo resource consuming, use get list instead?
        $items = &$this->getObjects($criteria);
        foreach ($items as $item) {
            $this->delete($item);
        }

        return true;
    }

    /**
     * @param $itemId
     *
     * @return bool
     */
    public function updateCounter($itemId)
    {
        $sql = 'UPDATE ' . $this->db->prefix($this->helper->getDirname() . '_items') . ' SET counter=counter+1 WHERE itemid = ' . $itemId;
        if ($this->db->queryF($sql)) {
            return true;
        }

        return false;
    }

    /**
     * @param string|array $notNullFields
     * @param bool         $withAnd
     *
     * @return string
     */
    public function notNullFieldClause($notNullFields = '', $withAnd = false)
    {
        $ret = '';
        if ($withAnd) {
            $ret .= ' AND ';
        }
        if (!empty($notNullFields) && \is_array($notNullFields)) {
            foreach ($notNullFields as $v) {
                $ret .= " ($v IS NOT NULL AND $v <> ' ' )";
            }
        } elseif (!empty($notNullFields)) {
            $ret .= " ($notNullFields IS NOT NULL AND $notNullFields <> ' ' )";
        }

        return $ret;
    }

    /**
     * @param array        $queryArray
     * @param string       $andor
     * @param int          $limit
     * @param int          $offset
     * @param int|array    $userid
     * @param array        $categories
     * @param int|string   $sortby
     * @param string|array $searchin
     * @param string       $extra
     *
     * @return array
     */
    public function getItemsFromSearch($queryArray = [], $andor = 'AND', $limit = 0, $offset = 0, $userid = 0, $categories = [], $sortby = 0, $searchin = '', $extra = '')
    {
        $count            = 0;
        $ret              = [];
        $criteriaKeywords = $criteriaPermissions = $criteriaUser = null;
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = \xoops_getHandler('groupperm');
        $groups           = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        $searchin         = empty($searchin) ? ['title', 'body', 'summary'] : (\is_array($searchin) ? $searchin : [$searchin]);
        if (\in_array('all', $searchin, true) || 0 === \count($searchin)) {
            $searchin = ['title', 'subtitle', 'body', 'summary', 'meta_keywords'];
            //add support for searching in tags if Tag module exists and is active
            if (false !== $this->helper::getHelper('tag')) {
                $searchin[] = 'item_tag';
            }
        }
        if ($userid && \is_array($userid)) {
            $userid       = \array_map('\intval', $userid);
            $criteriaUser = new \CriteriaCompo();
            $criteriaUser->add(new \Criteria('uid', '(' . \implode(',', $userid) . ')', 'IN'), 'OR');
        } elseif (\is_numeric($userid) && $userid > 0) {
            $criteriaUser = new \CriteriaCompo();
            $criteriaUser->add(new \Criteria('uid', $userid), 'OR');
        }

        //        if (is_array($queryArray)) {
        //            $count = count($queryArray);
        //        }
        if ($queryArray && \is_array($queryArray)) {
            $criteriaKeywords = new \CriteriaCompo();
            foreach ($queryArray as $iValue) {
                $criteriaKeyword = new \CriteriaCompo();
                foreach ($searchin as $searchField) {
                    $criteriaKeyword->add(new \Criteria($searchField, '%' . $iValue . '%', 'LIKE'), 'OR');
                }
                /*
                if (in_array('title', $searchin)) {
                    $criteriaKeyword->add(new \Criteria('title', '%' . $iValue . '%', 'LIKE'), 'OR');
                }
                if (in_array('subtitle', $searchin)) {
                    $criteriaKeyword->add(new \Criteria('subtitle', '%' . $iValue . '%', 'LIKE'), 'OR');
                }
                if (in_array('body', $searchin)) {
                    $criteriaKeyword->add(new \Criteria('body', '%' . $iValue . '%', 'LIKE'), 'OR');
                }
                if (in_array('summary', $searchin)) {
                    $criteriaKeyword->add(new \Criteria('summary', '%' . $iValue . '%', 'LIKE'), 'OR');
                }
                if (in_array('meta_keywords', $searchin)) {
                    $criteriaKeyword->add(new \Criteria('meta_keywords', '%' . $iValue . '%', 'LIKE'), 'OR');
                }
                */
                $criteriaKeywords->add($criteriaKeyword, $andor);
                unset($criteriaKeyword);
            }
        }
        if (!$this->publisherIsAdmin && (\count($categories) > 0)) {
            $criteriaPermissions = new \CriteriaCompo();
            // Categories for which user has access
            $categoriesGranted = $grouppermHandler->getItemIds('category_read', $groups, $this->helper->getModule()->getVar('mid'));
            $categoriesGranted = \array_intersect($categoriesGranted, $categories);
            if (0 === \count($categoriesGranted)) {
                return $ret;
            }
            $grantedCategories = new \Criteria('categoryid', '(' . \implode(',', $categoriesGranted) . ')', 'IN');
            $criteriaPermissions->add($grantedCategories, 'AND');
        } elseif (\count($categories) > 0) {
            $criteriaPermissions = new \CriteriaCompo();
            $grantedCategories   = new \Criteria('categoryid', '(' . \implode(',', $categories) . ')', 'IN');
            $criteriaPermissions->add($grantedCategories, 'AND');
        }
        $criteriaItemsStatus = new \CriteriaCompo();
        $criteriaItemsStatus->add(new \Criteria('status', Constants::PUBLISHER_STATUS_PUBLISHED));
        $criteria = new \CriteriaCompo();
        if (null !== $criteriaUser) {
            $criteria->add($criteriaUser, 'AND');
        }
        if (null !== $criteriaKeywords) {
            $criteria->add($criteriaKeywords, 'AND');
        }
        if (null !== $criteriaPermissions) {
            $criteria->add($criteriaPermissions);
        }
        if (null !== $criteriaItemsStatus) {
            $criteria->add($criteriaItemsStatus, 'AND');
        }
        $criteria->setLimit($limit);
        $criteria->setStart($offset);
        if (empty($sortby)) {
            $sortby = 'datesub';
        }
        $criteria->setSort($sortby);
        $order = 'ASC';
        if ('datesub' === $sortby) {
            $order = 'DESC';
        }
        $criteria->order = $order; // patch for XOOPS <= 2.5.10, does not set order correctly using setOrder() method
        $ret             = &$this->getObjects($criteria);

        return $ret;
    }

    /**
     * @param array $categoriesObj
     * @param array $status
     *
     * @return array
     */
    public function getLastPublishedByCat($categoriesObj, $status = [Constants::PUBLISHER_STATUS_PUBLISHED])
    {
        $ret    = [];
        $catIds = [];
        foreach ($categoriesObj as $parentid) {
            foreach ($parentid as $category) {
                $catId          = $category->getVar('categoryid');
                $catIds[$catId] = $catId;
            }
        }
        if (empty($catIds)) {
            return $ret;
        }
        /*$cat  = [];

        $sql = "SELECT categoryid, MAX(datesub) as date FROM " . $this->db->prefix($this->helper->getDirname() . '_items') . " WHERE status IN (" . implode(',', $status) . ") GROUP BY categoryid";
        $result = $this->db->query($sql);
        while (false !== ($row = $this->db->fetchArray($result))) {
            $cat[$row['categoryid']] = $row['date'];
        }
        if (count($cat) == 0) return $ret;
        $sql = "SELECT categoryid, itemid, title, short_url, uid, datesub FROM " . $this->db->prefix($this->helper->getDirname() . '_items');
        $criteriaBig = new \CriteriaCompo();
        foreach ($cat as $id => $date) {
            $criteria = new \CriteriaCompo(new \Criteria('categoryid', $id));
            $criteria->add(new \Criteria('datesub', $date));
            $criteriaBig->add($criteria, 'OR');
            unset($criteria);
        }
        $sql .= " " . $criteriaBig->renderWhere();
        $result = $this->db->query($sql);
        while (false !== ($row = $this->db->fetchArray($result))) {
//            $item = new Item();
            $item = static::create();
            $item->assignVars($row);
            $ret[$row['categoryid']] = $item;
            unset($item);
        }
        */
        $sql    = 'SELECT mi.categoryid, mi.itemid, mi.title, mi.short_url, mi.uid, mi.datesub';
        $sql    .= ' FROM (SELECT categoryid, MAX(datesub) AS date FROM ' . $this->db->prefix($this->helper->getDirname() . '_items');
        $sql    .= ' WHERE status IN (' . \implode(',', $status) . ')';
        $sql    .= ' AND categoryid IN (' . \implode(',', $catIds) . ')';
        $sql    .= ' GROUP BY categoryid)mo';
        $sql    .= ' JOIN ' . $this->db->prefix($this->helper->getDirname() . '_items') . ' mi ON mi.datesub = mo.date';
        $result = $this->db->query($sql);
        while (false !== ($row = $this->db->fetchArray($result))) {
            // $item = new Item();
            $item = $this->create();
            $item->assignVars($row);
            $ret[$row['categoryid']] = $item;
            unset($item);
        }

        return $ret;
    }

    /**
     * @param         $parentid
     * @param         $catsCount
     * @param string  $spaces
     * @return int
     */
    public function countArticlesByCat($parentid, $catsCount, $spaces = '')
    {
        //        global $resultCatCounts;
        $newspaces = $spaces . '--';
        $thecount  = 0;
        foreach ($catsCount[$parentid] as $subCatId => $count) {
            $thecount                         += $count;
            $this->resultCatCounts[$subCatId] = $count;
            if (isset($catsCount[$subCatId])) {
                $thecount                         += $this->countArticlesByCat($subCatId, $catsCount, $newspaces);
                $this->resultCatCounts[$subCatId] = $thecount;
            }
        }

        return $thecount;
    }

    /**
     * @param int   $catId
     * @param array $status
     * @param bool  $inSubCat
     *
     * @return array
     */
    public function getCountsByCat($catId, $status, $inSubCat = false)
    {
        //        global $resultCatCounts;

        $ret       = [];
        $catsCount = [];
        $sql       = 'SELECT c.parentid, i.categoryid, COUNT(*) AS count FROM ' . $this->db->prefix($this->helper->getDirname() . '_items') . ' AS i INNER JOIN ' . $this->db->prefix($this->helper->getDirname() . '_categories') . ' AS c ON i.categoryid=c.categoryid';
        if ((int)$catId > 0) {
            $sql .= ' WHERE i.categoryid = ' . (int)$catId;
            $sql .= ' AND i.status IN (' . \implode(',', $status) . ')';
        } else {
            $sql .= ' WHERE i.status IN (' . \implode(',', $status) . ')';
        }
        $sql    .= ' GROUP BY i.categoryid ORDER BY c.parentid ASC, i.categoryid ASC';
        $result = $this->db->query($sql);
        if (!$result) {
            return $ret;
        }
        if (!$inSubCat) {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $catsCount[$row['categoryid']] = $row['count'];
            }

            return $catsCount;
        }
        //        while (false !== ($row = $this->db->fetchArray($result))) {
        while (false !== ($row = $this->db->fetchArray($result))) {
            $catsCount[$row['parentid']][$row['categoryid']] = $row['count'];
        }
        //        $resultCatCounts = [];
        foreach ($catsCount[0] as $subCatId => $count) {
            $this->resultCatCounts[$subCatId] = $count;
            if (isset($catsCount[$subCatId])) {
                $this->resultCatCounts[$subCatId] += $this->countArticlesByCat($subCatId, $catsCount, '');
            }
        }

        return $this->resultCatCounts;
    }
}
