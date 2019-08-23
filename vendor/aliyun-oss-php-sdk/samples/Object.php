<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
//******************************* Simple usage ***************************************************************

// Upload the in-memory string (hi, oss) to an OSS file
$result = $ossClient->putObject($bucket, "b.file", "hi, oss");
Common::println("b.file is created");
Common::println($result['x-oss-request-id']);
Common::println($result['etag']);
Common::println($result['content-md5']);
Common::println($result['body']);

// Uploads a local file to an OSS file
$result = $ossClient->uploadFile($bucket, "c.file", __FILE__);
Common::println("c.file is created");
Common::println("b.file is created");
Common::println($result['x-oss-request-id']);
Common::println($result['etag']);
Common::println($result['content-md5']);
Common::println($result['body']);

// Download an oss object as an in-memory variable
$content = $ossClient->getObject($bucket, "b.file");
Common::println("b.file is fetched, the content is: " . $content);

// Add a symlink to an object
$content = $ossClient->putSymlink($bucket, "test-symlink", "b.file");
Common::println("test-symlink is created");
Common::println($result['x-oss-request-id']);
Common::println($result['etag']);

// Get a symlink
$content = $ossClient->getSymlink($bucket, "test-symlink");
Common::println("test-symlink refer to : " . $content[OssClient::OSS_SYMLINK_TARGET]);

// Download an object to a local file.
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => "./c.file.localcopy",
);
$ossClient->getObject($bucket, "c.file", $options);
Common::println("b.file is fetched to the local file: c.file.localcopy");
Common::println("b.file is created");

// Copy an object
$result = $ossClient->copyObject($bucket, "c.file", $bucket, "c.file.copy");
Common::println("lastModifiedTime: " . $result[0]);
Common::println("ETag: " . $result[1]);

// Check whether an object exists
$doesExist = $ossClient->doesObjectExist($bucket, "c.file.copy");
Common::println("file c.file.copy exist? " . ($doesExist ? "yes" : "no"));

// Delete an object
$result = $ossClient->deleteObject($bucket, "c.file.copy");
Common::println("c.file.copy is deleted");
Common::println("b.file is created");
Common::println($result['x-oss-request-id']);

// Check whether an object exists
$doesExist = $ossClient->doesObjectExist($bucket, "c.file.copy");
Common::println("file c.file.copy exist? " . ($doesExist ? "yes" : "no"));

// Delete multiple objects in batch
$result = $ossClient->deleteObjects($bucket, array("b.file", "c.file"));
foreach($result as $object)
    Common::println($object);

sleep(2);
unlink("c.file.localcopy");

//******************************* For complete usage, see the following functions ****************************************************

listObjects($ossClient, $bucket);
listAllObjects($ossClient, $bucket);
createObjectDir($ossClient, $bucket);
putObject($ossClient, $bucket);
uploadFile($ossClient, $bucket);
getObject($ossClient, $bucket);
getObjectToLocalFile($ossClient, $bucket);
copyObject($ossClient, $bucket);
modifyMetaForObject($ossClient, $bucket);
getObjectMeta($ossClient, $bucket);
deleteObject($ossClient, $bucket);
deleteObjects($ossClient, $bucket);
doesObjectExist($ossClient, $bucket);
getSymlink($ossClient, $bucket);
putSymlink($ossClient, $bucket);
/**
 * Create a 'virtual' folder
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function createObjectDir($ossClient, $bucket)
{
    try {
        $ossClient->createObjectDir($bucket, "dir");
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Upload in-memory data to oss
 *
 * Simple upload---upload specified in-memory data to an OSS object
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function putObject($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    $content = file_get_contents(__FILE__);
    $options = array();
    try {
        $ossClient->putObject($bucket, $object, $content, $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Uploads a local file to OSS
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function uploadFile($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    $filePath = __FILE__;
    $options = array();

    try {
        $ossClient->uploadFile($bucket, $object, $filePath, $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Lists all files and folders in the bucket. 
 * Note if there's more items than the max-keys specified, the caller needs to use the nextMarker returned as the value for the next call's maker paramter.
 * Loop through all the items returned from ListObjects.
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function listObjects($ossClient, $bucket)
{
    $prefix = 'oss-php-sdk-test/';
    $delimiter = '/';
    $nextMarker = '';
    $maxkeys = 1000;
    $options = array(
        'delimiter' => $delimiter,
        'prefix' => $prefix,
        'max-keys' => $maxkeys,
        'marker' => $nextMarker,
    );
    try {
        $listObjectInfo = $ossClient->listObjects($bucket, $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    $objectList = $listObjectInfo->getObjectList(); // object list
    $prefixList = $listObjectInfo->getPrefixList(); // directory list
    if (!empty($objectList)) {
        print("objectList:\n");
        foreach ($objectList as $objectInfo) {
            print($objectInfo->getKey() . "\n");
        }
    }
    if (!empty($prefixList)) {
        print("prefixList: \n");
        foreach ($prefixList as $prefixInfo) {
            print($prefixInfo->getPrefix() . "\n");
        }
    }
}

/**
 * Lists all folders and files under the bucket. Use nextMarker repeatedly to get all objects.
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function listAllObjects($ossClient, $bucket)
{
    // Create dir/obj 'folder' and put some files into it.
    for ($i = 0; $i < 100; $i += 1) {
        $ossClient->putObject($bucket, "dir/obj" . strval($i), "hi");
        $ossClient->createObjectDir($bucket, "dir/obj" . strval($i));
    }

    $prefix = 'dir/';
    $delimiter = '/';
    $nextMarker = '';
    $maxkeys = 30;

    while (true) {
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $nextMarker,
        );
        var_dump($options);
        try {
            $listObjectInfo = $ossClient->listObjects($bucket, $options);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        // Get the nextMarker, and it would be used as the next call's marker parameter to resume from the last call
        $nextMarker = $listObjectInfo->getNextMarker();
        $listObject = $listObjectInfo->getObjectList();
        $listPrefix = $listObjectInfo->getPrefixList();
        var_dump(count($listObject));
        var_dump(count($listPrefix));
        if ($nextMarker === '') {
            break;
        }
    }
}

/**
 * Get the content of an object.
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function getObject($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    $options = array();
    try {
        $content = $ossClient->getObject($bucket, $object, $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    if (file_get_contents(__FILE__) === $content) {
        print(__FUNCTION__ . ": FileContent checked OK" . "\n");
    } else {
        print(__FUNCTION__ . ": FileContent checked FAILED" . "\n");
    }
}

/**
 * Put symlink
 *
 * @param OssClient $ossClient  The Instance of OssClient
 * @param string $bucket bucket name
 * @return null
 */
