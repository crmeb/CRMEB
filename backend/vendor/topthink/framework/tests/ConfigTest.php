<?php

namespace think\tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use think\Config;

class ConfigTest extends TestCase
{
    public function testLoad()
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('test.php')->setContent("<?php return ['key1'=> 'value1','key2'=>'value2'];");
        $root->addChild($file);

        $config = new Config();

        $config->load($file->url(), 'test');

        $this->assertEquals('value1', $config->get('test.key1'));
        $this->assertEquals('value2', $config->get('test.key2'));

        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $config->get('test'));
    }

    public function testSetAndGet()
    {
        $config = new Config();

        $config->set([
            'key1' => 'value1',
            'key2' => [
                'key3' => 'value3',
            ],
        ], 'test');

        $this->assertTrue($config->has('test.key1'));
        $this->assertEquals('value1', $config->get('test.key1'));
        $this->assertEquals('value3', $config->get('test.key2.key3'));

        $this->assertEquals(['key3' => 'value3'], $config->get('test.key2'));
        $this->assertFalse($config->has('test.key3'));
        $this->assertEquals('none', $config->get('test.key3', 'none'));
    }

    public function testGlobalHook()
    {
        $config = new Config();

        // Set initial config
        $config->set(['key1' => 'original_value'], 'test');

        // Register global hook
        $config->hook(function ($name, $value) {
            if ($name === 'test.key1') {
                return 'hooked_value';
            }
            if ($name === 'test.key2' && is_null($value)) {
                return 'default_from_hook';
            }
            return $value;
        });

        // Test hook modifies existing value
        $this->assertEquals('hooked_value', $config->get('test.key1'));

        // Test hook provides default for non-existent key
        $this->assertEquals('default_from_hook', $config->get('test.key2'));

        // Test hook returns original value for other keys
        $config->set(['key3' => 'unchanged'], 'test');
        $this->assertEquals('unchanged', $config->get('test.key3'));
    }

    public function testSpecificKeyHook()
    {
        $config = new Config();

        // Set initial config
        $config->set([
            'key1' => 'value1',
            'key2' => 'value2'
        ], 'test');

        $config->set([
            'key1' => 'value1'
        ], 'other');

        // Register hook for specific key 'test'
        $config->hook(function ($name, $value) {
            if (str_contains($name, 'key1')) {
                return 'test_hooked_' . $value;
            }
            return $value;
        }, 'test');

        // Register hook for specific key 'other'
        $config->hook(function ($name, $value) {
            if (str_contains($name, 'key1')) {
                return 'other_hooked_' . $value;
            }
            return $value;
        }, 'other');

        // Test specific hook for 'test' configuration
        $this->assertEquals('test_hooked_value1', $config->get('test.key1'));
        $this->assertEquals('value2', $config->get('test.key2')); // No hook for key2

        // Test specific hook for 'other' configuration
        $this->assertEquals('other_hooked_value1', $config->get('other.key1'));
    }

    public function testHookPriority()
    {
        $config = new Config();

        // Set initial config
        $config->set(['key1' => 'value1'], 'test');

        // Register global hook first
        $config->hook(function ($name, $value) {
            return 'global_' . $value;
        });

        // Register specific key hook (should override global)
        $config->hook(function ($name, $value) {
            return 'specific_' . $value;
        }, 'test');

        // Specific hook should take priority over global hook
        $this->assertEquals('specific_value1', $config->get('test.key1'));
    }

    public function testHookWithNullReturn()
    {
        $config = new Config();

        // Register hook that returns null
        $config->hook(function ($name, $value) {
            if ($name === 'test.nonexistent') {
                return null; // This should trigger default value
            }
            return $value;
        });

        // Test with default value when hook returns null
        $this->assertEquals('default_value', $config->get('test.nonexistent', 'default_value'));
    }

    public function testHookWithTopLevelConfig()
    {
        $config = new Config();

        // Set top-level config
        $config->set(['key1' => 'value1', 'key2' => 'value2'], 'database');

        // Register hook for database config
        $config->hook(function ($name, $value) {
            if ($name === 'database') {
                return array_merge($value, ['key3' => 'added_by_hook']);
            }
            return $value;
        }, 'database');

        // Test hook modifies entire config section
        $result = $config->get('database');
        $this->assertIsArray($result);
        $this->assertEquals('value1', $result['key1']);
        $this->assertEquals('value2', $result['key2']);
        $this->assertEquals('added_by_hook', $result['key3']);
    }

    public function testLazyLoadingBehavior()
    {
        $config = new Config();

        // Counter to verify hook is called
        $hookCallCount = 0;

        // Register hook with counter
        $config->hook(function ($name, $value) use (&$hookCallCount) {
            $hookCallCount++;
            return $value ? $value . '_processed' : 'processed_default';
        });

        // Set config value
        $config->set(['key1' => 'value1'], 'test');

        // First call should trigger hook
        $result1 = $config->get('test.key1');
        $this->assertEquals('value1_processed', $result1);
        $this->assertEquals(1, $hookCallCount);

        // Second call should also trigger hook (no caching)
        $result2 = $config->get('test.key1');
        $this->assertEquals('value1_processed', $result2);
        $this->assertEquals(2, $hookCallCount);

        // Test with non-existent key
        $result3 = $config->get('test.nonexistent', 'default');
        $this->assertEquals('processed_default', $result3);
        $this->assertEquals(3, $hookCallCount);
    }
}
