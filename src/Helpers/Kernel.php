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

if ( ! function_exists('kernel')) {
    /**
     * kernel
     *
     * Convenient shortcut for O2System Kernel Instance
     *
     * @return O2System\Kernel
     */
    function kernel()
    {
        if (class_exists('O2System\Framework', false)) {
            return O2System\Framework::getInstance();
        } elseif (class_exists('O2System\Reactor', false)) {
            return O2System\Reactor::getInstance();
        }

        return O2System\Kernel::getInstance();
    }
}

// ------------------------------------------------------------------------


if ( ! function_exists('services')) {
    /**
     * services
     *
     * Convenient shortcut for O2System Framework Services container.
     *
     * @return mixed|\O2System\Kernel\Containers\Services
     */
    function services()
    {
        $args = func_get_args();

        if (count($args)) {
            if (kernel()->services->has($args[ 0 ])) {
                if (isset($args[ 1 ]) and is_array($args[ 1 ])) {
                    return kernel()->services->get($args[ 0 ], $args[ 1 ]);
                }

                return kernel()->services->get($args[ 0 ]);
            }

            return false;
        }

        return kernel()->services;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('profiler')) {
    /**
     * profiler
     *
     * Convenient shortcut for O2System Gear Profiler service.
     *
     * @return bool|O2System\Gear\Profiler
     */
    function profiler()
    {
        if (services()->has('profiler')) {
            return services('profiler');
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('language')) {
    /**
     * language
     *
     * Convenient shortcut for O2System Kernel Language service.
     *
     * @return O2System\Kernel\Services\Language|O2System\Framework\Services\Language
     */
    function language()
    {
        $args = func_get_args();

        if (count($args)) {
            if (services()->has('language')) {
                $language =& services()->get('language');

                return call_user_func_array([&$language, 'getLine'], $args);
            }

            return false;
        }

        return services('language');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('logger')) {
    /**
     * logger
     *
     * Convenient shortcut for O2System Kernel Logger service.
     *
     * @return O2System\Kernel\Services\Logger
     */
    function logger()
    {
        $args = func_get_args();

        if (count($args)) {
            if (services()->has('logger')) {
                $logger =& services('logger');

                return call_user_func_array([&$logger, 'log'], $args);
            }

            return false;
        }

        return services('logger');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('shutdown')) {
    /**
     * shutdown
     *
     * Convenient shortcut for O2System Kernel Shutdown service.
     *
     * @return bool|O2System\Kernel\Services\Shutdown
     */
    function shutdown()
    {
        if (services()->has('shutdown')) {
            return services('shutdown');
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('input')) {
    /**
     * input
     *
     * Convenient shortcut for O2System Kernel Input Instance
     *
     * @return bool|O2System\Kernel\Http\Input|O2System\Kernel\Cli\Input
     */
    function input()
    {
        if (services()->has('input')) {
            return services('input');
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('output')) {
    /**
     * output
     *
     * Convenient shortcut for O2System Kernel Browser Instance
     *
     * @return bool|O2System\Kernel\Http\Output|O2System\Kernel\Cli\Output
     */
    function output()
    {
        if (services()->has('output')) {
            return services('output');
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('server_request')) {
    /**
     * server_request
     *
     * Convenient shortcut for O2System Kernel Http Message Request service.
     *
     * @return O2System\Kernel\Http\Message\Request
     */
    function server_request()
    {
        if (services()->has('serverRequest') === false) {
            services()->load(new \O2System\Kernel\Http\Message\ServerRequest(), 'serverRequest');
        }

        return services('serverRequest');
    }
}