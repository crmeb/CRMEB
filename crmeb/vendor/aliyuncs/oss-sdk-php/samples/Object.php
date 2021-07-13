<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
//*******************************简单使用***************************************************************

// 简单上传变量的内容到oss文件
$result = $ossClient->putObject($bucket, "b.file", "hi, oss");
Common::println("b.file is created");
Common::println($result['x-oss-request-id']);
Common::println($result['etag']);
Common::println($result['content-md5']);
Common::println($result['body']);

// 上传本地文件
$result = $ossClient->uploadFile($bucket, "c.file", __FILE__);
Common::println("c.file is created");
Common::println("b.file is created");
Common::println($result['x-oss-request-id']);
Common::println($result['etag']);
Common::println($result['content-md5']);
Common::println($result['body']);

// 下载object到本地变量
$content = $ossClient->getObject($bucket, "b.file");
Common::println("b.file is fetched, the content is: " . $content);

// 给object添加symlink
$content = $ossClient->putSymlink($bucket, "test-symlink", "b.file");
Common::println("test-symlink is created");
Common::println($result['x-oss-request-id']);
Common::println($result['etag']);

// 获取symlink
$content = $ossClient->getSymlink($bucket, "test-symlink");
Common::println("test-symlink refer to : " . $content[OssClient::OSS_SYMLINK_TARGET]);

// 下载object到本地文件
$options = array(
    OssClient::OSS_FILE_DOWNLOAD => "./c.file.localcopy",
);
$ossClient->getObject($bucket, "c.file", $options);
Common::println("b.file is fetched to the local file: c.file.localcopy");
Common::println("b.file is created");

// 拷贝object
$result = $ossClient->copyObject($bucket, "c.file", $bucket, "c.file.copy");
Common::println("lastModifiedTime: " . $result[0]);
Common::println("ETag: " . $result[1]);

// 判断object是否存在
$doesExist = $ossClient->doesObjectExist($bucket, "c.file.copy");
Common::println("file c.file.copy exist? " . ($doesExist ? "yes" : "no"));

// 删除object
$result = $ossClient->deleteObject($bucket, "c.file.copy");
Common::println("c.file.copy is deleted");
Common::println("b.file is created");
Common::println($result['x-oss-request-id']);

// 判断object是否存在
$doesExist = $ossClient->doesObjectExist($bucket, "c.file.copy");
Common::println("file c.file.copy exist? " . ($doesExist ? "yes" : "no"));

// 批量删除object
$result = $ossClient->deleteObjects($bucket, array("b.file", "c.file"));
foreach($result as $object)
    Common::println($object);

sleep(2);
unlink("c.file.localcopy");

//******************************* 完整用法参考下面函数 ****************************************************

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
 * 创建虚拟目录
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 把本地变量的内容到文件
 *
 * 简单上传,上传指定变量的内存值作为object的内容
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 上传指定的本地文件内容
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 列出Bucket内所有目录和文件, 注意如果符合条件的文件数目超过设置的max-keys， 用户需要使用返回的nextMarker作为入参，通过
 * 循环调用ListObjects得到所有的文件，具体操作见下面的 listAllObjects 示例
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
    $objectList = $listObjectInfo->getObjectList(); // 文件列表
    $prefixList = $listObjectInfo->getPrefixList(); // 目录列表
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
 * 列出Bucket内所有目录和文件， 根据返回的nextMarker循环得到所有Objects
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
 * @return null
 */
function listAllObjects($ossClient, $bucket)
{
    //构造dir下的文件和虚拟目录
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
        // 得到nextMarker，从上一次listObjects读到的最后一个文件的下一个文件开始继续获取文件列表
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
 * 获取object的内容
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * put symlink
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 获取symlink
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * get_object_to_local_file
 *
 * 获取object
 * 将object下载到指定的文件
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 拷贝object
 * 当目的object和源object完全相同时，表示修改object的meta信息
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 修改Object Meta
 * 利用copyObject接口的特性：当目的object和源object完全相同时，表示修改object的meta信息
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 获取object meta, 也就是getObjectMeta接口
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 删除object
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 批量删除object
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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
 * 判断object是否存在
 *
 * @param OssClient $ossClient OssClient实例
 * @param string $bucket 存储空间名称
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

