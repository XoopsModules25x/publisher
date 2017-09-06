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
 * @copyright         The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license           http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package           Publisher
 * @subpackage        Include
 * @since             1.0
 * @author            trabis <lusopoemas@gmail.com>
 * @author            Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 */

define('MYTEXTSANITIZER_EXTENDED_MEDIA', 1);

/**
 * Class MyTextSanitizerExtension
 */
class MyTextSanitizerExtension
{
    /**
     * MyTextSanitizerExtension constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return MyTextSanitizerExtension
     */
    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * @param $patterns
     * @param $replacements
     */
    public function wmp(&$patterns, &$replacements)
    {
        $patterns[]     = "/\[wmp=(['\"]?)([^\"']*),([^\"']*)\\1]([^\"]*)\[\/wmp\]/sU";
        $rp             = "<object classid=\"clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6\" id=\"WindowsMediaPlayer\" width=\"\\2\" height=\"\\3\">\n";
        $rp             .= "<param name=\"URL\" value=\"\\4\">\n";
        $rp             .= "<param name=\"AutoStart\" value=\"0\">\n";
        $rp             .= "<embed autostart=\"0\" src=\"\\4\" type=\"video/x-ms-wmv\" width=\"\\2\" height=\"\\3\" controls=\"ImageWindow\" console=\"cons\"> </embed>";
        $rp             .= "</object>\n";
        $replacements[] = $rp;
    }

    /**
     * @param      $url
     * @param bool $width
     * @param bool $height
     *
     * @return string
     */
    public function displayFlash($url, $width = false, $height = false)
    {
        if (!$width || !$height) {
            if (!$dimension = @getimagesize($url)) {
                return "<a href='{$url}' target='_blank'>{$url}</a>";
            }
            if (!empty($width)) {
                $height = $dimension[1] * $width / $dimension[0];
            } elseif (!empty($height)) {
                $width = $dimension[0] * $height / $dimension[1];
            } else {
                list($width, $height) = [$dimension[0], $dimension[1]];
            }
        }

        $rp = "<object width='{$width}' height='{$height}' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0'>";
        $rp .= "<param name='movie' value='{$url}'>";
        $rp .= "<param name='QUALITY' value='high'>";
        $rp .= "<PARAM NAME='bgcolor' VALUE='#FFFFFF'>";
        $rp .= "<param name='wmode' value='transparent'>";
        $rp .= "<embed src='{$url}' width='{$width}' height='{$height}' quality='high' bgcolor='#FFFFFF' wmode='transparent'  pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash'></embed>";
        $rp .= '</object>';

        return $rp;
    }

    /**
     * @param $patterns
     * @param $replacements
     */
    public function flash(&$patterns, &$replacements)
    {
        $patterns[]     = "/\[(swf|flash)=(['\"]?)([^\"']*),([^\"']*)\\2]([^\"]*)\[\/\\1\]/esU";
        $replacements[] = "MyTextSanitizerExtension::displayFlash( '\\5', '\\3', '\\4' )";
    }

    /**
     * @param $patterns
     * @param $replacements
     */
    public function mms(&$patterns, &$replacements)
    {
        $patterns[]     = "/\[mms=(['\"]?)([^\"']*),([^\"']*)\\1]([^\"]*)\[\/mms\]/sU";
        $rp             = "<OBJECT id=videowindow1 height='\\3' width='\\2' classid='CLSID:6BF52A52-394A-11D3-B153-00C04F79FAA6'>";
        $rp             .= "<PARAM NAME=\"URL\" VALUE=\"\\4\">";
        $rp             .= '<PARAM NAME="rate" VALUE="1">';
        $rp             .= '<PARAM NAME="balance" VALUE="0">';
        $rp             .= '<PARAM NAME="currentPosition" VALUE="0">';
        $rp             .= '<PARAM NAME="defaultFrame" VALUE="">';
        $rp             .= '<PARAM NAME="playCount" VALUE="1">';
        $rp             .= '<PARAM NAME="autoStart" VALUE="0">';
        $rp             .= '<PARAM NAME="currentMarker" VALUE="0">';
        $rp             .= '<PARAM NAME="invokeURLs" VALUE="-1">';
        $rp             .= '<PARAM NAME="baseURL" VALUE="">';
        $rp             .= '<PARAM NAME="volume" VALUE="50">';
        $rp             .= '<PARAM NAME="mute" VALUE="0">';
        $rp             .= '<PARAM NAME="uiMode" VALUE="full">';
        $rp             .= '<PARAM NAME="stretchToFit" VALUE="0">';
        $rp             .= '<PARAM NAME="windowlessVideo" VALUE="0">';
        $rp             .= '<PARAM NAME="enabled" VALUE="-1">';
        $rp             .= '<PARAM NAME="enableContextMenu" VALUE="-1">';
        $rp             .= '<PARAM NAME="fullScreen" VALUE="0">';
        $rp             .= '<PARAM NAME="SAMIStyle" VALUE="">';
        $rp             .= '<PARAM NAME="SAMILang" VALUE="">';
        $rp             .= '<PARAM NAME="SAMIFilename" VALUE="">';
        $rp             .= '<PARAM NAME="captioningID" VALUE="">';
        $rp             .= '<PARAM NAME="enableErrorDialogs" VALUE="0">';
        $rp             .= '<PARAM NAME="_cx" VALUE="12700">';
        $rp             .= '<PARAM NAME="_cy" VALUE="8731">';
        $rp             .= '</OBJECT>';
        $replacements[] = $rp;
    }

