# phossa2/env [ABANDONED]

**PLEASE USE [phoole/env](https://github.com/phoole/env) library instead**

[![Build Status](https://travis-ci.org/phossa2/env.svg?branch=master)](https://travis-ci.org/phossa2/env)
[![Code Quality](https://scrutinizer-ci.com/g/phossa2/env/badges/quality-score.png?b=master)](https://travis-ci.org/phossa2/env)
[![Code Climate](https://codeclimate.com/github/phossa2/env/badges/gpa.svg)](https://codeclimate.com/github/phossa2/env)
[![PHP 7 ready](http://php7ready.timesplinter.ch/phossa2/env/master/badge.svg)](https://travis-ci.org/phossa2/env)
[![HHVM](https://img.shields.io/hhvm/phossa2/env.svg?style=flat)](http://hhvm.h4cc.de/package/phossa2/env)
[![Latest Stable Version](https://img.shields.io/packagist/vpre/phossa2/env.svg?style=flat)](https://packagist.org/packages/phossa2/env)
[![License](https://img.shields.io/:license-mit-blue.svg)](http://mit-license.org/)

**phossa2/env** is a library to load environment variables from fully bash shell
compatible files.

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
       "phossa2/env": "2.*"
    }
}
```

Usage
---

- Put your environments in file `.env`,

  ```shell
  # this is comment line
  BASE_DIR='/home/web'

  # reference here
  APP_DIR=${BASE_DIR}/app   # another comment here
  ```

- Load and use your env variables in PHP script

  ```php
  <?php
  // ...
  $env = new Phossa2\Env\Environment();

  # load env
  $env->load(__DIR__ . '/.env');

  // use your env
  echo getenv('APP_DIR');
  ```

Features
---

- Compatible with bash if not using extended features like
  [relaxed syntax](#relax), [get current dir and file](#current) or
  [PHP globals](#php).

- Support shell default values, `${param:-new}` or `${param:=new}`

- Able to `source another_env_file` in the env file

- By default, **WILL** overwrite any existing environment variables. This is
  the default behavior in bash.

  To disable overwrite and honor existing env variables,

  ```php
  env->load('./.env', $overload = false);
  ```

- <a name="relax"></a>Relaxed syntax (not compatible with bash) in env file

  ```php
  # spaces before and after '=' is allowed. NOT recommended though
  ROOT_DIR = /var/tmp
  ```

- <a name="current"></a>Get current *path*, *dir*, *filename* with
  `${BASH_SOURCE}`, `${BASH_SOURCE%/*}`, `${BASH_SOURCE##*/}`

  ```php
  # set current file
  MY_FILE=${BASH_SOURCE##*/}

  # set root dir to current dir
  ROOT_DIR=${BASH_SOURCE%/*}
  ```

  or with `${__PATH__}`, `${__DIR__}`, `${__FILE__}`, which is not compatible
  with bash script.

- <a name="php"></a>Support PHP global variables like `$_SERVER` etc.

  This is not compatible with shell script, thus *NOT* recommended.

  ```php
  HOST=${_SERVER.HTTP_HOST}
  ```

- Support PHP 5.4+, PHP 7.0+, HHVM

- PHP7 ready for return type declarations and argument type declarations.

- PSR-1, PSR-2, PSR-4 compliant.

Change log
---

Please see [CHANGELOG](CHANGELOG.md) from more information.

Testing
---

```bash
$ composer test
```

Contributing
---

Please see [CONTRIBUTE](CONTRIBUTE.md) for more information.

Dependencies
---

- PHP >= 5.4.0

- phossa2/shared >= 2.0.21

License
---

[MIT License](http://mit-license.org/)
