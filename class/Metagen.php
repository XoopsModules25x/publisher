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
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 */

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * Class Metagen
 */
class Metagen
{
    /**
     * @var Publisher
     * @access public
     */
    public $helper;

    /**
     * @var MyTextSanitizer
     */
    public $myts;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $originalTitle;

    /**
     * @var string
     */
    public $keywords;

    /**
     * @var string
     */
    public $categoryPath;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     *
     */
    public $minChar = 4;

    /**
     * @param string $title
     * @param string $keywords
     * @param string $description
     * @param string $categoryPath
     */
    public function __construct($title, $keywords = '', $description = '', $categoryPath = '')
    {
        $this->helper = Publisher\Helper::getInstance();
        $this->myts      = \MyTextSanitizer::getInstance();
        $this->setCategoryPath($categoryPath);
        $this->setTitle($title);
        $this->setDescription($description);
        if ('' == $keywords) {
            $keywords = $this->createMetaKeywords();
        }
        $this->setKeywords($keywords);
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title         = $this->html2text($title);
        $this->originalTitle = $this->title;
        $titleTag            = [];
        $titleTag['module']  = $this->helper->getModule()->getVar('name');
        if (isset($this->title) && ('' != $this->title) && (strtoupper($this->title) != strtoupper($titleTag['module']))) {
            $titleTag['title'] = $this->title;
        }
        if (isset($this->categoryPath) && ('' != $this->categoryPath)) {
            $titleTag['category'] = $this->categoryPath;
        }
        $ret = isset($titleTag['title']) ? $titleTag['title'] : '';
        if (isset($titleTag['category']) && '' != $titleTag['category']) {
            if ('' != $ret) {
                $ret .= ' - ';
            }
            $ret .= $titleTag['category'];
        }
        if (isset($titleTag['module']) && '' != $titleTag['module']) {
            if ('' != $ret) {
                $ret .= ' - ';
            }
            $ret .= $titleTag['module'];
        }
        $this->title = $ret;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @param string $categoryPath
     */
    public function setCategoryPath($categoryPath)
    {
        $categoryPath       = $this->html2text($categoryPath);
        $this->categoryPath = $categoryPath;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $description       = $this->html2text($description);
        $description       = $this->purifyText($description);
        $this->description = $description;
    }

    /**
     * Does nothing
     */
    public function createTitleTag()
    {
    }

    /**
     * @param int $maxWords
     *
     * @return string
     */
    public function createMetaDescription($maxWords = 30)
    {
        $description = $this->purifyText($this->description);
        $description = $this->html2text($description);
        $words       = explode(' ', $description);
        $ret         = '';
        $i           = 1;
        $wordCount   = count($words);
        foreach ($words as $word) {
            $ret .= $word;
            if ($i < $wordCount) {
                $ret .= ' ';
            }
            ++$i;
        }

        return $ret;
    }

    /**
     * @param string $text
     * @param int    $minChar
     *
     * @return array
     */
    public function findMetaKeywords($text, $minChar)
    {
        $keywords         = [];
        $text             = $this->purifyText($text);
        $text             = $this->html2text($text);
        $originalKeywords = explode(' ', $text);
        foreach ($originalKeywords as $originalKeyword) {
            $secondRoundKeywords = explode("'", $originalKeyword);
            foreach ($secondRoundKeywords as $secondRoundKeyword) {
                if (strlen($secondRoundKeyword) >= $minChar) {
                    if (!in_array($secondRoundKeyword, $keywords)) {
                        $keywords[] = trim($secondRoundKeyword);
                    }
                }
            }
        }

        return $keywords;
    }

    /**
     * @return string
     */
    public function createMetaKeywords()
    {
        $keywords       = $this->findMetaKeywords($this->originalTitle . ' ' . $this->description, $this->minChar);
        $moduleKeywords = $this->helper->getConfig('seo_meta_keywords');
        if ('' != $moduleKeywords) {
            $moduleKeywords = explode(',', $moduleKeywords);
            $keywords       = array_merge($keywords, array_map('trim', $moduleKeywords));
        }
        $ret = implode(',', $keywords);

        return $ret;
    }

    /**
     * Does nothing
     */
    public function autoBuildMetaKeywords()
    {
    }

    /**
     * Build Metatags
     */
    public function buildAutoMetaTags()
    {
        $this->keywords    = $this->createMetaKeywords();
        $this->description = $this->createMetaDescription();
        //$this->title = $this->createTitleTag();
    }

    /**
     * Creates meta tags
     */
    public function createMetaTags()
    {
        global $xoopsTpl, $xoTheme;
        if ('' != $this->keywords) {
            $xoTheme->addMeta('meta', 'keywords', $this->keywords);
        }
        if ('' != $this->description) {
            $xoTheme->addMeta('meta', 'description', $this->description);
        }
        if ('' != $this->title) {
            $xoopsTpl->assign('xoops_pagetitle', $this->title);
        }
    }

    /**
     * Return true if the string is length > 0
     *
     * @credit psylove
     * @var    string $string Chaine de caractère
     * @return boolean
     */
    public static function emptyString($var)
    {
        return (strlen($var) > 0);
    }

    /**
     * Create a title for the short_url field of an article
     *
     * @credit psylove
     *
     * @param string $title   title of the article
     * @param bool   $withExt do we add an html extension or not
     *
     * @return string short url for article
     */
    public static function generateSeoTitle($title = '', $withExt = true)
    {
        // Transformation de la chaine en minuscule
        // Codage de la chaine afin d'éviter les erreurs 500 en cas de caractères imprévus
        $title = rawurlencode(strtolower($title));
        // Transformation des ponctuations

        $pattern = [
            '/%09/', // Tab
            '/%20/', // Space
            '/%21/', // !
            '/%22/', // "
            '/%23/', // #
            '/%25/', // %
            '/%26/', // &
            '/%27/', // '
            '/%28/', // (
            '/%29/', // )
            '/%2C/', // ,
            '/%2F/', // /
            '/%3A/', // :
            '/%3B/', // ;
            '/%3C/', // <
            '/%3D/', // =
            '/%3E/', // >
            '/%3F/', // ?
            '/%40/', // @
            '/%5B/', // [
            '/%5C/', // \
            '/%5D/', // ]
            '/%5E/', // ^
            '/%7B/', // {
            '/%7C/', // |
            '/%7D/', // }
            '/%7E/', // ~
            "/\./" // .
        ];
        $repPat  = ['-', '-', '-', '-', '-', '-100', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-at-', '-', '-', '-', '-', '-', '-', '-', '-', '-'];
        $title   = str_replace($pattern, $repPat, $title);
        // Transformation des caractères accentués
        $pattern = [
            '/%B0/', // °
            '/%E8/', // è
            '/%E9/', // é
            '/%EA/', // ê
            '/%EB/', // ë
            '/%E7/', // ç
            '/%E0/', // à
            '/%E2/', // â
            '/%E4/', // ä
            '/%EE/', // î
            '/%EF/', // ï
            '/%F9/', // ù
            '/%FC/', // ü
            '/%FB/', // û
            '/%F4/', // ô
            '/%F6/', // ö
        ];
        $repPat  = ['-', 'e', 'e', 'e', 'e', 'c', 'a', 'a', 'a', 'i', 'i', 'u', 'u', 'u', 'o', 'o'];
        $title   = str_replace($pattern, $repPat, $title);
        $tableau = explode('-', $title); // Transforms the string in table //Transforme la chaine de caractères en tableau
        $tableau = array_filter($tableau, ['Metagen', 'emptyString']); // Remove empty strings of the table //Supprime les chaines vides du tableau
        $title   = implode('-', $tableau); // Transforms a character string in table separated by a hyphen //Transforme un tableau en chaine de caractères séparé par un tiret
        if (count($title) > 0) {
            if ($withExt) {
                $title .= '.html';
            }

            return $title;
        }

        return '';
    }

    /**
     * @param      $text
     * @param bool $keyword
     *
     * @return mixed
     */
    public function purifyText($text, $keyword = false)
    {
        //        $text = str_replace(['&nbsp;', ' '], ['<br>', ' '], $text); //for php 5.4
        $text = str_replace('&nbsp;', ' ', $text);
        $text = str_replace('<br>', ' ', $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
        $text = $this->myts->undoHtmlSpecialChars($text);

        $text = str_replace(')', ' ', $text);
        $text = str_replace('(', ' ', $text);
        $text = str_replace(':', ' ', $text);
        $text = str_replace('&euro', ' euro ', $text);
        $text = str_replace('&hellip', '...', $text);
        $text = str_replace('&rsquo', ' ', $text);
        $text = str_replace('!', ' ', $text);
        $text = str_replace('?', ' ', $text);
        $text = str_replace('"', ' ', $text);
        $text = str_replace('-', ' ', $text);
        $text = str_replace('\n', ' ', $text);

        //        $text = str_replace([')','(',':','&euro','&hellip','&rsquo','!','?','"','-','\n'], [' ' , ' ',  ' ',  ' euro ',  '...',  ' ', ' ', ' ',  ' ', ' ',  ' '], $text); //for PHP 5.4

        if ($keyword) {
            $text = str_replace('.', ' ', $text);
            $text = str_replace(',', ' ', $text);
            $text = str_replace('\'', ' ', $text);
            //            $text = str_replace(['.', ' '], [',', ' '], ['\'', ' '], $text); //for PHP 5.4
        }
        $text = str_replace(';', ' ', $text);

        return $text;
    }

    /**
     * @param string $document
     *
     * @return mixed
     */
    public function html2text($document)
    {
        // PHP Manual:: function preg_replace
        // $document should contain an HTML document.
        // This will remove HTML tags, javascript sections
        // and white space. It will also convert some
        // common HTML entities to their text equivalent.
        // Credits : newbb2
        $search = [
            "'<script[^>]*?>.*?</script>'si", // Strip out javascript
            "'<img.*?>'si", // Strip out img tags
            "'<[\/\!]*?[^<>]*?>'si", // Strip out HTML tags
            "'([\r\n])[\s]+'", // Strip out white space
            "'&(quot|#34);'i", // Replace HTML entities
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i"
        ]; // evaluate as php

        $replace = [
            '',
            '',
            '',
            "\\1",
            '"',
            '&',
            '<',
            '>',
            ' ',
            chr(161),
            chr(162),
            chr(163),
            chr(169)
        ];

        $text = preg_replace($search, $replace, $document);

        preg_replace_callback('/&#(\d+);/', function ($matches) {
            return chr($matches[1]);
        }, $document);

        return $text;
    }
}
