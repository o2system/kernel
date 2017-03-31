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

namespace O2System\Kernel\Cli\Abstracts;

// ------------------------------------------------------------------------

use O2System\Kernel\Cli\Writers\Format;
use O2System\Kernel\Cli\Writers\Formatter;
use O2System\Kernel\Cli\Writers\Table;

/**
 * Class AbstractCommand
 *
 * @package O2System\Cli\Abstracts
 */
abstract class AbstractCommand
{
    /**
     * AbstractCommand::$version
     *
     * Command version.
     *
     * @var string
     */
    protected $version;

    /**
     * AbstractCommand::$description
     *
     * Command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Abstract::$options
     *
     * Command options.
     *
     * @var array
     */
    protected $options = [];

    protected $optionsShortcuts = [];

    protected $verbose = false;

    final public function __construct()
    {
        language()->loadFile( 'cli' );

        foreach ( $this->options as $optionCaller => $optionProps ) {
            $shortcut = empty( $optionProps[ 'shortcut' ] )
                ? '-' . substr( $optionCaller, 0, 1 )
                : '-' . rtrim( $optionProps[ 'shortcut' ] );

            if ( array_key_exists( $shortcut, $this->optionsShortcuts ) ) {
                $shortcut = '-' . substr( $optionCaller, 0, 2 );
            }

            $this->options[ $optionCaller ][ 'shortcut' ] = $shortcut;

            $this->optionsShortcuts[ $shortcut ] = $optionCaller;
        }
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description )
    {
        $this->description = trim( $description );
    }

    public function setOptions( array $options )
    {
        foreach ( $options as $caller => $props ) {
            call_user_func_array( [ &$this, 'addOption' ], $props );
        }
    }

    public function addOption( $caller, $description, $shortcut = null )
    {
        $shortcut = empty( $shortcut )
            ? '-' . substr( $caller, 0, 1 )
            : '-' . rtrim( $shortcut );

        $this->options[ $caller ] = [
            'shortcut'    => $shortcut,
            'description' => $description,
        ];
    }

    final public function optionVersion()
    {
        if ( property_exists( $this, 'version' ) ) {
            if ( ! empty( $this->version ) ) {
                // Show Name & Version Line
                output()->writeln( PHP_EOL . ' v' . $this->version );
            }
        }
    }

    final public function optionVerbose()
    {
        $this->verbose = true;
    }

    final public function callExecute()
    {
        $command = new \ReflectionClass( $this );

        foreach ( $_GET as $method => $arguments ) {

            $method = camelcase( 'option-' . $method );

            if ( $command->hasMethod( $method ) ) {

                if ( is_bool( $arguments ) ) {
                    call_user_func_array( [ &$this, $method ], [ $arguments ] );
                } else {

                    $arguments = is_array( $arguments )
                        ? $arguments
                        : [ $arguments ];

                    if ( call_user_func_array( [ &$this, $method ], $arguments ) === false ) {
                        exit( EXIT_ERROR );
                        break;
                    }
                }
            }
        }

        $this->execute();
    }

    public function execute()
    {
        $this->optionHelp();
    }

    final public function optionHelp()
    {
        print_out( language() );
        // Show Commands
        output()->writeln( PHP_EOL . language()->getLine( 'CLI_COMMAND' ) . ':' );

        $table = new Table();
        $table->isShowBorder = false;

        $table
            ->addRow()
            ->addColumn( $this->caller )
            ->addColumn( $this->description );

        output()->write( ( new Format() )->setString( $table->render() ) );

        // Show Options
        output()->writeln( language()->getLine( 'CLI_OPTIONS' ) . ':' );

        $table = new Table();
        $table->isShowBorder = false;

        foreach ( $this->options as $optionCaller => $optionProps ) {
            $table
                ->addRow()
                ->addColumn( '--' . $optionCaller )
                ->addColumn( $optionProps[ 'shortcut' ] )
                ->addColumn( $optionProps[ 'description' ] );
        }

        output()->write( ( new Format() )->setString( $table->render() ) );
    }
}