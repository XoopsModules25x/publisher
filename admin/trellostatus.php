<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Publisher\TrelloManagement;

require_once __DIR__ . '/admin_header.php';

$xoopsDb = \XoopsDatabaseFactory::getDatabaseConnection();

$trelloManagement = new TrelloManagement($xoopsDb);

$statusId = Request::getInt('statusId', 0, 'GET');
$itemId   = Request::getInt('itemId', 0, 'GET');

$result = $trelloManagement->editTaskStatus($statusId, $itemId);
