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
 * @package         Class
 * @subpackage      Utils
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 */
// defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

class Publisher extends \Xmf\Module\Helper
{
    public $debugArray = [];

    /**
     * @param $debug
     */
    protected function __construct()
    {
        //        $this->debug   = $debug;
        $this->dirname = basename(dirname(__DIR__));
    }

    /**
     * @param bool $debug
     *
     * @return Publisher
     */
    public static function getInstance($debug = false)
    {
        static $instance;
        if (null === $instance) {
            $instance = new static($debug);
        }

        return $instance;
    }


    /**
     * @param null|string $name
     * @param null|string $value
     *
     * @return mixed
     */
    public function setConfig($name = null, $value = null)
    {
        if ($this->configs === null) {
            $this->initConfig();
        }
        $this->configs[$name] = $value;
        $this->addLog("Setting config '{$name}' : " . $this->configs[$name]);

        return $this->configs[$name];
    }

}
