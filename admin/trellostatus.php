<?php declare(strict_types=1);

use XoopsModules\Publisher\TrelloManagement;

$projectManagement = new TrelloManagement();

$statusId = $_GET['status'];
$itemId = $_GET['itemid'];

$result = $projectManagement->editTaskStatus($statusId, $itemId);
