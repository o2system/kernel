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

namespace O2System\Kernel\Containers;

// ------------------------------------------------------------------------

use O2System\Spl\Containers\Datastructures\SplServiceRegistry;
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
     * @param string        $service
     * @param string|null   $offset
     */
    public function load($service, $offset = null)
    {
        if (is_string($service)) {
            if(class_exists('O2System\Framework', false)) {
                $className = str_replace(
                    ['App\\','App\Kernel\\','O2System\Framework\\','O2System\Kernel\\'],
                    '',
                    $service
                );

                foreach(['App\\','App\Kernel\\','O2System\Framework\\','O2System\Kernel\\'] as $namespace) {
                    if (class_exists($namespace . $className)) {
                        $service = $namespace . $className;
                        break;
                    }
                }
            }

            if (class_exists($service)) {
                $service = new SplServiceRegistry($service);
            }
        }

        if($service instanceof SplServiceRegistry) {
            if (profiler() !== false) {
                profiler()->watch('Load New Service: ' . $service->getClassName());
            }

            $this->register($service, $offset);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Services::add
     *
     * @param object        $service
     * @param string|null   $offset
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
}