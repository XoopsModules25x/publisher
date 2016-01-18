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
 * Publisher
 *
 * @copyright    The XOOPS Project (http://www.xoops.org)
 * @license      GNU GPL (http://www.gnu.org/licenses/gpl-2.0.html/)
 * @package      Publisher
 * @since        1.0
 * @author       Mage, Mamba
 * @version      $Id: index.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once __DIR__ . '/admin_header.php';
include_once dirname(__DIR__) . '/class/utilities.php';

xoops_cp_header();

$indexAdmin = new ModuleAdmin();

foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
    PublisherUtilities::createFolder($uploadFolders[$i]);
    $indexAdmin->addConfigBoxLine($uploadFolders[$i], 'folder');
    //    $indexAdmin->addConfigBoxLine(array($folder[$i], '777'), 'chmod');
}

echo $indexAdmin->addNavigation('index.php');
echo $indexAdmin->renderIndex();

include_once __DIR__ . '/admin_footer.php';
