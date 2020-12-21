<?php

declare(strict_types=1);
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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 */

use Xmf\Request;
use XoopsModules\Publisher\{Cloner,
    Utility
};

require_once __DIR__ . '/admin_header.php';

Utility::cpHeader();
//publisher_adminMenu(-1, _AM_PUBLISHER_CLONE);
Utility::openCollapsableBar('clone', 'cloneicon', _AM_PUBLISHER_CLONE, _AM_PUBLISHER_CLONE_DSC);

if ('submit' === Request::getString('op', '', 'POST')) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('clone.php', 3, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
    }

    //    $clone = $_POST['clone'];
    $clone = Request::getString('clone', '', 'POST');

    //check if name is valid
    if (empty($clone) || preg_match('/[^a-zA-Z0-9\_\-]/', $clone)) {
        redirect_header('clone.php', 3, sprintf(_AM_PUBLISHER_CLONE_INVALIDNAME, $clone));
    }

    // Check wether the cloned module exists or not
    if ($clone && is_dir($GLOBALS['xoops']->path('modules/' . $clone))) {
        redirect_header('clone.php', 3, sprintf(_AM_PUBLISHER_CLONE_EXISTS, $clone));
    }

    $patterns = [
        mb_strtolower(PUBLISHER_DIRNAME)          => mb_strtolower($clone),
        mb_strtoupper(PUBLISHER_DIRNAME)          => mb_strtoupper($clone),
        ucfirst(mb_strtolower(PUBLISHER_DIRNAME)) => ucfirst(mb_strtolower($clone)),
    ];

    $patKeys   = array_keys($patterns);
    $patValues = array_values($patterns);
    Cloner::cloneFileFolder(PUBLISHER_ROOT_PATH);
    $logocreated = Cloner::createLogo(mb_strtolower($clone));

    $msg = '';
    if (is_dir($GLOBALS['xoops']->path('modules/' . mb_strtolower($clone)))) {
        $msg .= sprintf(_AM_PUBLISHER_CLONE_CONGRAT, "<a href='" . XOOPS_URL . "/modules/system/admin.php?fct=modulesadmin'>" . ucfirst(mb_strtolower($clone)) . '</a>') . "<br>\n";
        if (!$logocreated) {
            $msg .= _AM_PUBLISHER_CLONE_IMAGEFAIL;
        }
    } else {
        $msg .= _AM_PUBLISHER_CLONE_FAIL;
    }
    echo $msg;
} else {
    require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
    $form  = new \XoopsThemeForm(sprintf(_AM_PUBLISHER_CLONE_TITLE, $helper->getModule()->getVar('name', 'E')), 'clone', 'clone.php', 'post', true);
    $clone = new \XoopsFormText(_AM_PUBLISHER_CLONE_NAME, 'clone', 20, 20, '');
    $clone->setDescription(_AM_PUBLISHER_CLONE_NAME_DSC);
    $form->addElement($clone, true);
    $form->addElement(new \XoopsFormHidden('op', 'submit'));
    $form->addElement(new \XoopsFormButton('', '', _SUBMIT, 'submit'));
    $form->display();
}

// End of collapsable bar
Utility::closeCollapsableBar('clone', 'cloneicon');

require_once __DIR__ . '/admin_footer.php';

// work around for PHP < 5.0.x
/*
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data, $file_append = false)
    {
        if ($fp == fopen($filename, (!$file_append ? 'w+' : 'a+'))) {
            fwrite($fp, $data);
            fclose($fp);
        }
    }
}
*/
