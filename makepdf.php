<?php

declare(strict_types=1);

/**
 * File : makepdf.php for publisher
 * For tcpdf_for_xoops 2.01 and higher
 * Created by montuy337513 / philodenelle - http://www.chg-web.org
 */

use Xmf\Request;
use XoopsModules\Publisher\{
    Helper,
    Utility
};
/** @var Helper $helper */

error_reporting(E_ALL);

require_once __DIR__ . '/header.php';

$itemId     = Request::getInt('itemid', 0, 'GET');
$itemPageId = Request::getInt('page', -1, 'GET');
if (0 == $itemId) {
    redirect_header('<script>javascript:history.go(-1)</script>', 1, _MD_PUBLISHER_NOITEMSELECTED);
}

//2.5.8
require_once XOOPS_ROOT_PATH . '/class/libraries/vendor/tecnickcom/tcpdf/tcpdf.php';

// Creating the item object for the selected item
$itemObj = $helper->getHandler('Item')->get($itemId);

// if the selected item was not found, exit
if (!$itemObj) {
    redirect_header('<script>javascript:history.go(-1)</script>', 1, _MD_PUBLISHER_NOITEMSELECTED);
}

// Creating the category object that holds the selected item
$categoryObj = $helper->getHandler('Category')->get($itemObj->categoryid());

// Check user permissions to access that category of the selected item
if (!$itemObj->accessGranted()) {
    redirect_header('<script>javascript:history.go(-1)</script>', 1, _NOPERM);
}

$helper->loadLanguage('main');

$dateformat    = $itemObj->getDatesub();
$sender_inform = sprintf(_MD_PUBLISHER_WHO_WHEN, $itemObj->posterName(), $itemObj->getDatesub());
$mainImage     = $itemObj->getMainImage();

$content = '';
if (empty($mainImage['image_path'])) {
    $content .= '<img src="' . PUBLISHER_URL . '/assets/images/default_image.jpg" alt="' . $myts->undoHtmlSpecialChars($mainImage['image_name']) . '"><br>';
}
if ('' != $mainImage['image_path']) {
    $content .= '<img src="' . $mainImage['image_path'] . '" alt="' . $myts->undoHtmlSpecialChars($mainImage['image_name']) . '"><br>';
}
$content .= '<a href="' . PUBLISHER_URL . '/item.php?itemid=' . $itemId . '" style="text-decoration: none; color: #000000; font-size: 120%;" title="' . $myts->undoHtmlSpecialChars($itemObj->getTitle()) . '">' . $myts->undoHtmlSpecialChars($itemObj->getTitle()) . '</a>';
$content .= '<br><span style="color: #CCCCCC; font-weight: bold; font-size: 80%;">'
            . _CO_PUBLISHER_CATEGORY
            . ' : </span><a href="'
            . PUBLISHER_URL
            . '/category.php?categoryid='
            . $itemObj->categoryid()
            . '" style="color: #CCCCCC; font-weight: bold; font-size: 80%;" title="'
            . $myts->undoHtmlSpecialChars($categoryObj->name())
            . '">'
            . $myts->undoHtmlSpecialChars($categoryObj->name())
            . '</a>';
$content .= '<br><span style="font-size: 80%; font-style: italic;">' . $sender_inform . '</span><br>';
$content .= $itemObj->getBody();
$content = str_replace('[pagebreak]', '', $content);

// Configuration for TCPDF_for_XOOPS
$pdf_data = [
    'author'           => $itemObj->posterName(),
    'title'            => $myts->undoHtmlSpecialChars($categoryObj->name()),
    'page_format'      => 'A4',
    'page_orientation' => 'P',
    'unit'             => 'mm',
    'rtl'              => false, //true if right to left
];

$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, _CHARSET, false);

$doc_title  = Utility::convertCharset($myts->undoHtmlSpecialChars($itemObj->getTitle()));
$docSubject = $myts->undoHtmlSpecialChars($categoryObj->name());

$docKeywords = $myts->undoHtmlSpecialChars($itemObj->meta_keywords());
if (array_key_exists('rtl', $pdf_data)) {
    $pdf->setRTL($pdf_data['rtl']);
}
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($doc_title);
$pdf->SetSubject($docSubject);
//$pdf->SetKeywords(XOOPS_URL . ', '.' by TCPDF_for_XOOPS (chg-web.org), '.$doc_title);
$pdf->SetKeywords($docKeywords);

$firstLine  = Utility::convertCharset($GLOBALS['xoopsConfig']['sitename']) . ' (' . XOOPS_URL . ')';
$secondLine = Utility::convertCharset($GLOBALS['xoopsConfig']['slogan']);

$PDF_HEADER_LOGO       = '_blank.png';
$PDF_HEADER_LOGO_WIDTH = '';

//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $firstLine, $secondLine);
$pdf->setHeaderData($PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $firstLine, $secondLine);
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//print : disable the possibility to print the PDF from any PDF viewer.
//modify : prevent the modification of contents of the document by operations other than those controlled by 'fill-forms', 'extract' and 'assemble';
//copy : prevent the copy or otherwise extract text and graphics from the document;
//annot-forms : Add or modify text annotations, fill in interactive form fields, and, if 'modify' is also set, create or modify interactive form fields (including signature fields);
//fill-forms : Fill in existing interactive form fields (including signature fields), even if 'annot-forms' is not specified;
//extract : Extract text and graphics (in support of accessibility to users with disabilities or for other purposes);
//assemble : Assemble the document (insert, rotate, or delete pages and create bookmarks or thumbnail images), even if 'modify' is not set;
//print-high : Print the document to a representation from which a faithful digital copy of the PDF content could be generated. When this is not set, printing is limited to a low-level representation of the appearance, possibly of degraded quality.
//owner : (inverted logic - only for public-key) when set permits change of encryption and enables all other permissions.

$pdf->SetProtection(['modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble']);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);
//set auto page breaks
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

//2.5.8
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

$pdf->setFooterData($tc = [0, 64, 0], $lc = [0, 64, 128]);

//initialize document
$pdf->Open();
$pdf->AddPage();
$pdf->writeHTML($content, true, 0, true, 0);
$pdf->Output();
