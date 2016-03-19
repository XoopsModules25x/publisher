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

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once $GLOBALS['xoops']->path('kernel/groupperm.php');

include_once dirname(__DIR__) . '/include/common.php';

/**
 * Class PublisherGroupPermHandler
 */
class PublisherGroupPermHandler extends XoopsGroupPermHandler
{
    /**
     * Check permission
     *
     * @param string              $gpermName    Name of permission
     * @param int                 $gpermItemId  ID of an item
     * @param int          /array $gpermGroupId A group ID or an array of group IDs
     * @param int                 $gpermModId   ID of a module
     *
     * @return bool TRUE if permission is enabled
     */
    public function checkRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModId = 1)
    {
        $criteria = new CriteriaCompo(new Criteria('gperm_modid', $gpermModId));
        $criteria->add(new Criteria('gperm_name', $gpermName));
        $gpermItemId = (int)$gpermItemId;
        if ($gpermItemId > 0) {
            $criteria->add(new Criteria('gperm_itemid', $gpermItemId));
        }
        if (is_array($gpermGroupId)) {
            $criteria2 = new CriteriaCompo();
            foreach ($gpermGroupId as $gid) {
                $criteria2->add(new Criteria('gperm_groupid', $gid), 'OR');
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new Criteria('gperm_groupid', $gpermGroupId));
        }
        return $this->getCount($criteria) > 0;
    }
}
