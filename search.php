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
 * @subpackage      Action
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use Xmf\Request;
use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

require_once __DIR__ . '/header.php';
xoops_loadLanguage('search');
//Checking general permissions
$configHandler     = xoops_getHandler('config');
$xoopsConfigSearch = $configHandler->getConfigsByCat(XOOPS_CONF_SEARCH);
if (empty($xoopsConfigSearch['enable_search'])) {
    redirect_header(PUBLISHER_URL . '/index.php', 2, _NOPERM);
    //    exit();
}

$groups       = $GLOBALS['xoopsUser'] ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
$gpermHandler = xoops_getModuleHandler('groupperm', PUBLISHER_DIRNAME);
$module_id    = $helper->getModule()->mid();

//Checking permissions
if (!$helper->getConfig('perm_search') || !$gpermHandler->checkRight('global', Constants::PUBLISHER_SEARCH, $groups, $module_id)) {
    redirect_header(PUBLISHER_URL, 2, _NOPERM);
    //    exit();
}

$GLOBALS['xoopsConfig']['module_cache'][$module_id] = 0;
$GLOBALS['xoopsOption']['template_main']            = 'publisher_search.tpl';
include $GLOBALS['xoops']->path('header.php');

$module_info_search = $helper->getModule()->getInfo('search');
require_once PUBLISHER_ROOT_PATH . '/' . $module_info_search['file'];

$limit    = 10; //$helper->getConfig('idxcat_perpage');
$uid      = 0;
$queries  = [];
$andor    = Request::getString('andor', '', 'POST');
$start    = Request::getInt('start', 0, 'POST');
$category = Request::getArray('category', [], 'POST');
$username = Request::getString('uname', '', 'POST');
$searchin = Request::getArray('searchin', [], 'POST');
$sortby   = Request::getString('sortby', '', 'POST');
$term     = Request::getString('term', '', 'POST');

if (empty($category) || (is_array($category) && in_array('all', $category))) {
    $category = [];
} else {
    $category = !is_array($category) ? explode(',', $category) : $category;
    $category = array_map('intval', $category);
}

$andor  = in_array(strtoupper($andor), ['OR', 'AND', 'EXACT']) ? strtoupper($andor) : 'OR';
$sortby = in_array(strtolower($sortby), ['itemid', 'datesub', 'title', 'categoryid']) ? strtolower($sortby) : 'itemid';

if ($term && 'none' !== Request::getString('submit', 'none', 'POST')) {
    $next_search['category'] = implode(',', $category);
    $next_search['andor']    = $andor;
    $next_search['term']     = $term;
    $query                   = trim($term);

    if ('EXACT' !== $andor) {
        $ignored_queries = []; // holds keywords that are shorter than allowed minimum length
        $temp_queries    = preg_split("/[\s,]+/", $query);
        foreach ($temp_queries as $q) {
            $q = trim($q);
            if (strlen($q) >= $xoopsConfigSearch['keyword_min']) {
                $queries[] = $myts->addSlashes($q);
            } else {
                $ignored_queries[] = $myts->addSlashes($q);
            }
        }
        //        unset($q);
        if (0 == count($queries)) {
            redirect_header(PUBLISHER_URL . '/search.php', 2, sprintf(_SR_KEYTOOSHORT, $xoopsConfigSearch['keyword_min']));
            //            exit();
        }
    } else {
        if (strlen($query) < $xoopsConfigSearch['keyword_min']) {
            redirect_header(PUBLISHER_URL . '/search.php', 2, sprintf(_SR_KEYTOOSHORT, $xoopsConfigSearch['keyword_min']));
            //            exit();
        }
        $queries = [$myts->addSlashes($query)];
    }

    $uname_required       = false;
    $search_username      = trim($username);
    $next_search['uname'] = $search_username;
    if (!empty($search_username)) {
        $uname_required  = true;
        $search_username = $myts->addSlashes($search_username);
        if (!$result = $GLOBALS['xoopsDB']->query('SELECT uid FROM ' . $GLOBALS['xoopsDB']->prefix('users') . ' WHERE uname LIKE ' . $GLOBALS['xoopsDB']->quoteString("%$search_username%"))) {
            redirect_header(PUBLISHER_URL . '/search.php', 1, _CO_PUBLISHER_ERROR);
            //            exit();
        }
        $uid = [];
        while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
            $uid[] = $row['uid'];
        }
    } else {
        $uid = 0;
    }

    $next_search['sortby']   = $sortby;
    $next_search['searchin'] = implode('|', $searchin);

    $extra = '';
    if (!empty($time)) {
        $extra = '';
    }

    if ($uname_required && (!$uid || count($uid) < 1)) {
        $results = [];
    } else {
        $results = $module_info_search['func']($queries, $andor, $limit, $start, $uid, $category, $sortby, $searchin, $extra);
    }

    if (count($results) < 1) {
        $results[] = ['text' => _SR_NOMATCH];
    }

    $xoopsTpl->assign('results', $results);

    if (count($next_search) > 0) {
        $items = [];
        foreach ($next_search as $para => $val) {
            if (!empty($val)) {
                $items[] = "{$para}={$val}";
            }
        }
        if (count($items) > 0) {
            $paras = implode('&', $items);
        }
        unset($next_search, $para, $val, $items);
    }
    $search_url = PUBLISHER_URL . '/search.php?' . $paras;

    if (count($results)) {
        $next            = $start + $limit;
        $queries         = implode(',', $queries);
        $search_url_next = $search_url . "&start={$next}";
        $search_next     = '<a href="' . htmlspecialchars($search_url_next) . '">' . _SR_NEXT . '</a>';
        $xoopsTpl->assign('search_next', $search_next);
    }
    if ($start > 0) {
        $prev            = $start - $limit;
        $search_url_prev = $search_url . "&start={$prev}";
        $search_prev     = '<a href="' . htmlspecialchars($search_url_prev) . '">' . _SR_PREVIOUS . '</a>';
        $xoopsTpl->assign('search_prev', $search_prev);
    }

    unset($results);
    $search_info = _SR_KEYWORDS . ': ' . $myts->htmlSpecialChars($term);
    if ($uname_required) {
        if ($search_info) {
            $search_info .= '<br>';
        }
        $search_info .= _CO_PUBLISHER_UID . ': ' . $myts->htmlSpecialChars($search_username);
    }
    $xoopsTpl->assign('search_info', $search_info);
}

