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

/**
 * Class AbstractCommandPool
 *
 * @package O2System\Kernel\Cli\Abstracts
 */
abstract class AbstractCommandPool
{
    /**
     * App::$commands
     *
     * Array of application commands line interface.
     *
     * @var array
     */
    protected $commands = [];

    // ------------------------------------------------------------------------

    /**
     * App::hasCommand
     *
     * Check whether the application has a command that you're looking for.
     *
     * @param string $command Command array offset key.
     *
     * @return bool
     */
    public function hasCommand( $command )
    {
        return (bool)array_key_exists( $command, $this->commands );
    }

    // ------------------------------------------------------------------------

    public function loadCommands( $namespace, $commandsPath )
    {
        if ( is_dir( $commandsPath ) ) {
            $namespace = $namespace . rtrim( '\\', $namespace ) . '\\';
            $commandsPath = rtrim( $commandsPath, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

            foreach ( glob( $commandsPath . '*.php' ) as $filePath ) {
                if ( is_file( $filePath ) ) {
                    $commandClassName = $namespace . pathinfo( $filePath, PATHINFO_FILENAME );
                    $this->addCommand( new $commandClassName );
                }
            }
        }
    }

    /**
     * App::registerCommand
     *
     * @param AbstractCommand $command
     */
    public function addCommand( AbstractCommand $command )
    {
        $this->commands[ $command->getCaller() ] = $command;
    }
}