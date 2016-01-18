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
 * @version         $Id: latest_files.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

include_once dirname(__DIR__) . '/include/common.php';

/**
 * @param $options
 *
 * @return array
 */
function publisher_latest_files_show($options)
{
    $publisher =& PublisherPublisher::getInstance();
    /**
     * $options[0] : Category
     * $options[1] : Sort order - datesub | counter
     * $options[2] : Number of files to display
     * $oprions[3] : bool TRUE to link to the file download, FALSE to link to the article
     */

    $block = array();

    $sort           = $options[1];
    $order          = publisherGetOrderBy($sort);
    $limit          = $options[2];
    $directDownload = $options[3];

    // creating the files objects
    $filesObj =& $publisher->getHandler('file')->getAllFiles(0, PublisherConstants::PUBLISHER_STATUS_FILE_ACTIVE, $limit, 0, $sort, $order, explode(',', $options[0]));
    foreach ($filesObj as $fileObj) {
        $aFile         = array();
        $aFile['link'] = $directDownload ? $fileObj->getFileLink() : $fileObj->getItemLink();
        if ($sort === 'datesub') {
            $aFile['new'] = $fileObj->getDatesub();
        } elseif ($sort === 'counter') {
            $aFile['new'] = $fileObj->counter();
        } elseif ($sort === 'weight') {
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
    include_once PUBLISHER_ROOT_PATH . '/class/blockform.php';
    xoops_load('XoopsFormLoader');

    $form = new PublisherBlockForm();

    $catEle   = new XoopsFormLabel(_MB_PUBLISHER_SELECTCAT, publisherCreateCategorySelect($options[0], 0, true, 'options[0]'));
    $orderEle = new XoopsFormSelect(_MB_PUBLISHER_ORDER, 'options[1]', $options[1]);
    $orderEle->addOptionArray(array(
                                  'datesub' => _MB_PUBLISHER_DATE,
                                  'counter' => _MB_PUBLISHER_HITS,
                                  'weight'  => _MB_PUBLISHER_WEIGHT));
    $dispEle   = new XoopsFormText(_MB_PUBLISHER_DISP, 'options[2]', 10, 255, $options[2]);
    $directEle = new XoopsFormRadioYN(_MB_PUBLISHER_DIRECTDOWNLOAD, 'options[3]', $options[3]);

    $form->addElement($catEle);
    $form->addElement($orderEle);
    $form->addElement($dispEle);
    $form->addElement($directEle);

    return $form->render();
}
