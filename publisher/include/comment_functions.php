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
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @subpackage      Include
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: comment_functions.php 10374 2012-12-12 23:39:48Z trabis $
 *
 * @param $itemId
 * @param $totalNum
 */

function publisher_com_update($itemId, $totalNum)
{
    $db  = XoopsDatabaseFactory::getDatabaseConnection();
    $sql = 'UPDATE ' . $db->prefix('publisher_items') . ' SET comments = ' . $totalNum . ' WHERE itemid = ' . $itemId;
    $db->query($sql);
}

/**
 * @param $comment
 */
function publisher_com_approve(&$comment)
{
    // notification mail here
}
