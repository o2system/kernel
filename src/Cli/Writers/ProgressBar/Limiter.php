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
 * Class Limiter
 *
 * Progress bar frequency limit to avoiding app slow performance.
 *
 * @package O2System\Kernel\Cli\Writers\ProgressBar
 */
class Limiter
{
    /**
     * Limiter::$frequency
     *
     * @var int
     */
    protected $frequency;

    /**
     * Limiter::$limit
     *
     * @var int
     */
    protected $limit;

    /**
     * Limiter::$timer
     *
     * Frequency limit timer instance.
     *
     * @var \O2System\Kernel\Cli\Writers\ProgressBar\Timer
     */
    protected $timer;

    // ------------------------------------------------------------------------

    /**
     * Limiter::__construct
     *
     * @param $frequency
     */
    public function __construct($frequency)
    {
        $this->setFrequency($frequency);
        $this->timer = new Timer();
        $this->timer->start();
    }

    // ------------------------------------------------------------------------

    /**
     * Limiter::setFrequency
     *
     * Sets the frequency limit
     *
     * @param int $frequency
     *
     * @return static
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
        $this->limit = 1.0 / $frequency;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Limiter::isValid
     *
     * Validate if the timer still in time frame.
     *
     * @param int $frequency
     *
     * @return bool
     */
    public function isValid()
    {
        $timeLimit = $this->timer->getSeconds();

        if ($timeLimit > $this->limit) {
            $this->timer->start($timeLimit - $this->limit);

            return true;
        }

        return false;
    }
}