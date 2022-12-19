<?php

namespace Volc\Service\Live;

use Exception;
use Throwable;
use Volc\Service\Live\Models\Request\UpdateDenyConfigRequest;

class LiveUtils {

    public static function formatRequestParamV2($req): array
    {
        try {
            $json_encode = json_encode($req);

            $query = json_decode($json_encode, true);
            foreach ($query as $key => $value) {
                switch (gettype($value)) {
                    case 'integer':
                    case 'string':
                    case 'double':
                    case 'NULL':
                    case 'boolean':
                        break;
                    default:
                        $d = json_encode($value);
                        $query[$key] = $d;
                }
            }
        } catch (Exception $e) {
            echo $e, "\n";
            throw $e;
        } catch (Throwable $t) {
            echo $t, "\n";
            throw $t;
        }
        return $query;
    }

    public static function formatRequestParam($req): array
    {
        try {
            $jsonData = $req->serializeToJsonString();
            $query = json_decode($jsonData, true);
            foreach ($query as $key => $value) {
                switch (gettype($query[$key])) {
                    case 'integer':
                    case 'string':
                    case 'double':
                    case 'NULL':
                    case 'boolean':
                        break;
                    default:
                        $d = json_encode($value);
                        $query[$key] = $d;
                }
            }
        } catch (Exception $e) {
            echo $e, "\n";
            throw $e;
        } catch (Throwable $t) {
            echo $t, "\n";
            throw $t;
        }
        return $query;
    }

    public static function parseResponseData($response, $respData)
    {
        try {
            $respData->mergeFromJsonString($response->getBody(), true);
        } catch (Exception $e) {
            echo $e, "\n";
            if ($respData == null || $respData->getResponseMetadata() == null) {
                throw new Exception($response->getReasonPhrase());
            }
        } catch (Throwable $t) {
            echo $t, "\n";
            if ($respData == null || $respData->getResponseMetadata() == null) {
                throw new Exception($response->getReasonPhrase());
            }
        }
        return $respData;
    }

}
