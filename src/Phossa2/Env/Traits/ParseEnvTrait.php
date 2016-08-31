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

use Phossa2\Shared\Reference\ReferenceTrait;

/**
 * Collections of PARSE related methods
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.4
 * @since   2.0.1 added
 * @since   2.0.4 using ReferenceTrait
 */
trait ParseEnvTrait
{
    use ReferenceTrait;

    /**
     * Resolving sourced file './path/to/file' or '../path/to/file'
     *
     * @param  string $file
     * @param  string $path
     * @return string
     * @access protected
     */
    protected function expandPath(
        /*# string */ $file,
        /*# string */ $path
    )/*# : string */ {
        // relative path found
        if (false !== strpos($file, './')) {
            // remember working dir
            $old = getcwd();

            // change to current file's directory
            chdir(dirname(realpath($path)));

            // expand file path
            $file = realpath($file);

            // back to working dir
            chdir($old);
        }

        return $file;
    }

    /**
     * Find the env value base on the name
     *
     * - support super globals like '${_SERVER.HTTP_HOST}' etc.
     * - use getenv()
     *
     * {@inheritDoc}
     */
    protected function getReference(/*# string */ $name)
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
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function resolveUnknown(/*# string */ $name)
    {
        return '';
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
                $this->setEnv($name, $default, false);
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
     * @param  bool $overload
     * @access protected
     */
    protected function setEnv(
        /*# string */ $key,
        /*# string */ $val,
        /*# bool */ $overload
    ) {
        if ($overload || false === getenv($key)) {
            // set env
            putenv("$key=$val");

            // also populate $_ENV
            $_ENV[$key] = $val;
        }
    }
}
