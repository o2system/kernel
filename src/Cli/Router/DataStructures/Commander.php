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

namespace O2System\Kernel\Cli\Router\DataStructures;

// ------------------------------------------------------------------------

use O2System\Spl\Info\SplClassInfo;

/**
 * Class Commander
 *
 * @package O2System\DataStructures
 */
class Commander extends SplClassInfo
{
    /**
     * Commander::$requestMethod
     *
     * @var string|null
     */
    private $requestMethod = null;

    /**
     * Commander::$requestMethodArgs
     *
     * @var array
     */
    private $requestMethodArgs = [];

    /**
     * Commander::$properties
     *
     * @var array
     */
    private $properties = [];

    /**
     * Commander::$instance
     *
     * @var \O2System\Kernel\Cli\Commander
     */
    private $instance;

    // ------------------------------------------------------------------------

    /**
     * Commander::__construct
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        if (is_object($filePath)) {
            if ($filePath instanceof \O2System\Kernel\Cli\Commander) {
                parent::__construct($filePath);
                $this->instance = $filePath;
            }
        } elseif (is_string($filePath) && is_file($filePath)) {
            $className = prepare_class_name(pathinfo($filePath, PATHINFO_FILENAME));
            @list($namespaceDirectory, $subNamespace) = explode('Commanders', dirname($filePath));
            $classNamespace = loader()->getDirNamespace(
                    $namespaceDirectory
                ) . 'Commanders' . (empty($subNamespace) ? null : str_replace('/', '\\', $subNamespace)) . '\\';
            $className = $classNamespace . $className;

            if (class_exists('\O2System\Kernel\Cli\\' . $className)) {
                parent::__construct('\O2System\Kernel\Cli\\' . $className);
            } elseif (class_exists('\O2System\Framework\Cli\\' . $className)) {
                parent::__construct('\O2System\Framework\Cli\\' . $className);
            } elseif (class_exists('\O2System\Reactor\Cli\\' . $className)) {
                parent::__construct('\O2System\Reactor\Cli\\' . $className);
            } elseif (class_exists('\App\Cli\\' . $className)) {
                parent::__construct('\App\Cli\\' . $className);
            } elseif (class_exists('\App\\' . $className)) {
                parent::__construct('\App\\' . $className);
            } elseif (class_exists($className)) {
                parent::__construct($className);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Commander::setProperties
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    // ------------------------------------------------------------------------

    /**
     * Commander::getParameter
     *
     * @return string
     */
    public function getParameter()
    {
        return strtolower(get_class_name($this->name));
    }

    // ------------------------------------------------------------------------

    /**
     * Commander::getInstance
     *
     * @return \O2System\Kernel\Cli\Commander|string
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
     * Commander::getRequestMethod
     *
     * @return string|null
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    // ------------------------------------------------------------------------

    /**
     * Commander::setRequestMethod
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
     * Commander::getRequestMethodArgs
     *
     * @return array
     */
    public function getRequestMethodArgs()
    {
        return $this->requestMethodArgs;
    }

    // ------------------------------------------------------------------------

    /**
     * Commander::setRequestMethodArgs
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
}