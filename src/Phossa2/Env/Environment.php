<?php
/**
 * Phossa Project
 *
 * PHP version 5.4
 *
 * @category  Library
 * @package   Phossa2\Env
 * @copyright Copyright (c) 2016 phossa.com
 * @license   http://mit-license.org/ MIT License
 * @link      http://www.phossa.com/
 */
/*# declare(strict_types=1); */

namespace Phossa2\Env;

use Phossa2\Env\Message\Message;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Env\Exception\LogicException;

/**
 * Load environment key/value pairs from certain path.
 *
 * - support shell behavior
 * - support ${param:-word} and ${param:=word}
 * - extra support for PHP superglobals like '${_SERVER.HTTP_HOST}'
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.1
 * @since   2.0.0 added
 */
class Environment extends ObjectAbstract implements EnvironmentInterface
{
    /**
     * overload previous key/value or envs
     *
     * @var    bool
     * @access protected
     */
    protected $overload = false;

    /*
     * mark for including another file
     */
    const LOAD_FILE = '__LOAD_FILE__';

    /**
     * constructor
     *
     * @param  bool $overload override existing env
     * @access public
     */
    public function __construct(/*# : bool */ $overload = false)
    {
        $this->setOverload($overload);
    }

    /**
     * Set overloading
     *
     * @param  bool $overload
     * @access public
     */
    public function setOverload(/*# bool */ $overload)
    {
        $this->overload = $overload;
    }

    /**
     * {@inheritDoc}
     */
    public function load(/*# string */ $path)
    {
        return $this->parseEnv($this->loadEnv($path), $path);
    }

    /**
     * Load & parse, return the key/value pairs
     *
     * @param  string $path
     * @return array
     * @throws LogicException if $path not readable or failure
     * @access protected
     */
    protected function loadEnv(/*# string */ $path)/*# : array */
    {
        // read data
        $content = $this->readContent($path);

        // parse it
        $pairs = $this->parseString($content);

        // expand any '${__DIR__}' or '${__FILE__}'
        if (false !== strpos($content, '${__')) {
            $this->expandMagic($pairs, $path);
        }

        return $pairs;
    }

    /**
     * Read from a file, returns the content string
     *
     * @param  string $path
     * @throws LogicException if $path not readable or failure
     * @return string
     * @access protected
     */
    protected function readContent(/*# string */ $path)/*# : string */
    {
        $str = @file_get_contents($path);

        if (is_string($str)) {
            return $str;
        } else {
            throw new LogicException(
                Message::get(Message::ENV_READ_FAIL, $path),
                Message::ENV_READ_FAIL
                );
        }
    }

