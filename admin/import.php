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

use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/admin_header.php';

$op = Request::getString('op', Request::getString('op', 'none', 'GET'), 'POST');

switch ($op) {
    case 'importExecute':
        $importfile      = Request::getString('importfile', 'nonselected', 'POST');
        $importfile_path = $GLOBALS['xoops']->path('modules/' . $helper->getModule()->dirname() . '/admin/import/' . $importfile . '.php');
        require_once $importfile_path;
        break;

    case 'default':
    default:
        $importfile = 'none';

        Publisher\Utility::cpHeader();
        //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);

        Publisher\Utility::openCollapsableBar('import', 'importicon', _AM_PUBLISHER_IMPORT_TITLE, _AM_PUBLISHER_IMPORT_INFO);

        xoops_load('XoopsFormLoader');
        /* @var  $moduleHandler XoopsModuleHandler */
        $moduleHandler = xoops_getHandler('module');

        // WF-Section
        /*$wfs_version = 0;
        $moduleObj = $moduleHandler->getByDirname('wfsection');
        if ($moduleObj) {
        $from_module_version = round($moduleObj->getVar('version') / 100, 2);
        if (($from_module_version == 1.5) || $from_module_version == 1.04 || $from_module_version == 1.01 || $from_module_version == 2.07 || $from_module_version == 2.06) {
        $importfile_select_array["wfsection"] = "WF-Section " . $from_module_version;
        $wfs_version = $from_module_version;
        }
        } */

        // News
        $news_version = 0;
        $moduleObj    = $moduleHandler->getByDirname('news');
        if ($moduleObj) {
            $from_module_version = round($moduleObj->getVar('version') / 100, 2);
            if ($from_module_version >= 1.1) {
                $importfile_select_array['news'] = 'News ' . $from_module_version;
                $news_version                    = $from_module_version;
            }
        }

        // xNews
        $xnews_version = 0;
        $moduleObj     = $moduleHandler->getByDirname('xnews');
        if ($moduleObj) {
            $from_module_version = round($moduleObj->getVar('version') / 100, 2);
            if ($from_module_version >= 1.1) {
                $importfile_select_array['xnews'] = 'xNews ' . $from_module_version;
                $xnews_version                    = $from_module_version;
            }
        }

        // AMS
        $ams_version = 0;
        $moduleObj   = $moduleHandler->getByDirname('AMS');
        if ($moduleObj) {
            $from_module_version = round($moduleObj->getVar('version') / 100, 2);
            if ($from_module_version >= 1.1) {
                $importfile_select_array['ams'] = 'AMS ' . $from_module_version;
                $ams_version                    = $from_module_version;
            }
        }

        // Smartsection
        $smartsection_version = 0;
        $moduleObj            = $moduleHandler->getByDirname('smartsection');
        if ($moduleObj) {
            $from_module_version = round($moduleObj->getVar('version') / 100, 2);
            if ($from_module_version >= 1.1) {
                $importfile_select_array['smartsection'] = 'Smartsection ' . $from_module_version;
                $smartsection_version                    = $from_module_version;
            }
        }

        // C-Jay Content
        $cjaycontent_version = 0;
        $moduleObj           = $moduleHandler->getByDirname('cjaycontent');
        if ($moduleObj) {
            $from_module_version = round($moduleObj->getVar('version') / 100, 2);
            if ($from_module_version >= 1.1) {
                $importfile_select_array['cjaycontent'] = 'C-Jay Content ' . $from_module_version;
                $cjaycontent_version                    = $from_module_version;
            }
        }

        // FmContent
        $fmcontent_version = 0;
        $moduleObj         = $moduleHandler->getByDirname('fmcontent');
        if ($moduleObj) {
            $from_module_version = round($moduleObj->getVar('version') / 100, 2);
            if ($from_module_version >= 1.1) {
                $importfile_select_array['fmcontent'] = 'FmContent ' . $from_module_version;
                $fmcontent_version                    = $from_module_version;
            }
        }

        //  XF-Section
        /*$xfs_version = 0;
        $moduleObj = $moduleHandler->getByDirname('xfsection');
        If ($moduleObj) {
        $from_module_version = round($moduleObj->getVar('version') / 100, 2);
        if ($from_module_version > 1.00) {
        $importfile_select_array["xfsection"] = "XF-Section " . $from_module_version;
        $xfs_version = $from_module_version;
        }
        } */

        if (isset($importfile_select_array) && count($importfile_select_array) > 0) {
            $sform = new \XoopsThemeForm(_AM_PUBLISHER_IMPORT_SELECTION, 'op', xoops_getenv('PHP_SELF'), 'post', true);
            $sform->setExtra('enctype="multipart/form-data"');

            // Partners to import
            $importfile_select = new \XoopsFormSelect('', 'importfile', $importfile);
            $importfile_select->addOptionArray($importfile_select_array);
            $importfile_tray = new \XoopsFormElementTray(_AM_PUBLISHER_IMPORT_SELECT_FILE, '&nbsp;');
            $importfile_tray->addElement($importfile_select);
            $importfile_tray->setDescription(_AM_PUBLISHER_IMPORT_SELECT_FILE_DSC);
            $sform->addElement($importfile_tray);

            // Buttons
            $button_tray = new \XoopsFormElementTray('', '');
            $hidden      = new \XoopsFormHidden('op', 'importExecute');
            $button_tray->addElement($hidden);

            $butt_import = new \XoopsFormButton('', '', _AM_PUBLISHER_IMPORT, 'submit');
            $butt_import->setExtra('onclick="this.form.elements.op.value=\'importExecute\'"');
            $button_tray->addElement($butt_import);

            $butt_cancel = new \XoopsFormButton('', '', _AM_PUBLISHER_CANCEL, 'button');
            $butt_cancel->setExtra('onclick="history.go(-1)"');
            $button_tray->addElement($butt_cancel);

            $sform->addElement($button_tray);
            /*$sform->addElement(new \XoopsFormHidden('xfs_version', $xfs_version));
             $sform->addElement(new \XoopsFormHidden('wfs_version', $wfs_version));*/
            $sform->addElement(new \XoopsFormHidden('news_version', $news_version));
            $sform->addElement(new \XoopsFormHidden('xnews_version', $xnews_version));
            $sform->addElement(new \XoopsFormHidden('ams_version', $ams_version));
            $sform->addElement(new \XoopsFormHidden('cjaycontent_version', $cjaycontent_version));
            $sform->addElement(new \XoopsFormHidden('smartsection_version', $smartsection_version));
            $sform->display();
            unset($hidden);
        } else {
            echo "<span style='color: #567; margin: 3px 0 12px 0; font-weight: bold; font-size: small; display: block;'>" . _AM_PUBLISHER_IMPORT_NO_MODULE . '</span>';
        }

        // End of collapsable bar

        Publisher\Utility::closeCollapsableBar('import', 'importicon');

        break;
}

//xoops_cp_footer();
require_once __DIR__ . '/admin_footer.php';
