<?php namespace XoopsModules\Publisher;

/*
 * $Id
 * Module: Publisher
 * Author: Sudhaker Raj <http://xoops.biz>
 * Licence: GNU
 */

// define PUBLISHER_SEO_ENABLED in mainfile.php, possible values
//   are "rewrite" & "path-info"

/**
 * Create a title for the shortUrl field of an article
 *
 * @credit psylove
 *
 * @var    string $title   title of the article
 * @var    string $withExt do we add an html extension or not
 * @return string sort_url for the article
 */

use XoopsModules\Publisher;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * Class Seo
 */
class Seo
{
    /**
     * @param string $title
     * @param bool   $withExt
     *
     * @return mixed|string
     */
    public static function getTitle($title = '', $withExt = true)
    {

        /**
         * if XOOPS ML is present, let's sanitize the title with the current language
         */
        $myts = \MyTextSanitizer::getInstance();
        if (method_exists($myts, 'formatForML')) {
            $title = $myts->formatForML($title);
        }

        // Transformation de la chaine en minuscule
        // Codage de la chaine afin d'éviter les erreurs 500 en cas de caractères imprévus
        $title = rawurlencode(strtolower($title));

        // Transformation des ponctuations
        $pattern    = [
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
        $repPattern = ['-', '-', '', '', '', '-100', '', '-', '', '', '', '-', '', '', '', '-', '', '', '-at-', '', '-', '', '-', '', '-', '', '-', ''];
        $title      = preg_replace($pattern, $repPattern, $title);

        // Transformation des caractères accentués
        //                  è        é        ê        ë        ç        à        â        ä        î        ï        ù        ü        û        ô        ö
        $pattern    = ['/%B0/', '/%E8/', '/%E9/', '/%EA/', '/%EB/', '/%E7/', '/%E0/', '/%E2/', '/%E4/', '/%EE/', '/%EF/', '/%F9/', '/%FC/', '/%FB/', '/%F4/', '/%F6/'];
        $repPattern = ['-', 'e', 'e', 'e', 'e', 'c', 'a', 'a', 'a', 'i', 'i', 'u', 'u', 'u', 'o', 'o'];
        $title      = preg_replace($pattern, $repPattern, $title);

        if (count($title) > 0) {
            if ($withExt) {
                $title .= '.html';
            }

            return $title;
        }

        return '';
    }

    /**
     * @param        $op
     * @param        $id
     * @param string $shortUrl
     *
     * @return string
     */
    public static function generateUrl($op, $id, $shortUrl = '')
    {
        $helper = Publisher\Helper::getInstance();
        if ('none' !== $helper->getConfig('seo_url_rewrite')) {
            if (!empty($shortUrl)) {
                $shortUrl .= '.html';
            }

            if ('htaccess' === $helper->getConfig('seo_url_rewrite')) {
                // generate SEO url using htaccess
                return XOOPS_URL . '/' . $helper->getConfig('seo_module_name') . ".${op}.${id}/${shortUrl}";
            } elseif ('path-info' === $helper->getConfig('seo_url_rewrite')) {
                // generate SEO url using path-info
                return PUBLISHER_URL . "/index.php/${op}.${id}/${shortUrl}";
            } else {
                exit('Unknown SEO method.');
            }
        } else {
            // generate classic url
            switch ($op) {
                case 'category':
                    return PUBLISHER_URL . "/${op}.php?categoryid=${id}";
                case 'item':
                case 'print':
                    return PUBLISHER_URL . "/${op}.php?itemid=${id}";
                default:
                    exit('Unknown SEO operation.');
            }
        }
    }
}
