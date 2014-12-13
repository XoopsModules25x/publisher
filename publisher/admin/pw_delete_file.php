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
 * @version         $Id: pw_delete_file.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';

if (isset($_POST["op"]) && ($_POST["op"] == "delfileok")) {
    $dir = publisher_getUploadDir(true, 'content');
    @unlink($dir . '/' . $_POST["address"]);
    redirect_header($_POST['backto'], 2, _AM_PUBLISHER_FDELETED);
} else {
    xoops_cp_header();
    xoops_confirm(array('backto' => $_POST['backto'], 'address' => $_POST["address"], 'op' => 'delfileok'), 'pw_delete_file.php', _AM_PUBLISHER_RUSUREDELF, _YES);
    xoops_cp_footer();
}
