<?php
/**
 * File ComplexMapTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace ERP\Tests\Component;

use ERP\Component\ComplexMap;

/**
 * Test Class
 *
 * @package ERP\Component
 * @subPackage Tests
 */
class TestClass
{
    /** @var mixed */
    protected $value;

    /**
     * Constructor
     *
     * @param null $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }
}

/**
 * Complex Map Test
 *
 * @package ERP
 * @subPackage Tests
 */
class ComplexMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test keys
     * @var array
     */
    protected $keys;

    /**
     * Test values
     * @var array
     */
    protected $values;

    /**
     * Test map
     * @var ComplexMap
     */
    protected $complexMap;

    /**
     * Create sample ComplexMap for test cases and stores references to the
     * original map keys/values to be used in test cases
     *
     * @return void
     */
    public function setUp()
    {
        $this->keys   = $this->getTestKeys();
        $this->values = $this->getTestValues();

        $this->complexMap = new ComplexMap($this->keys, $this->values);
    }

    /**
     * Return sample test keys array for testing
     * both scalar & complex map keys
     *
     * @return array
     */
    protected function getTestKeys()
    {
        return [
            'foo',                // test string
            12345,                // test integer
            [1, 2, 3],            // test array
            new \stdClass(),      // test object
            new TestClass('foo'), // test class
            function() {},        // test callable
        ];
    }

    /**
     * Return sample test values array for testing
     * both scalar & complex map values
     *
     * @return array
     */
    protected function getTestValues()
    {
        return [
            'bar',                // test string
            54321,                // test integer
            [3, 2, 1],            // test array
            new \stdClass(),      // test object
            new TestClass('bar'), // test class
            function() {},        // test callable
        ];
    }

    /**
     * Test that the map keys & values are direct references to the
     * original array values passed in during map creation
     *
     * @covers \ERP\ComplexMap::__constructor
     */
    public function testConstructor()
    {
        \PHPUnit_Framework_Assert::assertAttributeSame($this->keys, 'keys', $this->complexMap);
        \PHPUnit_Framework_Assert::assertAttributeSame($this->values, 'values', $this->complexMap);
        \PHPUnit_Framework_Assert::assertAttributeNotSame($this->getTestKeys(), 'keys', $this->complexMap);
        \PHPUnit_Framework_Assert::assertAttributeNotSame($this->getTestValues(), 'values', $this->complexMap);
    }

    /**
     * Test that the constructor throws proper argument exceptions
     * on mis-matching map key/value lengths
     *
     * @expectedException \InvalidArgumentException
     * @covers \ERP\ComplexMap::__constructor
     */
    public function testLengthMisMatchException()
    {
        new ComplexMap([], ['value']);
    }

    /**
     * Tests that key & value arrays are converted to indexed
     * arrays during map instantiation
     *
     * @covers \ERP\ComplexMap::__constructor
     */
    public function testInternalArrayConversion()
    {
        $reflectionClass = new \ReflectionClass(new ComplexMap(
            ['foo' => 'bar'],
            ['bax' => 12345]
        ));

        $keysProperty   = $reflectionClass->getProperty('keys');
        $valuesProperty = $reflectionClass->getProperty('values');

        $keysProperty->setAccessible(true);
        $valuesProperty->setAccessible(true);

        \PHPUnit_Framework_Assert::assertContainsOnly('integer', array_keys($keysProperty->getValue($this->complexMap)));
        \PHPUnit_Framework_Assert::assertContainsOnly('integer', array_keys($valuesProperty->getValue($this->complexMap)));
    }

    /**
     * @covers \ERP\ComplexMap::count
     */
    public function testCount()
    {
        \PHPUnit_Framework_Assert::assertEquals(count($this->keys), $this->complexMap->count());
        \PHPUnit_Framework_Assert::assertEquals(count($this->values), $this->complexMap->count());
    }

    /**
     * @covers \ERP\ComplexMap::seek
     */
    public function testSeek()
    {
        foreach ($this->keys as $index => $key) {
            $this->complexMap->seek($key);
            \PHPUnit_Framework_Assert::assertSame($this->values[$index], $this->complexMap->current());
        }
    }

    /**
     * @covers \ERP\ComplexMap::current
     */
    public function testCurrent()
    {
        \PHPUnit_Framework_Assert::assertSame(current($this->values), $this->complexMap->current());

        foreach ($this->values as $index => $value) {
            $this->complexMap->seek($this->keys[$index]);
            \PHPUnit_Framework_Assert::assertSame($value, $this->complexMap->current());
        }
    }

    /**
     * @covers \ERP\ComplexMap::next
     */
    public function testNext()
    {
        foreach ($this->values as $value) {
            \PHPUnit_Framework_Assert::assertSame($value, $this->complexMap->current());
            $this->complexMap->next();
        }
    }

