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
 *  Publisher class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: blockform.php 10374 2012-12-12 23:39:48Z trabis $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

xoops_load('XoopsForm');

/**
 * Form that will output formatted as a HTML table
 *
 * No styles and no JavaScript to check for required fields.
 */
class PublisherBlockForm extends XoopsForm
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('', '', '');
    }

    /**
     * create HTML to output the form as a table
     *
     * YOU SHOULD AVOID TO USE THE FOLLOWING Nocolspan METHOD, IT WILL BE REMOVED
     *
     * To use the noColspan simply use the following example:
     *
     * $colspan = new XoopsFormDhtmlTextArea( '', 'key', $value, '100%', '100%' );
     * $colspan->setNocolspan();
     * $form->addElement( $colspan );
     *
     * @return string
     */
    public function render()
    {
        $ret = '<table border="0" width="100%">' . NWLINE;
        foreach ($this->getElements() as $ele) {
            if (!$ele->isHidden()) {
                if (!$ele->getNocolspan()) {
                    $ret .= '<tr><td style="vertical-align: top; width: 250px;">';
                    $ret .= '<span style="font-weight: bold;">' . $ele->getCaption() . '</span>';
                    if (isset($eleDesc) && $eleDesc == $ele->getDescription()) {
                        $ret .= '<br /><br /><span style="font-weight: normal;">' . $eleDesc . '</span>';
                    }
                    $ret .= '</td><td>' . $ele->render() . '</td></tr>';
                } else {
                    $ret .= '<tr><td colspan="2">';
                    $ret .= '<span style="font-weight: bold;">' . $ele->getCaption() . '</span>';
                    $ret .= '</td></tr><tr><td>' . $ele->render() . '</td></tr>';
                }
            }
        }
        $ret .= '</table>';

        return $ret;
    }
}
