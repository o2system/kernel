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

namespace O2System\Kernel\Containers;

// ------------------------------------------------------------------------

use O2System\Spl\Containers\DataStructures\SplServiceRegistry;
use O2System\Spl\Containers\SplServiceContainer;

/**
 * Class Services
 *
 * @package O2System\Framework
 */
class Services extends SplServiceContainer
{
    /**
     * Services::load
     *
     * @param string      $className
     * @param string|null $offset
     */
    public function load($className, $offset = null)
    {
        if (is_string($className)) {
            $className = str_replace([
                'O2System\Framework\\',
                'O2System\Reactor\\',
                'O2System\Kernel\\',
                'App\\',
            ], '',
                ltrim($className, '\\')
            );

            if (class_exists($className)) {
                $service = new SplServiceRegistry($className);
            } else {
                if (is_object(kernel()->modules)) {
                    if ($module = kernel()->modules->top()) {
                        if (class_exists($serviceClassName = $module->getNamespace() . $className)) {
                            $service = new SplServiceRegistry($serviceClassName);
                        }
                    }
                }

                if (empty($service)) {
                    if (class_exists($serviceClassName = 'App\\' . $className)) {
                        $service = new SplServiceRegistry($serviceClassName);
                    } elseif (class_exists($serviceClassName = 'O2System\Framework\\' . $className)) {
                        $service = new SplServiceRegistry($serviceClassName);
                    } elseif (class_exists($serviceClassName = 'O2System\Reactor\\' . $className)) {
                        $service = new SplServiceRegistry($serviceClassName);
                    } elseif (class_exists($serviceClassName = 'O2System\Kernel\\' . $className)) {
                        $service = new SplServiceRegistry($serviceClassName);
                    } elseif(class_exists($className)) {
                        $service = new SplServiceRegistry($serviceClassName);
                    }
                }
            }
        }

        if (isset($service)) {
            if ($service instanceof SplServiceRegistry) {
                if (profiler() !== false) {
                    profiler()->watch('Load New Service: ' . $service->getClassName());
                }

                $this->register($service, $offset);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Services::register
     *
     * @param SplServiceRegistry $service
     * @param string|null        $offset
     */
    public function register(SplServiceRegistry $service, $offset = null)
    {
        if ($service instanceof SplServiceRegistry) {
            $offset = isset($offset)
                ? $offset
                : camelcase($service->getParameter());

            $this->attach($offset, $service);

            if (profiler() !== false) {
                profiler()->watch('Register New Service: ' . $service->getClassName());
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Services::add
     *
     * @param object      $service
     * @param string|null $offset
     */
    public function add($service, $offset = null)
    {
        if (is_object($service)) {
            if ( ! $service instanceof SplServiceRegistry) {
                $service = new SplServiceRegistry($service);
            }
        }

        if (profiler() !== false) {
            profiler()->watch('Add New Service: ' . $service->getClassName());
        }

        $this->register($service, $offset);
    }
}