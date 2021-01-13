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
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/preloads/autoloader.php';

$moduleDirName      = basename(__DIR__);
$moduleDirNameUpper = mb_strtoupper($moduleDirName);
xoops_loadLanguage('common');

xoops_load('xoopseditorhandler');
$editorHandler = \XoopsEditorHandler::getInstance();

$xoops_url = parse_url(XOOPS_URL);

$modversion = [
    'version'             => '1.08',
    'module_status'       => 'Alpha 4',
    'release_date'        => '2021/01/12',
    'name'                => _MI_PUBLISHER_MD_NAME,
    'description'         => _MI_PUBLISHER_MD_DESC,
    'author'              => 'Trabis (www.Xuups.com)',
    'credits'             => 'w4z004, hsalazar, Mithrandir, fx2024, Ackbarr, Mariuss, Marco, Michiel, phppp, outch, Xvitry, Catzwolf, Shine, McDonald, trabis, Mowaffak, Bandit-X, Shiva',
    'module_website_url'  => 'www.xoops.org',
    'module_website_name' => 'Support site',
    'help'                => 'page=help',
    'license'             => 'GNU GPL 2.0 or later',
    'license_url'         => 'www.gnu.org/licenses/gpl-2.0.html',
    'official'            => 1,
    // ------------------- Folders & Files -------------------
    'dirname'             => $moduleDirName,
    // Local path icons
    'modicons16'          => 'assets/images/icons/16',
    'modicons32'          => 'assets/images/icons/32',
    // images
    'iconsmall'           => 'assets/images/iconsmall.png',
    'iconbig'             => 'assets/images/iconbig.png',
    'image'               => 'assets/images/logoModule.png',
    'release_file'        => XOOPS_URL . '/modules/' . $moduleDirName . '/docs/changelog.txt',
    // ------------------- Install/Update -------------------
    'onInstall'           => 'include/oninstall.php',
    'onUpdate'            => 'include/onupdate.php',
    // ------------------- Min Requirements -------------------
    'min_php'             => '7.2',
    'min_xoops'           => '2.5.10',
    'min_admin'           => '1.2',
    'min_db'              => ['mysql' => '5.5'],
    // ------------------- Admin Menu -------------------
    'hasAdmin'            => 1,
    'system_menu'         => 1,
    'adminindex'          => 'admin/index.php',
    'adminmenu'           => 'admin/menu.php',
    // ------------------- Main Menu -------------------
    'hasMain'             => 1,
    // ------------------- Mysql -------------------
    'sqlfile'             => ['mysql' => 'sql/mysql.sql'],
    // ------------------- Tables -------------------
    'tables'              => [
        $moduleDirName . '_' . 'categories',
        $moduleDirName . '_' . 'items',
        $moduleDirName . '_' . 'files',
        $moduleDirName . '_' . 'meta',
        $moduleDirName . '_' . 'mimetypes',
        $moduleDirName . '_' . 'rating',
        //        $moduleDirName . '_' . 'voting',
    ],
];

// ------------------- Help files ------------------- //
$modversion['helpsection'] = [
    ['name' => _MI_PUBLISHER_HELP_OVERVIEW, 'link' => 'page=help'],
    ['name' => _MI_PUBLISHER_DISCLAIMER, 'link' => 'page=disclaimer'],
    ['name' => _MI_PUBLISHER_LICENSE, 'link' => 'page=license'],
    ['name' => _MI_PUBLISHER_SUPPORT, 'link' => 'page=support'],
];

//require_once $GLOBALS['xoops']->path('modules/' . $modversion['dirname'] . '/include/constants.php');
xoops_load('constants', $moduleDirName);

$modversion['people']['testers'][] = 'urban, AEIOU, pacho, mariane';
//$modversion['people']['translaters'][] = '';
//$modversion['people']['documenters'][] = '';
$modversion['author_word'] = '';

// Search
$modversion['hasSearch']      = 1;
$modversion['search']['file'] = 'include/search.inc.php';
$modversion['search']['func'] = 'publisher_search';

global $xoopsModule;
if (is_object($xoopsModule) && $xoopsModule->dirname() == $modversion['dirname'] && $xoopsModule->isactive()) {
    $isAdmin = false;
    if (is_object($GLOBALS['xoopsUser'])) {
        $isAdmin = $GLOBALS['xoopsUser']->isAdmin($GLOBALS['xoopsModule']->getVar('mid'));
    }
    // Add the Submit new item button
    $allowsubmit = (isset($GLOBALS['xoopsModuleConfig']['perm_submit']) && 1 == $GLOBALS['xoopsModuleConfig']['perm_submit']);
    $anonpost    = (isset($GLOBALS['xoopsModuleConfig']['permissions_anon_post']) && 1 == $GLOBALS['xoopsModuleConfig']['permissions_anon_post']);
    if ($isAdmin || ($allowsubmit && (is_object($GLOBALS['xoopsUser']) || $anonpost))) {
        $modversion['sub'][] = [
            'name' => _MI_PUBLISHER_SUB_SMNAME1,
            'url'  => 'submit.php?op=add',
        ];
    }

    // ------------------- Search -------------------
    $allowsearch = (isset($GLOBALS['xoopsModuleConfig']['perm_search']) && 1 == $GLOBALS['xoopsModuleConfig']['perm_search']);
    if ($allowsearch) {
        $modversion['sub'][] = [
            'name' => _MI_PUBLISHER_SUB_SMNAME3,
            'url'  => 'search.php',
        ];
    }
}
// Add the Archive button
$modversion['sub'][] = [
    'name' => _MI_PUBLISHER_SUB_ARCHIVE,
    'url'  => 'archive.php',
];

