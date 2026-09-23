<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;
use OSS\Model\TaggingConfig;
use OSS\Model\Tag;
use OSS\Model\DeleteObjectInfo;
use OSS\Core\OssUtil;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientObjectVersioningTest extends TestOssClientBase
{
    public function testObjectBasic()
    {
        $object = 'object';
        $content1 = 'hello';
        $content2 = 'hello world';
        $ret1 = $this->ossClient->putObject($this->bucket, $object, $content1, array(OssClient::OSS_HEADERS => array('x-oss-object-acl' => 'public-read', 'x-oss-tagging' => 'key1=value1')));
        $ret2 = $this->ossClient->putObject($this->bucket, $object, $content2, array(OssClient::OSS_HEADERS => array('x-oss-object-acl' => 'private', 'x-oss-tagging' => 'key2=value2')));

        $this->assertTrue(isset($ret1[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($ret2[OssClient::OSS_HEADER_VERSION_ID]));

        $versionId1 = $ret1[OssClient::OSS_HEADER_VERSION_ID];
        $versionId2 = $ret2[OssClient::OSS_HEADER_VERSION_ID];

        //get object
        $res = $this->ossClient->getObject($this->bucket, $object);
        $res1 = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $res2 = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId2));
        $this->assertEquals($content1, $res1);
        $this->assertEquals($content2, $res2);
        $this->assertEquals($content2, $res);

        //meta
        $headers = $this->ossClient->getObjectMeta($this->bucket, $object);
        $headers1 = $this->ossClient->getObjectMeta($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $headers2 = $this->ossClient->getObjectMeta($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId2));

        $this->assertTrue(isset($headers[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($headers1[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($headers2[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertEquals($versionId1, $headers1[OssClient::OSS_HEADER_VERSION_ID]);
        $this->assertEquals($versionId2, $headers2[OssClient::OSS_HEADER_VERSION_ID]);
        $this->assertEquals($versionId2, $headers[OssClient::OSS_HEADER_VERSION_ID]);


        $sheaders = $this->ossClient->getSimplifiedObjectMeta($this->bucket, $object);
        $sheaders1 = $this->ossClient->getSimplifiedObjectMeta($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $sheaders2 = $this->ossClient->getSimplifiedObjectMeta($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId2));

        $this->assertTrue(isset($sheaders[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($sheaders1[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($sheaders2[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertEquals($versionId1, $sheaders1[OssClient::OSS_HEADER_VERSION_ID]);
        $this->assertEquals($versionId2, $sheaders2[OssClient::OSS_HEADER_VERSION_ID]);
        $this->assertEquals($versionId2, $sheaders[OssClient::OSS_HEADER_VERSION_ID]);

        //acl
        $acl = $this->ossClient->getObjectAcl($this->bucket, $object);
        $acl1 = $this->ossClient->getObjectAcl($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $acl2 = $this->ossClient->getObjectAcl($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId2));

        $this->assertEquals('public-read', $acl1);
        $this->assertEquals('private', $acl2);
        $this->assertEquals('private', $acl);

        $this->ossClient->putObjectAcl($this->bucket, $object, 'public-read-write', array(OssClient::OSS_VERSION_ID => $versionId1));
        $acl = $this->ossClient->getObjectAcl($this->bucket, $object);
        $acl1 = $this->ossClient->getObjectAcl($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $this->assertEquals('public-read-write', $acl1);
        $this->assertEquals('private', $acl);

        //tagging
        $tag = $this->ossClient->getObjectTagging($this->bucket, $object);
        $tag1 = $this->ossClient->getObjectTagging($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $tag2 = $this->ossClient->getObjectTagging($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId2));
        $this->assertEquals(1, count($tag1->getTags()));
        $this->assertEquals("key1", $tag1->getTags()[0]->getKey());
        $this->assertEquals("value1", $tag1->getTags()[0]->getValue());
        $this->assertEquals(1, count($tag2->getTags()));
        $this->assertEquals("key2", $tag2->getTags()[0]->getKey());
        $this->assertEquals("value2", $tag2->getTags()[0]->getValue());
        $this->assertEquals(1, count($tag->getTags()));
        $this->assertEquals("key2", $tag->getTags()[0]->getKey());
        $this->assertEquals("value2", $tag->getTags()[0]->getValue());

        $config = new TaggingConfig();
        $config->addTag(new Tag("key11", "value11"));
        $this->ossClient->putObjectTagging($this->bucket, $object, $config, array(OssClient::OSS_VERSION_ID => $versionId1));
        $tag = $this->ossClient->getObjectTagging($this->bucket, $object);
        $tag1 = $this->ossClient->getObjectTagging($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $this->assertEquals(1, count($tag1->getTags()));
        $this->assertEquals("key11", $tag1->getTags()[0]->getKey());
        $this->assertEquals("value11", $tag1->getTags()[0]->getValue());
        $this->assertEquals(1, count($tag->getTags()));
        $this->assertEquals("key2", $tag->getTags()[0]->getKey());
        $this->assertEquals("value2", $tag->getTags()[0]->getValue());

        $this->ossClient->deleteObjectTagging($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $tag = $this->ossClient->getObjectTagging($this->bucket, $object);
        $tag1 = $this->ossClient->getObjectTagging($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $this->assertEquals(0, count($tag1->getTags()));
        $this->assertEquals(1, count($tag->getTags()));
        $this->assertEquals("key2", $tag->getTags()[0]->getKey());
        $this->assertEquals("value2", $tag->getTags()[0]->getValue());

        //delete
        $dret = $this->ossClient->deleteObject($this->bucket, $object);
        $this->assertTrue(isset($dret['x-oss-delete-marker']));
        $this->assertTrue(isset($dret['x-oss-version-id']));
        $this->assertEquals("true", $dret['x-oss-delete-marker']);
        $this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object));
        $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1)));
        $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId2)));

        $dret1 = $this->ossClient->deleteObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        $this->assertFalse(isset($dret1['x-oss-delete-marker']));
        $this->assertTrue(isset($dret1['x-oss-version-id']));
        $this->assertEquals($versionId1, $dret1['x-oss-version-id']);
        $this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1)));


        $dret_ = $this->ossClient->deleteObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $dret['x-oss-version-id']));
        $this->assertTrue(isset($dret_['x-oss-delete-marker']));
        $this->assertTrue(isset($dret_['x-oss-version-id']));
        $this->assertEquals($dret['x-oss-version-id'], $dret_['x-oss-version-id']);
        $this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object));

    }

    public function testObjectSymlink()
    {
        $object1 = 'object-target-1';
        $object2 = 'object-target-2';
        $symlink = 'object-symlink';
        $content1 = 'hello';
        $content2 = 'hello world';


        $ret1 = $this->ossClient->putObject($this->bucket, $object1, $content1);
        $sym1 = $this->ossClient->putSymlink($this->bucket, $symlink, $object1);

        $ret2 = $this->ossClient->putObject($this->bucket, $object2, $content2);
        $sym2 = $this->ossClient->putSymlink($this->bucket, $symlink, $object2);

        $this->assertTrue(isset($ret1[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($ret2[OssClient::OSS_HEADER_VERSION_ID]));

        $this->assertTrue(isset($sym1[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($sym2[OssClient::OSS_HEADER_VERSION_ID]));

        $versionId1 = $ret1[OssClient::OSS_HEADER_VERSION_ID];
        $versionId2 = $ret2[OssClient::OSS_HEADER_VERSION_ID];

        $sym_versionId1 = $sym1[OssClient::OSS_HEADER_VERSION_ID];
        $sym_versionId2 = $sym2[OssClient::OSS_HEADER_VERSION_ID];


        $sym_ret = $this->ossClient->getSymlink($this->bucket, $symlink);
        $sym_ret1 = $this->ossClient->getSymlink($this->bucket, $symlink, array(OssClient::OSS_VERSION_ID => $sym_versionId1));
        $sym_ret2 = $this->ossClient->getSymlink($this->bucket, $symlink, array(OssClient::OSS_VERSION_ID => $sym_versionId2));

        $this->assertTrue(isset($sym_ret['x-oss-version-id']));
        $this->assertTrue(isset($sym_ret1['x-oss-version-id']));
        $this->assertTrue(isset($sym_ret2['x-oss-version-id']));

        $this->assertEquals($sym_versionId1, $sym_ret1['x-oss-version-id']);
        $this->assertEquals($sym_versionId2, $sym_ret2['x-oss-version-id']);
        $this->assertEquals($sym_versionId2, $sym_ret['x-oss-version-id']);


        $res = $this->ossClient->getObject($this->bucket, $symlink);
        $res1 = $this->ossClient->getObject($this->bucket, $symlink, array(OssClient::OSS_VERSION_ID => $sym_versionId1));
        $res2 = $this->ossClient->getObject($this->bucket, $symlink, array(OssClient::OSS_VERSION_ID => $sym_versionId2));
        $this->assertEquals($content1, $res1);
        $this->assertEquals($content2, $res2);
        $this->assertEquals($content2, $res);
    }

    public function testObjectCopy()
    {
        $object = 'copy-= +object';
        $content1 = 'hello';
        $content2 = 'hello world';
        $to_bucket = $this->bucket;
        $to_object = $object . '.copy';
        $to_object1 = $object . '.copy1';
        $to_object2 = $object . '.copy2';

        $ret1 = $this->ossClient->putObject($this->bucket, $object, $content1);
        $ret2 = $this->ossClient->putObject($this->bucket, $object, $content2);

        $versionId1 = $ret1[OssClient::OSS_HEADER_VERSION_ID];
        $versionId2 = $ret2[OssClient::OSS_HEADER_VERSION_ID];

        $cret = $this->ossClient->copyObject($this->bucket, $object, $to_bucket, $to_object);
        $cret1 = $this->ossClient->copyObject($this->bucket, $object, $to_bucket, $to_object1, array(OssClient::OSS_VERSION_ID => $versionId1));
        $cret2 = $this->ossClient->copyObject($this->bucket, $object, $to_bucket, $to_object2, array(OssClient::OSS_VERSION_ID => $versionId2));
        $this->assertFalse(empty($cret1));
        $this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($cret1[0]));
        $this->assertEquals(trim($ret1['etag'], '"'), trim($cret1[1], '"'));
        $this->assertTrue(isset($cret1['x-oss-version-id']));
        $this->assertEquals($versionId1, $cret1['x-oss-copy-source-version-id']);

        $this->assertFalse(empty($cret2));
        $this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($cret2[0]));
        $this->assertEquals(trim($ret2['etag'], '"'), trim($cret2[1], '"'));
        $this->assertTrue(isset($cret2['x-oss-version-id']));
        $this->assertEquals($versionId2, $cret2['x-oss-copy-source-version-id']);

        $this->assertFalse(empty($cret));
        $this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($cret[0]));
        $this->assertEquals(trim($ret2['etag'], '"'), trim($cret[1], '"'));
        $this->assertTrue(isset($cret2['x-oss-version-id']));
        $this->assertEquals($versionId2, $cret['x-oss-copy-source-version-id']);

        $res = $this->ossClient->getObject($this->bucket, $to_object);
        $res1 = $this->ossClient->getObject($this->bucket, $to_object1);
        $res2 = $this->ossClient->getObject($this->bucket, $to_object2);
        $this->assertEquals($content1, $res1);
        $this->assertEquals($content2, $res2);
        $this->assertEquals($content2, $res);
    }

    public function testObjectRestore()
    {
        $object = 'retore-object';
        $content1 = 'hello';
        $content2 = 'hello world';
        $ret1 = $this->ossClient->putObject($this->bucket, $object, $content1, array(OssClient::OSS_HEADERS => array('x-oss-storage-class' => 'Archive')));
        $ret2 = $this->ossClient->putObject($this->bucket, $object, $content2);

        $versionId1 = $ret1[OssClient::OSS_HEADER_VERSION_ID];
        $versionId2 = $ret2[OssClient::OSS_HEADER_VERSION_ID];

        try{
            $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('403', $e->getHTTPStatus());
            $this->assertEquals('InvalidObjectState', $e->getErrorCode());
        }

        try{
            $this->ossClient->restoreObject($this->bucket, $object);
            $this->assertTrue(false);
        }catch(OssException $e){
            $this->assertEquals('400', $e->getHTTPStatus());
            $this->assertEquals('OperationNotSupported', $e->getErrorCode());
        }
        
        $result = $this->ossClient->restoreObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        common::waitMetaSync();
        $this->assertEquals('202', $result['info']['http_code']);

        try{
            $this->ossClient->restoreObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $versionId1));
        }catch(OssException $e){
            $this->assertEquals('409', $e->getHTTPStatus());
            $this->assertEquals('RestoreAlreadyInProgress', $e->getErrorCode());
        }
    }

    public function testObjectMultiPart()
    {
        $object_src = 'multi-= +object.src';
        $content1 = 'hello';
        $content2 = 'hello world';
        $ret1 = $this->ossClient->putObject($this->bucket, $object_src, $content1);
        $ret2 = $this->ossClient->putObject($this->bucket, $object_src, $content2);

        $this->assertTrue(isset($ret1[OssClient::OSS_HEADER_VERSION_ID]));
        $this->assertTrue(isset($ret2[OssClient::OSS_HEADER_VERSION_ID]));

        $versionId1 = $ret1[OssClient::OSS_HEADER_VERSION_ID];
        $versionId2 = $ret2[OssClient::OSS_HEADER_VERSION_ID];

        //object
        $object = "multi-object";
        $upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);
        $copyId = 1;
        $eTag = $this->ossClient->uploadPartCopy($this->bucket, $object_src, $this->bucket, $object, $copyId, $upload_id);
        $upload_parts[] = array(
            'PartNumber' => $copyId,
            'ETag' => $eTag,
        );
        $ret = $this->ossClient->completeMultipartUpload($this->bucket, $object, $upload_id, $upload_parts);

        //object-1
        $object1 = "multi-object-1";
        $upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object1);
        $copyId = 1;
        $eTag = $this->ossClient->uploadPartCopy($this->bucket, $object_src, $this->bucket, $object1, $copyId, $upload_id, array(OssClient::OSS_VERSION_ID => $versionId1));
        $upload_parts1[] = array(
            'PartNumber' => $copyId,
            'ETag' => $eTag,
        );
        $ret1 = $this->ossClient->completeMultipartUpload($this->bucket, $object1, $upload_id, $upload_parts1);

        //object-2
        $object2 = "multi-object-2";
        $upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object2);
        $copyId = 1;
        $eTag = $this->ossClient->uploadPartCopy($this->bucket, $object_src, $this->bucket, $object2, $copyId, $upload_id, array(OssClient::OSS_VERSION_ID => $versionId2));
        $upload_parts2[] = array(
            'PartNumber' => $copyId,
            'ETag' => $eTag,
        );
        $ret2 = $this->ossClient->completeMultipartUpload($this->bucket, $object2, $upload_id, $upload_parts2);

        $res = $this->ossClient->getObject($this->bucket, $object);
        $res1 = $this->ossClient->getObject($this->bucket, $object1);
        $res2 = $this->ossClient->getObject($this->bucket, $object2);

        $this->assertEquals($content1, $res1);
        $this->assertEquals($content2, $res2);
        $this->assertEquals($content2, $res);
    }

    public function testObjectMisc()
    {
        //use multipart
        $options = array(
            OssClient::OSS_PART_SIZE => 1,
        );
        
        $object = 'misc-object';

        $smallFile1 = __DIR__ . DIRECTORY_SEPARATOR . "/smallfile1.tmp";
        $smallFile2 = __DIR__ . DIRECTORY_SEPARATOR . "/smallfile2.tmp";
        $bigFile1 = __DIR__ . DIRECTORY_SEPARATOR . "/bigfile1.tmp";
        $bigFile2 = __DIR__ . DIRECTORY_SEPARATOR . "/bigfile2.tmp";

        OssUtil::generateFile($smallFile1, 5);
        OssUtil::generateFile($smallFile2, 10);
        OssUtil::generateFile($bigFile1, 128 * 1024);
        OssUtil::generateFile($bigFile2, 256 * 1024);
        
        $sret1 = $this->ossClient->multiuploadFile($this->bucket, $object, $smallFile1, $options);
        $sret2 = $this->ossClient->multiuploadFile($this->bucket, $object, $smallFile2, $options);
        $bret1 = $this->ossClient->multiuploadFile($this->bucket, $object, $bigFile1, $options);
        $bret2 = $this->ossClient->multiuploadFile($this->bucket, $object, $bigFile2, $options);


        $res = $this->ossClient->getObject($this->bucket, $object);
        $sres1 = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $sret1['x-oss-version-id']));
        $sres2 = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $sret2['x-oss-version-id']));
        $bres1 = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $bret1['x-oss-version-id']));
        $bres2 = $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_VERSION_ID => $bret2['x-oss-version-id']));

        
        $this->assertEquals(file_get_contents($smallFile1), $sres1);
        $this->assertEquals(file_get_contents($smallFile2), $sres2);
        $this->assertEquals(file_get_contents($bigFile1), $bres1);
        $this->assertEquals(file_get_contents($bigFile2), $bres2);
        $this->assertEquals(file_get_contents($bigFile2), $res);


        unlink($smallFile1);
        unlink($smallFile2);
        unlink($bigFile1);
        unlink($bigFile2);
    }

    public function testListObjects()
    {
        //folder 
        for ($i = 0; $i < 12; $i++) {
            $key = 'folder/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->deleteObject($this->bucket, $key);
        }

        //test
        for ($i = 0; $i < 8; $i++) {
            $key = 'test/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->deleteObject($this->bucket, $key);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }
        //work
        for ($i = 0; $i < 5; $i++) {
            $key = 'work/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
        }
        //sub++
        for ($i = 0; $i < 3; $i++) {
            $key = 'sub++/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->putObject($this->bucket, $key, "content");
        }
        //file++
        for ($i = 0; $i < 2; $i++) {
            $key = 'file++'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->deleteObject($this->bucket, $key);
        }

        //list default
        $result = $this->ossClient->listObjectVersions($this->bucket);
        $versionList = $result->getObjectVersionList();
        $deleteMarkerList = $result->getDeleteMarkerList();
        $prefixList = $result->getPrefixList();

        $this->assertNotNull($versionList);
        $this->assertNotNull($deleteMarkerList);
        $this->assertNotNull($prefixList);
        $this->assertTrue(is_array($versionList));
        $this->assertTrue(is_array($deleteMarkerList));
        $this->assertTrue(is_array($prefixList));
        $this->assertEquals(2, count($versionList));
        $this->assertEquals(2, count($deleteMarkerList));
        $this->assertEquals(4, count($prefixList));

        $this->assertEquals('file++00', $versionList[0]->getKey());
        $this->assertEquals('false', $versionList[0]->getIsLatest());
        $this->assertEquals('file++01', $versionList[1]->getKey());
        $this->assertEquals('false', $versionList[1]->getIsLatest());

        $this->assertEquals('file++00', $deleteMarkerList[0]->getKey());
        $this->assertEquals('true', $deleteMarkerList[0]->getIsLatest());
        $this->assertEquals('file++01', $deleteMarkerList[1]->getKey());
        $this->assertEquals('true', $deleteMarkerList[1]->getIsLatest());


        $this->assertEquals('folder/', $prefixList[0]->getPrefix());
        $this->assertEquals('sub++/', $prefixList[1]->getPrefix());
        $this->assertEquals('test/', $prefixList[2]->getPrefix());
        $this->assertEquals('work/', $prefixList[3]->getPrefix());

        //list by prefix
        $prefix = 'folder/';
        $delimiter = '';
        $next_marker = '';
        $maxkeys = 1000;
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'key-marker' => $next_marker,
        );

        $result = $this->ossClient->listObjectVersions($this->bucket, $options);
        $versionList = $result->getObjectVersionList();
        $deleteMarkerList = $result->getDeleteMarkerList();
        $prefixList = $result->getPrefixList();

        $this->assertEquals(24, count($versionList));
        $this->assertEquals(12, count($deleteMarkerList));
        $this->assertEquals(0, count($prefixList));

        $this->assertEquals('folder/00', $versionList[0]->getKey());
        $this->assertEquals('folder/00', $versionList[1]->getKey());
        $this->assertEquals('folder/00', $deleteMarkerList[0]->getKey());
        $this->assertEquals('folder/01', $deleteMarkerList[1]->getKey());


        //max-key & key-marker & version-id-marker
        $count = 0;
        $markerCount = 0;
        $nextMarker = '';
        $nextVersionIdMarker = '';

        while (true) {
            $options = array(
                'delimiter' => '',
                'key-marker' => $nextMarker,
                'max-keys' => 1,
                'version-id-marker' => $nextVersionIdMarker,
            );
            $result = $this->ossClient->listObjectVersions($this->bucket, $options);

            $nextMarker = $result->getNextKeyMarker();
            $nextVersionIdMarker = $result->getNextVersionIdMarker();
            $count += count($result->getObjectVersionList());
            $markerCount += count($result->getDeleteMarkerList());
            $this->assertEquals(1, count($result->getObjectVersionList()) + count($result->getDeleteMarkerList()));
            if ($result->getIsTruncated() !== "true") {
                break;
            }
        }
        $this->assertEquals(12*3 + 8*3 + 5 + 3*3 + 2*2, $count + $markerCount);
    }

    public function testDeleteObjects()
    {
        //deletes 
        for ($i = 0; $i < 5; $i++) {
            $key = 'deletes/'. sprintf("%02d",$i);
            $this->ossClient->putObject($this->bucket, $key, "content");
            $this->ossClient->putObject($this->bucket, $key, "content");
        }

        $options = array(
            'delimiter' => '',
            'prefix' => 'deletes/',
            'max-keys' => 1000,
        );
        $result = $this->ossClient->listObjects($this->bucket, $options);
        $this->assertEquals(5, count($result->getObjectList()));

        //delete without version-id
        $objects = array(); 
        for ($i = 0; $i < 5; $i++) {
            $key = 'deletes/'. sprintf("%02d",$i);
            $objects[] = new DeleteObjectInfo($key);
        }
        $dresult = $this->ossClient->deleteObjectVersions($this->bucket, $objects);
        $this->assertEquals(5, count($dresult));
        $this->assertEquals('deletes/00', $dresult[0]->getKey());
        $this->assertEquals('true', $dresult[0]->getDeleteMarker());
        $this->assertEquals('', $dresult[0]->getVersionId());
        $this->assertFalse(empty($dresult[0]->getDeleteMarkerVersionId()));

        $result = $this->ossClient->listObjects($this->bucket, $options);
        $this->assertEquals(0, count($result->getObjectList()));

        //delete by version-id
        $vresult = $this->ossClient->listObjectVersions($this->bucket, $options);
        $versions = $vresult->getObjectVersionList();
        $deleteMarkerList = $vresult->getDeleteMarkerList();
        $this->assertEquals(10, count($versions));
        $this->assertEquals(5, count($deleteMarkerList));

        $objects = array(); 
        foreach ($versions as $obj) {
            $objects[] = new DeleteObjectInfo($obj->getKey(), $obj->getVersionId());
        }
        $dresult = $this->ossClient->deleteObjectVersions($this->bucket, $objects);
        $this->assertEquals(10, count($dresult));
        $this->assertEquals('deletes/00', $dresult[0]->getKey());
        $this->assertEquals('', $dresult[0]->getDeleteMarker());
        $this->assertFalse(empty($dresult[0]->getVersionId()));
        $this->assertTrue(empty($dresult[0]->getDeleteMarkerVersionId()));
        $this->assertEquals('deletes/00', $dresult[1]->getKey());
        $this->assertEquals('', $dresult[1]->getDeleteMarker());
        $this->assertFalse(empty($dresult[1]->getVersionId()));
        $this->assertTrue(empty($dresult[1]->getDeleteMarkerVersionId()));


        $vresult = $this->ossClient->listObjectVersions($this->bucket, $options);
        $versions = $vresult->getObjectVersionList();
        $deleteMarkerList = $vresult->getDeleteMarkerList();
        $this->assertEquals(0, count($versions));
        $this->assertEquals(5, count($deleteMarkerList));

        $objects = array(); 
        foreach ($deleteMarkerList as $obj) {
            $objects[] = new DeleteObjectInfo($obj->getKey(), $obj->getVersionId());
        }
        $dresult = $this->ossClient->deleteObjectVersions($this->bucket, $objects);
        $this->assertEquals(5, count($dresult));
        $this->assertEquals('deletes/00', $dresult[0]->getKey());
        $this->assertEquals('true', $dresult[0]->getDeleteMarker());
        $this->assertFalse(empty($dresult[1]->getVersionId()));
        $this->assertFalse(empty($dresult[1]->getDeleteMarkerVersionId()));

        $vresult = $this->ossClient->listObjectVersions($this->bucket, $options);
        $versions = $vresult->getObjectVersionList();
        $deleteMarkerList = $vresult->getDeleteMarkerList();
        $this->assertEquals(0, count($versions));
        $this->assertEquals(0, count($deleteMarkerList));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ossClient->putBucketVersioning($this->bucket, "Enabled");

    }

    protected function tearDown(): void
    {
        if (!$this->ossClient->doesBucketExist($this->bucket)) {
            return;
        }

        $this->ossClient->putBucketVersioning($this->bucket, "Suspended");

        $result = $this->ossClient->listObjectVersions(
            $this->bucket, array('max-keys' => 1000, 'delimiter' => ''));

        $versions = $result->getObjectVersionList();
        $deleteMarkers = $result->getDeleteMarkerList();

        foreach ($versions as $obj) {
            $options = array(
                OssClient::OSS_VERSION_ID => $obj->getVersionId(),
            );
            $this->ossClient->deleteObject($this->bucket, $obj->getKey(), $options);
        }

        foreach ($deleteMarkers as $del) {
            $options = array(
                OssClient::OSS_VERSION_ID => $del->getVersionId(),
            );
            $this->ossClient->deleteObject($this->bucket, $del->getKey(), $options);
        }

        parent::tearDown();
    }
}
