<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Publisher\{Constants,
    Helper,
    TrelloManagement
};

/** @var Admin $adminObject */

$GLOBALS['xoopsOption']['template_main'] = 'publisher_trello.tpl';
require_once __DIR__ . '/admin_header.php';

xoops_cp_header();

$xoopsDb = \XoopsDatabaseFactory::getDatabaseConnection();

$adminObject->displayNavigation(basename(__FILE__));
$helper = Helper::getInstance();

$statusArray = [
    Constants::PUBLISHER_STATUS_SUBMITTED => \_CO_PUBLISHER_SUBMITTED,
    Constants::PUBLISHER_STATUS_PUBLISHED => \_CO_PUBLISHER_PUBLISHED,
    Constants::PUBLISHER_STATUS_OFFLINE   => \_CO_PUBLISHER_OFFLINE,
    Constants::PUBLISHER_STATUS_REJECTED  => \_CO_PUBLISHER_REJECTED,
];

$trelloManagement = new TrelloManagement($xoopsDb);
$statusResult     = $trelloManagement->getAllStatus();

/** @var \XoopsTpl $xoopsTpl */
$xoopsTpl->assign('statusResult', $statusResult);
$xoopsTpl->assign('statusArray', $statusArray);
$xoopsTpl->assign('publisher_url', $helper->url());
$xoopsTpl->assign('mod_url', $helper->url());

require_once __DIR__ . '/admin_footer.php';
