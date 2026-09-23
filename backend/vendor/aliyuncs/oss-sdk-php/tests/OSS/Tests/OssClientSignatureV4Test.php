<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Credentials\StaticCredentialsProvider;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientSignatureV4Test extends TestOssClientBase
{
    /**
     * @var OssClient
     */
    protected $stsOssClient;

    public function testBaseInterfaceForObject()
    {

        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $this->ossClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        // test GetObjectMeta
        try {
            $res = $this->ossClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('200', $res['info']['http_code']);
            $this->assertEquals('text/plain', $res['content-type']);
            $this->assertEquals('Accept-Encoding', $res['vary']);
            $this->assertFalse(isset($res['Content-Encoding']));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $options = array(OssClient::OSS_HEADERS => array(OssClient::OSS_ACCEPT_ENCODING => 'deflate, gzip'));
        try {
            $res = $this->ossClient->getObjectMeta($this->bucket, $object, $options);
            $this->assertEquals('200', $res['info']['http_code']);
            $this->assertEquals('text/plain', $res['content-type']);
            $this->assertEquals('Accept-Encoding', $res['vary']);
            $this->assertFalse(isset($res['content-length']));
            $this->assertEquals('gzip', $res['content-encoding']);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $options = array(OssClient::OSS_HEADERS => array(OssClient::OSS_ACCEPT_ENCODING => 'deflate, gzip'));
        try {
            $res = $this->ossClient->getObject($this->bucket, $object, $options);
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            $res = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_LAST_MODIFIED => "xx"));
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }

        try {
            $res = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_ETAG => "xx"));
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }

        $content = file_get_contents(__FILE__);
        $options = array(
            OssClient::OSS_LENGTH => strlen($content),
            OssClient::OSS_HEADERS => array(
                'Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT',
                'Cache-Control' => 'no-cache',
                'Content-Disposition' => 'attachment;filename=oss_download.log',
                'Content-Language' => 'zh-CN',
                'x-oss-server-side-encryption' => 'AES256',
                'x-oss-meta-self-define-title' => 'user define meta info',
            ),
        );

        try {
            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $result = $this->ossClient->deleteObjects($this->bucket, "stringtype", $options);
            $this->assertEquals('stringtype', $result[0]);
        } catch (OssException $e) {
            $this->assertEquals('objects must be array', $e->getMessage());
        }

        try {
            $this->ossClient->uploadFile($this->bucket, $object, "notexist.txt", $options);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('notexist.txt file does not exist', $e->getMessage());
        }

        $content = file_get_contents(__FILE__);
        $options = array(
            OssClient::OSS_LENGTH => strlen($content),
            OssClient::OSS_HEADERS => array(
                'Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT',
                'Cache-Control' => 'no-cache',
                'Content-Disposition' => 'attachment;filename=oss_download.log',
                'Content-Language' => 'zh-CN',
                'x-oss-server-side-encryption' => 'AES256',
                'x-oss-meta-self-define-title' => 'user define meta info',
            ),
        );

        try {
            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * GetObject to the local variable and check for match
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * GetObject first five bytes
         */
        try {
            $options = array(OssClient::OSS_RANGE => '0-4');
            $content = $this->ossClient->getObject($this->bucket, $object, $options);
            $this->assertEquals($content, '<?php');
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Upload the local file to object
         */
        try {
            $this->ossClient->uploadFile($this->bucket, $object, __FILE__);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Download the file to the local variable and check for match.
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Download the file to the local file
         */
        $localfile = "upload-test-object-name.txt";
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile,
        );

        try {
            $this->ossClient->getObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        $this->assertTrue(file_get_contents($localfile) === file_get_contents(__FILE__));
        if (file_exists($localfile)) {
            unlink($localfile);
        }

        /**
         * Download the file to the local file. no such key
         */
        $localfile = "upload-test-object-name-no-such-key.txt";
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile,
        );

        try {
            $this->ossClient->getObject($this->bucket, $object . "no-such-key", $options);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            $this->assertFalse(file_exists($localfile));
            if (strpos($e, "The specified key does not exist") == false) {
                $this->assertTrue(true);
            }
        }

        /**
         * Download the file to the content. no such key
         */
        try {
            $result = $this->ossClient->getObject($this->bucket, $object . "no-such-key");
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "The specified key does not exist") == false) {
                $this->assertTrue(true);
            }
        }

        /**
         * Copy object
         */
        $to_bucket = $this->bucket;
        $to_object = $object . '.copy';
        $options = array();
        try {
            $result = $this->ossClient->copyObject($this->bucket, $object, $to_bucket, $to_object, $options);
            $this->assertFalse(empty($result));
            $this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($result[0]));
            $this->assertEquals(strlen("\"5B3C1A2E053D763E1B002CC607C5A0FE\""), strlen($result[1]));
        } catch (OssException $e) {
            $this->assertFalse(true);
            var_dump($e->getMessage());

        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $to_object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * List the files in your bucket.
         */
        $prefix = '';
        $delimiter = '/';
        $next_marker = '';
        $maxkeys = 1000;
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $next_marker,
        );

        try {
            $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
            $objectList = $listObjectInfo->getObjectList();
            $prefixList = $listObjectInfo->getPrefixList();
            $this->assertNotNull($objectList);
            $this->assertNotNull($prefixList);
            $this->assertTrue(is_array($objectList));
            $this->assertTrue(is_array($prefixList));

        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        /**
         * Set the meta information for the file
         */
        $from_bucket = $this->bucket;
        $from_object = "oss-php-sdk-test/upload-test-object-name.txt";
        $to_bucket = $from_bucket;
        $to_object = $from_object;
        $copy_options = array(
            OssClient::OSS_HEADERS => array(
                'Expires' => '2012-10-01 08:00:00',
                'Content-Disposition' => 'attachment; filename="xxxxxx"',
            ),
        );
        try {
            $this->ossClient->copyObject($from_bucket, $from_object, $to_bucket, $to_object, $copy_options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Get the meta information for the file
         */
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $objectMeta = $this->ossClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('attachment; filename="xxxxxx"', $objectMeta[strtolower('Content-Disposition')]);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         *  Delete single file
         */
        $object = "oss-php-sdk-test/upload-test-object-name.txt";

        try {
            $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object));
            $this->ossClient->deleteObject($this->bucket, $object);
            $this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         *  Delete multiple files
         */
        $object1 = "oss-php-sdk-test/upload-test-object-name.txt";
        $object2 = "oss-php-sdk-test/upload-test-object-name.txt.copy";
        $list = array($object1, $object2);
        try {
            $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object2));

            $result = $this->ossClient->deleteObjects($this->bucket, $list);
            $this->assertEquals($list[0], $result[0]);
            $this->assertEquals($list[1], $result[1]);

            $result = $this->ossClient->deleteObjects($this->bucket, $list, array('quiet' => 'true'));
            $this->assertEquals(array(), $result);
            $this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object2));

            $this->ossClient->putObject($this->bucket, $object, $content);
            $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object));
            $result = $this->ossClient->deleteObjects($this->bucket, $list, array('quiet' => true));
            $this->assertEquals(array(), $result);
            $this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object));
        } catch (OssException $e) {

            $this->assertFalse(true);
        }

        $content_array = array('Hello OSS', 'Hi OSS', 'OSS OK');

        /**
         * Append the upload string
         */
        try {
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[0], 0);
            $this->assertEquals($position, strlen($content_array[0]));
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[1], $position);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[2], $position, array(OssClient::OSS_LENGTH => strlen($content_array[2])));
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[2]));
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }

        /**
         * Check if the content is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, implode($content_array));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        /**
         * Delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Append the upload of invalid local files
         */
        try {
            $position = $this->ossClient->appendFile($this->bucket, $object, "invalid-file-path", 0);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
        }

        /**
         * Append the upload of local files
         */
        try {
            $position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, 0);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)));
            $position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, $position);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)) * 2);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__) . file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        $options = array(
            OssClient::OSS_HEADERS => array(
                'Expires' => '2012-10-01 08:00:00',
                'Content-Disposition' => 'attachment; filename="xxxxxx"',
            ),
        );

        /**
         * Append upload with option
         */
        try {
            $position = $this->ossClient->appendObject($this->bucket, $object, "Hello OSS, ", 0, $options);
            $position = $this->ossClient->appendObject($this->bucket, $object, "Hi OSS.", $position);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Get the meta information for the file
         */
        try {
            $objectMeta = $this->ossClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('attachment; filename="xxxxxx"', $objectMeta[strtolower('Content-Disposition')]);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $options = array(OssClient::OSS_CHECK_MD5 => true);

        $content = file_get_contents(__FILE__);
        /**
         * Upload data to start MD5
         */
        try {
            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Upload file to start MD5
         */
        try {
            $this->ossClient->uploadFile($this->bucket, $object, __FILE__, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $object = "oss-php-sdk-test/append-test-object-name.txt";
        $content_array = array('Hello OSS', 'Hi OSS', 'OSS OK');
        $options = array(OssClient::OSS_CHECK_MD5 => true);

        /**
         * Append the upload string
         */
        try {
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[0], 0, $options);
            $this->assertEquals($position, strlen($content_array[0]));
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[1], $position, $options);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
            $position = $this->ossClient->appendObject($this->bucket, $object, $content_array[2], $position, $options);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[1]));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the content is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, implode($content_array));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Append upload of local files
         */
        try {
            $position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, 0, $options);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)));
            $position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, $position, $options);
            $this->assertEquals($position, (sprintf('%u', filesize(__FILE__)) * 2));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__) . file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $options = array(
            OssClient::OSS_HEADERS => array(
                "Content-Type" => "application/octet-stream",
                "name" => "aliyun",
                "email" => "aliyun@aliyun.com",
            ),
            OssClient::OSS_ADDITIONAL_HEADERS => array('name', 'email')
        );
        try {
            $this->ossClient->uploadFile($this->bucket, $object, __FILE__, $options);
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }

        try {
            $content = $this->ossClient->getObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


    }

    public function testObjectKeyWithQuestionMark()
    {
        /**
         *  Upload the local variable to bucket
         */
        $object = "oss-php-sdk-test/??/upload-test-object-name???123??123??.txt";
        $content = file_get_contents(__FILE__);
        $options = array(
            OssClient::OSS_LENGTH => strlen($content),
            OssClient::OSS_HEADERS => array(
                'Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT',
                'Cache-Control' => 'no-cache',
                'Content-Disposition' => 'attachment;filename=oss_download.log',
                'Content-Language' => 'zh-CN',
                'x-oss-server-side-encryption' => 'AES256',
                'x-oss-meta-self-define-title' => 'user define meta info',
            ),
        );

        try {
            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $this->ossClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * GetObject to the local variable and check for match
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * GetObject first five bytes
         */
        try {
            $options = array(OssClient::OSS_RANGE => '0-4');
            $content = $this->ossClient->getObject($this->bucket, $object, $options);
            $this->assertEquals($content, '<?php');
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        /**
         * Upload the local file to object
         */
        try {
            $this->ossClient->uploadFile($this->bucket, $object, __FILE__);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Download the file to the local variable and check for match.
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Copy object
         */
        $to_bucket = $this->bucket;
        $to_object = $object . '.copy';
        $options = array();
        try {
            $result = $this->ossClient->copyObject($this->bucket, $object, $to_bucket, $to_object, $options);
            $this->assertFalse(empty($result));
            $this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($result[0]));
            $this->assertEquals(strlen("\"5B3C1A2E053D763E1B002CC607C5A0FE\""), strlen($result[1]));
        } catch (OssException $e) {
            $this->assertFalse(true);
            var_dump($e->getMessage());

        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->ossClient->getObject($this->bucket, $to_object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        try {
            $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object));
            $this->ossClient->deleteObject($this->bucket, $object);
            $this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testBaseInterfaceForBucekt()
    {
        $this->ossClient->createBucket($this->bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);

        $bucketListInfo = $this->ossClient->listBuckets();
        $this->assertNotNull($bucketListInfo);

        $bucketList = $bucketListInfo->getBucketList();
        $this->assertTrue(is_array($bucketList));
        $this->assertGreaterThan(0, count($bucketList));

        $this->ossClient->putBucketAcl($this->bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        Common::waitMetaSync();
        $this->assertEquals($this->ossClient->getBucketAcl($this->bucket), OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);

        $this->assertTrue($this->ossClient->doesBucketExist($this->bucket));
        $this->assertFalse($this->ossClient->doesBucketExist($this->bucket . '-notexist'));

        $this->assertNotNull($this->ossClient->getBucketLocation($this->bucket));

        $res = $this->ossClient->getBucketMeta($this->bucket);
        $this->assertEquals('200', $res['info']['http_code']);
        $this->assertNotNull($res['x-oss-bucket-region']);
    }

    public function testBaseInterfaceForObjectWithSts()
    {

        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $this->stsOssClient->putObject($this->bucket, $object, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        // test GetObjectMeta
        try {
            $res = $this->stsOssClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('200', $res['info']['http_code']);
            $this->assertEquals('text/plain', $res['content-type']);
            $this->assertEquals('Accept-Encoding', $res['vary']);
            $this->assertTrue(isset($res['content-encoding']));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $options = array(OssClient::OSS_HEADERS => array(OssClient::OSS_ACCEPT_ENCODING => 'deflate, gzip'));
        try {
            $res = $this->stsOssClient->getObjectMeta($this->bucket, $object, $options);
            $this->assertEquals('200', $res['info']['http_code']);
            $this->assertEquals('text/plain', $res['content-type']);
            $this->assertEquals('Accept-Encoding', $res['vary']);
            $this->assertEquals('gzip', $res['content-encoding']);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $options = array(OssClient::OSS_HEADERS => array(OssClient::OSS_ACCEPT_ENCODING => 'deflate, gzip'));
        try {
            $res = $this->stsOssClient->getObject($this->bucket, $object, $options);
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        try {
            $res = $this->stsOssClient->getObject($this->bucket, $object, array(OssClient::OSS_LAST_MODIFIED => "xx"));
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }

        try {
            $res = $this->stsOssClient->getObject($this->bucket, $object, array(OssClient::OSS_ETAG => "xx"));
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }

        $content = file_get_contents(__FILE__);
        $options = array(
            OssClient::OSS_LENGTH => strlen($content),
            OssClient::OSS_HEADERS => array(
                'Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT',
                'Cache-Control' => 'no-cache',
                'Content-Disposition' => 'attachment;filename=oss_download.log',
                'Content-Language' => 'zh-CN',
                'x-oss-server-side-encryption' => 'AES256',
                'x-oss-meta-self-define-title' => 'user define meta info',
            ),
        );

        try {
            $this->stsOssClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $this->stsOssClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $result = $this->stsOssClient->deleteObjects($this->bucket, "stringtype", $options);
            $this->assertEquals('stringtype', $result[0]);
        } catch (OssException $e) {
            $this->assertEquals('objects must be array', $e->getMessage());
        }

        try {
            $result = $this->stsOssClient->deleteObjects($this->bucket, "stringtype", $options);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('objects must be array', $e->getMessage());
        }

        try {
            $this->stsOssClient->uploadFile($this->bucket, $object, "notexist.txt", $options);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('notexist.txt file does not exist', $e->getMessage());
        }

        $content = file_get_contents(__FILE__);
        $options = array(
            OssClient::OSS_LENGTH => strlen($content),
            OssClient::OSS_HEADERS => array(
                'Expires' => 'Fri, 28 Feb 2020 05:38:42 GMT',
                'Cache-Control' => 'no-cache',
                'Content-Disposition' => 'attachment;filename=oss_download.log',
                'Content-Language' => 'zh-CN',
                'x-oss-server-side-encryption' => 'AES256',
                'x-oss-meta-self-define-title' => 'user define meta info',
            ),
        );

        try {
            $this->stsOssClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $this->stsOssClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        /**
         * GetObject to the local variable and check for match
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * GetObject first five bytes
         */
        try {
            $options = array(OssClient::OSS_RANGE => '0-4');
            $content = $this->stsOssClient->getObject($this->bucket, $object, $options);
            $this->assertEquals($content, '<?php');
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        /**
         * Upload the local file to object
         */
        try {
            $this->stsOssClient->uploadFile($this->bucket, $object, __FILE__);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Download the file to the local variable and check for match.
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Download the file to the local file
         */
        $localfile = "upload-test-object-name.txt";
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile,
        );

        try {
            $this->stsOssClient->getObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        $this->assertTrue(file_get_contents($localfile) === file_get_contents(__FILE__));
        if (file_exists($localfile)) {
            unlink($localfile);
        }

        /**
         * Download the file to the local file. no such key
         */
        $localfile = "upload-test-object-name-no-such-key.txt";
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile,
        );

        try {
            $this->stsOssClient->getObject($this->bucket, $object . "no-such-key", $options);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            $this->assertFalse(file_exists($localfile));
            if (strpos($e, "The specified key does not exist") == false) {
                $this->assertTrue(true);
            }
        }

        /**
         * Download the file to the content. no such key
         */
        try {
            $result = $this->stsOssClient->getObject($this->bucket, $object . "no-such-key");
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
            if (strpos($e, "The specified key does not exist") == false) {
                $this->assertTrue(true);
            }
        }

        /**
         * Copy object
         */
        $to_bucket = $this->bucket;
        $to_object = $object . '.copy';
        $options = array();
        try {
            $result = $this->stsOssClient->copyObject($this->bucket, $object, $to_bucket, $to_object, $options);
            $this->assertFalse(empty($result));
            $this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($result[0]));
            $this->assertEquals(strlen("\"5B3C1A2E053D763E1B002CC607C5A0FE\""), strlen($result[1]));
        } catch (OssException $e) {
            $this->assertFalse(true);
            var_dump($e->getMessage());

        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $to_object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * List the files in your bucket.
         */
        $prefix = '';
        $delimiter = '/';
        $next_marker = '';
        $maxkeys = 1000;
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $next_marker,
        );

        try {
            $listObjectInfo = $this->stsOssClient->listObjects($this->bucket, $options);
            $objectList = $listObjectInfo->getObjectList();
            $prefixList = $listObjectInfo->getPrefixList();
            $this->assertNotNull($objectList);
            $this->assertNotNull($prefixList);
            $this->assertTrue(is_array($objectList));
            $this->assertTrue(is_array($prefixList));

        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        /**
         * Set the meta information for the file
         */
        $from_bucket = $this->bucket;
        $from_object = "oss-php-sdk-test/upload-test-object-name.txt";
        $to_bucket = $from_bucket;
        $to_object = $from_object;
        $copy_options = array(
            OssClient::OSS_HEADERS => array(
                'Expires' => '2012-10-01 08:00:00',
                'Content-Disposition' => 'attachment; filename="xxxxxx"',
            ),
        );
        try {
            $this->stsOssClient->copyObject($from_bucket, $from_object, $to_bucket, $to_object, $copy_options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Get the meta information for the file
         */
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $objectMeta = $this->stsOssClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('attachment; filename="xxxxxx"', $objectMeta[strtolower('Content-Disposition')]);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         *  Delete single file
         */
        $object = "oss-php-sdk-test/upload-test-object-name.txt";

        try {
            $this->assertTrue($this->stsOssClient->doesObjectExist($this->bucket, $object));
            $this->stsOssClient->deleteObject($this->bucket, $object);
            $this->assertFalse($this->stsOssClient->doesObjectExist($this->bucket, $object));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         *  Delete multiple files
         */
        $object1 = "oss-php-sdk-test/upload-test-object-name.txt";
        $object2 = "oss-php-sdk-test/upload-test-object-name.txt.copy";
        $list = array($object1, $object2);
        try {
            $this->assertTrue($this->stsOssClient->doesObjectExist($this->bucket, $object2));

            $result = $this->stsOssClient->deleteObjects($this->bucket, $list);
            $this->assertEquals($list[0], $result[0]);
            $this->assertEquals($list[1], $result[1]);

            $result = $this->stsOssClient->deleteObjects($this->bucket, $list, array('quiet' => 'true'));
            $this->assertEquals(array(), $result);
            $this->assertFalse($this->stsOssClient->doesObjectExist($this->bucket, $object2));

            $this->stsOssClient->putObject($this->bucket, $object, $content);
            $this->assertTrue($this->stsOssClient->doesObjectExist($this->bucket, $object));
            $result = $this->stsOssClient->deleteObjects($this->bucket, $list, array('quiet' => true));
            $this->assertEquals(array(), $result);
            $this->assertFalse($this->stsOssClient->doesObjectExist($this->bucket, $object));
        } catch (OssException $e) {

            $this->assertFalse(true);
        }

        $content_array = array('Hello OSS', 'Hi OSS', 'OSS OK');

        /**
         * Append the upload string
         */
        try {
            $position = $this->stsOssClient->appendObject($this->bucket, $object, $content_array[0], 0);
            $this->assertEquals($position, strlen($content_array[0]));
            $position = $this->stsOssClient->appendObject($this->bucket, $object, $content_array[1], $position);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
            $position = $this->stsOssClient->appendObject($this->bucket, $object, $content_array[2], $position, array(OssClient::OSS_LENGTH => strlen($content_array[2])));
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[2]));
        } catch (OssException $e) {
            print_r($e->getMessage());
            $this->assertFalse(true);
        }

        /**
         * Check if the content is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, implode($content_array));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        /**
         * Delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Append the upload of invalid local files
         */
        try {
            $position = $this->stsOssClient->appendFile($this->bucket, $object, "invalid-file-path", 0);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertTrue(true);
        }

        /**
         * Append the upload of local files
         */
        try {
            $position = $this->stsOssClient->appendFile($this->bucket, $object, __FILE__, 0);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)));
            $position = $this->stsOssClient->appendFile($this->bucket, $object, __FILE__, $position);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)) * 2);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__) . file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }


        $options = array(
            OssClient::OSS_HEADERS => array(
                'Expires' => '2012-10-01 08:00:00',
                'Content-Disposition' => 'attachment; filename="xxxxxx"',
            ),
        );

        /**
         * Append upload with option
         */
        try {
            $position = $this->stsOssClient->appendObject($this->bucket, $object, "Hello OSS, ", 0, $options);
            $position = $this->stsOssClient->appendObject($this->bucket, $object, "Hi OSS.", $position);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Get the meta information for the file
         */
        try {
            $objectMeta = $this->stsOssClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('attachment; filename="xxxxxx"', $objectMeta[strtolower('Content-Disposition')]);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $options = array(OssClient::OSS_CHECK_MD5 => true);

        $content = file_get_contents(__FILE__);
        /**
         * Upload data to start MD5
         */
        try {
            $this->stsOssClient->putObject($this->bucket, $object, $content, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Upload file to start MD5
         */
        try {
            $this->stsOssClient->uploadFile($this->bucket, $object, __FILE__, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $object = "oss-php-sdk-test/append-test-object-name.txt";
        $content_array = array('Hello OSS', 'Hi OSS', 'OSS OK');
        $options = array(OssClient::OSS_CHECK_MD5 => true);

        /**
         * Append the upload string
         */
        try {
            $position = $this->stsOssClient->appendObject($this->bucket, $object, $content_array[0], 0, $options);
            $this->assertEquals($position, strlen($content_array[0]));
            $position = $this->stsOssClient->appendObject($this->bucket, $object, $content_array[1], $position, $options);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
            $position = $this->stsOssClient->appendObject($this->bucket, $object, $content_array[2], $position, $options);
            $this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[1]));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the content is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, implode($content_array));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Append upload of local files
         */
        try {
            $position = $this->stsOssClient->appendFile($this->bucket, $object, __FILE__, 0, $options);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)));
            $position = $this->stsOssClient->appendFile($this->bucket, $object, __FILE__, $position, $options);
            $this->assertEquals($position, sprintf('%u', filesize(__FILE__)) * 2);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * Check if the replication is the same
         */
        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object);
            $this->assertEquals($content, file_get_contents(__FILE__) . file_get_contents(__FILE__));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        $options = array(
            OssClient::OSS_HEADERS => array(
                "Content-Type" => "application/octet-stream",
                "name" => "aliyun",
                "email" => "aliyun@aliyun.com",
            ),
            OssClient::OSS_ADDITIONAL_HEADERS => array('name', 'email')
        );
        try {
            $this->stsOssClient->uploadFile($this->bucket, $object, __FILE__, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $content = $this->stsOssClient->getObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        /**
         * delete test object
         */
        try {
            $this->stsOssClient->deleteObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

    }

    public function testBaseInterfaceForBucektWithSts()
    {
        $options = array(
            OssClient::OSS_HEADERS => array(
                "Content-Type" => "application/octet-stream",
                "name" => "aliyun",
                "email" => "aliyun@aliyun.com",
            ),
            OssClient::OSS_ADDITIONAL_HEADERS => array('name', 'email')
        );
        $this->stsOssClient->createBucket($this->bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE, $options);

        $bucketListInfo = $this->stsOssClient->listBuckets($options);
        $this->assertNotNull($bucketListInfo);

        $bucketList = $bucketListInfo->getBucketList();
        $this->assertTrue(is_array($bucketList));
        $this->assertGreaterThan(0, count($bucketList));

        $this->stsOssClient->putBucketAcl($this->bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE, $options);
        Common::waitMetaSync();
        $this->assertEquals($this->stsOssClient->getBucketAcl($this->bucket), OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);

        $this->assertTrue($this->stsOssClient->doesBucketExist($this->bucket));
        $this->assertFalse($this->stsOssClient->doesBucketExist($this->bucket . '-notexist'));

        $this->assertNotNull($this->stsOssClient->getBucketLocation($this->bucket));

        $res = $this->stsOssClient->getBucketMeta($this->bucket, $options);
        $this->assertEquals('200', $res['info']['http_code']);
        $this->assertNotNull($res['x-oss-bucket-region']);
    }

    protected function setUp(): void
    {
        $config = array(
            'signatureVersion' => OssClient::OSS_SIGNATURE_VERSION_V4
        );
        $this->bucket = Common::getBucketName() . '-' . time();
        $this->ossClient = Common::getOssClient($config);
        $this->ossClient->createBucket($this->bucket);
        Common::waitMetaSync();
        $this->stsOssClient = Common::getStsOssClient($config);
        Common::waitMetaSync();
    }

    protected function tearDown(): void
    {
        if (!$this->ossClient->doesBucketExist($this->bucket)) {
            return;
        }

        $objects = $this->ossClient->listObjects(
            $this->bucket, array('max-keys' => 1000, 'delimiter' => ''))->getObjectList();
        $keys = array();
        foreach ($objects as $obj) {
            $keys[] = $obj->getKey();
        }
        if (count($keys) > 0) {
            $this->ossClient->deleteObjects($this->bucket, $keys);
        }
        $uploads = $this->ossClient->listMultipartUploads($this->bucket)->getUploads();
        foreach ($uploads as $up) {
            $this->ossClient->abortMultipartUpload($this->bucket, $up->getKey(), $up->getUploadId());
        }

        $this->ossClient->deleteBucket($this->bucket);
    }
}
