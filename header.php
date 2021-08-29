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
 */

use XoopsModules\Publisher\{Helper
};

/** @var Helper $helper
 * {@internal $helper defined in ./include/common.php }}
 */

require_once \dirname(__DIR__, 2) . '/mainfile.php';
require_once __DIR__ . '/include/common.php';

$myts = \MyTextSanitizer::getInstance();
if ('none' !== $helper->getConfig('seo_url_rewrite')) {
    require_once $helper->path('include/seo.inc.php');
}

$modPathIcon16 = $GLOBALS['xoopsModule']->getInfo('modicons16');
$modPathIcon32 = $GLOBALS['xoopsModule']->getInfo('modicons16');

if (!isset($GLOBALS['xoTheme']) || !is_object($GLOBALS['xoTheme'])) {
    require_once $GLOBALS['xoops']->path('/class/theme.php');
    $GLOBALS['xoTheme'] = new \xos_opal_Theme();
}
