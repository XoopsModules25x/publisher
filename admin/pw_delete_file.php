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
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Assert;
use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/admin_header.php';

if ('delfileok' === Request::getString('op', '', 'POST')) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header(XOOPS_URL . '/modules/publisher/admin/item.php', 3, _AM_PUBLISHER_FILE_DELETE_ERROR);
    }

    $dir        = Publisher\Utility::getUploadDir(true, 'content');
    $check_path = realpath($dir);

    $filename  = Request::getString('address', '', 'POST');
    $path_file = realpath($dir . '/' . $filename);
    try {
        Assert::startsWith($path_file, $check_path, _AM_PUBLISHER_FILE_DELETE_ERROR);
    } catch (\InvalidArgumentException $e) {
        // handle the exception
        redirect_header(XOOPS_URL . '/modules/publisher/admin/item.php', 2, $e->getMessage());
    }
    if (file_exists($dir . '/' . $filename)) {
        unlink($dir . '/' . $filename);
    }
    redirect_header(Request::getString('backto', '', 'POST'), 2, _AM_PUBLISHER_FDELETED);
} else {
    xoops_cp_header();
    xoops_confirm(['backto' => Request::getString('backto', '', 'POST'), 'address' => Request::getString('address', '', 'POST'), 'op' => 'delfileok'], 'pw_delete_file.php', _AM_PUBLISHER_RUSUREDELF, _YES);
    xoops_cp_footer();
}
