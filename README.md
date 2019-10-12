[![O2System Framework](http://o2system.id/assets/img/logo/logo-200px.png?logo)](http://o2system.id)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/o2system/kernel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/o2system/kernel/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/o2system/kernel/badges/build.png?b=master)](https://scrutinizer-ci.com/g/o2system/kernel/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/o2system/kernel/v/stable)](https://packagist.org/packages/o2system/kernel)
[![Total Downloads](https://poser.pugx.org/o2system/kernel/downloads)](https://packagist.org/packages/o2system/kernel)
[![License](https://poser.pugx.org/o2system/kernel/license)](https://packagist.org/packages/o2system/kernel)

# O2System Kernel
O2System Kernel is a set of PHP classes that is the core of O2System Framework. It contains classes that are loaded at startup. It handles the start-up process as well as input/output requests from the client side whether they are browser requests or command line requests, translating them into router for the framework. It handles registries and services like modules, language, config, and etc. The kernel design pattern is based on Hybrid (or modular) kernels and the kernel bootstrap class it is based on Singleton Design Pattern and has a dependency on 3 major set of PHP classes: O2System\Spl (O2System Standard PHP Library), O2System\Psr (O2System PHP Standard Recommendations) and O2System\Gear (O2System PHP Debugger).

### Features
- HTTP Request Input-Output Handler
- Cli Request Input-Output Handler
- Language Service
- Logger Service
- Shutdown Service

### Composer Installation
The best way to install O2System Kernel is to use [Composer](https://getcomposer.org)
```
composer require o2system/kernel
```
> Packagist: [https://packagist.org/packages/o2system/kernel](https://packagist.org/packages/o2system/kernel)

### Usage
Documentation is available on this repository [wiki](https://github.com/o2system/kernel/wiki) or visit this repository [github page](https://o2system.github.io/kernel).

### Ideas and Suggestions
Please kindly mail us at [support@o2system.id](mailto:support@o2system.id])

### Bugs and Issues
Please kindly submit your [issues at Github](http://github.com/o2system/kernel/issues) so we can track all the issues along development and send a [pull request](http://github.com/o2system/kernel/pulls) to this repository.

### System Requirements
- PHP 7.2+
- [Composer](https://getcomposer.org)
- [O2System Gear](https://github.com/o2system/gear)
- [O2System Psr](https://github.com/o2system/psr)
- [O2System Spl](https://github.com/o2system/spl)
