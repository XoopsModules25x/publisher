<?php namespace XoopsModules\Publisher;

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
 * @subpackage      Include
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 */

defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * class Constants
 */
class Constants
{
    // ITEM status
    const PUBLISHER_STATUS_NOTSET = -1;
    const PUBLISHER_STATUS_ALL = 0;
    const PUBLISHER_STATUS_SUBMITTED = 1;
    const PUBLISHER_STATUS_PUBLISHED = 2;
    const PUBLISHER_STATUS_OFFLINE = 3;
    const PUBLISHER_STATUS_REJECTED = 4;

    // Notification Events
    const PUBLISHER_NOT_CATEGORY_CREATED = 1;
    const PUBLISHER_NOTIFY_ITEM_SUBMITTED = 2;
    const PUBLISHER_NOTIFY_ITEM_PUBLISHED = 3;
    const PUBLISHER_NOTIFY_ITEM_REJECTED = 4;

    // Form constants
    const PUBLISHER_SUMMARY = 1;
    //const PUBLISHER_DISPLAY_SUMMARY = 2;
    const PUBLISHER_AVAILABLE_PAGE_WRAP = 3;
    const PUBLISHER_ITEM_TAG = 4;
    const PUBLISHER_IMAGE_ITEM = 5;
    //const PUBLISHER_IMAGE_UPLOAD = 6;
    const PUBLISHER_ITEM_UPLOAD_FILE = 7;
    const PUBLISHER_UID = 8;
    const PUBLISHER_DATESUB = 9;
    const PUBLISHER_STATUS = 10;
    const PUBLISHER_ITEM_SHORT_URL = 11;
    const PUBLISHER_ITEM_META_KEYWORDS = 12;
    const PUBLISHER_ITEM_META_DESCRIPTION = 13;
    const PUBLISHER_WEIGHT = 14;
    const PUBLISHER_ALLOWCOMMENTS = 15;
    //const PUBLISHER_PERMISSIONS_ITEM = 16;
    //const PUBLISHER_PARTIAL_VIEW = 17;
    const PUBLISHER_DOHTML = 18;
    const PUBLISHER_DOSMILEY = 19;
    const PUBLISHER_DOXCODE = 20;
    const PUBLISHER_DOIMAGE = 21;
    const PUBLISHER_DOLINEBREAK = 22;
    const PUBLISHER_NOTIFY = 23;
    const PUBLISHER_SUBTITLE = 24;
    const PUBLISHER_AUTHOR_ALIAS = 25;

    // Global constants
    const PUBLISHER_SEARCH = 1;
    const PUBLISHER_RATE = 2;

    // File status
    const PUBLISHER_STATUS_FILE_NOTSET = -1;
    const PUBLISHER_STATUS_FILE_ACTIVE = 1;
    const PUBLISHER_STATUS_FILE_INACTIVE = 2;
}
