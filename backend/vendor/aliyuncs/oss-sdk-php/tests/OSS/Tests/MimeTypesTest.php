<?php

namespace OSS\Tests;

use OSS\Core\MimeTypes;

class MimeTypesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMimeType()
    {
        $this->assertEquals('application/xml', MimeTypes::getMimetype('file.xml'));
    }
}
