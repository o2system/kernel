<?php
/**
 * This file is part of the O2System Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace O2System\Kernel\Cli\Writers\ProgressBar;

// ------------------------------------------------------------------------

/**
 * Class Timer
 *
 * @package O2System\Kernel\Cli\Writers\ProgressBar
 */
class Timer
{
    /**
     * Timer::$time
     *
     * @var int
     */
    protected $time;

    /**
     * Timer::__construct
     */
    public function __construct()
    {
        $this->start();
    }

    // ------------------------------------------------------------------------

    /**
     * Timer::start
     *
     * @param int $offset
     */
    public function start($offset = 0)
    {
        $this->time = microtime(true) + $offset;
    }

    // ------------------------------------------------------------------------

    /**
     * Timer::getSeconds
     *
     * @return mixed
     */
    public function getSeconds()
    {
        return microtime(true) - $this->time;
    }
}