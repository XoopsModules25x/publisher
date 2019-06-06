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

use Xmf\Yaml;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Common;

require_once __DIR__ . '/admin_header.php';

xoops_cp_header();
/** @var \XoopsModules\Publisher\Helper $helper */
$helper = \XoopsModules\Publisher\Helper::getInstance();
$helper->loadLanguage('main');
$helper->loadLanguage('admin');
$adminObject  = \Xmf\Module\Admin::getInstance();
$utility      = new Publisher\Utility();
$configurator = new Publisher\Common\Configurator();

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
    $adminObject->addConfigBoxLine('<span style="color:#ff0000;"><img src="' . $pathIcon16 . '/0.png" alt="!">' . _MD_PUBLISHER_ERROR_NO_PDF . '</span>', 'default');
}

$modStats    = [];
$moduleStats = $utility::getModuleStats($configurator, $modStats);

$adminObject->addInfoBox(constant('CO_' . $moduleDirNameUpper . '_' . 'STATS_SUMMARY'));
if ($moduleStats && is_array($moduleStats)) {
    foreach ($moduleStats as $key => $value) {
        switch ($key) {
            case 'totalcategories':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf( $ret . ' ' . _AM_PUBLISHER_TOTALCAT ));
                break;
            case 'totalitems':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_ITEMS ));
                break;
            case 'totaloffline':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTAL_OFFLINE ));
                break;
            case 'totalpublished':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTALPUBLISHED ));
                break;
            case 'totalrejected':
                $ret = '<span style=\'font-weight: bold; color: red;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_REJECTED ));
                break;
            case 'totalsubmitted':
                $ret = '<span style=\'font-weight: bold; color: green;\'>' . $value . '</span>';
                $adminObject->addInfoBoxLine(sprintf($ret . ' ' . _AM_PUBLISHER_TOTALSUBMITTED ));
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

//------------- Test Data ----------------------------

if ($helper->getConfig('displaySampleButton')) {
    $yamlFile            = dirname(__DIR__) . '/config/admin.yml';
    $config              = loadAdminConfig($yamlFile);
    $displaySampleButton = $config['displaySampleButton'];

    if (1 == $displaySampleButton) {
        xoops_loadLanguage('admin/modulesadmin', 'system');
        require __DIR__ . '/../testdata/index.php';

        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'ADD_SAMPLEDATA'), './../testdata/index.php?op=load', 'add');
        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'SAVE_SAMPLEDATA'), './../testdata/index.php?op=save', 'add');
        //    $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'EXPORT_SCHEMA'), './../testdata/index.php?op=exportschema', 'add');
        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'HIDE_SAMPLEDATA_BUTTONS'), '?op=hide_buttons', 'delete');
    } else {
        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLEDATA_BUTTONS'), '?op=show_buttons', 'add');
        $displaySampleButton = $config['displaySampleButton'];
    }
    $adminObject->displayButton('left', '');
}

//------------- End Test Data ----------------------------

$adminObject->displayIndex();

/**
 * @param $yamlFile
 * @return array|bool
 */
function loadAdminConfig($yamlFile)
{
    $config = Yaml::loadWrapped($yamlFile); // work with phpmyadmin YAML dumps
    return $config;
}

/**
 * @param $yamlFile
 */
function hideButtons($yamlFile)
{
    $app['displaySampleButton'] = 0;
    Yaml::save($app, $yamlFile);
    redirect_header('index.php', 0, '');
}

/**
 * @param $yamlFile
 */
function showButtons($yamlFile)
{
    $app['displaySampleButton'] = 1;
    Yaml::save($app, $yamlFile);
    redirect_header('index.php', 0, '');
}

$op = \Xmf\Request::getString('op', 0, 'GET');

switch ($op) {
    case 'hide_buttons':
        hideButtons($yamlFile);
        break;
    case 'show_buttons':
        showButtons($yamlFile);
        break;
}

echo $utility::getServerStats();

require_once __DIR__ . '/admin_footer.php';
