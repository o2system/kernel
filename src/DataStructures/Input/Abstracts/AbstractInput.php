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

use O2System\Security\Filters\Xss;
use O2System\Security\Form\Validator;
use O2System\Spl\DataStructures\SplArrayStorage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractInput
 * @package O2System\Kernel\DataStructures\Input\Abstracts
 */
abstract class AbstractInput extends SplArrayStorage implements
    ContainerInterface
{
    /**
     * AbstractInput::$filter
     *
     * @var bool
     */
    protected $filter = FILTER_DEFAULT;

    /**
     * AbstractInput::$validator
     *
     * @var Validator
     */
    public $validator;

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::has
     *
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($offset)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $offset Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($offset)
    {
        return (bool)$this->offsetExists($offset);
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::get
     *
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $offset Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get($offset)
    {
        if ($this->has($offset)) {
            return $this->offsetGet($offset);
        }

        // @todo throw exception
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::offsetGet
     *
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            $value = $this->filterVar(parent::offsetGet($offset));

            if ($this->validator instanceof Validator) {
                if ($this->validator->hasRule($offset)) {
                    if ($this->validator->validate([
                        $offset => $value,
                    ], true)) {
                        return $value;
                    }
                }
            }

            return $value;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::validation
     *
     * @param array $rules
     * @param array $customErrors
     *
     * @return static
     */
    public function validation(array $rules, array $customErrors = [])
    {
        $this->validator = new Validator();
        $this->validator->setRules($rules, $customErrors);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::validate
     *
     * @param string $field
     * @param string $rule
     * @param array  $customErrors
     *
     * @return bool|static
     */
    public function validate(string $field = null, string $rule = null, array $customErrors = [])
    {
        if(isset($field) and isset($rule)) {
            $this->validator = new Validator();
            $this->validator->addRule($field, $field, $rule, $customErrors);

            return $this->offsetGet($field);
        } elseif ($this->validator instanceof Validator) {
            if($this->validator->validate($this->storage)) {
                return $this;
            }
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::setFilter
     *
     * @param mixed $filter
     *
     * @return static
     * @example
     *         AbstractInput::setFilter(FILTER_DEFAULT);
     *
     *         // Callback filter example
     *         AbstractInput::setFilter(function($value){
     *              // do something
     *              return $filteredValue;
     *         });
     *
     */
    public function setFilter($filter)
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
     * AbstractInput::filter
     *
     * @param string $offset
     * @param mixed  $filter
     *                      
     * @return mixed
     */
    public function filter(string $offset, $filter)
    {
        $this->setFilter($filter);

        if ($this->offsetExists($offset)) {
            return $this->filterVar(parent::offsetGet($offset));
        }

        return null;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractInput::filterVar
     *
     * @param mixed $value
     *
     * @return mixed|void
     */
    protected function filterVar($value)
    {
        if (is_array($value) and is_int($this->filter)) {
            $value = $this->filterVarRecursive($value, $this->filter);
        } elseif (is_callable($this->filter)) {
            $value = call_user_func_array($this->filter, [$value]);
        } elseif(is_object($value)) {
            return $value;
        } else {
            $value = filter_var($value, $this->filter);
        }

        if (class_exists('O2System\Framework', false)) {
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
     * AbstractInput::filterRecursive
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
