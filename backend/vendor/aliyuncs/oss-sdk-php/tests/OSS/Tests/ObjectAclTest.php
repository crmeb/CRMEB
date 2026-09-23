<?php

namespace OSS\Tests;

require_once __DIR__ . '/Common.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

class ObjectAclTest extends TestOssClientBase
{
    public function testGetSet()
    {
        $client = $this->ossClient;
        $bucket = $this->bucket;

        $object = 'test/object-acl';
        $client->deleteObject($bucket, $object);
        $client->putObject($bucket, $object, "hello world");

        $acl = $client->getObjectAcl($bucket, $object);
        $this->assertEquals('default', $acl);

        $client->putObjectAcl($bucket, $object, 'public-read');
        $acl = $client->getObjectAcl($bucket, $object);
        $this->assertEquals('public-read', $acl);

        $content = $client->getObject($bucket, $object);
        $this->assertEquals('hello world', $content);
    }
}
