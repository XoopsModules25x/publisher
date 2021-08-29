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
 * Publisher
 *
 * @copyright    The XOOPS Project (https://xoops.org)
 * @license      GNU GPL (https://www.gnu.org/licenses/gpl-2.0.html/)
 * @since        1.0
 * @author       Mage, Mamba
 */

use Xmf\Module\Admin;

/** @var Admin $adminObject */

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

$adminObject->displayNavigation(basename(__FILE__));
$adminObject::setPaypal('xoopsfoundation@gmail.com');
$adminObject->displayAbout(false);

require_once __DIR__ . '/admin_footer.php';
