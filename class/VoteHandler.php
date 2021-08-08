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

/**
 * Class Object VoteHandler
 */
class VoteHandler extends \XoopsPersistableObjectHandler
{
    private const TABLE = 'publisher_rating';
    private const ENTITY = Vote::class;
    private const ENTITYNAME = 'Vote';
    private const KEYNAME = 'ratingid';
    private const IDENTIFIER = 'itemid';
    private const SOURCE = 'source';

    /**
     * @var Helper
     */
    public $helper;

    /**
     * Constructor
     * @param \XoopsDatabase|null                 $db
     * @param \XoopsModules\Publisher\Helper|null $helper
     */
    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        $this->db = $db;
        /** @var Helper $this->helper */
        $this->helper = $helper ?? Helper::getInstance();

        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * get inserted id
     *
     * @param null
     * @return int reference to the {@link Get} object
     */
    public function getInsertId(): int
    {
        return $this->db->getInsertId();
    }

    /**
     * Get Rating per item in the database
     * @param int|null $itemId
     * @param int|null $source
     * @return array
     */
    public function getItemRating($itemId = null, $source = null): array
    {
        $itemId    = $itemId ?? 0;
        $source    = $source ?? 0;
        $xoopsUser = $GLOBALS['xoopsUser'];

        $itemRating            = [];
        $itemRating['nb_vote'] = 0;
        $uid                   = \is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
        $voted                 = false;
        $ip                    = \getenv('REMOTE_ADDR');
        $currentRating         = 0;
        $count                 = 0;

        $max_units   = 10;
        $ratingbarsValue  = (int)$this->helper->getConfig('ratingbars');
        $ratingArray = [Constants::RATING_5STARS, Constants::RATING_10STARS, Constants::RATING_10NUM];

        if (\in_array($ratingbarsValue, $ratingArray)) {
            $rating_unitwidth = 25;
            if (Constants::RATING_5STARS === (int)$this->helper->getConfig('ratingbars')) {
                $max_units = 5;
            }

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));

            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['nb_vote'] = $count;

            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);

            $itemRating['avg_rate_value'] = 0;
            if ($count > 0) {
                $itemRating['avg_rate_value'] = \number_format($currentRating / $count, 2);
            }
            if (1 == $count) {
                $text      = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_1);
                $shorttext = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_SHORT_1);
            } else {
                $text      = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_X);
                $shorttext = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_SHORT_X);
            }
            $text                    = \str_replace('%m', $max_units, $text);
            $text                    = \str_replace('%t', $itemRating['nb_vote'], $text);
            $shorttext               = \str_replace('%t', $itemRating['nb_vote'], $shorttext);
            $itemRating['text']      = $text;
            $itemRating['shorttext'] = $shorttext;
            $itemRating['size']      = ($itemRating['avg_rate_value'] * $rating_unitwidth) . 'px';
            $itemRating['maxsize']   = ($max_units * $rating_unitwidth) . 'px';

            $itemRating['ip']    = $ip;
            $itemRating['uid']   = $uid;
            $itemRating['voted'] = $voted;
            // YouTube Liking  ==========================================
        } elseif (Constants::RATING_LIKES === (int)$this->helper->getConfig('ratingbars')) {
            // get count of "dislikes"
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 0, '<'));

            $voteObjs = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count    = \count($voteObjs);

            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);
            $itemRating['dislikes'] = $count;

            // get count of "likes"
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 0, '>'));

            $voteObjs      = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count         = \count($voteObjs);
            $currentRating = 0;
            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);
            $itemRating['likes'] = $count;

            $itemRating['nb_vote'] = $itemRating['likes'] + $itemRating['dislikes'];
            $itemRating['ip']      = $ip;
            $itemRating['uid']     = $uid;
            $itemRating['voted']   = $voted;
            // Facebook Reactions  ==========================================
        } elseif (Constants::RATING_REACTION === (int)$this->helper->getConfig('ratingbars')) {
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 0, '<'));

            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['nb_vote'] = $count;

            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);
            $itemRating['dislikes'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 0, '>'));

            $voteObjs      = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count         = \count($voteObjs);
            $currentRating = 0;
            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);
            $itemRating['likes'] = $count;

            $itemRating['nb_vote'] = $itemRating['likes'] + $itemRating['dislikes'];
            $itemRating['ip']      = $ip;
            $itemRating['uid']     = $uid;
            $itemRating['voted']   = $voted;
        } else {
            $itemRating['uid']     = $uid;
            $itemRating['nb_vote'] = $count;
            $itemRating['voted']   = $voted;
            $itemRating['ip']      = $ip;
        }
        return $itemRating;
    }

    /**
     * Get Rating per item in the database
     * @param Item|null     $itemObj
     * @param int|null $source
     * @return array
     */
    public function getItemRating5($itemObj = null, $source = null): array
    {
        $itemId    = $itemObj->itemid();
        $source    = $source ?? 0;
        $xoopsUser = $GLOBALS['xoopsUser'];

        $itemRating            = [];
        $itemRating['nb_vote'] = 0;
        $uid                   = \is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
        $voted                 = false;
        $ip                    = \getenv('REMOTE_ADDR');
        $currentRating         = 0;
        $count                 = 0;

        $max_units   = 10;
        $ratingbarsValue  = $itemObj->votetype();
        $ratingArray = [Constants::RATING_5STARS, Constants::RATING_10STARS, Constants::RATING_10NUM];

        if (\in_array($ratingbarsValue, $ratingArray)) {
            $rating_unitwidth = 25;
            if (Constants::RATING_5STARS === $ratingbarsValue) {
                $max_units = 5;
            }

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));

            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['nb_vote'] = $count;

            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);

            $itemRating['avg_rate_value'] = 0;
            if ($count > 0) {
                $itemRating['avg_rate_value'] = \number_format($currentRating / $count, 2);
            }
            if (1 == $count) {
                $text      = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_1);
                $shorttext = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_SHORT_1);
            } else {
                $text      = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_X);
                $shorttext = \str_replace('%c', $itemRating['avg_rate_value'], \_MA_PUBLISHER_RATING_CURRENT_SHORT_X);
            }
            $text                    = \str_replace('%m', $max_units, $text);
            $text                    = \str_replace('%t', $itemRating['nb_vote'], $text);
            $shorttext               = \str_replace('%t', $itemRating['nb_vote'], $shorttext);
            $itemRating['text']      = $text;
            $itemRating['shorttext'] = $shorttext;
            $itemRating['size']      = ($itemRating['avg_rate_value'] * $rating_unitwidth) . 'px';
            $itemRating['maxsize']   = ($max_units * $rating_unitwidth) . 'px';

            $itemRating['ip']    = $ip;
            $itemRating['uid']   = $uid;
            $itemRating['voted'] = $voted;
            // YouTube Liking  ==========================================
        } elseif (Constants::RATING_LIKES === $ratingbarsValue) {
            // get count of "dislikes"
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 0, '<'));

            $voteObjs = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count    = \count($voteObjs);

            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);
            $itemRating['dislikes'] = $count;

            // get count of "likes"
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 0, '>'));

            $voteObjs      = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count         = \count($voteObjs);
            $currentRating = 0;
            foreach ($voteObjs as $voteObj) {
                $currentRating += $voteObj->getVar('rate');
                if (($voteObj->getVar('ip') == $ip && 0 == $uid) || ($uid > 0 && $uid == $voteObj->getVar('uid'))) {
                    $voted            = true;
                    $itemRating['id'] = $voteObj->getVar('ratingid');
                }
            }
            unset($criteria);
            $itemRating['likes'] = $count;

            $itemRating['nb_vote'] = $itemRating['likes'] + $itemRating['dislikes'];
            $itemRating['ip']      = $ip;
            $itemRating['uid']     = $uid;
            $itemRating['voted']   = $voted;
            // Facebook Reactions  ==========================================
        } elseif (Constants::RATING_REACTION === $ratingbarsValue) {
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 1));
            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['likes'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 2));
            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['love'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 3));
            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['smile'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 4));
            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['wow'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 5));
            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['sad'] = $count;

            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
            $criteria->add(new \Criteria(static::SOURCE, $source));
            $criteria->add(new \Criteria('rate', 6));
            $voteObjs              = $this->helper->getHandler(static::ENTITYNAME)->getObjects($criteria);
            $count                 = \count($voteObjs);
            $itemRating['angry'] = $count;


            $itemRating['nb_vote'] = $itemRating['likes'] + $itemRating['love'] + $itemRating['smile'] + $itemRating['wow'] + $itemRating['sad'] + $itemRating['angry'];
            $itemRating['ip']      = $ip;
            $itemRating['uid']     = $uid;
            $itemRating['voted']   = $voted;
        } else {
            $itemRating['uid']     = $uid;
            $itemRating['nb_vote'] = $count;
            $itemRating['voted']   = $voted;
            $itemRating['ip']      = $ip;
        }
        return $itemRating;
    }


    /**
     * delete vote of given item
     * @param mixed $itemId
     * @param mixed $source
     * @return bool
     */
    public function deleteAllVote($itemId, $source): bool
    {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria(static::IDENTIFIER, $itemId));
        $criteria->add(new \Criteria(static::SOURCE, $source));

        return $this->deleteAll($criteria);
    }