// ------------------- Blocks -------------------
$modversion['blocks'][] = [
    'file'        => 'items_new.php',
    'name'        => _MI_PUBLISHER_ITEMSNEW,
    'description' => _MI_PUBLISHER_ITEMSNEW_DSC,
    'show_func'   => 'publisher_items_new_show',
    'edit_func'   => 'publisher_items_new_edit',
    'options'     => '0|datesub|0|6|65|none|0|0|0|0|0|0|0',
    'template'    => 'publisher_items_new.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'items_recent.php',
    'name'        => _MI_PUBLISHER_RECENTITEMS,
    'description' => _MI_PUBLISHER_RECENTITEMS_DSC,
    'show_func'   => 'publisher_items_recent_show',
    'edit_func'   => 'publisher_items_recent_edit',
    'options'     => '0|datesub|5|65|0|0|1|1|1|0|0|0',
    'template'    => 'publisher_items_recent.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'items_spot.php',
    'name'        => _MI_PUBLISHER_ITEMSPOT,
    'description' => _MI_PUBLISHER_ITEMSPOT_DSC,
    'show_func'   => 'publisher_items_spot_show',
    'edit_func'   => 'publisher_items_spot_edit',
    'options'     => '1|5|0|0|1|0|block|100|0|date|0|1|1|1|1|0|1',
    'template'    => 'publisher_items_spot.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'items_random_item.php',
    'name'        => _MI_PUBLISHER_ITEMSRANDOM_ITEM,
    'description' => _MI_PUBLISHER_ITEMSRANDOM_ITEM_DSC,
    'show_func'   => 'publisher_items_random_item_show',
    'edit_func'   => 'publisher_items_random_item_edit',
    'options'     => '1|0|0|0|0|0|0|1',
    'template'    => 'publisher_items_random_item.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'items_menu.php',
    'name'        => _MI_PUBLISHER_ITEMSMENU,
    'description' => _MI_PUBLISHER_ITEMSMENU_DSC,
    'show_func'   => 'publisher_items_menu_show',
    'edit_func'   => 'publisher_items_menu_edit',
    'options'     => '0|datesub|5',
    'template'    => 'publisher_items_menu.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'latest_files.php',
    'name'        => _MI_PUBLISHER_LATESTFILES,
    'description' => _MI_PUBLISHER_LATESTFILES_DSC,
    'show_func'   => 'publisher_latest_files_show',
    'edit_func'   => 'publisher_latest_files_edit',
    'options'     => '0|datesub|5|0',
    'template'    => 'publisher_latest_files.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'date_to_date.php',
    'name'        => _MI_PUBLISHER_DATE_TO_DATE,
    'description' => _MI_PUBLISHER_DATE_TO_DATE_DSC,
    'show_func'   => 'publisher_date_to_date_show',
    'edit_func'   => 'publisher_date_to_date_edit',
    'options'     => formatTimestamp(1424860422, 'm/j/Y') . ' | ' . formatTimestamp(time(), 'm/j/Y'),
    'template'    => 'publisher_date_to_date.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'items_columns.php',
    'name'        => _MI_PUBLISHER_COLUMNS,
    'description' => _MI_PUBLISHER_COLUMNS_DSC,
    'show_func'   => 'publisher_items_columns_show',
    'edit_func'   => 'publisher_items_columns_edit',
    'options'     => '2|0|4|256|normal|0|0',
    'template'    => 'publisher_items_columns.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'latest_news.php',
    'name'        => _MI_PUBLISHER_LATEST_NEWS,
    'description' => _MI_PUBLISHER_LATEST_NEWS_DSC,
    'show_func'   => 'publisher_latest_news_show',
    'edit_func'   => 'publisher_latest_news_edit',
    'options'     => '0|6|1|300|0|0|300|30|up|datesub|1|900|400|1|dcdcdc|CENTER|1|0|0|1|1|1|1|0|0|0|0|0|1|0|normal|0',
    'template'    => 'publisher_latest_news.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'search.php',
    'name'        => _MI_PUBLISHER_SEARCH,
    'description' => _MI_PUBLISHER_SEARCH_DSC,
    'show_func'   => 'publisher_search_show',
    'template'    => 'publisher_search_block.tpl',
];

$modversion['blocks'][] = [
    'file'        => 'category_items_sel.php',
    'name'        => _MI_PUBLISHER_CATEGORY_ITEMS_SEL,
    'description' => _MI_PUBLISHER_CATEGORY_ITEMS_SEL_DSC,
    'show_func'   => 'publisher_category_items_sel_show',
    'edit_func'   => 'publisher_category_items_sel_edit',
    'options'     => '0|datesub|5|65',
    'template'    => 'publisher_category_items_sel.tpl',
];

// ------------------- Templates -------------------

$modversion['templates'] = [
    ['file' => 'publisher_header.tpl', 'description' => '_MI_PUBLISHER_HEADER_DSC'],
    ['file' => 'publisher_footer.tpl', 'description' => '_MI_PUBLISHER_FOOTER_DSC'],
    ['file' => 'publisher_singleitem.tpl', 'description' => '_MI_PUBLISHER_SINGLEITEM_DSC'],
    ['file' => 'publisher_categories_table.tpl', 'description' => '_MI_PUBLISHER_CATEGORIES_TABLE_DSC'],
    ['file' => 'publisher_display_list.tpl', 'description' => '_MI_PUBLISHER_DISPLAY_LIST_DSC'],
    ['file' => 'publisher_display_summary.tpl', 'description' => '_MI_PUBLISHER_DISPLAY_SUMMARY_DSC'],
    ['file' => 'publisher_display_full.tpl', 'description' => '_MI_PUBLISHER_DISPLAY_FULL_DSC'],
    ['file' => 'publisher_display_wfsection.tpl', 'description' => '_MI_PUBLISHER_DISPLAY_WFSECTION_DSC'],
    ['file' => 'publisher_item.tpl', 'description' => '_MI_PUBLISHER_ITEM_DSC'],
    ['file' => 'publisher_submit.tpl', 'description' => '_MI_PUBLISHER_SUBMIT_DSC'],
    ['file' => 'publisher_singleitem_block.tpl', 'description' => '_MI_PUBLISHER_SINGLEITEM_BLOCK_DSC'],
    ['file' => 'publisher_print.tpl', 'description' => '_MI_PUBLISHER_PRINT_DSC'],
    ['file' => 'publisher_rss.tpl', 'description' => '_MI_PUBLISHER_RSS_DSC'],
    ['file' => 'publisher_addfile.tpl', 'description' => '_MI_PUBLISHER_ADDFILE_DSC'],
    ['file' => 'publisher_search.tpl', 'description' => '_MI_PUBLISHER_SEARCH_DSC'],
    ['file' => 'publisher_author_items.tpl', 'description' => '_MI_PUBLISHER_AUTHOR_ITEMS_DSC'],
    ['file' => 'publisher_archive.tpl', 'description' => '_MI_PUBLISHER_ARCHIVE_DSC'],
    ['file' => 'publisher_vote.tpl', 'description' => ''],

    //admin
    ['file' => 'publisher_trello.tpl', 'description' => '_MI_PUBLISHER_TRELLO_DSC', 'type' => 'admin'],
];

