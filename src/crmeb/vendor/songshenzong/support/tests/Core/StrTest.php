<?php

namespace Songshenzong\Support\Test\Core;

use stdClass;
use PHPUnit\Framework\TestCase;
use Songshenzong\Support\Strings;

/**
 * Class StrTest
 *
 * @package Songshenzong\Support\Test\Core
 */
class StrTest extends TestCase
{
    /**
     * @var string
     */
    protected $xmlString = <<<ETO
<?xml version="1.0" encoding="UTF-8"?>
<books>
  <book>
    <author>Jack Herrington</author>
    <title>PHP Hacks</title>
    <publisher>O'Reilly</publisher>
  </book>
  <book>
    <author>Jack Herrington</author>
    <title>Podcasting Hacks</title>
    <publisher>O'Reilly</publisher>
  </book>
</books>
ETO;

    /**
     * @var string
     */
    protected $jsonString = '{ "tools": [ { "name":"css format" , "site":"https://songshenzong.com" }, { "name":"pwd check" , "site":"https://songshenzong.com" } ] }';

    /**
     * @var array
     */
    protected $simpleJsonArray = ['json' => true];

    /**
     * @var string
     */
    protected $simpleJsonString = '{"json":true}';

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testIsJson()
    {
        $this->assertEquals(false, Strings::isJson());
        $this->assertEquals(false, Strings::isJson(''));
        $this->assertEquals(false, Strings::isJson($this->xmlString));
        $this->assertEquals(true, Strings::isJson($this->simpleJsonString));
        $this->assertEquals(true, Strings::isJson($this->jsonString));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testIsXml()
    {
        $this->assertEquals(false, Strings::isXml(''));
        $this->assertEquals(false, Strings::isXml());
        $this->assertEquals(false, Strings::isXml($this->jsonString));
        $this->assertEquals(true, Strings::isXml($this->xmlString));
        $this->assertEquals(false, Strings::isXml($this->simpleJsonString));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testToArray()
    {
        $this->assertEquals($this->simpleJsonArray, Strings::toArray($this->simpleJsonString));
        $this->assertEquals([], Strings::toArray());
        $this->assertEquals([], Strings::toArray(''));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testToObject()
    {
        $this->assertEquals(true, Strings::toObject($this->simpleJsonString)->json);
        $this->assertObjectHasAttribute('book', Strings::toObject($this->xmlString));
        $this->assertEquals(new stdClass(), Strings::toObject());
        $this->assertEquals(new stdClass(), Strings::toObject(''));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testXmlToArray()
    {
        $this->assertArrayHasKey('book', Strings::xmlToArray($this->xmlString));
        $this->assertObjectHasAttribute('book', Strings::xmlToObject($this->xmlString));
    }
}
