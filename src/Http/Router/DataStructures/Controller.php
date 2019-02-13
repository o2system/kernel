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

namespace O2System\Kernel\Http\Router\DataStructures;

// ------------------------------------------------------------------------

use O2System\Kernel\Http\Message\Uri\Segments;
use O2System\Spl\Info\SplClassInfo;

/**
 * Class Controller
 *
 * @package O2System\DataStructures
 */
class Controller extends SplClassInfo
{
    /**
     * Controller::$requestSegments
     *
     * Request Segments
     *
     * @var Segments
     */
    private $requestSegments;

    /**
     * Controller::$requestMethod
     *
     * @var string|null
     */
    private $requestMethod = null;

    /**
     * Controller::$requestMethodArgs
     *
     * @var array
     */
    private $requestMethodArgs = [];

    /**
     * Controller::$properties
     *
     * @var array
     */
    private $properties = [];

    /**
     * Controller::$instance
     *
     * @var \O2System\Kernel\Http\Controller
     */
    private $instance;

    // ------------------------------------------------------------------------

    /**
     * Controller::__construct
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        if (is_object($filePath)) {
            if ($filePath instanceof \O2System\Kernel\Http\Controller) {
                parent::__construct($filePath);
                $this->instance = $filePath;
            }
        } elseif (is_string($filePath) && is_file($filePath)) {
            $className = prepare_class_name(pathinfo($filePath, PATHINFO_FILENAME));
            @list($namespaceDirectory, $subNamespace) = explode('Controllers', dirname($filePath));

            $classNamespace = loader()->getDirNamespace(
                    $namespaceDirectory
                ) . 'Controllers' . (empty($subNamespace) ? null : str_replace('/', '\\', $subNamespace)) . '\\';
            $className = $classNamespace . $className;

            if (class_exists($className)) {
                parent::__construct($className);
            } elseif (class_exists('\O2System\Kernel\Http\\' . $className)) {
                parent::__construct('\O2System\Kernel\Http\\' . $className);
            }
        } elseif (class_exists($filePath)) {
            parent::__construct($filePath);
        } elseif (class_exists('\O2System\Kernel\Http\\' . $filePath)) {
            parent::__construct('\O2System\Kernel\Http\\' . $filePath);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::setProperties
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::getInstance
     *
     * @return \O2System\Kernel\Http\Controller|string
     */
    public function &getInstance()
    {
        if (empty($this->instance)) {
            $className = $this->name;
            $this->instance = new $className();

            if (count($this->properties)) {
                foreach ($this->properties as $key => $value) {
                    $setterMethodName = camelcase('set_' . $key);

                    if (method_exists($this->instance, $setterMethodName)) {
                        $this->instance->{$setterMethodName}($value);
                    } else {
                        $this->instance->{$key} = $value;
                    }
                }
            }
        }

        return $this->instance;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::getRequestSegments
     *
     * @return \O2System\Kernel\Http\Message\Uri\Segments
     */
    public function getRequestSegments()
    {
        if (empty($this->requestSegments)) {
            $segments[] = $this->getParameter();

            if ( ! in_array($this->getRequestMethod(), ['index', 'route'])) {
                array_push($segments, $this->getRequestMethod());
            }

            $this->setRequestSegments($segments);
        }

        return $this->requestSegments;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::setRequestSegments
     *
     * @param array $segments
     *
     * @return static
     */
    public function setRequestSegments(array $segments)
    {
        $this->requestSegments = new Segments($segments);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::getParameter
     *
     * @return string
     */
    public function getParameter()
    {
        return dash(get_class_name($this->name));
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::getRequestMethod
     *
     * @return string|null
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::setRequestMethod
     *
     * @param string $method
     *
     * @return static
     */
    public function setRequestMethod($method)
    {
        $this->requestMethod = $method;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::getRequestMethodArgs
     *
     * @return array
     */
    public function getRequestMethodArgs()
    {
        return $this->requestMethodArgs;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::setRequestMethodArgs
     *
     * @param array $arguments
     *
     * @return static
     */
    public function setRequestMethodArgs(array $arguments)
    {
        $arguments = array_values($arguments);
        array_unshift($arguments, null);
        unset($arguments[ 0 ]);

        $this->requestMethodArgs = $arguments;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::isValid
     *
     * @return bool
     */
    public function isValid()
    {
        if ( ! empty($this->name) and $this->hasMethod('__call') and $this->isSubclassOf('\O2System\Kernel\Http\Controller')) {
            return true;
        }

        return false;
    }
}