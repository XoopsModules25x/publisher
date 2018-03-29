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
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once $GLOBALS['xoops']->path('kernel/groupperm.php');

require_once __DIR__ . '/../include/common.php';

/**
 * Class GroupPermHandler
 */
class GrouppermHandler extends \XoopsGroupPermHandler
{
    /**
     * Check permission
     *
     * @param string $gpermName   Name of permission
     * @param int    $gpermItemId ID of an item
     * @param        $gpermGroupId
     * @param int    $gpermModId  ID of a module
     *
     * @param  bool  $trueifadmin
     * @return bool TRUE if permission is enabled
     * @internal param $int /array $gpermGroupId A group ID or an array of group IDs
     */
    public function checkRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModId = 1, $trueifadmin = true) //checkRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModId = 1)
    {
        $criteria = new \CriteriaCompo(new \Criteria('gperm_modid', $gpermModId));
        $criteria->add(new \Criteria('gperm_name', $gpermName));
        $gpermItemId = (int)$gpermItemId;
        if ($gpermItemId > 0) {
            $criteria->add(new \Criteria('gperm_itemid', $gpermItemId));
        }
        if (is_array($gpermGroupId)) {
            $criteria2 = new \CriteriaCompo();
            foreach ($gpermGroupId as $gid) {
                $criteria2->add(new \Criteria('gperm_groupid', $gid), 'OR');
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new \Criteria('gperm_groupid', $gpermGroupId));
        }

        return $this->getCount($criteria) > 0;
    }
}
