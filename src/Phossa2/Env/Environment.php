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

use Phossa2\Env\Traits\ParseEnvTrait;
use Phossa2\Shared\Base\ObjectAbstract;
use Phossa2\Env\Traits\LoadEnvTrait;

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
 * @since   2.0.1 added overload()
 */
class Environment extends ObjectAbstract implements EnvironmentInterface
{
    use LoadEnvTrait, ParseEnvTrait;

    /**
     * {@inheritDoc}
     */
    public function load(/*# string */ $path)
    {
        return $this->parseEnv($this->loadEnv($path), $path);
    }

    /**
     * {@inheritDoc}
     */
    public function overload(/*# string */ $path)
    {
        return $this->parseEnv($this->loadEnv($path), $path, true);
    }

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
                $overload ? $this->overload($file) : $this->load($file);

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
}
