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
 * Interface ContextualColorsInterface
 *
 * @package O2System\Kernel\Cli\Interfaces
 */
interface ContextualColorsInterface
{
    /**
     * ContextualColorsInterface::BLACK
     *
     * Black color context.
     *
     * @var string
     */
    const BLACK = 'black';

    /**
     * ContextualColorsInterface::WHITE
     *
     * White color context.
     *
     * @var string
     */
    const WHITE = 'white';

    /**
     * ContextualColorsInterface::YELLOW
     *
     * Yellow color context.
     *
     * @var string
     */
    const YELLOW = 'yellow';

    /**
     * ContextualColorsInterface::BROWN
     *
     * Brown color context.
     *
     * @var string
     */
    const BROWN = 'brown';

    /**
     * ContextualColorsInterface::MAGENTA
     *
     * Magenta color context.
     *
     * @var string
     */
    const MAGENTA = 'magenta';

    /**
     * ContextualColorsInterface::GRAY
     *
     * Gray color context.
     *
     * @var string
     */
    const GRAY = 'gray';

    /**
     * ContextualColorsInterface::LIGHT_GRAY
     *
     * Light gray color context.
     *
     * @var string
     */
    const LIGHT_GRAY = 'light-gray';

    /**
     * ContextualColorsInterface::BLUE
     *
     * Blue color context.
     *
     * @var string
     */
    const BLUE = 'blue';

    /**
     * ContextualColorsInterface::LIGHT_BLUE
     *
     * Light blue color context.
     *
     * @var string
     */
    const LIGHT_BLUE = 'light-blue';

    /**
     * ContextualColorsInterface::GREEN
     *
     * Green color context.
     *
     * @var string
     */
    const GREEN = 'green';

    /**
     * ContextualColorsInterface::LIGHT_GREEN
     *
     * Light green color context.
     *
     * @var string
     */
    const LIGHT_GREEN = 'light-green';

    /**
     * ContextualColorsInterface::CYAN
     *
     * Cyan color context.
     *
     * @var string
     */
    const CYAN = 'cyan';

    /**
     * ContextualColorsInterface::LIGHT_CYAN
     *
     * Light cyan color context.
     *
     * @var string
     */
    const LIGHT_CYAN = 'light-cyan';

    /**
     * ContextualColorsInterface::RED
     *
     * Red color context.
     *
     * @var string
     */
    const RED = 'red';

    /**
     * ContextualColorsInterface::LIGHT_RED
     *
     * Light red color context.
     *
     * @var string
     */
    const LIGHT_RED = 'light-red';

    /**
     * ContextualColorsInterface::PURPLE
     *
     * Purple color context.
     *
     * @var string
     */
    const PURPLE = 'purple';

    /**
     * ContextualColorsInterface::LIGHT_PURPLE
     *
     * Light purple color context.
     *
     * @var string
     */
    const LIGHT_PURPLE = 'light-purple';
}