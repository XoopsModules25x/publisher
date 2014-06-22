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
 * @version         $Id: groupperm.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || die("XOOPS root path not defined");

include_once XOOPS_ROOT_PATH . '/kernel/groupperm.php';

include_once dirname(dirname(__FILE__)) . '/include/common.php';

class PublisherGroupPermHandler extends XoopsGroupPermHandler
{
    /**
     * Check permission
     *
     * @param string    $gperm_name    Name of permission
     * @param int       $gperm_itemid  ID of an item
     * @param int/array $gperm_groupid A group ID or an array of group IDs
     * @param int       $gperm_modid   ID of a module
     *
     * @return bool TRUE if permission is enabled
     */
    public function checkRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
        $criteria = new CriteriaCompo(new Criteria('gperm_modid', $gperm_modid));
        $criteria->add(new Criteria('gperm_name', $gperm_name));
        $gperm_itemid = intval($gperm_itemid);
        if ($gperm_itemid > 0) {
            $criteria->add(new Criteria('gperm_itemid', $gperm_itemid));
        }
        if (is_array($gperm_groupid)) {
            $criteria2 = new CriteriaCompo();
            foreach ($gperm_groupid as $gid) {
                $criteria2->add(new Criteria('gperm_groupid', $gid), 'OR');
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new Criteria('gperm_groupid', $gperm_groupid));
        }
        if ($this->getCount($criteria) > 0) {
            return true;
        }

        return false;
    }
}
