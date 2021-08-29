<?php

declare(strict_types=1);

namespace XoopsModules\Publisher;

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
use XoopsModules\Publisher\{
    Form
};
/** @var \XoopsMemberHandler $memberHandler */
/** @var \XoopsImageHandler $imageHandler */


require_once \dirname(__DIR__) . '/include/common.php';

/**
 * Class Item
 */
class Item extends \XoopsObject
{
    public const PAGEWRAP = '[pagewrap=';
    public const BODYTAG = '<body>';
    /**
     * @var Helper
     */
    public $helper;
    /** @var \XoopsMySQLDatabase */
    public $db;
    public $groupsRead = [];
    /**
     * @var Category
     */
    public $category;

    /**
     * @param int|null $id
     */
    public function __construct($id = null)
    {
//        $this->helper = Helper::getInstance();
        $this->db     = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->initVar('itemid', \XOBJ_DTYPE_INT, 0);
        $this->initVar('categoryid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('title', \XOBJ_DTYPE_TXTBOX, '', true, 255);
        $this->initVar('subtitle', \XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('summary', \XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('body', \XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('author_alias', \XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('datesub', \XOBJ_DTYPE_INT, '', false);
        $this->initVar('dateexpire', \XOBJ_DTYPE_INT, '', false);
        $this->initVar('status', \XOBJ_DTYPE_INT, -1, false);
        $this->initVar('image', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('images', \XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('counter', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('rating', \XOBJ_DTYPE_OTHER, 0, false);
        $this->initVar('votes', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('weight', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('dohtml', \XOBJ_DTYPE_INT, 1, true);
        $this->initVar('dosmiley', \XOBJ_DTYPE_INT, 1, true);
        $this->initVar('doimage', \XOBJ_DTYPE_INT, 1, true);
        $this->initVar('dobr', \XOBJ_DTYPE_INT, 1, false);
        $this->initVar('doxcode', \XOBJ_DTYPE_INT, 1, true);
        $this->initVar('cancomment', \XOBJ_DTYPE_INT, 1, true);
        $this->initVar('comments', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('notifypub', \XOBJ_DTYPE_INT, 1, false);
        $this->initVar('meta_keywords', \XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('meta_description', \XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('short_url', \XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('item_tag', \XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('votetype', \XOBJ_DTYPE_INT, 0, false);
        // Non consistent values
        $this->initVar('pagescount', \XOBJ_DTYPE_INT, 0, false);
        if (null !== $id) {
            $item = $this->helper->getHandler('Item')->get($id);
            foreach ($item->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $arg = $args[0] ?? null;

        return $this->getVar($method, $arg);
    }

    /**
     * @return null|Category
     */
    public function getCategory()
    {
        if (null === $this->category) {
            $this->category = $this->helper->getHandler('Category')->get($this->getVar('categoryid'));
        }

        return $this->category;
    }

    /**
     * @param int    $maxLength
     * @param string $format
     *
     * @return mixed|string
     */
    public function getTitle($maxLength = 0, $format = 'S')
    {
        $ret = $this->getVar('title', $format);
        if (0 != $maxLength) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (\mb_strlen($ret) >= $maxLength) {
                    $ret = Utility::substr($ret, 0, $maxLength);
                }
            }
        }

        return $ret;
    }

    /**
     * @param int    $maxLength
     * @param string $format
     *
     * @return mixed|string
     */
    public function getSubtitle($maxLength = 0, $format = 'S')
    {
        $ret = $this->getVar('subtitle', $format);
        if (0 != $maxLength) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (\mb_strlen($ret) >= $maxLength) {
                    $ret = Utility::substr($ret, 0, $maxLength);
                }
            }
        }

        return $ret;
    }

    /**
     * @param int    $maxLength
     * @param string $format
     * @param string $stripTags
     *
     * @return mixed|string
     */
    public function getSummary($maxLength = 0, $format = 'S', $stripTags = '')
    {
        $ret = $this->getVar('summary', $format);
        if (!empty($stripTags)) {
            $ret = \strip_tags($ret, $stripTags);
        }
        if (0 != $maxLength) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (\mb_strlen($ret) >= $maxLength) {
                    //$ret = Utility::substr($ret , 0, $maxLength);
                    //                    $ret = Utility::truncateTagSafe($ret, $maxLength, $etc = '...', $breakWords = false);
                    $ret = Utility::truncateHtml($ret, $maxLength, $etc = '...', $breakWords = false);
                }
            }
        }

        return $ret;
    }

    /**
     * @param int  $maxLength
     * @param bool $fullSummary
     *
     * @return mixed|string
     */
    public function getBlockSummary($maxLength = 0, $fullSummary = false)
    {
        if ($fullSummary) {
            $ret = $this->getSummary(0, 's', '<br><br>');
        } else {
            $ret = $this->getSummary($maxLength, 's', '<br><br>');
        }
        //no summary? get body!
        if ('' === $ret) {
            $ret = $this->getBody($maxLength, 's', '<br><br>');
        }

        return $ret;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function wrapPage($fileName)
    {
        $content = '';
        $page    = Utility::getUploadDir(true, 'content') . $fileName;
        if (\is_file($page)) {
            // this page uses smarty template
            \ob_start();
            require $page;
            $content = \ob_get_clean();
            // Cleaning the content
            $bodyStartPos = \mb_strpos($content, self::BODYTAG);
            if ($bodyStartPos) {
                $bodyEndPos = \mb_strpos($content, '</body>', $bodyStartPos);
                $content    = \mb_substr($content, $bodyStartPos + \mb_strlen(self::BODYTAG), $bodyEndPos - \mb_strlen(self::BODYTAG) - $bodyStartPos);
            }
            // Check if ML Hack is installed, and if yes, parse the $content in formatForML
            $myts = \MyTextSanitizer::getInstance();
            if (\method_exists($myts, 'formatForML')) {
                $content = $myts->formatForML($content);
            }
        }

        return $content;
    }

    /**
     * This method returns the body to be displayed. Not to be used for editing
     *
     * @param int    $maxLength
     * @param string $format
     * @param string $stripTags
     *
     * @return mixed|string
     */
    public function getBody($maxLength = 0, $format = 'S', $stripTags = '')
    {
        $ret     = $this->getVar('body', $format);
        $wrapPos = \mb_strpos($ret, self::PAGEWRAP);
        if (!(false === $wrapPos)) {
            $wrapPages      = [];
            $wrapCodeLength = \mb_strlen(self::PAGEWRAP);
            while (!(false === $wrapPos)) {
                $endWrapPos = \mb_strpos($ret, ']', $wrapPos);
                if ($endWrapPos) {
                    $wrapPagename = \mb_substr($ret, $wrapPos + $wrapCodeLength, $endWrapPos - $wrapCodeLength - $wrapPos);
                    $wrapPages[]  = $wrapPagename;
                }
                $wrapPos = \mb_strpos($ret, self::PAGEWRAP, $endWrapPos - 1);
            }
            foreach ($wrapPages as $page) {
                $wrapPageContent = $this->wrapPage($page);
                $ret             = \str_replace("[pagewrap={$page}]", $wrapPageContent, $ret);
            }
        }
        if ($this->helper->getConfig('item_disp_blocks_summary')) {
            $summary = $this->getSummary($maxLength, $format, $stripTags);
            if ($summary) {
                $ret = $this->getSummary() . '<br><br>' . $ret;
            }
        }
        if (!empty($stripTags)) {
            $ret = \strip_tags($ret, $stripTags);
        }
        if (0 != $maxLength) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (\mb_strlen($ret) >= $maxLength) {
                    //$ret = Utility::substr($ret , 0, $maxLength);
                    $ret = Utility::truncateHtml($ret, $maxLength, $etc = '...', $breakWords = false);
                }
            }
        }

        return $ret;
    }

    /**
     * @param string $dateFormat
     * @param string $format
     *
     * @return string
     */
    public function getDatesub($dateFormat = '', $format = 'S')
    {
        if (empty($dateFormat)) {
            $dateFormat = $this->helper->getConfig('format_date');
        }

        return \formatTimestamp($this->getVar('datesub', $format), $dateFormat);
    }

    /**
     * @param string $dateFormat
     * @param string $format
     *
     * @return string|false
     */
    public function getDateExpire($dateFormat = '', $format = 'S')
    {
        if (empty($dateFormat)) {
            $dateFormat = $this->helper->getConfig('format_date');
        }
        if (0 == $this->getVar('dateexpire')) {
            return false;
        }

        return \formatTimestamp($this->getVar('dateexpire', $format), $dateFormat);
    }

    /**
     * @param int $realName
     *
     * @return string
     */
    public function posterName($realName = -1)
    {
        \xoops_load('XoopsUserUtility');
        if (-1 == $realName) {
            $realName = $this->helper->getConfig('format_realname');
        }
        $ret = $this->author_alias();
        if ('' == $ret) {
            $ret = \XoopsUserUtility::getUnameFromId($this->uid(), $realName);
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function posterAvatar()
    {
        $ret           = 'blank.gif';
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        $thisUser      = $memberHandler->getUser($this->uid());
        if (\is_object($thisUser)) {
            $ret = $thisUser->getVar('user_avatar');
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function getLinkedPosterName()
    {
        \xoops_load('XoopsUserUtility');
        $ret = $this->author_alias();
        if ('' === $ret) {
            $ret = \XoopsUserUtility::getUnameFromId($this->uid(), $this->helper->getConfig('format_realname'), true);
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function updateCounter()
    {
        return $this->helper->getHandler('Item')->updateCounter($this->itemid());
    }

    /**
     * @param bool $force
     *
     * @return bool
     */
    public function store($force = true)
    {
        $isNew = $this->isNew();
        if (!$this->helper->getHandler('Item')->insert($this, $force)) {
            return false;
        }
        if ($isNew && Constants::PUBLISHER_STATUS_PUBLISHED == $this->getVar('status')) {
            // Increment user posts
            $userHandler   = \xoops_getHandler('user');
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = \xoops_getHandler('member');
            $poster        = $userHandler->get($this->uid());
            if (\is_object($poster) && !$poster->isNew()) {
                $poster->setVar('posts', $poster->getVar('posts') + 1);
                if (!$memberHandler->insertUser($poster, true)) {
                    $this->setErrors('Article created but could not increment user posts.');

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->name();
    }

    /**
     * @return string
     */
    public function getCategoryUrl()
    {
        return $this->getCategory()->getCategoryUrl();
    }

    /**
     * @return string
     */
    public function getCategoryLink()
    {
        return $this->getCategory()->getCategoryLink();
    }

    /**
     * @param bool $withAllLink
     *
     * @return array|bool|string
     */
    public function getCategoryPath($withAllLink = true)
    {
        return $this->getCategory()->getCategoryPath($withAllLink);
    }

    /**
     * @return string
     */
    public function getCategoryImagePath()
    {
        return Utility::getImageDir('category', false) . $this->getCategory()->getImage();
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->helper->getHandler('File')->getAllFiles($this->itemid(), Constants::PUBLISHER_STATUS_FILE_ACTIVE);
    }

    /**
     * @param $icons
     * @return string
     */
    public function getAdminLinks($icons)
    {
        $adminLinks = '';
        if (\is_object($GLOBALS['xoopsUser'])
            && (Utility::userIsAdmin() || Utility::userIsAuthor($this)
                || $this->helper->getHandler('Permission')->isGranted('item_submit', $this->categoryid()))) {
            if (Utility::userIsAdmin() || Utility::userIsAuthor($this) || Utility::userIsModerator($this)) {
                if ($this->helper->getConfig('perm_edit') || Utility::userIsModerator($this) || Utility::userIsAdmin()) {
                    // Edit button
                    $adminLinks .= "<a href='" . PUBLISHER_URL . '/submit.php?itemid=' . $this->itemid() . "'>" . $icons['edit'] . '</a>';
                    $adminLinks .= ' ';
                }
                if ($this->helper->getConfig('perm_delete') || Utility::userIsModerator($this) || Utility::userIsAdmin()) {
                    // Delete button
                    $adminLinks .= "<a href='" . PUBLISHER_URL . '/submit.php?op=del&amp;itemid=' . $this->itemid() . "'>" . $icons['delete'] . '</a>';
                    $adminLinks .= ' ';
                }
            }
            if ($this->helper->getConfig('perm_clone') || Utility::userIsModerator($this) || Utility::userIsAdmin()) {
                // Duplicate button
                $adminLinks .= "<a href='" . PUBLISHER_URL . '/submit.php?op=clone&amp;itemid=' . $this->itemid() . "'>" . $icons['clone'] . '</a>';
                $adminLinks .= ' ';
            }
        }

        return $adminLinks;
    }

    /**
     * @param $icons
     * @return string
     */
    public function getPdfButton($icons)
    {
        $pdfButton = '';
        // PDF button
        if (\is_file(XOOPS_ROOT_PATH . '/class/libraries/vendor/tecnickcom/tcpdf/tcpdf.php')) {
            $pdfButton .= "<a href='" . PUBLISHER_URL . '/makepdf.php?itemid=' . $this->itemid() . "' rel='nofollow' target='_blank'>" . $icons['pdf'] . '</a>&nbsp;';
            $pdfButton .= ' ';
        } else {
            //                if (is_object($GLOBALS['xoopsUser']) && Utility::userIsAdmin()) {
            //                    $GLOBALS['xoTheme']->addStylesheet('/modules/system/css/jquery.jgrowl.min.css');
            //                    $GLOBALS['xoTheme']->addScript('browse.php?Frameworks/jquery/plugins/jquery.jgrowl.js');
            //                    $adminLinks .= '<script type="text/javascript">
            //                    (function($){
            //                        $(document).ready(function(){
            //                            $.jGrowl("' . _MD_PUBLISHER_ERROR_NO_PDF . '");});
            //                        })(jQuery);
            //                        </script>';
            //                }
        }

        return $pdfButton;
    }

    /**
     * @param $icons
     * @return string
     */
    public function getPrintLinks($icons)
    {
        $printLinks = '';
        // Print button
        $printLinks .= "<a href='" . Seo::generateUrl('print', $this->itemid(), $this->short_url()) . "' rel='nofollow' target='_blank'>" . $icons['print'] . '</a>&nbsp;';
        $printLinks .= ' ';

        return $printLinks;
    }

    /**
     * @param array $notifications
     */
    public function sendNotifications($notifications = [])
    {
        /** @var \XoopsNotificationHandler $notificationHandler */
        $notificationHandler = \xoops_getHandler('notification');
        $tags                = [];

        $tags['MODULE_NAME']   = $this->helper->getModule()->getVar('name');
        $tags['ITEM_NAME']     = $this->getTitle();
        $tags['ITEM_SUBNAME']  = $this->getSubtitle();
        $tags['CATEGORY_NAME'] = $this->getCategoryName();
        $tags['CATEGORY_URL']  = PUBLISHER_URL . '/category.php?categoryid=' . $this->categoryid();
        $tags['ITEM_BODY']     = $this->body();
        $tags['DATESUB']       = $this->getDatesub();
        foreach ($notifications as $notification) {
            switch ($notification) {
                case Constants::PUBLISHER_NOTIFY_ITEM_PUBLISHED:
                    $tags['ITEM_URL'] = PUBLISHER_URL . '/item.php?itemid=' . $this->itemid();
                    $notificationHandler->triggerEvent('global_item', 0, 'published', $tags, [], $this->helper->getModule()->getVar('mid'));
                    $notificationHandler->triggerEvent('category_item', $this->categoryid(), 'published', $tags, [], $this->helper->getModule()->getVar('mid'));
                    $notificationHandler->triggerEvent('item', $this->itemid(), 'approved', $tags, [], $this->helper->getModule()->getVar('mid'));
                    break;
                case Constants::PUBLISHER_NOTIFY_ITEM_SUBMITTED:
                    $tags['WAITINGFILES_URL'] = PUBLISHER_URL . '/admin/item.php?itemid=' . $this->itemid();
                    $notificationHandler->triggerEvent('global_item', 0, 'submitted', $tags, [], $this->helper->getModule()->getVar('mid'));
                    $notificationHandler->triggerEvent('category_item', $this->categoryid(), 'submitted', $tags, [], $this->helper->getModule()->getVar('mid'));
                    break;
                case Constants::PUBLISHER_NOTIFY_ITEM_REJECTED:
                    $notificationHandler->triggerEvent('item', $this->itemid(), 'rejected', $tags, [], $this->helper->getModule()->getVar('mid'));
                    break;
                case -1:
                default:
                    break;
            }
        }
    }

    /**
     * Sets default permissions for this item
     */
    public function setDefaultPermissions()
    {
        $memberHandler = \xoops_getHandler('member');
        $groups        = $memberHandler->getGroupList();
        $groupIds      = \count($groups) > 0 ? \array_keys($groups) : [];
        /*
        $j             = 0;
        $groupIds      = [];
        foreach (array_keys($groups) as $i) {
            $groupIds[$j] = $i;
            ++$j;
        }
        */
        $this->groupsRead = $groupIds;
    }

    /**
     * @param $groupIds
     * @deprecated - NOT USED
     *
     * @todo       look at this
     */
    public function setPermissions($groupIds)
    {
        if (!isset($groupIds)) {
            $this->setDefaultPermissions();
            /*
            $memberHandler = xoops_getHandler('member');
            $groups        = $memberHandler->getGroupList();
            $j             = 0;
            $groupIds      = [];
            foreach (array_keys($groups) as $i) {
                $groupIds[$j] = $i;
                ++$j;
            }
            */
        }
    }

    /**
     * @return bool
     */
    public function notLoaded()
    {
        return -1 == $this->getVar('itemid');
    }

    /**
     * @return string
     */
    public function getItemUrl()
    {
        return Seo::generateUrl('item', $this->itemid(), $this->short_url());
    }

    /**
     * @param bool $class
     * @param int  $maxsize
     *
     * @return string
     */
    public function getItemLink($class = false, $maxsize = 0)
    {
        if ($class) {
            return '<a class="' . $class . '" href="' . $this->getItemUrl() . '">' . $this->getTitle($maxsize) . '</a>';
        }

        return '<a href="' . $this->getItemUrl() . '">' . $this->getTitle($maxsize) . '</a>';
    }

    /**
     * @return string
     */
    public function getWhoAndWhen()
    {
        $posterName = $this->getLinkedPosterName();
        $postdate   = $this->getDatesub();

        return \sprintf(\_CO_PUBLISHER_POSTEDBY, $posterName, $postdate);
    }

    /**
     * @return string
     */
    public function getWho()
    {
        $posterName = $this->getLinkedPosterName();

        return $posterName;
    }

    /**
     * @return string
     */
    public function getWhen()
    {
        $postdate = $this->getDatesub();

        return $postdate;
    }

    /**
     * @param null|string $body
     *
     * @return string
     */
    public function plainMaintext($body = null)
    {
        $ret = '';
        if (!$body) {
            $body = $this->body();
        }
        $ret .= \str_replace('[pagebreak]', '<br><br>', $body);

        return $ret;
    }

    /**
     * @param int         $itemPageId
     * @param null|string $body
     *
     * @return string
     */
    public function buildMainText($itemPageId = -1, $body = null)
    {
        if (null === $body) {
            $body = $this->body();
        }
        $bodyParts = \explode('[pagebreak]', $body);
        $this->setVar('pagescount', \count($bodyParts));
        if (\count($bodyParts) <= 1) {
            return $this->plainMaintext($body);
        }
        $ret = '';
        if (-1 == $itemPageId) {
            $ret .= \trim($bodyParts[0]);

            return $ret;
        }
        if ($itemPageId >= \count($bodyParts)) {
            $itemPageId = \count($bodyParts) - 1;
        }
        $ret .= \trim($bodyParts[$itemPageId]);

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        static $ret;
        $itemId = $this->getVar('itemid');
        if (!isset($ret[$itemId])) {
            $ret[$itemId]['main']   = '';
            $ret[$itemId]['others'] = [];
            $imagesIds              = [];
            $image                  = $this->getVar('image');
            $images                 = $this->getVar('images');
            if ('' != $images) {
                $imagesIds = \explode('|', $images);
            }
            if ($image > 0) {
                $imagesIds[] = $image;
            }
            $imageObjs = [];
            if (\count($imagesIds) > 0) {
                $imageHandler = \xoops_getHandler('image');
                $criteria     = new \CriteriaCompo(new \Criteria('image_id', '(' . \implode(',', $imagesIds) . ')', 'IN'));
                $imageObjs    = $imageHandler->getObjects($criteria, true);
                unset($criteria);
            }
            foreach ($imageObjs as $id => $imageObj) {
                if ($id == $image) {
                    $ret[$itemId]['main'] = $imageObj;
                } else {
                    $ret[$itemId]['others'][] = $imageObj;
                }
                unset($imageObj);
            }
            unset($imageObjs);
        }

        return $ret[$itemId];
    }

    /**
     * @param string $display
     * @param int    $maxCharTitle
     * @param int    $maxCharSummary
     * @param bool   $fullSummary
     *
     * @return array
     */
    public function toArraySimple($display = 'default', $maxCharTitle = 0, $maxCharSummary = 300, $fullSummary = false)
    {
        $itemPageId = -1;
        if (\is_numeric($display)) {
            $itemPageId = $display;
            $display    = 'all';
        }
        $item['itemid']       = $this->itemid();
        $item['uid']          = $this->uid();
        $item['itemurl']      = $this->getItemUrl();
        $item['titlelink']    = $this->getItemLink('titlelink', $maxCharTitle);
        $item['subtitle']     = $this->subtitle();
        $item['datesub']      = $this->getDatesub();
        $item['dateexpire']   = $this->getDateExpire();
        $item['counter']      = $this->counter();
        $item['hits']         = '&nbsp;' . $this->counter() . ' ' . _READS . '';
        $item['who']          = $this->getWho();
        $item['when']         = $this->getWhen();
        $item['category']     = $this->getCategoryName();
        $item['categorylink'] = $this->getCategoryLink();
        $item['cancomment']   = $this->cancomment();
        $item['votetype']     = $this->votetype();
        $comments             = $this->comments();
        if ($comments > 0) {
            //shows 1 comment instead of 1 comm. if comments ==1
            //langugage file modified accordingly
            if (1 == $comments) {
                $item['comments'] = '&nbsp;' . \_MD_PUBLISHER_ONECOMMENT . '&nbsp;';
            } else {
                $item['comments'] = '&nbsp;' . $comments . '&nbsp;' . \_MD_PUBLISHER_COMMENTS . '&nbsp;';
            }
        } else {
            $item['comments'] = '&nbsp;' . \_MD_PUBLISHER_NO_COMMENTS . '&nbsp;';
        }
        $item = $this->getMainImage($item);
        switch ($display) {
            case 'summary':
                $item = $this->toArrayFull($item);
                $item = $this->toArrayAll($item, $itemPageId);
            case 'list':
                $item = $this->toArrayFull($item);
                $item = $this->toArrayAll($item, $itemPageId);
            //break;
            case 'full':
                $item = $this->toArrayFull($item);
                $item = $this->toArrayAll($item, $itemPageId);
            case 'wfsection':
                $item = $this->toArrayFull($item);
                $item = $this->toArrayAll($item, $itemPageId);
            case 'default':
                $item    = $this->toArrayFull($item);
                $item    = $this->toArrayAll($item, $itemPageId);
                $summary = $this->getSummary($maxCharSummary);
                if (!$summary) {
                    $summary = $this->getBody($maxCharSummary);
                }
                $item['summary']   = $summary;
                $item['truncated'] = $maxCharSummary > 0 && \mb_strlen($summary) > $maxCharSummary;
                $item              = $this->toArrayFull($item);
                break;
            case 'all':
                $item = $this->toArrayFull($item);
                $item = $this->toArrayAll($item, $itemPageId);
                break;
        }
        // Highlighting searched words
        $highlight = true;
        if ($highlight && Request::getString('keywords', '', 'GET')) {
            $keywords = \htmlspecialchars(\trim(\urldecode(Request::getString('keywords', '', 'GET'))), \ENT_QUOTES | \ENT_HTML5);
            $fields   = ['title', 'maintext', 'summary'];
            foreach ($fields as $field) {
                if (isset($item[$field])) {
                    $item[$field] = $this->highlight($item[$field], $keywords);
                }
            }
        }

        return $item;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    public function toArrayFull($item)
    {
        $configurator = new Common\Configurator();
        $icons = $configurator->icons;

        $item['title']       = $this->getTitle();
        $item['clean_title'] = $this->getTitle();
        $item['itemurl']     = $this->getItemUrl();

        $item['adminlink']    = $this->getAdminLinks($icons);
        $item['pdfbutton']    = $this->getPdfButton($icons);
        $item['printlink']    = $this->getPrintLinks($icons);
        $item['categoryPath'] = $this->getCategoryPath($this->helper->getConfig('format_linked_path'));
        $item['who_when']     = $this->getWhoAndWhen();
        $item['who']          = $this->getWho();
        $item['when']         = $this->getWhen();
        $item['category']     = $this->getCategoryName();
        $item['body']         = $this->getBody();
        $item['more']         = $this->getItemUrl();
        $item                 = $this->getMainImage($item);

        return $item;
    }

    /**
     * @param array $item
     * @param int   $itemPageId
     *
     * @return array
     */
    public function toArrayAll($item, $itemPageId)
    {
        $item['maintext'] = $this->buildMainText($itemPageId, $this->getBody());
        $item             = $this->getOtherImages($item);

        return $item;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    public function getMainImage($item = [])
    {
        $images             = $this->getImages();
        $item['image_path'] = '';
        $item['image_name'] = '';
        if (\is_object($images['main'])) {
            $dimensions           = \getimagesize($GLOBALS['xoops']->path('uploads/' . $images['main']->getVar('image_name')));
            $item['image_width']  = $dimensions[0];
            $item['image_height'] = $dimensions[1];
            $item['image_path']   = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
            // check to see if GD function exist
            if (\function_exists('imagecreatetruecolor')) {
                $item['image_thumb'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '&amp;h=180';
            } else {
                $item['image_thumb'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
            }
            $item['image_name'] = $images['main']->getVar('image_nicename');
        }

        return $item;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    public function getOtherImages($item = [])
    {
        $images         = $this->getImages();
        $item['images'] = [];
        $i              = 0;
        foreach ($images['others'] as $image) {
            $dimensions                   = \getimagesize($GLOBALS['xoops']->path('uploads/' . $image->getVar('image_name')));
            $item['images'][$i]['width']  = $dimensions[0];
            $item['images'][$i]['height'] = $dimensions[1];
            $item['images'][$i]['path']   = XOOPS_URL . '/uploads/' . $image->getVar('image_name');
            // check to see if GD function exist
            if (\function_exists('imagecreatetruecolor')) {
                $item['images'][$i]['thumb'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $image->getVar('image_name') . '&amp;w=240';
            } else {
                $item['images'][$i]['thumb'] = XOOPS_URL . '/uploads/' . $image->getVar('image_name');
            }
            $item['images'][$i]['name'] = $image->getVar('image_nicename');
            ++$i;
        }

        return $item;
    }

    /**
     * @param string       $content
     * @param string|array $keywords
     *
     * @return string Text
     */
    public function highlight($content, $keywords)
    {
        $color = $this->helper->getConfig('format_highlight_color');
        if (0 !== \mb_strpos($color, '#')) {
            $color = '#' . $color;
        }
        require_once __DIR__ . '/Highlighter.php';
        $highlighter = new Highlighter();
        $highlighter->setReplacementString('<span style="font-weight: bolder; background-color: ' . $color . ';">\1</span>');

        return $highlighter->highlight($content, $keywords);
    }

    /**
     *  Create metada and assign it to template
     */
    public function createMetaTags()
    {
        $publisherMetagen = new Metagen($this->getTitle(), $this->meta_keywords(), $this->meta_description(), $this->category->categoryPath);
        $publisherMetagen->createMetaTags();
    }

    /**
     * @param string $str
     *
     * @return string
     */
    protected function convertForJapanese($str)
    {
        // no action, if not flag
        if (!\defined('_PUBLISHER_FLAG_JP_CONVERT')) {
            return $str;
        }
        // no action, if not Japanese
        if ('japanese' !== $GLOBALS['xoopsConfig']['language']) {
            return $str;
        }
        // presume OS Browser
        $agent   = Request::getString('HTTP_USER_AGENT', '', 'SERVER');
        $os      = '';
        $browser = '';
        //        if (preg_match("/Win/i", $agent)) {
        if (false !== \mb_stripos($agent, 'Win')) {
            $os = 'win';
        }
        //        if (preg_match("/MSIE/i", $agent)) {
        if (false !== \mb_stripos($agent, 'MSIE')) {
            $browser = 'msie';
        }
        // if msie
        if (('win' === $os) && ('msie' === $browser)) {
            // if multibyte
            if (\function_exists('mb_convert_encoding')) {
                $str = \mb_convert_encoding($str, 'SJIS', 'EUC-JP');
                $str = \rawurlencode($str);
            }
        }

        return $str;
    }

    /**
     * @param string $title
     * @param bool   $checkperm
     *
     * @return Form\ItemForm
     */
    public function getForm($title = 'default', $checkperm = true)
    {
        //        require_once $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME . '/class/form/item.php');
        $form = new Form\ItemForm($title, 'form', \xoops_getenv('SCRIPT_NAME'), 'post', true);
        $form->setCheckPermissions($checkperm);
        $form->createElements($this);

        return $form;
    }

    /**
     * Checks if a user has access to a selected item. if no item permissions are
     * set, access permission is denied. The user needs to have necessary category
     * permission as well.
     * Also, the item needs to be Published
     *
     * @return bool : TRUE if the no errors occured
     */
    public function accessGranted()
    {
        if (Utility::userIsAdmin()) {
            return true;
        }
        if (Constants::PUBLISHER_STATUS_PUBLISHED != $this->getVar('status')) {
            return false;
        }
        // Do we have access to the parent category
        if ($this->helper->getHandler('Permission')->isGranted('category_read', $this->categoryid())) {
            return true;
        }

        return false;
    }

    /**
     * The name says it all
     */
    public function setVarsFromRequest()
    {
        //Required fields
        //        if (!empty($categoryid = Request::getInt('categoryid', 0, 'POST'))) {
        //            $this->setVar('categoryid', $categoryid);}
        if (\is_object($GLOBALS['xoopsUser'])) {
            $userTimeoffset = $GLOBALS['xoopsUser']->getVar('timezone_offset');
        } else {
            $userTimeoffset = null;
        }
        $this->setVar('categoryid', Request::getInt('categoryid', 0, 'POST'));
        $this->setVar('title', Request::getString('title', '', 'POST'));
        $this->setVar('body', Request::getText('body', '', 'POST'));

        //Not required fields
        $this->setVar('summary', Request::getText('summary', '', 'POST'));
        $this->setVar('subtitle', Request::getString('subtitle', '', 'POST'));
        $this->setVar('item_tag', Request::getString('item_tag', '', 'POST'));

        if ('' !== ($imageFeatured = Request::getString('image_featured', '', 'POST'))) {
            $imageItem = Request::getArray('image_item', [], 'POST');
            //            $imageFeatured = Request::getString('image_featured', '', 'POST');
            //Todo: get a better image class for xoops!
            //Image hack
            $imageItemIds = [];

            $sql    = 'SELECT image_id, image_name FROM ' . $GLOBALS['xoopsDB']->prefix('image');
            $result = $GLOBALS['xoopsDB']->query($sql, 0, 0);
            while (false !== ($myrow = $GLOBALS['xoopsDB']->fetchArray($result))) {
                $imageName = $myrow['image_name'];
                $id        = $myrow['image_id'];
                if ($imageName == $imageFeatured) {
                    $this->setVar('image', $id);
                }
                if (\in_array($imageName, $imageItem, true)) {
                    $imageItemIds[] = $id;
                }
            }
            $this->setVar('images', \implode('|', $imageItemIds));
        } else {
            $this->setVar('image', 0);
            $this->setVar('images', '');
        }

        if (false !== ($authorAlias = Request::getString('author_alias', '', 'POST'))) {
            $this->setVar('author_alias', $authorAlias);
            if ('' !== $this->getVar('author_alias')) {
                $this->setVar('uid', 0);
            }
        }

        //mb TODO check on version
        //check if date is set and convert it to GMT date
        //        if (($datesub = Request::getString('datesub', '', 'POST'))) {
        if ('' !== Request::getString('datesub', '', 'POST')) {
            //            if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            //                $this->setVar('datesub', strtotime(Request::getArray('datesub', array(), 'POST')['date']) + Request::getArray('datesub', array(), 'POST')['time']);
            //            } else {
            $resDate     = Request::getArray('datesub', [], 'POST');
            $resTime     = Request::getArray('datesub', [], 'POST');
            $dateTimeObj = \DateTime::createFromFormat(_SHORTDATESTRING, $resDate['date']);
            $dateTimeObj->setTime(0, 0, (int)$resTime['time']);
            $serverTimestamp = \userTimeToServerTime($dateTimeObj->getTimestamp(), $userTimeoffset);
            $this->setVar('datesub', $serverTimestamp);
            //            }
        } elseif ($this->isNew()) {
            $this->setVar('datesub', \time());
        }

        // date expire
        if (0 !== Request::getInt('use_expire_yn', 0, 'POST')) {
            if ('' !== Request::getString('dateexpire', '', 'POST')) {
                $resExDate   = Request::getArray('dateexpire', [], 'POST');
                $resExTime   = Request::getArray('dateexpire', [], 'POST');
                $dateTimeObj = \DateTime::createFromFormat(_SHORTDATESTRING, $resExDate['date']);
                $dateTimeObj->setTime(0, 0, (int)$resExTime['time']);
                $serverTimestamp = \userTimeToServerTime($dateTimeObj->getTimestamp(), $userTimeoffset);
                $this->setVar('dateexpire', $serverTimestamp);
            }
        } else {
            $this->setVar('dateexpire', 0);
        }

        $this->setVar('short_url', Request::getString('item_short_url', '', 'POST'));
        $this->setVar('meta_keywords', Request::getString('item_meta_keywords', '', 'POST'));
        $this->setVar('meta_description', Request::getString('item_meta_description', '', 'POST'));
        $this->setVar('weight', Request::getInt('weight', 0, 'POST'));

        if ($this->isNew()) {
            $this->setVar('uid', \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->uid() : 0);
            $this->setVar('cancoment', $this->helper->getConfig('submit_allowcomments'));
            $this->setVar('status', $this->helper->getConfig('submit_status'));
            $this->setVar('dohtml', $this->helper->getConfig('submit_dohtml'));
            $this->setVar('dosmiley', $this->helper->getConfig('submit_dosmiley'));
            $this->setVar('doxcode', $this->helper->getConfig('submit_doxcode'));
            $this->setVar('doimage', $this->helper->getConfig('submit_doimage'));
            $this->setVar('dobr', $this->helper->getConfig('submit_dobr'));
            $this->setVar('votetype', $this->helper->getConfig('ratingbars'));
        } else {
            $this->setVar('uid', Request::getInt('uid', 0, 'POST'));
            $this->setVar('cancomment', Request::getInt('allowcomments', 1, 'POST'));
            $this->setVar('status', Request::getInt('status', 1, 'POST'));
            $this->setVar('dohtml', Request::getInt('dohtml', 1, 'POST'));
            $this->setVar('dosmiley', Request::getInt('dosmiley', 1, 'POST'));
            $this->setVar('doxcode', Request::getInt('doxcode', 1, 'POST'));
            $this->setVar('doimage', Request::getInt('doimage', 1, 'POST'));
            $this->setVar('dobr', Request::getInt('dolinebreak', 1, 'POST'));
            $this->setVar('votetype', Request::getInt('votetype', 1, 'POST'));
        }

        $this->setVar('notifypub', Request::getString('notify', '', 'POST'));
    }

    public function assignOtherProperties()
    {
        $module = $this->helper->getModule();
        $module_id   = $module->getVar('mid');
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = \xoops_getHandler('groupperm');

        $this->category    = $this->helper->getHandler('Category')->get($this->getVar('categoryid'));
        $this->groups_read = $grouppermHandler->getGroupIds('item_read', $this->itemid(), $module_id);
    }
}
