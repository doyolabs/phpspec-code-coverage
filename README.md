PhpSpec Code Coverage
---
Provide code coverage extension during phpspec run

[![License][phpspec-license-badge]][phpspec-license]
[![Version][phpspec-version-badge]][phpspec-version]
[![Build Status][phpspec-travis-badge]][phpspec-travis]
[![Coverage][phpspec-cover-badge]][phpspec-cover]
[![Score][phpspec-score-badge]][phpspec-score]

[phpspec]:                   https://github.com/doyolabs/phpspec-code-coverage
[phpspec-license]:           https://github.com/doyolabs/phpspec-code-coverage/blob/master/LICENSE
[phpspec-license-badge]:     https://img.shields.io/packagist/l/doyo/phpspec-code-coverage.svg?style=flat-square
[phpspec-version]:           https://packagist.org/packages/doyo/phpspec-code-coverage
[phpspec-version-badge]:     https://img.shields.io/packagist/vpre/doyo/phpspec-code-coverage.svg?style=flat-square
[phpspec-travis]:            https://travis-ci.com/doyolabs/phpspec-code-coverage
[phpspec-travis-badge]:      https://img.shields.io/travis/com/doyolabs/phpspec-code-coverage/master.svg?style=flat-square
[phpspec-cover]:             https://coveralls.io/github/doyolabs/phpspec-code-coverage?branch=master
[phpspec-cover-badge]:       https://img.shields.io/coveralls/github/doyolabs/phpspec-code-coverage/master.svg?style=flat-square
[phpspec-score]:             https://scrutinizer-ci.com/g/doyolabs/phpspec-code-coverage/?branch=master
[phpspec-score-badge]:       https://img.shields.io/scrutinizer/quality/g/doyolabs/phpspec-code-coverage/master.svg?style=flat-square


### Support
*  PHP: >=7.0
*  Behat: >=3.0
*  PHP Code Coverage: >=5.3

Install
----
```shell
$ composer require doyo/phpspec-code-coverage
```
After installing this extension, you can collect code coverage by using this command:
```shell
$ ./vendor/bin/phpspec run --coverage
```
The reports will be generated in target directory as you defined in configuration.

Configuration
----
```yaml
# phpspec.yaml.dist
extensions:
    Doyo\PhpSpec\CodeCoverage\Extension:
        filters:
            whitelist:
                - src
            blacklist:
                - path/to/blacklist/dir
        reports:
            php: build/cov/phpspec.cov
            html: build/phpspec
```
