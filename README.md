# Highlight text or element - codecept module

[![Build Status](https://travis-ci.org/hyperia-sk/highlight-ception.svg?branch=master)](https://travis-ci.org/hyperia-sk/highlight-ception)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/hyperia-sk/highlight-ception/master/LICENSE) 

![screenshot from 2017-08-13 15-20-53](https://user-images.githubusercontent.com/6382002/29250010-0bdb3cf6-803b-11e7-92af-f666caf497e4.png)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```shell
composer require hyperia/highlight-ception:"^1.0"
```

or add

```
"hyperia/highlight-ception": "^1.0"
```

to the require section of your composer.json.

## Configuration (usage)

enable `HighlightCeption` module in `acceptance.suite.yml` config file:

```yaml
modules:
    enabled:
        - HighlightCeption:
            module: WebDriver
            timeWait: 2
            cssStyle:
                "background-color": "yellow"
                "color": "black"
```

### Parameter description

- `module` - codeception driver WebDriver
- `timeWait` - wait seconds between `see`, `seeLink`, `seeElements`, `seeInField`, `click`, `clickWithLeftButton` and `clickWithRightButton`
- `cssStyle` - custom highlight css style