// Config categories
/*
$modversion['configcat']['seo']      = [
    'name'        => _MI_PUBLISHER_CONFCAT_SEO,
    'description' => _MI_PUBLISHER_CONFCAT_SEO_DSC,
];
$modversion['configcat']['indexcat'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_INDEXCAT,
    'description' => _MI_PUBLISHER_CONFCAT_INDEXCAT_DSC,
];

$modversion['configcat']['index'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_INDEX,
    'description' => _MI_PUBLISHER_CONFCAT_INDEX_DSC,
];

$modversion['configcat']['category'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_CATEGORY,
    'description' => _MI_PUBLISHER_CONFCAT_CATEGORY_DSC,
];

$modversion['configcat']['indexcattemplate'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_INDEXCATTEMPLATE,
    'description' => _MI_PUBLISHER_CONFCAT_INDEXCATTEMPLATEDSC,
];

$modversion['configcat']['itemallarticle'] = [
    'name'        => _MI_PUBLISHER_ITEM_ALLARTICLE,
    'description' => _MI_PUBLISHER_ITEM_ALLARTICLEDSC,
];

$modversion['configcat']['item'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_ITEM,
    'description' => _MI_PUBLISHER_CONFCAT_ITEM_DSC,
];

$modversion['configcat']['print'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_PRINT,
    'description' => _MI_PUBLISHER_CONFCAT_PRINT_DSC,
];

$modversion['configcat']['search'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_SEARCH,
    'description' => _MI_PUBLISHER_CONFCAT_SEARCH_DSC,
];

$modversion['configcat']['submit'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_SUBMIT,
    'description' => _MI_PUBLISHER_CONFCAT_SUBMIT_DSC,
];

$modversion['configcat']['permissions'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_PERMISSIONS,
    'description' => _MI_PUBLISHER_CONFCAT_PERMISSIONS_DSC,
];


$modversion['configcat']['archive'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_ARCHIVE,
    'description' => _MI_PUBLISHER_CONFCAT_ARCHIVEDSC,
];

$modversion['configcat']['authorpage'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_AUTHORPAGE,
    'description' => _MI_PUBLISHER_CONFCAT_AUTHORPAGEDSC,
];

$modversion['configcat']['format'] = [
    'name'        => _MI_PUBLISHER_CONFCAT_FORMAT,
    'description' => _MI_PUBLISHER_CONFCAT_FORMAT_DSC,
];
*/

// Config Settings (only for modules that need config settings generated automatically)

################### SEO ####################

//$isModuleAction = (!empty($_POST['fct']) && 'modulesadmin' == $_POST['fct']) ? true : false;
$isModuleAction = ('modulesadmin' === Request::getString('fct', '', 'POST'));
//if ($isModuleAction && (in_array(php_sapi_name(), array('apache', 'apache2handler', 'cgi-fcgi')))) {
//    _MI_PUBLISHER_URL_REWRITE_HTACCESS => 'htaccess'
//}

// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_SEO',
    'description' => '_MI_PUBLISHER_CONFCAT_SEO_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'seo_url_rewrite',
    'title'       => '_MI_PUBLISHER_URL_REWRITE',
    'description' => '_MI_PUBLISHER_URL_REWRITE_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'none',
    'options'     => array_merge(
        [_MI_PUBLISHER_URL_REWRITE_NONE => 'none'],
        [_MI_PUBLISHER_URL_REWRITE_PATHINFO => 'path-info'], // Is performing module install/update?
        ($isModuleAction && in_array(PHP_SAPI, ['apache', 'apache2handler', 'cgi-fcgi', 'fpm-fcgi'], true)) ? [_MI_PUBLISHER_URL_REWRITE_HTACCESS => 'htaccess'] : []
    ),
    'category'    => 'seo',
];

$modversion['config'][] = [
    'name'        => 'seo_module_name',
    'title'       => '_MI_PUBLISHER_SEOMODNAME',
    'description' => '_MI_PUBLISHER_SEOMODNAMEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => $modversion['dirname'],
    'category'    => 'seo',
];
$modversion['config'][] = [
    'name'        => 'seo_meta_keywords',
    'title'       => '_MI_PUBLISHER_SEO_METAKEYWORDS',
    'description' => '_MI_PUBLISHER_SEO_METAKEYWORDS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'seo',
];
################### INDEX & CATEGORY PAGE ####################

// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_INDEX',
    'description' => '_MI_PUBLISHER_CONFCAT_INDEX_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'index_title_and_welcome',
    'title'       => '_MI_PUBLISHER_WELCOME',
    'description' => '_MI_PUBLISHER_WELCOMEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'index',
];

$modversion['config'][] = [
    'name'        => 'index_welcome_msg',
    'title'       => '_MI_PUBLISHER_INDEXMSG',
    'description' => '_MI_PUBLISHER_INDEXMSGDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_PUBLISHER_INDEXMSGDEF,
    'category'    => 'index',
];

$modversion['config'][] = [
    'name'        => 'index_footer',
    'title'       => '_MI_PUBLISHER_INDEXFOOTER',
    'description' => '_MI_PUBLISHER_INDEXFOOTERDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'index',
];

$modversion['config'][] = [
    'name'        => 'cat_list_image_width',
    'title'       => '_MI_PUBLISHER_CATLIST_IMG_W',
    'description' => '_MI_PUBLISHER_CATLIST_IMG_WDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '90',
    'category'    => 'category',
];

$modversion['config'][] = [
    'name'        => 'cat_main_image_width',
    'title'       => '_MI_PUBLISHER_CATMAINIMG_W',
    'description' => '_MI_PUBLISHER_CATMAINIMG_WDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '150',
    'category'    => 'category',
];

