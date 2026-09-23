<?php


namespace Volc\Service\Vod\Upload;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\NoSeekStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\RetryMiddleware;
use Throwable;
use Volc\Service\Base\Models\Base\ResponseError;
use Volc\Service\Vod\Models\Business\PartUploadInfo;
use Volc\Service\Vod\Models\Business\StorageClassType;
use Volc\Service\Vod\Models\Business\VodStoreInfo;
use Volc\Service\Vod\Models\Business\VpcTosUploadAddress;
use Volc\Service\Vod\Models\Request\VodApplyUploadInfoRequest;
use Volc\Service\Vod\Models\Request\VodCommitUploadInfoRequest;
use Volc\Service\Vod\Models\Request\VodQueryMoveObjectTaskInfoRequest;
use Volc\Service\Vod\Models\Request\VodSubmitMoveObjectTaskRequest;
use Volc\Service\Vod\Models\Request\VodUploadMaterialRequest;
use Volc\Service\Vod\Models\Request\VodUploadMediaRequest;
use Volc\Service\Vod\Models\Response\VodCommitUploadInfoResponse;
use Volc\Service\Vod\Models\Response\VodQueryMoveObjectTaskInfoResponse;
use Volc\Service\Vod\Models\Response\VodSubmitMoveObjectTaskResponse;
use Volc\Service\Vod\Vod;

const MinChunkSize = 1024 * 1024 * 20;
const LargeFileSize = 1024 * 1024 * 1024;
const UploadModeDirect = "direct";
const UploadModePart = "part";
const HeaderCrc64 = "x-tos-hash-crc64ecma";
const HeaderRequestId = "x-tos-request-id";
const HeaderEtag = "ETag";
const QuickCompleteModeEnable = "enable";

class VodUpload extends Vod
{

    public function uploadToB(string $spaceName, string $filePath, string $fileType, string $fileName,
                              string $fileExtension, string $clientNetWorkMode, string $clientIDCMode, int $storageClass, string $uploadHostPrefer, int $chunkSize)
    {
        $fileSize = filesize($filePath);
        $applyRequest = new VodApplyUploadInfoRequest();
        $applyRequest->setSpaceName($spaceName);
        $applyRequest->setFileName($fileName);
        $applyRequest->setFileType($fileType);
        $applyRequest->setFileExtension($fileExtension);
        $applyRequest->setStorageClass($storageClass);
        $applyRequest->setClientNetWorkMode($clientNetWorkMode);
        $applyRequest->setClientIDCMode($clientIDCMode);
        $applyRequest->setNeedFallback(true);
        $applyRequest->setUploadHostPrefer($uploadHostPrefer);
        $applyRequest->setFileSize($fileSize);
        $resp = $this->innerUpload($applyRequest, $filePath, $chunkSize);
        if ($resp[0] != 0) {
            throw new Exception($resp[1]);
        }
        return $resp[2];
    }

