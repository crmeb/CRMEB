<?php

namespace AlibabaCloud\Tea\XML\Tests;

use AlibabaCloud\Tea\XML\XML;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RpcUtilsTest extends TestCase
{
    private $xmlStr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
    "<tests>\n" .
    "  <name>test</name>\n" .
    "  <value>1</value>\n" .
    "</tests>\n";

    public function testParseXml()
    {
        $res   = XML::parseXml($this->xmlStr, new tests());
        $name  = $res['name'];
        $value = $res['value'];
        $this->assertEquals('test', $name);
        $this->assertEquals(1, $value);

        $res   = XML::parseXml($this->xmlStr, null);
        $name  = $res['name'];
        $value = $res['value'];
        $this->assertEquals('test', $name);
        $this->assertEquals(1, $value);
    }

    public function testArrayToXML()
    {
        $data = [
            'tests' => [
                'name'  => 'test',
                'value' => 1,
            ],
        ];
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tests><name>test</name><value>1</value></tests>", XML::toXML($data));
    }

    public function testObjectToXML()
    {
        $obj        = new tests();
        $obj->name  = 'test';
        $obj->value = 1;
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tests><name>test</name><value>1</value></tests>", XML::toXML($obj));
    }
}

class tests
{
    public $name  = '';
    public $value = 0;
}
