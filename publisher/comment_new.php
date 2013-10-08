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
 * @subpackage      Comments
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: comment_new.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(dirname(dirname(__FILE__))) . '/mainfile.php';
include_once dirname(__FILE__) . '/include/common.php';

$com_itemid = isset($_GET['com_itemid']) ? intval($_GET['com_itemid']) : 0;
if ($com_itemid > 0) {
    $itemObj = $publisher->getHandler('item')->get($com_itemid);
    $com_replytext = _POSTEDBY . '&nbsp;<strong>' . $itemObj->linkedPosterName() . '</strong>&nbsp;' . _DATE . '&nbsp;<strong>' . $itemObj->dateSub() . '</strong><br /><br />' . $itemObj->summary();
    $bodytext = $itemObj->body();
    if ($bodytext != '') {
        $com_replytext .= '<br /><br />' . $bodytext . '';
    }
    $com_replytitle = $itemObj->title();
    include_once XOOPS_ROOT_PATH . '/include/comment_new.php';
}

?>
