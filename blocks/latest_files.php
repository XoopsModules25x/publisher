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
 * @subpackage      Blocks
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher;
use XoopsModules\Publisher\Constants;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_latest_files_show($options)
{
    $helper = Publisher\Helper::getInstance();

    /**
     * $options[0] : Category
     * $options[1] : Sort order - datesub | counter
     * $options[2] : Number of files to display
     * $oprions[3] : bool TRUE to link to the file download, FALSE to link to the article
     */

    $block = [];

    $sort           = $options[1];
    $order          = Publisher\Utility::getOrderBy($sort);
    $limit          = $options[2];
    $directDownload = $options[3];

    // creating the files objects
    $filesObj = $helper->getHandler('File')->getAllFiles(0, Constants::PUBLISHER_STATUS_FILE_ACTIVE, $limit, 0, $sort, $order, explode(',', $options[0]));
    foreach ($filesObj as $fileObj) {
        $aFile         = [];
        $aFile['link'] = $directDownload ? $fileObj->getFileLink() : $fileObj->getItemLink();
        if ('datesub' === $sort) {
            $aFile['new'] = $fileObj->getDatesub();
        } elseif ('counter' === $sort) {
            $aFile['new'] = $fileObj->counter();
        } elseif ('weight' === $sort) {
            $aFile['new'] = $fileObj->weight();
        }
        $block['files'][] = $aFile;
    }

    return $block;
}

/**
 * @param $options
 *
 * @return string
 */
function publisher_latest_files_edit($options)
{
    // require_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new Publisher\BlockForm();

    $catEle   = new \XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, Publisher\Utility::createCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new \XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray([
                                  'datesub' => _MB_PUBLISHER_DATE,
                                  'counter' => _MB_PUBLISHER_HITS,
                                  'weight'  => _MB_PUBLISHER_WEIGHT
                              ]);
    $dispEle   = new \XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);
    $directEle = new \XoopsFormRadioYN(_MB_PUBLISHER_DIRECTDOWNLOAD, 'options[3]', $options[3]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);
    $form->addElement($directEle);

    return $form->render();
}
