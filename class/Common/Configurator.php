<?php namespace XoopsModules\Publisher\Common;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
/**
 * Configurator Class
 *
 * @copyright   XOOPS Project (https://xoops.org)
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      XOOPS Development Team
 * @package     Publisher
 * @since       1.05
 *
 */

require_once __DIR__ . '/../../include/common.php';
require_once __DIR__ . '/../../include/config.php';

//namespace Publisher;

/**
 * Class Configurator
 */
class Configurator
{
    public $uploadFolders   = [];
    public $blankFiles      = [];
    public $templateFolders = [];
    public $oldFiles        = [];
    public $oldFolders      = [];
    public $name;

    /**
     * Configurator constructor.
     */
    public function __construct()
    {
        $moduleDirName       = basename(dirname(dirname(__DIR__)));
        $capsDirName         = strtoupper($moduleDirName);
        $this->name          = 'Module Configurator';
        $this->uploadFolders = [
            constant($capsDirName . '_UPLOAD_PATH'),
            constant($capsDirName . '_UPLOAD_PATH') . '/content',
            constant($capsDirName . '_UPLOAD_PATH') . '/images',
            constant($capsDirName . '_UPLOAD_PATH') . '/images/category',
            constant($capsDirName . '_UPLOAD_PATH') . '/images/thumbnails',
        ];
        $this->blankFiles    = [
            constant($capsDirName . '_UPLOAD_PATH'),
            constant($capsDirName . '_UPLOAD_PATH') . '/images/category',
            constant($capsDirName . '_UPLOAD_PATH') . '/images/thumbnails',
        ];

        $this->templateFolders = [
            '/templates/',
            '/templates/blocks/',
            '/templates/admin/'

        ];
        $this->oldFiles        = [
            '/class/request.php',
            '/class/registry.php',
            '/class/utilities.php',
            '/class/util.php',
            '/include/constants.php',
            '/include/functions.php',
            '/ajaxrating.txt'
        ];
        $this->oldFolders      = [
            '/images',
            '/css',
            '/js',
            '/tcpdf',
        ];
    }
}
