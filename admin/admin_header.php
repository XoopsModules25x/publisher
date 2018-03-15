<?php
/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */


use XoopsModules\Publisher;

require_once __DIR__ . '/../../../include/cp_header.php';
//require_once $GLOBALS['xoops']->path('www/class/xoopsformloader.php');

include __DIR__ . '/../preloads/autoloader.php';
require_once __DIR__ . '/../include/common.php';

$moduleDirName = basename(dirname(__DIR__));

/** @var Publisher\Helper $helper */
$helper = Publisher\Helper::getInstance();
/** @var Xmf\Module\Admin $adminObject */
$adminObject = \Xmf\Module\Admin::getInstance();

$pathIcon16    = \Xmf\Module\Admin::iconUrl('', 16);
$pathIcon32    = \Xmf\Module\Admin::iconUrl('', 32);
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');

$imagearray = [
    'editimg'   => "<img src='" . PUBLISHER_IMAGES_URL . "/button_edit.png' alt='" . _AM_PUBLISHER_ICO_EDIT . "' align='middle'>",
    'deleteimg' => "<img src='" . PUBLISHER_IMAGES_URL . "/button_delete.png' alt='" . _AM_PUBLISHER_ICO_DELETE . "' align='middle'>",
    'online'    => "<img src='" . PUBLISHER_IMAGES_URL . "/on.png' alt='" . _AM_PUBLISHER_ICO_ONLINE . "' align='middle'>",
    'offline'   => "<img src='" . PUBLISHER_IMAGES_URL . "/off.png' alt='" . _AM_PUBLISHER_ICO_OFFLINE . "' align='middle'>"
];

$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $GLOBALS['xoopsTpl'] = new \XoopsTpl();
}
