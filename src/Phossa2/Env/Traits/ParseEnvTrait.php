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

namespace Phossa2\Env\Traits;

/**
 * Collections of PARSE related methods
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.1
 * @since   2.0.1 added
 */
trait ParseEnvTrait
{
    use LoadEnvTrait;

    /**
     * Parse & set env
     *
     * @param  array $envs env pairs
     * @param  string $path current path
     * @param  bool $overload overwrite existing env or not
     * @return $this
     * @access protected
     */
    protected function parseEnv(
        array $envs,
        /*# string */ $path,
        /*# bool */ $overload = false
    )/*# : array */ {
        foreach ($envs as $key => $val) {
            // source another env file
            if ($this->source_marker === $val) {
                $file = $this->resolvePath(
                    $this->resolveReference($key), // may have refs in it
                    $path
                );
                $this->load($file);

            // not overload
            } elseif (!$overload && false !== getenv($key)) {
                continue;

            // set env
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
     * @param  string &$name
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
