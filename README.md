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

- Support shell default value like `${param:-new}` or `${param:=new}`

- Able to `source another_env_file` in the env file

- By default, will **NOT** overwrite any existing environment variables.

  May overwrite by,

  ```php
  env->load('./.env', $overload = true);
  ```

- Relaxed syntax (but not recommended) in env file

  ```php
  # spaces before and after '=' is allowed. NOT recommended though
  ROOT_DIR = /var/tmp

  # end of line comment
  APP_DIR=${ROOT_DIR}/app  # comment here

  # use quotes to quote spaces
  MY_NAME="Phossa Project"
  ```

- Get current *path*, *dir*, *filename* with `${0}`, `${0%/*}`, `${0##*/}`

  ```php
  # set current file
  MY_FILE=${0##*/}

  # set root dir to current dir
  ROOT_DIR=${0%/*}
  ```

  or with `${__PATH__}`, `${__DIR__}`, `${__FILE__}`, which is not compatible
  with shell script.

- Support PHP global variables like `$_SERVER` etc. in env file.

  This is not compatible with shell script, thus *NOT* recommended.

  ```php
  HOST=${_SERVER.HTTP_HOST}
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