$modversion['config'][] = [
    'name'        => 'idxcat_show_subcats',
    'title'       => '_MI_PUBLISHER_SHOW_SUBCATS',
    'description' => '_MI_PUBLISHER_SHOW_SUBCATS_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'all',
    'options'     => [
        _MI_PUBLISHER_SHOW_SUBCATS_NO       => 'no',
        _MI_PUBLISHER_SHOW_SUBCATS_NOTEMPTY => 'nonempty',
        _MI_PUBLISHER_SHOW_SUBCATS_ALL      => 'all',
        _MI_PUBLISHER_SHOW_SUBCATS_NOMAIN   => 'nomain',
    ],
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_subcat_dsc',
    'title'       => '_MI_PUBLISHER_DISSBCATDSC',
    'description' => '_MI_PUBLISHER_DISSBCATDSCDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'cat_display_summary',
    'title'       => '_MI_PUBLISHER_DCS',
    'description' => '_MI_PUBLISHER_DCS_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'category',
];

$modversion['config'][] = [
    'name'        => 'index_disp_subtitle',
    'title'       => '_MI_PUBLISHER_DISP_INDEX_SUB',
    'description' => '_MI_PUBLISHER_DISP_INDEX_SUB_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'index',
];

$modversion['config'][] = [
    'name'        => 'cat_disp_subtitle',
    'title'       => '_MI_PUBLISHER_DISP_CAT_SUB',
    'description' => '_MI_PUBLISHER_DISP_CAT_SUB_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'category',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_art_count',
    'title'       => '_MI_PUBLISHER_ARTCOUNT',
    'description' => '_MI_PUBLISHER_ARTCOUNTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_show_rss_link',
    'title'       => '_MI_PUBLISHER_SHOW_RSS',
    'description' => '_MI_PUBLISHER_SHOW_RSSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_collaps_heading',
    'title'       => '_MI_PUBLISHER_COLLHEAD',
    'description' => '_MI_PUBLISHER_COLLHEADDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_last_item',
    'title'       => '_MI_PUBLISHER_LASTITEM',
    'description' => '_MI_PUBLISHER_LASTITEMDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_last_item_size',
    'title'       => '_MI_PUBLISHER_LASTITSIZE',
    'description' => '_MI_PUBLISHER_LASTITSIZEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '100',
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_cat_perpage',
    'title'       => '_MI_PUBLISHER_CATPERPAGE',
    'description' => '_MI_PUBLISHER_CATPERPAGEDSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 15,
    'options'     => ['5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50],
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_perpage',
    'title'       => '_MI_PUBLISHER_PERPAGE',
    'description' => '_MI_PUBLISHER_PERPAGEDSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 15,
    'options'     => ['5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50],
    'category'    => 'indexcat',
];

$modversion['config'][] = [
    'name'        => 'idxcat_index_perpage',
    'title'       => '_MI_PUBLISHER_PERPAGEINDEX',
    'description' => '_MI_PUBLISHER_PERPAGEINDEXDSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 15,
    'options'     => ['5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50],
    'category'    => 'indexcat',
];

################### LATEST PUBLISHED ARTICLES FOR INDEX AND CATEGORY PAGE  ####################

// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_INDEXCATTEMPLATE',
    'description' => '_MI_PUBLISHER_CONFCAT_INDEXCATTEMPLATEDSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'index_display_last_items',
    'title'       => '_MI_PUBLISHER_LASTITEMS',
    'description' => '_MI_PUBLISHER_LASTITEMSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_items_display_type',
    'title'       => '_MI_PUBLISHER_DISTYPE',
    'description' => '_MI_PUBLISHER_DISTYPEDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => [
        _MI_PUBLISHER_DISPLAYTYPE_SUMMARY   => 'summary',
        _MI_PUBLISHER_DISPLAYTYPE_FULL      => 'full',
        _MI_PUBLISHER_DISPLAYTYPE_LIST      => 'list',
        _MI_PUBLISHER_DISPLAYTYPE_WFSECTION => 'wfsection',
    ],
    'default'     => 'full',
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_date_col',
    'title'       => '_MI_PUBLISHER_DISDATECOL',
    'description' => '_MI_PUBLISHER_DISDATECOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_hits_col',
    'title'       => '_MI_PUBLISHER_HITSCOL',
    'description' => '_MI_PUBLISHER_HITSCOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_mainimage',
    'title'       => '_MI_PUBLISHER_MAINIMAGE',
    'description' => '_MI_PUBLISHER_MAINIMAGEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_summary',
    'title'       => '_MI_PUBLISHER_SUMMARY',
    'description' => '_MI_PUBLISHER_SUMMARYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_readmore',
    'title'       => '_MI_PUBLISHER_READMORE',
    'description' => '_MI_PUBLISHER_READMOREDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_category',
    'title'       => '_MI_PUBLISHER_ARTICLECATEGORY',
    'description' => '_MI_PUBLISHER_ARTICLECATEGORYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_display_poster',
    'title'       => '_MI_PUBLISHER_POSTER',
    'description' => '_MI_PUBLISHER_POSTERDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'indexcattemplate',
];

$modversion['config'][] = [
    'name'        => 'idxcat_disp_commentlink',
    'title'       => '_MI_PUBLISHER_COMMENTLINK',
    'description' => '_MI_PUBLISHER_COMMENTLINKDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'indexcattemplate',
];

################### ITEM PAGE ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_ITEM',
    'description' => '_MI_PUBLISHER_CONFCAT_ITEM_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'item_title_size',
    'title'       => '_MI_PUBLISHER_TITLE_SIZE',
    'description' => '_MI_PUBLISHER_TITLE_SIZEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '100',
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_itemcategory',
    'title'       => '_MI_PUBLISHER_ITEMCATEGORY',
    'description' => '_MI_PUBLISHER_ITEMCATEGORYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_who_link',
    'title'       => '_MI_PUBLISHER_WHO',
    'description' => '_MI_PUBLISHER_WHODSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_when_link',
    'title'       => '_MI_PUBLISHER_WHEN',
    'description' => '_MI_PUBLISHER_WHENDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_hits_link',
    'title'       => '_MI_PUBLISHER_HITS',
    'description' => '_MI_PUBLISHER_HITSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_admin_hits',
    'title'       => '_MI_PUBLISHER_ADMINHITS',
    'description' => '_MI_PUBLISHER_ADMINHITSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_pdf_button',
    'title'       => '_MI_PUBLISHER_DISPLAY_PDF',
    'description' => '_MI_PUBLISHER_DISPLAY_PDF_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_print_link',
    'title'       => '_MI_PUBLISHER_PRINT',
    'description' => '_MI_PUBLISHER_PRINTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_comment_link',
    'title'       => '_MI_PUBLISHER_DISCOM',
    'description' => '_MI_PUBLISHER_DISCOMDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'perm_author_items',
    'title'       => '_MI_PUBLISHER_ALLOW_AUTHOR_ITEMS',
    'description' => '_MI_PUBLISHER_AUTHOR_ITEMS_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

// Number column
$modversion['config'][] = [
    'name'        => 'numb_col',
    'title'       => '_MI_BLOG_NUMB_COL',
    'description' => '_MI_BLOG_NUMB_COL_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 1,
    'options'     => [1 => '1', 2 => '2', 3 => '3', 4 => '4'],
];
// Divide by
$modversion['config'][] = [
    'name'        => 'divideby',
    'title'       => '_MI_BLOG_DIVIDEBY',
    'description' => '_MI_BLOG_DIVIDEBY_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 1,
    'options'     => [1 => '1', 2 => '2', 3 => '3', 4 => '4'],
];
// Table type
$modversion['config'][] = [
    'name'        => 'table_type',
    'title'       => '_MI_BLOG_TABLE_TYPE',
    'description' => '_MI_BLOG_DIVIDEBY_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 'bordered',
    'options'     => ['bordered' => 'bordered', 'striped' => 'striped', 'hover' => 'hover', 'condensed' => 'condensed'],
];

$modversion['config'][] = [
    'name'        => 'item_disp_blocks_summary',
    'title'       => '_MI_PUBLISHER_DISP_BLOCK_SUM',
    'description' => '_MI_PUBLISHER_DISP_BLOCK_SUM_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_subtitle',
    'title'       => '_MI_PUBLISHER_DISP_ITEM_SUB',
    'description' => '_MI_PUBLISHER_DISP_ITEM_SUB_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'maximum_filesize',
    'title'       => '_MI_PUBLISHER_MAX_SIZE',
    'description' => '_MI_PUBLISHER_MAX_SIZEDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '10485760', //10MB
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_disp_defaultimage',
    'title'       => '_MI_PUBLISHER_DEFAULTIMAGE',
    'description' => '_MI_PUBLISHER_DEFAULTIMAGEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'maximum_image_width',
    'title'       => '_MI_PUBLISHER_MAX_WIDTH',
    'description' => '_MI_PUBLISHER_MAX_WIDTHDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '8000',
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'maximum_image_height',
    'title'       => '_MI_PUBLISHER_MAX_HEIGHT',
    'description' => '_MI_PUBLISHER_MAX_HEIGHTDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '8000',
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_footer',
    'title'       => '_MI_PUBLISHER_ITEMFOOTER',
    'description' => '_MI_PUBLISHER_ITEMFOOTERDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'idxcat_partial_view_text',
    'title'       => '_MI_PUBLISHER_PV_TEXT',
    'description' => '_MI_PUBLISHER_PV_TEXTDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_PUBLISHER_PV_TEXT_DEF,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'item_other_items_type',
    'title'       => '_MI_PUBLISHER_OTHERITEMS',
    'description' => '_MI_PUBLISHER_OTHERITEMSDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => [
        _MI_PUBLISHER_OTHER_ITEMS_TYPE_NONE          => 'none',
        _MI_PUBLISHER_OTHER_ITEMS_TYPE_PREVIOUS_NEXT => 'previous_next',
        _MI_PUBLISHER_OTHER_ITEMS_TYPE_ALL           => 'all',
    ],
    'default'     => 'previous_next',
    'category'    => 'item',
];

