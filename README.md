# phossa2/env
[![Build Status](https://travis-ci.org/phossa2/env.svg?branch=master)](https://travis-ci.org/phossa2/env)
[![Code Quality](https://scrutinizer-ci.com/g/phossa2/env/badges/quality-score.png?b=master)](https://travis-ci.org/phossa2/env)
[![HHVM](https://img.shields.io/hhvm/phossa2/env.svg?style=flat)](http://hhvm.h4cc.de/package/phossa2/env)
[![Latest Stable Version](https://img.shields.io/packagist/vpre/phossa2/env.svg?style=flat)](https://packagist.org/packages/phossa2/env)
[![License](https://poser.pugx.org/phossa2/env/license)](http://mit-license.org/)

**phossa2/env** is a library to load environments from shell style files.

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

Usage
---

- Put your environments in file `.env`

  ```shell
  # this is comment line
  BASE_DIR='/home/web'

  # reference here
  APP_DIR=${BASE_DIR}/app   # also comment here
  ```

- Load and use your environments

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

- Reference definition is **NOT** required before it is being used.

  ```php
  # reference not defined yet
  APP_DIR=${BASE_DIR}/app

  # define here is ok
  BASE_DIR='/home/web'
  ```

  References can **EVEN** span over multiple files.

- Multiple env files supportted.

  ```php
  $env = new Phossa2\Env\Environment();

  # load one env file
  $env->load(__DIR__ . '/.env');

  # load my own envs
  $env->load(__DIR__ . '/myenv');
  ```

  Any unresolved references in one env file will be tried once a new file
  loaded.

- By default, will **NOT** overwrite any existing environment variables.

  May overwrite by,

  ```php
  env->setOverload(true);
  ```

- Relaxed syntax in env file

  ```php
  # Spaces before and after '=' is allowed.
  ROOT_DIR = /var/tmp

  # end of line comment
  APP_DIR=${ROOT_DIR}/app  # comment here

  # use quotes to quote spaces
  MY_NAME="Phossa Project"
  ```

- Support PHP global variables like `$_SERVER` etc. in env file.

  ```php
  HOST=${_SERVER.HTTP_HOST}
  ```

- Support magic variables, like `__DIR__` and `__FILE__`.

  Note: `${__FILE__}` consists only the file name (no path)

  ```php
  # set current file
  MY_FILE=${__FILE__}

  # set root dir to current dir
  ROOT_DIR=${__DIR__}
  ```

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
