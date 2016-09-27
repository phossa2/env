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

use Phossa2\Env\Message\Message;
use Phossa2\Env\Exception\LogicException;

/**
 * Collections of LOAD related methods
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.2
 * @since   2.0.1 added
 * @since   2.0.2 added support for ${0} etc.
 * @since   2.0.3 changed ${0} to ${BASH_SOURCE}
 */
trait LoadEnvTrait
{
    /**
     * marker for sourcing another env file
     *
     * @var    string
     * @access private
     */
    private $source_marker = '__SOURCING_FILE__';

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
        if (false !== strpos($content, '${BASH_SOURCE') ||
            false !== strpos($content, '${__')
        ) {
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
        $str = file_get_contents($path);

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
        $regex =
        '~^\s*+
            (?:
                (?:([^#\s=]++) \s*+ = \s*+
                    (?|
                        (([^"\'#\s][^#\n]*?)) |
                        (["\'])((?:\\\2|.)*?)\2
                    )?
                ) |
                (?: (\.|source) \s++ ([^#\n]*) )
            )\s*?(?:[#].*)?
        $~mx';

        if (preg_match_all($regex, $string, $matched, \PREG_SET_ORDER)) {
            return $this->clearPairs($matched);
        } else {
            return [];
        }
    }

    /**
     * Clean up, return key/value pairs
     *
     * @param  array $matched
     * @return array
     * @access protected
     */
    protected function clearPairs(/*# array */ $matched)/*# : array */
    {
        $pairs = [];
        foreach ($matched as $m) {
            // source another env file
            if (isset($m[5])) {
                $file = trim($m[5]);
                $pairs[$file] = $this->source_marker;

            // value found
            } elseif (isset($m[3])) {
                $pairs[$m[1]] = $m[3];

            // no value defined
            } else {
                $pairs[$m[1]] = '';
            }
        }
        return $pairs;
    }

    /**
     * Expand PATH/DIR/FILENAME in key & value
     *
     * @param  array &$data
     * @param  string $path
     * @access protected
     * @since  2.0.2 added support for ${0} etc.
     * @since  2.0.3 changed to ${BASH_SOURCE}
     */
    protected function expandMagic(array &$data, $path)
    {
        $srch = [
            '${BASH_SOURCE}', '${BASH_SOURCE%/*}', '${BASH_SOURCE##*/}',
            '${__PATH__}', '${__DIR__}', '${__FILE__}'
        ];
        $repl = [
            $path, dirname($path), basename($path),
            $path, dirname($path), basename($path)
        ];

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
}
