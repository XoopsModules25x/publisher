<?php

declare(strict_types=1);

/** @return object */

use Xmf\Module\Admin;
use XoopsModules\Publisher\Helper;

$pathIcon16    = Admin::iconUrl('', 16);
$moduleDirName = basename(dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);
$helper = Helper::getInstance();
$helper->loadLanguage('admin');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$print = constant('CO_' . $moduleDirNameUpper . '_' . 'PRINT');
$pdf = constant('CO_' . $moduleDirNameUpper . '_' . 'PDF');

return (object)[
    'edit'    => "<img src='" . $pathIcon16 . "/edit.png'  alt='" . _EDIT . "' title='" . _EDIT . "' align='middle'>",
    'delete'  => "<img src='" . $pathIcon16 . "/delete.png' alt='" . _DELETE . "' title='" . _DELETE . "' align='middle'>",
    'clone'   => "<img src='" . $pathIcon16 . "/editcopy.png' alt='" . _CLONE . "' title='" . _CLONE . "' align='middle'>",
    'preview' => "<img src='" . $pathIcon16 . "/view.png' alt='" . _PREVIEW . "' title='" . _PREVIEW . "' align='middle'>",
    'print'   => "<img src='" . $pathIcon16 . "/printer.png' alt='" . $print . "' title='" . $print . "' align='middle'>",
    'pdf'     => "<img src='" . $pathIcon16 . "/pdf.png' alt='" . $pdf . "' title='" . $pdf . "' align='middle'>",
    'add'     => "<img src='" . $pathIcon16 . "/add.png' alt='" . _ADD . "' title='" . _ADD . "' align='middle'>",
    '0'       => "<img src='" . $pathIcon16 . "/0.png' alt='" . 0 . "' title='" . 0 . "' align='middle'>",
    '1'       => "<img src='" . $pathIcon16 . "/1.png' alt='" . 1 . "' title='" . 1 . "' align='middle'>",

    //Publisher
    'rejectededit' => "<img src='" . $pathIcon16 . "/edit.png'  alt='" . _AM_PUBLISHER_REJECTED_EDIT . "' title='" . _AM_PUBLISHER_REJECTED_EDIT . "' align='middle'>",
    'online'       => "<img src='" . $pathModIcon32 . "/on.png' alt='" . _AM_PUBLISHER_ICO_ONLINE . "' title='" . _AM_PUBLISHER_ICO_ONLINE . "' align='middle'>",
    'offline'      => "<img src='" . $pathModIcon32 . "/off.png' alt='" . _AM_PUBLISHER_ICO_OFFLINE . "' title='" . _AM_PUBLISHER_ICO_OFFLINE . "' align='middle'>",
    'moderate'     => "<img src='" . $pathModIcon32 . "/approve.gif' alt='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' title='" . _AM_PUBLISHER_SUBMISSION_MODERATE . "' align='middle'>",
    'mail'         => "<img src='" . $pathModIcon32 . "/friend.gif' alt='" . _CO_PUBLISHER_MAIL . "' title='" . _CO_PUBLISHER_MAIL . "' align='middle'>",
];

