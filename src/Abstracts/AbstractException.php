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

namespace O2System\Kernel\Abstracts;

// ------------------------------------------------------------------------

use O2System\Gear\Trace;

/**
 * Class Exception
 *
 * @package O2System\Kernel
 */
abstract class AbstractException extends \Exception
{
    /**
     * Exception Header
     *
     * @var string
     */
    protected $header;

    /**
     * Exception Description
     *
     * @var string
     */
    protected $description;

    /**
     * Exception View
     *
     * @var string
     */
    protected $view = 'exception';

    // ------------------------------------------------------------------------

    /**
     * Exception::__construct
     *
     * @param string          $message
     * @param int             $code
     * @param array           $context
     * @param \Exception|NULL $previous
     */
    public function __construct ( $message, $code = 0, array $context = [ ], \Exception $previous = null )
    {
        $className = get_called_class();
        $xClassName = explode( '\\', $className );
        $langClassName = end( $xClassName );

        if ( false !== ( $exceptionKey = array_search( 'Exceptions', $xClassName ) ) ) {
            if ( isset( $xClassName[ $exceptionKey - 1 ] ) ) {
                $this->view = strtolower( $xClassName[ $exceptionKey - 1 ] . '_' . $this->view );

                $langClassName = $xClassName[ $exceptionKey - 1 ] . '_' . $langClassName;
                language()->loadFile( $xClassName[ $exceptionKey - 1 ] );
            }
        }

        $this->header = language()->getLine( 'E_HEADER_' . $langClassName );
        $this->description = language()->getLine( 'E_DESCRIPTION_' . $langClassName );
        $message = language()->getLine( $message, $context );

        parent::__construct( $message, $code, $previous );
    }

    public function getHeader ()
    {
        return $this->header;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function getChronology ()
    {
        return ( new Trace( $this->getTrace() ) )->chronology();
    }

    public function getAssetsUrl ( $path = '', $dirname = null )
    {
        $path = is_array( $path ) ? implode( '/', $path ) : $path;

        $dirname = is_bool( $dirname ) ? PATH_KERNEL . '/Views/http' : $dirname;
        $dirname = is_null( $dirname ) ? dirname( $this->getView() ) : $dirname;

        $scriptFilename = str_replace( [ '/', '\\' ], '/', dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) );
        $scriptName = str_replace( [ '/', '\\' ], '/', dirname( $_SERVER[ 'SCRIPT_NAME' ] ) );
        $assetsDirectory = str_replace( [ '/', '\\' ], '/', $dirname );

        if ( strpos( $scriptName, 'public' ) ) {
            $scriptFilename = str_replace( 'public', '', $scriptFilename );
            $scriptName = str_replace( 'public', '', $scriptName );
        }

        return '//' . $_SERVER[ 'HTTP_HOST' ] . $scriptName . str_replace(
            $scriptFilename,
            '',
            $assetsDirectory
        ) . '/assets/' . trim( $path, '/' );
    }

    public function getView ()
    {
        return $this->view;
    }
}