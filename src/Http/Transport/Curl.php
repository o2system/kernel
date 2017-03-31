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

namespace O2System\Kernel\Http\Transport;

// ------------------------------------------------------------------------

use O2System\Kernel\Http\Abstracts\AbstractTransport;
use O2System\Kernel\Http\Message\Response;
use O2System\Psr\Http\Message\RequestInterface;

class Curl extends AbstractTransport
{
    protected $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
    ];

    public function getResponse( RequestInterface $request )
    {
        $response = new Response;

        return $response;
    }
}