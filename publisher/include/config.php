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
 * animal module for xoops
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         GPL 2.0 or later
 * @package         Publisher
 * @subpackage      Config
 * @since           1.03
 * @author          XOOPS Development Team - ( http://xoops.org )
 * @version         $Id: const_entete.php 9860 2012-07-13 10:41:41Z beckmi $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");
include_once __DIR__ . '/common.php';

$moduleDirName =  basename(dirname(__DIR__));
$uploadFolders = array(
    PUBLISHER_UPLOAD_PATH,
    PUBLISHER_UPLOAD_PATH . '/content',
    PUBLISHER_UPLOAD_PATH . '/images',
    PUBLISHER_UPLOAD_PATH . '/images/category',
    PUBLISHER_UPLOAD_PATH . '/images/thumbnails');

$copyFiles = array(
    PUBLISHER_UPLOAD_PATH . '/images/category',
    PUBLISHER_UPLOAD_PATH . '/images/thumbnails');
