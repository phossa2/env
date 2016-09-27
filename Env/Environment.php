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

use Phossa2\Env\Traits\LoadEnvTrait;
use Phossa2\Env\Traits\ParseEnvTrait;
use Phossa2\Shared\Base\ObjectAbstract;

/**
 * Load environment key/value pairs from certain path.
 *
 * - support shell behavior
 * - support ${param:-word} and ${param:=word}
 * - extra support for PHP superglobals like '${_SERVER.HTTP_HOST}'
 * - added support for ${BASH_SOURCE} etc.
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.2
 * @since   2.0.0 added
 * @since   2.0.2 added support for ${0} etc.
 * @sincee  2.0.3 changed to ${BASH_SOURCE}, default overload is TRUE
 * @since   2.0.4 using ReferenceTrait
 */
class Environment extends ObjectAbstract implements EnvironmentInterface
{
    use LoadEnvTrait, ParseEnvTrait;

    /**
     * {@inheritDoc}
     */
    public function load(/*# string */ $path, /*# bool */ $overload = true)
    {
        $pairs = $this->loadEnv($path);
        return $this->parseEnv($pairs, $path, (bool) $overload);
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
        /*# bool */ $overload
    )/*# : array */ {
        foreach ($envs as $key => $val) {
            // source another env file
            if ($this->source_marker === $val) {
                $src = $this->expandPath($this->deReference($key), $path);
                $this->load($src, $overload);

            // set env
            } else {
                $this->setEnv($key, $this->deReference($val), $overload);
            }
        }
        return $this;
    }
}
