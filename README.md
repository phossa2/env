# phossa2/env
[![Build Status](https://travis-ci.org/phossa2/env.svg?branch=master)](https://travis-ci.org/phossa2/env)
[![Code Quality](https://scrutinizer-ci.com/g/phossa2/env/badges/quality-score.png?b=master)](https://travis-ci.org/phossa2/env)
[![HHVM](https://img.shields.io/hhvm/phossa2/env.svg?style=flat)](http://hhvm.h4cc.de/package/phossa2/env)
[![Latest Stable Version](https://img.shields.io/packagist/vpre/phossa2/env.svg?style=flat)](https://packagist.org/packages/phossa2/env)
[![License](https://poser.pugx.org/phossa2/env/license)](http://mit-license.org/)

**phossa2/env** is a library to load environments from a shell style file.

It requires PHP 5.4, supports PHP 7.0+ and HHVM. It is compliant with
[PSR-1][PSR-1], [PSR-2][PSR-2], [PSR-4][PSR-4].

[PSR-1]: http://www.php-fig.org/psr/psr-1/ "PSR-1: Basic Coding Standard"
[PSR-2]: http://www.php-fig.org/psr/psr-2/ "PSR-2: Coding Style Guide"
[PSR-4]: http://www.php-fig.org/psr/psr-4/ "PSR-4: Autoloader"

Installation
---
Install via the `composer` utility.

```
composer require "phossa2/env=2.*"
```

or add the following lines to your `composer.json`

```json
{
    "require": {
       "phossa2/env": "^2.0.0"
    }
}
```

Features
---

- Support PHP 5.4+, PHP 7.0+, HHVM

- PHP7 ready for return type declarations and argument type declarations.

- PSR-1, PSR-2, PSR-4 compliant.

Dependencies
---

- PHP >= 5.4.0

- phossa2/shared >= 2.0.1

License
---

[MIT License](http://mit-license.org/)
