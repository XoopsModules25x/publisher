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
 * @version         $Id: xoops_version.php 11345 2013-04-03 22:35:51Z luciorota $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
//mb xoops_load('XoopsLocal');
xoops_load('XoopsRequest');

$moduleDirName = basename(__DIR__);
xoops_load('xoopseditorhandler');
$editorHandler = XoopsEditorHandler::getInstance();
$xoops_url     = parse_url(XOOPS_URL);

$modversion = array(
    'name'                => _MI_PUBLISHER_MD_NAME,
    'version'             => '1.03',
    'module_status'       => 'Beta 2',
    'release_date'        => '2016/03/19',
    'description'         => _MI_PUBLISHER_MD_DESC,
    'author'              => 'Trabis (www.Xuups.com)',
    'credits'             => 'w4z004, hsalazar, Mithrandir, fx2024, Ackbarr, Mariuss, Marco, Michiel, phppp, outch, Xvitry, Catzwolf, Shine, McDonald, trabis, Mowaffak, Bandit-x, Shiva',
    'module_website_url'  => 'www.xoops.org',
    'module_website_name' => 'Support site',
    'help'                => 'page=help',
    'license'             => 'GNU GPL 2.0 or later',
    'license_url'         => 'www.gnu.org/licenses/gpl-2.0.html',
    'official'            => 1,
    // ------------------- Folders & Files -------------------
    'dirname'             => $moduleDirName,
    'dirmoduleadmin'      => 'Frameworks/moduleclasses',
    'sysIcons16'          => 'Frameworks/moduleclasses/icons/16',
    'sysIcons32'          => 'Frameworks/moduleclasses/icons/32',
    // Local path icons
    'modIcons16'          => 'assets/images/icons/16',
    'modIcons32'          => 'assets/images/icons/32',
    // images
    'iconsmall'           => 'assets/images/iconsmall.png',
    'iconbig'             => 'assets/images/iconbig.png',
    'image'               => 'assets/images/logo.png',
    'release_file'        => XOOPS_URL . '/modules/' . $moduleDirName . '/docs/changelog.txt',
    // ------------------- Install/Update -------------------
    'onInstall'           => 'include/oninstall.php',
    'onUpdate'            => 'include/onupdate.php',
    // ------------------- Min Requirements -------------------
    'min_php'             => '5.5',
    'min_xoops'           => '2.5.8',
    'min_admin'           => '1.1',
    'min_db'              => array('mysql' => '5.0.7', 'mysqli' => '5.0.7'),
    // ------------------- Admin Menu -------------------
    'hasAdmin'            => 1,
    'system_menu'         => 1,
    'adminindex'          => 'admin/index.php',
    'adminmenu'           => 'admin/menu.php',
    // ------------------- Main Menu -------------------
    'hasMain'             => 1,
    // ------------------- Mysql -------------------
    'sqlfile'             => array('mysql' => 'sql/mysql.sql'),
    // ------------------- Tables -------------------
    'tables'              => array(
        $moduleDirName . '_categories',
        $moduleDirName . '_items',
        $moduleDirName . '_files',
        $moduleDirName . '_meta',
        $moduleDirName . '_mimetypes',
        $moduleDirName . '_rating'));

//include_once $GLOBALS['xoops']->path('modules/' . $modversion['dirname'] . '/include/constants.php');
xoops_load('constants', $moduleDirName);
/*
$logo_filename = $modversion['dirname'] . '_logo.png';

if (file_exists($GLOBALS['xoops']->path('modules/' . $modversion['dirname'] . '/assets/images/' . $logo_filename))) {
    $modversion['image'] = 'assets/images/{$logo_filename}';
} else {
    $modversion['image'] = 'assets/images/module_logo.png';
}
*/

$modversion['people']['testers'][] = 'urban, AEIOU, pacho, mariane';
//$modversion['people']['translaters'][] = '';
//$modversion['people']['documenters'][] = '';
$modversion['author_word'] = '';

// Search
$modversion['hasSearch']      = 1;
$modversion['search']['file'] = 'include/search.inc.php';
$modversion['search']['func'] = 'publisher_search';

if (is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') == $modversion['dirname']) {
    $isAdmin = false;
    if (is_object($GLOBALS['xoopsUser'])) {
        $isAdmin = $GLOBALS['xoopsUser']->isAdmin($GLOBALS['xoopsModule']->getVar('mid'));
    }
    // Add the Submit new item button
    $allowsubmit = (isset($GLOBALS['xoopsModuleConfig']['perm_submit']) && $GLOBALS['xoopsModuleConfig']['perm_submit'] == 1);
    $anonpost    = (isset($GLOBALS['xoopsModuleConfig']['permissions_anon_post']) && $GLOBALS['xoopsModuleConfig']['permissions_anon_post'] == 1);
    if ($isAdmin || ($allowsubmit && (is_object($GLOBALS['xoopsUser']) || $anonpost))) {
        $modversion['sub'][] = array(
            'name' => _MI_PUBLISHER_SUB_SMNAME1,
            'url'  => 'submit.php?op=add');
    }

    // ------------------- Search -------------------
    $allowsearch = (isset($GLOBALS['xoopsModuleConfig']['perm_search']) && $GLOBALS['xoopsModuleConfig']['perm_search'] == 1);
    if ($allowsearch) {
        $modversion['sub'][] = array(
            'name' => _MI_PUBLISHER_SUB_SMNAME3,
            'url'  => 'search.php');
    }
}
// Add the Archive button
$modversion['sub'][] = array(
    'name' => _MI_PUBLISHER_SUB_ARCHIVE,
    'url'  => 'archive.php');

