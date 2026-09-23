<?php
include_once(__DIR__ . '/../../../vendor/autoload.php');

use Volc\Service\ImageX\V2\Imagex;

$client = Imagex::getInstance();
//todo
//$client->config["Host"] = "";

// call below method if you dont set ak and sk in ～/.volc/config
// $client->setAccessKey("ak");
// $client->setSecretKey("sk");

$request = array();

$params["ServiceId"] = "imagex service id";   // 服务 ID
// $params["Data"] = "your data";  // 文件数据，与FilePath二选一
// $params["FilePath"] = "your file path";  // 文件数据，与Data二选一
// $params["StoreKey"] = "your store key";  // 文件数据，与FilePath二选一
// $params["Prefix"] = "your prefix";  // 文件前缀
// $params["FileExtension"] = "your file extension";  // 文件后缀
// $params["ContentType"] = "your content type";  // 文件Content-Type
// $params["StorageClass"] = "your storage class";  // 文件存储类型
// $params["PartSize"] = 0;  // 偏好分片大小，单位为字节(0表示按照默认规则分片)
// $params["Overwrite"] = false;  // 是否进行上传覆盖

try {
    $response = $client->vpcUploadImages($request);
    print_r($response);
} catch (Exception $e) {
    print_r($e);
}