// ================== Rating/Voting =================
$modversion['config'][] = [
    'name'        => 'rating_voting',
    'title'       => '_MI_PUBLISHER_RATING_VOTING',
    'description' => '_MI_PUBLISHER_RATING_VOTING_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'perm_rating',
    'title'       => '_MI_PUBLISHER_ALLOWRATING',
    'description' => '_MI_PUBLISHER_ALLOWRATING_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'item',
];

$modversion['config'][] = [
    'name'        => 'repeat_rating',
    'title'       => '_MI_PUBLISHER_ALLOW_REPEAT_RATING',
    'description' => '_MI_PUBLISHER_ALLOW_REPEAT_RATING_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'item',
];

// Get groups
/** @var \XoopsMemberHandler $memberHandler */
$memberHandler    = \xoops_getHandler('member');
$xoopsGroups      = $memberHandler->getGroupList();
$ratingbar_groups = [];
foreach ($xoopsGroups as $key => $group) {
    $ratingbar_groups[$group] = $key;
}
// Rating: Groups with rating permissions
$modversion['config'][] = [
    'name'        => 'ratingbar_groups',
    'title'       => '_MI_BLOG_RATINGBAR_GROUPS',
    'description' => '_MI_BLOG_RATINGBAR_GROUPS_DESC',
    'formtype'    => 'select_multi',
    'valuetype'   => 'array',
    'default'     => [1],
    'options'     => $ratingbar_groups,
];
// Rating : used ratingbar
$modversion['config'][] = [
    'name'        => 'ratingbars',
    'title'       => '_MI_BLOG_RATINGBARS',
    'description' => '_MI_BLOG_RATINGBARS_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 1,
    'options'     => ['_MI_BLOG_RATING_NONE' => 0, '_MI_BLOG_RATING_5STARS' => 1, '_MI_BLOG_RATING_10STARS' => 2, '_MI_BLOG_RATING_LIKES' => 3, '_MI_BLOG_RATING_10NUM' => 4, '_MI_BLOG_RATING_REACTION' => 5],
];

################### LATEST ARTICLES IN ARTICLE PAGE  ####################

// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_ITEM_ALLARTICLE',
    'description' => '_MI_PUBLISHER_ITEM_ALLARTICLEDSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_date_col',
    'title'       => '_MI_PUBLISHER_DISDATECOL',
    'description' => '_MI_PUBLISHER_ITEM_DISDATECOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_hits_col',
    'title'       => '_MI_PUBLISHER_HITSCOL',
    'description' => '_MI_PUBLISHER_ITEM_HITSCOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_mainimage',
    'title'       => '_MI_PUBLISHER_MAINIMAGE',
    'description' => '_MI_PUBLISHER_ITEM_MAINIMAGEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_summary',
    'title'       => '_MI_PUBLISHER_SUMMARY',
    'description' => '_MI_PUBLISHER_ITEM_SUMMARYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_readmore',
    'title'       => '_MI_PUBLISHER_READMORE',
    'description' => '_MI_PUBLISHER_ITEM_READMOREDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_category',
    'title'       => '_MI_PUBLISHER_ARTICLECATEGORY',
    'description' => '_MI_PUBLISHER_ITEM_ARTICLECATEGORYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_display_poster',
    'title'       => '_MI_PUBLISHER_POSTER',
    'description' => '_MI_PUBLISHER_ITEM_POSTERDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'itemallarticle',
];

$modversion['config'][] = [
    'name'        => 'allitem_disp_commentlink',
    'title'       => '_MI_PUBLISHER_COMMENTLINK',
    'description' => '_MI_PUBLISHER_ITEM_COMMENTLINKDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'itemallarticle',
];

################### PRINT ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_PRINT',
    'description' => '_MI_PUBLISHER_CONFCAT_PRINT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'print_header',
    'title'       => '_MI_PUBLISHER_HEADERPRINT',
    'description' => '_MI_PUBLISHER_HEADERPRINTDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => '',
    'category'    => 'print',
];

$modversion['config'][] = [
    'name'        => 'print_logourl',
    'title'       => '_MI_PUBLISHER_PRINTLOGOURL',
    'description' => '_MI_PUBLISHER_PRINTLOGOURLDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => XOOPS_URL . '/images/logo.png',
    'category'    => 'print',
];

$modversion['config'][] = [
    'name'        => 'print_footer',
    'title'       => '_MI_PUBLISHER_FOOTERPRINT',
    'description' => '_MI_PUBLISHER_FOOTERPRINTDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'item footer',
    'options'     => [
        _MI_PUBLISHER_ITEMFOOTER_SEL  => 'item footer',
        _MI_PUBLISHER_INDEXFOOTER_SEL => 'index footer',
        _MI_PUBLISHER_BOTH_FOOTERS    => 'both',
        _MI_PUBLISHER_NO_FOOTERS      => 'none',
    ],
    'category'    => 'print',
];

################### ARCHIVE PAGE ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_ARCHIVE',
    'description' => '_MI_PUBLISHER_CONFCAT_ARCHIVEDSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];
$modversion['config'][] = [
    'name'        => 'archive_display_mainimage',
    'title'       => '_MI_PUBLISHER_MAINIMAGE',
    'description' => '_MI_PUBLISHER_ARCHIVE_MAINIMAGEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_summary',
    'title'       => '_MI_PUBLISHER_SUMMARY',
    'description' => '_MI_PUBLISHER_ARCHIVE_SUMMARYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_hits_col',
    'title'       => '_MI_PUBLISHER_HITSCOL',
    'description' => '_MI_PUBLISHER_ARCHIVE_HITSCOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_category',
    'title'       => '_MI_PUBLISHER_ARTICLECATEGORY',
    'description' => '_MI_PUBLISHER_ARCHIVE_ARTICLECATEGORYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_date_col',
    'title'       => '_MI_PUBLISHER_DISDATECOL',
    'description' => '_MI_PUBLISHER_ARCHIVE_DISDATECOLDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_poster',
    'title'       => '_MI_PUBLISHER_POSTER',
    'description' => '_MI_PUBLISHER_ARCHIVE_POSTERDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_comment',
    'title'       => '_MI_PUBLISHER_DISCOM',
    'description' => '_MI_PUBLISHER_ARCHIVE_COMMENTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_printlink',
    'title'       => '_MI_PUBLISHER_PRINT',
    'description' => '_MI_PUBLISHER_ARCHIVE_PRINTLINKDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_pdfbutton',
    'title'       => '_MI_PUBLISHER_DISPLAY_PDF',
    'description' => '_MI_PUBLISHER_ARCHIVE_PDFLINKDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

$modversion['config'][] = [
    'name'        => 'archive_display_emaillink',
    'title'       => '_MI_PUBLISHER_EMAILLINK',
    'description' => '_MI_PUBLISHER_ARCHIVE_EMAILLINKDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'archive',
];

################### ITEM BY SAME AUTHOR PAGE ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_AUTHORPAGE',
    'description' => '_MI_PUBLISHER_CONFCAT_AUTHORPAGEDSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'authorpage_display_image',
    'title'       => '_MI_PUBLISHER_MAINIMAGE',
    'description' => '_MI_PUBLISHER_AUTHORPAGE_IMAGEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'authorpage',
];

$modversion['config'][] = [
    'name'        => 'authorpage_disp_summary',
    'title'       => '_MI_PUBLISHER_SUMMARY',
    'description' => '_MI_PUBLISHER_AUTHORPAGE_SUMMARYDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'authorpage',
];

$modversion['config'][] = [
    'name'        => 'authorpage_display_hits',
    'title'       => '_MI_PUBLISHER_HITSCOL',
    'description' => '_MI_PUBLISHER_AUTHORPAGE_HITSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'authorpage',
];

$modversion['config'][] = [
    'name'        => 'authorpage_disp_comment',
    'title'       => '_MI_PUBLISHER_DISCOM',
    'description' => '_MI_PUBLISHER_AUTHORPAGE_COMMENTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'authorpage',
];

$modversion['config'][] = [
    'name'        => 'authorpage_display_rating',
    'title'       => '_MI_PUBLISHER_DISPRATING',
    'description' => '_MI_PUBLISHER_AUTHORPAGE_RATINGDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'authorpage',
];