// ------------------- Blocks -------------------
$modversion['blocks'][] = array(
    'file'        => 'items_new.php',
    'name'        => _MI_PUBLISHER_ITEMSNEW,
    'description' => _MI_PUBLISHER_ITEMSNEW_DSC,
    'show_func'   => 'publisher_items_new_show',
    'edit_func'   => 'publisher_items_new_edit',
    'options'     => '0|datesub|0|5|65|none',
    'template'    => 'publisher_items_new.tpl');

$modversion['blocks'][] = array(
    'file'        => 'items_recent.php',
    'name'        => _MI_PUBLISHER_RECENTITEMS,
    'description' => _MI_PUBLISHER_RECENTITEMS_DSC,
    'show_func'   => 'publisher_items_recent_show',
    'edit_func'   => 'publisher_items_recent_edit',
    'options'     => '0|datesub|5|65',
    'template'    => 'publisher_items_recent.tpl');

$modversion['blocks'][] = array(
    'file'        => 'items_spot.php',
    'name'        => _MI_PUBLISHER_ITEMSPOT,
    'description' => _MI_PUBLISHER_ITEMSPOT_DSC,
    'show_func'   => 'publisher_items_spot_show',
    'edit_func'   => 'publisher_items_spot_edit',
    'options'     => '1|5|0|0|1|1|bullet|0|0',
    'template'    => 'publisher_items_spot.tpl');

$modversion['blocks'][] = array(
    'file'        => 'items_random_item.php',
    'name'        => _MI_PUBLISHER_ITEMSRANDOM_ITEM,
    'description' => _MI_PUBLISHER_ITEMSRANDOM_ITEM_DSC,
    'show_func'   => 'publisher_items_random_item_show',
    'template'    => 'publisher_items_random_item.tpl');

$modversion['blocks'][] = array(
    'file'        => 'items_menu.php',
    'name'        => _MI_PUBLISHER_ITEMSMENU,
    'description' => _MI_PUBLISHER_ITEMSMENU_DSC,
    'show_func'   => 'publisher_items_menu_show',
    'edit_func'   => 'publisher_items_menu_edit',
    'options'     => '0|datesub|5',
    'template'    => 'publisher_items_menu.tpl');

$modversion['blocks'][] = array(
    'file'        => 'latest_files.php',
    'name'        => _MI_PUBLISHER_LATESTFILES,
    'description' => _MI_PUBLISHER_LATESTFILES_DSC,
    'show_func'   => 'publisher_latest_files_show',
    'edit_func'   => 'publisher_latest_files_edit',
    'options'     => '0|datesub|5|0',
    'template'    => 'publisher_latest_files.tpl');

$modversion['blocks'][] = array(
    'file'        => 'date_to_date.php',
    'name'        => _MI_PUBLISHER_DATE_TO_DATE,
    'description' => _MI_PUBLISHER_DATE_TO_DATE_DSC,
    'show_func'   => 'publisher_date_to_date_show',
    'edit_func'   => 'publisher_date_to_date_edit',
    'options'     => "formatTimestamp(time(), 'm/j/Y') . " | " . formatTimestamp(time(), 'm/j/Y')",
    'template'    => 'publisher_date_to_date.tpl');

$modversion['blocks'][] = array(
    'file'        => 'items_columns.php',
    'name'        => _MI_PUBLISHER_COLUMNS,
    'description' => _MI_PUBLISHER_COLUMNS_DSC,
    'show_func'   => 'publisher_items_columns_show',
    'edit_func'   => 'publisher_items_columns_edit',
    'options'     => '2|0|4|256|normal',
    'template'    => 'publisher_items_columns.tpl');

$modversion['blocks'][] = array(
    'file'        => 'latest_news.php',
    'name'        => _MI_PUBLISHER_LATEST_NEWS,
    'description' => _MI_PUBLISHER_LATEST_NEWS_DSC,
    'show_func'   => 'publisher_latest_news_show',
    'edit_func'   => 'publisher_latest_news_edit',
    'options'     => '0|6|2|300|0|0|100|30|1|published|1|120|120|1|dcdcdc|RIGHT|1|1|1|1|1|1|1|1|1|1|1|1|1|extended|',
    'template'    => 'publisher_latest_news.tpl');