function putSymlink($ossClient, $bucket)
{
    $symlink = "test-samples-symlink";
    $object = "test-samples-object";
    try {
        $ossClient->putObject($bucket, $object, 'test-content');
        $ossClient->putSymlink($bucket, $symlink, $object);
        $content = $ossClient->getObject($bucket, $symlink);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    if ($content == 'test-content') {
        print(__FUNCTION__ . ": putSymlink checked OK" . "\n");
    } else {
        print(__FUNCTION__ . ": putSymlink checked FAILED" . "\n");
    }
}

/**
 * Get symlink
 *
 * @param OssClient $ossClient  OssClient instance
 * @param string $bucket  bucket name
 * @return null
 */
function getSymlink($ossClient, $bucket)
{
    $symlink = "test-samples-symlink";
    $object = "test-samples-object";
    try {
        $ossClient->putObject($bucket, $object, 'test-content');
        $ossClient->putSymlink($bucket, $symlink, $object);
        $content = $ossClient->getSymlink($bucket, $symlink);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    if ($content[OssClient::OSS_SYMLINK_TARGET]) {
        print(__FUNCTION__ . ": getSymlink checked OK" . "\n");
    } else {
        print(__FUNCTION__ . ": getSymlink checked FAILED" . "\n");
    }
}

/**
 * Get_object_to_local_file
 *
 * Get object
 * Download object to a specified file.
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function getObjectToLocalFile($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    $localfile = "upload-test-object-name.txt";
    $options = array(
        OssClient::OSS_FILE_DOWNLOAD => $localfile,
    );

    try {
        $ossClient->getObject($bucket, $object, $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK, please check localfile: 'upload-test-object-name.txt'" . "\n");
    if (file_get_contents($localfile) === file_get_contents(__FILE__)) {
        print(__FUNCTION__ . ": FileContent checked OK" . "\n");
    } else {
        print(__FUNCTION__ . ": FileContent checked FAILED" . "\n");
    }
    if (file_exists($localfile)) {
        unlink($localfile);
    }
}

/**
 * Copy object
 * When the source object is same as the target one, copy operation will just update the metadata.
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function copyObject($ossClient, $bucket)
{
    $fromBucket = $bucket;
    $fromObject = "oss-php-sdk-test/upload-test-object-name.txt";
    $toBucket = $bucket;
    $toObject = $fromObject . '.copy';
    $options = array();

    try {
        $ossClient->copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Update Object Meta
 * it leverages the feature of copyObject： when the source object is just the target object, the metadata could be updated via copy
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function modifyMetaForObject($ossClient, $bucket)
{
    $fromBucket = $bucket;
    $fromObject = "oss-php-sdk-test/upload-test-object-name.txt";
    $toBucket = $bucket;
    $toObject = $fromObject;
    $copyOptions = array(
        OssClient::OSS_HEADERS => array(
            'Cache-Control' => 'max-age=60',
            'Content-Disposition' => 'attachment; filename="xxxxxx"',
        ),
    );
    try {
        $ossClient->copyObject($fromBucket, $fromObject, $toBucket, $toObject, $copyOptions);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Get object meta, that is, getObjectMeta
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function getObjectMeta($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    try {
        $objectMeta = $ossClient->getObjectMeta($bucket, $object);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    if (isset($objectMeta[strtolower('Content-Disposition')]) &&
        'attachment; filename="xxxxxx"' === $objectMeta[strtolower('Content-Disposition')]
    ) {
        print(__FUNCTION__ . ": ObjectMeta checked OK" . "\n");
    } else {
        print(__FUNCTION__ . ": ObjectMeta checked FAILED" . "\n");
    }
}

/**
 * Delete an object
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function deleteObject($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    try {
        $ossClient->deleteObject($bucket, $object);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}


/**
 * Delete multiple objects in batch
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function deleteObjects($ossClient, $bucket)
{
    $objects = array();
    $objects[] = "oss-php-sdk-test/upload-test-object-name.txt";
    $objects[] = "oss-php-sdk-test/upload-test-object-name.txt.copy";
    try {
        $ossClient->deleteObjects($bucket, $objects);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Check whether an object exists
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function doesObjectExist($ossClient, $bucket)
{
    $object = "oss-php-sdk-test/upload-test-object-name.txt";
    try {
        $exist = $ossClient->doesObjectExist($bucket, $object);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
    var_dump($exist);
}

