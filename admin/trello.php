<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Publisher\{
    Constants,
    TrelloManagement
};

$GLOBALS['xoopsOption']['template_main'] = 'publisher_trello.tpl';
require_once __DIR__ . '/admin_header.php';

xoops_cp_header();

$adminObject->displayNavigation(basename(__FILE__));

$statusArray      = [
    Constants::PUBLISHER_STATUS_SUBMITTED => \_CO_PUBLISHER_SUBMITTED,
    Constants::PUBLISHER_STATUS_PUBLISHED => \_CO_PUBLISHER_PUBLISHED,
    Constants::PUBLISHER_STATUS_OFFLINE   => \_CO_PUBLISHER_OFFLINE,
    Constants::PUBLISHER_STATUS_REJECTED  => \_CO_PUBLISHER_REJECTED,
];

$projectName = 'StartTuts';
$trelloManagement = new TrelloManagement();
$statusResult = $trelloManagement->getAllStatus();

foreach ($statusResult as $statusRow) {
//    $itemResult[] = $trelloManagement->getProjectTaskByStatus($statusRow['id'], $projectName);
//    $itemResult[] = $trelloManagement->getProjectTaskByStatus($statusRow['itemid'], $statusRow['itemid']);
}

//$xoopsTpl->assign('taskResult', $itemResult);
$xoopsTpl->assign('statusResult', $statusResult);
$xoopsTpl->assign('itemResult', $statusResult);
$xoopsTpl->assign('statusArray', $statusArray);
$xoopsTpl->assign('publisher_url', PUBLISHER_URL);


require_once __DIR__ . '/admin_footer.php';
