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

/**
 * EnvInterface
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
interface EnvironmentInterface
{
    /**
     * Load env from a file/path (local file or other storage etc.)
     *
     * @param  string $path
     * @return $this
     * @throws LogicException if parse error
     * @throws NotFoundException if $path not found
     * @access public
     */
    public function load(/*# string */ $path);
}
