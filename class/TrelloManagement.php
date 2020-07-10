<?php declare(strict_types=1);


namespace XoopsModules\Publisher;

use XoopsModules\Publisher\Helper;

/**
 * Class TrelloManagement
 * @package XoopsModules\Publisher
 */
class TrelloManagement
{
    /**
     * @param $statusId
     * @param $itemId
     * @return mixed
     */
    public function getProjectTaskByStatus($statusId, $itemId)
    {
        $helper = Helper::getInstance();
        $dbHandle = new TrelloDBController();
        $query = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_items') .  'WHERE status= ? AND itemid = ?';
        $result = $dbHandle->runQuery($query, 'ii', [$statusId, $itemId]);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getAllStatus()
    {
        $helper = Helper::getInstance();
        $dbHandle = new TrelloDBController();
        $query = 'SELECT itemid, title, status FROM ' . $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_items');
        $result = $dbHandle->runBaseQuery($query);

        return $result;
    }

    /**
     * @param $statusId
     * @param $itemId
     */
    public function editTaskStatus($statusId, $itemId)
    {
        $helper = Helper::getInstance();
        $dbHandle = new TrelloDBController();
        $query = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix($helper->getDirname() . '_items') . 'SET status = ? WHERE itemid = ?';
        $result = $dbHandle->update($query, 'ii', [$statusId, $itemId]);

        return $result;
    }
}
