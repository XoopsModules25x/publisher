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
 * @package         Class
 * @subpackage      Handlers
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: permission.php 10661 2013-01-04 19:22:48Z trabis $
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
include_once dirname(__DIR__) . '/include/common.php';

/**
 * Class PublisherPermissionHandler
 */
class PublisherPermissionHandler extends XoopsObjectHandler
{
    /**
     * @var PublisherPublisher
     * @access public
     */
    public $publisher;

    /**
     *
     */
    public function __construct()
    {
        $this->publisher = PublisherPublisher::getInstance();
    }

    /**
     * Returns permissions for a certain type
     *
     * @param string $gpermName "global", "forum" or "topic" (should perhaps have "post" as well - but I don't know)
     * @param int    $id        id of the item (forum, topic or possibly post) to get permissions for
     *
     * @return array
     */
    public function getGrantedGroupsById($gpermName, $id)
    {
        static $items;
        if (isset($items[$gpermName][$id])) {
            return $items[$gpermName][$id];
        }
        $groups   = array();
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('gperm_modid', $this->publisher->getModule()->getVar('mid')));
        $criteria->add(new Criteria('gperm_name', $gpermName));
        $criteria->add(new Criteria('gperm_itemid', $id));
        //Instead of calling groupperm handler and get objects, we will save some memory and do it our way
        $db    = XoopsDatabaseFactory::getDatabaseConnection();
        $limit = $start = 0;
        $sql   = 'SELECT gperm_groupid FROM ' . $db->prefix('group_permission');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $db->query($sql, $limit, $start);
        while (($myrow = $db->fetchArray($result)) !== false) {
            $groups[$myrow['gperm_groupid']] = $myrow['gperm_groupid'];
        }
        $items[$gpermName][$id] = $groups;

        return $groups;
    }

    /**
     * Returns permissions for a certain type
     *
     * @param string $gpermName "global", "forum" or "topic" (should perhaps have "post" as well - but I don't know)
     *
     * @return array
     */
    public function getGrantedItems($gpermName)
    {
        static $items;
        if (isset($items[$gpermName])) {
            return $items[$gpermName];
        }

        $ret = array();
        //Instead of calling groupperm handler and get objects, we will save some memory and do it our way
        $criteria = new CriteriaCompo(new Criteria('gperm_name', $gpermName));
        $criteria->add(new Criteria('gperm_modid', $this->publisher->getModule()->getVar('mid')));

        //Get user's groups
        $groups    = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
        $criteria2 = new CriteriaCompo();
        foreach ($groups as $gid) {
            $criteria2->add(new Criteria('gperm_groupid', $gid), 'OR');
        }
        $criteria->add($criteria2);
        $db  = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = 'SELECT gperm_itemid FROM ' . $db->prefix('group_permission');
        $sql .= ' ' . $criteria->renderWhere();
        $result = $db->query($sql, 0, 0);
        while (($myrow = $db->fetchArray($result)) !== false) {
            $ret[$myrow['gperm_itemid']] = $myrow['gperm_itemid'];
        }
        $items[$gpermName] = $ret;

        return $ret;
    }

    /**
     * @param string $gpermName
     * @param int    $id
     *
     * @return bool
     */
    public function isGranted($gpermName, $id)
    {
        if (!$id) {
            return false;
        }
        $permissions = $this->getGrantedItems($gpermName);
        if (!empty($permissions) && isset($permissions[$id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Saves permissions for the selected category
     *  saveCategoryPermissions()
     *
     * @param array   $groups   : group with granted permission
     * @param integer $itemId   : itemid on which we are setting permissions for Categories and Forums
     * @param string  $permName : name of the permission
     *
     * @return boolean : TRUE if the no errors occured
     */
    public function saveItemPermissions($groups, $itemId, $permName)
    {
        $result        = true;
        $moduleId     = $this->publisher->getModule()->getVar('mid');
        $gpermHandler = xoops_getHandler('groupperm');
        // First, if the permissions are already there, delete them
        $gpermHandler->deleteByModule($moduleId, $permName, $itemId);
        // Save the new permissions
        if (count($groups) > 0) {
            foreach ($groups as $groupId) {
                echo $groupId . '-';
                echo $gpermHandler->addRight($permName, $itemId, $groupId, $moduleId);
            }
        }

        return $result;
    }

    /**
     * Delete all permission for a specific item
     *  deletePermissions()
     *
     * @param integer $itemId    : id of the item for which to delete the permissions
     * @param string  $gpermName
     *
     * @return boolean : TRUE if the no errors occured
     */
    public function deletePermissions($itemId, $gpermName)
    {
        $result        = true;
        $gpermHandler = xoops_getHandler('groupperm');
        $gpermHandler->deleteByModule($this->publisher->getModule()->getVar('mid'), $gpermName, $itemId);

        return $result;
    }
}
