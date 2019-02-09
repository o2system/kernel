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

/*
| -------------------------------------------------------------------
| ERROR CODES
| -------------------------------------------------------------------
| This file contains an array of error codes.
*/

return [
    // 1xx: Information
    100 => 'CONTINUE',
    101 => 'SWITCHING_PROTOCOLS',
    103 => 'CHECKPOINT',

    // 2xx: Successful
    200 => 'OK',
    201 => 'CREATED',
    202 => 'ACCEPTED',
    203 => 'NON_AUTHORITATIVE_INFORMATION',
    204 => 'NO_CONTENT',
    205 => 'RESET_CONTENT',
    206 => 'PARTIAL_CONTENT',

    // 3xx: Redirection
    300 => 'MULTIPLE_CHOICES',
    301 => 'MOVED_PERMANENTLY',
    302 => 'FOUND',
    303 => 'NOT_MODIFIED',
    306 => 'SWITCH_PROXY',
    307 => 'TEMPORARY_REDIRECT',
    308 => 'RESUME_INCOMPLETE',

    // 4xx: Client Error
    400 => 'BAD_REQUEST',
    401 => 'UNAUTHORIZED',
    402 => 'PAYMENT_REQUIRED',
    403 => 'FORBIDDEN',
    404 => 'NOT_FOUND',
    405 => 'METHOD_NOT_ALLOWED',
    406 => 'NOT_ACCEPTABLE',
    407 => 'PROXY_AUTHENTICATION_REQUIRED',
    408 => 'REQUEST_TIMEOUT',
    409 => 'CONFLICT',
    410 => 'GONE',
    411 => 'LENGTH_REQUIRED',
    412 => 'PRECONDITION_FAILED',
    413 => 'REQUEST_ENTITY_TOO_LARGE',
    414 => 'REQUEST_URI_TOO_LONG',
    415 => 'UNSUPPORTED_MEDIA_TYPE',
    416 => 'REQUESTED_RANGE_NOT_SATISFIABLE',
    417 => 'EXPECTATION_FAILED',

    // 5xx: Server Error
    500 => 'INTERNAL_SERVER_ERROR',
    501 => 'NOT_IMPLEMENTED',
    502 => 'BAD_GATEWAY',
    503 => 'SERVICE_UNAVAILABLE',
    504 => 'GATEWAY_TIMEOUT',
    505 => 'HTTP_VERSION_NOT_SUPPORTED',
    511 => 'NETWORK_AUTHENTICATION_REQUIRED',
];