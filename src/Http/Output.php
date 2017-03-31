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
use O2System\Spl\Exceptions\Abstracts\AbstractException;
use O2System\Spl\Exceptions\ErrorException;
use O2System\Spl\Traits\Collectors\FilePathCollectorTrait;

/**
 * Class Output
 *
 * @package O2System\Kernel\Http
 */
class Output extends Message\Response
{
    use FilePathCollectorTrait;

    protected $mimeType = 'text/html';

    protected $charset = 'utf8';

    // ------------------------------------------------------------------------

    /**
     * Output::__construct
     *
     * Constructs the Kernel Output.
     *
     * @return Output
     */
    public function __construct()
    {
        parent::__construct();

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
    public function register()
    {
        set_error_handler( [ &$this, 'errorHandler' ] );
        set_exception_handler( [ &$this, 'exceptionHandler' ] );
        register_shutdown_function( [ &$this, 'shutdownHandler' ] );
    }

    /**
     * Output::shutdownHandler
     *
     * Kernel defined shutdown handler function.
     *
     * @return void
     */
    public function shutdownHandler()
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
    // --------------------------------------------------------------------

    /**
     * Output::errorHandler
     *
     * Kernel defined error handler function.
     *
     * @param int    $errorSeverity The first parameter, errno, contains the level of the error raised, as an integer.
     * @param string $errorMessage  The second parameter, errstr, contains the error message, as a string.
     * @param string $errorFile     The third parameter is optional, errfile, which contains the filename that the error
     *                              was raised in, as a string.
     * @param string $errorLine     The fourth parameter is optional, errline, which contains the line number the error
     *                              was raised at, as an integer.
     * @param array  $errorContext  The fifth parameter is optional, errcontext, which is an array that points to the
     *                              active symbol table at the point the error occurred. In other words, errcontext will
     *                              contain an array of every variable that existed in the scope the error was triggered
     *                              in. User error handler must not modify error context.
     *
     * @return bool If the function returns FALSE then the normal error handler continues.
     * @throws ErrorException
     */
    public function errorHandler( $errorSeverity, $errorMessage, $errorFile, $errorLine, array $errorContext = [] )
    {
        $isFatalError = ( ( ( E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR ) & $errorSeverity ) === $errorSeverity );

        // When the error is fatal the Kernel will throw it as an exception.
        if ( $isFatalError ) {
            throw new ErrorException( $errorMessage, $errorSeverity, $errorFile, $errorLine, $errorContext );
        }

        // Should we ignore the error? We'll get the current error_reporting
        // level and add its bits with the severity bits to find out.
        if ( ( $errorSeverity & error_reporting() ) !== $errorSeverity ) {
            return false;
        }

        $error = new ErrorException( $errorMessage, $errorSeverity, $errorFile, $errorLine, $errorContext );

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

        $displayError = str_ireplace( [ 'off', 'none', 'no', 'false', 'null' ], 0, ini_get( 'display_errors' ) );

        // Should we display the error?
        if ( $displayError == 1 ) {
            if ( is_ajax() ) {
                $this->setContentType( 'application/json' );
                $this->statusCode = 500;
                $this->reasonPhrase = 'Internal Server Error';

                $this->send( implode(
                    ' ',
                    [
                        '[ ' . $error->getStringSeverity() . ' ] ',
                        $error->getMessage(),
                        $error->getFile() . ':' . $error->getLine(),
                    ]
                ) );
            } else {

                foreach ( array_reverse( $this->filePaths ) as $filePath ) {
                    if ( is_file( $filePath . 'error.phtml' ) ) {
                        $filePath .= 'error.phtml';
                        break;
                    }
                }

                ob_start();
                include $filePath;
                $buffer = ob_get_contents();
                ob_end_clean();

                echo $buffer;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::setContentType
     *
     * @param string $mimeType
     * @param string $charset
     *
     * @return $this
     */
    public function setContentType( $mimeType, $charset = null )
    {
        static $mimes = [];

        if ( empty( $mimes ) ) {
            $mimes = require( str_replace( 'Http', 'Config', __DIR__ ) . DIRECTORY_SEPARATOR . 'Mimes.php' );
        }

        if ( strpos( $mimeType, '/' ) === false ) {
            $extension = ltrim( $mimeType, '.' );
            // Is this extension supported?
            if ( isset( $mimes[ $extension ] ) ) {
                $mimeType =& $mimes[ $extension ];
                if ( is_array( $mimeType ) ) {
                    $mimeType = current( $mimeType );
                }
            }
        }

        $this->mimeType = $mimeType;

        $this->addHeader(
            'Content-Type',
            $mimeType
            . ( empty( $charset ) ? '' : '; charset=' . $charset )
        );

        return $this;
    }

    // ------------------------------------------------------------------------

    public function addHeader( $name, $value )
    {
        $this->headers[ $name ] = $value;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Output::send
     *
     * @param       $data
     * @param array $headers
     */
    public function send( $data = null, array $headers = [] )
    {
        $statusCode = $this->statusCode;
        $reasonPhrase = $this->reasonPhrase;

        if ( is_ajax() ) {
            $contentType = isset( $_SERVER[ 'HTTP_X_REQUESTED_CONTENT_TYPE' ] ) ? $_SERVER[ 'HTTP_X_REQUESTED_CONTENT_TYPE' ] : 'application/json';
            $this->setContentType( $contentType );
        }

        $this->sendHeaders( $headers );

        if ( is_array( $data ) OR is_object( $data ) ) {
            $response = [
                'status' => (int)$statusCode,
                'reason' => $reasonPhrase,
            ];

            if ( array_key_exists( 'message', $data ) ) {
                $response[ 'message' ] = $data[ 'message' ];
                unset( $data[ 'message' ] );
            }

            if ( count( $data ) ) {
                $response[ 'result' ] = $data;
            }

            if ( $this->mimeType === 'application/json' ) {
                echo json_encode( $response, JSON_PRETTY_PRINT );
            } elseif ( $this->mimeType === 'application/xml' ) {
                $xml = new \SimpleXMLElement( '<response/>' );

                $result = $response[ 'result' ];
                unset( $response[ 'result' ] );

                foreach ( $response as $item => $value ) {
                    $xml->addAttribute( $item, $value );
                }

                function array_to_xml( $data, \SimpleXMLElement &$xml )
                {
                    foreach ( $data as $key => $value ) {
                        if ( is_numeric( $key ) ) {
                            $key = 'item' . $key; //dealing with <0/>..<n/> issues
                        }
                        if ( is_array( $value ) ) {
                            $subnode = $xml->addChild( $key );
                            array_to_xml( $value, $subnode );
                        } else {
                            $xml->addChild( "$key", htmlspecialchars( "$value" ) );
                        }
                    }
                }

                array_to_xml( $result, $xml );

                echo $xml->asXML();
            } else {
                echo serialize( $data );
            }

        } elseif ( $this->mimeType === 'application/json' ) {
            $response = [
                'status' => (int)$statusCode,
                'reason' => $reasonPhrase,
            ];

            if ( ! empty( $data ) ) {
                $response[ 'message' ] = $data;
            }

            echo json_encode( $response, JSON_PRETTY_PRINT );
        } elseif ( $this->mimeType === 'application/xml' ) {
            $xml = new \SimpleXMLElement( '<response/>' );
            $xml->addAttribute( 'status', $statusCode );
            $xml->addAttribute( 'reason', $reasonPhrase );

            if ( ! empty( $data ) ) {
                $xml->addChild( 'message', $data );
            }
            echo $xml->asXML();
        } else {
            echo $data;
        }

        exit( EXIT_SUCCESS );
    }

    protected function sendHeaders( array $headers = [] )
    {
        ini_set( 'expose_php', 0 );

        // collect headers that already sent
        foreach ( headers_list() as $header ) {
            $headerParts = explode( ':', $header );
            $headerParts = array_map( 'trim', $headerParts );
            $headers[ $headerParts[ 0 ] ] = $headerParts[ 1 ];
            header_remove( $header[ 0 ] );
        }

        if ( count( $headers ) ) {
            $this->headers = array_merge( $this->headers, $headers );
        }

        if ( $this->statusCode === 204 ) {
            $this->statusCode = 200;
            $this->reasonPhrase = 'OK';
        }

        header( 'HTTP/' . $this->protocol . ' ' . $this->statusCode . ' ' . $this->reasonPhrase );

        foreach ( $this->headers as $name => $value ) {
            header( $name . ': ' . trim( $value ) );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::exceptionHandler
     *
     * Kernel defined exception handler function.
     *
     * @param \Exception|\Error|\O2System\Kernel\Abstracts\AbstractException $exception Throwable exception.
     *
     * @return void
     */
    public function exceptionHandler( $exception )
    {
        if ( is_ajax() ) {
            $this->statusCode = 500;
            $this->reasonPhrase = 'Internal Server Error';

            $this->send( implode(
                ' ',
                [
                    ( $exception->getCode() != 0 ? '[ ' . $exception->getCode() . ']' : '' ),
                    $exception->getMessage(),
                    $exception->getFile() . ':' . $exception->getLine(),
                ]
            ) );
        } elseif ( $exception instanceof \Error ) {
            $error = new ErrorException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine()
            );

            foreach ( array_reverse( $this->filePaths ) as $filePath ) {
                if ( is_file( $filePath . 'error.phtml' ) ) {
                    $filePath .= 'error.phtml';
                    break;
                }
            }

            ob_start();
            include $filePath;
            $buffer = ob_get_contents();
            ob_end_clean();
            echo $buffer;

            exit( EXIT_ERROR );
        } elseif ( $exception instanceof AbstractException ) {
            foreach ( array_reverse( $this->filePaths ) as $filePath ) {
                $filePath .= 'exception.phtml';

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
        } elseif ( $exception instanceof \Exception ) {
            foreach ( array_reverse( $this->filePaths ) as $filePath ) {
                $filePath .= 'exception-spl.phtml';

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

    /**
     * Output::sendError
     *
     * @param int               $code
     * @param null|array|string $vars
     * @param array             $headers
     */
    public function sendError( $code = 204, $vars = null, $headers = [] )
    {
        $languageKey = $code . '_' . error_code_string( $code );

        $error = [
            'code'    => $code,
            'title'   => language()->getLine( $languageKey . '_TITLE' ),
            'message' => language()->getLine( $languageKey . '_MESSAGE' ),
        ];

        $this->statusCode = $code;
        $this->reasonPhrase = $error[ 'title' ];

        if ( is_string( $vars ) ) {
            $error[ 'message' ] = $vars;
        } elseif ( is_array( $vars ) ) {
            $error = array_merge( $error, $vars );
        }

        if ( is_ajax() or $this->mimeType !== 'text/html' ) {
            $this->statusCode = $code;
            $this->reasonPhrase = $error[ 'title' ];
            $this->send();
        } else {
            $this->sendHeaders( $headers );
            extract( $error );

            foreach ( array_reverse( $this->filePaths ) as $filePath ) {
                if ( is_file( $filePath . 'error-code.phtml' ) ) {
                    $filePath .= 'error-code.phtml';
                    break;
                }
            }

            ob_start();
            include $filePath;
            $buffer = ob_get_contents();
            ob_end_clean();
            echo $buffer;
        }

        exit( EXIT_ERROR );
    }
}