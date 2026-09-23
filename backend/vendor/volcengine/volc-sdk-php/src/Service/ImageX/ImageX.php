<?php

namespace Volc\Service\ImageX;

use Exception;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Volc\Base\V4Curl;
use GuzzleHttp\Client;

const ResourceServiceIdTRN = "trn:ImageX:*:*:ServiceId/%s";
const ResourceStoreKeyTRN = "trn:ImageX:*:*:StoreKeys/%s";

class ImageX extends V4Curl
{
    const MinChunkSize = 1024 * 1024 * 20;
    const LargeFileSize = 1024 * 1024 * 1024;

    public $config = [];

    public $apiList = [];

    public function __construct($config)
    {
        $this->config = ImageXConfig::$region;
        $this->apiList = ImageXConfig::$apiList;
        parent::__construct($config);
    }

    /**
     * @throws \Exception
     */
    protected function getConfig(string $region)
    {
        if (!isset($this->config[$region])) {
            throw new \Exception(sprintf("ImageX not support region, %s", $region));
        }
        return $this->config[$region];
    }

    public function applyUploadImage(array $query)
    {
        $response = $this->request('ApplyImageUpload', $query);
        return (string)$response->getBody();
    }

    public function commitUploadImage(array $query)
    {
        $response = $this->request('CommitImageUpload', $query);
        return (string)$response->getBody();
    }

    public function createImageContentTask($args)
    {
        $config = [
            "query" => ["ServiceId" => $args['ServiceId']],
            "json" => $args,
        ];
        $response = $this->request('CreateImageContentTask', $config);
        return (string)$response->getBody();
    }

    public function getImageContentTaskDetail($args)
    {
        $config = [
            "query" => ["ServiceId" => $args['ServiceId']],
            "json" => $args,
        ];
        $response = $this->request('GetImageContentTaskDetail', $config);
        return (string)$response->getBody();
    }

    public function getImageContentBlockList($args)
    {
        $config = [
            "query" => ["ServiceId" => $args['ServiceId']],
            "json" => $args,
        ];
        $response = $this->request('GetImageContentBlockList', $config);
        return (string)$response->getBody();
    }

    public function upload(string $uploadHost, $storeInfo, string $file)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $fileResource = null;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $fileSize = 0;

        if (file_exists($file)) {
            // 如果传入是一个文件
            $fileResource = fopen($file, 'rb');
            if ($fileResource === false) {
                return -1;
            }

            $fileSize = filesize($file);
            if ($fileSize === false) {
                return -1;
            }
        } else {
            return -1;
        }

        if ($fileSize == 0) {
            return -1;
        }

        if ($fileSize <= static::MinChunkSize) {
            $content = stream_get_contents($fileResource);
            if (strlen($content) == $fileSize) {
                return $this->directUpload($uploadHost, $storeInfo, $content);
            } else {
                return -1;
            }
        } else {
            $isLargeFile = $fileSize > static::LargeFileSize;
            $handlerStack = HandlerStack::create(new CurlHandler());
            $handlerStack->push(Middleware::retry(ImageXUtil::retryDecider(), ImageXUtil::retryDelay()));
            $client = new Client([
                'base_uri' => "https://" . $uploadHost,
                'timeout' => $isLargeFile ? 600.0 : 30.0,
                'handler' => $handlerStack,
            ]);

            return $this->chunkUpload($storeInfo, $fileResource, $fileSize, $client);
        }

