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
 */

use Xmf\Request;

require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/include/common.php';

$com_itemid = Request::getInt('com_itemid', 0, 'GET');
if ($com_itemid > 0) {
    $itemObj       = $helper->getHandler('Item')->get($com_itemid);
    $com_replytext = _POSTEDBY . '&nbsp;<strong>' . $itemObj->getLinkedPosterName() . '</strong>&nbsp;' . _DATE . '&nbsp;<strong>' . $itemObj->dateSub() . '</strong><br><br>' . $itemObj->summary();
    $bodytext      = $itemObj->body();
    if ('' != $bodytext) {
        $com_replytext .= '<br><br>' . $bodytext . '';
    }
    $com_replytitle = $itemObj->getTitle();
    require_once $GLOBALS['xoops']->path('include/comment_new.php');
}
