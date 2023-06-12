<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\upload\extend\cos;


class Sts
{
// 临时密钥计算样例
    function _hex2bin($data)
    {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }

    // obj 转 query string
    function json2str($obj, $notEncode = false)
    {
        ksort($obj);
        $arr = array();
        if (!is_array($obj)) {
            throw new \Exception('$obj must be an array, the actual value is:' . json_encode($obj));
        }
        foreach ($obj as $key => $val) {
            array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
        }
        return join('&', $arr);
    }

    // 计算临时密钥用的签名
    function getSignature($opt, $key, $method, $config)
    {
        $host = "sts.tencentcloudapi.com";

        if (array_key_exists('domain', $config)) {
            $host = $config['domain'];
        }

        if (array_key_exists('endpoint', $config)) {
            $host = "sts." . $config['endpoint'];
        }

        $formatString = $method . $host . '/?' . $this->json2str($opt, 1);
        $sign = hash_hmac('sha1', $formatString, $key);
        $sign = base64_encode($this->_hex2bin($sign));
        return $sign;
    }

    // v2接口的key首字母小写，v3改成大写，此处做了向下兼容
    function backwardCompat($result)
    {
        if (!is_array($result)) {
            throw new \Exception('$result must be an array, the actual value is:' . json_encode($result));
        }
        $compat = array();
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $compat[lcfirst($key)] = $this->backwardCompat($value);
            } elseif ($key == 'Token') {
                $compat['sessionToken'] = $value;
            } else {
                $compat[lcfirst($key)] = $value;
            }
        }
        return $compat;
    }

    // 获取临时密钥
    function getTempKeys($config)
    {
        $result = null;
        try {
            if (array_key_exists('policy', $config)) {
                $policy = $config['policy'];
            } else {
                if (array_key_exists('bucket', $config)) {
                    $ShortBucketName = substr($config['bucket'], 0, strripos($config['bucket'], '-'));
                    $AppId = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
                } else {
                    throw new \Exception("bucket== null");
                }
                if (array_key_exists('allowPrefix', $config)) {
                    if (!(strpos($config['allowPrefix'], '/') === 0)) {
                        $config['allowPrefix'] = '/' . $config['allowPrefix'];
                    }
                } else {
                    throw new \Exception("allowPrefix == null");
                }
                if (!array_key_exists('region', $config)) {
                    throw new \Exception("region == null");
                }
                $policy = array(
                    'version' => '2.0',
                    'statement' => array(
                        array(
                            'action' => $config['allowActions'],
                            'effect' => 'allow',
                            'resource' => array(
                                'qcs::cos:' . $config['region'] . ':uid/' . $AppId . ':' . $config['bucket'] . $config['allowPrefix']
                            )
                        )
                    )
                );
            }
            $policyStr = str_replace('\\/', '/', json_encode($policy));
            $Action = 'GetFederationToken';
            $Nonce = rand(10000, 20000);
            $Timestamp = time();
            $Method = 'POST';
            if (array_key_exists('durationSeconds', $config)) {
                if (!(is_integer($config['durationSeconds']))) {
                    throw new \Exception("durationSeconds must be a int type");
                }
            }
            $params = array(
                'SecretId' => $config['secretId'],
                'Timestamp' => $Timestamp,
                'Nonce' => $Nonce,
                'Action' => $Action,
                'DurationSeconds' => $config['durationSeconds'],
                'Version' => '2018-08-13',
                'Name' => 'cos',
                'Region' => $config['region'],
                'Policy' => urlencode($policyStr)
            );
            $params['Signature'] = $this->getSignature($params, $config['secretKey'], $Method, $config);
            $url = 'https://sts.tencentcloudapi.com/';

            if (array_key_exists('url', $config)) {
                $url = $config['url'];
            }

            if (!array_key_exists('url', $config) && array_key_exists('domain', $config)) {
                $url = 'https://sts.' . $config['domain'];
            }

            if (array_key_exists('endpoint', $config)) {
                $url = 'https://sts.' . $config['endpoint'];
            }

            $ch = curl_init($url);
            if (array_key_exists('proxy', $config)) {
                $config['proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['proxy']);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->json2str($params));
            $result = curl_exec($ch);
            if (curl_errno($ch)) $result = curl_error($ch);
            curl_close($ch);
            $result = json_decode($result, 1);
            if (isset($result['Response'])) {
                $result = $result['Response'];
                if (isset($result['Error'])) {
                    throw new \Exception("get cam failed");
                }
                $result['startTime'] = $result['ExpiredTime'] - $config['durationSeconds'];
            }
            $result = $this->backwardCompat($result);
            return $result;
        } catch (\Exception $e) {
            if ($result == null) {
                $result = "error: " . $e->getMessage();
            } else {
                $result = json_encode($result);
            }
            throw new \Exception($result);
        }
    }

    //申请角色授权
    function getRoleCredential($config)
    {
        $result = null;
        try {
            if (array_key_exists('policy', $config)) {
                $policy = $config['policy'];
            } else {
                if (array_key_exists('bucket', $config)) {
                    $ShortBucketName = substr($config['bucket'], 0, strripos($config['bucket'], '-'));
                    $AppId = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
                } else {
                    throw new \Exception("bucket== null");
                }
                if (array_key_exists('allowPrefix', $config)) {
                    if (!(strpos($config['allowPrefix'], '/') === 0)) {
                        $config['allowPrefix'] = '/' . $config['allowPrefix'];
                    }
                } else {
                    throw new \Exception("allowPrefix == null");
                }
                if (!array_key_exists('region', $config)) {
                    throw new \Exception("region == null");
                }
                $policy = array(
                    'version' => '2.0',
                    'statement' => array(
                        array(
                            'action' => $config['allowActions'],
                            'effect' => 'allow',
                            'resource' => array(
                                'qcs::cos:' . $config['region'] . ':uid/' . $AppId . ':' . $config['bucket'] . $config['allowPrefix']
                            )
                        )
                    )
                );
            }
            if (array_key_exists('roleArn', $config)) {
                $RoleArn = $config['roleArn'];
            } else {
                throw new \Exception("roleArn == null");
            }
            $policyStr = str_replace('\\/', '/', json_encode($policy));
            $Action = 'AssumeRole';
            $Nonce = rand(10000, 20000);
            $Timestamp = time();
            $Method = 'POST';
            $ExternalId = "";
            if (array_key_exists('externalId', $config)) {
                $ExternalId = $config['externalId'];
            }
            if (array_key_exists('durationSeconds', $config)) {
                if (!(is_integer($config['durationSeconds']))) {
                    throw new \Exception("durationSeconds must be a int type");
                }
            }
            $params = array(
                'SecretId' => $config['secretId'],
                'Timestamp' => $Timestamp,
                'RoleArn' => $RoleArn,
                'Action' => $Action,
                'Nonce' => $Nonce,
                'DurationSeconds' => $config['durationSeconds'],
                'Version' => '2018-08-13',
                'RoleSessionName' => 'cos',
                'Region' => $config['region'],
                'ExternalId' => $ExternalId,
                'Policy' => urlencode($policyStr)
            );
            $params['Signature'] = $this->getSignature($params, $config['secretKey'], $Method, $config);
            $url = 'https://sts.internal.tencentcloudapi.com/';

            if (array_key_exists('endpoint', $config)) {
                $url = 'https://sts.' . $config['endpoint'];
            }
            $ch = curl_init($url);
            if (array_key_exists('proxy', $config)) {
                $config['proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['proxy']);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->json2str($params));
            $result = curl_exec($ch);
            if (curl_errno($ch)) $result = curl_error($ch);
            curl_close($ch);
            $result = json_decode($result, 1);
            if (isset($result['Response'])) {
                $result = $result['Response'];
                if (isset($result['Error'])) {
                    throw new \Exception("get cam failed");
                }
                $result['startTime'] = $result['ExpiredTime'] - $config['durationSeconds'];
            }
            $result = $this->backwardCompat($result);
            return $result;
        } catch (\Exception $e) {
            if ($result == null) {
                $result = "error: " . $e->getMessage();
            } else {
                $result = json_encode($result);
            }
            throw new \Exception($result);
        }
    }


    // get policy
    function getPolicy($scopes)
    {
        if (!is_array($scopes)) {
            return null;
        }
        $statements = array();

        for ($i = 0, $counts = count($scopes); $i < $counts; $i++) {
            $actions = array();
            $resources = array();
            array_push($actions, $scopes[$i]->get_action());
            array_push($resources, $scopes[$i]->get_resource());

            $statement = array(
                'action' => $actions,
                'effect' => $scopes[$i]->get_effect(),
                'resource' => $resources
            );
            array_push($statements, $statement);
        }

        $policy = array(
            'version' => '2.0',
            'statement' => $statements
        );
        return $policy;
    }
}
