# O2System Kernel
O2System Kernel is a set of PHP classes that is the core of O2System Framework. It is the first classes loaded on start-up. It handles the start-up process as well as input/output requests from client side either it is a browser requests or command line requests, translating them into router for the framework. It handles registries and services like modules, language, config, and etc. The kernel design pattern is based on Hybrid (or modular) kernels and the kernel bootstrap class it is based on Singleton Design Pattern.

Installation
------------
The best way to install [O2System Kernel](https://packagist.org/packages/o2system/kernel) is to use [Composer](http://getcomposer.org)
```
composer require o2system/kernel
```

Manual Installation
------------
1. Download the [master zip file](https://github.com/o2system/kernel/archive/master.zip).
2. Extract into your project folder.
3. Require the autoload.php file.<br>
```php
require your_project_folder_path/kernel/src/autoload.php
```

Usage Example
-------------
```php
namespace O2System;

class Framework extends Kernel {
  protected function __construct()
    {
        parent::__construct();
        // Do something here.
    }
}

Framework::getInstance();
```

Documentation is available on this repository [wiki](https://github.com/o2system/kernel/wiki) or visit this repository [github page](https://o2system.github.io/kernel).

Ideas and Suggestions
---------------------
Please kindly mail us at [o2system.framework@gmail.com](mailto:o2system.framework@gmail.com).

Bugs and Issues
---------------
Please kindly submit your [issues at Github](http://github.com/o2system/kernel/issues) so we can track all the issues along development and send a [pull request](http://github.com/o2system/kernel/pulls) to this repository.

System Requirements
-------------------
- PHP 5.6+
- [Composer](http://getcomposer.org)

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim](http://steevenz.com)
* Github Pages Designer and Writer: [Teguh Rianto](http://teguhrianto.tk)
