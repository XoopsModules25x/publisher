<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          luciorota <lucio.rota@gmail.com>
 * @version         $Id: install.php 11345 2013-04-03 22:35:51Z luciorota $
 *
 * @param $xoopsModule
 *
 * @return bool
 */

function xoops_module_pre_install_publisher(&$xoopsModule)
{
    // NOP
    return true;
}

/**
 * @param $xoopsModule
 *
 * @return bool|string
 */
function xoops_module_install_publisher(&$xoopsModule)
{
    xoops_loadLanguage('modinfo', $xoopsModule->getVar('dirname'));
    include_once XOOPS_ROOT_PATH . "/modules/" . $xoopsModule->getVar('dirname') . "/include/functions.php";

    $ret = true;
    $msg = '';
    // Create content directory
    $dir = XOOPS_ROOT_PATH . "/uploads/" . $xoopsModule->getVar('dirname') . "/content";
    if (!publisher_mkdir($dir))
        $msg.= sprintf(_MI_AJAXFM_WARNING_DIRNOTCREATED, $dir);
    if (empty($msg))
        return $ret;
    else
        return $msg;
}
