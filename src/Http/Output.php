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
 * Class Browser
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
     * Browser::__construct
     *
     * Constructs the Kernel Browser.
     *
     * @return Output
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
     * Browser::register
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

    /**
     * Browser::shutdownHandler
     *
     * Kernel defined shutdown handler function.
     *
     * @return void
     */
    public function shutdownHandler()
    {
        $lastError = error_get_last();

        if (is_array($lastError)) {
            $this->errorHandler(
                $lastError['type'],
                $lastError['message'],
                $lastError['file'],
                $lastError['line']
            );
        }

        // Execute Kernel Shutdown Service
        shutdown()->execute();
    }
    // --------------------------------------------------------------------

    /**
     * Browser::errorHandler
     *
     * Kernel defined error handler function.
     *
     * @param int $errorSeverity The first parameter, errno, contains the level of the error raised, as an integer.
     * @param string $errorMessage The second parameter, errstr, contains the error message, as a string.
     * @param string $errorFile The third parameter is optional, errfile, which contains the filename that the error
     *                              was raised in, as a string.
     * @param string $errorLine The fourth parameter is optional, errline, which contains the line number the error
     *                              was raised at, as an integer.
     * @param array $errorContext The fifth parameter is optional, errcontext, which is an array that points to the
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

        if (strpos($errorFile, 'parser') !== false) {
            if (function_exists('parser')) {
                if (services()->has('presenter')) {
                    presenter()->initialize();

                    $vars = presenter()->getArrayCopy();
                    extract($vars);
                }

                $errorFile = str_replace(PATH_ROOT, DIRECTORY_SEPARATOR, parser()->getSourceFilePath());
                $error = new ErrorException($errorMessage, $errorSeverity, $errorFile, $errorLine, $errorContext);

                $filePath = $this->getFilePath('error');

                ob_start();
                include $filePath;
                $htmlOutput = ob_get_contents();
                ob_end_clean();

                echo $htmlOutput;
                return;
            }
        }

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

            if (class_exists('O2System\Framework')) {
                if (services()->has('presenter')) {
                    presenter()->initialize();

                    if (presenter()->theme->use) {
                        presenter()->theme->load();
                    }

                    $vars = presenter()->getArrayCopy();
                    extract($vars);
                }
            }

            $filePath = $this->getFilePath('error');

            ob_start();
            include $filePath;
            $htmlOutput = ob_get_contents();
            ob_end_clean();

            if (class_exists('O2System\Framework')) {
                if (services()->has('presenter')) {
                    $htmlOutput = presenter()->assets->parseSourceCode($htmlOutput);
                }
            }

            echo $htmlOutput;
            exit(EXIT_ERROR);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Browser::setContentType
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
            if (isset($mimes[$extension])) {
                $mimeType =& $mimes[$extension];
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

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Browser::send
     *
     * @param       $data
     * @param array $headers
     */
    public function send($data = null, array $headers = [])
    {
        $statusCode = $this->statusCode;
        $reasonPhrase = $this->reasonPhrase;

        if (is_ajax()) {
            $contentType = isset($_SERVER['HTTP_X_REQUESTED_CONTENT_TYPE']) ? $_SERVER['HTTP_X_REQUESTED_CONTENT_TYPE'] : 'application/json';
            $this->setContentType($contentType);
        }

        $this->sendHeaders($headers);

        $response = [
            'status' => (int)$statusCode,
            'reason' => $reasonPhrase,
            'success' => $statusCode >= 200 && $statusCode < 300 ? true : false,
            'message' => isset($data['message']) ? $data['message'] : '',
            'result' => [],
        ];

        if ($data instanceof \ArrayIterator) {
            $data = $data->getArrayCopy();
        }

        if (is_array($data) and count($data)) {
            if (is_numeric(key($data))) {
                $response['result'] = $data;
            } elseif (is_string(key($data))) {
                if (array_key_exists('success', $data)) {
                    $response['success'] = $data['success'];
                    unset($data['success']);
                }

                if (array_key_exists('message', $data)) {
                    $response['message'] = $data['message'];
                    unset($data['message']);
                }

                if (array_key_exists('timestamp', $data)) {
                    $response['timestamp'] = $data['timestamp'];
                    unset($data['timestamp']);
                }

                if (array_key_exists('metadata', $data)) {
                    $response['metadata'] = $data['metadata'];
                    unset($data['metadata']);
                }

                if (array_key_exists('errors', $data)) {
                    $response['errors'] = $data['errors'];
                }

                if (array_key_exists('error', $data)) {
                    $response['error'] = $data['error'];
                }

                if (array_key_exists('data', $data)) {
                    if ($data['data'] instanceof \ArrayIterator) {
                        $data['data'] = $data['data']->getArrayCopy();
                    }

                    if (is_array($data['data'])) {
                        if (is_string(key($data['data']))) {
                            $response['result'] = [$data['data']];
                        } elseif (is_numeric(key($data['data']))) {
                            $response['result'] = $data['data'];
                        }
                    } else {
                        $response['result'] = [$data['data']];
                    }
                }
            }
        }

        if (is_object($data)) {
            if (isset($data->success)) {
                $response['success'] = $data->success;
                unset($data->success);
            }

            if (isset($data->message)) {
                $response['message'] = $data->message;
                unset($data->message);
            }

            if (isset($data->timestamp)) {
                $response['timestamp'] = $data->timestamp;
                unset($data->timestamp);
            }

            if (isset($data->metadata)) {
                $response['metadata'] = $data->metadata;
                unset($data->metadata);
            }

            if (isset($data->errors)) {
                $response['errors'] = $data->errors;
                unset($data->errors);
            }

            if (isset($data->error)) {
                $response['error'] = $data->error;
                unset($data->error);
            }

            if (isset($data->data)) {
                if ($data->data instanceof \ArrayIterator) {
                    $data->data = $data->data->getArrayCopy();
                }

                if (is_array($data->data)) {
                    if (is_string(key($data->data))) {
                        $response['result'] = [$data->data];
                    } elseif (is_numeric(key($data->data))) {
                        $response['result'] = $data->data;
                    }
                } else {
                    $response['result'] = [$data->data];
                }
            }
        }

        if ($this->mimeType === 'application/json') {
            if (!empty($data)) {
                array_push($response['result'], $data);
            }

            echo json_encode($response, JSON_PRETTY_PRINT);
        } elseif ($this->mimeType === 'application/xml') {
            $xml = new \SimpleXMLElement('<?xml version="1.0"?><response></response>');
            $xml->addAttribute('status', $statusCode);
            $xml->addAttribute('reason', $reasonPhrase);

            if (!empty($data)) {
                $this->arrayToXml(['message' => $data], $xml);
            }
            echo $xml->asXML();
        } else {
            echo $data;
        }

        exit(EXIT_SUCCESS);
    }

    public function sendPayload(array $data, $mimeType = null)
    {
        $mimeType = isset($mimeType) ? $mimeType : $this->mimeType;
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
        exit(EXIT_SUCCESS);
    }

    protected function arrayToXml($data, \SimpleXMLElement &$xml)
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

    protected function sendHeaders(array $headers = [])
    {
        ini_set('expose_php', 0);

        // collect headers that already sent
        foreach (headers_list() as $header) {
            $headerParts = explode(':', $header);
            $headerParts = array_map('trim', $headerParts);
            $headers[$headerParts[0]] = $headerParts[1];
            header_remove($header[0]);
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

    public function sendHeaderStatus($statusCode, $reasonPhrase, $protocol = '1.1')
    {
        @header('HTTP/' . $protocol . ' ' . $statusCode . ' ' . $reasonPhrase, true);

        return $this;
    }

    public function sendHeader($name, $value, $replace = true)
    {
        @header($name . ': ' . trim($value), $replace);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Browser::exceptionHandler
     *
     * Kernel defined exception handler function.
     *
     * @param \Exception|\Error|\O2System\Spl\Exceptions\Abstracts\AbstractException $exception Throwable exception.
     *
     * @return void
     */
    public function exceptionHandler($exception)
    {
        if (is_ajax()) {
            $this->statusCode = 500;
            $this->reasonPhrase = 'Internal Server Error';

            $this->send(implode(
                ' ',
                [
                    ($exception->getCode() != 0 ? '[ ' . $exception->getCode() . ']' : ''),
                    $exception->getMessage(),
                    $exception->getFile() . ':' . $exception->getLine(),
                ]
            ));
        } elseif ($exception instanceof AbstractException) {

            if (class_exists('O2System\Framework')) {
                if (services()->has('presenter')) {
                    presenter()->initialize();

                    if (presenter()->theme->use) {
                        presenter()->theme->load();
                    }

                    $vars = presenter()->getArrayCopy();
                    extract($vars);
                }
            }

            ob_start();
            include $this->getFilePath('exception');
            $htmlOutput = ob_get_contents();
            ob_end_clean();

            if (class_exists('O2System\Framework')) {
                if (services()->has('presenter')) {
                    $htmlOutput = presenter()->assets->parseSourceCode($htmlOutput);
                }
            }

            echo $htmlOutput;
            exit(EXIT_ERROR);
        } elseif ($exception instanceof \Exception || $exception instanceof \Error) {

            $exceptionClassName = get_class_name($exception);
            $header = language()->getLine('E_HEADER_' . $exceptionClassName);
            $description = language()->getLine('E_DESCRIPTION_' . $exceptionClassName);
            $trace = new Trace($exception->getTrace());

            if (class_exists('O2System\Framework')) {
                if (services()->has('presenter')) {
                    presenter()->initialize();

                    if (presenter()->theme->use) {
                        presenter()->theme->load();
                    }

                    $vars = presenter()->getArrayCopy();
                    extract($vars);
                }
            }

            ob_start();
            include $this->getFilePath('exception-spl');
            $htmlOutput = ob_get_contents();
            ob_end_clean();

            if (class_exists('O2System\Framework')) {
                if (services()->has('presenter')) {
                    $htmlOutput = presenter()->assets->parseSourceCode($htmlOutput);
                }
            }

            echo $htmlOutput;
            exit(EXIT_ERROR);
        }
    }

    /**
     * Browser::sendError
     *
     * @param int $code
     * @param null|array|string $vars
     * @param array $headers
     */
    public function sendError($code = 204, $vars = null, $headers = [])
    {
        $languageKey = $code . '_' . error_code_string($code);

        $error = [
            'code' => $code,
            'title' => language()->getLine($languageKey . '_TITLE'),
            'message' => language()->getLine($languageKey . '_MESSAGE'),
        ];

        $this->statusCode = $code;
        $this->reasonPhrase = $error['title'];

        if (is_string($vars)) {
            $vars = ['message' => $vars];
        } elseif (is_array($vars) and empty($vars['message'])) {
            $vars['message'] = $error['message'];
        }

        if (isset($vars['message'])) {
            $error['message'] = $vars['message'];
        }

        if (is_ajax() or $this->mimeType !== 'text/html') {
            $this->statusCode = $code;
            $this->reasonPhrase = $error['title'];
            $this->send($vars);

            exit(EXIT_ERROR);
        }

        $this->sendHeaders($headers);

        if (class_exists('O2System\Framework')) {
            if (services()->has('presenter')) {
                presenter()->initialize();

                if (presenter()->theme->use) {
                    presenter()->theme->load();
                }

                $vars = presenter()->getArrayCopy();
                extract($vars);
            }
        }

        extract($error);

        ob_start();
        include $this->getFilePath('error-code');
        $htmlOutput = ob_get_contents();
        ob_end_clean();

        if (class_exists('O2System\Framework')) {
            if (services()->has('presenter')) {
                $htmlOutput = presenter()->assets->parseSourceCode($htmlOutput);
            }
        }

        echo $htmlOutput;
        exit(EXIT_ERROR);
    }

    public function getFilePath($filename)
    {
        $filePaths = array_reverse($this->filePaths);

        if (class_exists('O2System\Framework')) {
            if (function_exists('modules')) {
                if (modules()) {
                    $filePaths = modules()->getDirs('Views');
                }
            }
        }

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
}
