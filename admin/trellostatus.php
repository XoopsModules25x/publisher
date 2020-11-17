<?php declare(strict_types=1);

use XoopsModules\Publisher\TrelloManagement;

require_once __DIR__ . '/admin_header.php';

$xoopsDb = \XoopsDatabaseFactory::getDatabaseConnection();

$trelloManagement = new TrelloManagement($xoopsDb);

$statusId = $_GET['statusId'];
$itemId = $_GET['itemId'];

$result = $trelloManagement->editTaskStatus($statusId, $itemId);
