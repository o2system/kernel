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

namespace O2System\Kernel\Cli\Abstracts;

// ------------------------------------------------------------------------

/**
 * Class AbstractCommandPool
 *
 * @package O2System\Kernel\Cli\Abstracts
 */
abstract class AbstractCommandersPool
{
    /**
     * AbstractCommandPool::$commandsNamespace
     *
     * Commanders namespace.
     *
     * @var string
     */
    protected $commandersNamespace;

    /**
     * AbstractCommandPool::$commandPath
     *
     * Commanders path.
     *
     * @var string
     */
    protected $commandersPath;

    /**
     * AbstractCommandPool::$commandersPool
     *
     * Commanders pool.
     *
     * @var array
     */
    protected $commandersPool = [];

    // ------------------------------------------------------------------------

    /**
     * AbstractCommandPool::setCommandNamespace
     *
     * Sets command namespace.
     *
     * @param string $namespace
     *
     * @return static
     */
    public function setCommandersNamespace($namespace)
    {
        $this->commandersNamespace = '\\' . trim($namespace, '\\') . '\\';

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommandPool::setCommandsPath
     *
     * Sets command namespace.
     *
     * @param string $path
     *
     * @return static
     */
    public function setCommandersPath($path)
    {
        $path = str_replace(['\\' . '/'], DIRECTORY_SEPARATOR, $path);
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (is_dir($path)) {
            $this->commandersPath = $path;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommandPool::hasCommand
     *
     * Check whether the application has a command that you're looking for.
     *
     * @param string $command Command array offset key.
     *
     * @return bool
     */
    public function hasCommand($command)
    {
        return (bool)array_key_exists($command, $this->commandersPool);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommandPool::loadCommands
     *
     * Load all commands.
     */
    protected function loadCommanders()
    {
        if (isset($this->commandersPath) and isset($this->commandersNamespace)) {

            foreach (glob($this->commandersPath . '*.php') as $filePath) {
                if (is_file($filePath)) {
                    $commandClassName = $this->commandersNamespace . pathinfo($filePath, PATHINFO_FILENAME);
                    $this->addCommander(new $commandClassName);
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommandPool::addCommander
     *
     * Add new commander to the pool.
     *
     * @param AbstractCommander $commander
     */
    public function addCommander(AbstractCommander $commander)
    {
        $this->commandersPool[ $commander->getCommandName() ] = $commander;
    }
}