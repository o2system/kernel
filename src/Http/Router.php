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

/**
 * Class Router
 * @package O2System\Kernel\Http
 */
class Router
{
    /**
     * Router::$addresses
     *
     * @var Router\Addresses
     */
    protected $addresses;

    // ------------------------------------------------------------------------

    public function setAddresses( Router\Addresses $addresses ) {
        $this->addresses = $addresses;

        return $this;
    }

    // ------------------------------------------------------------------------

    public function parseRequest( Message\Uri $uri = null )
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

        if ($this->addresses instanceof Router\Addresses) {
            // Domain routing
            if (null !== ($domain = $this->addresses->getDomain())) {
                if (is_array($domain)) {
                    $uriSegments = array_merge($domain, $uriSegments);
                    $uriString = implode('/', array_map('dash', $uriSegments));
                }
            } elseif (false !== ($subdomain = $uri->getSubdomain())) {
                if (is_array($subdomain)) {
                    $uriSegments = array_merge($subdomain, $uriSegments);
                    $uriString = implode('/', array_map('dash', $uriSegments));
                }
            }
        }

        // Try to translate from uri string
        if (false !== ($action = $this->addresses->getTranslation($uriString))) {
            if ( ! $action->isValidHttpMethod(request()->getMethod()) && ! $action->isAnyHttpMethod()) {
                output()->sendError(405);
            } else {
                if (false !== ($parseSegments = $action->getParseUriString($uriString))) {
                    $uriSegments = $parseSegments;
                } else {
                    $uriSegments = [];
                }

                $this->parseAction($action, $uriSegments);
            }
        }

        // Whoops it seems there is no match
        output()->sendError(404);
    }

    // ------------------------------------------------------------------------

    protected function parseAction(Router\Datastructures\Action $action, array $uriSegments = [])
    {
        ob_start();
        $closure = $action->getClosure();
        if (empty($closure)) {
            $closure = ob_get_contents();
        }
        ob_end_clean();

        if ($closure instanceof Controller) {
            $uriSegments = empty($uriSegments)
                ? $action->getClosureParameters()
                : $uriSegments;
            $this->setController(
                (new Router\Datastructures\Controller($closure))
                    ->setRequestMethod('index'),
                $uriSegments
            );
        } elseif ($closure instanceof Controller) {
            $this->setController($closure, $action->getClosureParameters());
        } elseif (is_array($closure)) {
            $uri = (new \O2System\Kernel\Http\Message\Uri())
                ->withSegments(new \O2System\Kernel\Http\Message\Uri\Segments(''))
                ->withQuery('');
            $this->parseRequest($uri->addSegments($closure));
        } else {
            if (class_exists($closure)) {
                $this->setController(
                    (new Router\Datastructures\Controller($closure))
                        ->setRequestMethod('index'),
                    $uriSegments
                );
            } elseif (preg_match("/([a-zA-Z0-9\\\]+)(@)([a-zA-Z0-9\\\]+)/", $closure, $matches)) {
                $this->setController(
                    (new Router\Datastructures\Controller($matches[ 1 ]))
                        ->setRequestMethod($matches[ 3 ]),
                    $uriSegments
                );
            } elseif (presenter()->theme->use === true) {
                if ( ! presenter()->partials->offsetExists('content') && $closure !== '') {
                    presenter()->partials->offsetSet('content', $closure);
                }

                if (presenter()->partials->offsetExists('content')) {
                    profiler()->watch('VIEW_SERVICE_RENDER');
                    view()->render();
                    exit(EXIT_SUCCESS);
                } else {
                    output()->sendError(204);
                    exit(EXIT_ERROR);
                }
            } elseif (is_string($closure) && $closure !== '') {
                if (is_json($closure)) {
                    output()->setContentType('application/json');
                    output()->send($closure);
                } else {
                    output()->send($closure);
                }
            } elseif (is_array($closure) || is_object($closure)) {
                output()->send($closure);
            } elseif (is_numeric($closure)) {
                output()->sendError($closure);
            } else {
                output()->sendError(204);
                exit(EXIT_ERROR);
            }
        }
    }
}