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

namespace O2System\Kernel\DataStructures;

// ------------------------------------------------------------------------

use O2System\Spl\DataStructures\SplArrayObject;

/**
 * Class Config
 *
 * @package O2System\Core\Metadata
 */
class Config extends SplArrayObject
{
    /**
     * Camelcase Offset CONSTANT Flag
     *
     * @var bool
     */
    const CAMELCASE_OFFSET = true;

    /**
     * Standard Offset CONSTANT Flag
     *
     * @var bool
     */
    const STD_OFFSET = false;

    /**
     * Config with Camelcase Offset Flag.
     *
     * @var bool
     */
    private $camelcaseOffset;

    // ------------------------------------------------------------------------

    /**
     * Config::__construct
     *
     * @param array $config Initial array of config values
     * @param bool  $flag   Flag using camelcase offset
     *
     * @return Config Returns an SplArrayObject object on success.
     */
    public function __construct(array $config = [], $flag = self::CAMELCASE_OFFSET)
    {
        parent::__construct($config);

        $this->camelcaseOffset = $flag;

        if ($this->camelcaseOffset === true) {
            if (count($config) > 0) {
                foreach ($config as $offset => $value) {
                    $this->offsetSet($offset, $value);
                }
            }
        }

    }

    // ------------------------------------------------------------------------

    /**
     * Config::offsetSet
     *
     * Overriding method for SplArrayObject
     *
     * @param string $offset The index being set.
     * @param mixed  $value  The new value for the <i>index</i>.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($this->camelcaseOffset === true) {
            if (is_array($value)) {
                if (is_string(key($value))) {
                    $newValue = [];

                    foreach ($value as $key => $val) {
                        $newValue[ camelcase($key) ] = $val;
                    }

                    parent::offsetSet(camelcase($offset), new self($newValue));

                    return;
                }
            } elseif (is_string($offset)) {
                parent::offsetSet(camelcase($offset), $value);

                return;
            }
        }

        parent::offsetSet($offset, $value);
    }
}