$modversion['blocks'][] = array(
    'file'        => 'search.php',
    'name'        => _MI_PUBLISHER_SEARCH,
    'description' => _MI_PUBLISHER_SEARCH_DSC,
    'show_func'   => 'publisher_search_show',
    'template'    => 'publisher_search_block.tpl');

$modversion['blocks'][] = array(
    'file'        => 'category_items_sel.ph',
    'name'        => _MI_PUBLISHER_CATEGORY_ITEMS_SEL,
    'description' => _MI_PUBLISHER_CATEGORY_ITEMS_SEL_DSC,
    'show_func'   => 'publisher_category_items_sel_show',
    'edit_func'   => 'publisher_category_items_sel_edit',
    'options'     => '0|datesub|5|65',
    'template'    => 'publisher_category_items_sel.tpl');

// ------------------- Templates -------------------

$modversion['templates'] = array(
    array(
        'file'        => 'publisher_header.tpl',
        'description' => '_MI_PUBLISHER_HEADER_DSC'),
    array(
        'file'        => 'publisher_footer.tpl',
        'description' => '_MI_PUBLISHER_FOOTER_DSC'),
    array(
        'file'        => 'publisher_singleitem.tpl',
        'description' => '_MI_PUBLISHER_SINGLEITEM_DSC'),
    array(
        'file'        => 'publisher_categories_table.tpl',
        'description' => '_MI_PUBLISHER_CATEGORIES_TABLE_DSC'),
    array(
        'file'        => 'publisher_display_list.tpl',
        'description' => '_MI_PUBLISHER_DISPLAY_LIST_DSC'),
    array(
        'file'        => 'publisher_display_summary.tpl',
        'description' => '_MI_PUBLISHER_DISPLAY_SUMMARY_DSC'),
    array(
        'file'        => 'publisher_display_full.tpl',
        'description' => '_MI_PUBLISHER_DISPLAY_FULL_DSC'),
    array(
        'file'        => 'publisher_display_wfsection.tpl',
        'description' => '_MI_PUBLISHER_DISPLAY_WFSECTION_DSC'),
    array(
        'file'        => 'publisher_item.tpl',
        'description' => '_MI_PUBLISHER_ITEM_DSC'),
    array(
        'file'        => 'publisher_submit.tpl',
        'description' => '_MI_PUBLISHER_SUBMIT_DSC'),
    array(
        'file'        => 'publisher_singleitem_block.tpl',
        'description' => '_MI_PUBLISHER_SINGLEITEM_BLOCK_DSC'),
    array(
        'file'        => 'publisher_print.tpl',
        'description' => '_MI_PUBLISHER_PRINT_DSC'),
    array(
        'file'        => 'publisher_rss.tpl',
        'description' => '_MI_PUBLISHER_RSS_DSC'),
    array(
        'file'        => 'publisher_addfile.tpl',
        'description' => '_MI_PUBLISHER_ADDFILE_DSC'),
    array(
        'file'        => 'publisher_search.tpl',
        'description' => '_MI_PUBLISHER_SEARCH_DSC'),
    array(
        'file'        => 'publisher_author_items.tpl',
        'description' => '_MI_PUBLISHER_AUTHOR_ITEMS_DSC'),
    array(
        'file'        => 'publisher_archive.tpl',
        'description' => '_MI_PUBLISHER_ARCHIVE__DSC'));

// Config categories

$modversion['configcat']['seo']      = array(
    'name'        => _MI_PUBLISHER_CONFCAT_SEO,
    'description' => _MI_PUBLISHER_CONFCAT_SEO_DSC);
$modversion['configcat']['indexcat'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_INDEXCAT,
    'description' => _MI_PUBLISHER_CONFCAT_INDEXCAT_DSC);

$modversion['configcat']['index'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_INDEX,
    'description' => _MI_PUBLISHER_CONFCAT_INDEX_DSC);

$modversion['configcat']['category'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_CATEGORY,
    'description' => _MI_PUBLISHER_CONFCAT_CATEGORY_DSC);

$modversion['configcat']['item'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_ITEM,
    'description' => _MI_PUBLISHER_CONFCAT_ITEM_DSC);

$modversion['configcat']['print'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_PRINT,
    'description' => _MI_PUBLISHER_CONFCAT_PRINT_DSC);

$modversion['configcat']['search'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_SEARCH,
    'description' => _MI_PUBLISHER_CONFCAT_SEARCH_DSC);

$modversion['configcat']['submit'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_SUBMIT,
    'description' => _MI_PUBLISHER_CONFCAT_SUBMIT_DSC);

$modversion['configcat']['permissions'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_PERMISSIONS,
    'description' => _MI_PUBLISHER_CONFCAT_PERMISSIONS_DSC);

$modversion['configcat']['format'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_FORMAT,
    'description' => _MI_PUBLISHER_CONFCAT_FORMAT_DSC);

//mb
$modversion['configcat']['group_header'] = array(
    'name'        => _MI_PUBLISHER_CONFCAT_FORMAT,
    'description' => _MI_PUBLISHER_CONFCAT_FORMAT_DSC);

