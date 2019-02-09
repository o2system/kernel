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

namespace O2System\Kernel\Cli\Writers\Interfaces;

// ------------------------------------------------------------------------

/**
 * Interface ContextualClassInterface
 *
 * @package O2System\Kernel\Cli\Interfaces
 */
interface ContextualClassInterface
{
    /**
     * ContextualClassInterface::PRIMARY
     *
     * Label primary context class.
     *
     * @var string
     */
    const PRIMARY = 'primary';

    /**
     * ContextualClassInterface::SUCCESS
     *
     * Label success context class.
     *
     * @var string
     */
    const SUCCESS = 'success';

    /**
     * ContextualClassInterface::INFO
     *
     * Label info context class.
     *
     * @var string
     */
    const INFO = 'info';

    /**
     * ContextualClassInterface::WARNING
     *
     * Label warning context class.
     *
     * @var string
     */
    const WARNING = 'warning';

    /**
     * ContextualClassInterface::DANGER
     *
     * Label danger context class.
     *
     * @var string
     */
    const DANGER = 'danger';

    // ------------------------------------------------------------------------

    /**
     * ContextualClassInterface::setContextualClass
     *
     * Sets text contextual class.
     *
     * @param string $class
     *
     * @return static
     */
    public function setContextualClass($class);
}