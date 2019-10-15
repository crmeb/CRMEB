<?php

namespace OSS\Tests;


use OSS\Result\ExistResult;
use OSS\Http\ResponseCore;
use OSS\Core\OssException;

class ExistResultTest extends \PHPUnit_Framework_TestCase
{
    public function testParseValid200()
    {
        $response = new ResponseCore(array(), "", 200);
        $result = new ExistResult($response);
        $this->assertTrue($result->isOK());
        $this->assertEquals($result->getData(), true);
    }

    public function testParseInvalid404()
    {
        $response = new ResponseCore(array(), "", 404);
        $result = new ExistResult($response);
        $this->assertTrue($result->isOK());
        $this->assertEquals($result->getData(), false);
    }

    public function testInvalidResponse()
    {
        $response = new ResponseCore(array(), "", 300);
        try {
            new ExistResult($response);
            $this->assertTrue(false);
        } catch (OssException $e) {

        }
    }
}
