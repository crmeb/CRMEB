<?php

namespace Volc\Service\Imp;

use Exception;
use Throwable;

class ImpUtils {

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