    /**
     * @covers \ERP\ComplexMap::key
     */
    public function testkey()
    {
        \PHPUnit_Framework_Assert::assertSame(current($this->keys), $this->complexMap->key());

        foreach ($this->keys as $key) {
            \PHPUnit_Framework_Assert::assertSame($key, $this->complexMap->key());
            $this->complexMap->next();
        }
    }

    /**
     * @covers \ERP\ComplexMap::valid
     */
    public function testValid()
    {
        \PHPUnit_Framework_Assert::assertTrue($this->complexMap->valid());

        foreach ($this->keys as $key) {
            \PHPUnit_Framework_Assert::assertTrue($this->complexMap->valid());
            $this->complexMap->next();
        }

        \PHPUnit_Framework_Assert::assertFalse($this->complexMap->valid());
    }

    /**
     * @covers \ERP\ComplexMap::rewind
     */
    public function testRewind()
    {
        \PHPUnit_Framework_Assert::assertSame(current($this->keys), $this->complexMap->key());
        \PHPUnit_Framework_Assert::assertSame(current($this->values), $this->complexMap->current());

        $this->complexMap->seek($this->keys[5]);

        \PHPUnit_Framework_Assert::assertSame($this->keys[5], $this->complexMap->key());
        \PHPUnit_Framework_Assert::assertSame($this->values[5], $this->complexMap->current());

        $this->complexMap->rewind();

        \PHPUnit_Framework_Assert::assertSame($this->keys[0], $this->complexMap->key());
        \PHPUnit_Framework_Assert::assertSame($this->values[0], $this->complexMap->current());
    }

    /**
     * @covers \ERP\ComplexMap::offsetExists
     */
    public function testOffsetExists()
    {
        foreach ($this->keys as $key) {
            \PHPUnit_Framework_Assert::assertTrue($this->complexMap->offsetExists($key));
        }

        \PHPUnit_Framework_Assert::assertFalse($this->complexMap->offsetExists(new TestClass('foo')));
    }

    /**
     * @covers \ERP\ComplexMap::offsetGet
     */
    public function testOffsetGet()
    {
        foreach ($this->keys as $index => $key) {
            \PHPUnit_Framework_Assert::assertSame($this->values[$index], $this->complexMap->offsetGet($key));
        }
    }

    /**
     * @covers \ERP\ComplexMap::offsetSet
     */
    public function testOffsetSet()
    {
        $newKey = new TestClass('foo');
        $value  = new \stdClass();

        // test overwrite existing
        $this->complexMap->offsetSet($this->keys[4], $value);
        \PHPUnit_Framework_Assert::assertSame($value, $this->complexMap->offsetGet($this->keys[4]));

        // test adding new
        $this->complexMap->offsetSet($newKey, $value);
        \PHPUnit_Framework_Assert::assertSame($value, $this->complexMap->offsetGet($newKey));
    }

    /**
     * @covers \ERP\ComplexMap::offsetUnset
     */
    public function testOffsetUnset()
    {
        $this->complexMap->offsetUnset(current($this->keys));

        \PHPUnit_Framework_Assert::assertFalse($this->complexMap->offsetExists(current($this->keys)));
        \PHPUnit_Framework_Assert::assertEquals(count($this->keys) - 1, $this->complexMap->count());
    }

    /**
     * Test that the Countable interface behaves as expected
     *
     * @covers \ERP\ComplexMap::count
     */
    public function testCountable()
    {
        \PHPUnit_Framework_Assert::assertInstanceOf(\Countable::class, $this->complexMap);
        \PHPUnit_Framework_Assert::assertCount(count($this->keys), $this->complexMap);
        \PHPUnit_Framework_Assert::assertCount(count($this->values), $this->complexMap);
    }

    /**
     * Test that the Iterator & Traversable interfaces behave as expected and
     * mapped keys/values are stored in the same order they were created in
     *
     * @covers \ERP\ComplexMap::current
     * @covers \ERP\ComplexMap::next
     * @covers \ERP\ComplexMap::key
     * @covers \ERP\ComplexMap::valid
     * @covers \ERP\ComplexMap::rewind
     */
    public function testIterable()
    {
        \PHPUnit_Framework_Assert::assertInstanceOf(\Iterator::class, $this->complexMap);
        \PHPUnit_Framework_Assert::assertInstanceOf(\Traversable::class, $this->complexMap);

        foreach ($this->complexMap as $key => $value) {
            \PHPUnit_Framework_Assert::assertSame(current($this->keys), $key);
            \PHPUnit_Framework_Assert::assertSame(current($this->values), $value);

            next($this->keys);
            next($this->values);
        }
    }

    /**
     * Test that the ArrayAccess interface behaves as expected
     *
     * @covers \ERP\ComplexMap::offsetExists
     * @covers \ERP\ComplexMap::offsetGet
     * @covers \ERP\ComplexMap::offsetSet
     * @covers \ERP\ComplexMap::offsetUnset
     */
    public function testArrayAccess()
    {
        foreach ($this->keys as $index => $key) {
            \PHPUnit_Framework_Assert::assertSame($this->values[$index], $this->complexMap[$key]);
        }
    }
}
