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
 *  Publisher class
 *
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          John Neill <catzwolf@xoosla.com>
 */
// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once __DIR__ . '/../include/common.php';

/**
 * XoopsThemeTabForm
 *
 * @package
 * @author    John
 * @copyright Copyright (c) 2009
 * @access    public
 */
class ThemeTabForm extends \XoopsForm
{
    public $formTabs = [];

    /**
     * "action" attribute for the html form
     *
     * @var string
     */
    public $action;

    /**
     * "method" attribute for the form.
     *
     * @var string
     */
    public $method;

    /**
     * "name" attribute of the form
     *
     * @var string
     */
    public $name;

    /**
     * title for the form
     *
     * @var string
     */
    public $title;

    /**
     * summary for the form (WGAC2 Requirement)
     *
     * @var string
     */
    public $summary = '';

    /**
     * array of {@link XoopsFormElement} objects
     *
     * @var array
     */
    public $elements = [];

    /**
     * extra information for the <form> tag
     *
     * @var array
     */
    public $extra = [];

    /**
     * required elements
     *
     * @var array
     */
    public $required = [];

    /**
     * @param string $title
     * @param string $name
     * @param string $action
     * @param string $method
     * @param bool   $addtoken
     * @param string $summary
     */
    public function __construct($title, $name, $action, $method = 'post', $addtoken = false, $summary = '')
    {
        //        global $xoTheme;
        //        $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/ui.core.js');
        //        $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/ui.tabs.js');
        //        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/jquery-ui-1.7.1.custom.css');

        $GLOBALS['xoTheme']->addScript('browse.php?Frameworks/jquery/plugins/jquery.ui.js');
        $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . '/modules/system/css/ui/' . xoops_getModuleOption('jquery_theme', 'system') . '/ui.all.css');

