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

namespace O2System\Kernel\Cli;

// ------------------------------------------------------------------------

use O2System\Gear\Trace;
use O2System\Kernel\Cli\Writers\Format;
use O2System\Kernel\Cli\Writers\Line;
use O2System\Kernel\Cli\Writers\Table;
use O2System\Spl\Exceptions\Abstracts\AbstractException;
use O2System\Spl\Exceptions\ErrorException;
use O2System\Spl\Traits\Collectors\FilePathCollectorTrait;

/**
 * Class Output
 *
 * @package O2System\Kernel\Http
 */
class Output
{
    use FilePathCollectorTrait;

    // ------------------------------------------------------------------------

    /**
     * Output::__construct
     */
    public function __construct()
    {
        // Set Output Views Directory
        $this->setFileDirName('Views');
        $this->addFilePath(PATH_KERNEL);

        // Autoload exception and error language file
        language()->loadFile(['exception', 'error']);

        // Register Kernel defined handler
        $this->register();
    }

    // ------------------------------------------------------------------------

    /**
     * Output::register
     *
     * Register Kernel defined error, exception and shutdown handler.
     *
     * @return void
     */
    public function register()
    {
        set_error_handler([&$this, 'errorHandler']);
        set_exception_handler([&$this, 'exceptionHandler']);
        register_shutdown_function([&$this, 'shutdownHandler']);
    }

    // ------------------------------------------------------------------------

    /**
     * Output::shutdownHandler
     *
     * Kernel defined shutdown handler function.
     *
     * @return void
     * @throws \O2System\Spl\Exceptions\ErrorException
     */
    public function shutdownHandler()
    {
        $lastError = error_get_last();

        if (is_array($lastError)) {
            $this->errorHandler(
                $lastError[ 'type' ],
                $lastError[ 'message' ],
                $lastError[ 'file' ],
                $lastError[ 'line' ]
            );
        }

        // Execute shutdown service
        if(services()->has('shutdown')) {
            shutdown()->execute();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::errorHandler
     *
     * Kernel defined error handler function.
     *
     * @param int    $errno      The first parameter, errno, contains the level of the error raised, as an integer.
     * @param string $errstr     The second parameter, errstr, contains the error message, as a string.
     * @param string $errfile    The third parameter is optional, errfile, which contains the filename that the error
     *                           was raised in, as a string.
     * @param string $errline    The fourth parameter is optional, errline, which contains the line number the error
     *                           was raised at, as an integer.
     * @param array  $errcontext The fifth parameter is optional, errcontext, which is an array that points to the
     *                           active symbol table at the point the error occurred. In other words, errcontext will
     *                           contain an array of every variable that existed in the scope the error was triggered
     *                           in. User error handler must not modify error context.
     *
     * @return bool If the function returns FALSE then the normal error handler continues.
     * @throws ErrorException
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, array $errcontext = [])
    {
        $isFatalError = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $errno) === $errno);

        // When the error is fatal the Kernel will throw it as an exception.
        if ($isFatalError) {
            throw new ErrorException($errstr, $errno, $errfile, $errline);
        }

        // Should we ignore the error? We'll get the current error_reporting
        // level and add its bits with the severity bits to find out.
        if (($errno & error_reporting()) !== $errno) {
            return false;
        }

        $error = new ErrorException($errstr, $errno, $errfile, $errline);
        
        // Logged the error
        if(services()->has('logger')) {
            logger()->error(
                implode(
                    ' ',
                    [
                        '[ ' . $error->getStringSeverity() . ' ] ',
                        $error->getMessage(),
                        $error->getFile() . ':' . $error->getLine(),
                    ]
                )
            );
        }

        $errdisplay = str_ireplace(['off', 'none', 'no', 'false', 'null'], 0, ini_get('display_errors'));