// Config Settings (only for modules that need config settings generated automatically)

################### SEO ####################

//$isModuleAction = (!empty($_POST['fct']) && 'modulesadmin' == $_POST['fct']) ? true : false;
$isModuleAction = ('modulesadmin' === XoopsRequest::getString('fct', '', 'POST'));
//if ($isModuleAction && (in_array(php_sapi_name(), array('apache', 'apache2handler', 'cgi-fcgi')))) {
//    _MI_PUBLISHER_URL_REWRITE_HTACCESS => 'htaccess'
//}

// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_SEO',
    'description' => '_MI_PUBLISHER_CONFCAT_SEO_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'seo_url_rewrite',
    'title'       => '_MI_PUBLISHER_URL_REWRITE',
    'description' => '_MI_PUBLISHER_URL_REWRITE_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'none',
    'options'     => array_merge(array(_MI_PUBLISHER_URL_REWRITE_NONE => 'none'), array(_MI_PUBLISHER_URL_REWRITE_PATHINFO => 'path-info'), // Is performing module install/update?
                                 ($isModuleAction && in_array(php_sapi_name(), array('apache', 'apache2handler', 'cgi-fcgi'))) ? array(_MI_PUBLISHER_URL_REWRITE_HTACCESS => 'htaccess') : array()),
    'category'    => 'seo');

$modversion['config'][] = array(
    'name'        => 'seo_module_name',
    'title'       => '_MI_PUBLISHER_SEOMODNAME',
    'description' => '_MI_PUBLISHER_SEOMODNAMEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => $modversion['dirname'],
    'category'    => 'seo');