        $this->title   = $title;
        $this->name    = $name;
        $this->action  = $action;
        $this->method  = $method;
        $this->summary = $summary;
        if (false !== $addtoken) {
            $this->addElement(new \XoopsFormHiddenToken());
        }
    }

    //function render() {}

    /**
     * @param XoopsTpl $tpl
     */
    public function assign(\XoopsTpl $tpl)
    {
        $i        = -1;
        $tab      = -1;
        $elements = [];
        if (count($this->getRequired()) > 0) {
            $this->elements[] = "<tr class='foot'><td colspan='2'>* = " . _REQUIRED . '</td></tr>';
        }
        foreach ($this->getElements() as $ele) {
            ++$i;
            if (is_string($ele) && 'addTab' === $ele) {
                ++$tab;
                continue;
            }
            if (is_string($ele) && 'endTabs' === $ele) {
                $tab = -1;
                continue;
            }
            if (is_string($ele)) {
                $elements[$i]['body'] = $ele;
                $elements[$i]['tab']  = $tab;
                continue;
            }
            $eleName                  = $ele->getName();
            $eleDescription           = $ele->getDescription();
            $n                        = $eleName ?: $i;
            $elements[$n]['name']     = $eleName;
            $elements[$n]['caption']  = $ele->getCaption();
            $elements[$n]['body']     = $ele->render();
            $elements[$n]['hidden']   = $ele->isHidden() ? true : false;
            $elements[$n]['required'] = $ele->isRequired();
            if ('' != $eleDescription) {
                $elements[$n]['description'] = $eleDescription;
            }
            $elements[$n]['tab'] = $tab;
        }
        $js = $this->renderValidationJS();
        $tpl->assign($this->getName(), [
            'title'      => $this->getTitle(),
            'id'         => 'tab_' . preg_replace('/[^a-z0-9]+/i', '', $this->getTitle()),
            'name'       => $this->getName(),
            'action'     => $this->getAction(),
            'method'     => $this->getMethod(),
            'extra'      => 'onsubmit="return xoopsFormValidate_' . $this->getName() . '();"' . $this->getExtra(),
            'javascript' => $js,
            'tabs'       => $this->formTabs,
            'elements'   => $elements
        ]);
    }

    /**
     * XoopsThemeTabForm::startTab()
     *
     * @param mixed $tabText
     */
    public function startTab($tabText)
    {
        $temp = $this->startFormTabs($tabText);
        $this->addElement($temp);
    }

    /**
     * XoopsThemeTabForm::endTab()
     */
    public function endTabs()
    {
        $temp = $this->endFormTabs();
        $this->addElement($temp);
    }

    /**
     * Creates a tab with title text and starts that tabs page
     *
     * @param $tabText - This is what is displayed on the tab
     *
     * @return string
     */
    public function startFormTabs($tabText)
    {
        $this->formTabs[] = $tabText;
        $ret              = 'addTab';

        return $ret;
    }

    /**
     * Ends a tab page
     *
     * @return string
     */
    public function endFormTabs()
    {
        $ret = 'endTabs';

        return $ret;
    }

    /**
     * @param bool $encode
     *
     * @return string
     */
    public function getSummary($encode = false)
    {
        return $encode ? htmlspecialchars($this->summary, ENT_QUOTES) : $this->summary;
    }

    /**
     * return the title of the form
     *
     * @param bool $encode To sanitizer the text?
     *
     * @return string
     */
    public function getTitle($encode = false)
    {
        return $encode ? htmlspecialchars($this->title, ENT_QUOTES) : $this->title;
    }

    /**
     * get the "name" attribute for the <form> tag
     * Deprecated, to be refactored
     *
     * @param bool $encode To sanitizer the text?
     *
     * @return string
     */
    public function getName($encode = true)
    {
        return $encode ? htmlspecialchars($this->name, ENT_QUOTES) : $this->name;
    }

    /**
     * get the "action" attribute for the <form> tag
     *
     * @param bool $encode To sanitizer the text?
     *
     * @return string
     */
    public function getAction($encode = true)
    {
        // Convert &amp; to & for backward compatibility
        return $encode ? htmlspecialchars(str_replace('&amp;', '&', $this->action), ENT_QUOTES) : $this->action;
    }

    /**
     * get the "method" attribute for the <form> tag
     *
     * @return string
     */
    public function getMethod()
    {
        return ('get' === strtolower($this->method)) ? 'get' : 'post';
    }

    /**
     * Add an element to the form
     *
     * @param string|\XoopsFormElement $formElement reference to a {@link XoopsFormElement}
     * @param bool                    $required    is this a "required" element?
     */
    public function addElement($formElement, $required = false)
    {
        if (is_string($formElement)) {
            $this->elements[] =& $formElement;
        } elseif (is_subclass_of($formElement, 'xoopsformelement')) {
            $this->elements[] =& $formElement;
            if ($required) {
                if (method_exists($formElement, 'setRequired')) {
                    $formElement->setRequired(true);
                } else {
                    $formElement->required = true;
                }
                $this->required[] =& $formElement;
            }
        }
    }

    /**
     * get an array of forms elements
     *
     * @param bool $recurse get elements recursively?
     *
     * @return array array of {@link XoopsFormElement}s
     */
    public function &getElements($recurse = false)
    {
        if (!$recurse) {
            return $this->elements;
        } else {
            $ret   = [];
            $count = count($this->elements);
            for ($i = 0; $i < $count; ++$i) {
                if (is_object($this->elements[$i])) {
                    $ret[] =& $this->elements[$i];
                }
            }

            return $ret;
        }
    }

    /**
     * get an array of "name" attributes of form elements
     *
     * @return array array of form element names
     */
    public function getElementNames()
    {
        $ret      = [];
        $elements = &$this->getElements(true);
        $count    = count($elements);
        for ($i = 0; $i < $count; ++$i) {
            $ret[] = $elements[$i]->getName();
        }

        return $ret;
    }

    /**
     * get a reference to a {@link XoopsFormElement} object by its "name"
     *
     * @param string $name "name" attribute assigned to a {@link XoopsFormElement}
     *
     * @return bool|\XoopsFormElement reference to a {@link XoopsFormElement}, false if not found
     */
    public function &getElementByName($name)
    {
        $elements =& $this->getElements(true);
        $count    = count($elements);
        for ($i = 0; $i < $count; ++$i) {
            if ($name == $elements[$i]->getName(false)) {
                return $elements[$i];
            }
        }
        $elt = null;

        return $elt;
    }

    /**
     * Sets the "value" attribute of a form element
     *
     * @param string $name  the "name" attribute of a form element
     * @param string $value the "value" attribute of a form element
     */
    public function setElementValue($name, $value)
    {
        $ele =& $this->getElementByName($name);
        if (is_object($ele) && method_exists($ele, 'setValue')) {
            $ele->setValue($value);
        }
    }

    /**
     * Sets the "value" attribute of form elements in a batch
     *
     * @param array $values array of name/value pairs to be assigned to form elements
     */
    public function setElementValues($values)
    {
        if (is_array($values) && !empty($values)) {
            // will not use getElementByName() for performance..
            $elements =& $this->getElements(true);
            $count    = count($elements);
            for ($i = 0; $i < $count; ++$i) {
                $name = $elements[$i]->getName(false);
                if ($name && isset($values[$name]) && method_exists($elements[$i], 'setValue')) {
                    $elements[$i]->setValue($values[$name]);
                }
            }
        }
    }

    /**
     * Gets the "value" attribute of a form element
     *
     * @param string $name   the "name" attribute of a form element
     * @param bool   $encode To sanitizer the text?
     *
     * @return string the "value" attribute assigned to a form element, null if not set
     */
    public function getElementValue($name, $encode = false)
    {
        $ele =& $this->getElementByName($name);
        if (is_object($ele) && method_exists($ele, 'getValue')) {
            return $ele->getValue($encode);
        }

        return null;
    }

    /**
     * gets the "value" attribute of all form elements
     *
     * @param bool $encode To sanitizer the text?
     *
     * @return array array of name/value pairs assigned to form elements
     */
    public function getElementValues($encode = false)
    {
        // will not use getElementByName() for performance..
        $elements =& $this->getElements(true);
        $count    = count($elements);
        $values   = [];
        for ($i = 0; $i < $count; ++$i) {
            $name = $elements[$i]->getName(false);
            if ($name && method_exists($elements[$i], 'getValue')) {
                $values[$name] = $elements[$i]->getValue($encode);
            }
        }

        return $values;
    }

    /**
     * set the extra attributes for the <form> tag
     *
     * @param string $extra extra attributes for the <form> tag
     * @return string|void
     */
    public function setExtra($extra)
    {
        if (!empty($extra)) {
            $this->extra[] = $extra;
        }
    }

    /**
     * set the summary tag for the <form> tag
     *
     * @param string $summary
     */
    public function setSummary($summary)
    {
        if (!empty($summary)) {
            $this->summary = strip_tags($summary);
        }
    }

    /**
     * get the extra attributes for the <form> tag
     *
     * @return string
     */
    public function &getExtra()
    {
        $extra = empty($this->extra) ? '' : ' ' . implode(' ', $this->extra);

        return $extra;
    }

    /**
     * make an element "required"
     *
     * @param XoopsFormElement $formElement reference to a {@link XoopsFormElement}
     */
    public function setRequired(\XoopsFormElement $formElement)
    {
        $this->required[] =& $formElement;
    }

    /**
     * get an array of "required" form elements
     *
     * @return array array of {@link XoopsFormElement}s
     */
    public function &getRequired()
    {
        return $this->required;
    }

    /**
     * insert a break in the form
     * This method is abstract. It must be overwritten in the child classes.
     *
     * @param string $extra extra information for the break
     *
     * @abstract
     */
    public function insertBreak($extra = null)
    {
    }

    /**
     * returns renderered form
     * This method is abstract. It must be overwritten in the child classes.
     *
     * @abstract
     */
    public function render()
    {
        return '';
    }

    /**
     * displays rendered form
     */
    public function display()
    {
        echo $this->render();
    }

    /**
     * Renders the Javascript function needed for client-side for validation
     * Form elements that have been declared "required" and not set will prevent the form from being
     * submitted. Additionally, each element class may provide its own "renderValidationJS" method
     * that is supposed to return custom validation code for the element.
     * The element validation code can assume that the JS "myform" variable points to the form, and must
     * execute <i>return false</i> if validation fails.
     * A basic element validation method may contain something like this:
     * <code>
     * function renderValidationJS() {
     *            $name = $this->getName();
     *            return "if (myform.{$name}.value != 'valid') { " .
     *              "myform.{$name}.focus(); window.alert( '$name is invalid' ); return false;" .
     *              " }";
     * }
     * </code>
     *
     * @param boolean $withtags Include the < javascript > tags in the returned string
     *
     * @return string
     */
    public function renderValidationJS($withtags = true)
    {
        $js = '';
        if ($withtags) {
            $js .= "\n<!-- Start Form Validation JavaScript //-->\n<script type='text/javascript'>\n<!--//\n";
        }
        $formname = $this->getName();
        $js       .= "function xoopsFormValidate_{$formname}() { var myform = window.document.{$formname}; ";
        $elements =& $this->getElements(true);
        foreach ($elements as $elt) {
            if (method_exists($elt, 'renderValidationJS')) {
                $js .= $elt->renderValidationJS();
            }
        }
        $js .= "return true;\n}\n";
        if ($withtags) {
            $js .= "//--></script>\n";
        }

        return $js;
    }
}
