<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\Cache;
use ArrayObject;

abstract class CacheTest extends \Doctrine\Tests\DoctrineTestCase
{
    /**
     * @dataProvider provideDataToCache
     */
    public function testSetContainsFetchDelete($value)
    {
        $cache = $this->_getCacheDriver();

        // Test saving a value, checking if it exists, and fetching it back
        $this->assertTrue($cache->save('key', $value));
        $this->assertTrue($cache->contains('key'));
        if (is_object($value)) {
            $this->assertEquals($value, $cache->fetch('key'), 'Objects retrieved from the cache must be equal but not necessarily the same reference');
        } else {
            $this->assertSame($value, $cache->fetch('key'), 'Scalar and array data retrieved from the cache must be the same as the original, e.g. same type');
        }

        // Test deleting a value
        $this->assertTrue($cache->delete('key'));
        $this->assertFalse($cache->contains('key'));
        $this->assertFalse($cache->fetch('key'));
    }

    /**
     * @dataProvider provideDataToCache
     */
    public function testUpdateExistingEntry($value)
    {
        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key', 'old-value'));
        $this->assertTrue($cache->contains('key'));

        $this->assertTrue($cache->save('key', $value));
        $this->assertTrue($cache->contains('key'));
        if (is_object($value)) {
            $this->assertEquals($value, $cache->fetch('key'), 'Objects retrieved from the cache must be equal but not necessarily the same reference');
        } else {
            $this->assertSame($value, $cache->fetch('key'), 'Scalar and array data retrieved from the cache must be the same as the original, e.g. same type');
        }
    }

    public function testFetchMulti()
    {
        $cache = $this->_getCacheDriver();

        $cache->deleteAll();

        // Test saving some values, checking if it exists, and fetching it back with multiGet
        $this->assertTrue($cache->save('key1', 'value1'));
        $this->assertTrue($cache->save('key2', 'value2'));

        $this->assertEquals(
            array('key1' => 'value1', 'key2' => 'value2'),
            $cache->fetchMultiple(array('key1', 'key2'))
        );
        $this->assertEquals(
            array('key1' => 'value1', 'key2' => 'value2'),
            $cache->fetchMultiple(array('key1', 'key3', 'key2'))
        );
        $this->assertEquals(
            array('key1' => 'value1', 'key2' => 'value2'),
            $cache->fetchMultiple(array('key1', 'key2', 'key3'))
        );
    }

    public function testFetchMultiWithEmptyKeysArray()
    {
        $cache = $this->_getCacheDriver();
        
        $this->assertEmpty(
            $cache->fetchMultiple(array())
        );
    }

    public function testFetchMultiWithFalsey()
    {
        $cache = $this->_getCacheDriver();

        $cache->deleteAll();

        $values = array(
            'string' => 'str',
            'integer' => 1,
            'boolean' => true,
            'null' => null,
            'array_empty' => array(),
            'integer_zero' => 0,
            'string_empty' => ''
        );
        foreach ($values AS $key => $value) {
            $cache->save($key, $value);
        }

        $this->assertEquals(
            $values,
            $cache->fetchMultiple(array_keys($values))
        );
    }

    public function provideDataToCache()
    {
        return array(
            'array' => array(array('one', 2, 3.01)),
            'string' => array('value'),
            'integer' => array(1),
            'float' => array(1.5),
            'object' => array(new ArrayObject()),
            'true' => array(true),
            // the following are considered FALSE in boolean context, but caches should still recognize their existence
            'null' => array(null),
            'false' => array(false),
            'array_empty' => array(array()),
            'string_zero' => array('0'),
            'integer_zero' => array(0),
            'float_zero' => array(0.0),
            'string_empty' => array('')
        );
    }

    public function testDeleteIsSuccessfulWhenKeyDoesNotExist()
    {
        $cache = $this->_getCacheDriver();

        $this->assertFalse($cache->contains('key'));
        $this->assertTrue($cache->delete('key'));
    }

