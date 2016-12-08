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

/*
 *---------------------------------------------------------------
 * KERNEL PATH
 *---------------------------------------------------------------
 *
 * RealPath to application folder.
 *
 * WITH TRAILING SLASH!
 */
if ( ! defined( 'PATH_KERNEL' ) ) {
    define( 'PATH_KERNEL', __DIR__ . DIRECTORY_SEPARATOR );
}

require_once 'Helpers/Kernel.php';

/**
 * Class Kernel
 *
 * @package O2System
 */
class Kernel extends Psr\Patterns\AbstractSingletonPattern
{
    /**
     * Kernel Services
     *
     * @var array
     */
    private $services = [ ];

    /**
     * Kernel::__construct
     */
    protected function __construct ()
    {
        parent::__construct();

        $this->addService( new Gear\Profiler() );

        $this->getService( 'profiler' )->watch( 'INSTANTIATE_KERNEL_SERVICES' );

        foreach ( [ 'Language', 'Logger', 'Shutdown' ] as $serviceClassName ) {
            if ( class_exists( 'O2System\Framework', false ) ) {
                if ( class_exists( 'App\Kernel\Services\\' . $serviceClassName ) ) {
                    $this->addService( new Kernel\Registries\Service( 'App\Kernel\Services\\' . $serviceClassName ) );
                } elseif ( class_exists( 'O2System\Framework\Services\\' . $serviceClassName ) ) {
                    $this->addService(
                        new Kernel\Registries\Service( 'O2System\Framework\Services\\' . $serviceClassName )
                    );
                } elseif ( class_exists( 'O2System\Kernel\Services\\' . $serviceClassName ) ) {
                    $this->addService(
                        new Kernel\Registries\Service( 'O2System\Kernel\Services\\' . $serviceClassName )
                    );
                }
            } elseif ( class_exists( 'O2System\Kernel\Services\\' . $serviceClassName ) ) {
                $this->addService( new Kernel\Registries\Service( 'O2System\Kernel\Services\\' . $serviceClassName ) );
            }
        }

        $this->getService( 'profiler' )->watch( 'INSTANTIATE_KERNEL_I/O_SERVICE' );

        if ( is_cli() ) {
            $this->cliIO();
        } else {
            $this->httpIO();
        }
    }

    // ------------------------------------------------------------------------

    public function addService ( $service, $offset = null )
    {
        if ( is_object( $service ) ) {
            if ( ! $service instanceof Kernel\Registries\Service ) {
                $service = new Kernel\Registries\Service( $service );
            }
        } elseif ( is_string( $service ) ) {
            if ( strpos( $service, 'O2System\Framework\\' ) !== false ) {
                $serviceExtends = str_replace( 'O2System\Framework\\', 'App\\', $service );

                if ( class_exists( $serviceExtends ) ) {
                    $service = $serviceExtends;
                }
            }

            if ( class_exists( $service ) ) {
                $service = new Kernel\Registries\Service( $service );
            }
        }

        if ( $service instanceof Kernel\Registries\Service ) {
            $offset = isset( $offset )
                ? $offset
                : $service->getClassParameter();
            $this->services[ $offset ] = $service;
        }
    }

    public function &getService ( $offset, $getInstance = true )
    {
        $getService[ $offset ] = false;

        if ( $this->hasService( $offset ) ) {
            if ( $getInstance ) {
                return $this->services[ $offset ]->getInstance();
            }

            return $this->services[ $offset ];
        }

        return $getService[ $offset ];
    }

    public function hasService ( $offset )
    {
        return (bool) array_key_exists( $offset, $this->services );
    }

    private function cliIO ()
    {
        // Instantiate Kernel Cli Input
        $this->addService( new Kernel\Cli\Input() );

        // Instantiate Kernel Cli Output
        $this->addService( new Kernel\Cli\Output() );
    }

    private function httpIO ()
    {
        // Instantiate Kernel Http Input
        $this->addService( new Kernel\Http\Input() );

        // Instantiate Kernel Http Output
        $this->addService( new Kernel\Http\Output() );
    }

    public function loadService ( $service, $offset = null )
    {
        if ( class_exists( $service ) ) {
            $service = new Kernel\Registries\Service( $service );
            $offset = isset( $offset )
                ? $offset
                : $service->getClassParameter();
            $this->services[ $offset ] = $service;
        }
    }
}