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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @version         $Id: item.php 10728 2013-01-09 22:09:22Z trabis $
 */

//namespace Publisher;

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
include_once dirname(__DIR__) . '/include/common.php';

/**
 * Class PublisherItem
 */
class PublisherItem extends XoopsObject
{
    /**
     * @var PublisherPublisher
     * @access public
     */
    public $publisher;
    public $groupsRead = array();

    /**
     * @var PublisherCategory
     * @access public
     */
    public $category;

    /**
     * @param int|null $id
     */
    public function __construct($id = null)
    {
        $this->publisher = PublisherPublisher::getInstance();
        $this->db        = XoopsDatabaseFactory::getDatabaseConnection();
        $this->initVar('itemid', XOBJ_DTYPE_INT, 0);
        $this->initVar('categoryid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX, '', true, 255);
        $this->initVar('subtitle', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('summary', XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('body', XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('author_alias', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('datesub', XOBJ_DTYPE_INT, '', false);
        $this->initVar('status', XOBJ_DTYPE_INT, -1, false);
        $this->initVar('image', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('images', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('counter', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('rating', XOBJ_DTYPE_OTHER, 0, false);
        $this->initVar('votes', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('dosmiley', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('doimage', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('dobr', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('doxcode', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('cancomment', XOBJ_DTYPE_INT, 1, true);
        $this->initVar('comments', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('notifypub', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('meta_keywords', XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('meta_description', XOBJ_DTYPE_TXTAREA, '', false);
        $this->initVar('short_url', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('item_tag', XOBJ_DTYPE_TXTAREA, '', false);
        // Non consistent values
        $this->initVar('pagescount', XOBJ_DTYPE_INT, 0, false);
        if (isset($id)) {
            $item = $this->publisher->getHandler('item')->get($id);
            foreach ($item->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $arg = isset($args[0]) ? $args[0] : null;

        return $this->getVar($method, $arg);
    }

    /**
     * @return null|PublisherCategory
     */
    public function getCategory()
    {
        if (!isset($this->category)) {
            $this->category = $this->publisher->getHandler('category')->get($this->getVar('categoryid'));
        }

        return $this->category;
    }

    /**
     * @param int $maxLength
     * @param string $format
     *
     * @return mixed|string
     */
    public function getTitle($maxLength = 0, $format = 'S')
    {
        $ret = $this->getVar('title', $format);
        if ($maxLength != 0) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (strlen($ret) >= $maxLength) {
                    $ret = publisherSubstr($ret, 0, $maxLength);
                }
            }
        }

        return $ret;
    }

    /**
     * @param int $maxLength
     * @param string $format
     *
     * @return mixed|string
     */
    public function getSubtitle($maxLength = 0, $format = 'S')
    {
        $ret = $this->getVar('subtitle', $format);
        if ($maxLength != 0) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (strlen($ret) >= $maxLength) {
                    $ret = publisherSubstr($ret, 0, $maxLength);
                }
            }
        }

        return $ret;
    }

    /**
     * @param int $maxLength
     * @param string $format
     * @param string $stripTags
     *
     * @return mixed|string
     */
    public function getSummary($maxLength = 0, $format = 'S', $stripTags = '')
    {
        $ret = $this->getVar('summary', $format);
        if (!empty($stripTags)) {
            $ret = strip_tags($ret, $stripTags);
        }
        if ($maxLength != 0) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (strlen($ret) >= $maxLength) {
                    //$ret = publisherSubstr($ret , 0, $maxLength);
                    $ret = publisherTruncateTagSafe($ret, $maxLength, $etc = '...', $breakWords = false);
                }
            }
        }

        return $ret;
    }

    /**
     * @param int $maxLength
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
        $page    = publisherGetUploadDir(true, 'content') . $fileName;
        if (file_exists($page)) {
            // this page uses smarty template
            ob_start();
            include($page);
            $content = ob_get_contents();
            ob_end_clean();
            // Cleaning the content
            $bodyStartPos = strpos($content, '<body>');
            if ($bodyStartPos) {
                $bodyEndPos = strpos($content, '</body>', $bodyStartPos);
                $content    = substr($content, $bodyStartPos + strlen('<body>'), $bodyEndPos - strlen('<body>') - $bodyStartPos);
            }
            // Check if ML Hack is installed, and if yes, parse the $content in formatForML
            $myts = MyTextSanitizer::getInstance();
            if (method_exists($myts, 'formatForML')) {
                $content = $myts->formatForML($content);
            }
        }

        return $content;
    }

    /**
     * This method returns the body to be displayed. Not to be used for editing
     *
     * @param int $maxLength
     * @param string $format
     * @param string $stripTags
     *
     * @return mixed|string
     */
    public function getBody($maxLength = 0, $format = 'S', $stripTags = '')
    {
        $ret     = $this->getVar('body', $format);
        $wrapPos = strpos($ret, '[pagewrap=');
        if (!($wrapPos === false)) {
            $wrapPages      = array();
            $wrapCodeLength = strlen('[pagewrap=');
            while (!($wrapPos === false)) {
                $endWrapPos = strpos($ret, ']', $wrapPos);
                if ($endWrapPos) {
                    $wrap_page_name = substr($ret, $wrapPos + $wrapCodeLength, $endWrapPos - $wrapCodeLength - $wrapPos);
                    $wrapPages[]    = $wrap_page_name;
                }
                $wrapPos = strpos($ret, '[pagewrap=', $endWrapPos - 1);
            }
            foreach ($wrapPages as $page) {
                $wrapPageContent = $this->wrapPage($page);
                $ret             = str_replace("[pagewrap={$page}]", $wrapPageContent, $ret);
            }
        }
        if ($this->publisher->getConfig('item_disp_blocks_summary')) {
            $summary = $this->getSummary($maxLength, $format, $stripTags);
            if ($summary) {
                $ret = $this->getSummary() . $ret;
            }
        }
        if (!empty($stripTags)) {
            $ret = strip_tags($ret, $stripTags);
        }
        if ($maxLength != 0) {
            if (!XOOPS_USE_MULTIBYTES) {
                if (strlen($ret) >= $maxLength) {
                    //$ret = publisherSubstr($ret , 0, $maxLength);
                    $ret = publisherTruncateTagSafe($ret, $maxLength, $etc = '...', $breakWords = false);
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
        if (empty($dateformat)) {
            $dateFormat = $this->publisher->getConfig('format_date');
        }
        //mb        xoops_load('XoopsLocal');
        //mb        return XoopsLocal::formatTimestamp($this->getVar('datesub', $format), $dateFormat);
        return formatTimestamp($this->getVar('datesub', $format), $dateFormat);
    }

    /**
     * @param int $realName
     *
     * @return string
     */
    public function posterName($realName = -1)
    {
        xoops_load('XoopsUserUtility');
        if ($realName == -1) {
            $realName = $this->publisher->getConfig('format_realname');
        }
        $ret = $this->author_alias();
        if ($ret == '') {
            $ret = XoopsUserUtility::getUnameFromId($this->uid(), $realName);
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function posterAvatar()
    {
        $ret           = 'blank.gif';
        $memberHandler = xoops_getHandler('member');
        $thisUser      = $memberHandler->getUser($this->uid());
        if (is_object($thisUser)) {
            $ret = $thisUser->getVar('user_avatar');
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function getLinkedPosterName()
    {
        xoops_load('XoopsUserUtility');
        $ret = $this->author_alias();
        if ($ret === '') {
            $ret = XoopsUserUtility::getUnameFromId($this->uid(), $this->publisher->getConfig('format_realname'), true);
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function updateCounter()
    {
        return $this->publisher->getHandler('item')->updateCounter($this->itemid());
    }

    /**
     * @param bool $force
     *
     * @return bool
     */
    public function store($force = true)
    {
        $isNew = $this->isNew();
        if (!$this->publisher->getHandler('item')->insert($this, $force)) {
            return false;
        }
        if ($isNew && $this->status() == PublisherConstants::PUBLISHER_STATUS_PUBLISHED) {
            // Increment user posts
            $userHandler   = xoops_getHandler('user');
            $memberHandler = xoops_getHandler('member');
            $poster        = $userHandler->get($this->uid());
            if (is_object($poster) && !$poster->isNew()) {
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
     * @return string
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
        return publisherGetImageDir('category', false) . $this->getCategory()->getImage();
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->publisher->getHandler('file')->getAllFiles($this->itemid(), PublisherConstants::PUBLISHER_STATUS_FILE_ACTIVE);
    }

    /**
     * @return string
     */
    public function getAdminLinks()
    {
        $adminLinks = '';
        if (is_object($GLOBALS['xoopsUser']) && (publisherUserIsAdmin() || publisherUserIsAuthor($this) || $this->publisher->getHandler('permission')->isGranted('item_submit', $this->categoryid()))) {
            if (publisherUserIsAdmin() || publisherUserIsAuthor($this) || publisherUserIsModerator($this)) {
                if ($this->publisher->getConfig('perm_edit') || publisherUserIsModerator($this) || publisherUserIsAdmin()) {
                    // Edit button
                    $adminLinks .= "<a href='" . PUBLISHER_URL . '/submit.php?itemid=' . $this->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/edit.gif'" . " title='" . _CO_PUBLISHER_EDIT . "' alt='" . _CO_PUBLISHER_EDIT . "'/></a>";
                    $adminLinks .= ' ';
                }
                if ($this->publisher->getConfig('perm_delete') || publisherUserIsModerator($this) || publisherUserIsAdmin()) {
                    // Delete button
                    $adminLinks .= "<a href='" . PUBLISHER_URL . '/submit.php?op=del&amp;itemid=' . $this->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/delete.png'" . " title='" . _CO_PUBLISHER_DELETE . "' alt='" . _CO_PUBLISHER_DELETE . "' /></a>";
                    $adminLinks .= ' ';
                }
            }
            if ($this->publisher->getConfig('perm_clone') || publisherUserIsModerator($this) || publisherUserIsAdmin()) {
                // Duplicate button
                $adminLinks .= "<a href='" . PUBLISHER_URL . '/submit.php?op=clone&amp;itemid=' . $this->itemid() . "'><img src='" . PUBLISHER_URL . "/assets/images/links/clone.gif'" . " title='" . _CO_PUBLISHER_CLONE . "' alt='" . _CO_PUBLISHER_CLONE . "' /></a>";
                $adminLinks .= ' ';
            }
        }
        if ($this->publisher->getConfig('display_pdf')) {
            // PDF button
            $adminLinks .= "<a href='" . PUBLISHER_URL . '/makepdf.php?itemid=' . $this->itemid() . "' rel='nofollow' target='_blank'><img src='" . PUBLISHER_URL . "/assets/images/links/pdf.gif' title='" . _CO_PUBLISHER_PDF . "' alt='" . _CO_PUBLISHER_PDF . "' /></a>";
            $adminLinks .= ' ';
        }
        // Print button
        $adminLinks .= "<a href='" . PublisherSeo::generateUrl('print', $this->itemid(), $this->short_url()) . "' rel='nofollow' target='_blank'><img src='" . PUBLISHER_URL . "/assets/images/links/print.gif' title='" . _CO_PUBLISHER_PRINT . "' alt='" . _CO_PUBLISHER_PRINT . "' /></a>";
        $adminLinks .= ' ';
        // Email button
        if (xoops_isActiveModule('tellafriend')) {
            $subject  = sprintf(_CO_PUBLISHER_INTITEMFOUND, $GLOBALS['xoopsConfig']['sitename']);
            $subject  = $this->convertForJapanese($subject);
            $maillink = publisherTellAFriend($subject);
            $adminLinks .= '<a href="' . $maillink . '"><img src="' . PUBLISHER_URL . '/assets/images/links/friend.gif" title="' . _CO_PUBLISHER_MAIL . '" alt="' . _CO_PUBLISHER_MAIL . '" /></a>';
            $adminLinks .= ' ';
        }

        return $adminLinks;
    }

    /**
     * @param array $notifications
     */
    public function sendNotifications($notifications = array())
    {
        $notificationHandler   = xoops_getHandler('notification');
        $tags                  = array();
        $tags['MODULE_NAME']   = $this->publisher->getModule()->getVar('name');
        $tags['ITEM_NAME']     = $this->getTitle();
        $tags['ITEM_NAME']     = $this->subtitle();
        $tags['CATEGORY_NAME'] = $this->getCategoryName();
        $tags['CATEGORY_URL']  = PUBLISHER_URL . '/category.php?categoryid=' . $this->categoryid();
        $tags['ITEM_BODY']     = $this->body();
        $tags['DATESUB']       = $this->getDatesub();
        foreach ($notifications as $notification) {
            switch ($notification) {
                case PublisherConstants::PUBLISHER_NOTIFY_ITEM_PUBLISHED:
                    $tags['ITEM_URL'] = PUBLISHER_URL . '/item.php?itemid=' . $this->itemid();
                    $notificationHandler->triggerEvent('global_item', 0, 'published', $tags, array(), $this->publisher->getModule()->getVar('mid'));
                    $notificationHandler->triggerEvent('category_item', $this->categoryid(), 'published', $tags, array(), $this->publisher->getModule()->getVar('mid'));
                    $notificationHandler->triggerEvent('item', $this->itemid(), 'approved', $tags, array(), $this->publisher->getModule()->getVar('mid'));
                    break;
                case PublisherConstants::PUBLISHER_NOTIFY_ITEM_SUBMITTED:
                    $tags['WAITINGFILES_URL'] = PUBLISHER_URL . '/admin/item.php?itemid=' . $this->itemid();
                    $notificationHandler->triggerEvent('global_item', 0, 'submitted', $tags, array(), $this->publisher->getModule()->getVar('mid'));
                    $notificationHandler->triggerEvent('category_item', $this->categoryid(), 'submitted', $tags, array(), $this->publisher->getModule()->getVar('mid'));
                    break;
                case PublisherConstants::PUBLISHER_NOTIFY_ITEM_REJECTED:
                    $notificationHandler->triggerEvent('item', $this->itemid(), 'rejected', $tags, array(), $this->publisher->getModule()->getVar('mid'));
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
        $memberHandler = xoops_getHandler('member');
        $groups        = $memberHandler->getGroupList();
        $j             = 0;
        $groupIds      = array();
        foreach (array_keys($groups) as $i) {
            $groupIds[$j] = $i;
            ++$j;
        }
        $this->groupsRead = $groupIds;
    }

    /**
     * @todo look at this
     *
     * @param $groupIds
     */
    public function setPermissions($groupIds)
    {
        if (!isset($groupIds)) {
            $memberHandler = xoops_getHandler('member');
            $groups        = $memberHandler->getGroupList();
            $j             = 0;
            $groupIds      = array();
            foreach (array_keys($groups) as $i) {
                $groupIds[$j] = $i;
                ++$j;
            }
        }
    }

    /**
     * @return bool
     */
    public function notLoaded()
    {
        return $this->getVar('itemid') == -1;
    }

    /**
     * @return string
     */
    public function getItemUrl()
    {
        return PublisherSeo::generateUrl('item', $this->itemid(), $this->short_url());
    }

    /**
     * @param bool $class
     * @param int $maxsize
     *
     * @return string
     */
    public function getItemLink($class = false, $maxsize = 0)
    {
        if ($class) {
            return '<a class=' . $class . ' href="' . $this->getItemUrl() . '">' . $this->getTitle($maxsize) . '</a>';
        } else {
            return '<a href="' . $this->getItemUrl() . '">' . $this->getTitle($maxsize) . '</a>';
        }
    }

    /**
     * @return string
     */
    public function getWhoAndWhen()
    {
        $posterName = $this->getLinkedPosterName();
        $postdate   = $this->getDatesub();

        return sprintf(_CO_PUBLISHER_POSTEDBY, $posterName, $postdate);
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
        $ret .= str_replace('[pagebreak]', '<br /><br />', $body);

        return $ret;
    }

    /**
     * @param int $itemPageId
     * @param null|string $body
     *
     * @return string
     */
    public function buildMainText($itemPageId = -1, $body = null)
    {
        if (!$body) {
            $body = $this->body();
        }
        $bodyParts = explode('[pagebreak]', $body);
        $this->setVar('pagescount', count($bodyParts));
        if (count($bodyParts) <= 1) {
            return $this->plainMaintext($body);
        }
        $ret = '';
        if ($itemPageId == -1) {
            $ret .= trim($bodyParts[0]);

            return $ret;
        }
        if ($itemPageId >= count($bodyParts)) {
            $itemPageId = count($bodyParts) - 1;
        }
        $ret .= trim($bodyParts[$itemPageId]);

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        static $ret;
        $itemid = $this->getVar('itemid');
        if (!isset($ret[$itemid])) {
            $ret[$itemid]['main']   = '';
            $ret[$itemid]['others'] = array();
            $imagesIds              = array();
            $image                  = $this->getVar('image');
            $images                 = $this->getVar('images');
            if ($images != '') {
                $imagesIds = explode('|', $images);
            }
            if ($image > 0) {
                $imagesIds = array_merge($imagesIds, array($image));
            }
            $imageObjs = array();
            if (count($imagesIds) > 0) {
                $imageHandler = xoops_getHandler('image');
                $criteria     = new CriteriaCompo(new Criteria('image_id', '(' . implode(',', $imagesIds) . ')', 'IN'));
                $imageObjs    = $imageHandler->getObjects($criteria, true);
                unset($criteria);
            }
            foreach ($imageObjs as $id => $imageObj) {
                if ($id == $image) {
                    $ret[$itemid]['main'] = $imageObj;
                } else {
                    $ret[$itemid]['others'][] = $imageObj;
                }
                unset($imageObj);
            }
            unset($imageObjs);
        }

        return $ret[$itemid];
    }

    /**
     * @param string $display
     * @param int $maxCharTitle
     * @param int $maxCharSummary
     * @param bool $fullSummary
     *
     * @return array
     */
    public function toArraySimple($display = 'default', $maxCharTitle = 0, $maxCharSummary = 0, $fullSummary = false)
    {
        $itemPageId = -1;
        if (is_numeric($display)) {
            $itemPageId = $display;
            $display    = 'all';
        }
        $item['itemid']    = $this->itemid();
        $item['uid']       = $this->uid();
        $item['itemurl']   = $this->getItemUrl();
        $item['titlelink'] = $this->getItemLink('titlelink', $maxCharTitle);
        $item['subtitle']  = $this->subtitle();
        $item['datesub']   = $this->getDatesub();
        $item['counter']   = $this->counter();
        $item['who']       = $this->getWho();
        $item['when']      = $this->getWhen();
        $item['category']  = $this->getCategoryName();
        $item              = $this->getMainImage($item);
        switch ($display) {
            case 'summary':
            case 'list':
                break;
            case 'full':
            case 'wfsection':
            case 'default':
                $summary = $this->getSummary($maxCharSummary);
                if (!$summary) {
                    $summary = $this->getBody($maxCharSummary);
                }
                $item['summary'] = $summary;
                $item            = $this->toArrayFull($item);
                break;
            case 'all':
                $item = $this->toArrayFull($item);
                $item = $this->toArrayAll($item, $itemPageId);
                break;
        }
        // Highlighting searched words
        $highlight = true;
        if ($highlight && XoopsRequest::getString('keywords', '', 'GET')) {
            $myts     = MyTextSanitizer::getInstance();
            $keywords = $myts->htmlSpecialChars(trim(urldecode(XoopsRequest::getString('keywords', '', 'GET'))));
            $fields   = array('title', 'maintext', 'summary');
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
        $item['title']        = $this->getTitle();
        $item['clean_title']  = $this->getTitle();
        $item['itemurl']      = $this->getItemUrl();
        $item['cancomment']   = $this->cancomment();
        $item['comments']     = $this->comments();
        $item['adminlink']    = $this->getAdminLinks();
        $item['categoryPath'] = $this->getCategoryPath($this->publisher->getConfig('format_linked_path'));
        $item['who_when']     = $this->getWhoAndWhen();
        $item['who']          = $this->getWho();
        $item['when']         = $this->getWhen();
        $item['category']     = $this->getCategoryName();
        $item                 = $this->getMainImage($item);

        return $item;
    }

    /**
     * @param array $item
     * @param int $itemPageId
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
    public function getMainImage($item = array())
    {
        $images             = $this->getImages();
        $item['image_path'] = '';
        $item['image_name'] = '';
        if (is_object($images['main'])) {
            $dimensions           = getimagesize($GLOBALS['xoops']->path('uploads/' . $images['main']->getVar('image_name')));
            $item['image_width']  = $dimensions[0];
            $item['image_height'] = $dimensions[1];
            $item['image_path']   = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
            // check to see if GD function exist
            if (!function_exists('imagecreatetruecolor')) {
                $item['image_thumb'] = XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name');
            } else {
                $item['image_thumb'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $images['main']->getVar('image_name') . '&amp;h=180';
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
    public function getOtherImages($item = array())
    {
        $images         = $this->getImages();
        $item['images'] = array();
        $i              = 0;
        foreach ($images['others'] as $image) {
            $dimensions                   = getimagesize($GLOBALS['xoops']->path('uploads/' . $image->getVar('image_name')));
            $item['images'][$i]['width']  = $dimensions[0];
            $item['images'][$i]['height'] = $dimensions[1];
            $item['images'][$i]['path']   = XOOPS_URL . '/uploads/' . $image->getVar('image_name');
            // check to see if GD function exist
            if (!function_exists('imagecreatetruecolor')) {
                $item['images'][$i]['thumb'] = XOOPS_URL . '/uploads/' . $image->getVar('image_name');
            } else {
                $item['images'][$i]['thumb'] = PUBLISHER_URL . '/thumb.php?src=' . XOOPS_URL . '/uploads/' . $image->getVar('image_name') . '&amp;w=240';
            }
            $item['images'][$i]['name'] = $image->getVar('image_nicename');
            ++$i;
        }

        return $item;
    }

    /**
     * @param string $content
     * @param string|array $keywords
     *
     * @return Text
     */
    public function highlight($content, $keywords)
    {
        $color = $this->publisher->getConfig('format_highlight_color');
        if (substr($color, 0, 1) !== '#') {
            $color = '#' . $color;
        }
        include_once __DIR__ . '/highlighter.php';
        $highlighter = new PublisherHighlighter();
        $highlighter->setReplacementString('<span style="font-weight: bolder; background-color: ' . $color . ';">\1</span>');

        return $highlighter->highlight($content, $keywords);
    }

    /**
     *  Create metada and assign it to template
     */
    public function createMetaTags()
    {
        $publisherMetagen = new PublisherMetagen($this->getTitle(), $this->meta_keywords(), $this->meta_description(), $this->category->categoryPath);
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
        if (!defined('_PUBLISHER_FLAG_JP_CONVERT')) {
            return $str;
        }
        // no action, if not Japanese
        if ($GLOBALS['xoopsConfig']['language'] !== 'japanese') {
            return $str;
        }
        // presume OS Browser
        $agent   = XoopsRequest::getString('HTTP_USER_AGENT', '', 'SERVER');
        $os      = '';
        $browser = '';
        //        if (preg_match("/Win/i", $agent)) {
        if (false !== stripos($agent, 'Win')) {
            $os = 'win';
        }
        //        if (preg_match("/MSIE/i", $agent)) {
        if (false !== stripos($agent, 'MSIE')) {
            $browser = 'msie';
        }
        // if msie
        if (($os === 'win') && ($browser === 'msie')) {
            // if multibyte
            if (function_exists('mb_convert_encoding')) {
                $str = mb_convert_encoding($str, 'SJIS', 'EUC-JP');
                $str = rawurlencode($str);
            }
        }

        return $str;
    }

    /**
     * @param string $title
     * @param bool $checkperm
     *
     * @return PublisherItemForm
     */
    public function getForm($title = 'default', $checkperm = true)
    {
        include_once $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME . '/class/form/item.php');
        $form = new PublisherItemForm($title, 'form', xoops_getenv('PHP_SELF'));
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
     * @return boolean : TRUE if the no errors occured
     */
    public function accessGranted()
    {
        if (publisherUserIsAdmin()) {
            return true;
        }
        if ($this->status() != PublisherConstants::PUBLISHER_STATUS_PUBLISHED) {
            return false;
        }
        // Do we have access to the parent category
        if ($this->publisher->getHandler('permission')->isGranted('category_read', $this->categoryid())) {
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
        //        if (!empty($categoryid = XoopsRequest::getInt('categoryid', 0, 'POST'))) {
        //            $this->setVar('categoryid', $categoryid);}

        $this->setVar('categoryid', XoopsRequest::getInt('categoryid', 0, 'POST'));
        $this->setVar('title', XoopsRequest::getString('title', '', 'POST'));
        $this->setVar('body', XoopsRequest::getText('body', '', 'POST'));

        //Not required fields
        $this->setVar('summary', XoopsRequest::getText('summary', '', 'POST'));
        $this->setVar('subtitle', XoopsRequest::getString('subtitle', '', 'POST'));
        $this->setVar('item_tag', XoopsRequest::getString('item_tag', '', 'POST'));

        if ($imageFeatured = XoopsRequest::getString('image_featured', '', 'POST')) {
            $imageItem = XoopsRequest::getArray('image_item', array(), 'POST');
            //            $imageFeatured = XoopsRequest::getString('image_featured', '', 'POST');
            //Todo: get a better image class for xoops!
            //Image hack
            $imageItemIds = array();

            $sql    = 'SELECT image_id, image_name FROM ' . $GLOBALS['xoopsDB']->prefix('image');
            $result = $GLOBALS['xoopsDB']->query($sql, 0, 0);
            while (($myrow = $GLOBALS['xoopsDB']->fetchArray($result)) !== false) {
                $imageName = $myrow['image_name'];
                $id        = $myrow['image_id'];
                if ($imageName == $imageFeatured) {
                    $this->setVar('image', $id);
                }
                if (in_array($imageName, $imageItem)) {
                    $imageItemIds[] = $id;
                }
            }
            $this->setVar('images', implode('|', $imageItemIds));
        } else {
            $this->setVar('image', 0);
            $this->setVar('images', '');
        }

        if ($authorAlias = XoopsRequest::getString('author_alias', '', 'POST')) {
            $this->setVar('author_alias', $authorAlias);
            if ($this->getVar('author_alias') !== '') {
                $this->setVar('uid', 0);
            }
        }

        //mb TODO check on version
        if ($datesub = XoopsRequest::getString('datesub', '', 'POST')) {
            //            if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            //                $this->setVar('datesub', strtotime(XoopsRequest::getArray('datesub', array(), 'POST')['date']) + XoopsRequest::getArray('datesub', array(), 'POST')['time']);
            //            } else {
            $resDate = XoopsRequest::getArray('datesub', array(), 'POST');
            $resTime = XoopsRequest::getArray('datesub', array(), 'POST');
            //            $this->setVar('datesub', strtotime($resDate['date']) + $resTime['time']);
            $localTimestamp = strtotime($resDate['date']) + $resTime['time'];

            // get user Timezone offset and use it to find out the Timezone, needed for PHP DataTime
            $userTimeoffset = $GLOBALS['xoopsUser']->getVar('timezone_offset');
            $tz             = timezone_name_from_abbr(null, $userTimeoffset * 3600);
            if ($tz === false) {
                $tz = timezone_name_from_abbr(null, $userTimeoffset * 3600, false);
            }

            $userTimezone = new DateTimeZone($tz);
            $gmtTimezone  = new DateTimeZone('GMT');
            $myDateTime   = new DateTime('now', $gmtTimezone);
            $offset       = $userTimezone->getOffset($myDateTime);

            $gmtTimestamp = $localTimestamp - $offset;
            $this->setVar('datesub', $gmtTimestamp);

            //            }
        } elseif ($this->isNew()) {
            $this->setVar('datesub', time());
        }

        $this->setVar('short_url', XoopsRequest::getString('item_short_url', '', 'POST'));
        $this->setVar('meta_keywords', XoopsRequest::getString('item_meta_keywords', '', 'POST'));
        $this->setVar('meta_description', XoopsRequest::getString('item_meta_description', '', 'POST'));
        $this->setVar('weight', XoopsRequest::getInt('weight', 0, 'POST'));

        if ($this->isNew()) {
            $this->setVar('uid', is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->uid() : 0);
            $this->setVar('cancoment', $this->publisher->getConfig('submit_allowcomments'));
            $this->setVar('status', $this->publisher->getConfig('submit_status'));
            $this->setVar('dohtml', $this->publisher->getConfig('submit_dohtml'));
            $this->setVar('dosmiley', $this->publisher->getConfig('submit_dosmiley'));
            $this->setVar('doxcode', $this->publisher->getConfig('submit_doxcode'));
            $this->setVar('doimage', $this->publisher->getConfig('submit_doimage'));
            $this->setVar('dobr', $this->publisher->getConfig('submit_dobr'));
        } else {
            $this->setVar('uid', XoopsRequest::getInt('uid', 0, 'POST'));
            $this->setVar('cancomment', XoopsRequest::getInt('allowcomments', 1, 'POST'));
            $this->setVar('status', XoopsRequest::getInt('status', 1, 'POST'));
            $this->setVar('dohtml', XoopsRequest::getInt('dohtml', 1, 'POST'));
            $this->setVar('dosmiley', XoopsRequest::getInt('dosmiley', 1, 'POST'));
            $this->setVar('doxcode', XoopsRequest::getInt('doxcode', 1, 'POST'));
            $this->setVar('doimage', XoopsRequest::getInt('doimage', 1, 'POST'));
            $this->setVar('dobr', XoopsRequest::getInt('dolinebreak', 1, 'POST'));
        }

        $this->setVar('notifypub', XoopsRequest::getString('notify', '', 'POST'));
    }
}

/**
 * Items handler class.
 * This class is responsible for providing data access mechanisms to the data source
 * of Q&A class objects.
 *
 * @author  marcan <marcan@notrevie.ca>
 * @package Publisher
 */
class PublisherItemHandler extends XoopsPersistableObjectHandler
{
    /**
     * @var PublisherPublisher
     * @access public
     */
    public $publisher;

    protected $resultCatCounts = array();

    /**
     * @param null|XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'publisher_items', 'PublisherItem', 'itemid', 'title');
        $this->publisher = PublisherPublisher::getInstance();
    }

    /**
     * @param bool $isNew
     *
     * @return object
     */
    public function create($isNew = true)
    {
        $obj = parent::create($isNew);
        if ($isNew) {
            $obj->setDefaultPermissions();
        }

        return $obj;
    }

    /**
     * retrieve an item
     *
     * @param int $id itemid of the user
     *
     * @param null $fields
     * @return mixed reference to the <a href='psi_element://PublisherItem'>PublisherItem</a> object, FALSE if failed
     *                object, FALSE if failed
     */
    public function get($id = null, $fields = null)
    {
        $obj = parent::get($id);
        if (is_object($obj)) {
            $obj->assignOtherProperties();
        }

        return $obj;
    }

    /**
     * insert a new item in the database
     *
     * @param XoopsObject $item reference to the {@link PublisherItem} object
     * @param bool $force
     *
     * @return bool FALSE if failed, TRUE if already present and unchanged or successful
     */
    public function insert(XoopsObject $item, $force = false)  //insert(&$item, $force = false)
    {
        if (!$item->meta_keywords() || !$item->meta_description() || !$item->short_url()) {
            $publisherMetagen = new PublisherMetagen($item->getTitle(), $item->getVar('meta_keywords'), $item->getVar('summary'));
            // Auto create meta tags if empty
            if (!$item->meta_keywords()) {
                $item->setVar('meta_keywords', $publisherMetagen->keywords);
            }
            if (!$item->meta_description()) {
                $item->setVar('meta_description', $publisherMetagen->description);
            }
            // Auto create short_url if empty
            if (!$item->short_url()) {
                $item->setVar('short_url', substr(PublisherMetagen::generateSeoTitle($item->getVar('title', 'n'), false), 0, 254));
            }
        }
        if (!parent::insert($item, $force)) {
            return false;
        }
        if (xoops_isActiveModule('tag')) {
            // Storing tags information
            $tagHandler = xoops_getModuleHandler('tag', 'tag');
            $tagHandler->updateByItem($item->getVar('item_tag'), $item->getVar('itemid'), PUBLISHER_DIRNAME, 0);
        }

        return true;
    }

    /**
     * delete an item from the database
     *
     * @param XoopsObject $item reference to the ITEM to delete
     * @param bool $force
     *
     * @return bool FALSE if failed.
     */
    public function delete(XoopsObject $item, $force = false)
    {
        // Deleting the files
        if (!$this->publisher->getHandler('file')->deleteItemFiles($item)) {
            $item->setErrors('An error while deleting a file.');
        }
        if (!parent::delete($item, $force)) {
            $item->setErrors('An error while deleting.');

            return false;
        }
        // Removing tags information
        if (xoops_isActiveModule('tag')) {
            $tagHandler = xoops_getModuleHandler('tag', 'tag');
            $tagHandler->updateByItem('', $item->getVar('itemid'), PUBLISHER_DIRNAME, 0);
        }

        return true;
    }

    /**
     * retrieve items from the database
     *
     * @param CriteriaElement $criteria {@link CriteriaElement} conditions to be met
     * @param bool|string $idKey        what shall we use as array key ? none, itemid, categoryid
     * @param bool $as_object
     * @param string $notNullFields
     * @return array array of <a href='psi_element://PublisherItem'>PublisherItem</a> objects
     *                                  objects
     */
    public function &getObjects(CriteriaElement $criteria = null, $idKey = false, $as_object = true, $notNullFields = '') //&getObjects($criteria = null, $idKey = 'none', $notNullFields = '')
    {
        $ret   = array();
        $limit = $start = 0;
        $sql   = 'SELECT * FROM ' . $this->db->prefix('publisher_items');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $whereClause = $criteria->renderWhere();
            if ($whereClause !== 'WHERE ()') {
                $sql .= ' ' . $criteria->renderWhere();
                if (!empty($notNullFields)) {
                    $sql .= $this->notNullFieldClause($notNullFields, true);
                }
            } elseif (!empty($notNullFields)) {
                $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
            }
            if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        } elseif (!empty($notNullFields)) {
            $sql .= $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result || count($result) === 0) {
            return $ret;
        }
        $theObjects = array();
        while (($myrow = $this->db->fetchArray($result)) !== false) {
            $item = new PublisherItem();
            $item->assignVars($myrow);
            $theObjects[$myrow['itemid']] = $item;
            unset($item);
        }
        foreach ($theObjects as $theObject) {
            if ($idKey === 'none') {
                $ret[] = $theObject;
            } elseif ($idKey === 'itemid') {
                $ret[$theObject->itemid()] = $theObject;
            } else {
                $ret[$theObject->getVar($idKey)][$theObject->itemid()] = $theObject;
            }
            unset($theObject);
        }

        return $ret;
    }

    /**
     * count items matching a condition
     *
     * @param CriteriaElement $criteria {@link CriteriaElement} to match
     * @param string $notNullFields
     *
     * @return int count of items
     */
    public function getCount(CriteriaElement $criteria = null, $notNullFields = '')
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix('publisher_items');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $whereClause = $criteria->renderWhere();
            if ($whereClause !== 'WHERE ()') {
                $sql .= ' ' . $criteria->renderWhere();
                if (!empty($notNullFields)) {
                    $sql .= $this->notNullFieldClause($notNullFields, true);
                }
            } elseif (!empty($notNullFields)) {
                $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
            }
        } elseif (!empty($notNullFields)) {
            $sql .= ' WHERE ' . $this->notNullFieldClause($notNullFields);
        }
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * @param  int $categoryid
     * @param  string $status
     * @param  string $notNullFields
     * @param                $criteriaPermissions
     * @return CriteriaCompo
     */
    private function getItemsCriteria($categoryid = -1, $status = '', $notNullFields = '', $criteriaPermissions)
    {
        //        global $publisherIsAdmin;
        //        $ret = 0;
        //        if (!$publisherIsAdmin) {
        //            $criteriaPermissions = new CriteriaCompo();
        //            // Categories for which user has access
        //            $categoriesGranted = $this->publisher->getHandler('permission')->getGrantedItems('category_read');
        //            if (!empty($categoriesGranted)) {
        //                $grantedCategories = new Criteria('categoryid', "(" . implode(',', $categoriesGranted) . ")", 'IN');
        //                $criteriaPermissions->add($grantedCategories, 'AND');
        //            } else {
        //                return $ret;
        //            }
        //        }
        if (isset($categoryid) && $categoryid != -1) {
            $criteriaCategory = new criteria('categoryid', $categoryid);
        }
        $criteriaStatus = new CriteriaCompo();
        if (!empty($status) && is_array($status)) {
            foreach ($status as $v) {
                $criteriaStatus->add(new Criteria('status', $v), 'OR');
            }
        } elseif (!empty($status) && $status != -1) {
            $criteriaStatus->add(new Criteria('status', $status), 'OR');
        }
        $criteria = new CriteriaCompo();
        if (!empty($criteriaCategory)) {
            $criteria->add($criteriaCategory);
        }
        if (!empty($criteriaPermissions)) {
            $criteria->add($criteriaPermissions);
        }
        if (!empty($criteriaStatus)) {
            $criteria->add($criteriaStatus);
        }

        return $criteria;
    }

    /**
     * @param        $categoryid
     * @param string $status
     * @param string $notNullFields
     *
     * @return int
     */
    public function getItemsCount($categoryid = -1, $status = '', $notNullFields = '')
    {

        //        global $publisherIsAdmin;
        $criteriaPermissions = '';
        if (!$GLOBALS['publisherIsAdmin']) {
            $criteriaPermissions = new CriteriaCompo();
            // Categories for which user has access
            $categoriesGranted = $this->publisher->getHandler('permission')->getGrantedItems('category_read');
            if (!empty($categoriesGranted)) {
                $grantedCategories = new Criteria('categoryid', '(' . implode(',', $categoriesGranted) . ')', 'IN');
                $criteriaPermissions->add($grantedCategories, 'AND');
            } else {
                return 0;
            }
        }
        //        $ret = array();
        $criteria = self::getItemsCriteria($categoryid, $status, $notNullFields, $criteriaPermissions);
        /*
                if (isset($categoryid) && $categoryid != -1) {
                    $criteriaCategory = new criteria('categoryid', $categoryid);
                }
                $criteriaStatus = new CriteriaCompo();
                if (!empty($status) && is_array($status)) {
                    foreach ($status as $v) {
                        $criteriaStatus->add(new Criteria('status', $v), 'OR');
                    }
                } elseif (!empty($status) && $status != -1) {
                    $criteriaStatus->add(new Criteria('status', $status), 'OR');
                }
                $criteria = new CriteriaCompo();
                if (!empty($criteriaCategory)) {
                    $criteria->add($criteriaCategory);
                }
                if (!empty($criteriaPermissions)) {
                    $criteria->add($criteriaPermissions);
                }
                if (!empty($criteriaStatus)) {
                    $criteria->add($criteriaStatus);
                }
        */
        $ret = $this->getCount($criteria, $notNullFields);

        return $ret;
    }

    /**
     * @param int $limit
     * @param int $start
     * @param int $categoryid
     * @param string $sort
     * @param string $order
     * @param string $notNullFields
     * @param bool $asObject
     * @param string $idKey
     *
     * @return array
     */
    public function getAllPublished($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $idKey = 'none')
    {
        $otherCriteria = new Criteria('datesub', time(), '<=');

        return $this->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED), $categoryid, $sort, $order, $notNullFields, $asObject, $otherCriteria, $idKey);
    }

    /**
     * @param PublisherItem $obj
     *
     * @return bool
     */
    public function getPreviousPublished($obj)
    {
        $ret           = false;
        $otherCriteria = new CriteriaCompo();
        $otherCriteria->add(new Criteria('datesub', $obj->getVar('datesub'), '<'));
        $objs = $this->getItems(1, 0, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED), $obj->getVar('categoryid'), 'datesub', 'DESC', '', true, $otherCriteria, 'none');
        if (count($objs) > 0) {
            $ret = $objs[0];
        }

        return $ret;
    }

    /**
     * @param PublisherItem $obj
     *
     * @return bool
     */
    public function getNextPublished($obj)
    {
        $ret           = false;
        $otherCriteria = new CriteriaCompo();
        $otherCriteria->add(new Criteria('datesub', $obj->getVar('datesub'), '>'));
        $otherCriteria->add(new Criteria('datesub', time(), '<='));
        $objs = $this->getItems(1, 0, array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED), $obj->getVar('categoryid'), 'datesub', 'ASC', '', true, $otherCriteria, 'none');
        if (count($objs) > 0) {
            $ret = $objs[0];
        }

        return $ret;
    }

    /**
     * @param int $limit
     * @param int $start
     * @param int $categoryid
     * @param string $sort
     * @param string $order
     * @param string $notNullFields
     * @param bool $asObject
     * @param string $idKey
     *
     * @return array
     */
    public function getAllSubmitted($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $idKey = 'none')
    {
        return $this->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_SUBMITTED), $categoryid, $sort, $order, $notNullFields, $asObject, null, $idKey);
    }

    /**
     * @param int $limit
     * @param int $start
     * @param int $categoryid
     * @param string $sort
     * @param string $order
     * @param string $notNullFields
     * @param bool $asObject
     * @param string $idKey
     *
     * @return array
     */
    public function getAllOffline($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $idKey = 'none')
    {
        return $this->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_OFFLINE), $categoryid, $sort, $order, $notNullFields, $asObject, null, $idKey);
    }

    /**
     * @param int $limit
     * @param int $start
     * @param int $categoryid
     * @param string $sort
     * @param string $order
     * @param string $notNullFields
     * @param bool $asObject
     * @param string $idKey
     *
     * @return array
     */
    public function getAllRejected($limit = 0, $start = 0, $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $idKey = 'none')
    {
        return $this->getItems($limit, $start, array(PublisherConstants::PUBLISHER_STATUS_REJECTED), $categoryid, $sort, $order, $notNullFields, $asObject, null, $idKey);
    }

    /**
     * @param  int $limit
     * @param  int $start
     * @param  string $status
     * @param  int $categoryid
     * @param  string $sort
     * @param  string $order
     * @param  string $notNullFields
     * @param  bool $asObject
     * @param  null $otherCriteria
     * @param  string $idKey
     * @return array
     * @internal param bool $asObject
     */
    public function getItems($limit = 0, $start = 0, $status = '', $categoryid = -1, $sort = 'datesub', $order = 'DESC', $notNullFields = '', $asObject = true, $otherCriteria = null, $idKey = 'none')
    {
        //        global $publisherIsAdmin;
        $criteriaPermissions = '';
        if (!$GLOBALS['publisherIsAdmin']) {
            $criteriaPermissions = new CriteriaCompo();
            // Categories for which user has access
            $categoriesGranted = $this->publisher->getHandler('permission')->getGrantedItems('category_read');
            if (!empty($categoriesGranted)) {
                $grantedCategories = new Criteria('categoryid', '(' . implode(',', $categoriesGranted) . ')', 'IN');
                $criteriaPermissions->add($grantedCategories, 'AND');
            } else {
                return array();
            }
        }

        $criteria = self::getItemsCriteria($categoryid, $status, $notNullFields, $criteriaPermissions);
        /*
                if (isset($categoryid) && $categoryid != -1) {
                    $criteriaCategory = new criteria('categoryid', $categoryid);
                }
                $criteriaStatus = new CriteriaCompo();
                if (!empty($status) && is_array($status)) {
                    foreach ($status as $v) {
                        $criteriaStatus->add(new Criteria('status', $v), 'OR');
                    }
                } elseif (!empty($status) && $status != -1) {
                    $criteriaStatus->add(new Criteria('status', $status), 'OR');
                }
                $criteria = new CriteriaCompo();
                if (!empty($criteriaCategory)) {
                    $criteria->add($criteriaCategory);
                }
                if (!empty($criteriaPermissions)) {
                    $criteria->add($criteriaPermissions);
                }
                if (!empty($criteriaStatus)) {
                    $criteria->add($criteriaStatus);
                }
        */
        //        $ret = array();

        if (!empty($otherCriteria)) {
            $criteria->add($otherCriteria);
        }
        $criteria->setLimit($limit);
        $criteria->setStart($start);
        $criteria->setSort($sort);
        $criteria->setOrder($order);
        $ret =& $this->getObjects($criteria, $idKey, $notNullFields);

        return $ret;
    }

    /**
     * @param string $field
     * @param string $status
     * @param int $categoryId
     *
     * @return bool
     */
    public function getRandomItem($field = '', $status = '', $categoryId = -1)
    {
        $ret           = false;
        $notNullFields = $field;
        // Getting the number of published Items
        $totalItems = $this->getItemsCount($categoryId, $status, $notNullFields);
        if ($totalItems > 0) {
            --$totalItems;
            mt_srand((double)microtime() * 1000000);
            $entryNumber = mt_rand(0, $totalItems);
            $item        = $this->getItems(1, $entryNumber, $status, $categoryId, $sort = 'datesub', $order = 'DESC', $notNullFields);
            if ($item) {
                $ret = $item[0];
            }
        }

        return $ret;
    }

    /**
     * delete Items matching a set of conditions
     *
     * @param CriteriaElement $criteria {@link CriteriaElement}
     *
     * @param bool $force
     * @param bool $asObject
     * @return bool FALSE if deletion failed
     */
    public function deleteAll(CriteriaElement $criteria = null, $force = true, $asObject = false) //deleteAll($criteria = null)
    {
        //todo resource consuming, use get list instead?
        $items =& $this->getObjects($criteria);
        foreach ($items as $item) {
            $this->delete($item);
        }

        return true;
    }

    /**
     * @param $itemid
     *
     * @return bool
     */
    public function updateCounter($itemid)
    {
        $sql = 'UPDATE ' . $this->db->prefix('publisher_items') . ' SET counter=counter+1 WHERE itemid = ' . $itemid;
        if ($this->db->queryF($sql)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string|array $notNullFields
     * @param bool $withAnd
     *
     * @return string
     */
    public function notNullFieldClause($notNullFields = '', $withAnd = false)
    {
        $ret = '';
        if ($withAnd) {
            $ret .= ' AND ';
        }
        if (!empty($notNullFields) && is_array($notNullFields)) {
            foreach ($notNullFields as $v) {
                $ret .= " ($v IS NOT NULL AND $v <> ' ' )";
            }
        } elseif (!empty($notNullFields)) {
            $ret .= " ($notNullFields IS NOT NULL AND $notNullFields <> ' ' )";
        }

        return $ret;
    }

    /**
     * @param array $queryarray
     * @param string $andor
     * @param int $limit
     * @param int $offset
     * @param int $userid
     * @param array $categories
     * @param int $sortby
     * @param string $searchin
     * @param string $extra
     *
     * @return array
     */
    public function getItemsFromSearch($queryarray = array(), $andor = 'AND', $limit = 0, $offset = 0, $userid = 0, $categories = array(), $sortby = 0, $searchin = '', $extra = '')
    {
        //        global $publisherIsAdmin;
        $ret          = array();
        $gpermHandler = xoops_getHandler('groupperm');
        $groups       = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
        $searchin     = empty($searchin) ? array('title', 'body', 'summary') : (is_array($searchin) ? $searchin : array($searchin));
        if (in_array('all', $searchin) || count($searchin) == 0) {
            $searchin = array('title', 'subtitle', 'body', 'summary', 'meta_keywords');
        }
        if (is_array($userid) && count($userid) > 0) {
            $userid       = array_map('intval', $userid);
            $criteriaUser = new CriteriaCompo();
            $criteriaUser->add(new Criteria('uid', '(' . implode(',', $userid) . ')', 'IN'), 'OR');
        } elseif (is_numeric($userid) && $userid > 0) {
            $criteriaUser = new CriteriaCompo();
            $criteriaUser->add(new Criteria('uid', $userid), 'OR');
        }
        $count = count($queryarray);
        if (is_array($queryarray) && $count > 0) {
            $criteriaKeywords = new CriteriaCompo();
            $elementCount     = count($queryarray);
            for ($i = 0; $i < $elementCount; ++$i) {
                $criteriaKeyword = new CriteriaCompo();
                if (in_array('title', $searchin)) {
                    $criteriaKeyword->add(new Criteria('title', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                }
                if (in_array('subtitle', $searchin)) {
                    $criteriaKeyword->add(new Criteria('subtitle', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                }
                if (in_array('body', $searchin)) {
                    $criteriaKeyword->add(new Criteria('body', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                }
                if (in_array('summary', $searchin)) {
                    $criteriaKeyword->add(new Criteria('summary', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                }
                if (in_array('meta_keywords', $searchin)) {
                    $criteriaKeyword->add(new Criteria('meta_keywords', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                }
                $criteriaKeywords->add($criteriaKeyword, $andor);
                unset($criteriaKeyword);
            }
        }
        if (!$GLOBALS['publisherIsAdmin'] && (count($categories) > 0)) {
            $criteriaPermissions = new CriteriaCompo();
            // Categories for which user has access
            $categoriesGranted = $gpermHandler->getItemIds('category_read', $groups, $this->publisher->getModule()->getVar('mid'));
            if (count($categories) > 0) {
                $categoriesGranted = array_intersect($categoriesGranted, $categories);
            }
            if (count($categoriesGranted) == 0) {
                return $ret;
            }
            $grantedCategories = new Criteria('categoryid', '(' . implode(',', $categoriesGranted) . ')', 'IN');
            $criteriaPermissions->add($grantedCategories, 'AND');
        } elseif (count($categories) > 0) {
            $criteriaPermissions = new CriteriaCompo();
            $grantedCategories   = new Criteria('categoryid', '(' . implode(',', $categories) . ')', 'IN');
            $criteriaPermissions->add($grantedCategories, 'AND');
        }
        $criteriaItemsStatus = new CriteriaCompo();
        $criteriaItemsStatus->add(new Criteria('status', PublisherConstants::PUBLISHER_STATUS_PUBLISHED));
        $criteria = new CriteriaCompo();
        if (!empty($criteriaUser)) {
            $criteria->add($criteriaUser, 'AND');
        }
        if (!empty($criteriaKeywords)) {
            $criteria->add($criteriaKeywords, 'AND');
        }
        if (!empty($criteriaPermissions)) {
            $criteria->add($criteriaPermissions);
        }
        if (!empty($criteriaItemsStatus)) {
            $criteria->add($criteriaItemsStatus, 'AND');
        }
        $criteria->setLimit($limit);
        $criteria->setStart($offset);
        if (empty($sortby)) {
            $sortby = 'datesub';
        }
        $criteria->setSort($sortby);
        $order = 'ASC';
        if ($sortby === 'datesub') {
            $order = 'DESC';
        }
        $criteria->setOrder($order);
        $ret =& $this->getObjects($criteria);

        return $ret;
    }

    /**
     * @param array $categoriesObj
     * @param array $status
     *
     * @return array
     */
    public function getLastPublishedByCat($categoriesObj, $status = array(PublisherConstants::PUBLISHER_STATUS_PUBLISHED))
    {
        $ret    = array();
        $catIds = array();
        foreach ($categoriesObj as $parentid) {
            foreach ($parentid as $category) {
                $catId          = $category->getVar('categoryid');
                $catIds[$catId] = $catId;
            }
        }
        if (empty($catIds)) {
            return $ret;
        }
        /*$cat = array();

        $sql = "SELECT categoryid, MAX(datesub) as date FROM " . $this->db->prefix('publisher_items') . " WHERE status IN (" . implode(',', $status) . ") GROUP BY categoryid";
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchArray($result)) {
            $cat[$row['categoryid']] = $row['date'];
        }
        if (count($cat) == 0) return $ret;
        $sql = "SELECT categoryid, itemid, title, short_url, uid, datesub FROM " . $this->db->prefix('publisher_items');
        $criteriaBig = new CriteriaCompo();
        foreach ($cat as $id => $date) {
            $criteria = new CriteriaCompo(new Criteria('categoryid', $id));
            $criteria->add(new Criteria('datesub', $date));
            $criteriaBig->add($criteria, 'OR');
            unset($criteria);
        }
        $sql .= " " . $criteriaBig->renderWhere();
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchArray($result)) {
            $item = new PublisherItem();
            $item->assignVars($row);
            $ret[$row['categoryid']] = $item;
            unset($item);
        }
        */
        $sql = 'SELECT mi.categoryid, mi.itemid, mi.title, mi.short_url, mi.uid, mi.datesub';
        $sql .= ' FROM (SELECT categoryid, MAX(datesub) AS date FROM ' . $this->db->prefix('publisher_items');
        $sql .= ' WHERE status IN (' . implode(',', $status) . ')';
        $sql .= ' AND categoryid IN (' . implode(',', $catIds) . ')';
        $sql .= ' GROUP BY categoryid)mo';
        $sql .= ' JOIN ' . $this->db->prefix('publisher_items') . ' mi ON mi.datesub = mo.date';
        $result = $this->db->query($sql);
        while (($row = $this->db->fetchArray($result)) !== false) {
            $item = new PublisherItem();
            $item->assignVars($row);
            $ret[$row['categoryid']] = $item;
            unset($item);
        }

        return $ret;
    }

    /**
     * @param         $parentid
     * @param         $catsCount
     * @param  string $spaces
     * @return int
     */
    public function countArticlesByCat($parentid, $catsCount, $spaces = '')
    {
        //        global $resultCatCounts;
        $newspaces = $spaces . '--';
        $thecount  = 0;
        foreach ($catsCount[$parentid] as $subCatId => $count) {
            $thecount += $count;
            $this->resultCatCounts[$subCatId] = $count;
            if (isset($catsCount[$subCatId])) {
                $thecount += $this->countArticlesByCat($subCatId, $catsCount, $newspaces);
                $this->resultCatCounts[$subCatId] = $thecount;
            }
        }

        return $thecount;
    }

    /**
     * @param int $catId
     * @param array $status
     * @param bool $inSubCat
     *
     * @return array
     */
    public function getCountsByCat($catId = 0, $status, $inSubCat = false)
    {
        //        global $resultCatCounts;
        $ret       = array();
        $catsCount = array();
        $sql       = 'SELECT c.parentid, i.categoryid, COUNT(*) AS count FROM ' . $this->db->prefix('publisher_items') . ' AS i INNER JOIN ' . $this->db->prefix('publisher_categories') . ' AS c ON i.categoryid=c.categoryid';
        if ((int)$catId > 0) {
            $sql .= ' WHERE i.categoryid = ' . (int)$catId;
            $sql .= ' AND i.status IN (' . implode(',', $status) . ')';
        } else {
            $sql .= ' WHERE i.status IN (' . implode(',', $status) . ')';
        }
        $sql .= ' GROUP BY i.categoryid ORDER BY c.parentid ASC, i.categoryid ASC';
        $result = $this->db->query($sql);
        if (!$result) {
            return $ret;
        }
        if (!$inSubCat) {
            while (($row = $this->db->fetchArray($result)) !== false) {
                $catsCount[$row['categoryid']] = $row['count'];
            }

            return $catsCount;
        }
        //        while ($row = $this->db->fetchArray($result)) {
        while (($row = $this->db->fetchArray($result)) !== false) {
            $catsCount[$row['parentid']][$row['categoryid']] = $row['count'];
        }
        //        $resultCatCounts = array();
        foreach ($catsCount[0] as $subCatId => $count) {
            $this->resultCatCounts[$subCatId] = $count;
            if (isset($catsCount[$subCatId])) {
                $this->resultCatCounts[$subCatId] += $this->countArticlesByCat($subCatId, $catsCount, '');
            }
        }

        return $this->resultCatCounts;
    }
}