$modversion['config'][] = array(
    'name'        => 'seo_meta_keywords',
    'title'       => '_MI_PUBLISHER_SEO_METAKEYWORDS',
    'description' => '_MI_PUBLISHER_SEO_METAKEYWORDS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'seo');
################### INDEX PAGE ####################

// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       =>  '_MI_PUBLISHER_CONFCAT_INDEXCAT',
    'description' => '_MI_PUBLISHER_CONFCAT_INDEXCAT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'index_title_and_welcome',
    'title'       => '_MI_PUBLISHER_WELCOME',
    'description' => '_MI_PUBLISHER_WELCOMEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'index');

$modversion['config'][] = array(
    'name'        => 'index_welcome_msg',
    'title'       => '_MI_PUBLISHER_INDEXMSG',
    'description' => '_MI_PUBLISHER_INDEXMSGDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_PUBLISHER_INDEXMSGDEF,
    'category'    => 'index');

$modversion['config'][] = array(
    'name'        => 'index_display_last_items',
    'title'       => '_MI_PUBLISHER_LASTITEMS',
    'description' => '_MI_PUBLISHER_LASTITEMSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'index');

$modversion['config'][] = array(
    'name'        => 'index_footer',
    'title'       => '_MI_PUBLISHER_INDEXFOOTER',
    'description' => '_MI_PUBLISHER_INDEXFOOTERDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'index');
################### CATEGORY PAGE ####################
// display_categeory_summary enabled by Freeform Solutions March 21 2006

// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_CATEGORY',
    'description' => '_MI_PUBLISHER_CONFCAT_CATEGORY_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'cat_display_summary',
    'title'       => '_MI_PUBLISHER_DCS',
    'description' => '_MI_PUBLISHER_DCS_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'category');

$modversion['config'][] = array(
    'name'        => 'cat_list_image_width',
    'title'       => '_MI_PUBLISHER_CATLIST_IMG_W',
    'description' => '_MI_PUBLISHER_CATLIST_IMG_WDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '90',
    'category'    => 'category');

$modversion['config'][] = array(
    'name'        => 'cat_main_image_width',
    'title'       => '_MI_PUBLISHER_CATMAINIMG_W',
    'description' => '_MI_PUBLISHER_CATMAINIMG_WDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '150',
    'category'    => 'category');
################### ITEM PAGE ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_ITEM',
    'description' => '_MI_PUBLISHER_CONFCAT_ITEM_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'item_title_size',
    'title'       => '_MI_PUBLISHER_TITLE_SIZE',
    'description' => '_MI_PUBLISHER_TITLE_SIZEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '60',
    'category'    => 'item');

$modversion['config'][] = array(
    'name'        => 'item_disp_comment_link',
    'title'       => '_MI_PUBLISHER_DISCOM',
    'description' => '_MI_PUBLISHER_DISCOMDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item');

$modversion['config'][] = array(
    'name'        => 'item_disp_whowhen_link',
    'title'       => '_MI_PUBLISHER_WHOWHEN',
    'description' => '_MI_PUBLISHER_WHOWHENDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item');

$modversion['config'][] = array(
    'name'        => 'item_admin_hits',
    'title'       => '_MI_PUBLISHER_ADMINHITS',
    'description' => '_MI_PUBLISHER_ADMINHITSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item');

$modversion['config'][] = array(
    'name'        => 'item_footer',
    'title'       => '_MI_PUBLISHER_ITEMFOOTER',
    'description' => '_MI_PUBLISHER_ITEMFOOTERDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'item');

$modversion['config'][] = array(
    'name'        => 'item_other_items_type',
    'title'       => '_MI_PUBLISHER_OTHERITEMS',
    'description' => '_MI_PUBLISHER_OTHERITEMSDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => array(
        _MI_PUBLISHER_OTHER_ITEMS_TYPE_NONE          => 'none',
        _MI_PUBLISHER_OTHER_ITEMS_TYPE_PREVIOUS_NEXT => 'previous_next',
        _MI_PUBLISHER_OTHER_ITEMS_TYPE_ALL           => 'all'),
    'default'     => 'previous_next',
    'category'    => 'item');

################### INDEX AND CATEGORIES ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_INDEXCAT',
    'description' => '_MI_PUBLISHER_CONFCAT_INDEXCAT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'idxcat_show_subcats',
    'title'       => '_MI_PUBLISHER_SHOW_SUBCATS',
    'description' => '_MI_PUBLISHER_SHOW_SUBCATS_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'all',
    'options'     => array(
        _MI_PUBLISHER_SHOW_SUBCATS_NO       => 'no',
        _MI_PUBLISHER_SHOW_SUBCATS_NOTEMPTY => 'nonempty',
        _MI_PUBLISHER_SHOW_SUBCATS_ALL      => 'all',
        _MI_PUBLISHER_SHOW_SUBCATS_NOMAIN   => 'nomain'),
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_display_last_item',
    'title'       => '_MI_PUBLISHER_LASTITEM',
    'description' => '_MI_PUBLISHER_LASTITEMDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_last_item_size',
    'title'       => '_MI_PUBLISHER_LASTITSIZE',
    'description' => '_MI_PUBLISHER_LASTITSIZEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '50',
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_items_display_type',
    'title'       => '_MI_PUBLISHER_DISTYPE',
    'description' => '_MI_PUBLISHER_DISTYPEDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => array(
        _MI_PUBLISHER_DISPLAYTYPE_SUMMARY   => 'summary',
        _MI_PUBLISHER_DISPLAYTYPE_FULL      => 'full',
        _MI_PUBLISHER_DISPLAYTYPE_LIST      => 'list',
        _MI_PUBLISHER_DISPLAYTYPE_WFSECTION => 'wfsection'),
    'default'     => 'summary',
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_display_subcat_dsc',
    'title'       => '_MI_PUBLISHER_DISSBCATDSC',
    'description' => '_MI_PUBLISHER_DISSBCATDSCDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_display_date_col',
    'title'       => '_MI_PUBLISHER_DISDATECOL',
    'description' => '_MI_PUBLISHER_DISDATECOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_display_hits_col',
    'title'       => '_MI_PUBLISHER_HITSCOL',
    'description' => '_MI_PUBLISHER_HITSCOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_show_rss_link',
    'title'       => '_MI_PUBLISHER_SHOW_RSS',
    'description' => '_MI_PUBLISHER_SHOW_RSSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_collaps_heading',
    'title'       => '_MI_PUBLISHER_COLLHEAD',
    'description' => '_MI_PUBLISHER_COLLHEADDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_cat_perpage',
    'title'       => '_MI_PUBLISHER_CATPERPAGE',
    'description' => '_MI_PUBLISHER_CATPERPAGEDSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 15,
    'options'     => array('5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50),
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_perpage',
    'title'       => '_MI_PUBLISHER_PERPAGE',
    'description' => '_MI_PUBLISHER_PERPAGEDSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 15,
    'options'     => array('5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50),
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_index_perpage',
    'title'       => '_MI_PUBLISHER_PERPAGEINDEX',
    'description' => '_MI_PUBLISHER_PERPAGEINDEXDSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 15,
    'options'     => array('5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50),
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_partial_view_text',
    'title'       => '_MI_PUBLISHER_PV_TEXT',
    'description' => '_MI_PUBLISHER_PV_TEXTDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_PUBLISHER_PV_TEXT_DEF,
    'category'    => 'indexcat');

$modversion['config'][] = array(
    'name'        => 'idxcat_display_art_count',
    'title'       => '_MI_PUBLISHER_ARTCOUNT',
    'description' => '_MI_PUBLISHER_ARTCOUNTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'indexcat');

################### PRINT ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_PRINT',
    'description' => '_MI_PUBLISHER_CONFCAT_PRINT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'print_header',
    'title'       => '_MI_PUBLISHER_HEADERPRINT',
    'description' => '_MI_PUBLISHER_HEADERPRINTDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'print');

$modversion['config'][] = array(
    'name'        => 'print_logourl',
    'title'       => '_MI_PUBLISHER_PRINTLOGOURL',
    'description' => '_MI_PUBLISHER_PRINTLOGOURLDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => XOOPS_URL . '/images/logo.gif',
    'category'    => 'print');

$modversion['config'][] = array(
    'name'        => 'print_footer',
    'title'       => '_MI_PUBLISHER_FOOTERPRINT',
    'description' => '_MI_PUBLISHER_FOOTERPRINTDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'item footer',
    'options'     => array(
        _MI_PUBLISHER_ITEMFOOTER_SEL  => 'item footer',
        _MI_PUBLISHER_INDEXFOOTER_SEL => 'index footer',
        _MI_PUBLISHER_BOTH_FOOTERS    => 'both',
        _MI_PUBLISHER_NO_FOOTERS      => 'none'),
    'category'    => 'print');

################### FORMAT ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_FORMAT',
    'description' => '_MI_PUBLISHER_CONFCAT_FORMAT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'format_date',
    'title'       => '_MI_PUBLISHER_DATEFORMAT',
    'description' => '_MI_PUBLISHER_DATEFORMATDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => 'd-M-Y H:i',
    'category'    => 'format');

$modversion['config'][] = array(
    'name'        => 'format_order_by',
    'title'       => '_MI_PUBLISHER_ORDERBY',
    'description' => '_MI_PUBLISHER_ORDERBYDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => array(
        _MI_PUBLISHER_ORDERBY_TITLE    => 'title',
        _MI_PUBLISHER_ORDERBY_DATE     => 'date',
        _MI_PUBLISHER_ORDERBY_HITS     => 'counter',
        _MI_PUBLISHER_ORDERBY_RATING   => 'rating',
        _MI_PUBLISHER_ORDERBY_VOTES    => 'votes',
        _MI_PUBLISHER_ORDERBY_COMMENTS => 'comments',
        _MI_PUBLISHER_ORDERBY_WEIGHT   => 'weight'),
    'default'     => 'date',
    'category'    => 'format');

$modversion['config'][] = array(
    'name'        => 'format_image_nav',
    'title'       => '_MI_PUBLISHER_IMAGENAV',
    'description' => '_MI_PUBLISHER_IMAGENAVDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'format');

$modversion['config'][] = array(
    'name'        => 'format_realname',
    'title'       => '_MI_PUBLISHER_USEREALNAME',
    'description' => '_MI_PUBLISHER_USEREALNAMEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'format');

$modversion['config'][] = array(
    'name'        => 'format_highlight_color',
    'title'       => '_MI_PUBLISHER_HLCOLOR',
    'description' => '_MI_PUBLISHER_HLCOLORDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '#FFFF80',
    'category'    => 'format');

$modversion['config'][] = array(
    'name'        => 'format_linked_path',
    'title'       => '_MI_PUBLISHER_LINKPATH',
    'description' => '_MI_PUBLISHER_LINKPATHDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'format');

$modversion['config'][] = array(
    'name'        => 'format_breadcrumb_modname',
    'title'       => '_MI_PUBLISHER_BCRUMB',
    'description' => '_MI_PUBLISHER_BCRUMBDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'format');

################### SEARCH ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_SEARCH',
    'description' => '_MI_PUBLISHER_CONFCAT_SEARCH_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'search_cat_path',
    'title'       => '_MI_PUBLISHER_PATHSEARCH',
    'description' => '_MI_PUBLISHER_PATHSEARCHDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'search');

################### SUBMIT ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_SUBMIT',
    'description' => '_MI_PUBLISHER_CONFCAT_SUBMIT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'submit_intro_msg',
    'title'       => '_MI_PUBLISHER_SUBMITMSG',
    'description' => '_MI_PUBLISHER_SUBMITMSGDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_PUBLISHER_SUBMITMSGDEF,
    'category'    => 'submit');

xoops_load('XoopsEditorHandler');
$editorHandler = XoopsEditorHandler::getInstance();

$modversion['config'][] = array(
    'name'        => 'submit_editor',
    'title'       => '_MI_PUBLISHER_EDITOR',
    'description' => '_MI_PUBLISHER_EDITOR_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => array_flip($editorHandler->getList()),
    'default'     => 'dhtmltextarea',
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_editor_rows',
    'title'       => '_MI_PUBLISHER_EDITOR_ROWS',
    'description' => '_MI_PUBLISHER_EDITOR_ROWS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '35',
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_editor_cols',
    'title'       => '_MI_PUBLISHER_EDITOR_COLS',
    'description' => '_MI_PUBLISHER_EDITOR_COlS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '60',
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_editor_width',
    'title'       => '_MI_PUBLISHER_EDITOR_WIDTH',
    'description' => '_MI_PUBLISHER_EDITOR_WIDTH_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '100%',
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_editor_height',
    'title'       => '_MI_PUBLISHER_EDITOR_HEIGHT',
    'description' => '_MI_PUBLISHER_EDITOR_HEIGHT_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '400px',
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_status',
    'title'       => '_MI_PUBLISHER_FORM_STATUS',
    'description' => '_MI_PUBLISHER_FORM_STATUS_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => array(
        _MI_PUBLISHER_SUBMITTED => PublisherConstants::PUBLISHER_STATUS_SUBMITTED,
        _MI_PUBLISHER_PUBLISHED => PublisherConstants::PUBLISHER_STATUS_PUBLISHED,
        _MI_PUBLISHER_OFFLINE   => PublisherConstants::PUBLISHER_STATUS_OFFLINE,        
        _MI_PUBLISHER_REJECTED  => PublisherConstants::PUBLISHER_STATUS_REJECTED),
    'default'     => PublisherConstants::PUBLISHER_STATUS_SUBMITTED,
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_allowcomments',
    'title'       => '_MI_PUBLISHER_FORM_ALLOWCOMMENTS',
    'description' => '_MI_PUBLISHER_FORM_ALLOWCOMMENTS_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_dohtml',
    'title'       => '_MI_PUBLISHER_FORM_DOHTML',
    'description' => '_MI_PUBLISHER_FORM_DOHTML_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_dosmiley',
    'title'       => '_MI_PUBLISHER_FORM_DOSMILEY',
    'description' => '_MI_PUBLISHER_FORM_DOSMILEY_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_doxcode',
    'title'       => '_MI_PUBLISHER_FORM_DOXCODE',
    'description' => '_MI_PUBLISHER_FORM_DOXCODE_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_doimage',
    'title'       => '_MI_PUBLISHER_FORM_DOIMAGE',
    'description' => '_MI_PUBLISHER_FORM_DOIMAGE_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit');

$modversion['config'][] = array(
    'name'        => 'submit_dobr',
    'title'       => '_MI_PUBLISHER_FORM_DOBR',
    'description' => '_MI_PUBLISHER_FORM_DOBR_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit');

################### PERMISSIONS ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_PERMISSIONS',
    'description' => '_MI_PUBLISHER_CONFCAT_PERMISSIONS_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'perm_submit',
    'title'       => '_MI_PUBLISHER_ALLOWSUBMIT',
    'description' => '_MI_PUBLISHER_ALLOWSUBMITDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_edit',
    'title'       => '_MI_PUBLISHER_ALLOWEDIT',
    'description' => '_MI_PUBLISHER_ALLOWEDITDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_delete',
    'title'       => '_MI_PUBLISHER_ALLOWDELETE',
    'description' => '_MI_PUBLISHER_ALLOWDELETEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_anon_submit',
    'title'       => '_MI_PUBLISHER_ANONPOST',
    'description' => '_MI_PUBLISHER_ANONPOSTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_upload',
    'title'       => '_MI_PUBLISHER_UPLOAD',
    'description' => '_MI_PUBLISHER_UPLOADDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_clone',
    'title'       => '_MI_PUBLISHER_CLONE',
    'description' => '_MI_PUBLISHER_CLONEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_rating',
    'title'       => '_MI_PUBLISHER_ALLOWRATING',
    'description' => '_MI_PUBLISHER_ALLOWRATING_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_search',
    'title'       => '_MI_PUBLISHER_ALLOWSEARCH',
    'description' => '_MI_PUBLISHER_ALLOWSEARCH_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_author_items',
    'title'       => '_MI_PUBLISHER_ALLOW_AUTHOR_ITEMS',
    'description' => '_MI_PUBLISHER_ALLOW_AUTHOR_ITEMS_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_com_art_level',
    'title'       => '_MI_PUBLISHER_COMMENTS',
    'description' => '_MI_PUBLISHER_COMMENTSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions');

$modversion['config'][] = array(
    'name'        => 'perm_autoapprove',
    'title'       => '_MI_PUBLISHER_AUTOAPP',
    'description' => '_MI_PUBLISHER_AUTOAPPDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions');

################### OTHERS ####################
// group header
$modversion['config'][] = array(
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_OTHERS',
    'description' => '_MI_PUBLISHER_CONFCAT_OTHERS_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header');

$modversion['config'][] = array(
    'name'        => 'display_breadcrumb',
    'title'       => '_MI_PUBLISHER_DISPBREAD',
    'description' => '_MI_PUBLISHER_DISPBREADDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1);

$modversion['config'][] = array(
    'name'        => 'display_pdf',
    'title'       => '_MI_PUBLISHER_DISPLAY_PDF',
    'description' => '_MI_PUBLISHER_DISPLAY_PDF_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1);

$modversion['config'][] = array(
    'name'        => 'maximum_filesize',
    'title'       => '_MI_PUBLISHER_MAX_SIZE',
    'description' => '_MI_PUBLISHER_MAX_SIZEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '1000000');

$modversion['config'][] = array(
    'name'        => 'maximum_image_width',
    'title'       => '_MI_PUBLISHER_MAX_WIDTH',
    'description' => '_MI_PUBLISHER_MAX_WIDTHDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '800');

$modversion['config'][] = array(
    'name'        => 'maximum_image_height',
    'title'       => '_MI_PUBLISHER_MAX_HEIGHT',
    'description' => '_MI_PUBLISHER_MAX_HEIGHTDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '800');

########### ADDED in final #############

$modversion['config'][] = array(
    'name'        => 'item_disp_blocks_summary',
    'title'       => '_MI_PUBLISHER_DISP_BLOCK_SUM',
    'description' => '_MI_PUBLISHER_DISP_BLOCK_SUM_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item');

$modversion['config'][] = array(
    'name'        => 'index_disp_subtitle',
    'title'       => '_MI_PUBLISHER_DISP_INDEX_SUB',
    'description' => '_MI_PUBLISHER_DISP_INDEX_SUB_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'index');

$modversion['config'][] = array(
    'name'        => 'cat_disp_subtitle',
    'title'       => '_MI_PUBLISHER_DISP_CAT_SUB',
    'description' => '_MI_PUBLISHER_DISP_CAT_SUB_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'category');

$modversion['config'][] = array(
    'name'        => 'item_disp_subtitle',
    'title'       => '_MI_PUBLISHER_DISP_ITEM_SUB',
    'description' => '_MI_PUBLISHER_DISP_ITEM_SUB_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item');

// Comments
$modversion['hasComments']          = 1;
$modversion['comments']['itemName'] = 'itemid';
$modversion['comments']['pageName'] = 'item.php';

// Comment callback functions
$modversion['comments']['callbackFile']        = 'include/comment_functions.php';
$modversion['comments']['callback']['approve'] = 'publisher_com_approve';
$modversion['comments']['callback']['update']  = 'publisher_com_update';

// Notification
$modversion['hasNotification']             = 1;
$modversion['notification']['lookup_file'] = 'include/notification.inc.php';
$modversion['notification']['lookup_func'] = 'publisher_notify_iteminfo';

$modversion['notification']['category'][] = array(
    'name'           => 'global_item',
    'title'          => _MI_PUBLISHER_GLOBAL_ITEM_NOTIFY,
    'description'    => _MI_PUBLISHER_GLOBAL_ITEM_NOTIFY_DSC,
    'subscribe_from' => "array('index.php', 'category.php', 'item.php')");

$modversion['notification']['category'][] = array(
    'name'           => 'category_item',
    'title'          => _MI_PUBLISHER_CATEGORY_ITEM_NOTIFY,
    'description'    => _MI_PUBLISHER_CATEGORY_ITEM_NOTIFY_DSC,
    'subscribe_from' => "array('index.php', 'category.php', 'item.php')",
    'item_name'      => 'categoryid',
    'allow_bookmark' => 1);
$modversion['notification']['category'][] = array(
    'name'           => 'item',
    'title'          => _MI_PUBLISHER_ITEM_NOTIFY,
    'description'    => _MI_PUBLISHER_ITEM_NOTIFY_DSC,
    'subscribe_from' => "array('item.php')",
    'item_name'      => 'itemid',
    'allow_bookmark' => 1);

$modversion['notification']['event'][] = array(
    'name'          => 'category_created',
    'category'      => 'global_item',
    'title'         => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY,
    'caption'       => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY_DSC,
    'mail_template' => 'global_item_category_created',
    'mail_subject'  => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY_SBJ);
$modversion['notification']['event'][] = array(
    'name'          => 'submitted',
    'category'      => 'global_item',
    'admin_only'    => 1,
    'title'         => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY,
    'caption'       => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY_DSC,
    'mail_template' => 'global_item_submitted',
    'mail_subject'  => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY_SBJ);
$modversion['notification']['event'][] = array(
    'name'          => 'published',
    'category'      => 'global_item',
    'title'         => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY,
    'caption'       => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY_DSC,
    'mail_template' => 'global_item_published',
    'mail_subject'  => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY_SBJ);
$modversion['notification']['event'][] = array(
    'name'          => 'submitted',
    'category'      => 'category_item',
    'admin_only'    => 1,
    'title'         => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY,
    'caption'       => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY_DSC,
    'mail_template' => 'category_item_submitted',
    'mail_subject'  => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY_SBJ);
$modversion['notification']['event'][] = array(
    'name'          => 'published',
    'category'      => 'category_item',
    'title'         => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY,
    'caption'       => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY_DSC,
    'mail_template' => 'category_item_published',
    'mail_subject'  => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY_SBJ);
$modversion['notification']['event'][] = array(
    'name'          => 'rejected',
    'category'      => 'item',
    'invisible'     => 1,
    'title'         => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY,
    'caption'       => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY_DSC,
    'mail_template' => 'item_rejected',
    'mail_subject'  => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY_SBJ);
$modversion['notification']['event'][] = array(
    'name'          => 'approved',
    'category'      => 'item',
    'invisible'     => 1,
    'title'         => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY,
    'caption'       => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY_DSC,
    'mail_template' => 'item_approved',
    'mail_subject'  => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY_SBJ);
