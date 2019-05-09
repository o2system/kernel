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

namespace O2System;

// ------------------------------------------------------------------------

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
class Kernel extends Spl\Patterns\Creational\Singleton\AbstractSingleton
{
    /**
     * Kernel Services
     *
     * @var Kernel\Containers\Services
     */
    public $services;

    // ------------------------------------------------------------------------

    /**
     * Kernel::__construct
     */
    protected function __construct()
    {
        parent::__construct();

        $this->services = new Kernel\Containers\Services();

        if (isset($_ENV[ 'DEBUG_STAGE' ]) and $_ENV[ 'DEBUG_STAGE' ] === 'DEVELOPER') {
            if(class_exists('\O2System\Gear\Profiler')) {
                $this->services->load(Gear\Profiler::class);
            }

            if (profiler() !== false) {
                profiler()->watch('Starting Kernel Services');
            }
        }

        $services = [
            'Services\Language' => 'language'
        ];

        foreach ($services as $className => $classOffset) {
            $this->services->load($className, $classOffset);
        }

        if (class_exists('O2System\Framework', false) or class_exists(
            'O2System\Reactor', false)) {
            if (profiler() !== false) {
                profiler()->watch('Starting Kernel I/O Service');
            }
            
            if (is_cli()) {
                $this->cliIO();
            } else {
                $this->httpIO();
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::cliIO
     *
     * Runs command line input/output services.
     */
    private function cliIO()
    {
        $services = [
            'Cli\Input'  => 'input',
            'Cli\Output' => 'output',
        ];

        foreach ($services as $className => $classOffset) {
            $this->services->load($className, $classOffset);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::httpIO
     *
     * Runs http input/output services.
     */
    private function httpIO()
    {
        $services = [
            'Http\Message\ServerRequest' => 'serverRequest',
            'Http\Input'                 => 'input',
            'Http\Output'                => 'output',
        ];

        foreach ($services as $className => $classOffset) {
            $this->services->load($className, $classOffset);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::__isset
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        return (bool)isset($this->{$property});
    }

    // ------------------------------------------------------------------------

    /**
     * Kernel::__get
     *
     * @param string $property
     *
     * @return mixed
     */
    public function &__get($property)
    {
        $get[ $property ] = null;

        if (isset($this->{$property})) {
            $get[ $property ] =& $this->{$property};
        } elseif ($this->services->has($property)) {
            $get[ $property ] = $this->services->get($property);
        }

        return $get[ $property ];
    }
}