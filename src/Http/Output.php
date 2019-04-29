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

namespace O2System\Kernel\Http;

// ------------------------------------------------------------------------

use O2System\Gear\Trace;
use O2System\Spl\Exceptions\Abstracts\AbstractException;
use O2System\Spl\Exceptions\ErrorException;
use O2System\Spl\Traits\Collectors\FilePathCollectorTrait;
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;

/**
 * Class Output
 *
 * @package O2System\Kernel\Http
 */
class Output extends Message\Response
{
    use FilePathCollectorTrait;

    /**
     * Output::$mimeType
     *
     * @var string
     */
    protected $mimeType = 'text/html';

    /**
     * Output::$charset
     *
     * @var string
     */
    protected $charset = 'utf8';

    // ------------------------------------------------------------------------

    /**
     * Output::__construct
     */
    public function __construct()
    {
        parent::__construct();

        // Set Browser Views Directory
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
    final private function register()
    {
        $whoops = new \Whoops\Run();

        if (is_ajax() or $this->mimeType === 'application/json' or $this->mimeType === 'application/xml') {
            $whoops->pushHandler(new CallbackHandler(function ($error) {
                $this->send([
                    'status'   => 500,
                    'success'  => false,
                    'message'  => $error->getMessage(),
                    'metadata' => [
                        'file'  => $error->getFile(),
                        'line'  => $error->getLine(),
                        'trace' => $error->getTrace(),
                    ],
                ]);
            }));
        } elseif (is_cli() or $this->mimeType === 'text/plain') {
            $whoops->pushHandler(new PlainTextHandler());
        } elseif ($this->mimeType === 'text/html') {
            $whoops->pushHandler(new PrettyPageHandler());
        }

        $whoops->register();

        set_error_handler([&$this, 'errorHandler']);
        set_exception_handler([&$whoops, 'handleException']);
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
    public function errorHandler($errorSeverity, $errorMessage, $errorFile, $errorLine, $errorContext = [])
    {
        $isFatalError = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $errorSeverity) === $errorSeverity);

        // When the error is fatal the Kernel will throw it as an exception.
        if ($isFatalError) {
            throw new ErrorException($errorMessage, $errorSeverity, $errorLine, $errorLine, $errorContext);
        }

        // Should we ignore the error? We'll get the current error_reporting
        // level and add its bits with the severity bits to find out.
        if (($errorSeverity & error_reporting()) !== $errorSeverity) {
            return false;
        }

        $error = new ErrorException($errorMessage, $errorSeverity, $errorFile, $errorLine, $errorContext);

        // Logged the error
        if (services()->has('logger')) {
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

        // Should we display the error?
        if (str_ireplace(['off', 'none', 'no', 'false', 'null'], 0, ini_get('display_errors')) == 1) {
            if (is_ajax()) {
                $this->setContentType('application/json');
                $this->statusCode = 500;
                $this->reasonPhrase = 'Internal Server Error';

                $this->send(implode(
                    ' ',
                    [
                        '[ ' . $error->getStringSeverity() . ' ] ',
                        $error->getMessage(),
                        $error->getFile() . ':' . $error->getLine(),
                    ]
                ));
                exit(EXIT_ERROR);
            }

            $filePath = $this->getFilePath('error');

            ob_start();
            include $filePath;
            $htmlOutput = ob_get_contents();
            ob_end_clean();

            echo $htmlOutput;
            exit(EXIT_ERROR);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::getFilePath
     *
     * @param string $filename
     *
     * @return string
     */
    public function getFilePath($filename)
    {
        $filePaths = array_reverse($this->filePaths);

        foreach ($filePaths as $filePath) {
            if (is_file($filePath . $filename . '.phtml')) {
                return $filePath . $filename . '.phtml';
                break;
            } elseif (is_file($filePath . 'errors' . DIRECTORY_SEPARATOR . $filename . '.phtml')) {
                return $filePath . 'errors' . DIRECTORY_SEPARATOR . $filename . '.phtml';
                break;
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
    public function setContentType($mimeType, $charset = null)
    {
        static $mimes = [];

        if (empty($mimes)) {
            $mimes = require(str_replace('Http', 'Config', __DIR__) . DIRECTORY_SEPARATOR . 'Mimes.php');
        }

        if (strpos($mimeType, '/') === false) {
            $extension = ltrim($mimeType, '.');
            // Is this extension supported?
            if (isset($mimes[ $extension ])) {
                $mimeType =& $mimes[ $extension ];
                if (is_array($mimeType)) {
                    $mimeType = current($mimeType);
                }
            }
        }

        $this->mimeType = $mimeType;

        $this->addHeader(
            'Content-Type',
            $mimeType
            . (empty($charset) ? '' : '; charset=' . $charset)
        );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Output::addHeader
     *
     * @param string $name
     * @param string $value
     *
     * @return static
     */
    public function addHeader($name, $value)
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
    public function send($data = null, array $headers = [])
    {
        $response = [
            'status'  => $statusCode = $this->statusCode,
            'reason'  => $reasonPhrase = readable($this->reasonPhrase),
            'success' => true,
            'message' => null,
            'result'  => [],
        ];

        if (is_array($data)) {
            if (isset($data[ 'status' ])) {
                $response[ 'status' ] = $statusCode = $data[ 'status' ];
                unset($data[ 'status' ]);
            }

            if (isset($data[ 'reason' ])) {
                $response[ 'reason' ] = $reasonPhrase = $data[ 'reason' ];
                unset($data[ 'reason' ]);
            }

            if (isset($data[ 'success' ])) {
                $response[ 'success' ] = $data[ 'success' ];
                unset($data[ 'success' ]);
            }

            if (isset($data[ 'message' ])) {
                $response[ 'message' ] = $data[ 'message' ];
                unset($data[ 'message' ]);
            }

            if (isset($data[ 'metadata' ])) {
                $response[ 'metadata' ] = $data[ 'metadata' ];
                unset($data[ 'metadata' ]);
            }

            if (isset($data[ 'result' ])) {
                $data = $data[ 'result' ];
            }

            if (isset($data[ 'data' ])) {
                $data = $data[ 'data' ];
            }
        } elseif (is_object($data)) {
            if (isset($data->status)) {
                $response[ 'status' ] = $statusCode = $data->status;
                unset($data->status);
            }

            if (isset($data->reason)) {
                $response[ 'reason' ] = $reasonPhrase = $data->reason;
                unset($data->reason);
            }

            if (isset($data->success)) {
                $response[ 'success' ] = $data->success;
                unset($data->success);
            }

            if (isset($data->message)) {
                $response[ 'message' ] = $data->message;
                unset($data->message);
            }

            if (isset($data->result)) {
                $data = $data->result;
            }

            if (isset($data->data)) {
                $data = $data->data;
            }
        }

        if (is_object($data) and method_exists($data, 'getArrayCopy')) {
            $data = $data->getArrayCopy();
        }

        $this->sendHeaderStatus($statusCode, $reasonPhrase);

        $this->sendHeaders($headers);

        if (is_object($data) and method_exists($data, 'getArrayCopy')) {
            $data = $data->getArrayCopy();
        }

        if (is_array($data)) {
            if (is_string(key($data))) {
                $response[ 'result' ] = [$data];
            } elseif (is_numeric(key($data))) {
                $response[ 'result' ] = $data;
            }
        } else {
            $response[ 'result' ] = $data;
        }

        if (is_ajax()) {
            $contentType = isset($_SERVER[ 'HTTP_X_REQUESTED_CONTENT_TYPE' ]) ? $_SERVER[ 'HTTP_X_REQUESTED_CONTENT_TYPE' ] : 'application/json';
            $this->setContentType($contentType);
        }

        if ($this->mimeType === 'application/json') {
            echo json_encode($response, JSON_PRETTY_PRINT);
        } elseif ($this->mimeType === 'application/xml') {
            $xml = new \SimpleXMLElement('<?xml version="1.0"?><response></response>');
            $xml->addAttribute('status', $statusCode);
            $xml->addAttribute('reason', $reasonPhrase);
            $this->arrayToXml($response, $xml);

            echo $xml->asXML();
        } elseif(is_cli()) {
            print_cli($response, true);
        } elseif(is_array($response['result'])) {
            print_r($response['result']);
        } else {
            echo $response[ 'result' ];
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::sendHeaders
     *
     * @param array $headers
     */
    protected function sendHeaders(array $headers = [])
    {
        ini_set('expose_php', 0);

        // collect headers that already sent
        foreach (headers_list() as $header) {
            $headerParts = explode(':', $header);
            $headerParts = array_map('trim', $headerParts);
            $headers[ $headerParts[ 0 ] ] = $headerParts[ 1 ];
            header_remove($header[ 0 ]);
        }

        if (count($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        }

        if ($this->statusCode === 204) {
            $this->statusCode = 200;
            $this->reasonPhrase = 'OK';
        }

        $this->sendHeaderStatus($this->statusCode, $this->reasonPhrase, $this->protocol);

        foreach ($this->headers as $name => $value) {
            $this->sendHeader($name, $value);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::sendHeaderStatus
     *
     * @param int    $statusCode
     * @param string $reasonPhrase
     * @param string $protocol
     *
     * @return $this
     */
    public function sendHeaderStatus($statusCode, $reasonPhrase, $protocol = '1.1')
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = empty($reasonPhrase) ? error_code_string($statusCode) : $reasonPhrase;

        @header('HTTP/' . $protocol . ' ' . $statusCode . ' ' . $reasonPhrase, true);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Output::sendHeader
     *
     * @param string $name
     * @param string $value
     * @param bool   $replace
     *
     * @return static
     */
    public function sendHeader($name, $value, $replace = true)
    {
        @header($name . ': ' . trim($value), $replace);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Output::arrayToXml
     *
     * @param array             $data
     * @param \SimpleXMLElement $xml
     */
    protected function arrayToXml(array $data, \SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Output::sendPayload
     *
     * @param array       $data
     * @param string|null $mimeType
     */
    public function sendPayload(array $data, $mimeType = null)
    {
        $mimeType = isset($mimeType) ? $mimeType : 'application/json';
        $this->setContentType($mimeType);

        if ($mimeType === 'application/json') {
            $payload = json_encode($data, JSON_PRETTY_PRINT);
        } elseif ($mimeType === 'application/xml') {
            $xml = new \SimpleXMLElement('<?xml version="1.0"?><payload></payload>');
            $this->arrayToXml($data, $xml);
            $payload = $xml->asXML();
        }

        $this->sendHeaders();
        echo $payload;
    }

    // ------------------------------------------------------------------------

    /**
     * Output::sendError
     *
     * @param int               $code
     * @param null|array|string $vars
     * @param array             $headers
     */
    public function sendError($code = 204, $vars = null, $headers = [])
    {
        $languageKey = $code . '_' . error_code_string($code);

        $error = [
            'code'    => $code,
            'title'   => language()->getLine($languageKey . '_TITLE'),
            'message' => language()->getLine($languageKey . '_MESSAGE'),
        ];

        $this->statusCode = $code;
        $this->reasonPhrase = $error[ 'title' ];

        if (is_string($vars)) {
            $vars = ['message' => $vars];
        } elseif (is_array($vars) and empty($vars[ 'message' ])) {
            $vars[ 'message' ] = $error[ 'message' ];
        }

        if (isset($vars[ 'message' ])) {
            $error[ 'message' ] = $vars[ 'message' ];
        }

        if (is_ajax() or $this->mimeType !== 'text/html') {
            $this->statusCode = $code;
            $this->reasonPhrase = $error[ 'title' ];
            $this->send($vars);

            exit(EXIT_ERROR);
        }

        $this->sendHeaders($headers);

        extract($error);

        ob_start();
        include $this->getFilePath('error-code');
        $htmlOutput = ob_get_contents();
        ob_end_clean();

        echo $htmlOutput;
        exit(EXIT_ERROR);
    }
}