################### FORMAT ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_FORMAT',
    'description' => '_MI_PUBLISHER_CONFCAT_FORMAT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'format_date',
    'title'       => '_MI_PUBLISHER_DATEFORMAT',
    'description' => '_MI_PUBLISHER_DATEFORMATDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => 'd M Y g:i A',
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'format_order_by',
    'title'       => '_MI_PUBLISHER_ORDERBY',
    'description' => '_MI_PUBLISHER_ORDERBYDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => [
        _MI_PUBLISHER_ORDERBY_TITLE    => 'title',
        _MI_PUBLISHER_ORDERBY_DATE     => 'date',
        _MI_PUBLISHER_ORDERBY_HITS     => 'counter',
        _MI_PUBLISHER_ORDERBY_RATING   => 'rating',
        _MI_PUBLISHER_ORDERBY_VOTES    => 'votes',
        _MI_PUBLISHER_ORDERBY_COMMENTS => 'comments',
        _MI_PUBLISHER_ORDERBY_WEIGHT   => 'weight',
    ],
    'default'     => 'date',
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'format_image_nav',
    'title'       => '_MI_PUBLISHER_IMAGENAV',
    'description' => '_MI_PUBLISHER_IMAGENAVDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'format_realname',
    'title'       => '_MI_PUBLISHER_USEREALNAME',
    'description' => '_MI_PUBLISHER_USEREALNAMEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'format_highlight_color',
    'title'       => '_MI_PUBLISHER_HLCOLOR',
    'description' => '_MI_PUBLISHER_HLCOLORDSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '#FFFF80',
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'format_linked_path',
    'title'       => '_MI_PUBLISHER_LINKPATH',
    'description' => '_MI_PUBLISHER_LINKPATHDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'display_breadcrumb',
    'title'       => '_MI_PUBLISHER_DISPBREAD',
    'description' => '_MI_PUBLISHER_DISPBREADDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'format',
];

$modversion['config'][] = [
    'name'        => 'format_breadcrumb_modname',
    'title'       => '_MI_PUBLISHER_BCRUMB',
    'description' => '_MI_PUBLISHER_BCRUMBDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'format',
];

################### SEARCH ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_SEARCH',
    'description' => '_MI_PUBLISHER_CONFCAT_SEARCH_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'search_cat_path',
    'title'       => '_MI_PUBLISHER_PATHSEARCH',
    'description' => '_MI_PUBLISHER_PATHSEARCHDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'search',
];

################### SUBMIT ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_SUBMIT',
    'description' => '_MI_PUBLISHER_CONFCAT_SUBMIT_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'submit_intro_msg',
    'title'       => '_MI_PUBLISHER_SUBMITMSG',
    'description' => '_MI_PUBLISHER_SUBMITMSGDSC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_PUBLISHER_SUBMITMSGDEF,
    'category'    => 'submit',
];

xoops_load('XoopsEditorHandler');
$editorHandler = \XoopsEditorHandler::getInstance();

$modversion['config'][] = [
    'name'        => 'submit_editor',
    'title'       => '_MI_PUBLISHER_EDITOR',
    'description' => '_MI_PUBLISHER_EDITOR_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => array_flip($editorHandler->getList()),
    'default'     => 'dhtmltextarea',
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_editor_rows',
    'title'       => '_MI_PUBLISHER_EDITOR_ROWS',
    'description' => '_MI_PUBLISHER_EDITOR_ROWS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '35',
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_editor_cols',
    'title'       => '_MI_PUBLISHER_EDITOR_COLS',
    'description' => '_MI_PUBLISHER_EDITOR_COlS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '60',
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_editor_width',
    'title'       => '_MI_PUBLISHER_EDITOR_WIDTH',
    'description' => '_MI_PUBLISHER_EDITOR_WIDTH_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '100%',
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_editor_height',
    'title'       => '_MI_PUBLISHER_EDITOR_HEIGHT',
    'description' => '_MI_PUBLISHER_EDITOR_HEIGHT_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '400px',
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_status',
    'title'       => '_MI_PUBLISHER_FORM_STATUS',
    'description' => '_MI_PUBLISHER_FORM_STATUS_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => [
        _MI_PUBLISHER_SUBMITTED => Constants::PUBLISHER_STATUS_SUBMITTED,
        _MI_PUBLISHER_PUBLISHED => Constants::PUBLISHER_STATUS_PUBLISHED,
        _MI_PUBLISHER_OFFLINE   => Constants::PUBLISHER_STATUS_OFFLINE,
        _MI_PUBLISHER_REJECTED  => Constants::PUBLISHER_STATUS_REJECTED,
    ],
    'default'     => Constants::PUBLISHER_STATUS_SUBMITTED,
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_allowcomments',
    'title'       => '_MI_PUBLISHER_FORM_ALLOWCOMMENTS',
    'description' => '_MI_PUBLISHER_FORM_ALLOWCOMMENTS_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_dohtml',
    'title'       => '_MI_PUBLISHER_FORM_DOHTML',
    'description' => '_MI_PUBLISHER_FORM_DOHTML_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_dosmiley',
    'title'       => '_MI_PUBLISHER_FORM_DOSMILEY',
    'description' => '_MI_PUBLISHER_FORM_DOSMILEY_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_doxcode',
    'title'       => '_MI_PUBLISHER_FORM_DOXCODE',
    'description' => '_MI_PUBLISHER_FORM_DOXCODE_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_doimage',
    'title'       => '_MI_PUBLISHER_FORM_DOIMAGE',
    'description' => '_MI_PUBLISHER_FORM_DOIMAGE_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit',
];

$modversion['config'][] = [
    'name'        => 'submit_dobr',
    'title'       => '_MI_PUBLISHER_FORM_DOBR',
    'description' => '_MI_PUBLISHER_FORM_DOBR_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'submit',
];

$optCats              = [_MI_PUBLISHER_IMGCAT_ALL => Constants::PUBLISHER_IMGCAT_ALL];
/** @var \XoopsImagecategoryHandler $imageCategoryHandler */
$imageCategoryHandler = xoops_getHandler('imagecategory');
$catlist              = $imageCategoryHandler->getList();
foreach ($catlist as $key => $value) {
    $optCats[$value] = $value;
}
$modversion['config'][] = [
    'name'        => 'submit_imgcat',
    'title'       => '_MI_PUBLISHER_IMGCAT',
    'description' => '_MI_PUBLISHER_IMGCAT_DSC',
    'formtype'    => 'select_multi',
    'valuetype'   => 'array',
    'options'     => $optCats,
    'default'     => [Constants::PUBLISHER_IMGCAT_ALL],
    'category'    => 'submit',
];

################### PERMISSIONS ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_PERMISSIONS',
    'description' => '_MI_PUBLISHER_CONFCAT_PERMISSIONS_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'even',
    'category'    => 'group_header',
];

