<?php namespace XoopsModules\Publisher;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Aidan Lister <aidan@php.net>
 * @link            http://aidanlister.com/2004/04/highlighting-a-search-string-in-html-text/
 */
class Highlighter
{
    /**
     * Perform a simple text replace
     * This should be used when the string does not contain HTML
     * (off by default)
     *
     * @var bool
     */
    protected $simple = false;

    /**
     * Only match whole words in the string
     * (off by default)
     *
     * @var bool
     */
    protected $wholeWords = false;

    /**
     * Case sensitive matching
     * (off by default)
     *
     * @var bool
     */
    protected $caseSens = false;

    /**
     * Overwrite links if matched
     * This should be used when the replacement string is a link
     * (off by default)
     */
    protected $stripLinks = false;

    /**
     * Style for the output string
     *
     * @var string
     */
    protected $replacementString = '<strong>\1</strong>';

    /**
     * @param bool $value
     */
    public function setSimple($value)
    {
        $this->simple = (bool)$value;
    }

    /**
     * @param bool $value
     */
    public function setWholeWords($value)
    {
        $this->wholeWords = (bool)$value;
    }

    /**
     * @param bool $value
     */
    public function setCaseSens($value)
    {
        $this->caseSens = (bool)$value;
    }

    /**
     * @param bool $value
     */
    public function setStripLinks($value)
    {
        $this->stripLinks = (bool)$value;
    }

    /**
     * @param string $value
     */
    public function setReplacementString($value)
    {
        $this->replacementString = (string)$value;
    }

    /**
     * Highlight a string in text without corrupting HTML tags
     *
     * @param string       $text   Haystack - The text to search
     * @param array|string $needle Needle - The string to highlight
     *
     * @return string $text with needle highlighted
     */
    public function highlight($text, $needle)
    {
        // Select pattern to use
        if ($this->simple) {
            $pattern   = '#(%s)#';
            $slPattern = '#(%s)#';
        } else {
            $pattern   = '#(?!<.*?)(%s)(?![^<>]*?>)#';
            $slPattern = '#<a\s(?:.*?)>(%s)</a>#';
        }
        // Case sensitivity
        if (!$this->caseSens) {
            $pattern   .= 'i';
            $slPattern .= 'i';
        }
        $needle = (array)$needle;
        foreach ($needle as $needleS) {
            $needleS = preg_quote($needleS);
            // Escape needle with optional whole word check
            if ($this->wholeWords) {
                $needleS = '\b' . $needleS . '\b';
            }
            // Strip links
            if ($this->stripLinks) {
                $slRegex = sprintf($slPattern, $needleS);
                $text    = preg_replace($slRegex, '\1', $text);
            }
            $regex = sprintf($pattern, $needleS);
            $text  = preg_replace($regex, $this->replacementString, $text);
        }

        return $text;
    }
}
