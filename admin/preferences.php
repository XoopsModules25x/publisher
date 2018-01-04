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
 * @author          Kazumi Ono (AKA onokazu)
 */

use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/admin_header.php';

$helper      = Publisher\Helper::getInstance();

$module  = $helper->getModule();
$modId     = $module->mid();
$modname = $module->name();
$dirName = $helper->getDirname();

$moduleHandler = xoops_getHandler('module');
$xoopsModule0        = $moduleHandler->getByDirname(basename(dirname(__DIR__)));
global $xoopsModule;

xoops_loadLanguage('admin', 'system');
xoops_loadLanguage('admin/preferences', 'system');

$op = 'showmod';
if (isset($_POST)) {
    foreach ($_POST as $k => $v) {
        ${$k} = $v;
    }
}
unset($k, $v);

$op = Request::getString('op', $op, 'GET');

$configcat = Request::getString('configcat', '', 'GET');

if ('showmod' === $op) {
    $configHandler = xoops_getHandler('config');

    $config = $configHandler->getConfigs(new \Criteria('conf_modid', $modId));
    $count  = count($config);
    if ($count < 1) {
        redirect_header($module->getInfo('adminindex'), 1);
    }

    $xv_configs  = $module->getInfo('config');
    $config_cats = $module->getInfo('configcat');

    if (!array_key_exists('others', $config_cats)) {
        $config_cats['others'] = [
            'name'        => _MI_PUBLISHER_CONFCAT_OTHERS,
            'description' => _MI_PUBLISHER_CONFCAT_OTHERS_DSC
        ];
    }
    $cat_others_used = false;

    xoops_loadLanguage('modinfo', $module->getVar('dirname'));

    if (1 == $module->getVar('hascomments')) {
        xoops_loadLanguage('comment');
    }

    if (1 == $module->getVar('hasnotification')) {
        xoops_loadLanguage('notification');
    }

    xoops_load('XoopsFormLoader');

    foreach ($config_cats as $formCat => $info) {
        $$formCat = new \XoopsThemeForm($info['name'], 'pref_form_' . $formCat, 'preferences.php', 'post', true);
    }
    unset($formCat, $info);

    for ($i = 0; $i < $count; ++$i) {
        foreach ($xv_configs as $xv_config) {
            if ($config[$i]->getVar('conf_name') == $xv_config['name']) {
                break;
            }
        }

        $formCat = @$xv_config['category'];
        $formCat = isset($xv_config['category']) ? $xv_config['category'] : '';
        unset($xv_config);

        if (!array_key_exists($formCat, $config_cats)) {
            $formCat         = 'others';
            $cat_others_used = true;
        }

        $title = (!defined($config[$i]->getVar('conf_desc'))
                  || '' == constant($config[$i]->getVar('conf_desc'))) ? constant($config[$i]->getVar('conf_title')) : constant($config[$i]->getVar('conf_title')) . '<br><br><span style="font-weight:normal;">' . constant($config[$i]->getVar('conf_desc')) . '</span>';
        switch ($config[$i]->getVar('conf_formtype')) {
            case 'textarea':
                $myts = \MyTextSanitizer::getInstance();
                if ('array' === $config[$i]->getVar('conf_valuetype')) {
                    // this is exceptional.. only when value type is arrayneed a smarter way for this
                    $ele = ('' != $config[$i]->getVar('conf_value')) ? new \XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), $myts->htmlspecialchars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50) : new \XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), '', 5, 50);
                } else {
                    $ele = new \XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), $myts->htmlspecialchars($config[$i]->getConfValueForOutput()), 5, 50);
                }
                break;
            case 'select':
                $ele     = new \XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
                $options = $configHandler->getConfigOptions(new \Criteria('conf_id', $config[$i]->getVar('conf_id')));
                $opcount = count($options);
                for ($j = 0; $j < $opcount; ++$j) {
                    $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                    $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                    $ele->addOption($optval, $optkey);
                }
                break;
            case 'select_multi':
                $ele     = new \XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
                $options = $configHandler->getConfigOptions(new \Criteria('conf_id', $config[$i]->getVar('conf_id')));
                $opcount = count($options);
                for ($j = 0; $j < $opcount; ++$j) {
                    $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                    $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                    $ele->addOption($optval, $optkey);
                }
                break;
            case 'yesno':
                $ele = new \XoopsFormRadioYN($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), _YES, _NO);
                break;
            case 'group':
                require_once $GLOBALS['xoops']->path('class/xoopslists.php');
                $ele = new \XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                break;
            case 'group_multi':
                require_once $GLOBALS['xoops']->path('class/xoopslists.php');
                $ele = new \XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                break;
            case 'user':
                require_once $GLOBALS['xoops']->path('class/xoopslists.php');
                $ele = new \XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                break;
            case 'user_multi':
                require_once $GLOBALS['xoops']->path('class/xoopslists.php');
                $ele = new \XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                break;
            case 'password':
                $myts = \MyTextSanitizer::getInstance();
                $ele  = new \XoopsFormPassword($title, $config[$i]->getVar('conf_name'), 50, 255, $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
            case 'color':
                $myts = \MyTextSanitizer::getInstance();
                $ele  = new \XoopsFormColorPicker($title, $config[$i]->getVar('conf_name'), $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
            case 'hidden':
                $myts = \MyTextSanitizer::getInstance();
                $ele  = new \XoopsFormHidden($config[$i]->getVar('conf_name'), $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
            case 'textbox':
            default:
                $myts = \MyTextSanitizer::getInstance();
                $ele  = new \XoopsFormText($title, $config[$i]->getVar('conf_name'), 50, 255, $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
        }
        $hidden = new \XoopsFormHidden('conf_ids[]', $config[$i]->getVar('conf_id'));
        $$formCat->addElement($ele);
        $$formCat->addElement($hidden);
        unset($ele, $hidden);
    }

    Publisher\Utility::cpHeader();
    //publisher_adminMenu(5, _PREFERENCES);
    foreach ($config_cats as $formCat => $info) {
        if ('others' === $formCat && !$cat_others_used) {
            continue;
        }
        $$formCat->addElement(new \XoopsFormHidden('op', 'save'));
        $$formCat->addElement(new \XoopsFormButton('', 'button', _GO, 'submit'));
        Publisher\Utility::openCollapsableBar($formCat . '_table', $formCat . '_icon', $info['name'], $info['description']);
        $$formCat->display();
        Publisher\Utility::closeCollapsableBar($formCat . '_table', $formCat . '_icon');
    }
    unset($formCat, $info);
    xoops_cp_footer();
    exit();
}

if ('save' === $op) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header($module->getInfo('adminindex'), 3, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
    }
    $count         = count($confIds);
    $configHandler = xoops_getHandler('config');
    if ($count > 0) {
        for ($i = 0; $i < $count; ++$i) {
            $config   = $configHandler->getConfig($confIds[$i]);
            $newValue = ${$config->getVar('conf_name')};
            if (is_array($newValue) || $newValue != $config->getVar('conf_value')) {
                $config->setConfValueForInput($newValue);
                $configHandler->insertConfig($config);
            }
            unset($newValue);
        }
    }
    redirect_header('preferences.php', 2, _AM_DBUPDATED);
}
