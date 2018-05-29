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

namespace O2System\Kernel\Datastructures;

// ------------------------------------------------------------------------

use O2System\Spl\Info\SplFileInfo;

/**
 * Class Service
 *
 * @package O2System\Kernel\Datastructures
 */
class Service extends \ReflectionClass
{
    /**
     * Class Name
     *
     * @var string
     */
    public $name;

    /**
     * Class Arguments
     *
     * @var array
     */
    private $arguments;

    /**
     * Class Singleton
     *
     * @var mixed
     */
    private $instance;

    /**
     * SplClassInfo constructor.
     *
     * @param string|object $class     Class Name or Class Object.
     * @param array         $arguments Class Constructor Arguments
     */
    public function __construct($class, array $arguments = [])
    {
        $this->arguments = $arguments;

        if (is_object($class)) {
            $this->instance =& $class;
            parent::__construct($class);
        } else {
            if (class_exists('O2System\Framework', false)) {
                if (strpos($class, 'O2System\\') !== false) {
                    $appClassName = str_replace('O2System\\', 'App\\', $class);

                    if (class_exists($appClassName)) {
                        parent::__construct($class);
                    } elseif (class_exists($class)) {
                        parent::__construct($class);
                    }
                } elseif (class_exists($class)) {
                    parent::__construct($class);
                }
            } elseif (class_exists($class)) {
                parent::__construct($class);
            } else {
                throw new \BadMethodCallException('Class not found!');
            }
        }
    }

    // ------------------------------------------------------------------------

    public function getClassParameter()
    {
        return camelcase($this->getClassName());
    }

    public function getClassName()
    {
        return get_class_name($this->name);
    }

    /**
     * Service::getFileInfo
     *
     * @return \O2System\Spl\Info\SplFileInfo
     */
    public function getFileInfo()
    {
        return new SplFileInfo($this->getFileName());
    }

    public function &getInstance()
    {
        if (empty($this->instance)) {
            if (empty($this->arguments)) {
                $this->instance = $this->newInstance();
            } else {
                $this->instance = $this->newInstance($this->arguments);
            }
        }

        return $this->instance;
    }
}