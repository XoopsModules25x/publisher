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
 * animal module for xoops
 *
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GPL 2.0 or later
 * @package         Publisher
 * @subpackage      Config
 * @since           1.03
 * @author          XOOPS Development Team - ( http://xoops.org )
 */

require_once dirname(dirname(dirname(__DIR__))) . '/mainfile.php';

$moduleDirName = basename(dirname(__DIR__));

$capsDirName = strtoupper($moduleDirName);

if (!defined($capsDirName . '_DIRNAME')) {
    define($capsDirName . '_DIRNAME', $moduleDirName);
    define($capsDirName . '_PATH', XOOPS_ROOT_PATH . '/modules/' . constant($capsDirName . '_DIRNAME'));
    define($capsDirName . '_URL', XOOPS_URL . '/modules/' . constant($capsDirName . '_DIRNAME'));
    define($capsDirName . '_ADMIN', constant($capsDirName . '_URL') . '/admin/index.php');
    define($capsDirName . '_ROOT_PATH', XOOPS_ROOT_PATH . '/modules/' . constant($capsDirName . '_DIRNAME'));
    define($capsDirName . '_AUTHOR_LOGOIMG', constant($capsDirName . '_URL') . '/assets/images/logoModule.png');
}

// Define here the place where main upload path

//$img_dir = $GLOBALS['xoopsModuleConfig']['uploaddir'];

//define($capsDirName . '_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . constant($capsDirName . '_DIRNAME')); // WITHOUT Trailing slash
defined($capsDirName . '_UPLOAD_PATH') or define($capsDirName . '_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . constant($capsDirName . '_DIRNAME')); // WITHOUT Trailing slash

//Configurator
/*
return array(
    'name'           => 'Module Configurator',
    'uploadFolders'  => array(
        constant($capsDirName . '_UPLOAD_PATH'),
        constant($capsDirName . '_UPLOAD_PATH') . '/content',
        constant($capsDirName . '_UPLOAD_PATH') . '/images',
        constant($capsDirName . '_UPLOAD_PATH') . '/images/category',
        constant($capsDirName . '_UPLOAD_PATH') . '/images/thumbnails',
    ),
    'blankFiles' => array(
        constant($capsDirName . '_UPLOAD_PATH'),
        constant($capsDirName . '_UPLOAD_PATH') . '/images/category',
        constant($capsDirName . '_UPLOAD_PATH') . '/images/thumbnails',
    ),

    'templateFolders' => array(
        '/templates/',
        '/templates/blocks/',
        '/templates/admin/'

    ),
    'oldFiles'        => array(
        '/class/request.php',
        '/class/registry.php',
        '/class/utilities.php',
        '/class/util.php',
        '/include/constants.php',
        '/include/functions.php',
        '/ajaxrating.txt'
    ),
    'oldFolders'      => array(
        '/images',
        '/css',
        '/js',
        '/tcpdf',
    ),
);
*/

/**
 * Class ModuleConfigurator
 */
class ModuleConfigurator
{
    public $uploadFolders   = [];
    public $blankFiles  = [];
    public $templateFolders = [];
    public $oldFiles        = [];
    public $oldFolders      = [];
    public $name;

    /**
     * ModuleConfigurator constructor.
     */
    public function __construct()
    {
        $moduleDirName        = basename(dirname(__DIR__));
        $capsDirName          = strtoupper($moduleDirName);
        $this->name           = 'Module Configurator';
        $this->uploadFolders  = [
            constant($capsDirName . '_UPLOAD_PATH'),
            constant($capsDirName . '_UPLOAD_PATH') . '/content',
            constant($capsDirName . '_UPLOAD_PATH') . '/images',
            constant($capsDirName . '_UPLOAD_PATH') . '/images/category',
            constant($capsDirName . '_UPLOAD_PATH') . '/images/thumbnails',
        ];
        $this->blankFiles = [
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
