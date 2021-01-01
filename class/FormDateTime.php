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
 *  Publisher class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 */
require_once \dirname(__DIR__) . '/include/common.php';

/**
 * Class FormDateTime
 */
class FormDateTime extends \XoopsFormElementTray
{
    /**
     * @param          $caption
     * @param          $name
     * @param int      $size
     * @param int|bool $value
     * @param bool     $showtime
     * @param bool     $formatTimestamp
     */
    public function __construct($caption, $name, $size = 15, $value = 0, $showtime = true, $formatTimestamp = true)
    {
        parent::__construct($caption, '&nbsp;');
        $value = (int)$value;
        $value = ($value > 0) ? $value : \time();
        if ($formatTimestamp) {
            if (\is_object($GLOBALS['xoopsUser'])) {
                $timeoffset = $GLOBALS['xoopsUser']->getVar('timezone_offset');
            } else {
                $timeoffset = null;
            }
            $value = (int)\formatTimestamp($value, 'U', $timeoffset);
        }
        $datetime = \getdate($value);

        $this->addElement(new \XoopsFormTextDateSelect('', $name . '[date]', $size, $value));
        $timearray = [];
        for ($i = 0; $i < 24; ++$i) {
            for ($j = 0; $j < 60; $j += 10) {
                $key             = ($i * 3600) + ($j * 60);
                $timearray[$key] = (0 != $j) ? $i . ':' . $j : $i . ':0' . $j;
            }
        }
        \ksort($timearray);
        $timeselect = new \XoopsFormSelect('', $name . '[time]', $datetime['hours'] * 3600 + 600 * \floor($datetime['minutes'] / 10));
        $timeselect->addOptionArray($timearray);
        $this->addElement($timeselect);
    }
}
