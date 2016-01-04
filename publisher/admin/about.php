<?php
/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Publisher
 *
 * @copyright    The XOOPS Project (http://www.xoops.org)
 * @license      GNU GPL (http://www.gnu.org/licenses/gpl-2.0.html/)
 * @package      Publisher
 * @since        1.0
 * @author       Mage, Mamba
 * @version      $Id: about.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';

xoops_cp_header();

$aboutAdmin = new ModuleAdmin();

echo $aboutAdmin->addNavigation('about.php');
echo $aboutAdmin->renderAbout('6KJ7RW5DR3VTJ', false);

//    if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
//        echo 'I am at least PHP version 5.4.0, my version: ' . PHP_VERSION . "\n";
//    } else {
//        echo 'I am using PHP lower than 5.4, my version: ' . PHP_VERSION . "\n";
//    }

include_once __DIR__ . '/admin_footer.php';