    /**
     * @param $patterns
     * @param $replacements
     */
    public function rtsp(&$patterns, &$replacements)
    {
        $patterns[] = "/\[rtsp=(['\"]?)([^\"']*),([^\"']*)\\1]([^\"]*)\[\/rtsp\]/sU";
        $rp         = "<object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" HEIGHT='\\3' ID=Player WIDTH='\\2' VIEWASTEXT>";
        $rp         .= '<param NAME="_ExtentX" VALUE="12726">';
        $rp         .= '<param NAME="_ExtentY" VALUE="8520">';
        $rp         .= '<param NAME="AUTOSTART" VALUE="0">';
        $rp         .= '<param NAME="SHUFFLE" VALUE="0">';
        $rp         .= '<param NAME="PREFETCH" VALUE="0">';
        $rp         .= '<param NAME="NOLABELS" VALUE="0">';
        $rp         .= '<param NAME="CONTROLS" VALUE="ImageWindow">';
        $rp         .= '<param NAME="CONSOLE" VALUE="_master">';
        $rp         .= '<param NAME="LOOP" VALUE="0">';
        $rp         .= '<param NAME="NUMLOOP" VALUE="0">';
        $rp         .= '<param NAME="CENTER" VALUE="0">';
        $rp         .= '<param NAME="MAINTAINASPECT" VALUE="1">';
        $rp         .= '<param NAME="BACKGROUNDCOLOR" VALUE="#000000">';
        $rp         .= "<param NAME=\"SRC\" VALUE=\"\\4\">";
        $rp         .= "<embed autostart=\"0\" src=\"\\4\" type=\"audio/x-pn-realaudio-plugin\" HEIGHT='\\3' WIDTH='\\2' controls=\"ImageWindow\" console=\"cons\"> </embed>";
        $rp         .= '</object>';
        $rp         .= "<br><object CLASSID=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA HEIGHT=32 ID=Player WIDTH='\\2' VIEWASTEXT>";
        $rp         .= '<param NAME="_ExtentX" VALUE="18256">';
        $rp         .= '<param NAME="_ExtentY" VALUE="794">';
        $rp         .= '<param NAME="AUTOSTART" VALUE="0">';
        $rp         .= '<param NAME="SHUFFLE" VALUE="0">';
        $rp         .= '<param NAME="PREFETCH" VALUE="0">';
        $rp         .= '<param NAME="NOLABELS" VALUE="0">';
        $rp         .= '<param NAME="CONTROLS" VALUE="controlpanel">';
        $rp         .= '<param NAME="CONSOLE" VALUE="_master">';
        $rp         .= '<param NAME="LOOP" VALUE="0">';
        $rp         .= '<param NAME="NUMLOOP" VALUE="0">';
        $rp         .= '<param NAME="CENTER" VALUE="0">';
        $rp         .= '<param NAME="MAINTAINASPECT" VALUE="0">';
        $rp         .= '<param NAME="BACKGROUNDCOLOR" VALUE="#000000">';
        $rp         .= "<param NAME=\"SRC\" VALUE=\"\\4\">";
        $rp         .= "<embed autostart=\"0\" src=\"\\4\" type=\"audio/x-pn-realaudio-plugin\" HEIGHT='30' WIDTH='\\2' controls=\"ControlPanel\" console=\"cons\"> </embed>";
        $rp         .= '</object>';

        $replacements[] = $rp;
    }
}
