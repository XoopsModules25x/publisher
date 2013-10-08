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
 * @version         $Id: preferences.php 10374 2012-12-12 23:39:48Z trabis $
 */

include_once dirname(__FILE__) . '/admin_header.php';

$module = $publisher->getModule();
$mod = $module->mid();
$modname = $module->name();

xoops_loadLanguage('admin', 'system');
xoops_loadLanguage('admin/preferences', 'system');

$op = 'showmod';
if (isset($_POST)) {
    foreach ($_POST as $k => $v) {
        ${$k} = $v;
    }
}
if (isset($_GET['op'])) {
    $op = trim($_GET['op']);
}

if (isset($_GET['configcat'])) {
    $configcat = $_GET['configcat'];
}

if ($op == 'showmod') {
    $config_handler = xoops_gethandler('config');

    $config = $config_handler->getConfigs(new Criteria('conf_modid', $mod));
    $count = count($config);
    if ($count < 1) {
        redirect_header($module->getInfo('adminindex'), 1);
    }

    $xv_configs = $module->getInfo('config');
    $config_cats = $module->getInfo('configcat');

    if (!in_array('others', array_keys($config_cats))) {
        $config_cats['others'] = array('name' => _MI_PUBLISHER_CONFCAT_OTHERS,
                                       'description' => _MI_PUBLISHER_CONFCAT_OTHERS_DSC);
    }
    $cat_others_used = false;

    xoops_loadLanguage('modinfo', $module->getVar('dirname'));

    if ($module->getVar('hascomments') == 1) {
        xoops_loadLanguage('comment');
    }

    if ($module->getVar('hasnotification') == 1) {
        xoops_loadLanguage('notification');
    }

    xoops_load('XoopsFormLoader');

    foreach ($config_cats as $form_cat => $info) {
        $$form_cat = new XoopsThemeForm($info['name'], 'pref_form_' . $form_cat, 'preferences.php', 'post', true);
    }

    for ($i = 0; $i < $count; $i++) {

        foreach ($xv_configs as $xv_config) {
            if ($config[$i]->getVar('conf_name') == $xv_config['name']) break;
        }
        $form_cat = @$xv_config['category'];

        if (!in_array($form_cat, array_keys($config_cats))) {
            $form_cat = 'others';
            $cat_others_used = true;
        }

        $title = (!defined($config[$i]->getVar('conf_desc')) || constant($config[$i]->getVar('conf_desc')) == '') ? constant($config[$i]->getVar('conf_title')) : constant($config[$i]->getVar('conf_title')) . '<br /><br /><span style="font-weight:normal;">' . constant($config[$i]->getVar('conf_desc')) . '</span>';
        switch ($config[$i]->getVar('conf_formtype')) {
            case 'textarea':
                $myts = MyTextSanitizer::getInstance();
                if ($config[$i]->getVar('conf_valuetype') == 'array') {
                    // this is exceptional.. only when value type is arrayneed a smarter way for this
                    $ele = ($config[$i]->getVar('conf_value') != '') ? new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), $myts->htmlspecialchars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50) : new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), '', 5, 50);
                } else {
                    $ele = new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), $myts->htmlspecialchars($config[$i]->getConfValueForOutput()), 5, 50);
                }
                break;
            case 'select':
                $ele = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
                $options = $config_handler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
                $opcount = count($options);
                for ($j = 0; $j < $opcount; $j++) {
                    $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                    $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                    $ele->addOption($optval, $optkey);
                }
                break;
            case 'select_multi':
                $ele = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
                $options = $config_handler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
                $opcount = count($options);
                for ($j = 0; $j < $opcount; $j++) {
                    $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                    $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                    $ele->addOption($optval, $optkey);
                }
                break;
            case 'yesno':
                $ele = new XoopsFormRadioYN($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), _YES, _NO);
                break;
            case 'group':
                include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                break;
            case 'group_multi':
                include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                break;
            case 'user':
                include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                break;
            case 'user_multi':
                include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                break;
            case 'password':
                $myts = MyTextSanitizer::getInstance();
                $ele = new XoopsFormPassword($title, $config[$i]->getVar('conf_name'), 50, 255, $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
            case 'color':
                $myts = MyTextSanitizer::getInstance();
                $ele = new XoopsFormColorPicker($title, $config[$i]->getVar('conf_name'), $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
            case 'hidden':
                $myts = MyTextSanitizer::getInstance();
                $ele = new XoopsFormHidden($config[$i]->getVar('conf_name'), $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
            case 'textbox':
            default:
                $myts = MyTextSanitizer::getInstance();
                $ele = new XoopsFormText($title, $config[$i]->getVar('conf_name'), 50, 255, $myts->htmlspecialchars($config[$i]->getConfValueForOutput()));
                break;
        }
        $hidden = new XoopsFormHidden('conf_ids[]', $config[$i]->getVar('conf_id'));
        $$form_cat->addElement($ele);
        $$form_cat->addElement($hidden);
        unset($ele);
        unset($hidden);
    }

    publisher_cpHeader();
    //publisher_adminMenu(5, _PREFERENCES);
    foreach ($config_cats as $form_cat => $info) {
        if ($form_cat == 'others' && !$cat_others_used) continue;
        $$form_cat->addElement(new XoopsFormHidden('op', 'save'));
        $$form_cat->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        publisher_openCollapsableBar($form_cat . '_table', $form_cat . '_icon', $info['name'], $info['description']);
        $$form_cat->display();
        publisher_closeCollapsableBar($form_cat . '_table', $form_cat . '_icon');
    }
    xoops_cp_footer();
    exit();
}

if ($op == 'save') {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header($module->getInfo('adminindex'), 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
    }
    $count = count($conf_ids);
    $config_handler = xoops_gethandler('config');
    if ($count > 0) {
        for ($i = 0; $i < $count; $i++) {
            $config = $config_handler->getConfig($conf_ids[$i]);
            $new_value =& ${$config->getVar('conf_name')};
            if (is_array($new_value) || $new_value != $config->getVar('conf_value')) {
                $config->setConfValueForInput($new_value);
                $config_handler->insertConfig($config);
            }
            unset($new_value);
        }
    }
    redirect_header('preferences.php', 2, _AM_DBUPDATED);

}