    public function testDeleteAll()
    {
        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->deleteAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testDeleteAllAndNamespaceVersioningBetweenCaches()
    {
        if ( ! $this->isSharedStorage()) {
            $this->markTestSkipped('The ' . __CLASS__ .' does not use shared storage');
        }

        $cache1 = $this->_getCacheDriver();
        $cache2 = $this->_getCacheDriver();

        $this->assertTrue($cache1->save('key1', 1));
        $this->assertTrue($cache2->save('key2', 2));

        /* Both providers are initialized with the same namespace version, so
         * they can see entries set by each other.
         */
        $this->assertTrue($cache1->contains('key1'));
        $this->assertTrue($cache1->contains('key2'));
        $this->assertTrue($cache2->contains('key1'));
        $this->assertTrue($cache2->contains('key2'));

        /* Deleting all entries through one provider will only increment the
         * namespace version on that object (and in the cache itself, which new
         * instances will use to initialize). The second provider will retain
         * its original version and still see stale data.
         */
        $this->assertTrue($cache1->deleteAll());
        $this->assertFalse($cache1->contains('key1'));
        $this->assertFalse($cache1->contains('key2'));
        $this->assertTrue($cache2->contains('key1'));
        $this->assertTrue($cache2->contains('key2'));

        /* A new cache provider should not see the deleted entries, since its
         * namespace version will be initialized.
         */
        $cache3 = $this->_getCacheDriver();
        $this->assertFalse($cache3->contains('key1'));
        $this->assertFalse($cache3->contains('key2'));
    }

    public function testFlushAll()
    {
        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->flushAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testFlushAllAndNamespaceVersioningBetweenCaches()
    {
        if ( ! $this->isSharedStorage()) {
            $this->markTestSkipped('The ' . __CLASS__ .' does not use shared storage');
        }

        $cache1 = $this->_getCacheDriver();
        $cache2 = $this->_getCacheDriver();

        /* Deleting all elements from the first provider should increment its
         * namespace version before saving the first entry.
         */
        $cache1->deleteAll();
        $this->assertTrue($cache1->save('key1', 1));

        /* The second provider will be initialized with the same namespace
         * version upon its first save operation.
         */
        $this->assertTrue($cache2->save('key2', 2));

        /* Both providers have the same namespace version and can see entries
         * set by each other.
         */
        $this->assertTrue($cache1->contains('key1'));
        $this->assertTrue($cache1->contains('key2'));
        $this->assertTrue($cache2->contains('key1'));
        $this->assertTrue($cache2->contains('key2'));

        /* Flushing all entries through one cache will remove all entries from
         * the cache but leave their namespace version as-is.
         */
        $this->assertTrue($cache1->flushAll());
        $this->assertFalse($cache1->contains('key1'));
        $this->assertFalse($cache1->contains('key2'));
        $this->assertFalse($cache2->contains('key1'));
        $this->assertFalse($cache2->contains('key2'));

        /* Inserting a new entry will use the same, incremented namespace
         * version, and it will be visible to both providers.
         */
        $this->assertTrue($cache1->save('key1', 1));
        $this->assertTrue($cache1->contains('key1'));
        $this->assertTrue($cache2->contains('key1'));

        /* A new cache provider will be initialized with the original namespace
         * version and not share any visibility with the first two providers.
         */
        $cache3 = $this->_getCacheDriver();
        $this->assertFalse($cache3->contains('key1'));
        $this->assertFalse($cache3->contains('key2'));
        $this->assertTrue($cache3->save('key3', 3));
        $this->assertTrue($cache3->contains('key3'));
    }

    public function testNamespace()
    {
        $cache = $this->_getCacheDriver();

        $cache->setNamespace('ns1_');

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns2_');

        $this->assertFalse($cache->contains('key1'));
    }

    public function testDeleteAllNamespace()
    {
        $cache = $this->_getCacheDriver();

        $cache->setNamespace('ns1');
        $this->assertFalse($cache->contains('key1'));
        $cache->save('key1', 'test');
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns2');
        $this->assertFalse($cache->contains('key1'));
        $cache->save('key1', 'test');
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns1');
        $this->assertTrue($cache->contains('key1'));
        $cache->deleteAll();
        $this->assertFalse($cache->contains('key1'));

        $cache->setNamespace('ns2');
        $this->assertTrue($cache->contains('key1'));
        $cache->deleteAll();
        $this->assertFalse($cache->contains('key1'));
    }

    /**
     * @group DCOM-43
     */
    public function testGetStats()
    {
        $cache = $this->_getCacheDriver();
        $stats = $cache->getStats();

        $this->assertArrayHasKey(Cache::STATS_HITS, $stats);
        $this->assertArrayHasKey(Cache::STATS_MISSES, $stats);
        $this->assertArrayHasKey(Cache::STATS_UPTIME, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_USAGE, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_AVAILABLE, $stats);
    }

    public function testFetchMissShouldReturnFalse()
    {
        $cache = $this->_getCacheDriver();

        /* Ensure that caches return boolean false instead of null on a fetch
         * miss to be compatible with ORM integration.
         */
        $result = $cache->fetch('nonexistent_key');

        $this->assertFalse($result);
        $this->assertNotNull($result);
    }

    /**
     * Check to see that objects are correctly serialized and unserialized by the cache
     * provider.
     */
    public function testCachedObject()
    {
        $cache = $this->_getCacheDriver();
        $cache->deleteAll();
        $obj = new \stdClass();
        $obj->foo = "bar";
        $obj2 = new \stdClass();
        $obj2->bar = "foo";
        $obj2->obj = $obj;
        $obj->obj2 = $obj2;
        $cache->save("obj", $obj);

        $fetched = $cache->fetch("obj");

        $this->assertInstanceOf("stdClass", $obj);
        $this->assertInstanceOf("stdClass", $obj->obj2);
        $this->assertInstanceOf("stdClass", $obj->obj2->obj);
        $this->assertEquals("bar", $fetched->foo);
        $this->assertEquals("foo", $fetched->obj2->bar);
    }

    /**
     * Check to see that objects fetched via fetchMultiple are properly unserialized
     */
    public function testFetchMultipleObjects()
    {
        $cache = $this->_getCacheDriver();
        $cache->deleteAll();
        $obj1 = new \stdClass();
        $obj1->foo = "bar";
        $cache->save("obj1", $obj1);
        $obj2 = new \stdClass();
        $obj2->bar = "baz";
        $cache->save("obj2", $obj2);

        $fetched = $cache->fetchMultiple(array("obj1", "obj2"));
        $this->assertInstanceOf("stdClass", $fetched["obj1"]);
        $this->assertInstanceOf("stdClass", $fetched["obj2"]);
        $this->assertEquals("bar", $fetched["obj1"]->foo);
        $this->assertEquals("baz", $fetched["obj2"]->bar);
    }

    /**
     * Return whether multiple cache providers share the same storage.
     *
     * This is used for skipping certain tests for shared storage behavior.
     *
     * @return boolean
     */
    protected function isSharedStorage()
    {
        return true;
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    abstract protected function _getCacheDriver();
}
