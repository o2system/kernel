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

use O2System\Kernel\Http\Router\Addresses;
use O2System\Kernel\Http\Router\Datastructures\Action;
use O2System\Kernel\Http\Message\Uri;

/**
 * Class Router
 * @package O2System\Kernel\Http
 */
class Router
{
    /**
     * Router::$addresses
     *
     * @var Addresses
     */
    protected $addresses;

    // ------------------------------------------------------------------------

    public function setAddresses( Addresses $addresses ) {
        $this->addresses = $addresses;

        return $this;
    }

    // ------------------------------------------------------------------------

    public function parseRequest( Uri $uri = null )
    {
        $uri = is_null( $uri ) ? request()->getUri() : $uri;
        $uriSegments = $uri->getSegments()->getParts();
        $uriString = $uri->getSegments()->getString();

        if ( empty( $uriSegments ) ) {
            $uriPath = urldecode(
                parse_url( $_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH )
            );

            $uriPathParts = explode('public/', $uriPath);
            $uriPath = end($uriPathParts);

            if( $uriPath !== '/' ) {
                $uriString = $uriPath;
                $uriSegments = array_filter( explode( '/', $uriString ) );
            }
        }

        // Define default action by app addresses config
        $defaultAction = $this->addresses->getTranslation( '/' );

        // Try to get action from URI String
        if ( false !== ( $action = $this->addresses->getTranslation( $uriString ) ) ) {
            if ( $action->isValidUriString( $uriString ) ) {
                if ( ! $action->isValidHttpMethod( request()->getMethod() ) && ! $action->isAnyHttpMethod() ) {
                    output()->sendError( 405 );
                } else {
                    $this->parseAction($action, $uriSegments);
                    if ( ! empty( o2system()->hasService( 'controller' ) ) ) {
                        return;
                    }
                }
            }
        }

        if ( count( $maps = $this->addresses->getTranslations() ) ) { // Try to parse route from route map
            foreach ( $maps as $map ) {
                if ( $map instanceof Action ) {
                    if ( $map->isValidHttpMethod( request()->getMethod() ) && $map->isValidUriString( $uriString ) ) {
                        if ( $this->parseAction( $map ) !== false ) {
                            return;

                            break;
                        }
                    }
                }
            }
        }

        // try to get default action
        if ( isset( $defaultAction ) ) {
            $this->parseAction( $defaultAction, $uriSegments );
        }

        // Let's the kernel do the rest when there is no controller found
        output()->sendError(404);
    }

    // ------------------------------------------------------------------------

    protected function parseAction( Action $action, array $uriSegments = [] )
    {
        ob_start();
        $closure = $action->getClosure();

        if(is_array($closure)) {
            $uri = ( new Uri() )
                ->withSegments( new Uri\Segments( '' ) )
                ->withQuery( '' );
            $this->parseRequest( $uri->addSegments( $closure ) );
        } else {
            $closure = ob_get_contents();
            ob_end_clean();

            if(! empty($closure)) {
                output()->send($closure);
            }
        }

        output()->sendError( 204 );
    }
}