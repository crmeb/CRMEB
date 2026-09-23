<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Base\OAuth\Models;

use AlibabaCloud\Tea\Model;

class AlipaySystemOauthTokenResponse extends Model {
    protected $_name = [
        'httpBody' => 'http_body',
        'code' => 'code',
        'msg' => 'msg',
        'subCode' => 'sub_code',
        'subMsg' => 'sub_msg',
        'userId' => 'user_id',
        'accessToken' => 'access_token',
        'expiresIn' => 'expires_in',
        'refreshToken' => 'refresh_token',
        'reExpiresIn' => 're_expires_in',
    ];
    public function validate() {
        Model::validateRequired('httpBody', $this->httpBody, true);
        Model::validateRequired('code', $this->code, true);
        Model::validateRequired('msg', $this->msg, true);
        Model::validateRequired('subCode', $this->subCode, true);
        Model::validateRequired('subMsg', $this->subMsg, true);
        Model::validateRequired('userId', $this->userId, true);
        Model::validateRequired('accessToken', $this->accessToken, true);
        Model::validateRequired('expiresIn', $this->expiresIn, true);
        Model::validateRequired('refreshToken', $this->refreshToken, true);
        Model::validateRequired('reExpiresIn', $this->reExpiresIn, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->httpBody) {
            $res['http_body'] = $this->httpBody;
        }
        if (null !== $this->code) {
            $res['code'] = $this->code;
        }
        if (null !== $this->msg) {
            $res['msg'] = $this->msg;
        }
        if (null !== $this->subCode) {
            $res['sub_code'] = $this->subCode;
        }
        if (null !== $this->subMsg) {
            $res['sub_msg'] = $this->subMsg;
        }
        if (null !== $this->userId) {
            $res['user_id'] = $this->userId;
        }
        if (null !== $this->accessToken) {
            $res['access_token'] = $this->accessToken;
        }
        if (null !== $this->expiresIn) {
            $res['expires_in'] = $this->expiresIn;
        }
        if (null !== $this->refreshToken) {
            $res['refresh_token'] = $this->refreshToken;
        }
        if (null !== $this->reExpiresIn) {
            $res['re_expires_in'] = $this->reExpiresIn;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return AlipaySystemOauthTokenResponse
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['http_body'])){
            $model->httpBody = $map['http_body'];
        }
        if(isset($map['code'])){
            $model->code = $map['code'];
        }
        if(isset($map['msg'])){
            $model->msg = $map['msg'];
        }
        if(isset($map['sub_code'])){
            $model->subCode = $map['sub_code'];
        }
        if(isset($map['sub_msg'])){
            $model->subMsg = $map['sub_msg'];
        }
        if(isset($map['user_id'])){
            $model->userId = $map['user_id'];
        }
        if(isset($map['access_token'])){
            $model->accessToken = $map['access_token'];
        }
        if(isset($map['expires_in'])){
            $model->expiresIn = $map['expires_in'];
        }
        if(isset($map['refresh_token'])){
            $model->refreshToken = $map['refresh_token'];
        }
        if(isset($map['re_expires_in'])){
            $model->reExpiresIn = $map['re_expires_in'];
        }
        return $model;
    }
    /**
     * @description 响应原始字符串
     * @var string
     */
    public $httpBody;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $msg;

    /**
     * @var string
     */
    public $subCode;

    /**
     * @var string
     */
    public $subMsg;

    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var int
     */
    public $expiresIn;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * @var int
     */
    public $reExpiresIn;

}
