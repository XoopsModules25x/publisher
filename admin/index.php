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
 * @copyright    The XOOPS Project (https://xoops.org)
 * @license      GNU GPL (http://www.gnu.org/licenses/gpl-2.0.html/)
 * @package      Publisher
 * @since        1.0
 * @author       Mage, Mamba
 */

use XoopsModules\Publisher;
use XoopsModules\Publisher\Common;

require_once __DIR__ . '/admin_header.php';
//require_once dirname(__DIR__) . '/class/Utility.php';

xoops_cp_header();
$helper = Publisher\Helper::getInstance();
$helper->loadLanguage('main');
$adminObject = \Xmf\Module\Admin::getInstance();
$utility = new Publisher\Utility();

/*
foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
    Publisher\Utility::createFolder($uploadFolders[$i]);
    $adminObject->addConfigBoxLine($uploadFolders[$i], 'folder');
    //    $adminObject->addConfigBoxLine(array($folder[$i], '777'), 'chmod');
}

//copy blank.png files, if needed
$file = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
foreach (array_keys($copyFiles) as $i) {
    $dest = $copyFiles[$i] . '/blank.png';
    Publisher\Utility::copyFile($file, $dest);
}
*/

if (!is_file(XOOPS_ROOT_PATH . '/class/libraries/vendor/tecnickcom/tcpdf/tcpdf.php')) {
    $adminObject->addConfigBoxLine('<span style="color:red;"><img src="' . $pathIcon16 . '/0.png" alt="!">' . _MD_PUBLISHER_ERROR_NO_PDF . '</span>', 'default');
}

$adminObject->displayNavigation(basename(__FILE__));

//------------- Test Data ----------------------------

if ($helper->getConfig('displaySampleButton')) {
    xoops_loadLanguage('admin/modulesadmin', 'system');
    require_once __DIR__ . '/../testdata/index.php';

    $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'ADD_SAMPLEDATA'), '__DIR__ . /../../testdata/index.php?op=load', 'add');

    $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'SAVE_SAMPLEDATA'), '__DIR__ . /../../testdata/index.php?op=save', 'add');

    //    $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'EXPORT_SCHEMA'), '__DIR__ . /../../testdata/index.php?op=exportschema', 'add');

    $adminObject->displayButton('left', '');
}

//------------- End Test Data ----------------------------

$adminObject->displayIndex();

echo $utility::getServerStats();

require_once __DIR__ . '/admin_footer.php';
