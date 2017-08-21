<?php

namespace Codeception\Module;

use Exception;
use Codeception\Module;
use Codeception\TestInterface;
use Codeception\Exception\ConfigurationException;
use Codeception\Util\Locator;

class HighlightCeption extends Module
{

    /**
     * Configuration array
     * 
     * @var array
     */
    protected $config = [
        'cssStyle' => [
            'background-color' => 'yellow',
            'color' => 'black',
        ],
        'timeWait' => 1,
        'module' => 'WebDriver'
    ];

    /**
     * Css style for hightlight text or element
     * 
     * @var array
     */
    private $cssStyle = [];

    /**
     * Time wait
     *
     * @var float|integer
     */
    private $timeWait = 0;

    /**
     * @var RemoteWebDriver
     */
    private $webDriver = null;

    /**
     * @var WebDriver
     */
    private $webDriverModule = null;

    /**
     * Event hook before a test starts
     *
     * @param TestInterface $test
     * @throws ConfigurationException
     */
    public function _before(TestInterface $test)
    {
        if (!$this->hasModule($this->config['module'])) {
            throw new ConfigurationException("HighlightCeption uses the WebDriver. Please ensure that this module is activated.");
        }

        $this->webDriverModule = $this->getModule($this->config['module']);
        $this->webDriver = $this->webDriverModule->webDriver;
        $this->timeWait = floatval($this->config['timeWait']);
        $this->cssStyle = json_encode($this->config['cssStyle']);
        $this->test = $test;
    }

    /**
     * Event hook after a test starts
     *
     * @param TestInterface $test
     * @throws ConfigurationException
     */
    public function _after(TestInterface $test)
    {
        $this->webDriverModule->wait($this->timeWait);
        $this->test = $test;
    }

    /**
     * @inheritdoc
     */
    public function see($text, $selector = null)
    {
        $this->highlightText($text);
        $this->webDriverModule->see($text, $selector);
    }

    /**
     * @inheritdoc
     */
    public function seeElement($selector, $attributes = [])
    {
        $this->highlightElement($selector);
        $this->webDriverModule->seeElement($selector, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function seeLink($text, $url = null)
    {
        $this->highlightText($text);
        $this->webDriverModule->seeLink($text, $url);
    }

    /**
     * @inheritdoc
     */
    public function seeInField($field, $value)
    {
        $this->highlightElement($field);
        $this->webDriverModule->seeInField($field, $value);
    }

    /**
     * @inheritdoc
     */
    public function click($link, $context = null)
    {
        $this->highlightElement($context);
        $this->webDriverModule->click($link, $context);
    }

    /**
     * @inheritdoc
     */
    public function clickWithLeftButton($cssOfXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->highlightElement($cssOfXPath);
        $this->webDriverModule->clickWithLeftButton($cssOfXPath, $offsetX, $offsetY);
    }

    /**
     * @inheritdoc
     */
    public function clickWithRightButton($cssOfXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->highlightElement($cssOfXPath);
        $this->webDriverModule->clickWithRightButton($cssOfXPath, $offsetX, $offsetY);
    }

    /**
     * Highlight text on site
     * 
     * @param string $text
     */
    private function highlightText($text)
    {
        try {
            $this->loadJQuery();
            $this->debug('[Highlight Text] ' . $text);
            $this->webDriverModule->executeJs('jQuery(document).ready(function (){
                jQuery("body").highlight("' . $text . '");
                ' . sprintf('jQuery(".highlight").css(%s);', $this->cssStyle) . '
            });');
        } catch(Exception $e) {
            $this->debug(sprintf("[Highlight Exception] %s \n%s", $e->getMessage(), $e->getTraceAsString()));
        }
    }

    /**
     * Highlight element on site
     * 
     * @param string|array $selector
     */
    private function highlightElement($selector)
    {
        try {
            $locator = $this->getSelector($selector);
            if ($locator) {
                $this->loadJQuery();
                if (Locator::isXPath($locator)) {
                    $this->loadJQueryXPath();
                    $this->debug('[Highlight XPath] ' . Locator::humanReadableString($locator));
                    $this->webDriverModule->executeJs(sprintf('jQuery(document).xpath("%s").css(%s);', addslashes($locator), $this->cssStyle));
                } else {
                    $this->debug('[Highlight Selector] ' . Locator::humanReadableString($locator));
                    $this->webDriverModule->executeJs(sprintf('jQuery("%s").css(%s);', addslashes($locator), $this->cssStyle));
                }
            }
        } catch(Exception $e) {
            $this->debug(sprintf("[Highlight Exception] %s \n%s", $e->getMessage(), $e->getTraceAsString()));
        }
    }

    /**
     * Resolve selector
     * 
     * @param string|array $selector
     * @return boolean
     * @todo 
     */
    private function getSelector($selector)
    {
        if (isset($selector['css'])) {
            return $selector['css'];
        }

        if (isset($selector['class'])) {
            return '.' . $selector['class'];
        }

        if (isset($selector['id'])) {
            return '#' . $selector['id'];
        }

        if (isset($selector['xpath'])) {
            return $selector['xpath'];
        }

        if (!empty($selector) && is_string($selector)) {
            return $selector;
        }

        return false;
    }

    /**
     * Load jQuery 
     */
    private function loadJQuery()
    {
        if ($this->webDriver->executeScript('return !window.jQuery;')) {
            $jQueryString = file_get_contents(__DIR__ . "/jquery.min.js");
            $this->webDriver->executeScript($jQueryString);
            $this->webDriver->executeScript('jQuery.noConflict();');
            $this->loadJQueryHighlight();
        }
    }

    /**
     * Load jQuery.XPath
     */
    private function loadJQueryXPath()
    {
        if ($this->webDriver->executeScript('return !window.jQuery.fn.xpath;')) {
            $jQueryXPath = file_get_contents(__DIR__ . "/jquery.xpath.min.js");
            $this->webDriver->executeScript($jQueryXPath);
        }
    }

    /**
     * Load jQuery.Highlight
     */
    private function loadJQueryHighlight()
    {
        if ($this->webDriver->executeScript('return !window.jQuery.fn.highlight;')) {
            $jQueryString = file_get_contents(__DIR__ . "/jquery.highlight.min.js");
            $this->webDriver->executeScript($jQueryString);
        }
    }

}