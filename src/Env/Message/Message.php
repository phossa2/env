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

namespace Phossa2\Env\Message;

use Phossa2\Shared\Message\Message as BaseMessage;

/**
 * Mesage class for Phossa2\Env
 *
 * @package Phossa2\Env
 * @author  Hong Zhang <phossa@126.com>
 * @version 2.0.0
 * @since   2.0.0 added
 */
class Message extends BaseMessage
{
    /*
     * Read env file "%s" failed
     */
    const ENV_READ_FAIL = 1606200902;

    /**
     * {@inheritDoc}
     */
    protected static $messages = [
        self::ENV_READ_FAIL => 'Read env file "%s" failed',
    ];
}
