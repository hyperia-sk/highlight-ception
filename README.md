# Highlight text or element - codecept module

[![Build Status](https://travis-ci.org/hyperia-sk/highlight-ception.svg?branch=master)](https://travis-ci.org/hyperia-sk/highlight-ception)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/hyperia-sk/highlight-ception/master/LICENSE) 
[![Latest Stable Version](https://poser.pugx.org/hyperia/highlight-ception/v/stable)](https://packagist.org/packages/hyperia/highlight-ception)

This module can be used to display the current representation of **css element**, **text** or **xpath** on a website with an expected. It was written on the shoulders of codeception and integrates in a very easy way.

![screenshot from 2017-08-13 15-20-53](https://user-images.githubusercontent.com/6382002/29250010-0bdb3cf6-803b-11e7-92af-f666caf497e4.png)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```shell
composer require hyperia/highlight-ception:"^1.1"
```

or add

```
"hyperia/highlight-ception": "^1.1"
```

to the require section of your composer.json.

## Configuration (usage)

enable `HighlightCeption` module in `acceptance.suite.yml` config file:

```yaml
modules:
    enabled:
        - WebDriver:
            url: http://hyperia.sk
            browser: chrome
        - HighlightCeption:
            module: WebDriver
            timeWait: 2
            cssStyle:
                background-color: yellow
                color: black
```

### Parameter description

- **module** - module responsible for browser interaction, default: WebDriver.
- **timeWait** - wait seconds between `see`, `seeLink`, `seeElements`, `seeInField`, `click`, `clickWithLeftButton` and `clickWithRightButton` functions
- **cssStyle** - your custom css style for highlight element or text on a site

## Requirements

HighlightCeption needs the following components to run:

- Codeception HighlightCeption is a module for **Codeception**. It will need a running version of this tool.
- **WebDriver module** This tool only works with the webdriver module in Codeception at the moment.

## Tests

```bash
./vendor/bin/codecept run -- -c test/integration
```

