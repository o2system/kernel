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
     * Router::$uri
     *
     * @var Router\Uri
     */
    protected $uri;
    
    /**
     * Router::$addresses
     *
     * @var Router\Addresses
     */
    protected $addresses;

    // ------------------------------------------------------------------------
    
    public function getUri()
    {
        return $this->uri;
    }

    public function setAddresses(Router\Addresses $addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }
    
    public function getAddresses()
    {
        return $this->addresses;
    }

    // ------------------------------------------------------------------------

    public function parseRequest(Message\Uri $uri = null)
    {
        $this->uri = is_null($uri) ? server_request()->getUri() : $uri;
        $uriSegments = $this->uri->getSegments()->getParts();
        $uriString = $this->uri->getSegments()->getString();

        $this->uri = is_null($uri) ? new Kernel\Http\Message\Uri() : $uri;
        $uriSegments = $this->uri->getSegments()->getParts();
        $uriString = $this->uri->getSegments()->getString();

        if($this->uri->getSegments()->getTotalParts()) {
            if(strpos(end($uriSegments), '.json') !== false) {
                output()->setContentType('application/json');
                $endSegment = str_replace('.json', '', end($uriSegments));
                array_pop($uriSegments);
                array_push($uriSegments, $endSegment);
                $this->uri = $this->uri->withSegments(new Kernel\Http\Message\Uri\Segments($uriSegments));
                $uriString = $this->uri->getSegments()->getString();
            } elseif(strpos(end($uriSegments), '.xml') !== false) {
                output()->setContentType('application/xml');
                $endSegment = str_replace('.xml', '', end($uriSegments));
                array_pop($uriSegments);
                array_push($uriSegments, $endSegment);
                $this->uri = $this->uri->withSegments(new Kernel\Http\Message\Uri\Segments($uriSegments));
                $uriString = $this->uri->getSegments()->getString();
            }
        } else {
            $uriPath = urldecode(
                parse_url($_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH)
            );

            $uriPathParts = explode('public/', $uriPath);
            $uriPath = end($uriPathParts);

            if ($uriPath !== '/') {
                $uriString = $uriPath;
                $uriSegments = array_filter(explode('/', $uriString));

                $this->uri = $this->uri->withSegments(new Kernel\Http\Message\Uri\Segments($uriSegments));
                $uriString = $this->uri->getSegments()->getString();
            }
        }

        if ($this->addresses instanceof Router\Addresses) {
            // Domain routing
            if (null !== ($domain = $this->addresses->getDomain())) {
                if (is_array($domain)) {
                    $uriSegments = array_merge($domain, $uriSegments);
                    $this->uri = $this->uri->withSegments(new Kernel\Http\Message\Uri\Segments($uriSegments));
                    $uriString = $this->uri->getSegments()->getString();
                }
            } elseif (false !== ($subdomain = $this->uri->getSubdomain())) {
                if (is_array($subdomain)) {
                    $uriSegments = array_merge($subdomain, $uriSegments);
                    $this->uri = $this->uri->withSegments(new Kernel\Http\Message\Uri\Segments($uriSegments));
                    $uriString = $this->uri->getSegments()->getString();
                }
            }
        }

        // Try to translate from uri string
        if (false !== ($action = $this->addresses->getTranslation($uriString))) {
            if ( ! $action->isValidHttpMethod(server_request()->getMethod()) && ! $action->isAnyHttpMethod()) {
                output()->sendError(405);
            } else {
                if (false !== ($parseSegments = $action->getParseUriString($uriString))) {
                    $uriSegments = $parseSegments;
                } else {
                    $uriSegments = [];
                }

                $this->uri = $this->uri->withSegments(new Kernel\Http\Message\Uri\Segments($uriSegments));
                $uriString = $this->uri->getSegments()->getString();

                $this->parseAction($action, $uriSegments);
            }
        }

        // Whoops it seems there is no match
        output()->sendError(404);
    }

    // ------------------------------------------------------------------------

    protected function parseAction(Router\Datastructures\Action $action, array $uriSegments = [])
    {
        $closure = $action->getClosure();
        if (empty($closure)) {
            output()->sendError(204);
        }

        if ($closure instanceof Controller) {
            $uriSegments = empty($uriSegments)
                ? $action->getClosureParameters()
                : $uriSegments;
            $this->setController(
                (new Router\Datastructures\Controller($closure))
                    ->setRequestMethod('index'),
                $uriSegments
            );
        } elseif ($closure instanceof Router\Datastructures\Controller) {
            $this->setController($closure, $action->getClosureParameters());
        } elseif (is_array($closure)) {
            $uri = (new Message\Uri())
                ->withSegments(new Message\Uri\Segments(''))
                ->withQuery('');
            $this->parseRequest($this->uri->addSegments($closure));
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

    // ------------------------------------------------------------------------

    protected function setController(
        Router\Datastructures\Controller $controller,
        array $uriSegments = []
    ) {
        if ( ! $controller->isValid()) {
            output()->sendError(400);
        }

        // Add Controller PSR4 Namespace
        loader()->addNamespace($controller->getNamespaceName(), $controller->getFileInfo()->getPath());

        $controllerMethod = $controller->getRequestMethod();
        $controllerMethod = empty($controllerMethod) ? reset($uriSegments) : $controllerMethod;
        $controllerMethod = camelcase($controllerMethod);

        // Set default controller method to index
        if ( ! $controller->hasMethod($controllerMethod) &&
            ! $controller->hasMethod('route')
        ) {
            $controllerMethod = 'index';
        }

        // has route method, controller method set to index as default
        if (empty($controllerMethod)) {
            $controllerMethod = 'index';
        }

        if (camelcase(reset($uriSegments)) === $controllerMethod) {
            array_shift($uriSegments);
        }

        $controllerMethodParams = $uriSegments;

        if ($controller->hasMethod('route')) {
            $controller->setRequestMethod('route');
            $controller->setRequestMethodArgs([
                $controllerMethod,
                $controllerMethodParams,
            ]);
        } elseif ($controller->hasMethod($controllerMethod)) {
            $method = $controller->getMethod($controllerMethod);

            // Method doesn't need any parameters
            if ($method->getNumberOfParameters() == 0) {
                // But there is parameters requested
                if (count($controllerMethodParams)) {
                    output()->sendError(404);
                } else {
                    $controller->setRequestMethod($controllerMethod);
                }
            } else {
                $parameters = [];

                if (count($controllerMethodParams)) {
                    if (is_numeric(key($controllerMethodParams))) {
                        $parameters = $controllerMethodParams;
                    } else {
                        foreach ($method->getParameters() as $index => $parameter) {
                            if (isset($uriSegments[ $parameter->name ])) {
                                $parameters[ $index ] = $controllerMethodParams[ $parameter->name ];
                            } else {
                                $parameters[ $index ] = null;
                            }
                        }
                    }
                }

                $controller->setRequestMethod($controllerMethod);
                $controller->setRequestMethodArgs($parameters);
            }
        }

        // Set Controller
        services()->add($controller, 'controller');
    }
}