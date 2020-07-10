<?php declare(strict_types=1);

use XoopsModules\Publisher\TrelloManagement;

$trelloManagement = new TrelloManagement();

$statusId = $_GET['status'];
$itemId = $_GET['itemid'];

$result = $trelloManagement->editTaskStatus($statusId, $itemId);
