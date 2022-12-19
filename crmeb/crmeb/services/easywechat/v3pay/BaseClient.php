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

namespace crmeb\services\easywechat\v3pay;


use crmeb\exceptions\PayException;
use crmeb\services\easywechat\Application;
use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Exceptions\HttpException;
use EasyWeChat\Core\Exceptions\InvalidConfigException;
use EasyWeChat\Core\Http;
use EasyWeChat\Encryption\EncryptionException;
use think\exception\InvalidArgumentException;


class BaseClient extends AbstractAPI
{

    use Certficates;

    /**
     * @var Application
     */
    protected $app;

    const BASE_URL = 'https://api.mch.weixin.qq.com/';

    const KEY_LENGTH_BYTE = 32;

    const AUTH_TAG_LENGTH_BYTE = 16;

    /**
     * BaseClient constructor.
     * @param AccessToken $accessToken
     * @param $app
     */
    public function __construct(AccessToken $accessToken, $app)
    {
        parent::__construct($accessToken);
        $this->app = $app;
    }

    /**
     * request.
     *
     * @param string $endpoint
     * @param string $method
     * @param array $options
     * @param bool $returnResponse
     */
    public function request(string $endpoint, string $method = 'POST', array $options = [], $serial = true)
    {
        $body = $options['body'] ?? '';

        if (isset($options['json'])) {
            $body = json_encode($options['json']);
            $options['body'] = $body;
            unset($options['json']);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'curl',
            'Accept' => 'application/json',
            'Authorization' => $this->getAuthorization($endpoint, $method, $body),
        ];

        $options['headers'] = array_merge($headers, ($options['headers'] ?? []));

        if ($serial) {
            $options['headers']['Wechatpay-Serial'] = $this->getCertficatescAttr('serial_no');
        }

        return $this->_doRequestCurl($method, self::BASE_URL . $endpoint, $options);
    }

    /**
     * @param $method
     * @param $location
     * @param array $options
     * @return mixed
     */
    private function _doRequestCurl($method, $location, $options = [])
    {
        $curl = curl_init();
        // POST数据设置
        if (strtolower($method) === 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data'] ?? $options['body'] ?? '');
        }
        // CURL头信息设置
        if (!empty($options['headers'])) {
            $headers = [];
            foreach ($options['headers'] as $k => $v) {
                $headers[] = "$k: $v";
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $location);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);
        return json_decode(substr($content, $headerSize), true);
    }

    /**
     * To id card, mobile phone number and other fields sensitive information encryption.
     *
     * @param string $string
     *
     * @return string
     */
    protected function encryptSensitiveInformation(string $string)
    {
        $certificates = $this->app->certficates->get()['certificates'];
        if (null === $certificates) {
            throw new InvalidConfigException('config certificate connot be empty.');
        }
        $encrypted = '';
        if (openssl_public_encrypt($string, $encrypted, $certificates, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new EncryptionException('Encryption of sensitive information failed');
        }
        return $sign;
    }


    /**
     * @param string $url
     * @param string $method
     * @param string $body
     * @return string
     */
    protected function getAuthorization(string $url, string $method, string $body)
    {
        $nonceStr = uniqid();
        $timestamp = time();
        $message = $method . "\n" .
            '/' . $url . "\n" .
            $timestamp . "\n" .
            $nonceStr . "\n" .
            $body . "\n";
        openssl_sign($message, $raw_sign, $this->getPrivateKey(), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $schema = 'WECHATPAY2-SHA256-RSA2048 ';
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->app['config']['v3_payment']['mchid'], $nonceStr, $timestamp, $this->app['config']['v3_payment']['serial_no'], $sign);
        return $schema . $token;
    }

    /**
     * 获取商户私钥
     * @return bool|resource
     */
    protected function getPrivateKey()
    {
        $key_path = $this->app['config']['v3_payment']['key_path'];
        if (!file_exists($key_path)) {
            throw new \InvalidArgumentException(
                "SSL certificate not found: {$key_path}"
            );
        }
        return openssl_pkey_get_private(file_get_contents($key_path));
    }

    /**
     * 获取商户公钥
     * @return bool|resource
     */
    protected function getPublicKey()
    {
        $key_path = $this->app['config']['v3_payment']['cert_path'];
        if (!file_exists($key_path)) {
            throw new \InvalidArgumentException(
                "SSL certificate not found: {$key_path}"
            );
        }
        return openssl_pkey_get_public(file_get_contents($key_path));
    }

    /**
     * 替换url
     * @param string $url
     * @param $search
     * @param $replace
     * @return array|string|string[]
     */
    public function getApiUrl(string $url, $search, $replace)
    {
        $newSearch = [];
        foreach ($search as $key) {
            $newSearch[] = '{' . $key . '}';
        }
        return str_replace($newSearch, $replace, $url);
    }

    /**
     * @param int $padding
     */
    private static function paddingModeLimitedCheck(int $padding): void
    {
        if (!($padding === OPENSSL_PKCS1_OAEP_PADDING || $padding === OPENSSL_PKCS1_PADDING)) {
            throw new PayException(sprintf("Doesn't supported padding mode(%d), here only support OPENSSL_PKCS1_OAEP_PADDING or OPENSSL_PKCS1_PADDING.", $padding));
        }
    }

    /**
     * 加密数据
     * @param string $plaintext
     * @param int $padding
     * @return string
     */
    public function encryptor(string $plaintext, int $padding = OPENSSL_PKCS1_OAEP_PADDING)
    {
        self::paddingModeLimitedCheck($padding);

        if (!openssl_public_encrypt($plaintext, $encrypted, $this->getPublicKey(), $padding)) {
            throw new PayException('Encrypting the input $plaintext failed, please checking your $publicKey whether or nor correct.');
        }

        return base64_encode($encrypted);
    }

    /**
     * decrypt ciphertext.
     *
     * @param array $encryptCertificate
     *
     * @return string
     */
    public function decrypt(array $encryptCertificate)
    {
        $ciphertext = base64_decode($encryptCertificate['ciphertext'], true);
        $associatedData = $encryptCertificate['associated_data'];
        $nonceStr = $encryptCertificate['nonce'];
        $aesKey = $this->app['config']['v3_payment']['key'];

        try {
            // ext-sodium (default installed on >= PHP 7.2)
            if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
                return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
            }
            // ext-libsodium (need install libsodium-php 1.x via pecl)
            if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
                return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
            }
            // openssl (PHP >= 7.1 support AEAD)
            if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
                $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
                $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);
                return \openssl_decrypt($ctext, 'aes-256-gcm', $aesKey, \OPENSSL_RAW_DATA, $nonceStr, $authTag, $associatedData);
            }
        } catch (\Exception $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        } catch (\SodiumException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }
        throw new InvalidArgumentException('AEAD_AES_256_GCM 需要 PHP 7.1 以上或者安装 libsodium-php');
    }
}

