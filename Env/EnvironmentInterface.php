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
 * EnvironmentInterface
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.3
 * @since   2.0.0 added
 * @since   2.0.3 changed $overload default to TRUE
 */
interface EnvironmentInterface
{
    /**
     * Load env from a file/path
     *
     * @param  string $path
     * @param  bool $overload overwrite existing env variables
     * @return $this
     * @throws LogicException if parse error
     * @throws NotFoundException if file not found
     * @access public
     * @since  2.0.0 added
     * @since  2.0.3 changed $overload to TRUE
     * @api
     */
    public function load(/*# string */ $path, /*# bool */ $overload = true);
}
