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

namespace O2System\Kernel\DataStructures\Input;

// ------------------------------------------------------------------------

use O2System\Kernel\DataStructures\Input\Abstracts\AbstractInput;

/**
 * Class Cookie
 * @package O2System\Kernel\DataStructures\Input
 */
class Cookie extends AbstractInput
{
    /**
     * Cookie::getIterator
     *
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($_COOKIE);
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::offsetExists
     *
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($_COOKIE[ $offset ]);
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::__get
     *
     * Implementing magic method __get to simplify gets PHP native session variable by requested offset,
     * just simply calling isset( $session[ 'offset' ] ).
     *
     * @param $offset
     *
     * @return mixed
     */
    public function &__get($offset)
    {
        return $_COOKIE[ $offset ];
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::offsetSet
     *
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $_COOKIE[ $offset ] = $value;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::offsetUnset
     *
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (isset($_COOKIE[ $offset ])) {
            unset($_COOKIE[ $offset ]);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::merge
     *
     * Merge new array of data into the data storage.
     *
     * @param array $data New array of data.
     *
     * @return array The old array of data storage.
     */
    public function merge(array $data)
    {
        $oldData = $_COOKIE;
        $_COOKIE = array_merge($_COOKIE, $data);

        return $oldData;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::exchange
     *
     * Exchange the array of data storage into the new array of data.
     *
     * @param array $data New array of data.
     *
     * @return array The old array of data storage.
     */
    public function exchange(array $data)
    {
        $oldData = $_COOKIE;
        $_COOKIE = $data;

        return $oldData;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::destroy
     *
     * Removes all object from the container and perform each object destruction.
     *
     * @return array Array of old storage items.
     */
    public function destroy()
    {
        $storage = $_COOKIE;

        $_COOKIE = [];

        return $storage;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::count
     *
     * Application of Countable::count method to count the numbers of contained objects.
     *
     * @see  http://php.net/manual/en/countable.count.php
     * @return int The numbers of data on the storage.
     */
    public function count()
    {
        return (int)count($_COOKIE);
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::serialize
     *
     * Application of Serializable::serialize method to serialize the data storage.
     *
     * @see  http://php.net/manual/en/serializable.serialize.php
     *
     * @return string The string representation of the serialized data storage.
     */
    public function serialize()
    {
        return serialize($_COOKIE);
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::unserialize
     *
     * Application of Serializable::unserialize method to unserialize and construct the data storage.
     *
     * @see  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized The string representation of the serialized data storage.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $_COOKIE = unserialize($serialized);
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::jsonSerialize
     *
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *        which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $_COOKIE;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::getArrayCopy
     *
     * Gets a copy of the data storage.
     *
     * @return array Returns a copy of the data storage.
     */
    public function getArrayCopy()
    {
        return $_COOKIE;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie::offsetGet
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
        return (isset($_COOKIE[ $offset ])) ? $this->filterVar($_COOKIE[ $offset ]) : false;
    }
}