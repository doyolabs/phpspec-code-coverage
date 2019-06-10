Behat Coverage Extension [![License](https://img.shields.io/packagist/l/doyo/phpspec-code-coverage.svg?style=flat-square)](https://github.com/doyolabs/phpspec-code-coverage/blob/master/LICENSE)
---
Provide code coverage extension during behat tests

### Status
| Branch  | Status | Coverage | Score | 
| :---: | :---: | :---: | :---: |
| **master**  | [![Build Status][travis-master]][travis] | [![Coverage][cover-stat-master]][cover-master] | [![Score][score-stat-master]][score-master]
| **develop** | [![Build Status][travis-develop]][travis] | [![Coverage][cover-stat-develop]][cover-develop] | [![Score][score-stat-develop]][score-develop] 

### Support
*  PHP: >=7.0
*  Behat: >=3.0
*  PHP Code Coverage: >=5.3

[travis]:                   https://travis-ci.com/doyolabs/phpspec-code-coverage
[travis-master]:            https://img.shields.io/travis/com/doyolabs/phpspec-code-coverage/master.svg?style=flat-square
[travis-develop]:           https://img.shields.io/travis/com/doyolabs/phpspec-code-coverage/develop.svg?style=flat-square
[cover-master]:             https://coveralls.io/github/doyolabs/phpspec-code-coverage?branch=master
[cover-develop]:            https://coveralls.io/github/doyolabs/phpspec-code-coverage?branch=develop
[cover-stat-develop]:       https://img.shields.io/coveralls/github/doyolabs/phpspec-code-coverage/develop.svg?style=flat-square
[cover-stat-master]:        https://img.shields.io/coveralls/github/doyolabs/phpspec-code-coverage/master.svg?style=flat-square
[score-master]:             https://scrutinizer-ci.com/g/doyolabs/phpspec-code-coverage/?branch=master
[score-develop]:            https://scrutinizer-ci.com/g/doyolabs/phpspec-code-coverage/?branch=develop
[score-stat-develop]:       https://img.shields.io/scrutinizer/quality/g/doyolabs/phpspec-code-coverage/develop.svg?style=flat-square
[score-stat-master]:        https://img.shields.io/scrutinizer/quality/g/doyolabs/phpspec-code-coverage/master.svg?style=flat-square

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