$modversion['config'][] = [
    'name'        => 'perm_submit',
    'title'       => '_MI_PUBLISHER_ALLOWSUBMIT',
    'description' => '_MI_PUBLISHER_ALLOWSUBMITDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_edit',
    'title'       => '_MI_PUBLISHER_ALLOWEDIT',
    'description' => '_MI_PUBLISHER_ALLOWEDITDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_delete',
    'title'       => '_MI_PUBLISHER_ALLOWDELETE',
    'description' => '_MI_PUBLISHER_ALLOWDELETEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_anon_submit',
    'title'       => '_MI_PUBLISHER_ANONPOST',
    'description' => '_MI_PUBLISHER_ANONPOSTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_upload',
    'title'       => '_MI_PUBLISHER_UPLOAD',
    'description' => '_MI_PUBLISHER_UPLOADDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_clone',
    'title'       => '_MI_PUBLISHER_CLONE',
    'description' => '_MI_PUBLISHER_CLONEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_search',
    'title'       => '_MI_PUBLISHER_ALLOWSEARCH',
    'description' => '_MI_PUBLISHER_ALLOWSEARCH_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_com_art_level',
    'title'       => '_MI_PUBLISHER_COMMENTS',
    'description' => '_MI_PUBLISHER_COMMENTSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
    'category'    => 'permissions',
];

$modversion['config'][] = [
    'name'        => 'perm_autoapprove',
    'title'       => '_MI_PUBLISHER_AUTOAPP',
    'description' => '_MI_PUBLISHER_AUTOAPPDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
    'category'    => 'permissions',
];

################### OTHERS ####################
// group header
$modversion['config'][] = [
    'name'        => 'extrasystems_configs',
    'title'       => '_MI_PUBLISHER_CONFCAT_OTHERS',
    'description' => '_MI_PUBLISHER_CONFCAT_OTHERS_DSC',
    'formtype'    => 'line_break',
    'valuetype'   => 'textbox',
    'default'     => 'odd',
    'category'    => 'group_header',
];

/**
 * Make Sample button visible?
 */
$modversion['config'][] = [
    'name'        => 'displaySampleButton',
    'title'       => '_MI_PUBLISHER_SHOW_SAMPLE_BUTTON',
    'description' => '_MI_PUBLISHER_SHOW_SAMPLE_BUTTON_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];

/**
 * Show Developer Tools?
 */
$modversion['config'][] = [
    'name'        => 'displayDeveloperTools',
    'title'       => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_DEV_TOOLS',
    'description' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_DEV_TOOLS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

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

$modversion['notification']['category'][] = [
    'name'           => 'global',
    'title'          => _MI_PUBLISHER_GLOBAL_ITEM_NOTIFY,
    'description'    => _MI_PUBLISHER_GLOBAL_ITEM_NOTIFY_DSC,
    'subscribe_from' => ['index.php', 'category.php', 'item.php'],
];

$modversion['notification']['category'][] = [
    'name'           => 'category',
    'title'          => _MI_PUBLISHER_CATEGORY_ITEM_NOTIFY,
    'description'    => _MI_PUBLISHER_CATEGORY_ITEM_NOTIFY_DSC,
    'subscribe_from' => ['index.php', 'category.php', 'item.php'],
    'item_name'      => 'categoryid',
    'allow_bookmark' => 1,
];
$modversion['notification']['category'][] = [
    'name'           => 'item',
    'title'          => _MI_PUBLISHER_ITEM_NOTIFY,
    'description'    => _MI_PUBLISHER_ITEM_NOTIFY_DSC,
    'subscribe_from' => ['item.php'],
    'item_name'      => 'itemid',
    'allow_bookmark' => 1,
];

$modversion['notification']['event'][] = [
    'name'          => 'category_created',
    'category'      => 'global',
    'title'         => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY,
    'caption'       => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY_DSC,
    'mail_template' => 'global_item_category_created',
    'mail_subject'  => _MI_PUBLISHER_GLOBAL_ITEM_CATEGORY_CREATED_NOTIFY_SBJ,
];
$modversion['notification']['event'][] = [
    'name'          => 'submitted',
    'category'      => 'global',
    'admin_only'    => 1,
    'title'         => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY,
    'caption'       => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY_DSC,
    'mail_template' => 'global_item_submitted',
    'mail_subject'  => _MI_PUBLISHER_GLOBAL_ITEM_SUBMITTED_NOTIFY_SBJ,
];
$modversion['notification']['event'][] = [
    'name'          => 'published',
    'category'      => 'global',
    'title'         => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY,
    'caption'       => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY_DSC,
    'mail_template' => 'global_item_published',
    'mail_subject'  => _MI_PUBLISHER_GLOBAL_ITEM_PUBLISHED_NOTIFY_SBJ,
];
$modversion['notification']['event'][] = [
    'name'          => 'submitted',
    'category'      => 'category',
    'admin_only'    => 1,
    'title'         => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY,
    'caption'       => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY_DSC,
    'mail_template' => 'category_item_submitted',
    'mail_subject'  => _MI_PUBLISHER_CATEGORY_ITEM_SUBMITTED_NOTIFY_SBJ,
];
$modversion['notification']['event'][] = [
    'name'          => 'published',
    'category'      => 'category',
    'title'         => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY,
    'caption'       => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY_DSC,
    'mail_template' => 'category_item_published',
    'mail_subject'  => _MI_PUBLISHER_CATEGORY_ITEM_PUBLISHED_NOTIFY_SBJ,
];
$modversion['notification']['event'][] = [
    'name'          => 'rejected',
    'category'      => 'item',
    'invisible'     => 1,
    'title'         => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY,
    'caption'       => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY_DSC,
    'mail_template' => 'item_rejected',
    'mail_subject'  => _MI_PUBLISHER_ITEM_REJECTED_NOTIFY_SBJ,
];
$modversion['notification']['event'][] = [
    'name'          => 'approved',
    'category'      => 'item',
    'invisible'     => 1,
    'title'         => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY,
    'caption'       => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY_CAP,
    'description'   => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY_DSC,
    'mail_template' => 'item_approved',
    'mail_subject'  => _MI_PUBLISHER_ITEM_APPROVED_NOTIFY_SBJ,
];