        // Should we display the error?
        if ($errdisplay == 1) {
            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString(language()->getLine('E_CAUGHT_ERROR'))
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::WARNING)
                    ->setString('[ ' . language()->getLine($error->getStringSeverity()) . ' ] ' . $error->getMessage())
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::INFO)
                    ->setString($error->getFile() . ':' . $error->getLine())
                    ->setNewLinesAfter(1)
            );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::write
     *
     * Write text to console.
     *
     * @param string $text
     * @param string $type
     */
    public function write($text, $type = 'stdout')
    {
        if (in_array($type, ['stdout', 'stderr']) && ! empty($text)) {
            $f = fopen('php://' . $type, 'w');

            if (is_array($text)) {
                $text = json_encode($text);
            }

            fwrite($f, $text);
            fclose($f);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::exceptionHandler
     *
     * Kernel defined exception handler function.
     *
     * @param \Exception|\Error|\O2System\Spl\Exceptions\Abstracts\AbstractException $exception Throwable exception.
     *
     * @return void
     */
    public function exceptionHandler($exception)
    {
        // Standard PHP Libraries Error
        if ($exception instanceof \Error) {
            $error = new ErrorException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine()
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString(language()->getLine('E_CAUGHT_ERROR'))
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::WARNING)
                    ->setString('[ ' . language()->getLine($error->getStringSeverity()) . ' ] ' . $error->getMessage())
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::INFO)
                    ->setString($error->getFile() . ':' . $error->getLine())
                    ->setNewLinesAfter(1)
            );

            exit(EXIT_ERROR);
        } elseif ($exception instanceof AbstractException) {

            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString(language()->getLine($exception->getHeader()))
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString('[ ' . language()->getLine($exception->getCode()) . ' ] ' . language()->getLine($exception->getDescription()))
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString($exception->getMessage())
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setString($debugTitle = language()->getLine('E_DEBUG_BACKTRACE') . ':')
                    ->setContextualClass(Format::INFO)
                    ->setNewLinesBefore(1)
                    ->setNewLinesAfter(1)
            );

            output()->write((new Line(strlen($debugTitle) * 2))
                ->setContextualClass(Line::INFO)
                ->setNewLinesAfter(2));

            $table = new Table();
            $table->isShowBorder = false;

            $i = 1;
            foreach ($exception->getChronology() as $chronology) {
                $table
                    ->addRow()
                    ->addColumn($i . '. ' . $chronology->call)
                    ->addRow()
                    ->addColumn($chronology->file . ':' . $chronology->line)
                    ->addRow()
                    ->addColumn('');

                $i++;
            }

            output()->write(
                (new Format())
                    ->setContextualClass(Format::INFO)
                    ->setString($table->render())
                    ->setNewLinesAfter(2)
            );
        } // Standard PHP Libraries Exception
        elseif ($exception instanceof \Exception) {
            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString('[ ' . language()->getLine($exception->getCode()) . ' ] ' . language()->getLine($exception->getMessage()))
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setContextualClass(Format::DANGER)
                    ->setString($exception->getMessage())
                    ->setNewLinesAfter(1)
            );

            output()->write(
                (new Format())
                    ->setString($debugTitle = language()->getLine('E_DEBUG_BACKTRACE') . ':')
                    ->setContextualClass(Format::INFO)
                    ->setNewLinesBefore(1)
                    ->setNewLinesAfter(1)
            );

            output()->write((new Line(strlen($debugTitle) * 2))
                ->setContextualClass(Line::INFO)
                ->setNewLinesAfter(2));

            $table = new Table();
            $table->isShowBorder = false;

            $trace = new Trace($exception->getTrace());

            $i = 1;
            foreach ($trace->getChronology() as $chronology) {
                $table
                    ->addRow()
                    ->addColumn($i . '. ' . $chronology->call)
                    ->addRow()
                    ->addColumn($chronology->file . ':' . $chronology->line)
                    ->addRow()
                    ->addColumn('');

                $i++;
            }

            output()->write(
                (new Format())
                    ->setContextualClass(Format::INFO)
                    ->setString($table->render())
                    ->setNewLinesAfter(2)
            );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::sendError
     *
     * @param int               $code
     * @param null|array|string $vars
     */
    public function sendError($code = 204, $vars = null)
    {
        static $errors = [];

        if (empty($errors)) {
            $errors = require(str_replace('Cli', 'Config', __DIR__) . DIRECTORY_SEPARATOR . 'Errors.php');
        }

        if (isset($errors[ $code ])) {
            $languageKey = $errors[ $code ];
        }

        $languageKey = strtoupper($code . '_' . $languageKey);

        $error = [
            'code'    => $code,
            'title'   => language()->getLine($languageKey . '_TITLE'),
            'message' => language()->getLine($languageKey . '_MESSAGE'),
        ];

        $this->statusCode = $code;
        $this->reasonPhrase = $error[ 'title' ];

        if (is_string($vars)) {
            $error[ 'message' ] = $vars;
        } elseif (is_array($vars)) {
            $error = array_merge($error, $vars);
        }

        $this->write(
            (new Format())
                ->setContextualClass(Format::DANGER)
                ->setString($error[ 'code' ] . ' - ' . $error[ 'title' ])
                ->setNewLinesAfter(1)
        );

        $this->write(
            (new Format())
                ->setString($error[ 'message' ])
                ->setNewLinesAfter(1)
        );

        exit(EXIT_ERROR);
    }

    // ------------------------------------------------------------------------

    /**
     * Output::verbose
     *
     * Write verbose text to console.
     *
     * @param string $text
     * @param string $type
     */
    public function verbose($text, $type = 'stdout')
    {
        if (isset($_ENV[ 'VERBOSE' ]) and $_ENV[ 'VERBOSE' ] === true) {
            $this->write($text, $type);
        }
    }
}