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
 */

use Xmf\Request;
use XoopsModules\Publisher;

require_once __DIR__ . '/admin_header.php';

Publisher\Utility::cpHeader();
//publisher_adminMenu(-1, _AM_PUBLISHER_CLONE);
Publisher\Utility::openCollapsableBar('clone', 'cloneicon', _AM_PUBLISHER_CLONE, _AM_PUBLISHER_CLONE_DSC);

if ('submit' === Request::getString('op', '', 'POST')) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('clone.php', 3, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        //        exit();
    }

    //    $clone = $_POST['clone'];
    $clone = Request::getString('clone', '', 'POST');

    //check if name is valid
    if (empty($clone) || preg_match('/[^a-zA-Z0-9\_\-]/', $clone)) {
        redirect_header('clone.php', 3, sprintf(_AM_PUBLISHER_CLONE_INVALIDNAME, $clone));
        //        exit();
    }

    // Check wether the cloned module exists or not
    if ($clone && is_dir($GLOBALS['xoops']->path('modules/' . $clone))) {
        redirect_header('clone.php', 3, sprintf(_AM_PUBLISHER_CLONE_EXISTS, $clone));
    }

    $patterns = [
        strtolower(PUBLISHER_DIRNAME)          => strtolower($clone),
        strtoupper(PUBLISHER_DIRNAME)          => strtoupper($clone),
        ucfirst(strtolower(PUBLISHER_DIRNAME)) => ucfirst(strtolower($clone))
    ];

    $patKeys   = array_keys($patterns);
    $patValues = array_values($patterns);
    PublisherClone::cloneFileFolder(PUBLISHER_ROOT_PATH);
    $logocreated = PublisherClone::createLogo(strtolower($clone));

    $msg = '';
    if (is_dir($GLOBALS['xoops']->path('modules/' . strtolower($clone)))) {
        $msg .= sprintf(_AM_PUBLISHER_CLONE_CONGRAT, "<a href='" . XOOPS_URL . "/modules/system/admin.php?fct=modulesadmin'>" . ucfirst(strtolower($clone)) . '</a>') . "<br>\n";
        if (!$logocreated) {
            $msg .= _AM_PUBLISHER_CLONE_IMAGEFAIL;
        }
    } else {
        $msg .= _AM_PUBLISHER_CLONE_FAIL;
    }
    echo $msg;
} else {
    require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
    $form  = new \XoopsThemeForm(sprintf(_AM_PUBLISHER_CLONE_TITLE, $helper->getModule()->getVar('name', 'E')), 'clone', 'clone.php', 'post', true);
    $clone = new \XoopsFormText(_AM_PUBLISHER_CLONE_NAME, 'clone', 20, 20, '');
    $clone->setDescription(_AM_PUBLISHER_CLONE_NAME_DSC);
    $form->addElement($clone, true);
    $form->addElement(new \XoopsFormHidden('op', 'submit'));
    $form->addElement(new \XoopsFormButton('', '', _SUBMIT, 'submit'));
    $form->display();
}

// End of collapsable bar
Publisher\Utility::closeCollapsableBar('clone', 'cloneicon');

require_once __DIR__ . '/admin_footer.php';

// work around for PHP < 5.0.x
/*
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data, $file_append = false)
    {
        if ($fp == fopen($filename, (!$file_append ? 'w+' : 'a+'))) {
            fwrite($fp, $data);
            fclose($fp);
        }
    }
}
*/

/**
 * Class PublisherClone
 */
class PublisherClone
{
    // recursive cloning script
    /**
     * @param $path
     */
    public static function cloneFileFolder($path)
    {
        global $patKeys;
        global $patValues;

        $newPath = str_replace($patKeys[0], $patValues[0], $path);

        if (is_dir($path)) {
            // create new dir
            mkdir($newPath);

            // check all files in dir, and process it
            if ($handle = opendir($path)) {
                while (false !== ($file = readdir($handle))) {
                    if (0 !== strpos($file, '.')) {
                        self::cloneFileFolder("{$path}/{$file}");
                    }
                }
                closedir($handle);
            }
        } else {
            $noChangeExtensions = ['jpeg', 'jpg', 'gif', 'png', 'zip', 'ttf'];
            if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $noChangeExtensions)) {
                // image
                copy($path, $newPath);
            } else {
                // file, read it
                $content = file_get_contents($path);
                $content = str_replace($patKeys, $patValues, $content);
                file_put_contents($newPath, $content);
            }
        }
    }

    /**
     * @param $dirname
     *
     * @return bool
     */
    public static function createLogo($dirname)
    {
        if (!extension_loaded('gd')) {
            return false;
        } else {
            $requiredFunctions = [
                'imagecreatefrompng',
                'imagecolorallocate',
                'imagefilledrectangle',
                'imagepng',
                'imagedestroy',
                'imagefttext',
                'imagealphablending',
                'imagesavealpha'
            ];
            foreach ($requiredFunctions as $func) {
                if (!function_exists($func)) {
                    return false;
                }
            }
            //            unset($func);
        }

        if (!file_exists($imageBase = $GLOBALS['xoops']->path('modules/' . $dirname . '/assets/images/logoModule.png'))
            || !file_exists($font = $GLOBALS['xoops']->path('modules/' . $dirname . '/assets/images/VeraBd.ttf'))) {
            return false;
        }

        $imageModule = imagecreatefrompng($imageBase);
        // save existing alpha channel
        imagealphablending($imageModule, false);
        imagesavealpha($imageModule, true);

        //Erase old text
        $greyColor = imagecolorallocate($imageModule, 237, 237, 237);
        imagefilledrectangle($imageModule, 5, 35, 85, 46, $greyColor);

        // Write text
        $textColor     = imagecolorallocate($imageModule, 0, 0, 0);
        $spaceToBorder = (80 - strlen($dirname) * 6.5) / 2;
        imagefttext($imageModule, 8.5, 0, $spaceToBorder, 45, $textColor, $font, ucfirst($dirname), []);

        // Set transparency color
        //$white = imagecolorallocatealpha($imageModule, 255, 255, 255, 127);
        //imagefill($imageModule, 0, 0, $white);
        //imagecolortransparent($imageModule, $white);

        imagepng($imageModule, $GLOBALS['xoops']->path('modules/' . $dirname . '/assets/images/logoModule.png'));
        imagedestroy($imageModule);

        return true;
    }
}
