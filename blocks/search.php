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
 * @subpackage      Blocks
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          phppp
 */

use Xmf\Request;
use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_search_show($options)
{
    $block      = [];
    $helper  = Publisher\Helper::getInstance();
    $categories = $helper->getHandler('Category')->getCategoriesForSearch();
    if (0 == count($categories)) {
        return $block;
    }

    xoops_loadLanguage('search');

    $andor    = Request::getString('andor', Request::getString('andor', '', 'GET'), 'POST');
    $username = Request::getString('uname', Request::getString('uname', null, 'GET'), 'POST');
    //  $searchin = isset($_POST["searchin"]) ? $_POST["searchin"] : (isset($_GET["searchin"]) ? explode("|", $_GET["searchin"]) : array());
    //  $searchin = Request::getArray('searchin', (explode("|", Request::getString('searchin', array(), 'GET'))), 'POST');

    $searchin = Request::getArray('searchin', '', 'POST');
    if (!isset($searchin)) {
        $searchin = Request::getString('searchin', [], 'GET');
        $searchin = isset($searchin) ? explode('|', $searchin) : [];
    }

    $sortby = Request::getString('sortby', Request::getString('sortby', null, 'GET'), 'POST');
    $term   = Request::getString('term', Request::getString('term', '', 'GET'));

    //mb TODO simplify next lines with category
    $category = Request::getArray('category', [], 'POST') ?: Request::getArray('category', null, 'GET');
    if (empty($category) || (is_array($category) && in_array('all', $category))) {
        $category = [];
    } else {
        $category = (!is_array($category)) ? explode(',', $category) : $category;
        $category = array_map('intval', $category);
    }

    $andor  = in_array(strtoupper($andor), ['OR', 'AND', 'EXACT']) ? strtoupper($andor) : 'OR';
    $sortby = in_array(strtolower($sortby), ['itemid', 'datesub', 'title', 'categoryid']) ? strtolower($sortby) : 'itemid';

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
    if ('exact' === $andor) {
        $typeSelect .= ' selected="selected"';
    }
    $typeSelect .= '>' . _SR_EXACT . '</option>';
    $typeSelect .= '</select>';

    /* category */

    $categorySelect = '<select name="category[]" size="5" multiple="multiple" width="150" style="width:150px;">';
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
    if (is_array($searchin) && in_array('title', $searchin)) {
        $searchSelect .= ' checked';
    }
    $searchSelect .= '>' . _CO_PUBLISHER_TITLE . '&nbsp;&nbsp;';
    $searchSelect .= '<input type="checkbox" name="searchin[]" value="subtitle"';
    if (is_array($searchin) && in_array('subtitle', $searchin)) {
        $searchSelect .= ' checked';
    }
    $searchSelect .= '>' . _CO_PUBLISHER_SUBTITLE . '&nbsp;&nbsp;';
    $searchSelect .= '<input type="checkbox" name="searchin[]" value="summary"';
    if (is_array($searchin) && in_array('summary', $searchin)) {
        $searchSelect .= ' checked';
    }
    $searchSelect .= '>' . _CO_PUBLISHER_SUMMARY . '&nbsp;&nbsp;';
    $searchSelect .= '<input type="checkbox" name="searchin[]" value="text"';
    if (is_array($searchin) && in_array('body', $searchin)) {
        $searchSelect .= ' checked';
    }
    $searchSelect .= '>' . _CO_PUBLISHER_BODY . '&nbsp;&nbsp;';
    $searchSelect .= '<input type="checkbox" name="searchin[]" value="keywords"';
    if (is_array($searchin) && in_array('meta_keywords', $searchin)) {
        $searchSelect .= ' checked';
    }
    $searchSelect .= '>' . _CO_PUBLISHER_ITEM_META_KEYWORDS . '&nbsp;&nbsp;';
    $searchSelect .= '<input type="checkbox" name="searchin[]" value="all"';
    if (empty($searchin) || (is_array($searchin) && in_array('all', $searchin))) {
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

    $block['typeSelect']     = $typeSelect;
    $block['searchSelect']   = $searchSelect;
    $block['categorySelect'] = $categorySelect;
    $block['sortbySelect']   = $sortbySelect;
    $block['search_term']    = htmlspecialchars($term, ENT_QUOTES);
    $block['search_user']    = $username;
    $block['publisher_url']  = PUBLISHER_URL;

    return $block;
}
