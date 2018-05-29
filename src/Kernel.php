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

namespace O2System;

// ------------------------------------------------------------------------

use O2System\Psr\Container\ContainerExceptionInterface;
use O2System\Psr\Container\ContainerInterface;
use O2System\Psr\NotFoundExceptionInterface;

/*
 *---------------------------------------------------------------
 * KERNEL PATH
 *---------------------------------------------------------------
 *
 * RealPath to application folder.
 *
 * WITH TRAILING SLASH!
 */
if ( ! defined('PATH_KERNEL')) {
    define('PATH_KERNEL', __DIR__ . DIRECTORY_SEPARATOR);
}

require_once 'Helpers/Kernel.php';

/**
 * Class Kernel
 *
 * @package O2System
 */
class Kernel extends Psr\Patterns\Creational\Singleton\AbstractSingleton implements ContainerInterface
{
    /**
     * Kernel Services
     *
     * @var array
     */
    private $services = [];

    // ------------------------------------------------------------------------

    /**
     * Kernel::__construct
     */
    protected function __construct()
    {
        parent::__construct();

        $this->addService(new Gear\Profiler());

        $this->getService('profiler')->watch('INSTANTIATE_KERNEL_SERVICES');

        foreach (['Language', 'Logger', 'Shutdown'] as $serviceClassName) {
            if (class_exists('O2System\Framework', false)) {
                if (class_exists('App\Kernel\Services\\' . $serviceClassName)) {
                    $this->addService(new Kernel\Datastructures\Service('App\Kernel\Services\\' . $serviceClassName));
                } elseif (class_exists('O2System\Framework\Services\\' . $serviceClassName)) {
                    $this->addService(
                        new Kernel\Datastructures\Service('O2System\Framework\Services\\' . $serviceClassName)
                    );
                } elseif (class_exists('O2System\Kernel\Services\\' . $serviceClassName)) {
                    $this->addService(
                        new Kernel\Datastructures\Service('O2System\Kernel\Services\\' . $serviceClassName)
                    );
                }
            } elseif (class_exists('O2System\Kernel\Services\\' . $serviceClassName)) {
                $this->addService(new Kernel\Datastructures\Service('O2System\Kernel\Services\\' . $serviceClassName));
            }
        }

        $this->getService('profiler')->watch('INSTANTIATE_KERNEL_I/O_SERVICE');

        if (is_cli()) {
            $this->cliIO();
        } else {
            $this->httpIO();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::addService
     *
     * @param object|string $service
     * @param string|null   $offset
     */
    public function addService($service, $offset = null)
    {
        if (is_object($service)) {
            if ( ! $service instanceof Kernel\Datastructures\Service) {
                $service = new Kernel\Datastructures\Service($service);
            }
        } elseif (is_string($service)) {
            if (strpos($service, 'O2System\Framework\\') !== false) {
                $serviceExtends = str_replace('O2System\Framework\\', 'App\\', $service);

                if (class_exists($serviceExtends)) {
                    $service = $serviceExtends;
                }
            }

            if (class_exists($service)) {
                $service = new Kernel\Datastructures\Service($service);
            }
        }

        if ($service instanceof Kernel\Datastructures\Service) {
            $offset = isset($offset)
                ? $offset
                : $service->getClassParameter();
            $this->services[ $offset ] = $service;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::getService
     *
     * @param string $offset
     * @param bool   $getInstance
     *
     * @return mixed
     */
    public function &getService($offset, $getInstance = true)
    {
        $getService[ $offset ] = false;

        if ($this->hasService($offset)) {
            if ($getInstance === true) {
                return $this->services[ $offset ]->getInstance();
            }

            return $this->services[ $offset ];
        }

        return $getService[ $offset ];
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::hasService
     *
     * @param $offset
     *
     * @return bool
     */
    public function hasService($offset)
    {
        return (bool)array_key_exists($offset, $this->services);
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::cliIO
     *
     */
    private function cliIO()
    {
        // Instantiate Kernel Cli Input
        $this->addService(new Kernel\Cli\Input());

        // Instantiate Kernel Cli Browser
        $this->addService(new Kernel\Cli\Output());
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::httpIO
     *
     */
    private function httpIO()
    {
        // Instantiate Kernel Http Input
        $this->addService(new Kernel\Http\Input());

        // Instantiate Kernel Http Browser
        $this->addService(new Kernel\Http\Output());
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::loadService
     *
     * @param string      $service
     * @param string|null $offset
     */
    public function loadService($service, $offset = null)
    {
        if (class_exists($service)) {
            $service = new Kernel\Datastructures\Service($service);
            $offset = isset($offset)
                ? $offset
                : $service->getClassParameter();
            $this->services[ $offset ] = $service;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->getService($id);
        }

        // @todo throw exception
    }

    // ------------------------------------------------------------------------

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return (bool)$this->hasService($id);
    }
}