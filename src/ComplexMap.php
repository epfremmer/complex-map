<?php
/**
 * File ComplexMap.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace ERP\Component;

/**
 * Complex Map
 *
 * Traversable map that supports the use of complex
 * non-scalar values as map keys.
 *
 * @package ERP\Component
 */
class ComplexMap implements \SeekableIterator, \Countable, \ArrayAccess
{

    /**
     * Map keys
     * @var array<int,mixed>
     */
    protected $keys;

    /**
     * Map values
     * @var array<int,mixed>
     */
    protected $values;

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * Constructor
     *
     * @param array $keys
     * @param array $values
     */
    public function __construct(array $keys = [], array $values = [])
    {
        if (count($keys) !== count($values)) {
            throw new \InvalidArgumentException("Map keys and values must be the same length");
        }

        $this->keys   = array_values($keys);
        $this->values = array_values($values);
    }

    /**
     * Return the numeric index of $key used to
     * reference the mapped key's value
     *
     * @param mixed $key
     * @return mixed
     */
    protected function getIndex($key)
    {
        $index = array_search($key, $this->keys, true);

        if ($index === false) {
            throw new \InvalidArgumentException("Index not found in map keys");
        }

        return $index;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->keys);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $position
     */
    public function seek($position)
    {
        $this->index = $this->getIndex($position);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->values[$this->index];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->index++;

        return $this->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->keys[$this->index];
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->index, $this->keys)
            && array_key_exists($this->index, $this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_search($offset, $this->keys, true) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $index = $this->getIndex($offset);

        return $this->values[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $index = array_search($offset, $this->keys, true);

        if ($index !== false) {
            $this->values[$index] = $value;
        } else {
            $this->keys[]   = $offset;
            $this->values[] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $index = $this->getIndex($offset);

        unset($this->keys[$index]);
        unset($this->values[$index]);
    }
}
