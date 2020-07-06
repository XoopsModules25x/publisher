<?php

declare(strict_types=1);
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
 *
 * @param $itemId
 * @param $totalNum
 */
function publisher_com_update($itemId, $totalNum)
{
    global $xoopsModule;
    /** @var \XoopsMySQLDatabase $db */
    $db  = \XoopsDatabaseFactory::getDatabaseConnection();
    $sql = 'UPDATE ' . $db->prefix($xoopsModule->getVar('dirname', 'n') . '_items') . ' SET comments = ' . $totalNum . ' WHERE itemid = ' . $itemId;
    $db->query($sql);
}

/**
 * @param $comment
 */
function publisher_com_approve(&$comment)
{
    // notification mail here
}
