<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;
use OSS\Core\OssUtil;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientMultipartUploadTest extends TestOssClientBase
{
    public function testInvalidDir()
    {
        try {
            $this->ossClient->uploadDir($this->bucket, "", "abc/ds/s/s/notexitst");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals("parameter error: abc/ds/s/s/notexitst is not a directory, please check it", $e->getMessage());
        }

    }

    public function testMultipartUploadBigFile()
    {
        $bigFileName = __DIR__ . DIRECTORY_SEPARATOR . "/bigfile.tmp";
        $localFilename = __DIR__ . DIRECTORY_SEPARATOR . "/localfile.tmp";
        OssUtil::generateFile($bigFileName, 6 * 1024 * 1024);
        $object = 'mpu/multipart-bigfile-test.tmp';
        try {
            $this->ossClient->multiuploadFile($this->bucket, $object, $bigFileName, array(OssClient::OSS_PART_SIZE => 1));
            $options = array(OssClient::OSS_FILE_DOWNLOAD => $localFilename);
            $this->ossClient->getObject($this->bucket, $object, $options);
            $this->assertEquals(md5_file($bigFileName), md5_file($localFilename));
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertFalse(true);
        }
        unlink($bigFileName);
        unlink($localFilename);
    }
    
    public function testMultipartUploadBigFileWithMD5Check()
    {
    	$bigFileName = __DIR__ . DIRECTORY_SEPARATOR . "/bigfile.tmp";
    	$localFilename = __DIR__ . DIRECTORY_SEPARATOR . "/localfile.tmp";
    	OssUtil::generateFile($bigFileName, 6 * 1024 * 1024);
    	$object = 'mpu/multipart-bigfile-test.tmp';
    	$options = array(
    			OssClient::OSS_CHECK_MD5 => true,
    			OssClient::OSS_PART_SIZE => 1,
    	);
    	try {
    		$this->ossClient->multiuploadFile($this->bucket, $object, $bigFileName, $options);
    		$options = array(OssClient::OSS_FILE_DOWNLOAD => $localFilename);
    		$this->ossClient->getObject($this->bucket, $object, $options);
    		$this->assertEquals(md5_file($bigFileName), md5_file($localFilename));
    	} catch (OssException $e) {
    		var_dump($e->getMessage());
    		$this->assertFalse(true);
    	}
    	unlink($bigFileName);
    	unlink($localFilename);
    }

    public function testCopyPart()
    {
        $object = "mpu/multipart-test.txt";
        $copiedObject = "mpu/multipart-test.txt.copied";
        $this->ossClient->putObject($this->bucket, $copiedObject, file_get_contents(__FILE__));
        /**
         *  step 1. 初始化一个分块上传事件, 也就是初始化上传Multipart, 获取upload id
         */
        try {
            $upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        /*
         * step 2. uploadPartCopy
         */
        $copyId = 1;
        $eTag = $this->ossClient->uploadPartCopy($this->bucket, $copiedObject, $this->bucket, $object, $copyId, $upload_id);
        $upload_parts[] = array(
            'PartNumber' => $copyId,
            'ETag' => $eTag,
        );

        try {
            $listPartsInfo = $this->ossClient->listParts($this->bucket, $object, $upload_id);
            $this->assertNotNull($listPartsInfo);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        /**
         * step 3.
         */
        try {
            $this->ossClient->completeMultipartUpload($this->bucket, $object, $upload_id, $upload_parts);
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertTrue(false);
        }

        $this->assertEquals($this->ossClient->getObject($this->bucket, $object), file_get_contents(__FILE__));
        $this->assertEquals($this->ossClient->getObject($this->bucket, $copiedObject), file_get_contents(__FILE__));
    }

    public function testAbortMultipartUpload()
    {
        $object = "mpu/multipart-test.txt";
        /**
         *  step 1. 初始化一个分块上传事件, 也就是初始化上传Multipart, 获取upload id
         */
        try {
            $upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        /*
         * step 2. 上传分片
         */
        $part_size = 10 * 1024 * 1024;
        $upload_file = __FILE__;
        $upload_filesize = filesize($upload_file);
        $pieces = $this->ossClient->generateMultiuploadParts($upload_filesize, $part_size);
        $response_upload_part = array();
        $upload_position = 0;
        $is_check_md5 = true;
        foreach ($pieces as $i => $piece) {
            $from_pos = $upload_position + (integer)$piece[OssClient::OSS_SEEK_TO];
            $to_pos = (integer)$piece[OssClient::OSS_LENGTH] + $from_pos - 1;
            $up_options = array(
                OssClient::OSS_FILE_UPLOAD => $upload_file,
                OssClient::OSS_PART_NUM => ($i + 1),
                OssClient::OSS_SEEK_TO => $from_pos,
                OssClient::OSS_LENGTH => $to_pos - $from_pos + 1,
                OssClient::OSS_CHECK_MD5 => $is_check_md5,
            );
            if ($is_check_md5) {
                $content_md5 = OssUtil::getMd5SumForFile($upload_file, $from_pos, $to_pos);
                $up_options[OssClient::OSS_CONTENT_MD5] = $content_md5;
            }
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
            $listPartsInfo = $this->ossClient->listParts($this->bucket, $object, $upload_id);
            $this->assertNotNull($listPartsInfo);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
        $this->assertEquals(1, count($listPartsInfo->getListPart()));

        $numOfMultipartUpload1 = 0;
        $options = null;
        try {
            $listMultipartUploadInfo = $listMultipartUploadInfo = $this->ossClient->listMultipartUploads($this->bucket, $options);
            $this->assertNotNull($listMultipartUploadInfo);
            $numOfMultipartUpload1 = count($listMultipartUploadInfo->getUploads());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $this->ossClient->abortMultipartUpload($this->bucket, $object, $upload_id);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        $numOfMultipartUpload2 = 0;
        try {
            $listMultipartUploadInfo = $listMultipartUploadInfo = $this->ossClient->listMultipartUploads($this->bucket, $options);
            $this->assertNotNull($listMultipartUploadInfo);
            $numOfMultipartUpload2 = count($listMultipartUploadInfo->getUploads());
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        $this->assertEquals($numOfMultipartUpload1 - 1, $numOfMultipartUpload2);
    }

    public function testPutObjectByRawApis()
    {
        $object = "mpu/multipart-test.txt";
        /**
         *  step 1. 初始化一个分块上传事件, 也就是初始化上传Multipart, 获取upload id
         */
        try {
            $upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
        /*
         * step 2. 上传分片
         */
        $part_size = 10 * 1024 * 1024;
        $upload_file = __FILE__;
        $upload_filesize = filesize($upload_file);
        $pieces = $this->ossClient->generateMultiuploadParts($upload_filesize, $part_size);
        $response_upload_part = array();
        $upload_position = 0;
        $is_check_md5 = true;
        foreach ($pieces as $i => $piece) {
            $from_pos = $upload_position + (integer)$piece[OssClient::OSS_SEEK_TO];
            $to_pos = (integer)$piece[OssClient::OSS_LENGTH] + $from_pos - 1;
            $up_options = array(
                OssClient::OSS_FILE_UPLOAD => $upload_file,
                OssClient::OSS_PART_NUM => ($i + 1),
                OssClient::OSS_SEEK_TO => $from_pos,
                OssClient::OSS_LENGTH => $to_pos - $from_pos + 1,
                OssClient::OSS_CHECK_MD5 => $is_check_md5,
            );
            if ($is_check_md5) {
                $content_md5 = OssUtil::getMd5SumForFile($upload_file, $from_pos, $to_pos);
                $up_options[OssClient::OSS_CONTENT_MD5] = $content_md5;
            }
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
            $listPartsInfo = $this->ossClient->listParts($this->bucket, $object, $upload_id);
            $this->assertNotNull($listPartsInfo);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        /**
         * step 3.
         */
        try {
            $this->ossClient->completeMultipartUpload($this->bucket, $object, $upload_id, $upload_parts);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    function testPutObjectsByDir()
    {
        $localDirectory = dirname(__FILE__);
        $prefix = "samples/codes";
        try {
            $this->ossClient->uploadDir($this->bucket, $prefix, $localDirectory);
        } catch (OssException $e) {
            var_dump($e->getMessage());
            $this->assertFalse(true);

        }
        $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, 'samples/codes/' . "OssClientMultipartUploadTest.php"));
    }

    public function testPutObjectByMultipartUpload()
    {
        $object = "mpu/multipart-test.txt";
        $file = __FILE__;
        $options = array();

        try {
            $this->ossClient->multiuploadFile($this->bucket, $object, $file, $options);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }
    
    public function testPutObjectByMultipartUploadWithMD5Check()
    {
    	$object = "mpu/multipart-test.txt";
    	$file = __FILE__;
    	$options = array(OssClient::OSS_CHECK_MD5 => true);
    
    	try {
    		$this->ossClient->multiuploadFile($this->bucket, $object, $file, $options);
    	} catch (OssException $e) {
    		$this->assertFalse(true);
    	}
    }

    public function testListMultipartUploads()
    {
        $options = null;
        try {
            $listMultipartUploadInfo = $this->ossClient->listMultipartUploads($this->bucket, $options);
            $this->assertNotNull($listMultipartUploadInfo);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }
}
