<?php
namespace OSS\Tests;

use OSS\Core\OssException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

class OssClientBucketStatTestTest extends TestOssClientBase
{
    public function testBucketStat()
    {
        try {
	        $content = "hello";
            $this->ossClient->putObject($this->bucket, "name-1.txt", $content);
            $this->ossClient->putObject($this->bucket, "name-2.txt", $content);
            $this->ossClient->putObject($this->bucket, "name-3.txt", $content);

			$object = "multipart-test.txt";
			$upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);

			Common::waitMetaSync();
			Common::waitMetaSync();
			Common::waitMetaSync();
            $stat = $this->ossClient->getBucketStat($this->bucket);
			$this->assertEquals(3, $stat->getObjectCount());
			$this->assertEquals(15, $stat->getStorage());
			$this->assertEquals(1, $stat->getMultipartUploadCount());

        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

}
