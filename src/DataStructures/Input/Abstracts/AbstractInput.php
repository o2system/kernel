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

namespace O2System\Kernel\DataStructures\Input\Abstracts;

// ------------------------------------------------------------------------

use O2System\Security\Filters\Rules;
use O2System\Security\Filters\Xss;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractInput
 * @package O2System\Kernel\DataStructures\Input\Abstracts
 */
abstract class AbstractInput implements
    \ArrayAccess,
    \IteratorAggregate,
    \Countable,
    \Serializable,
    \JsonSerializable,
    ContainerInterface
{
    /**
     * AbstractInput::$filter
     *
     * @var bool
     */
    protected $filter = FILTER_DEFAULT;

    /**
     * AbstractInput::$rules
     *
     * @var Rules
     */
    protected $rules;

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::has
     *
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return (bool)$this->offsetExists($id);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::exists
     *
     * Checks if the data exists on the storage.
     * An alias of AbstractInput::__isset method.
     *
     * @param string $offset The object offset key.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function exists($offset)
    {
        return $this->__isset($offset);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::__isset
     *
     * @param mixed $offset PHP native global variable offset.
     *
     * @return bool
     */
    public function __isset($offset)
    {
        return $this->offsetExists($offset);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::store
     *
     * Store the data into the storage.
     * An alias of AbstractInput::__set method.
     *
     * @param string $offset The data offset key.
     * @param mixed  $value  The data to be stored.
     *
     * @return void
     */
    public function store($offset, $value)
    {
        $this->__set($offset, $value);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::__set
     *
     * @param mixed $offset PHP native global variable offset.
     * @param mixed $value  PHP native global variable offset value to set.
     */
    public function __set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::remove
     *
     * Removes a data from the storage.
     * An alias of AbstractInput::__unset method.
     *
     * @param string $offset The object offset key.
     *
     * @return void
     */
    public function remove($offset)
    {
        $this->__unset($offset);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::__unset
     *
     * @param mixed $offset PHP Native globals variable offset
     *
     * @return void
     */
    public function __unset($offset)
    {
        $this->offsetUnset($offset);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::get
     *
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->offsetGet($id);
        }

        // @todo throw exception
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::validate
     *
     * @param array $rules
     *
     * @return bool
     */
    public function validate(array $rules)
    {
        $this->rules = new Rules();
        $this->rules->sets($rules);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::filter
     *
     * @param mixed $filter
     */
    public function filter($filter)
    {
        if (in_array($filter, filter_list())) {
            $this->filter = $filter;
        } elseif (is_callable($filter)) {
            $this->filter = $filter;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::offsetGet
     *
     * @param mixed $offset
     *
     * @return mixed|void
     */
    protected function filterVar($value)
    {
        if($this->rules instanceof Rules) {

        } elseif (is_array($value) and is_int($this->filter)) {
            $value = $this->filterVarRecursive($value, $this->filter);
        } elseif (is_callable($this->filter)) {
            $value = call_user_func_array($this->filter, [$value]);
        } else {
            $value = filter_var($value, $this->filter);
        }

        if(class_exists('O2System\Framework', false)) {
            if (services()->has('xssProtection')) {
                if ( ! services()->get('xssProtection')->verify()) {
                    if (is_string($value)) {
                        $value = Xss::clean($value);
                    }
                }
            }
        }

        return $value;
    }

    // ------------------------------------------------------------------------

    /**
     * Input::filterRecursive
     *
     * Gets multiple variables and optionally filters them.
     *
     * @see http://php.net/manual/en/function.filter-var.php
     * @see http://php.net/manual/en/function.filter-var-array.php
     *
     *
     * @param array     $data   An array with string keys containing the data to filter.
     * @param int|mixed $filter The ID of the filter to apply.
     *                          The Types of filters manual page lists the available filters.
     *                          If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                          This will result in no filtering taking place by default.
     *                          Its also can be An array defining the arguments.
     *                          A valid key is a string containing a variable name and a valid value is either
     *                          a filter type, or an array optionally specifying the filter, flags and options.
     *                          If the value is an array, valid keys are filter which specifies the filter type,
     *                          flags which specifies any flags that apply to the filter, and options which
     *                          specifies any options that apply to the filter. See the example below for
     *                          a better understanding.
     *
     * @return mixed
     */
    protected function filterVarRecursive(array $data, $filter = FILTER_DEFAULT)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) AND is_array($filter)) {
                $data[ $key ] = filter_var_array($value, $filter);
            } elseif (is_array($value)) {
                $data[ $key ] = $this->filterVarRecursive($value, $filter);
            } elseif (isset($filter)) {
                $data[ $key ] = filter_var($value, $filter);
            } else {
                $data[ $key ] = $value;
            }
        }

        return $data;
    }
}
