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
use Phossa2\Env\Exception\NotFoundException;

/**
 * Load environment key/value pairs from certain path.
 *
 * Either read from a local shell-style file or others.
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Environment extends ObjectAbstract implements EnvironmentInterface
{
    /**
     * current load path
     *
     * @var    string
     * @access protected
     */
    protected $path;

    /**
     * the loaded key/value pairs
     *
     * @var    array
     * @access protected
     */
    protected $loaded = [];

    /**
     * env data with unresolved reference
     *
     * @var    array
     * @access protected
     */
    protected $unresolved = [];

    /**
     * overload previous key/value or envs
     *
     * @var    bool
     * @access protected
     */
    protected $overload = false;

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
        // keep a record
        $this->path = $path;

        // read data
        $data = $this->loadFromPath($this->path);

        // check data
        $this->checkLoadedData($data);

        // merge with any unresolved
        if (!empty($this->unresolved)) {
            $data = array_replace($this->unresolved, $data);
            $this->unresolved = [];
        }

        // check data, parse & set envs
        return $this->parseEnv($data);
    }

    /**
     * Parse & set env
     *
     * @param  array $envs
     * @return $this
     * @access protected
     */
    protected function parseEnv(array $envs)/*# : array */
    {
        foreach ($envs as $key => $val) {
            list($str, $success) = $this->resolveReference($val);

            // resolved
            if ($success) {
                $this->setEnv($key, $str);

            // unresolved
            } else {
                $this->unresolved[$key] = $str;
            }

            // update record
            $this->loaded[$key] = $str;
        }
        return $this;
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
        putenv("$key=$val");
        $_ENV[$key] = $val;
    }

    /**
     * Recursively resolve reference in the string $str
     *
     * @param  string $str
     * @return array [result, status]
     * @access protected
     */
    protected function resolveReference(/*# string */ $str)/*# : array */
    {
        $ref = [];
        $cnt = 0;
        $success = true;
        while ($this->hasReference($str, $ref)) {
            $env = $this->matchEnv($ref[1]);
            if (++$cnt > 10 || false === $env) {
                $success = false;
                break;
            } else {
                $str = str_replace($ref[0], $env, $str);
            }
        }
        return [$str, $success];
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
     * - support '${__DIR__}, '${__FILE__} etc.
     * - use getenv()
     *
     * @param  string $name
     * @return string|false
     * @access protected
     */
    protected function matchEnv(/*# string */ $name)
    {
        // defined here
        if (isset($this->loaded[$name])) {
            return $this->loaded[$name];

        // found in environment
        } elseif (false !== getenv($name)) {
            return getenv($name);

        // __DIR__, __FILE__ etc.
        } elseif (substr($name, 0, 2) === '__') {
            return $this->matchMagicVars($name);

        // _SERVER.HTTP_HOST etc.
        } elseif ('_' === $name[0]) {
            return $this->matchGlobalVars($name);

        // not found
        } else {
            return false;
        }
    }

    /**
     * Match with __DIR__, __FILE__ etc.
     *
     * @param  string $name
     * @return false|string
     * @access protected
     */
    protected function matchMagicVars(/*# string */ $name)
    {
        switch($name) {
            case '__DIR__' :
                return dirname($this->path);
            case '__FILE__' :
                return basename($this->path);
            default :
                return false;
        }
    }

    /**
     * Match with _SERVER.HTTP_HOST etc.
     *
     * @param  string $name
     * @return false|string
     * @access protected
     */
    protected function matchGlobalVars(/*# string */ $name)
    {
        if (false !== strpos($name, '.')) {
            list($n, $k) = @explode('.', $name, 2);
            if (isset($GLOBALS[$n]) && isset($GLOBALS[$n][$k])) {
                return $GLOBALS[$n][$k];
            }
        }
        return false;
    }

    /**
     * Read from a file, returns the result array
     *
     * @param  string $path
     * @throws NotFoundException if $path not found
     * @throws LogicException if $path not readable or failure
     * @return array
     * @access protected
     */
    protected function loadFromPath(/*# string */ $path)/*# : array */
    {
        $str = file_get_contents($path);
        if (is_string($str)) {
            return $this->parseString($str);
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
            '~^ \s*+ ([^#\s=]++) \s*+ = \s*+
                (?:([^"\'#\s][^#]*)|((["\'])((?:\\\4|.)*?)\4)|\s*)
                (?:\s*\#.*)?
            $~mx';
        if (preg_match_all($regex, $string, $matched, \PREG_SET_ORDER)) {
            foreach ($matched as $m) {
                $pairs[$m[1]] = isset($m[5]) ? $m[5] : (isset($m[2]) ?
                    trim($m[2]) : '');
            }
        }
        return $pairs;
    }

    /**
     * Check loaded data
     *
     * @param  array &$data
     * @return $this
     * @throws LogicException if duplication found
     * @access protected
     */
    protected function checkLoadedData(array &$data)
    {
        foreach ($data as $key => $val) {
            // do not overload
            if (!$this->overload && false !== getenv($key)) {
                unset($data[$key]);

            // redefined
            } elseif (!$this->overload && isset($this->loaded[$key])) {
                throw new LogicException(
                    Message::get(Message::ENV_REDEFINE, $key),
                    Message::ENV_REDEFINE
                );

            } else {
                $this->loaded[$key] = $val;
            }
        }
        return $this;
    }
}
