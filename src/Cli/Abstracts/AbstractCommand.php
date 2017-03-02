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

use O2System\Kernel\Cli\Writers\Formatter;
use O2System\Kernel\Cli\Writers\Table;

/**
 * Class AbstractCommand
 *
 * @package O2System\Cli\Abstracts
 */
abstract class AbstractCommand
{
    protected $version;

    /**
     * AbstractCommand::$command
     *
     * Command name.
     *
     * @var string
     */
    protected $caller;

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
    protected $options          = [ ];

    protected $optionsShortcuts = [ ];

    protected $verbose          = false;

    final public function __construct ()
    {
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

    public function setCaller ( $caller )
    {
        $this->caller = underscore( trim( $caller ) );
    }

    /**
     * AbstractCommand::getCaller
     *
     * Returns command name.
     *
     * @return string
     */
    public function getCaller ()
    {
        return $this->caller;
    }

    public function setDescription ( $description )
    {
        $this->description = trim( $description );
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function setOptions ( array $options )
    {
        foreach ( $options as $caller => $props ) {
            call_user_func_array( [ &$this, 'addOption' ], $props );
        }
    }

    public function addOption ( $caller, $description, $shortcut = null )
    {
        $shortcut = empty( $shortcut )
            ? '-' . substr( $caller, 0, 1 )
            : '-' . rtrim( $shortcut );

        $this->options[ $caller ] = [
            'shortcut'    => $shortcut,
            'description' => $description,
        ];
    }

    final public function optionVersion ()
    {
        if ( property_exists( $this, 'version' ) ) {
            if ( ! empty( $this->version ) ) {
                // Show Name & Version Line
                output()->writeln( PHP_EOL . ' v' . $this->version );
            }
        }
    }

    final public function optionVerbose ()
    {
        $this->verbose = true;
    }

    final public function optionHelp ()
    {
        $formatter = new Formatter();

        // Show Commands
        output()->writeln( PHP_EOL . language()->getLine( 'CLI_COMMAND' ) . ':' );

        $table = new Table();
        $table->hideBorder();

        $table
            ->addRow()
            ->addColumn( $this->caller )
            ->addColumn( $this->description );

        output()->write(
            $formatter
                ->setIndent( 1 )
                ->format( $table->render() . PHP_EOL )
        );

        // Show Options
        output()->writeln( language()->getLine( 'CLI_OPTIONS' ) . ':' );

        $table = new Table();
        $table->hideBorder();

        foreach ( $this->options as $optionCaller => $optionProps ) {
            $table
                ->addRow()
                ->addColumn( '--' . $optionCaller )
                ->addColumn( $optionProps[ 'shortcut' ] )
                ->addColumn( $optionProps[ 'description' ] );
        }

        output()->writeln(
            $formatter
                ->setIndent( 1 )
                ->format( $table->render() )
        );
    }

    final public function callOptions ( array $options )
    {
        $command = new \ReflectionClass( $this );

        foreach ( $options as $method => $arguments ) {

            $method = 'option' . studlycapcase( $method );

            if ( $command->hasMethod( $method ) ) {
                $option = $command->getMethod( $method );

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

    abstract protected function execute ();
}