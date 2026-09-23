<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientObjectTest extends TestOssClientBase
{

    public function testGetObjectMeta()
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";

        try {
            $res = $this->ossClient->getObjectMeta($this->bucket, $object);
            $this->assertEquals('200', $res['info']['http_code']);
            $this->assertEquals('text/plain', $res['content-type']);
            $this->assertEquals('Accept-Encoding', $res['vary']);
            $this->assertTrue(isset($res['content-encoding']));
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $options = array(OssClient::OSS_HEADERS => array(OssClient::OSS_ACCEPT_ENCODING => 'deflate, gzip'));

        try {
            $res = $this->ossClient->getObjectMeta($this->bucket, $object, $options);
            $this->assertEquals('200', $res['info']['http_code']);
            $this->assertEquals('text/plain', $res['content-type']);
            $this->assertEquals('Accept-Encoding', $res['vary']);
            $this->assertEquals('gzip', $res['content-encoding']);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testGetObjectWithAcceptEncoding()
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        $options = array(OssClient::OSS_HEADERS => array(OssClient::OSS_ACCEPT_ENCODING => 'deflate, gzip'));

        try {
            $res = $this->ossClient->getObject($this->bucket, $object, $options);
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testGetObjectWithHeader()
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $res = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_LAST_MODIFIED => "xx"));
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }
    }

    public function testGetObjectWithIleggalEtag()
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $res = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_ETAG => "xx"));
            $this->assertEquals(file_get_contents(__FILE__), $res);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }
    }

    public function testObject()
    {
        /**
         *  Upload the local variable to bucket
         */
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
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

        try {
            $result = $this->ossClient->deleteObjects($this->bucket, "stringtype", $options);
            $this->assertEquals('stringtype', $result[0]);
        } catch (OssException $e) {
            $this->assertEquals('objects must be array', $e->getMessage());
        }

        try {
            $result = $this->ossClient->deleteObjects($this->bucket, "stringtype", $options);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('objects must be array', $e->getMessage());
        }

        try {
            $this->ossClient->uploadFile($this->bucket, $object, "notexist.txt", $options);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('notexist.txt file does not exist', $e->getMessage());
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
    }

    public function testAppendObject()
    {
        $object = "oss-php-sdk-test/append-test-object-name.txt";
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
    }

    public function testPutIllelObject()
    {
        $object = "/ilegal.txt";
        try {
            $this->ossClient->putObject($this->bucket, $object, "hi", null);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('"/ilegal.txt" object name is invalid', $e->getMessage());
        }
    }

    public function testCheckMD5()
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        $content = file_get_contents(__FILE__);
        $options = array(OssClient::OSS_CHECK_MD5 => true);

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
         * delete test object
         */
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testWithInvalidBucketName()
    {
        try {
            $this->ossClient->createBucket("abcefc/", "test-key");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('"abcefc/"bucket name is invalid', $e->getMessage());
        }
    }

    public function testGetSimplifiedObjectMeta()
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";

        try {
            $objectMeta = $this->ossClient->getSimplifiedObjectMeta($this->bucket, $object);
            $this->assertEquals(false, array_key_exists(strtolower('Content-Disposition'), $objectMeta));
            $this->assertEquals(strlen(file_get_contents(__FILE__)), $objectMeta[strtolower('Content-Length')]);
            $this->assertEquals(true, array_key_exists(strtolower('ETag'), $objectMeta));
            $this->assertEquals(true, array_key_exists(strtolower('Last-Modified'), $objectMeta));
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testUploadStream()
    {
        $object = "oss-php-sdk-test/put-from-stream.txt";
        $options = array(OssClient::OSS_CHECK_MD5 => true);
        $handle = fopen(__FILE__, 'rb');
        /**
         * Upload data to start MD5
         */
        try {
            $this->ossClient->uploadStream($this->bucket, $object, $handle, $options);
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

        $object = "oss-php-sdk-test/put-from-stream-without-md5.txt";
        $handle = fopen(__FILE__, 'rb');
        try {
            $this->ossClient->uploadStream($this->bucket, $object, $handle);
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

    public function testObjectKeyWithNonUTF8Name()
    {
        $object = "中文测试.txt";
        $hexObject = bin2hex($object);
        $gbkObject = iconv('UTF-8', 'GBK', $object);
        $hexGbkObject = bin2hex($gbkObject);
        $content = "hello world";

        $this->assertEquals("e4b8ade69687e6b58be8af952e747874", $hexObject);
        $this->assertEquals("d6d0cec4b2e2cad42e747874", $hexGbkObject);

        try {
            $this->ossClient->putObject($this->bucket, $gbkObject, $content);
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('InvalidArgument', $e->getErrorCode());
            $this->assertEquals('The characters encoding must be utf-8.', $e->getErrorMessage());
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }

        //enable object encoding check
        $config = array(
            'checkObjectEncoding' => true,
        );
        $ossClient = Common::getOssClient($config);
        try {
            $ossClient->putObject($this->bucket, $gbkObject, $content);
            $content1 = $this->ossClient->getObject($this->bucket, $object);
            $content2 = $ossClient->getObject($this->bucket, $gbkObject);
            $this->assertEquals($content, $content1);
            $this->assertEquals($content, $content2);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
        
        // ascii
        try {
            $ossClient->putObject($this->bucket, '1234', 'ascii');
            $content1 = $this->ossClient->getObject($this->bucket, '1234');
            $content2 = $ossClient->getObject($this->bucket, '1234');
            $this->assertEquals('ascii', $content1);
            $this->assertEquals('ascii', $content2);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testEncodeFilePath()
    {
        if (!OssUtil::isWin()) {
            $this->assertTrue(true);
            return;
        }

        $fileFolder = __DIR__ . DIRECTORY_SEPARATOR . "中文目录";
        $filePath1 = $fileFolder . DIRECTORY_SEPARATOR . "中文文件名1.txt";
        $filePath2 = $fileFolder . DIRECTORY_SEPARATOR . "中文文件名2.txt";

        $gbkfileFolder = iconv('UTF-8', 'GBK', $fileFolder);
        $gbkfilePath1 = iconv('UTF-8', 'GBK', $filePath1);
        $gbkfilePath2 = iconv('UTF-8', 'GBK', $filePath2);

        $hexfilePath1 = bin2hex($filePath1);
        $hexGbkfilePath2 = bin2hex($gbkfilePath2);

        $content1 = '';
        $content2 = '';
        if (version_compare(phpversion(), '7.0.0', '<')) {
            try {
                mkdir($gbkfileFolder);
            } catch (\Exception $e) {
            }
            OssUtil::generateFile($gbkfilePath1, 200 * 1024);
            OssUtil::generateFile($gbkfilePath2, 202 * 1024);
            $content1 = file_get_contents($gbkfilePath1);
            $content2 = file_get_contents($gbkfilePath2);
        } else {
            try {
                mkdir($fileFolder);
            } catch (\Exception $e) {
            }
            OssUtil::generateFile($filePath1, 200 * 1024);
            OssUtil::generateFile($filePath2, 202 * 1024);
            $content1 = file_get_contents($filePath1);
            $content2 = file_get_contents($filePath2);
        }

        try {

            // upload file
            $this->ossClient->uploadFile($this->bucket, '123', $filePath1);
            $this->ossClient->uploadFile($this->bucket, '234', $gbkfilePath2);

            $res = $this->ossClient->getObject($this->bucket, '123');
            $this->assertEquals($content1, $res);

            $res = $this->ossClient->getObject($this->bucket, '234');
            $this->assertEquals($content2, $res);

            // append file
            $position = $this->ossClient->appendFile($this->bucket, 'append-file', $filePath1, 0);
            $position = $this->ossClient->appendFile($this->bucket, 'append-file', $gbkfilePath2, $position);

            $res = $this->ossClient->getObject($this->bucket, 'append-file');
            $this->assertEquals($content1.$content2, $res);

            // multi paet
            $this->ossClient->multiuploadFile($this->bucket, 'multi-file-123', $filePath1, array(OssClient::OSS_PART_SIZE => 1));
            $this->ossClient->multiuploadFile($this->bucket, 'multi-file-234', $gbkfilePath2, array(OssClient::OSS_PART_SIZE => 1));
            $res = $this->ossClient->getObject($this->bucket, 'multi-file-123');
            $this->assertEquals($content1, $res);

            $res = $this->ossClient->getObject($this->bucket, 'multi-file-234');
            $this->assertEquals($content2, $res);

            // uploadDir
            $this->ossClient->uploadDir($this->bucket, "dir", $fileFolder);
            $options = array(
                'delimiter' => '',
                'prefix' => "dir",
            );
            $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
            $objectList = $listObjectInfo->getObjectList();
            $this->assertEquals(2, count($objectList));
            $this->assertEquals('dir/中文文件名1.txt', $objectList[0]->getKey());
            $this->assertEquals('dir/中文文件名2.txt', $objectList[1]->getKey());

            // uploadDir
            if (version_compare(phpversion(), '7.0.0', '<')) {
                $this->ossClient->uploadDir($this->bucket, "gbkdir", $gbkfileFolder);
                $options = array(
                    'delimiter' => '',
                    'prefix' => "gbkdir",
                );
                $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
                $objectList = $listObjectInfo->getObjectList();
                $this->assertEquals(2, count($objectList));
                $this->assertEquals('gbkdir/中文文件名1.txt', $objectList[0]->getKey());
                $this->assertEquals('gbkdir/中文文件名2.txt', $objectList[1]->getKey());
            }

        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            if (phpversion() < "7.0.0") {
                unlink($gbkfilePath1);
                unlink($gbkfilePath2);
                rmdir($gbkfileFolder);
            } else {
                unlink($filePath1);
                unlink($filePath2);
                rmdir($fileFolder);
            }
        } catch (\Exception $e) {
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->ossClient->putObject($this->bucket, 'oss-php-sdk-test/upload-test-object-name.txt', file_get_contents(__FILE__));
    }
}