//TODO
    // delete all votes for an item
    // delete all votes
    // updates Vote counts for an item after new vote
    // convert vote type to another
    // TopRated
    // getAggregate

    // Average, Sum, Count
    // getVotingElement (FiveStarts, Reaction)
    // buildForm, getStyle
    //
    //tableName
    //behaviors
    //rules
    //attributeLabels
    //afterSave
    //getModelIdByName
    //getModelNameById
    //getIsAllowGuests
    //getIsAllowChangeVote
    //updateRating

    //getId
    //getVoterId
    //getVoterName
    //getVoteableId
    //getVotableName
    //getValue
    //getRange
    //getMinValue
    //getMaxValue
    //getTime

    //VoteRepositoryInterface:
    //find
    //findByVoter
    //findByVotable
    //getCountByVotable
    //getAvgByVotable
    //create
    //delete


    //VotesRepositoryTest
    //repo
    //vote
    //__construct
    //testRepo
    //_testCreate
    //_testFindByVoter
    //_testFindByVotable
    //_testAvg
    //_testCount
    //_testDelete
    //_votable
    //_voter


    //FieldVoteResultBase:
        //calculateResult
        //getVotesForField
    //
    //
    //VotingApiField:
        //defaultFieldSettings
        //defaultStorageSettings
        //fieldSettingsForm
        //generateSampleValue
        //isEmpty
        //mainPropertyName
        //postSave
        //propertyDefinitions
        //schema
        //storageSettingsForm
    //
    //
    //VotingApiWidgetBase:
        //canVote
        //getEntityForVoting
        //getForm
        //getInitialVotingElement
        //getLabel
        //getResults
        //getValues
        //getVoteSummary
        //getWindow

    //Rating
        //afterSave
        //attributeLabels
        //behaviors
        //compressIp
        //expandIp
        //getIsAllowChangeVote
        //getIsAllowGuests
        //getModelIdByName
        //getModelNameById
        //rules
        //tableName
        //updateRating







}
