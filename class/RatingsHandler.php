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
 * Class Object RatingsHandler
 */
class RatingsHandler extends \XoopsPersistableObjectHandler
{
    private const TABLE = 'publisher_liking';
    private const ENTITY = Ratings::class;
    private const ENTITYNAME = 'Ratings';
    private const KEYNAME = 'rate_id';
    private const IDENTIFIER = 'rate_itemid';

    /**
     * Constructor
     * @param \XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        $this->db = $db;
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * @param bool $isNew
     *
     * @return Object
     */
    public function create($isNew = true)
    {
        return parent::create($isNew);
    }

    /**
     * retrieve a field
     *
     * @param int   $i field id
     * @param array $fields
     * @return mixed reference to the {@link Get} object
     */
    public function get($i = null, $fields = null)
    {
        return parent::get($i, $fields);
    }

    /**
     * get inserted id
     *
     * @param null
     * @return int reference to the {@link Get} object
     */
    public function getInsertId()
    {
        return $this->db->getInsertId();
    }

    /**
     * Get Rating per item in the database
     * @param int $itemId
     * @param int $source
     * @return array
     */
    public function getItemRating($itemId = 0, $source = 0)
    {
        $helper = \XoopsModules\Publisher\Helper::getInstance();

        $itemRating               = [];
        $itemRating['nb_ratings'] = 0;
        $uid                      = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        $voted                    = false;
        $ip                       = \getenv('REMOTE_ADDR');
        $currentRating           = 0;
        $count                    = 0;

        if (Constants::RATING_5STARS === (int)$helper->getConfig('ratingbars')
            || Constants::RATING_10STARS === (int)$helper->getConfig('ratingbars')
            || Constants::RATING_10NUM === (int)$helper->getConfig('ratingbars')) {
            $rating_unitwidth = 25;
            if (Constants::RATING_5STARS === (int)$helper->getConfig('ratingbars')) {
                $max_units = 5;
            } else {
                $max_units = 10;
            }

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('rate_itemid', $itemId));
            $criteria->add(new \Criteria('rate_source', $source));

            $ratingObjs               = $helper->getHandler('ratings')->getObjects($criteria);
            $count                    = count($ratingObjs);
            $itemRating['nb_ratings'] = $count;

            foreach ($ratingObjs as $ratingObj) {
                $currentRating += $ratingObj->getVar('rate_value');
                if (($ratingObj->getVar('rate_ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $ratingObj->getVar('rate_uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $ratingObj->getVar('rate_id');
                }
            }
            unset($criteria);

            $itemRating['avg_rate_value'] = 0;
            if ($count > 0) {
                $itemRating['avg_rate_value'] = \number_format($currentRating / $count, 2);
            }
            if (1 == $count) {
                $text      = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_BLOG_RATING_CURRENT_1);
                $shorttext = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_BLOG_RATING_CURRENT_SHORT_1);
            } else {
                $text      = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_BLOG_RATING_CURRENT_X);
                $shorttext = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_BLOG_RATING_CURRENT_SHORT_X);
            }
            $text                    = \str_replace('%m', $max_units, $text);
            $text                    = \str_replace('%t', $itemRating['nb_ratings'], $text);
            $shorttext               = \str_replace('%t', $itemRating['nb_ratings'], $shorttext);
            $itemRating['text']      = $text;
            $itemRating['shorttext'] = $shorttext;
            $itemRating['size']      = ($itemRating['avg_rate_value'] * $rating_unitwidth) . 'px';
            $itemRating['maxsize']   = ($max_units * $rating_unitwidth) . 'px';
        } elseif (Constants::RATING_LIKES === (int)$helper->getConfig('ratingbars')) {
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('rate_itemid', $itemId));
            $criteria->add(new \Criteria('rate_source', $source));
            $criteria->add(new \Criteria('rate_value', 0, '<'));

            $ratingObjs = $helper->getHandler('Ratings')->getObjects($criteria);
            $count      = count($ratingObjs);

            foreach ($ratingObjs as $ratingObj) {
                $currentRating += $ratingObj->getVar('rate_value');
                if (($ratingObj->getVar('rate_ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $ratingObj->getVar('rate_uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $ratingObj->getVar('rate_id');
                }
            }
            unset($criteria);
            $itemRating['dislikes'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('rate_itemid', $itemId));
            $criteria->add(new \Criteria('rate_source', $source));
            $criteria->add(new \Criteria('rate_value', 0, '>'));

            $ratingObjs     = $helper->getHandler('ratings')->getObjects($criteria);
            $count          = count($ratingObjs);
            $currentRating = 0;
            foreach ($ratingObjs as $ratingObj) {
                $currentRating += $ratingObj->getVar('rate_value');
                if (($ratingObj->getVar('rate_ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $ratingObj->getVar('rate_uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $ratingObj->getVar('rate_id');
                }
            }
            unset($criteria);
            $itemRating['likes'] = $count;

            $count = $itemRating['likes'] + $itemRating['dislikes'];
            // Facebook Reactions  ==========================================

        } elseif (Constants::RATING_REACTION === (int)$helper->getConfig('ratingbars')) {
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('rate_itemid', $itemId));
            $criteria->add(new \Criteria('rate_source', $source));
            $criteria->add(new \Criteria('rate_value', 0, '<'));

            $ratingObjs               = $helper->getHandler('ratings')->getObjects($criteria);
            $count                    = count($ratingObjs);
            $itemRating['nb_ratings'] = $count;

            foreach ($ratingObjs as $ratingObj) {
                $currentRating += $ratingObj->getVar('rate_value');
                if (($ratingObj->getVar('rate_ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $ratingObj->getVar('rate_uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $ratingObj->getVar('rate_id');
                }
            }
            unset($criteria);
            $itemRating['dislikes'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('rate_itemid', $itemId));
            $criteria->add(new \Criteria('rate_source', $source));
            $criteria->add(new \Criteria('rate_value', 0, '>'));

            $ratingObjs     = $helper->getHandler('ratings')->getObjects($criteria);
            $count          = count($ratingObjs);
            $currentRating = 0;
            foreach ($ratingObjs as $ratingObj) {
                $currentRating += $ratingObj->getVar('rate_value');
                if (($ratingObj->getVar('rate_ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $ratingObj->getVar('rate_uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $ratingObj->getVar('rate_id');
                }
            }
            unset($criteria);
            $itemRating['likes'] = $count;

            $count = $itemRating['likes'] + $itemRating['dislikes'];
        } else {
            $itemRating['uid']        = $uid;
            $itemRating['nb_ratings'] = $count;
            $itemRating['voted']      = $voted;
            $itemRating['ip']         = $ip;
        }
        return $itemRating;
    }

    /**
     * delete ratings of given item
     * @param mixed $itemId
     * @param mixed $source
     * @return bool
     */
    public function deleteAllRatings($itemId, $source)
    {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('rate_itemid', $itemId));
        $criteria->add(new \Criteria('rate_source', $source));

        return $this->deleteAll($criteria);
    }
}