    /**
     * @param VodUploadMediaRequest $vodUploadMediaRequest
     * @return VodCommitUploadInfoResponse
     * @throws Exception
     * @throws Throwable
     */
    public function uploadMedia(VodUploadMediaRequest $vodUploadMediaRequest): VodCommitUploadInfoResponse
    {
        $sessionKey = $this->uploadToB($vodUploadMediaRequest->getSpaceName(), $vodUploadMediaRequest->getFilePath(), "media",
            $vodUploadMediaRequest->getFileName(), $vodUploadMediaRequest->getFileExtension(), $vodUploadMediaRequest->getClientNetWorkMode(),
            $vodUploadMediaRequest->getClientIDCMode(), $vodUploadMediaRequest->getStorageClass(), $vodUploadMediaRequest->getUploadHostPrefer(), $vodUploadMediaRequest->getChunkSize());
        $request = new VodCommitUploadInfoRequest();
        $request->setSpaceName($vodUploadMediaRequest->getSpaceName());
        $request->setSessionKey($sessionKey);
        $request->setCallbackArgs($vodUploadMediaRequest->getCallbackArgs());
        $request->setFunctions($vodUploadMediaRequest->getFunctions());
        $request->setExpireTime($vodUploadMediaRequest->getExpireTime());
        try {
            return $this->commitUploadInfo($request);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function uploadMaterial(VodUploadMaterialRequest $vodUploadMaterialRequest): VodCommitUploadInfoResponse
    {
        $sessionKey = $this->uploadToB($vodUploadMaterialRequest->getSpaceName(), $vodUploadMaterialRequest->getFilePath(), $vodUploadMaterialRequest->getFileType(),
            $vodUploadMaterialRequest->getFileName(), $vodUploadMaterialRequest->getFileExtension(), $vodUploadMaterialRequest->getClientNetWorkMode(),
            $vodUploadMaterialRequest->getClientIDCMode(), 0,$vodUploadMaterialRequest->getUploadHostPrefer(), $vodUploadMaterialRequest->getChunkSize());
        $request = new VodCommitUploadInfoRequest();
        $request->setSpaceName($vodUploadMaterialRequest->getSpaceName());
        $request->setSessionKey($sessionKey);
        $request->setCallbackArgs($vodUploadMaterialRequest->getCallbackArgs());
        $request->setFunctions($vodUploadMaterialRequest->getFunctions());
        try {
            return $this->commitUploadInfo($request);
        } catch (Throwable $e) {
            throw $e;
        }
    }


    public function upload(VodApplyUploadInfoRequest $applyRequest, string $filePath): array
    {
        $this->innerUpload($applyRequest, $filePath, MinChunkSize);
    }

    private function innerUpload(VodApplyUploadInfoRequest $applyRequest, string $filePath, int $chunkSize): array
    {
        if (!file_exists($filePath)) {
            return array(-1, "file not exists", "", "");
        }
        if ($chunkSize < MinChunkSize) {
            $chunkSize = MinChunkSize;
        }
        try {
            $response = $this->applyUploadInfo($applyRequest);
            if ($response->getResponseMetadata()->getError() != null) {
                return array(-1, $response->getResponseMetadata()->serializeToJsonString(), "", "");
            }

            // 内网上传
            $vpcTosUploadAddress = $response->getResult()->getData()->getVpcTosUploadAddress();
            if ($vpcTosUploadAddress != null) {
                $vpcRespCode = $this->vpcUploadFile($vpcTosUploadAddress, $filePath);
                if ($vpcRespCode != 0){
                    return array(-1, "vpc upload " . $filePath . " error", "", "");
                }

                $uploadAddress = $response->getResult()->getData()->getUploadAddress();
                $oid = ($uploadAddress->getStoreInfos())[0]->getStoreUri();
                $session = $uploadAddress->getSessionKey();
                return array(0, "", $session, $oid);
            }

            $candidateUploadAddress = $response->getResult()->getData()->getCandidateUploadAddresses();
            $allUploadAddress = array();
            if ($candidateUploadAddress != null) {
                $allUploadAddress = array_merge(iterator_to_array($candidateUploadAddress->getMainUploadAddresses()),
                    iterator_to_array($candidateUploadAddress->getBackupUploadAddresses()),
                    iterator_to_array($candidateUploadAddress->getFallbackUploadAddresses()));
            }

            if (count($allUploadAddress) > 0) {
                foreach ($allUploadAddress as $uploadAddress) {
                    if (count($uploadAddress->getUploadHosts()) == 0 || count($uploadAddress->getStoreInfos()) == 0 && $uploadAddress->getStoreInfos()[0] == null) {
                        continue;
                    }
                    $uploadHost = ($uploadAddress->getUploadHosts())[0];
                    $oid = ($uploadAddress->getStoreInfos())[0]->getStoreUri();
                    $auth = ($uploadAddress->getStoreInfos())[0]->getAuth();
                    $session = $uploadAddress->getSessionKey();
                    $storeInfo = new VodStoreInfo();
                    $storeInfo->setStoreUri($oid);
                    $storeInfo->setAuth($auth);
                    try {
                        $respCode = $this->uploadFile($uploadHost, $storeInfo, $filePath, $applyRequest->getStorageClass(), $chunkSize);
                        if ($respCode != 0) {
                            continue;
                        }
                        return array(0, "", $session, $oid);
                    } catch (Throwable $e) {
                        continue;
                    }
                }
                return array(-1, "upload " . $filePath . " error", "", "");
            } else {
                $uploadAddress = $response->getResult()->getData()->getUploadAddress();
                $uploadHost = ($uploadAddress->getUploadHosts())[0];
                $oid = ($uploadAddress->getStoreInfos())[0]->getStoreUri();
                $session = $uploadAddress->getSessionKey();
                $storeInfo = new VodStoreInfo();
                $storeInfo->mergeFrom(($uploadAddress->getStoreInfos())[0]);

                $respCode = $this->uploadFile($uploadHost, $storeInfo, $filePath, $applyRequest->getStorageClass(), $chunkSize);
                if ($respCode != 0) {
                    return array(-1, "upload " . $filePath . " error", "", "");
                }

                return array(0, "", $session, $oid);
            }
        } catch (Throwable $e) {
            return array(-1, $e->getMessage(), "", "");
        }
    }

    public function uploadFile(string $uploadHost, VodStoreInfo $storeInfo, string $filePath, int $storageClass, int $chunkSize): int
    {
        if (!file_exists($filePath)) {
            return -1;
        }

        $oid = $storeInfo->getStoreUri();
        $auth = $storeInfo->getAuth();

        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

        $timeout = 30.0;
        $fileSize = filesize($filePath);

        if ($fileSize > LargeFileSize) {
            $timeout = 600.0;
        }
        $client = new Client([
            'base_uri' => "https://" . $uploadHost,
            'timeout' => $timeout,
            'handler' => $handlerStack,
        ]);

        if ($fileSize < $chunkSize) {
            return $this->directUpload($oid, $auth, $filePath, $client, $storageClass);
        } else {
            return $this->chunkUpload($oid, $auth, $filePath, true, $client, $storageClass, $chunkSize);
        }
    }

    public function retryDecider(): \Closure
    {
        return function ($retries,
                         Request $request,
                         Response $response = null,
                         RequestException $exception = null) {
            if ($retries >= 3) {
                return false;
            }
            return false;
        };
    }

    public function retryDelay(): \Closure
    {
        return function ($num) {
            return RetryMiddleware::exponentialDelay($num);
        };
    }

    private function directUpload(string $oid, string $auth, string $filePath, Client $client, int $storageClass): int
    {
        $content = file_get_contents($filePath);
        $crc32 = sprintf("%08x", crc32($content));
        $headers = ['Authorization' => $auth, 'Content-CRC32' => $crc32];

        if ($storageClass == StorageClassType::Archive) {
            $headers['X-Upload-Storage-Class'] = "archive";
        }
        if ($storageClass == StorageClassType::IA) {
            $headers['X-Upload-Storage-Class'] = "ia";
        }

        $response = $client->put($oid, ["body" => $content, "headers" => $headers]);
        $directUploadResponse = json_decode((string)$response->getBody(), true);
        if (!isset($directUploadResponse["success"]) || $directUploadResponse["success"] != 0) {
            return -2;
        }
        return 0;
    }

    private function chunkUpload(string $oid, string $auth, string $filePath, bool $isLargeFile, Client $client, int $storageClass, int $chunkSize): int
    {
        $uploadID = $this->initUploadPart($oid, $auth, $isLargeFile, $client, $storageClass);
        if ($uploadID == "") {
            return -1;
        }
        $fileSize = filesize($filePath);
        $num = floor($fileSize / $chunkSize);
        $lastNum = $num - 1;
        $fp = fopen($filePath, 'r');
        $checkSum = [];
        $objectContentType = "";
        for ($i = 0; $i < $lastNum; $i++) {
            $data = stream_get_contents($fp, $chunkSize, $i * $chunkSize);
            $partNumber = $i;
            if ($isLargeFile) {
                $partNumber = $partNumber + 1;
            }
            $uploadPartResp = $this->uploadPart($oid, $auth, $uploadID, $partNumber, $data, $isLargeFile, $client, $storageClass);
            $crc32 = $uploadPartResp[0];
            $oct = $uploadPartResp[1];
            if ($partNumber == 1) {
                $objectContentType = $oct;
            }
            if ($crc32 == "") {
                return -1;
            }
            $checkSum[] = $crc32;
        }
        $maxLength = $fileSize - $lastNum * $chunkSize;
        $data = stream_get_contents($fp, $maxLength, $lastNum * $chunkSize);
        if ($isLargeFile) {
            $lastNum = $lastNum + 1;
        }
        $uploadPartResp = $this->uploadPart($oid, $auth, $uploadID, $lastNum, $data, $isLargeFile, $client, $storageClass);
        $crc32 = $uploadPartResp[0];
        $oct = $uploadPartResp[1];
        if ($lastNum == 1) {
            $objectContentType = $oct;
        }
        if ($crc32 == "") {
            return -1;
        }
        $checkSum[] = $crc32;
        return $this->uploadMergePart($oid, $auth, $uploadID, $objectContentType, $checkSum, $isLargeFile, $client, $storageClass);
    }

    private function initUploadPart(string $oid, string $auth, bool $isLargeFile, Client $client, int $storageClass)
    {
        $headers = ['Authorization' => $auth];
        if ($isLargeFile) {
            $headers['X-Storage-Mode'] = ['gateway'];
        }
        if ($storageClass == StorageClassType::Archive) {
            $headers['X-Upload-Storage-Class'] = ['archive'];
        }
        if ($storageClass == StorageClassType::IA) {
            $headers['X-Upload-Storage-Class'] = "ia";
        }
        $response = $client->put($oid . '?uploads', ['headers' => $headers]);
        $initUploadResponse = json_decode((string)$response->getBody(), true);
        if (!isset($initUploadResponse["success"]) || $initUploadResponse["success"] != 0) {
            return "";
        }
        return $initUploadResponse['payload']['uploadID'];
    }

    private function uploadPart(string $oid, string $auth, string $uploadID, int $partNumber, $data, bool $isLargeFile, Client $client, int $storageClass): array
    {
        $uri = sprintf("%s?partNumber=%d&uploadID=%s", $oid, $partNumber, $uploadID);
        $crc32 = sprintf("%08x", crc32($data));
        $headers = ['Authorization' => $auth, 'Content-CRC32' => $crc32];
        if ($isLargeFile) {
            $headers['X-Storage-Mode'] = ['gateway'];
        }
        if ($storageClass == StorageClassType::Archive) {
            $headers['X-Upload-Storage-Class'] = ['archive'];
        }
        if ($storageClass == StorageClassType::IA) {
            $headers['X-Upload-Storage-Class'] = "ia";
        }
        $response = $client->put($uri, ['headers' => $headers, 'body' => $data]);
        $uploadPartResponse = json_decode((string)$response->getBody(), true);
        if (!isset($uploadPartResponse["success"]) || $uploadPartResponse["success"] != 0) {
            return ["", ""];
        }
        return [$crc32, $uploadPartResponse["payload"]["meta"]["ObjectContentType"]];
    }

    private function uploadMergePart(string $oid, string $auth, string $uploadID, string $objectContentType, array $checkSum, bool $isLargeFile, Client $client, int $storageClass): int
    {
        $uri = sprintf("%s?uploadID=%s&ObjectContentType=%s", $oid, $uploadID, $objectContentType);
        $m = [];
        for ($i = 0; $i < count($checkSum); $i++) {
            $no = $i;
            if ($isLargeFile) {
                $no = $i + 1;
            }
            $m[] = sprintf("%d:%s", $no, $checkSum[$i]);
        }
        $body = implode(",", $m);
        $headers = ['Authorization' => $auth];
        if ($isLargeFile) {
            $headers['X-Storage-Mode'] = ['gateway'];
        }
        if ($storageClass == StorageClassType::Archive) {
            $headers['X-Upload-Storage-Class'] = ['archive'];
        }
        if ($storageClass == StorageClassType::IA) {
            $headers['X-Upload-Storage-Class'] = "ia";
        }

        $response = $client->put($uri, ['headers' => $headers, 'body' => $body]);
        $uploadPartResponse = json_decode((string)$response->getBody(), true);
        if (!isset($uploadPartResponse["success"]) || $uploadPartResponse["success"] != 0) {
            return -1;
        }
        return 0;
    }

    public function moveObjectCrossSpace(VodSubmitMoveObjectTaskRequest $req, int $cycleNum): VodQueryMoveObjectTaskInfoResponse
    {
        $submitResponse = new VodSubmitMoveObjectTaskResponse();
        try {
            $submitResponse = $this->submitMoveObjectTask($req);
        } catch (Exception $e) {
            echo $e, "\n";
        } catch (Throwable $e) {
            echo $e, "\n";
        }

        $response = new VodQueryMoveObjectTaskInfoResponse();
        if ($submitResponse != null && $submitResponse->getResponseMetadata() != null && $submitResponse->getResponseMetadata()->getError() != null) {
            $response->setResponseMetadata($submitResponse->getResponseMetadata());
            return $response;
        }

        if ($cycleNum == 0) {
            $cycleNum = 600;
        }

        $request = new VodQueryMoveObjectTaskInfoRequest();
        $request->setTaskId($submitResponse->getResult()->getData()->getTaskId());
        $request->setSourceSpace($submitResponse->getResult()->getData()->getSourceSpace());
        $request->setTargetSpace($submitResponse->getResult()->getData()->getTargetSpace());

        for ($i = 0; $i < $cycleNum; $i++) {
            try {
                $response = $this->queryMoveObjectTaskInfo($request);
            } catch (Exception $e) {
                echo $e, "\n";
            } catch (Throwable $e) {
                echo $e, "\n";
            }

            if ($response != null && $response->getResponseMetadata() != null && $response->getResponseMetadata()->getError() != null) {
                return $response;
            }
            if ($response->getResult()->getData()->getState() == "success" || $response->getResult()->getData()->getState() == "failed") {
                return $response;
            }
            sleep(1);
        }

        $customError = new ResponseError();
        $customError->setCode("502");
        $customError->setMessage("task run time out, please retry or contact volc assistant");
        $submitResponse->getResponseMetadata()->setError($customError);
        $response->setResponseMetadata($submitResponse->getResponseMetadata());
        return $response;
    }

    public function vpcUploadFile(VpcTosUploadAddress $vpcUploadInfo, string $filePath): int
    {
        if (!file_exists($filePath)) {
            return -1;
        }

        if ($vpcUploadInfo->getQuickCompleteMode() == QuickCompleteModeEnable) {
            return 0;
        }

        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

        $timeout = 900.0;

        $client = new Client([
            'timeout' => $timeout,
            'handler' => $handlerStack,
        ]);

        if ($vpcUploadInfo->getUploadMode() == UploadModeDirect) {
            return $this->vpcPut($vpcUploadInfo->getPutUrl(), $filePath, $client, iterator_to_array($vpcUploadInfo->getPutUrlHeaders()));
        } else if ($vpcUploadInfo->getUploadMode() == UploadModePart) {
            return $this->vpcPartUpload($vpcUploadInfo->getPartUploadInfo(), $filePath,  $client);
        }
        return -1;
    }

    private function vpcPartUpload(PartUploadInfo $partUploadInfo, string $filePath, Client $client): int
    {
        $fileSize = filesize($filePath);
        $chunkSize = $partUploadInfo->getPartSize();
        $num = floor($fileSize / $chunkSize);

        $partUrls = iterator_to_array($partUploadInfo->getPartPutUrls());
        $completeBody = ['Parts' => []];


        for ($i = 0; $i < $num; $i++) {
            $partNumber = $i + 1;

            $partResp = $this->vpcPartPut($partUrls[$i],$filePath, $i * $chunkSize, $chunkSize, $client,[]);
            if ($partResp['res'] != 0){
                return -1;
            }
            $completeBody['Parts'][] = ['PartNumber'=>$partNumber, 'ETag'=>$partResp['etag']];
        }

        $lastChunkSize = $fileSize - $num * $chunkSize;

        $partResp = $this->vpcPartPut($partUrls[$i],$filePath, $num * $chunkSize, $lastChunkSize, $client,[]);
        if ($partResp['res'] != 0){
            return -1;
        }
        $completeBody['Parts'][] = ['PartNumber'=>$num+1, 'ETag'=>$partResp['etag']];
        $postBody = json_encode($completeBody);

        return $this->vpcPost($partUploadInfo->getCompletePartUrl(), $postBody, $client, iterator_to_array($partUploadInfo->getCompleteUrlHeaders()));
    }

    public function vpcPartPut(string $url, string $filePath,int $offset, int $partSize, Client $client, array $headers): array
    {
        $res = ['res'=>0];
        try {
            $file = fopen($filePath, 'r');
            $fileSize = filesize($filePath);
            if ($offset < 0 || $offset > $fileSize) {
                throw new Exception('invalid offset');
            } else if ($offset > 0) {
                fseek($file, $offset, 0);
            }

            $body = new NoSeekStream(new Stream($file));
            $headers['Content-Length'] = $partSize;

            $response = $client->put($url, ["body" => $body, "headers" => $headers]);
            if ($response == null || $response->getStatusCode() != 200) {
                $reqId = $response->getHeaderLine(HeaderRequestId);
                echo 'Vpc Part Upload Failed. Req Id: ' . $reqId . PHP_EOL;
                $res['res'] = -1;
                return $res;
            }

            $etag = $response->getHeaderLine(HeaderEtag);
            $res['etag'] = $etag;
        } catch (Exception $e) {
            echo $e, "\n";
            $res['res'] = -2;
            return $res;
        } catch (Throwable $e) {
            echo $e, "\n";
            $res['res'] = -2;
            return $res;
        }

        return $res;
    }

    public function vpcPost(string $url,string $body, Client $client, array $headers): int
    {
        try {
            $response = $client->post($url, ["body" => $body, "headers" => $headers]);

            if ($response == null || $response->getStatusCode() != 200) {
                $reqId = $response->getHeaderLine(HeaderRequestId);
                echo 'Vpc Direct Upload Failed. Req Id: ' . $reqId . PHP_EOL;
                return -1;
            }

        } catch (Exception $e) {
            echo $e, "\n";
            return -2;
        } catch (Throwable $e) {
            echo $e, "\n";
            return -2;
        }
        return 0;
    }

    public function vpcPut(string $url, string $filePath, Client $client, array $headers): int
    {
        try {
            $file = fopen($filePath, 'r');
            $body = new NoSeekStream(new Stream($file));

            $response = $client->put($url, ["body" => $body, "headers" => $headers]);
            if ($response == null || $response->getStatusCode() != 200) {
                $reqId = $response->getHeaderLine(HeaderRequestId);
                echo 'Vpc Direct Upload Failed. Req Id: ' . $reqId . PHP_EOL;
                return -1;
            }

        } catch (Exception $e) {
            echo $e, "\n";
            return -2;
        } catch (Throwable $e) {
            echo $e, "\n";
            return -2;
        }

        return 0;
    }
}