    /**
     * Parse whole string into key/value pairs
     *
     * @param  string $string
     * @return array
     * @access protected
     */
    protected function parseString(/*# string */ $string)/*# : array */
    {
        $pairs = [];
        $regex =
        '~^\s*+
            (?:
                (?:([^#\s=]++) \s*+ = \s*+
                    (?:
                        ([^"\'#\s][^#\r\n]*) |
                        ((["\'])((?:\\\4|.)*?)\4) |
                        \s*
                    )(?:\s*\#.*)?
                ) |
                (?: (\.|source) \s++ ([^#\r\n]*) )
            )
        $~mx';
        if (preg_match_all($regex, $string, $matched, \PREG_SET_ORDER)) {
            foreach ($matched as $m) {
                // source another env file
                if (isset($m[7])) {
                    $file = trim($m[7]);
                    $pairs[$file] = self::LOAD_FILE;

                    // quoted "val"
                } elseif (isset($m[5])) {
                    $pairs[$m[1]] = $m[5];

                    // normal case
                } elseif (isset($m[2])) {
                    $pairs[$m[1]] = trim($m[2]);

                    // no value defined
                } else {
                    $pairs[$m[1]] = '';
                }
            }
        }
        return $pairs;
    }

    /**
     * Expand ${__DIR__} & ${__FILE__} in key and value
     *
     * @param  array &$data
     * @param  string $path
     * @access protected
     */
    protected function expandMagic(array &$data, $path)
    {
        $srch = ['${__DIR__}', '${__FILE__}'];
        $repl = [ dirname($path), basename($path) ];

        // expand both key and value
        foreach ($data as $key => $val) {
            $k2 = str_replace($srch, $repl, $key);
            $v2 = str_replace($srch, $repl, $val);
            if ($k2 !== $key) {
                unset($data[$key]);
                $key = $k2;
            }
            $data[$key] = $v2;
        }
    }

    /**
     * Parse & set env
     *
     * @param  array $envs env pairs
     * @param  string $path current path
     * @return $this
     * @access protected
     */
    protected function parseEnv(array $envs, /*# string */ $path)/*# : array */
    {
        foreach ($envs as $key => $val) {
            // source another env file
            if (self::LOAD_FILE === $val) {
                $file = $this->resolvePath(
                    $this->resolveReference($key), // may have refs in it
                    $path
                );
                $this->load($file);

            // not overload
            } elseif (!$this->overload && false !== getenv($key)) {
                continue;

            // normal pair
            } else {
                $this->setEnv($key, $this->resolveReference($val));
            }
        }

        return $this;
    }

    /**
     * Resolving any './path/to/file' or '../path/to/file'
     *
     * @param  string $file
     * @param  string $path
     * @return string
     * @access protected
     */
    protected function resolvePath(
        /*# string */ $file,
        /*# string */ $path
    )/*# : string */ {
        if (false !== strpos($file, './')) {
            // remember working dir
            $old = getcwd();

            // change to current file's directory
            chdir(dirname(realpath($path)));

            // expand file path
            $real = realpath($file);

            // back to working dir
            chdir($old);

            return $real;
        } else {
            return $file;
        }
    }

    /**
     * Recursively resolve reference in the string $str
     *
     * @param  string $str
     * @return string
     * @access protected
     */
    protected function resolveReference(/*# string */ $str)/*# : string */
    {
        $ref = [];
        while ($this->hasReference($str, $ref)) {
            $env = $this->matchEnv($ref[1]);
            $str = str_replace($ref[0], $env, $str);
        }
        return $str;
    }

    /**
     * Has reference in the string ?
     *
     * @param  string $string
     * @param  array &$matches
     * @return bool
     * @access protected
     */
    protected function hasReference(
        /*# string */ $string, array &$matches
    )/*# : bool */ {
        if (false !== strpos($string, '${') &&
            preg_match('/\$\{([^\}]+)\}/', $string, $matches)) {
                return true;
        }
        return false;
    }

    /**
     * Find the env value base on the name
     *
     * - support super globals like '${_SERVER.HTTP_HOST}' etc.
     * - use getenv()
     *
     * @param  string $name
     * @return string
     * @access protected
     */
    protected function matchEnv(/*# string */ $name)
    {
        // default value
        $default = $this->defaultValue($name);

        // found in environment
        if (false !== getenv($name)) {
            return getenv($name);

        } elseif (null !== $default) {
            return $default;

        // PHP globals, _SERVER.HTTP_HOST etc.
        } elseif ('_' === $name[0]) {
            return $this->matchGlobalVars($name);

        // not found
        } else {
            return '';
        }
    }

    /**
     * Get default value
     *
     * @param  string $str
     * @return string|null
     * @access protected
     */
    protected function defaultValue(/*# string */ &$name)
    {
        $default = null;
        if (false !== strpos($name, ':-')) {
            list($name, $default) = explode(':-', $name, 2);
        } elseif (false !== strpos($name, ':=')) {
            list($name, $default) = explode(':=', $name, 2);
            if (false === getenv($name)) {
                $this->setEnv($name, $default);
            }
        }
        return $default;
    }

    /**
     * Match with _SERVER.HTTP_HOST etc.
     *
     * @param  string $name
     * @return string
     * @access protected
     */
    protected function matchGlobalVars(/*# string */ $name)/*# : string */
    {
        if (false !== strpos($name, '.')) {
            list($n, $k) = explode('.', $name, 2);
            if (isset($GLOBALS[$n]) && isset($GLOBALS[$n][$k])) {
                return $GLOBALS[$n][$k];
            }
        }
        return '';
    }

    /**
     * Set the env pair
     *
     * @param  string $key
     * @param  string $val
     * @access protected
     */
    protected function setEnv(/*# string */ $key, /*# string */ $val)
    {
        // set env
        putenv("$key=$val");

        // also populate $_ENV
        $_ENV[$key] = $val;
    }
}