        /** @noinspection PhpUnreachableStatementInspection */
        return 0;
    }

    /**
     * @param array $storeInfo
     * @param resource $fileResource
     * @param int $fileSize
     * @param Client $httpClient
     * @return int
     */
    private function chunkUpload(array $storeInfo, $fileResource, int $fileSize, Client $httpClient)
    {
        $isLargeFile = $fileSize > static::LargeFileSize;
        $uploadID = $this->initUploadPart($storeInfo, $isLargeFile, $httpClient);
        if ($uploadID == "") {
            return -1;
        }

        $num = floor($fileSize / static::MinChunkSize);
        $lastNum = $num - 1;
        $checkSum = [];
        for ($i = 0; $i < $lastNum; $i++) {
            $data = stream_get_contents($fileResource, static::MinChunkSize, $i * static::MinChunkSize);
            $partNumber = $i;
            if ($isLargeFile) {
                $partNumber = $partNumber + 1;
            }
            $crc32 = $this->uploadPart($storeInfo, $uploadID, $partNumber, $data, $isLargeFile, $httpClient);
            if ($crc32 == "") {
                return -1;
            }
            $checkSum[] = $crc32;
        }
        $maxLength = $fileSize - $lastNum * static::MinChunkSize;
        $data = stream_get_contents($fileResource, $maxLength, $lastNum * static::MinChunkSize);
        if ($isLargeFile) {
            $lastNum = $lastNum + 1;
        }
        $crc32 = $this->uploadPart($storeInfo, $uploadID, $lastNum, $data, $isLargeFile, $httpClient);
        if ($crc32 == "") {
            return -1;
        }
        $checkSum[] = $crc32;
        return $this->uploadMergePart($storeInfo, $uploadID, $checkSum, $isLargeFile, $httpClient);
    }

    private function initUploadPart(array $storeInfo, bool $isLargeFile, Client $httpClient)
    {
        $headers = ['Authorization' => $storeInfo["Auth"]];
        if ($isLargeFile) {
            $headers[] = ['X-Storage-Mode' => 'gateway'];
        }
        $response = $httpClient->put(ImageXUtil::escapePath($storeInfo["StoreUri"]) . '?uploads', ['headers' => $headers]);
        $initUploadResponse = json_decode((string)$response->getBody(), true);
        if (!isset($initUploadResponse["success"]) || $initUploadResponse["success"] != 0) {
            return "";
        }
        return $initUploadResponse['payload']['uploadID'];
    }

    private function uploadPart(array $storeInfo, string $uploadID, int $partNumber, $data, bool $isLargeFile, Client $client): string
    {
        $uri = sprintf("%s?partNumber=%d&uploadID=%s", ImageXUtil::escapePath($storeInfo["StoreUri"]), $partNumber, $uploadID);
        $crc32 = sprintf("%08x", crc32($data));
        $headers = ['Authorization' => ['Authorization' => $storeInfo["Auth"]], 'Content-CRC32' => $crc32];
        if ($isLargeFile) {
            $headers[] = ['X-Storage-Mode' => 'gateway'];
        }
        $response = $client->put($uri, ['headers' => $headers, 'body' => $data]);
        $uploadPartResponse = json_decode((string)$response->getBody(), true);
        if (!isset($uploadPartResponse["success"]) || $uploadPartResponse["success"] != 0) {
            return "";
        }
        return $crc32;
    }

    private function uploadMergePart(array $storeInfo, string $uploadID, array $checkSum, bool $isLargeFile, Client $client): int
    {
        $uri = sprintf("%s?uploadID=%s", ImageXUtil::escapePath($storeInfo["StoreUri"]), $uploadID);
        $m = [];
        for ($i = 0; $i < count($checkSum); $i++) {
            $no = $i;
            if ($isLargeFile) {
                $no = $i + 1;
            }
            $m[] = sprintf("%d:%s", $no, $checkSum[$i]);
        }
        $body = implode(",", $m);
        $headers = ['Authorization' => $storeInfo["Auth"]];
        if ($isLargeFile) {
            $headers[] = ['X-Storage-Mode' => 'gateway'];
        }
        $response = $client->put($uri, ['headers' => $headers, 'body' => $body]);
        $uploadPartResponse = json_decode((string)$response->getBody(), true);
        if (!isset($uploadPartResponse["success"]) || $uploadPartResponse["success"] != 0) {
            return -1;
        }
        return 0;
    }

    private function directUpload(string $uploadHost, array $storeInfo, $content)
    {
        $crc32 = sprintf("%08x", crc32($content));
        $tosClient = new Client([
            'base_uri' => "https://" . $uploadHost,
            'timeout' => 30.0,
        ]);
        $response = $tosClient->request('PUT', ImageXUtil::escapePath($storeInfo["StoreUri"]), ["body" => $content, "headers" => ['Authorization' => $storeInfo["Auth"], 'Content-CRC32' => $crc32]]);
        $uploadResponse = json_decode((string)$response->getBody(), true);
        if (!isset($uploadResponse["success"]) || $uploadResponse["success"] != 0) {
            return -2;
        }
        return 0;
    }

    public function uploadImages(array $params = [], array $filePaths = [])
    {
        if (!isset($params["ServiceId"])) {
            return "uploadImages: no ServiceId found";
        }
        $params["UploadNum"] = count($filePaths);

        $applyParams = array();
        $applyParams["Action"] = "ApplyImageUpload";
        $applyParams["Version"] = "2018-08-01";
        $applyParams["ServiceId"] = $params["ServiceId"];
        if (isset($params["SessionKey"])) {
            $applyParams["SessionKey"] = $params["SessionKey"];
        }
        $applyParams["UploadNum"] = $params["UploadNum"];
        if (isset($params["StoreKeys"]) && count($params["StoreKeys"]) != $params["UploadNum"]) {
            return "uploadImages: no StoreKeys found or StoreKeys size is unmatch";
        }
        $applyParams["StoreKeys"] = array();
        $queryStr = http_build_query($applyParams);
        if (isset($params["StoreKeys"]) && is_array($params["StoreKeys"])) {
            foreach ($params["StoreKeys"] as $key => $value) {
                $queryStr = $queryStr . "&StoreKeys=" . urlencode($value);
            }
        }

        $st1 = microtime(true);
        $response = $this->applyUploadImage(['query' => $queryStr]);
        $applyResponse = json_decode($response, true);
        if (isset($applyResponse["ResponseMetadata"]["Error"])) {
            return sprintf("uploadImages: request id %s error %s", $applyResponse["ResponseMetadata"]["RequestId"], $applyResponse["ResponseMetadata"]["Error"]["Message"]);
        }
        echo ("requsetID ". $applyResponse["ResponseMetadata"]["RequestId"] .", applyImageUpload cost " . (microtime(true) - $st1) * 1000 . "ms\n");

        $uploadAddr = $applyResponse['Result']['UploadAddress'];
        if (count($uploadAddr['UploadHosts']) == 0) {
            return "uploadImages: no upload host found";
        }
        $uploadHost = $uploadAddr['UploadHosts'][0];
        if (count($uploadAddr['StoreInfos']) != $params["UploadNum"]) {
            return "uploadImages: store infos num != upload num";
        }

        $st2 = microtime(true);
        $successOids = [];
        $skippedCommitResult = [];
        for ($i = 0; $i < count($filePaths); ++$i) {
            $storeInfo = $uploadAddr['StoreInfos'][$i];
            try {
                $respCode = $this->upload($uploadHost, $storeInfo, $filePaths[$i]);
                if ($respCode == 0) {
                    // succeed
                    $successOids[] = $storeInfo['StoreUri'];
                    $skippedCommitResult[] = [
                        'Uri' => $storeInfo['StoreUri'],
                        'UriStatus' => 2000,
                    ];
                } else {
                    // failed
                    $skippedCommitResult[] = [
                        'Uri' => $storeInfo['StoreUri'],
                        'UriStatus' => 2001,
                        'Error' => "upload " . $filePaths[$i] . " error",
                    ];
                }
            } catch (\Exception $e) {
                // failed
                $skippedCommitResult[] = [
                    'Uri' => $storeInfo['StoreUri'],
                    'UriStatus' => 2001,
                    'Error' => $e,
                ];
            }
        }
        if (count($successOids) == 0) {
            throw new Exception("no file uploaded");
        }
        echo ("requsetID ". $applyResponse["ResponseMetadata"]["RequestId"] .", upload cost " . (microtime(true) - $st2) * 1000 . "ms\n");

        if (isset($params["SkipCommit"]) && $params["SkipCommit"] === true) {
            return ["Result" => ["Results" => $skippedCommitResult]];
        }

        $commitParams = array();
        $commitParams["ServiceId"] = $params["ServiceId"];
        if (isset($params["SkipMeta"])) {
            $commitParams["SkipMeta"] = $params["SkipMeta"];
        }
        $commitBody = array();
        $commitBody["SuccessOids"] = $successOids;
        $commitBody["SessionKey"] = $uploadAddr['SessionKey'];
        if (isset($params["OptionInfos"])) {
            $commitBody["OptionInfos"] = $params["OptionInfos"];
        }
        if (isset($params["Functions"])) {
            $commitBody["Functions"] = $params["Functions"];
        }
        $commitReq = [
            "query" => $commitParams,
            "json" => $commitBody,
        ];

        $st3 = microtime(true);
        $response = $this->commitUploadImage($commitReq);
        echo ("requsetID ". $applyResponse["ResponseMetadata"]["RequestId"] .", commit cost " . (microtime(true) - $st3) * 1000 . "ms\n");

        return (string)$response;
    }

    public function getUploadAuthToken($query)
    {
        $token = [
            "Version" => 'v1',
        ];

        $url = $this->getRequestUrl("ApplyImageUpload", $query);
        $m = parse_url($url);
        $token["ApplyUploadToken"] = $m["query"];

        $url = $this->getRequestUrl("CommitImageUpload", $query);
        $m = parse_url($url);
        $token["CommitUploadToken"] = $m["query"];

        return base64_encode(json_encode($token));
    }

    public function getUploadAuth(array $serviceIDList, int $expire = 3600, string $keyPtn = '', array $tag = [])
    {
        $applyRes = [];
        $commitRes = [];
        if (sizeof($serviceIDList) == 0) {
            $applyRes[] = sprintf(ResourceServiceIdTRN, "*");
            $commitRes[] = sprintf(ResourceServiceIdTRN, "*");
        } else {
            foreach ($serviceIDList as $serviceID) {
                $applyRes[] = sprintf(ResourceServiceIdTRN, $serviceID);
                $commitRes[] = sprintf(ResourceServiceIdTRN, $serviceID);
            }
        }
        $applyRes[] = sprintf(ResourceStoreKeyTRN, $keyPtn);

        $policy = [
            'Statement' => [
                $this->newAllowStatement(['ImageX:ApplyImageUpload'], $applyRes),
                $this->newAllowStatement(['ImageX:CommitImageUpload'], $commitRes),
            ],
        ];
        foreach ($tag as $key => $value) {
            $policy['Statement'][] = $this->newAllowStatement([$key], [$value]);
        }
        return $this->signSts2($policy, $expire);
    }

    public function deleteImages(string $serviceID, array $uris = [])
    {
        $response = $this->request('DeleteImageUploadFiles', ['query' => ['ServiceId' => $serviceID], 'json' => ['StoreUris' => $uris]]);
        return (string)$response->getBody();
    }

    public function requestImageX(string $action, array $config = [])
    {
        $response = $this->request($action, $config);
        return (string)$response->getBody();
    }

    public function fetchImageUrl(array $json = [])
    {
        $response = $this->request('FetchImageUrl', ['json' => $json]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("FetchImageUrl: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function getUrlFetchTask(array $query)
    {
        $response = $this->request('GetUrlFetchTask', ['query' => $query]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("GetUrlFetchTask: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function updateImageFileKey(string $serviceId = "", array $params = [])
    {
        $params["Action"] = "UpdateImageFileKey";
        $params["Version"] = "2018-08-01";
        $params["ServiceId"] = $serviceId;
        $queryStr = http_build_query($params);
        $response = $this->request('UpdateImageFileKey', ['query' => $queryStr, 'json' => $params]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("UpdateImageFileKey: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function updateImageStorageTTL(array $params = [])
    {
        $params["Action"] = "UpdateImageStorageTTL";
        $params["Version"] = "2018-08-01";
        $queryStr = http_build_query($params);
        $response = $this->request('UpdateImageStorageTTL', ['query' => $queryStr, 'json' => $params]);
        $ocrResponse = json_decode((string)$response->getBody(), true);
        if (isset($ocrResponse["ResponseMetadata"]["Error"])) {
            return sprintf("getImageOCR: request id %s error %s", $ocrResponse["ResponseMetadata"]["RequestId"], $ocrResponse["ResponseMetadata"]["Error"]["Message"]);
        }
        return $ocrResponse["Result"];
    }

    public function getImageOCRV2(array $params = [])
    {
        $params["Action"] = "GetImageOCRV2";
        $params["Version"] = "2018-08-01";
        $queryStr = http_build_query($params);
        $response = $this->request('GetImageOCRV2', ['query' => $queryStr, 'json' => $params]);
        $ocrResponse = json_decode((string)$response->getBody(), true);
        if (isset($ocrResponse["ResponseMetadata"]["Error"])) {
            return sprintf("GetImageOCRV2: request id %s error %s", $ocrResponse["ResponseMetadata"]["RequestId"], $ocrResponse["ResponseMetadata"]["Error"]["Message"]);
        }
        return $ocrResponse["Result"];
    }

    public function getSegmentImage(array $params = [])
    {
        $query = [
            "ServiceId" => $params["ServiceId"],
        ];
        $query["Action"] = "GetSegmentImage";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetSegmentImage', ['query' => $queryStr, 'json' => $params]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("getSegmentImage: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function getImageStyleResult(array $query = [], array $body = [])
    {
        $query["Action"] = "GetImageStyleResult";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageStyleResult', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("GetImageStyleResult: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function updateImageDomainIPAuth(array $query = [], array $body = [])
    {
        $query["Action"] = "UpdateImageDomainIPAuth";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('UpdateImageDomainIPAuth', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("UpdateImageDomainIPAuth: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    public function updateRefer(array $query = [], array $body = [])
    {
        $query["Action"] = "UpdateRefer";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('UpdateRefer', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("updateRefer: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function createImageAuditTask(array $query = [], array $body = [])
    {
        $query["Action"] = "CreateImageAuditTask";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('CreateImageAuditTask', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("CreateImageAuditTask: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function deleteImageAuditTask(array $query = [], array $body = [])
    {
        $query["Action"] = "DeleteImageAuditTask";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DeleteImageAuditTask', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("DeleteImageAuditTask: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function deleteImageAuditResult(array $query = [], array $body = [])
    {
        $query["Action"] = "DeleteImageAuditResult";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DeleteImageAuditResult', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("DeleteImageAuditResult: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function updateAuditImageStatus(array $query = [], array $body = [])
    {
        $query["Action"] = "UpdateAuditImageStatus";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('UpdateAuditImageStatus', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("UpdateAuditImageStatus: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function updateImageAuditTaskStatus(array $query = [], array $body = [])
    {
        $query["Action"] = "UpdateImageAuditTaskStatus";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('UpdateImageAuditTaskStatus', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("UpdateImageAuditTaskStatus: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function getImageAuditTasks(array $query = [])
    {
        $query["Action"] = "GetImageAuditTasks";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageAuditTasks', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("GetImageAuditTasks: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function getImageAuditResult(array $query = [])
    {
        $query["Action"] = "GetImageAuditResult";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageAuditResult', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("GetImageAuditResult: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function updateImageAuditTask(array $query = [], array $body = [])
    {
        $query["Action"] = "UpdateImageAuditTask";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('UpdateImageAuditTask', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("UpdateImageAuditTask: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function createImageRetryAuditTask(array $query = [], array $body = [])
    {
        $query["Action"] = "CreateImageRetryAuditTask";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('CreateImageRetryAuditTask', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("CreateImageRetryAuditTask: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }

    public function getAuditEntrysCount(array $query = [])
    {
        $query["Action"] = "GetAuditEntrysCount";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetAuditEntrysCount', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            return sprintf("GetAuditEntrysCount: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]);
        }
        return $responseBody["Result"];
    }


    public function getImageDuplicateDetection(array $body = [])
    {
        $body["Action"] = "GetImageDuplicateDetection";
        $body["Version"] = "2018-08-01";
        $queryStr = http_build_query($body);
        $response = $this->request('GetImageDuplicateDetection', ['query' => $queryStr, 'json' => $body]);
        $segmentResponse = json_decode((string)$response->getBody(), true);
        if (isset($segmentResponse["ResponseMetadata"]["Error"])) {
            return sprintf("GetImageDuplicateDetection: request id %s error %s", $segmentResponse["ResponseMetadata"]["RequestId"], $segmentResponse["ResponseMetadata"]["Error"]["Message"]);
        }
        return $segmentResponse["Result"];
    }

    public function getImageDuplicateDetectionTaskStatus(array $query = [])
    {
        $query["Action"] = "GetDedupTaskStatus";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetDedupTaskStatus', ['query' => $queryStr]);
        $segmentResponse = json_decode((string)$response->getBody(), true);
        if (isset($segmentResponse["ResponseMetadata"]["Error"])) {
            return sprintf("GetDedupTaskStatus: request id %s error %s", $segmentResponse["ResponseMetadata"]["RequestId"], $segmentResponse["ResponseMetadata"]["Error"]["Message"]);
        }
        return $segmentResponse["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXDomainTrafficData($query)
    {
        $query["Action"] = "DescribeImageXDomainTrafficData";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXDomainTrafficData', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXDomainTrafficData: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXDomainBandwidthData($query)
    {
        $query["Action"] = "DescribeImageXDomainBandwidthData";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXDomainBandwidthData', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXDomainBandwidthData: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXBucketUsage($query)
    {
        $query["Action"] = "DescribeImageXBucketUsage";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXBucketUsage', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXBucketUsage: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXRequestCntUsage($query)
    {
        $query["Action"] = "DescribeImageXRequestCntUsage";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXRequestCntUsage', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXRequestCntUsage: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXBaseOpUsage($query)
    {
        $query["Action"] = "DescribeImageXBaseOpUsage";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXBaseOpUsage', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXBaseOpUsage: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXCompressUsage($query)
    {
        $query["Action"] = "DescribeImageXCompressUsage";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCompressUsage', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCompressUsage: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXEdgeRequest($query)
    {
        $query["Action"] = "DescribeImageXEdgeRequest";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXEdgeRequest', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXEdgeRequest: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXHitRateTrafficData($query)
    {
        $query["Action"] = "DescribeImageXHitRateTrafficData";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXHitRateTrafficData', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXHitRateTrafficData: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXHitRateRequestData($query)
    {
        $query["Action"] = "DescribeImageXHitRateRequestData";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXHitRateRequestData', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXHitRateRequestData: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXCDNTopRequestData($query)
    {
        $query["Action"] = "DescribeImageXCDNTopRequestData";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCDNTopRequestData', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCDNTopRequestData: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXSummary($query)
    {
        $query["Action"] = "DescribeImageXSummary";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSummary', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSummary: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXEdgeRequestBandwidth($query)
    {
        $query["Action"] = "DescribeImageXEdgeRequestBandwidth";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXEdgeRequestBandwidth', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXEdgeRequestBandwidth: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXEdgeRequestTraffic($query)
    {
        $query["Action"] = "DescribeImageXEdgeRequestTraffic";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXEdgeRequestTraffic', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXEdgeRequestTraffic: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXEdgeRequestRegions($query)
    {
        $query["Action"] = "DescribeImageXEdgeRequestRegions";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXEdgeRequestRegions', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXEdgeRequestRegions: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXMirrorRequestTraffic($body)
    {
        $query["Action"] = "DescribeImageXMirrorRequestTraffic";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXMirrorRequestTraffic', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXMirrorRequestTraffic: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXMirrorRequestBandwidth($body)
    {
        $query["Action"] = "DescribeImageXMirrorRequestBandwidth";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXMirrorRequestBandwidth', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXMirrorRequestBandwidth: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXMirrorRequestHttpCodeByTime($body)
    {
        $query["Action"] = "DescribeImageXMirrorRequestHttpCodeByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXMirrorRequestHttpCodeByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXMirrorRequestHttpCodeByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXMirrorRequestHttpCodeOverview($body)
    {
        $query["Action"] = "DescribeImageXMirrorRequestHttpCodeOverview";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXMirrorRequestHttpCodeOverview', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXMirrorRequestHttpCodeOverview: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function describeImageXServiceQuality($query)
    {
        $query["Action"] = "DescribeImageXServiceQuality";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXServiceQuality', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXServiceQuality: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function getImageXQueryApps($query)
    {
        $query["Action"] = "GetImageXQueryApps";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageXQueryApps', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("GetImageXQueryApps: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function getImageXQueryRegions($query)
    {
        $query["Action"] = "GetImageXQueryRegions";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageXQueryRegions', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("GetImageXQueryRegions: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function getImageXQueryDims($query)
    {
        $query["Action"] = "GetImageXQueryDims";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageXQueryDims', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("GetImageXQueryDims: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @return array
     * @throws Exception
     */
    public function getImageXQueryVals($query)
    {
        $query["Action"] = "GetImageXQueryVals";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('GetImageXQueryVals', ['query' => $queryStr]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("GetImageXQueryVals: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadSuccessRateByTime($body)
    {
        $query["Action"] = "DescribeImageXUploadSuccessRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadSuccessRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadSuccessRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadErrorCodeAll($body)
    {
        $query["Action"] = "DescribeImageXUploadErrorCodeAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadErrorCodeAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadErrorCodeAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadErrorCodeByTime($body)
    {
        $query["Action"] = "DescribeImageXUploadErrorCodeByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadErrorCodeByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadErrorCodeByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadCountByTime($body)
    {
        $query["Action"] = "DescribeImageXUploadCountByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadCountByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadCountByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadFileSize($body)
    {
        $query["Action"] = "DescribeImageXUploadFileSize";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadFileSize', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadFileSize: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadSpeed($body)
    {
        $query["Action"] = "DescribeImageXUploadSpeed";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadSpeed', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadSpeed: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadDuration($body)
    {
        $query["Action"] = "DescribeImageXUploadDuration";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadDuration', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadDuration: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXUploadSegmentSpeedByTime($body)
    {
        $query["Action"] = "DescribeImageXUploadSegmentSpeedByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXUploadSegmentSpeedByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXUploadSegmentSpeedByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnSuccessRateByTime($body)
    {
        $query["Action"] = "DescribeImageXCdnSuccessRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnSuccessRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnSuccessRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnSuccessRateAll($body)
    {
        $query["Action"] = "DescribeImageXCdnSuccessRateAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnSuccessRateAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnSuccessRateAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnErrorCodeByTime($body)
    {
        $query["Action"] = "DescribeImageXCdnErrorCodeByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnErrorCodeByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnErrorCodeByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnErrorCodeAll($body)
    {
        $query["Action"] = "DescribeImageXCdnErrorCodeAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnErrorCodeAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnErrorCodeAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnDurationDetailByTime($body)
    {
        $query["Action"] = "DescribeImageXCdnDurationDetailByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnDurationDetailByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnDurationDetailByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnDurationAll($body)
    {
        $query["Action"] = "DescribeImageXCdnDurationAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnDurationAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnDurationAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnReuseRateByTime($body)
    {
        $query["Action"] = "DescribeImageXCdnReuseRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnReuseRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnReuseRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnReuseRateAll($body)
    {
        $query["Action"] = "DescribeImageXCdnReuseRateAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnReuseRateAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnReuseRateAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXCdnProtocolRateByTime($body)
    {
        $query["Action"] = "DescribeImageXCdnProtocolRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXCdnProtocolRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXCdnProtocolRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientErrorCodeAll($body)
    {
        $query["Action"] = "DescribeImageXClientErrorCodeAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientErrorCodeAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientErrorCodeAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientErrorCodeByTime($body)
    {
        $query["Action"] = "DescribeImageXClientErrorCodeByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientErrorCodeByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientErrorCodeByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientDecodeSuccessRateByTime($body)
    {
        $query["Action"] = "DescribeImageXClientDecodeSuccessRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientDecodeSuccessRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientDecodeSuccessRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientDecodeDurationByTime($body)
    {
        $query["Action"] = "DescribeImageXClientDecodeDurationByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientDecodeDurationByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientDecodeDurationByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientQueueDurationByTime($body)
    {
        $query["Action"] = "DescribeImageXClientQueueDurationByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientQueueDurationByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientQueueDurationByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientLoadDurationAll($body)
    {
        $query["Action"] = "DescribeImageXClientLoadDurationAll";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientLoadDurationAll', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientLoadDurationAll: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientLoadDuration($body)
    {
        $query["Action"] = "DescribeImageXClientLoadDuration";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientLoadDuration', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientLoadDuration: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientFailureRate($body)
    {
        $query["Action"] = "DescribeImageXClientFailureRate";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientFailureRate', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientFailureRate: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientSdkVerByTime($body)
    {
        $query["Action"] = "DescribeImageXClientSdkVerByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientSdkVerByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientSdkVerByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientFileSize($body)
    {
        $query["Action"] = "DescribeImageXClientFileSize";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientFileSize', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientFileSize: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientTopFileSize($body)
    {
        $query["Action"] = "DescribeImageXClientTopFileSize";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientTopFileSize', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientTopFileSize: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientCountByTime($body)
    {
        $query["Action"] = "DescribeImageXClientCountByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientCountByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientCountByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientScoreByTime($body)
    {
        $query["Action"] = "DescribeImageXClientScoreByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientScoreByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientScoreByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientDemotionRateByTime($body)
    {
        $query["Action"] = "DescribeImageXClientDemotionRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientDemotionRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientDemotionRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientTopDemotionURL($body)
    {
        $query["Action"] = "DescribeImageXClientTopDemotionURL";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientTopDemotionURL', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientTopDemotionURL: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientQualityRateByTime($body)
    {
        $query["Action"] = "DescribeImageXClientQualityRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientQualityRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientQualityRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXClientTopQualityURL($body)
    {
        $query["Action"] = "DescribeImageXClientTopQualityURL";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXClientTopQualityURL', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXClientTopQualityURL: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXSensibleCountByTime($body)
    {
        $query["Action"] = "DescribeImageXSensibleCountByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSensibleCountByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSensibleCountByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXSensibleCacheHitRateByTime($body)
    {
        $query["Action"] = "DescribeImageXSensibleCacheHitRateByTime";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSensibleCacheHitRateByTime', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSensibleCacheHitRateByTime: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXSensibleTopSizeURL($body)
    {
        $query["Action"] = "DescribeImageXSensibleTopSizeURL";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSensibleTopSizeURL', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSensibleTopSizeURL: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXSensibleTopRamURL($body)
    {
        $query["Action"] = "DescribeImageXSensibleTopRamURL";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSensibleTopRamURL', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSensibleTopRamURL: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXSensibleTopResolutionURL($body)
    {
        $query["Action"] = "DescribeImageXSensibleTopResolutionURL";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSensibleTopResolutionURL', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSensibleTopResolutionURL: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageXSensibleTopUnknownURL($body)
    {
        $query["Action"] = "DescribeImageXSensibleTopUnknownURL";
        $query["Version"] = "2018-08-01";
        $queryStr = http_build_query($query);
        $response = $this->request('DescribeImageXSensibleTopUnknownURL', ['query' => $queryStr, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageXSensibleTopUnknownURL: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }

    /**
     * @param $query array
     * @param $body array
     * @return array
     * @throws Exception
     */
    public function describeImageVolcCdnAccessLog($query, $body)
    {
        $response = $this->request('DescribeImageVolcCdnAccessLog', ['query' => $query, 'json' => $body]);
        $responseBody = json_decode((string)$response->getBody(), true);
        if (isset($responseBody["ResponseMetadata"]["Error"])) {
            throw new Exception(sprintf("DescribeImageVolcCdnAccessLog: request id %s error %s", $responseBody["ResponseMetadata"]["RequestId"], $responseBody["ResponseMetadata"]["Error"]["Message"]));
        }
        return $responseBody["Result"];
    }
}
