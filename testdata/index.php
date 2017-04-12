<?php
/**
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package
 * @since           2.5.9
 * @author          Michael Beck (aka Mamba)
 */

include_once __DIR__ . '/../../../mainfile.php';

if (!isset($moduleDirName)) {
    $moduleDirName = basename(dirname(__DIR__));
}

$op = \Xmf\Request::getCmd('op', '');

switch ($op) {
    case 'load':
        loadSampleData();
        break;
}

// XMF TableLoad for SAMPLE data

function loadSampleData()
{
    xoops_loadLanguage('admin', $moduleDirName);
    $items = \Xmf\Yaml::readWrapped('item-data.yml');
    $cat = \Xmf\Yaml::readWrapped('cat-data.yml');

    \Xmf\Database\TableLoad::truncateTable('publisher_items');
    \Xmf\Database\TableLoad::truncateTable('publisher_categories');

    \Xmf\Database\TableLoad::loadTableFromArray('publisher_categories', $cat);
    \Xmf\Database\TableLoad::loadTableFromArray('publisher_items', $items);

    redirect_header( '../admin/item.php', 1, _AM_PUBLISHER_SAMPLEDATA_SUCCESS);
}
