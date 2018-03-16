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
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * Class Publisher\Category
 */
class Category extends \XoopsObject
{
    /**
     * @var Publisher
     * @access public
     */
    public $helper;

    /**
     * @var array
     */
    public $categoryPath = false;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->helper = Publisher\Helper::getInstance();
        $this->initVar('categoryid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('parentid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 100);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false, 255);
        $this->initVar('image', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('total', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('weight', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('created', XOBJ_DTYPE_INT, null, false);
        $this->initVar('template', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('header', XOBJ_DTYPE_TXTAREA, null, false);
        $this->initVar('meta_keywords', XOBJ_DTYPE_TXTAREA, null, false);
        $this->initVar('meta_description', XOBJ_DTYPE_TXTAREA, null, false);
        $this->initVar('short_url', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('moderator', XOBJ_DTYPE_INT, null, false, 0);
        //not persistent values
        $this->initVar('itemcount', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('last_itemid', XOBJ_DTYPE_INT);
        $this->initVar('last_title_link', XOBJ_DTYPE_TXTBOX);
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 1, false);
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $arg = isset($args[0]) ? $args[0] : null;

        return $this->getVar($method, $arg);
    }

    /**
     * @return bool
     */
    public function notLoaded()
    {
        return ($this->getVar('categoryid') == -1);
    }

    /**
     * @return bool
     */
    public function checkPermission()
    {
        //        global $publisherIsAdmin;
        $ret = false;
        if ($GLOBALS['publisherIsAdmin']) {
            return true;
        }
        if (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->getVar('uid') == $this->moderator()) {
            return true;
        }
        $categoriesGranted = $this->helper->getHandler('Permission')->getGrantedItems('category_read');
        if (in_array($this->categoryid(), $categoriesGranted)) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @param string $format
     *
     * @return mixed|string
     */
    public function getImage($format = 's')
    {
        if ('' != $this->getVar('image')) {
            return $this->getVar('image', $format);
        } else {
            return 'blank.png';
        }
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function template($format = 'n')
    {
        return $this->getVar('template', $format);
    }

    /**
     * @param bool $withAllLink
     *
     * @return array|bool|string
     */
    public function getCategoryPath($withAllLink = true)
    {
        if (empty($this->categoryPath)) {
            if ($withAllLink) {
                $ret = $this->getCategoryLink();
            } else {
                $ret = $this->name();
            }
            $parentid = $this->parentid();
            if (0 != $parentid) {
                $parentObj = $this->helper->getHandler('Category')->get($parentid);
                //                if ($parentObj->notLoaded()) {
                //                    exit;
                //                }

                try {
                    if ($parentObj->notLoaded()) {
                        throw new RuntimeException(_NOPERM);
                    }
                } catch (Exception $e) {
                    $this->helper->addLog($e);
                    //                    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
                }

                $ret = $parentObj->getCategoryPath($withAllLink) . ' <li> ' . $ret . '</li>';
            }
            $this->categoryPath = $ret;
        }

        return $this->categoryPath;
    }

    /**
     * @return mixed|string
     */
    public function getCategoryPathForMetaTitle()
    {
        $ret      = '';
        $parentid = $this->parentid();
        if (0 != $parentid) {
            $parentObj = $this->helper->getHandler('Category')->get($parentid);
            //            if ($parentObj->notLoaded()) {
            //                exit('NOT LOADED');
            //            }

            try {
                if ($parentObj->notLoaded()) {
                    throw new RuntimeException('NOT LOADED');
                }
            } catch (Exception $e) {
                $this->helper->addLog($e);
                //                    redirect_header('javascript:history.go(-1)', 1, _NOPERM);
            }

            $ret = $parentObj->getCategoryPath(false);
            $ret = str_replace(' >', ' -', $ret);
        }

        return $ret;
    }

    /**
     * @return array|null
     */
    public function getGroupsRead()
    {
        return $this->helper->getHandler('Permission')->getGrantedGroupsById('category_read', $this->categoryid());
    }

    /**
     * @return array|null
     */
    public function getGroupsSubmit()
    {
        return $this->helper->getHandler('Permission')->getGrantedGroupsById('item_submit', $this->categoryid());
    }

    /**
     * @return array|null
     */
    public function getGroupsModeration()
    {
        return $this->helper->getHandler('Permission')->getGrantedGroupsById('category_moderation', $this->categoryid());
    }

    /**
     * @return string
     */
    public function getCategoryUrl()
    {
        return Publisher\Seo::generateUrl('category', $this->categoryid(), $this->short_url());
    }

    /**
     * @param bool $class
     *
     * @return string
     */
    public function getCategoryLink($class = false)
    {
        if ($class) {
            return "<a class='$class' href='" . $this->getCategoryUrl() . "'>" . $this->name() . '</a>';
        } else {
            return "<a href='" . $this->getCategoryUrl() . "'>" . $this->name() . '</a>';
        }
    }

    /**
     * @param bool $sendNotifications
     * @param bool $force
     *
     * @return mixed
     */
    public function store($sendNotifications = true, $force = true)
    {
        $ret = $this->helper->getHandler('Category')->insert($this, $force);
        if ($sendNotifications && $ret && $this->isNew()) {
            $this->sendNotifications();
        }
        $this->unsetNew();

        return $ret;
    }

    /**
     * Send notifications
     */
    public function sendNotifications()
    {
        $tags                  = [];
        $tags['MODULE_NAME']   = $this->helper->getModule()->getVar('name');
        $tags['CATEGORY_NAME'] = $this->name();
        $tags['CATEGORY_URL']  = $this->getCategoryUrl();
        /* @var  $notificationHandler XoopsNotificationHandler */
        $notificationHandler = xoops_getHandler('notification');
        $notificationHandler->triggerEvent('global_item', 0, 'category_created', $tags);
    }

    /**
     * @param array $category
     *
     * @return array
     */
    public function toArraySimple($category = [])
    {
        $category['categoryid']       = $this->categoryid();
        $category['name']             = $this->name();
        $category['categorylink']     = $this->getCategoryLink();
        $category['categoryurl']      = $this->getCategoryUrl();
        $category['total']            = ($this->getVar('itemcount') > 0) ? $this->getVar('itemcount') : '';
        $category['description']      = $this->description();
        $category['header']           = $this->header();
        $category['meta_keywords']    = $this->meta_keywords();
        $category['meta_description'] = $this->meta_description();
        $category['short_url']        = $this->short_url();
        if ($this->getVar('last_itemid') > 0) {
            $category['last_itemid']     = $this->getVar('last_itemid', 'n');
            $category['last_title_link'] = $this->getVar('last_title_link', 'n');
        }
        if ('blank.png' !== $this->getImage()) {
            $category['image_path'] = Publisher\Utility::getImageDir('category', false) . $this->getImage();
        } else {
            $category['image_path'] = '';
        }
        $category['lang_subcategories'] = sprintf(_CO_PUBLISHER_SUBCATEGORIES_INFO, $this->name());

        return $category;
    }

    /**
     * @param array $category
     *
     * @return array
     */
    public function toArrayTable($category = [])
    {
        $category['categoryid']   = $this->categoryid();
        $category['categorylink'] = $this->getCategoryLink();
        $category['total']        = ($this->getVar('itemcount') > 0) ? $this->getVar('itemcount') : '';
        $category['description']  = $this->description();
        if ($this->getVar('last_itemid') > 0) {
            $category['last_itemid']     = $this->getVar('last_itemid', 'n');
            $category['last_title_link'] = $this->getVar('last_title_link', 'n');
        }
        if ('blank.png' !== $this->getImage()) {
            $category['image_path'] = Publisher\Utility::getImageDir('category', false) . $this->getImage();
        } else {
            $category['image_path'] = '';
        }
        $category['lang_subcategories'] = sprintf(_CO_PUBLISHER_SUBCATEGORIES_INFO, $this->name());

        return $category;
    }

    /**
     *
     */
    public function createMetaTags()
    {
        $publisherMetagen = new Publisher\Metagen($this->name(), $this->meta_keywords(), $this->meta_description());
        $publisherMetagen->createMetaTags();
    }

    /**
     * @param int $subCatsCount
     *
     * @return Publisher\CategoryForm
     */
    public function getForm($subCatsCount = 4)
    {
//        require_once $GLOBALS['xoops']->path('modules/' . PUBLISHER_DIRNAME . '/class/form/category.php');
        $form = new Publisher\Form\CategoryForm($this, $subCatsCount);

        return $form;
    }
}
