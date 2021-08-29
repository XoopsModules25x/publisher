<?php

declare(strict_types=1);
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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use Xmf\Module\Admin;
use XoopsModules\Publisher\Helper;
use XoopsModules\Publisher\Common\Configurator;

require \dirname(__DIR__) . '/preloads/autoloader.php';

require \dirname(__DIR__, 3) . '/include/cp_header.php';
//require_once $GLOBALS['xoops']->path('www/class/xoopsformloader.php');

require_once \dirname(__DIR__) . '/include/common.php';

$moduleDirName = \basename(\dirname(__DIR__));

$helper = Helper::getInstance();
/** @var Admin $adminObject */
$adminObject = Admin::getInstance();

$pathIcon16 = Admin::iconUrl('', '16');
$pathIcon32 = Admin::iconUrl('', '32');
$pathModIcon32 = XOOPS_URL .   '/modules/' . $moduleDirName . '/assets/images/icons/32/';
if (is_object($helper->getModule()) && false !== $helper->getModule()->getInfo('modicons32')) {
    $pathModIcon32 = $helper->url($helper->getModule()->getInfo('modicons32'));
}

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');

$configurator = new Configurator();
$icons = $configurator->icons;

$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $GLOBALS['xoopsTpl'] = new \XoopsTpl();
}
