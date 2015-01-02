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
 * PedigreeBreadcrumb Class
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      XOOPS Development Team
 * @package     Publisher
 * @since       1.03
 * @version     $Id: breadcrumb.php 12277 2014-01-26 01:21:57Z beckmi $
 *
 */

include_once dirname(__DIR__) . '/include/common.php';

//namespace Publisher;


/**
 * Class PublisherUtilities
 */
class PublisherUtilities
{

    /**
     * Function responsible for checking if a directory exists, we can also write in and create an index.html file
     *
     * @param string $folder The full path of the directory to check
     *
     * @return void
     */
    public static function prepareFolder($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0777);
            file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
        }
        chmod($folder, 0777);
    }
}
