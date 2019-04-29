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

namespace O2System\Kernel\Cli\Writers;

// ------------------------------------------------------------------------

/**
 * Class ProgressBar
 *
 * @package O2System\Kernel\Cli\Writers
 */
class ProgressBar
{
    /**
     * ProgressBar::$columns
     *
     * @var mixed
     */
    protected $columns;

    /**
     * ProgressBar::$limiter
     *
     * @var \O2System\Kernel\Cli\Writers\ProgressBar\Limiter
     */
    protected $limiter;

    /**
     * ProgressBar::$units
     *
     * @var mixed
     */
    protected $units;

    /**
     * ProgressBar::$total
     *
     * @var mixed
     */
    protected $total;

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::__construct
     */
    public function __construct()
    {
        // change the fps limit as needed
        $this->limiter = new ProgressBar\Limiter(10);

        output()->write(PHP_EOL);
    }

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::__destruct
     */
    public function __destruct()
    {
        $this->write();
    }

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::write
     *
     * Write progress bar.
     */
    protected function write()
    {
        $this->updateSize();
        $this->writeStatus($this->units, $this->total, $this->columns, $this->columns);
    }

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::updateSize
     *
     * Execute columns update.
     */
    protected function updateSize()
    {
        // get the number of columns
        $this->columns = exec("tput cols");
    }

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::writeStatus
     *
     * @param     $done
     * @param     $total
     * @param int $size
     * @param int $lineWidth
     */
    protected function writeStatus($done, $total, $size = 30, $lineWidth = -1)
    {
        if ($lineWidth <= 0) {
            $lineWidth = input()->env('COLUMNS');
        }

        static $start_time;

        // to take account for [ and ]
        $size -= 3;
        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }

        if (empty($start_time)) {
            $start_time = time();
        }
        $now = time();

        $percent = (double)($done / $total);

        $bar = floor($percent * $size);

        // jump to the begining
        output()->write("\r");

        // jump a line up
        output()->write("\x1b[A");

        $statusBar = "[";
        $statusBar .= str_repeat("=", $bar);
        if ($bar < $size) {
            $statusBar .= ">";
            $statusBar .= str_repeat(" ", $size - $bar);
        } else {
            $statusBar .= "=";
        }

        $percentOutput = number_format($percent * 100, 0);

        $statusBar .= "]";
        $details = "$percentOutput%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;


        $details .= ' ' . language()->getLine('CLI_PROGRESS_BAR_ESTIMATION') . ': ' . $this->formatTime($eta) . ' ' . language()->getLine('CLI_PROGRESS_BAR_ELAPSED') . ': ' . $this->formatTime($elapsed) . ' ';

        $lineWidth--;
        if (strlen($details) >= $lineWidth) {
            $details = substr($details, 0, $lineWidth - 1);
        }

        output()->write(implode(PHP_EOL, [
            $details,
            $statusBar,
        ]));

        //echo "$details\n$status_bar";

        flush();

        // when done, send a newline
        if ($done == $total) {
            //echo "\n";
            output()->write(PHP_EOL);
        }

    }

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::formatTime
     *
     * @param $time
     *
     * @return string
     */
    protected function formatTime($time)
    {
        if ($time > 100) {
            $time /= 60;
            if ($time > 100) {
                $time /= 60;

                return number_format($time) . " hr";
            }

            return number_format($time) . " min";
        }

        return number_format($time) . " sec";
    }

    // ------------------------------------------------------------------------

    /**
     * ProgressBar::update
     *
     * @param $units
     * @param $total
     */
    public function update($units, $total)
    {
        $this->units = $units;
        $this->total = $total;
        if ( ! $this->limiter->isValid()) {
            return;
        }
        $this->write();
    }
}