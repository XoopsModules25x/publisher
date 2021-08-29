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
 * Publisher
 *
 * @copyright    The XOOPS Project (https://xoops.org)
 * @license      GNU GPL (https://www.gnu.org/licenses/gpl-2.0.html/)
 * @since        1.0
 * @author       Mage, Mamba
 */

use Xmf\Module\Admin;
use Xmf\Request;
use Xmf\Yaml;
use XoopsModules\Publisher\{Common,
    Common\Configurator,
    Common\TestdataButtons,
    Helper,
    Utility
};

require_once __DIR__ . '/admin_header.php';

xoops_cp_header();
$adminObject  = Admin::getInstance();
$utility      = new Utility();
$configurator = new Configurator();
$helper       = Helper::getInstance();
$helper->loadLanguage('main');
$helper->loadLanguage('admin');

/*
foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
    Utility::createFolder($uploadFolders[$i]);
    $adminObject->addConfigBoxLine($uploadFolders[$i], 'folder');
    //    $adminObject->addConfigBoxLine(array($folder[$i], '777'), 'chmod');
}

//copy blank.png files, if needed
$file = PUBLISHER_ROOT_PATH . '/assets/images/blank.png';
foreach (array_keys($copyFiles) as $i) {
    $dest = $copyFiles[$i] . '/blank.png';
    Utility::copyFile($file, $dest);
}
*/

if (is_file(XOOPS_ROOT_PATH . '/class/libraries/vendor/tecnickcom/tcpdf/tcpdf.php')) {
    $adminObject->addConfigBoxLine('<span style="color:green;"><img src="' . $pathIcon16 . '/1.png" alt="!">' . _MD_PUBLISHER_PDF . '</span>', 'default');
} else {
    $adminObject->addConfigBoxLine('<span style="color:#ff0000;"><img src="' . $pathIcon16 . '/0.png" alt="!">' . _MD_PUBLISHER_ERROR_NO_PDF . '</span>', 'default');
}

$modStats    = [];
$moduleStats = $utility::getModuleStats($configurator);

$adminObject->addInfoBox(constant('CO_' . $moduleDirNameUpper . '_' . 'STATS_SUMMARY'));
if (is_array($moduleStats)  && count($moduleStats) > 0) {
    foreach ($moduleStats as $key => $value) {
        switch ($key) {
            case 'totalcategories':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTALCAT));
                break;
            case 'totalitems':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_ITEMS));
                break;
            case 'totaloffline':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTAL_OFFLINE));
                break;
            case 'totalpublished':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTALPUBLISHED));
                break;
            case 'totalrejected':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_REJECTED));
                break;
            case 'totalsubmitted':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTALSUBMITTED));
                break;
        }
    }
}

$adminObject->displayNavigation(basename(__FILE__));

//check for latest release
$newRelease = $utility::checkVerModule($helper);
if (!empty($newRelease)) {
    $adminObject->addItemButton($newRelease[0], $newRelease[1], 'download', 'style="color : Red"');
}

//------------- Test Data Buttons ----------------------------
if ($helper->getConfig('displaySampleButton')) {
    TestdataButtons::loadButtonConfig($adminObject);
    $adminObject->displayButton('left', '');
}
$op = Request::getString('op', 0, 'GET');
switch ($op) {
    case 'hide_buttons':
        TestdataButtons::hideButtons();
        break;
    case 'show_buttons':
        TestdataButtons::showButtons();
        break;
}
//------------- End Test Data Buttons ----------------------------

$adminObject->displayIndex();
echo $utility::getServerStats();

//codeDump(__FILE__);
require_once __DIR__ . '/admin_footer.php';
