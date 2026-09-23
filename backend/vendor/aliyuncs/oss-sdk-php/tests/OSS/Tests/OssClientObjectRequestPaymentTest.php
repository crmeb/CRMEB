<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;
use OSS\Model\RestoreConfig;
use OSS\Model\TaggingConfig;
use OSS\Model\Tag;
use OSS\Core\OssUtil;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientObjectRequestPaymentTest extends TestOssClientBase
{
    private $payerClient;

    public function testPayerClientWithoutRequester()
    {
        try {
            $this->payerClient->listObjects($this->bucket);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->createObjectDir($this->bucket, 'folder/');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->putObject($this->bucket, 'object', 'content');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->putSymlink($this->bucket, 'symlink', 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->getSymlink($this->bucket, 'default-symlink');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
    		$this->payerClient->uploadFile($this->bucket, 'file-object', __FILE__);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
    		$this->payerClient->appendObject($this->bucket, 'append-object', 'content', 0);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
    		$this->payerClient->appendObject($this->bucket, 'append-file', __FILE__, 0);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
    		$this->payerClient->copyObject($this->bucket, 'default-object', $this->bucket, 'copy-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->getObjectMeta($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
        }

        try {
            $this->payerClient->getSimplifiedObjectMeta($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
        }

        try {
            $this->payerClient->deleteObject($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->getObject($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->doesObjectExist($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
        }

        try {
            $this->payerClient->restoreObject($this->bucket, 'default-ia-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $config = new TaggingConfig();
            $config->addTag(new Tag("key1", "value1"));
            $this->payerClient->putObjectTagging($this->bucket, 'default-object', $config);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->getObjectTagging($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->deleteObjectTagging($this->bucket, 'default-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->initiateMultipartUpload($this->bucket, 'mup-object');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        $uploadId= $this->ossClient->initiateMultipartUpload($this->bucket, 'mup-object');

        try {
            $this->payerClient->listParts($this->bucket, 'mup-object', $uploadId);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->abortMultipartUpload($this->bucket, 'mup-object', $uploadId);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->listMultipartUploads($this->bucket);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }

        try {
            $this->payerClient->multiuploadFile($this->bucket, 'mup-file', __FILE__);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('AccessDenied', $e->getErrorCode());
        }
    }

    public function testObjectOperationsWithRequester()
    {
        $options = array(
            OssClient::OSS_HEADERS => array(
                OssClient::OSS_REQUEST_PAYER => 'requester',
        ));

        try {
            $this->payerClient->listObjects($this->bucket, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->createObjectDir($this->bucket, 'folder/', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->putObject($this->bucket, 'object', 'content', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->putSymlink($this->bucket, 'symlink', 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->getSymlink($this->bucket, 'default-symlink', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
    		$this->payerClient->uploadFile($this->bucket, 'file-object', __FILE__, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
    		$this->payerClient->appendObject($this->bucket, 'append-object', 'content', 0, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
    		$this->payerClient->appendObject($this->bucket, 'append-file', __FILE__, 0, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
    		$this->payerClient->copyObject($this->bucket, 'default-object', $this->bucket, 'copy-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->getObjectMeta($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->getSimplifiedObjectMeta($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->getObject($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->putObject($this->bucket, 'test-object', 'content', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->deleteObject($this->bucket, 'test-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->doesObjectExist($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $ia_options = array(
            OssClient::OSS_HEADERS => array(
                'x-oss-storage-class' => 'Archive',
        ));
        $this->ossClient->putObject($this->bucket, 'default-Archive-object', 'content', $ia_options);
        try {
            $this->payerClient->restoreObject($this->bucket, 'default-Archive-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $config = new TaggingConfig();
            $config->addTag(new Tag("key1", "value1"));
            $this->payerClient->putObjectTagging($this->bucket, 'default-object', $config, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->getObjectTagging($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $this->payerClient->deleteObjectTagging($this->bucket, 'default-object', $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }
    
    public function testMultipartOperationsWithRequester()
    {
        $options = array(
            OssClient::OSS_HEADERS => array(
                OssClient::OSS_REQUEST_PAYER => 'requester',
        ));

        $object = "mpu/multipart-test.txt";
        /**
         *  step 1. 初始化一个分块上传事件, 也就是初始化上传Multipart, 获取upload id
         */
        try {
            $upload_id = $this->payerClient->initiateMultipartUpload($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /*
         * step 2. 上传分片
         */
        $part_size = 1 * 1024 * 1024;
        $upload_file = __FILE__;
        $upload_filesize = sprintf('%u',filesize($upload_file));
        $pieces = $this->payerClient->generateMultiuploadParts($upload_filesize, $part_size);
        $response_upload_part = array();
        $upload_position = 0;
        $is_check_md5 = false;
        foreach ($pieces as $i => $piece) {
            $from_pos = $upload_position + (integer)$piece[OssClient::OSS_SEEK_TO];
            $to_pos = (integer)$piece[OssClient::OSS_LENGTH] + $from_pos - 1;
            $up_options = array(
                OssClient::OSS_FILE_UPLOAD => $upload_file,
                OssClient::OSS_PART_NUM => ($i + 1),
                OssClient::OSS_SEEK_TO => $from_pos,
                OssClient::OSS_LENGTH => $to_pos - $from_pos + 1,
                OssClient::OSS_CHECK_MD5 => $is_check_md5,
                OssClient::OSS_HEADERS => array(
                    OssClient::OSS_REQUEST_PAYER => 'requester',
                ),
            );

            //2. 将每一分片上传到OSS
            try {
                $response_upload_part[] = $this->ossClient->uploadPart($this->bucket, $object, $upload_id, $up_options);
            } catch (OssException $e) {
                $this->assertFalse(true);
            }
        }
        $upload_parts = array();
        foreach ($response_upload_part as $i => $eTag) {
            $upload_parts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }

        try {
            $listPartsInfo = $this->payerClient->listParts($this->bucket, $object, $upload_id, $options);
            $this->assertNotNull($listPartsInfo);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $uploads = $this->payerClient->listMultipartUploads($this->bucket, $options);
            $this->assertNotNull($uploads);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        /**
         * step 3.
         */
        try {
            $this->payerClient->completeMultipartUpload($this->bucket, $object, $upload_id, $upload_parts, $options);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testMiscOperationsWithRequester()
    {
        //use multipart
        $options = array(
            OssClient::OSS_PART_SIZE => 1,
            OssClient::OSS_HEADERS => array(
                OssClient::OSS_REQUEST_PAYER => 'requester',
        ));

        $bigFileName = __DIR__ . DIRECTORY_SEPARATOR . "/bigfile.tmp";
        OssUtil::generateFile($bigFileName, 256 * 1024);
        $object = 'mpu/multipart-bigfile-test.tmp';
        try {
            $this->ossClient->multiuploadFile($this->bucket, $object, $bigFileName, $options);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        //use uploadfile
        $options = array(
            OssClient::OSS_PART_SIZE => 1024*1024,
            OssClient::OSS_HEADERS => array(
                OssClient::OSS_REQUEST_PAYER => 'requester',
        ));

        try {
            $this->ossClient->multiuploadFile($this->bucket, $object, $bigFileName, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        unlink($bigFileName);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->payerClient = new OssClient(
            Common::getPayerAccessKeyId(),
            Common::getPayerAccessKeySecret(),
            Common::getEndpoint(), false);

        $policy = '{"Version":"1","Statement":[{"Action":["oss:*"],"Effect": "Allow",'.
                  '"Principal":["' . Common::getPayerUid() . '"],'.
                  '"Resource": ["acs:oss:*:*:' . $this->bucket . '","acs:oss:*:*:' . $this->bucket . '/*"]}]}';

        $this->ossClient->putBucketPolicy($this->bucket, $policy);
        $this->ossClient->putBucketRequestPayment($this->bucket, 'Requester');
        $this->ossClient->putObject($this->bucket, "default-object", "");
        $this->ossClient->putSymlink($this->bucket, "default-symlink", "default-object");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
