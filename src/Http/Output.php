<?php
/**
 * This file is part of the O2System PHP Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

namespace O2System\Kernel\Http;

// ------------------------------------------------------------------------

use O2System\Gear\Trace;
use O2System\Kernel\Abstracts\AbstractException;
use O2System\Kernel\Spl\Exceptions\ErrorException;
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
     *
     * Constructs the Kernel Output.
     *
     * @return Output
     */
    public function __construct ()
    {
        // Set Output Views Directory
        $this->setFileDirName( 'Views' );
        $this->addFilePath( PATH_KERNEL );

        // Autoload exception and error language file
        language()->loadFile( [ 'exception', 'error' ] );

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
    public function register ()
    {
        set_error_handler( [ &$this, 'errorHandler' ] );
        set_exception_handler( [ &$this, 'exceptionHandler' ] );
        register_shutdown_function( [ &$this, 'shutdownHandler' ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Output::shutdownHandler
     *
     * Kernel defined shutdown handler function.
     *
     * @return void
     */
    public function shutdownHandler ()
    {
        $lastError = error_get_last();

        if ( is_array( $lastError ) ) {
            $this->errorHandler(
                $lastError[ 'type' ],
                $lastError[ 'message' ],
                $lastError[ 'file' ],
                $lastError[ 'line' ]
            );
        }

        // Execute Kernel Shutdown Service
        shutdown()->execute();
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
    public function errorHandler ( $errno, $errstr, $errfile, $errline, array $errcontext = [ ] )
    {
        $isFatalError = ( ( ( E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR ) & $errno ) === $errno );

        // When the error is fatal the Kernel will throw it as an exception.
        if ( $isFatalError ) {
            throw new ErrorException( $errstr, $errno, $errfile, $errline );
        }

        // Should we ignore the error? We'll get the current error_reporting
        // level and add its bits with the severity bits to find out.
        if ( ( $errno & error_reporting() ) !== $errno ) {
            return false;
        }

        $error = new ErrorException( $errstr, $errno, $errfile, $errline );

        // Logged the error
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

        $errdisplay = str_ireplace( [ 'off', 'none', 'no', 'false', 'null' ], 0, ini_get( 'display_errors' ) );

        // Should we display the error?
        if ( $errdisplay == 1 ) {
            if ( is_ajax() ) {
                $this->showJSON(
                    null,
                    500,
                    'INTERNAL_SERVER_ERROR',
                    implode(
                        ' ',
                        [
                            '[ ' . $error->getStringSeverity() . ' ] ',
                            $error->getMessage(),
                            $error->getFile() . ':' . $error->getLine(),
                        ]
                    )
                );
            } else {
                ob_start();
                include PATH_KERNEL . 'Views/http/error.phtml';
                $buffer = ob_get_contents();
                ob_end_clean();

                echo $buffer;
            }
        }
    }

    // ------------------------------------------------------------------------

    public function showJSON ( $data, $status = 200, $description = 'OK', $message = 'OK' )
    {
        if ( ! is_int( $status ) ) {
            $status = 500;
            $description = 'INTERNAL_SERVER_ERROR';
            $message = 'Invalid type of status';
        }

        if ( empty( $data ) AND $status === 200 ) {
            $status = 204;
            $description = 'NO_CONTENT';
        }

        $langDescription = language()->getLine( $langDescriptionLine = $status . '_' . $description . '_DESCRIPTION' );

        if ( $langDescription !== $langDescriptionLine ) {
            $description = $langDescription;
        }
        if ( $langDescription !== $langDescriptionLine ) {
            $description = $langDescriptionLine;
        } else {
            $description = readable( str_replace( '_DESCRIPTION', '', $description ) );
        }

        if ( empty( $data ) ) {
            $response = [
                'status'      => (int) $status,
                'description' => '',
                'message'     => $message,
            ];
        } else {
            $response = [
                'status'      => (int) $status,
                'description' => '',
                'message'     => $message,
                'result'      => $data,
            ];
        }

        // Set Http Header
        header( 'HTTP/1.0 ' . $status . ' ' . $description );

        echo json_encode( $response, JSON_PRETTY_PRINT );

        exit( EXIT_SUCCESS );
    }

    // ------------------------------------------------------------------------

    public function show ( $string, array $headers = [ ] )
    {
        echo $string;

        exit( EXIT_SUCCESS );
    }

    /**
     * Output::exceptionHandler
     *
     * Kernel defined exception handler function.
     *
     * @param \Exception|\Error|\O2System\Kernel\Abstracts\AbstractException $exception Throwable exception.
     *
     * @return void
     */
    public function exceptionHandler ( $exception )
    {
        // Standard PHP Libraries Error
        if ( $exception instanceof \Error ) {
            $error = new ErrorException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine()
            );

            ob_start();
            include PATH_KERNEL . 'Views' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'error.phtml';
            $buffer = ob_get_contents();
            ob_end_clean();
            echo $buffer;

            exit( EXIT_ERROR );
        } elseif ( $exception instanceof AbstractException ) {
            foreach ( $this->filePaths as $filePath ) {
                $filePath .= 'http' . DIRECTORY_SEPARATOR . 'exception.phtml';

                if ( is_file( $filePath ) ) {
                    ob_start();
                    include $filePath;
                    $buffer = ob_get_contents();
                    ob_end_clean();
                    echo $buffer;

                    exit( EXIT_ERROR );

                    break;
                }
            }
        } // Standard PHP Libraries Exception
        elseif ( $exception instanceof \Exception ) {
            foreach ( $this->filePaths as $filePath ) {
                $filePath .= 'http' . DIRECTORY_SEPARATOR . 'exception-spl.phtml';

                if ( is_file( $filePath ) ) {
                    $exceptionClassName = get_class_name( $exception );
                    $header = language()->getLine( 'E_HEADER_' . $exceptionClassName );
                    $description = language()->getLine( 'E_DESCRIPTION_' . $exceptionClassName );

                    $trace = new Trace( $exception->getTrace() );

                    ob_start();
                    include $filePath;
                    $buffer = ob_get_contents();
                    ob_end_clean();
                    echo $buffer;

                    exit( EXIT_ERROR );

                    break;
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    public function showError ( $code = 204, $header = 'NO_CONTENT', $description = 'NO_CONTENT', $message = '' )
    {
        $langHeader = language()->getLine( $langHeaderLine = $code . '_' . $header . '_HEADER' );
        $langDescription = language()->getLine( $langDescriptionLine = $code . '_' . $description . '_DESCRIPTION' );

        if ( $langHeader !== $langHeaderLine ) {
            $header = $langHeader;
        } else {
            $header = readable( str_replace( '_HEADER', '', $header ) );
        }

        if ( $langDescription !== $langDescriptionLine ) {
            $description = $langDescriptionLine;
        } else {
            $description = readable( str_replace( '_DESCRIPTION', '', $description ) );
        }

        // Set Http Header
        header( 'HTTP/1.0 ' . $code . ' ' . $header );

        ob_start();
        include PATH_KERNEL . 'Views' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'error-code.phtml';
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;

        exit( EXIT_ERROR );
    }

    // ------------------------------------------------------------------------

    protected function getAssetsUrl ( $path = null )
    {
        $scriptFilename = str_replace( [ '/', '\\' ], '/', dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) );
        $scriptName = str_replace( [ '/', '\\' ], '/', dirname( $_SERVER[ 'SCRIPT_NAME' ] ) );
        $kernelDirectory = str_replace( [ '/', '\\' ], '/', PATH_KERNEL );

        if ( strpos( $scriptName, 'public' ) ) {
            $scriptFilename = str_replace( 'public', '', $scriptFilename );
            $scriptName = str_replace( 'public', '', $scriptName );
        }

        return '//' . $_SERVER[ 'HTTP_HOST' ] . $scriptName . str_replace(
            $scriptFilename,
            '',
            $kernelDirectory
        ) . 'Views/http/assets/' . ( is_array( $path ) ? implode( '/', $path ) : $path );
    }
}