<?php

namespace Codeception\Module;

use Exception;
use Codeception\Module;
use Codeception\TestInterface;
use Codeception\Exception\ConfigurationException;
use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;

class HighlightCeption extends Module
{

    /**
     * Configuration array
     *
     * @var array
     */
    protected $config = [
        'cssClassName' => 'hyperia-hc',
        'cssStyle' => [
            'background-color' => 'yellow',
            'color' => 'black',
        ],
        'timeWait' => 1,
        'module' => 'WebDriver'
    ];

    /**
     * Name to use for css style we inject in page
     *
     * @var string
     */
    private $cssClassName = "";

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
        $this->cssClassName = $this->config['cssClassName'];
        $this->webDriver = $this->webDriverModule->webDriver;
        $this->timeWait = floatval($this->config['timeWait']);
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
        $this->webDriverModule->see($text, $selector);
        $this->highlightText($text);
    }

    /**
     * @inheritdoc
     */
    public function seeElement($selector, $attributes = [])
    {
        $this->webDriverModule->seeElement($selector, $attributes);
        $this->highlightElement($selector);
    }

    /**
     * @inheritdoc
     */
    public function seeLink($text, $url = null)
    {
        $this->webDriverModule->seeLink($text, $url);
        $this->highlightText($text);
    }

    /**
     * @inheritdoc
     */
    public function seeInField($field, $value)
    {
        $this->webDriverModule->seeInField($field, $value);
        $this->highlightElement($field);
    }

    /**
     * @inheritdoc
     */
    public function click($link, $context = null)
    {
        $this->webDriverModule->click($link, $context);
        $this->highlightElement($context);
    }

    /**
     * @inheritdoc
     */
    public function clickWithLeftButton($cssOfXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->webDriverModule->clickWithLeftButton($cssOfXPath, $offsetX, $offsetY);
        $this->highlightElement($cssOfXPath);
    }

    /**
     * @inheritdoc
     */
    public function clickWithRightButton($cssOfXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->webDriverModule->clickWithRightButton($cssOfXPath, $offsetX, $offsetY);
        $this->highlightElement($cssOfXPath);
    }

    /**
     * Highlight text on site
     *
     * @param string $text
     */
    private function highlightText($text)
    {
        try {
            $this->injectCssClassToPage();
            $this->debug('[Highlight Text] ' . $text);
            $el = $this->webDriver->findElement(WebDriverBy::xpath("//*[text()[contains(., '{$text}')]]"));
            $origHtml = $this->webDriver->executeScript("let origHtml = arguments[0].innerHTML; let hlghtHtml = arguments[0].innerHTML.replace(/({$text})/g, '<span class=\"{$this->cssClassName}\">$1</span>'); arguments[0].innerHTML = hlghtHtml; return origHtml;", [$el]);
            $this->webDriverModule->wait($this->timeWait);
            $this->webDriver->executeScript("arguments[0].innerHTML = arguments[1];", [$el, $origHtml]);
        } catch (Exception $e) {
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
            $this->injectCssClassToPage();
            $locator = $this->getSelector($selector);
            if ($locator) {
                if (Locator::isXPath($locator)) {
                    $el = $this->webDriver->findElement(WebDriverBy::xpath($locator));
                } else {
                    // assume css
                    $el = $this->webDriver->findElement(WebDriverBy::cssSelector($locator));
                }
                $className = $this->webDriver->executeScript("let className = arguments[0].className; arguments[0].className += ' {$this->cssClassName}'; return className;", [$el]);
                $this->webDriverModule->wait($this->timeWait);
                $this->webDriver->executeScript("arguments[0].className = arguments[1];", [$el, $className]);
            }
        } catch (Exception $e) {
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
     * Creates a css style class to inject into page
     *
     * @param array $cssStyleArray
     * @return string CSS Style Class as a string
     */
    private function makeCssClassFrom($cssStyleArray)
    {
        $cssClass = ".{$this->cssClassName} {";
        foreach ($cssStyleArray as $key => $value) {
            $cssClass .= "{$key}: {$value};";
        }
        $cssClass .= "}";
        return $cssClass;
    }

    /**
     * Injects a css style into the page to append to elements
     * that we want to highlight.
     */
    private function injectCssClassToPage()
    {
        $cssClassAsString = $this->makeCssClassFrom($this->config['cssStyle']);
        $js = "let css = \"{$cssClassAsString}\";" .
                "let elem = document.createElement('style');" .
                "elem.type = 'text/css';" .
                "if (elem.styleSheet) { elem.styleSheet.cssText = css; } else { elem.appendChild(document.createTextNode(css));}" .
                "document.getElementsByTagName('head')[0].appendChild(elem);";
        $this->webDriver->executeScript($js);
    }

}