/* type */
$typeSelect = '<select name="andor">';
$typeSelect .= '<option value="OR"';
if ('OR' === $andor) {
    $typeSelect .= ' selected="selected"';
}
$typeSelect .= '>' . _SR_ANY . '</option>';
$typeSelect .= '<option value="AND"';
if ('AND' === $andor) {
    $typeSelect .= ' selected="selected"';
}
$typeSelect .= '>' . _SR_ALL . '</option>';
$typeSelect .= '<option value="EXACT"';
if ('EXACT' === $andor) {
    $typeSelect .= ' selected="selected"';
}
$typeSelect .= '>' . _SR_EXACT . '</option>';
$typeSelect .= '</select>';

/* category */
$categories = $helper->getHandler('Category')->getCategoriesForSearch();

$categorySelect = '<select name="category[]" size="5" multiple="multiple">';
$categorySelect .= '<option value="all"';
if (empty($category) || 0 == count($category)) {
    $categorySelect .= 'selected="selected"';
}
$categorySelect .= '>' . _ALL . '</option>';
foreach ($categories as $id => $cat) {
    $categorySelect .= '<option value="' . $id . '"';
    if (in_array($id, $category)) {
        $categorySelect .= 'selected="selected"';
    }
    $categorySelect .= '>' . $cat . '</option>';
}
unset($id, $cat);
$categorySelect .= '</select>';

/* scope */
$searchSelect = '';
$searchSelect .= '<input type="checkbox" name="searchin[]" value="title"';
if (in_array('title', $searchin)) {
    $searchSelect .= ' checked';
}
$searchSelect .= '>' . _CO_PUBLISHER_TITLE . '&nbsp;&nbsp;';
$searchSelect .= '<input type="checkbox" name="searchin[]" value="subtitle"';
if (in_array('subtitle', $searchin)) {
    $searchSelect .= ' checked';
}
$searchSelect .= '>' . _CO_PUBLISHER_SUBTITLE . '&nbsp;&nbsp;';
$searchSelect .= '<input type="checkbox" name="searchin[]" value="summary"';
if (in_array('summary', $searchin)) {
    $searchSelect .= ' checked';
}
$searchSelect .= '>' . _CO_PUBLISHER_SUMMARY . '&nbsp;&nbsp;';
$searchSelect .= '<input type="checkbox" name="searchin[]" value="text"';
if (in_array('body', $searchin)) {
    $searchSelect .= ' checked';
}
$searchSelect .= '>' . _CO_PUBLISHER_BODY . '&nbsp;&nbsp;';
$searchSelect .= '<input type="checkbox" name="searchin[]" value="keywords"';
if (in_array('meta_keywords', $searchin)) {
    $searchSelect .= ' checked';
}
$searchSelect .= '>' . _CO_PUBLISHER_ITEM_META_KEYWORDS . '&nbsp;&nbsp;';
$searchSelect .= '<input type="checkbox" name="searchin[]" value="all"';
if (empty($searchin) || in_array('all', $searchin)) {
    $searchSelect .= ' checked';
}
$searchSelect .= '>' . _ALL . '&nbsp;&nbsp;';

/* sortby */
$sortbySelect = '<select name="sortby">';
$sortbySelect .= '<option value="itemid"';
if ('itemid' === $sortby || empty($sortby)) {
    $sortbySelect .= ' selected="selected"';
}
$sortbySelect .= '>' . _NONE . '</option>';
$sortbySelect .= '<option value="datesub"';
if ('datesub' === $sortby) {
    $sortbySelect .= ' selected="selected"';
}
$sortbySelect .= '>' . _CO_PUBLISHER_DATESUB . '</option>';
$sortbySelect .= '<option value="title"';
if ('title' === $sortby) {
    $sortbySelect .= ' selected="selected"';
}
$sortbySelect .= '>' . _CO_PUBLISHER_TITLE . '</option>';
$sortbySelect .= '<option value="categoryid"';
if ('categoryid' === $sortby) {
    $sortbySelect .= ' selected="selected"';
}
$sortbySelect .= '>' . _CO_PUBLISHER_CATEGORY . '</option>';
$sortbySelect .= '</select>';

$xoopsTpl->assign('type_select', $typeSelect);
$xoopsTpl->assign('searchin_select', $searchSelect);
$xoopsTpl->assign('category_select', $categorySelect);
$xoopsTpl->assign('sortby_select', $sortbySelect);
$xoopsTpl->assign('search_term', htmlspecialchars($term, ENT_QUOTES));
$xoopsTpl->assign('search_user', $username);

$xoopsTpl->assign('modulename', $helper->getModule()->name());
$xoopsTpl->assign('module_dirname', $helper->getDirname());

if ($xoopsConfigSearch['keyword_min'] > 0) {
    $xoopsTpl->assign('search_rule', sprintf(_SR_KEYIGNORE, $xoopsConfigSearch['keyword_min']));
}

include $GLOBALS['xoops']->path('footer.php');
