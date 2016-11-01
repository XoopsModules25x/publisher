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
 */

include_once __DIR__ . '/admin_header.php';
include_once dirname(__DIR__) . '/class/utilities.php';

xoops_cp_header();
xoops_loadLanguage('main', PUBLISHER_DIRNAME);
$indexAdmin = new ModuleAdmin();

foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
    PublisherUtilities::createFolder($uploadFolders[$i]);
    $indexAdmin->addConfigBoxLine($uploadFolders[$i], 'folder');
    //    $indexAdmin->addConfigBoxLine(array($folder[$i], '777'), 'chmod');
}

//copy blank.png files, if needed
$file = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
foreach (array_keys($copyFiles) as $i) {
    $dest = $copyFiles[$i] . '/blank.png';
    PublisherUtilities::copyFile($file, $dest);
}

if (!is_file(XOOPS_ROOT_PATH . '/class/libraries/vendor/tecnickcom/tcpdf/tcpdf.php')) {
    $indexAdmin->addConfigBoxLine('<span style="color:red;"><img src="'.XOOPS_URL.'/Frameworks/moduleclasses/icons/16/0.png" alt="!" />' . _MD_PUBLISHER_ERROR_NO_PDF . '</span>', 'default');
}

echo $indexAdmin->addNavigation(basename(__FILE__));
echo $indexAdmin->renderIndex();

include_once __DIR__ . '/admin_footer.php';
