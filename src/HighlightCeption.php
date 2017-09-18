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
     * @var string
     */
    private $cssStyle = "";

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
        $this->cssStyle = $this->getInlineStyleStringFrom($this->config['cssStyle']);
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
            $this->debug('[Highlight Text] ' . $text);
            $el = $this->webDriver->findElement(WebDriverBy::xpath("//*[text()[contains(., '{$text}')]]"));
            $this->webDriver->executeScript("let str = arguments[0].innerHTML.replace(/({$text})/g, '<span style=\"{$this->cssStyle}\">$1</span>'); arguments[0].innerHTML = str;", [$el]);
            // $this->webDriver->executeScript("arguments[0].setAttribute('style', 'background-color: yellow; color: black;')", [$el]);
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
                if (Locator::isXPath($locator)) {
                  $el = $this->webDriver->findElement(WebDriverBy::xpath($locator));
                } else {
                  // assume css
                  $el = $this->webDriver->findElement(WebDriverBy::cssSelector($locator));
              }
              $this->webDriver->executeScript("arguments[0].setAttribute('style', 'background-color: yellow; color: black;')", [$el]);
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
    * Converts a css style array to inline css style string
    *
    * @param array $cssStyleArray
    * @return string Inline CSS style string
    */
    private function getInlineStyleStringFrom($cssStyleArray) {
      $inlineCss = "";
      foreach ($cssStyleArray as $key=>$value) {
        $inlineCss .= "{$key}: {$value};";
      }
      return $inlineCss;
    }
}
