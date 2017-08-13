<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\TestInterface;
use Codeception\Exception\ConfigurationException;

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
     * Highlight text on site
     * 
     * @param string $text
     */
    private function highlightText($text)
    {
        $this->webDriverModule->executeJs('jQuery.fn.highlight=function(c){function e(b,c){var d=0;if(3==b.nodeType){var a=b.data.toUpperCase().indexOf(c),a=a-(b.data.substr(0,a).toUpperCase().length-b.data.substr(0,a).length);if(0<=a){d=document.createElement("span");d.className="highlight";a=b.splitText(a);a.splitText(c.length);var f=a.cloneNode(!0);d.appendChild(f);a.parentNode.replaceChild(d,a);d=1}}else if(1==b.nodeType&&b.childNodes&&!/(script|style)/i.test(b.tagName))for(a=0;a<b.childNodes.length;++a)a+=e(b.childNodes[a],c);return d} return this.length&&c&&c.length?this.each(function(){e(this,c.toUpperCase())}):this};jQuery.fn.removeHighlight=function(){return this.find("span.highlight").each(function(){this.parentNode.firstChild.nodeName;with(this.parentNode)replaceChild(this.firstChild,this),normalize()}).end()};');
        $this->webDriverModule->executeJs('$("body").highlight("'.$text.'");');
        $this->webDriverModule->executeJs(sprintf('$(".highlight").css(%s);', $this->cssStyle));
        $this->webDriverModule->wait($this->timeWait);
    }
    
    /**
     * Highlight element on site
     * 
     * @param string|array $selector
     */
    private function highlightElement($selector)
    {
        $cssSelector = $this->resolveCssSelector($selector);
        if($cssSelector) {
            $this->debug('[CSS Selector] '.$cssSelector);
            $this->webDriverModule->executeJs(sprintf('$("%s").css(%s);', $cssSelector, $this->cssStyle));
            $this->webDriverModule->wait($this->timeWait);
        }
    }
    
    /**
     * Resolve CSS selector
     * 
     * @param string|array $selector
     * @return boolean
     */
    private function resolveCssSelector($selector)
    {
        if(isset($selector['css'])) {
            return $selector['css'];
        }
        
        if(isset($selector['class'])) {
            return '.'.$selector['class'];
        }
        
        if(isset($selector['id'])) {
            return '#'.$selector['id'];
        }
        
        if(!empty($selector) && is_string($selector)) {
            return $selector;
        }
        
        return false;
    }
    
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

        if ($this->webDriver->executeScript('return !window.jQuery;')) {
            $jQueryString = file_get_contents(__DIR__ . "/jquery.js");
            $this->webDriver->executeScript($jQueryString);
            $this->webDriver->executeScript('jQuery.noConflict();');
        }
        
        $this->timeWait = floatval($this->config['timeWait']);
        $this->cssStyle = json_encode($this->config['cssStyle']);
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
    public function seeElement($selector, $attributes = null)
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
        $this->webDriverModule->wait($this->timeWait);
    }

    /**
     * @inheritdoc
     */
    public function clickWithLeftButton($cssOfXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->highlightElement($cssOfXPath);
        $this->webDriverModule->clickWithLeftButton($cssOfXPath, $offsetX, $offsetY);
        $this->webDriverModule->wait($this->timeWait);
    }

    /**
     * @inheritdoc
     */
    public function clickWithRightButton($cssOfXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->highlightElement($cssOfXPath);
        $this->webDriverModule->clickWithRightButton($cssOfXPath, $offsetX, $offsetY);
        $this->webDriverModule->wait($this->timeWait);
    }

}
