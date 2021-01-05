<?php

declare(strict_types=1);

namespace XoopsModules\Publisher;

/**
 * Class Cloner
 */
class Cloner
{
    // recursive cloning script
    /**
     * @param $path
     */
    public static function cloneFileFolder($path)
    {
        global $patKeys;
        global $patValues;

        $newPath = \str_replace($patKeys[0], $patValues[0], $path);

        if (\is_dir($path)) {
            // create new dir
            if (!\mkdir($newPath) && !\is_dir($newPath)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $newPath));
            }

            // check all files in dir, and process it
            $handle = \opendir($path);
            if ($handle) {
                while (false !== ($file = \readdir($handle))) {
                    if (0 !== \mb_strpos($file, '.')) {
                        self::cloneFileFolder("{$path}/{$file}");
                    }
                }
                \closedir($handle);
            }
        } else {
            $noChangeExtensions = ['jpeg', 'jpg', 'gif', 'png', 'zip', 'ttf'];
            if (\in_array(\mb_strtolower(\pathinfo($path, \PATHINFO_EXTENSION)), $noChangeExtensions, true)) {
                // image
                \copy($path, $newPath);
            } else {
                // file, read it
                $content = file_get_contents($path);
                $content = \str_replace($patKeys, $patValues, $content);
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
        if (!\extension_loaded('gd')) {
            return false;
        }
        $requiredFunctions = [
            'imagecreatefrompng',
            'imagecolorallocate',
            'imagefilledrectangle',
            'imagepng',
            'imagedestroy',
            'imagefttext',
            'imagealphablending',
            'imagesavealpha',
        ];
        foreach ($requiredFunctions as $func) {
            if (!\function_exists($func)) {
                return false;
            }
        }
        //            unset($func);

        if (!\file_exists($imageBase = $GLOBALS['xoops']->path('modules/' . $dirname . '/assets/images/logoModule.png'))
            || !\file_exists($font = $GLOBALS['xoops']->path('modules/' . $dirname . '/assets/images/VeraBd.ttf'))) {
            return false;
        }

        $imageModule = \imagecreatefrompng($imageBase);
        // save existing alpha channel
        imagealphablending($imageModule, false);
        imagesavealpha($imageModule, true);

        //Erase old text
        $greyColor = \imagecolorallocate($imageModule, 237, 237, 237);
        \imagefilledrectangle($imageModule, 5, 35, 85, 46, $greyColor);

        // Write text
        $textColor     = \imagecolorallocate($imageModule, 0, 0, 0);
        $spaceToBorder = (int)((80 - \mb_strlen($dirname) * 6.5) / 2);
        \imagefttext($imageModule, 8.5, 0, $spaceToBorder, 45, $textColor, $font, \ucfirst($dirname), []);

        // Set transparency color
        //$white = imagecolorallocatealpha($imageModule, 255, 255, 255, 127);
        //imagefill($imageModule, 0, 0, $white);
        //imagecolortransparent($imageModule, $white);

        \imagepng($imageModule, $GLOBALS['xoops']->path('modules/' . $dirname . '/assets/images/logoModule.png'));
        \imagedestroy($imageModule);

        return true;
    }
}
