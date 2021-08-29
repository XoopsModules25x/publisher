<?php

declare(strict_types=1);

/** @return object */

use Xmf\Module\Admin;
use XoopsModules\Publisher\{
    Helper,
    Utility
};

$pathIcon16    = Admin::iconUrl('', '16');
$moduleDirName = \basename(\dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);
$helper = Helper::getInstance();
$helper->loadLanguage('admin');
$pathModIcon16 = $helper->url($helper->getModule()->getInfo('modicons16')) . '/';
$pathModIcon32 = $helper->url($helper->getModule()->getInfo('modicons32')) . '/';

$print = constant('_CO_' . $moduleDirNameUpper . '_' . 'PRINT');
$pdf = constant('_CO_' . $moduleDirNameUpper . '_' . 'PDF');

return [
    'edit'         => Utility::iconSourceTag($pathIcon16, 'edit.png', _EDIT),
    'delete'       => Utility::iconSourceTag($pathIcon16, 'delete.png', _DELETE),
    'clone'        => Utility::iconSourceTag($pathIcon16, 'editcopy.png', _CLONE),
    'preview'      => Utility::iconSourceTag($pathIcon16, 'view.png', _PREVIEW),
    'print'        => Utility::iconSourceTag($pathIcon16, 'printer.png', $print),
    'pdf'          => Utility::iconSourceTag($pathIcon16, 'pdf.png', $pdf),
    'add'          => Utility::iconSourceTag($pathIcon16, 'add.png', _ADD),
    '0'            => Utility::iconSourceTag($pathIcon16, '0.png', 0),
    '1'            => Utility::iconSourceTag($pathIcon16, '1.png', 1),
    //Publisher
    'rejectededit' => Utility::iconSourceTag($pathIcon16, 'edit.png', _AM_PUBLISHER_REJECTED_EDIT),
    'online'       => Utility::iconSourceTag($pathIcon16, 'on.png', _AM_PUBLISHER_ICO_ONLINE),
    'offline'      => Utility::iconSourceTag($pathIcon16, 'off.png', _AM_PUBLISHER_ICO_OFFLINE),
    'moderate'     => Utility::iconSourceTag($pathModIcon16, 'approve.gif', _AM_PUBLISHER_SUBMISSION_MODERATE),
    'mail'         => Utility::iconSourceTag($pathModIcon16, 'friend.gif', _CO_PUBLISHER_MAIL),
];

