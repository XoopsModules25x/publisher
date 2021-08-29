<?php

declare(strict_types=1);

/** @return object */

$moduleDirName = \basename(\dirname(__DIR__));
//    $moduleDirNameUpper = mb_strtoupper($moduleDirName);

return (object)[
    'dirname'    => $moduleDirName,
    'admin'      => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/admin',
    'modPath'    => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName,
    'modUrl'     => XOOPS_URL . '/modules/' . $moduleDirName,
    'uploadUrl'  => XOOPS_UPLOAD_URL . '/' . $moduleDirName,
    'uploadPath' => XOOPS_UPLOAD_PATH . '/' . $moduleDirName,

    'uploadPathCategory'    => XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/category',
    'uploadPathScreenshots' => XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/screenshots',
];

