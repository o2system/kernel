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

use O2System\Kernel\Cli\Writers\Format;
use O2System\Kernel\Cli\Writers\Table;

/**
 * Class AbstractCommander
 *
 * @package O2System\Cli\Abstracts
 */
abstract class AbstractCommander
{
    /**
     * AbstractCommander::$commandName
     *
     * Command name.
     *
     * @var string
     */
    protected $commandName;

    /**
     * AbstractCommander::$commandVersion
     *
     * Command version.
     *
     * @var string
     */
    protected $commandVersion;

    /**
     * AbstractCommander::$commandDescription
     *
     * Command description.
     *
     * @var string
     */
    protected $commandDescription;

    /**
     * AbstractCommander::$commandOptions
     *
     * Command options.
     *
     * @var array
     */
    protected $commandOptions = [];

    /**
     * AbstractCommander::$commandOptionsShortcuts
     *
     * Command options.
     *
     * @var array
     */
    protected $commandOptionsShortcuts = [
        '-h'  => 'help',
        '-v'  => 'version',
        '-vv' => 'verbose',
    ];

    protected $actionsPool = [];

    /**
     * AbstractCommander::$verbose
     *
     * Command options.
     *
     * @var bool
     */
    protected $optionVerbose = false;

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::__construct
     *
     * Commander class constructor.
     *
     * @final   This method cannot be overwritten.
     */
    final public function __construct()
    {
        language()->loadFile('cli');

        $className = explode('Commanders\\', get_class($this));
        $className = str_replace('\\', '/', end($className));
        $this->commandName = implode('/', array_map('strtolower', explode('/', $className)));

        foreach ($this->commandOptions as $optionName => $optionConfig) {
            $shortcut = empty($optionConfig[ 'shortcut' ])
                ? '-' . substr($optionName, 0, 1)
                : '-' . rtrim($optionConfig[ 'shortcut' ]);

            if (array_key_exists($shortcut, $this->commandOptionsShortcuts)) {
                $shortcut = '-' . substr($optionName, 0, 2);
            }

            $this->commandOptions[ $optionName ][ 'shortcut' ] = $shortcut;

            $this->commandOptionsShortcuts[ $shortcut ] = $optionName;
        }

        if (array_key_exists('VERBOSE', $_ENV)) {
            $this->optionVerbose = true;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::setCommandOptions
     *
     * Sets command options.
     *
     * @param array $commandOptions Array of commander options.
     *
     * @return static
     */
    public function setCommandOptions(array $commandOptions)
    {
        foreach ($commandOptions as $caller => $props) {
            call_user_func_array([&$this, 'addOption'], $props);
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::addCommandOption
     *
     * Add command option.
     *
     * @param string $optionName
     * @param string $optionDescription
     * @param string $optionShortcut
     */
    public function addCommandOption($optionName, $optionDescription, $optionShortcut = null)
    {
        $optionShortcut = empty($optionShortcut)
            ? '-' . substr($optionName, 0, 1)
            : '-' . rtrim($optionShortcut);

        $this->commandOptions[ $optionName ] = [
            'shortcut'    => $optionShortcut,
            'description' => $optionDescription,
        ];
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::optionVersion
     *
     * Option version method, write commander version string.
     *
     * @return void
     */
    public function optionVersion()
    {
        if (property_exists($this, 'commandVersion')) {
            if ( ! empty($this->commandVersion)) {
                // Show Name & Version Line
                output()->write(
                    (new Format())
                        ->setContextualClass(Format::INFO)
                        ->setString(ucfirst($this->commandName) . ' v' . $this->commandVersion)
                        ->setNewLinesAfter(1)
                );
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::optionVerbose
     *
     * Option verbose method, activate verbose output mode.
     *
     * @return void
     */
    public function optionVerbose()
    {
        $this->optionVerbose = true;
    }

    /**
     * AbstractCommander::__callOptions
     *
     * Options call executer.
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function __callOptions()
    {
        if (false !== ($options = input()->get())) {
            if (count($options)) {
                $command = new \ReflectionClass($this);

                foreach ($options as $method => $arguments) {

                    if (array_key_exists('-' . $method, $this->commandOptionsShortcuts)) {
                        $method = $this->commandOptionsShortcuts[ '-' . $method ];
                    }

                    if ($command->hasMethod($commandMethodName = camelcase('option-' . $method))) {
                        $commandMethod = $command->getMethod($commandMethodName);
                    } elseif ($command->hasMethod($commandMethodName = camelcase($method))) {
                        $commandMethod = $command->getMethod($commandMethodName);
                    }

                    if ($commandMethod instanceof \ReflectionMethod) {
                        if ($commandMethod->getNumberOfRequiredParameters() == 0) {
                            call_user_func([&$this, $commandMethodName]);
                        } elseif ($commandMethod->getNumberOfRequiredParameters() > 0 and empty($arguments)) {
                            if (isset($this->commandOptions[ $method ][ 'help' ])) {
                                output()->write(
                                    (new Format())
                                        ->setContextualClass(Format::INFO)
                                        ->setString(language()->getLine('CLI_USAGE') . ':')
                                        ->setNewLinesBefore(1)
                                        ->setNewLinesAfter(1)
                                );

                                output()->write(
                                    (new Format())
                                        ->setContextualClass(Format::INFO)
                                        ->setString(language()->getLine($this->commandOptions[ $method ][ 'help' ]))
                                        ->setNewLinesAfter(2)
                                );
                            }
                        } else {
                            $optionArguments = is_array($arguments)
                                ? $arguments
                                : [$arguments];

                            call_user_func_array([&$this, $commandMethodName], $optionArguments);
                        }
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::execute
     *
     * Default abstract commander execution to execute help option.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function execute()
    {
        if (false !== ($options = input()->get())) {
            $this->__callOptions();
        } else {
            $this->optionHelp();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::optionHelp
     *
     * Option help method, write commander help.
     *
     * @return void
     * @throws \ReflectionException
     */
    final public function optionHelp()
    {
        // Show Usage
        output()->write(
            (new Format())
                ->setContextualClass(Format::INFO)
                ->setString(language()->getLine('CLI_USAGE') . ':')
                ->setNewLinesBefore(1)
                ->setNewLinesAfter(1)
        );

        // Show Actions
        $this->loadActions();

        if (count($this->actionsPool)) {
            output()->write(
                (new Format())
                    ->setContextualClass(Format::INFO)
                    ->setString($this->commandName . '/action --option=value')
            );

            output()->write(
                (new Format())
                    ->setString(language()->getLine('CLI_ACTIONS') . ':')
                    ->setNewLinesBefore(2)
                    ->setNewLinesAfter(1)
            );

            $table = new Table();
            $table->isShowBorder = false;

            foreach ($this->actionsPool as $action) {

                if ($action instanceof AbstractCommander) {
                    $table
                        ->addRow()
                        ->addColumn($action->getCommandName())
                        ->addColumn(language()->getLine($action->getCommandDescription()));
                }
            }

            output()->write(
                (new Format())
                    ->setString($table->render())
            );
        } else {
            output()->write(
                (new Format())
                    ->setContextualClass(Format::INFO)
                    ->setString($this->commandName . ' --option=value')
            );
        }

        // Show Options
        output()->write(
            (new Format())
                ->setString(language()->getLine('CLI_OPTIONS') . ':')
                ->setNewLinesBefore(2)
                ->setNewLinesAfter(1)
        );

        $table = new Table();
        $table->isShowBorder = false;

        foreach ($this->commandOptions as $optionCaller => $optionProps) {
            $table
                ->addRow()
                ->addColumn('--' . $optionCaller)
                ->addColumn($optionProps[ 'shortcut' ])
                ->addColumn(language()->getLine($optionProps[ 'description' ]));
        }

        output()->write(
            (new Format())
                ->setString($table->render())
                ->setNewLinesAfter(2)
        );

        exit(EXIT_SUCCESS);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::loadActions
     *
     * Load all actions.
     *
     * @throws \ReflectionException
     */
    protected function loadActions()
    {
        $reflection = new \ReflectionClass($this);
        $actionNamespace = $reflection->name . '\\';
        $actionDirectory = get_class_name($reflection->name);
        $actionsPath = dirname($reflection->getFileName()) . DIRECTORY_SEPARATOR . $actionDirectory . DIRECTORY_SEPARATOR;

        foreach (glob($actionsPath . '*.php') as $filePath) {
            if (is_file($filePath)) {
                $commandClassName = $actionNamespace . pathinfo($filePath, PATHINFO_FILENAME);
                $this->addCommander(new $commandClassName);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::addCommander
     *
     * Add new commander to the pool.
     *
     * @param AbstractCommander $commander
     */
    public function addCommander(AbstractCommander $commander)
    {
        $this->actionsPool[ $commander->getCommandName() ] = $commander;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::getCommandName
     *
     * Gets command description.
     *
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::getCommandDescription
     *
     * Gets command description.
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return $this->commandDescription;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractCommander::setCommandDescription
     *
     * Sets command description.
     *
     * @param string $commandDescription
     *
     * @return static
     */
    public function setCommandDescription($commandDescription)
    {
        $this->commandDescription = trim($commandDescription);

        return $this;
    }
}