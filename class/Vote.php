<?php

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
 * Publisher module for xoops
 *
 * @copyright      module for xoops
 * @license        GPL 3.0 or later
 * @package        Publisher
 * @since          1.0
 * @min_xoops      2.5.10
 * @author         XOOPS Development Team
 */
\defined('XOOPS_ROOT_PATH') || exit('Restricted access');

/**
 * Class Object Vote
 */
class Vote extends \XoopsObject
{
    /**
     * Constructor
     *
     * @param null
     */
    public function __construct()
    {
        $this->initVar('ratingid', \XOBJ_DTYPE_INT);
        $this->initVar('source', \XOBJ_DTYPE_INT);
        $this->initVar('itemid', \XOBJ_DTYPE_INT);
        $this->initVar('rate', \XOBJ_DTYPE_INT);
        $this->initVar('uid', \XOBJ_DTYPE_INT);
        $this->initVar('ip', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('date', \XOBJ_DTYPE_INT);
        $this->initVar('votetype', \XOBJ_DTYPE_INT);
    }

    /**
     * @static function &getInstance
     *
     * @param null
     */
    public static function getInstance(): void
    {
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }
    }

//    /**
//     * The new inserted $Id
//     * @return int inserted id
//     */
//    public function getNewInsertedIdVote(): int
//    {
//        $newInsertedId = $GLOBALS['xoopsDB']->getInsertId();
//
//        return $newInsertedId;
//    }

    /**
     * Get Values
     * @param array|null  $keys
     * @param string|null $format
     * @param int|null    $maxDepth
     * @return array
     */
    public function getValuesVote($keys = null, $format = null, $maxDepth = null): array
    {
        $ret             = $this->getValues($keys, $format, $maxDepth);
        $ret['ratingid'] = $this->getVar('ratingid');
        $ret['source']   = $this->getVar('source');
        $ret['itemid']   = $this->getVar('itemid');
        $ret['value']    = $this->getVar('rate');
        $ret['uid']      = \XoopsUser::getUnameFromId($this->getVar('rate_uid'));
        $ret['ip']       = $this->getVar('rate_ip');
        $ret['date']     = \formatTimestamp($this->getVar('date'), 's');

        return $ret;
    }

    /**
     * Returns an array representation of the object
     *
     * @return array
     */
    public function toArrayVote(): array
    {
        $ret  = [];
        $vars = $this->getVars();
        foreach (\array_keys($vars) as $var) {
            $ret[$var] = $this->getVar('"{$var}"');
        }

        return $ret;
    }
}
