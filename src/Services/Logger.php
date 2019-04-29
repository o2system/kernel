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

namespace O2System\Kernel\Services;

// ------------------------------------------------------------------------

use O2System\Kernel\DataStructures\Config;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * Class Logger
 *
 * @package O2System\Kernel
 */
class Logger extends LogLevel implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Logger Threshold
     *
     * @var array
     */
    protected $threshold = [];

    /**
     * Logger Path
     *
     * @var string
     */
    protected $path;

    /**
     * Logger Lines
     *
     * @var array
     */
    protected $lines = [];

    // ------------------------------------------------------------------------

    /**
     * Logger::__construct
     *
     * @param Config|null $config Logger configuration
     *
     * @return Logger
     */
    public function __construct(Config $config = null)
    {
        if (isset($config->path)) {
            $this->path = $config->path;
        } elseif (defined('PATH_CACHE')) {
            $this->path = PATH_CACHE . 'logs' . DIRECTORY_SEPARATOR;
        } else {
            $this->path = dirname(
                    $_SERVER[ 'SCRIPT_FILENAME' ]
                ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        }

        if (isset($config->threshold)) {
            if (is_array($config->threshold)) {
                $this->threshold = $config->threshold;
            } else {
                array_push($this->threshold, $config->threshold);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::getLines
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::setThreshold
     *
     * Set logger threshold levels
     *
     * @param array $threshold
     */
    public function setThreshold(array $threshold)
    {
        $this->threshold = $threshold;
    }

    // --------------------------------------------------------------------

    /**
     * Logger::addThreshold
     *
     * Add logger threshold levels
     *
     * @param string $threshold
     */
    public function addThreshold($threshold)
    {
        array_push($this->threshold, $threshold);
    }

    // --------------------------------------------------------------------

    /**
     * Logger::setPath
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    // --------------------------------------------------------------------

    /**
     * Logger::log
     *
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function log($level, $message, array $context = [])
    {
        if ( ! in_array($level, $this->threshold)) {
            return false;
        }

        // Try to get language message
        $langMessage = language()->getLine($message, $context);

        // Re-Define message
        $message = empty($langMessage) ? $message : $langMessage;

        if ( ! is_dir($this->path)) {
            mkdir($this->path, true, 0775);
        }

        $this->lines[] = new \ArrayObject([
            'level'   => strtoupper($level),
            'time'    => date('r'),
            'message' => $message,
        ], \ArrayObject::ARRAY_AS_PROPS);

        $isNewFile = false;
        $filePath = $this->path . 'log-' . date('d-m-Y') . '.log';

        $log = '';

        if ( ! is_file($filePath)) {
            $isNewFile = true;
        }

        if ( ! $fp = @fopen($filePath, 'ab')) {
            return false;
        }

        $log .= strtoupper($level) . ' - ' . date('r') . ' --> ' . $message . "\n";

        flock($fp, LOCK_EX);

        $result = null;
        for ($written = 0, $length = strlen($log); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($log, $written))) === false) {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if ($isNewFile === true) {
            chmod($filePath, 0664);
        }

        return (bool)is_int($result);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::alert
     *
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log(Logger::ALERT, $message, $context);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::critical
     *
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log(Logger::CRITICAL, $message, $context);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::error
     *
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log(Logger::ERROR, $message, $context);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::warning
     *
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log(Logger::WARNING, $message, $context);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::notice
     *
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(Logger::NOTICE, $message, $context);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::info
     *
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log(Logger::INFO, $message, $context);
    }

    // ------------------------------------------------------------------------

    /**
     * Logger::debug
     *
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(Logger::DEBUG, $message, $context);
    }
}