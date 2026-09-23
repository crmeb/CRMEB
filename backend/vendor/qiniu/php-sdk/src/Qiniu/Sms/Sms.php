<?php

namespace Qiniu\Sms;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Http\Error;
use Qiniu\Http\Client;
use Qiniu\Http\Proxy;

class Sms
{
    private $auth;
    private $baseURL;
    private $proxy;

    public function __construct(Auth $auth, $proxy = null, $proxy_auth = null, $proxy_user_password = null)
    {
        $this->auth = $auth;
        $this->baseURL = sprintf("%s/%s/", Config::SMS_HOST, Config::SMS_VERSION);
        $this->proxy = new Proxy($proxy, $proxy_auth, $proxy_user_password);
    }

    /**
     * 创建签名
     *
     * @param string $signature 签名
     * @param string $source 签名来源，申请签名时必须指定签名来源
     * @param string $pics 签名对应的资质证明图片进行 base64 编码格式转换后的字符串，可选
     * @return array
     *
     * @link https://developer.qiniu.com/sms/api/5844/sms-api-create-signature
     */
    public function createSignature($signature, $source, $pics = null)
    {
        $params = array();
        $params['signature'] = $signature;
        $params['source'] = $source;
        if (!empty($pics)) {
            $params['pics'] = array($this->imgToBase64($pics));
        }
        $body = json_encode($params);
        $url = $this->baseURL . 'signature';
        return $this->post($url, $body);
    }

    /**
     * 编辑签名
     *
     * @param string $id 签名 ID
     * @param string $signature 签名
     * @param string $source 签名来源
     * @param string $pics 签名对应的资质证明图片进行 base64 编码格式转换后的字符串，可选
     * @return array
     * @link https://developer.qiniu.com/sms/api/5890/sms-api-edit-signature
     */
    public function updateSignature($id, $signature, $source, $pics = null)
    {
        $params = array();
        $params['signature'] = $signature;
        $params['source'] = $source;
        if (!empty($pics)) {
            $params['pics'] = array($this->imgToBase64($pics));
        }
        $body = json_encode($params);
        $url = $this->baseURL . 'signature/' . $id;
        return $this->PUT($url, $body);
    }

    /**
     * 列出签名
     *
     * @param string $audit_status 审核状态："passed"(通过), "rejected"(未通过), "reviewing"(审核中)
     * @param int $page 页码。默认为 1
     * @param int $page_size 分页大小。默认为 20
     * @return array
     * @link https://developer.qiniu.com/sms/api/5889/sms-api-query-signature
     */
    public function querySignature($audit_status = null, $page = 1, $page_size = 20)
    {

        $url = sprintf(
            "%s?audit_status=%s&page=%s&page_size=%s",
            $this->baseURL . 'signature',
            $audit_status,
            $page,
            $page_size
        );
        return $this->get($url);
    }

    /**
     * 查询单个签名
     *
     * @param string $signature_id
     * @return array
     * @link https://developer.qiniu.com/sms/api/5970/query-a-single-signature
     */
    public function checkSingleSignature($signature_id)
    {

        $url = sprintf(
            "%s/%s",
            $this->baseURL . 'signature',
            $signature_id
        );
        return $this->get($url);
    }

    /**
     * 删除签名
     *
     * @param string $signature_id 签名 ID
     * @return array
     * @link https://developer.qiniu.com/sms/api/5891/sms-api-delete-signature
     */
    public function deleteSignature($signature_id)
    {
        $url = $this->baseURL . 'signature/' . $signature_id;
        return $this->delete($url);
    }

    /**
     * 创建模板
     *
     * @param string $name 模板名称
     * @param string $template 模板内容 可设置自定义变量，发送短信时候使用，参考:${code}
     * @param string $type notification：通知类,verification：验证码,marketing：营销类,voice：语音类
     * @param string $description 申请理由简述
     * @param string $signature_id 已经审核通过的签名
     * @return array array
     * @link https://developer.qiniu.com/sms/api/5893/sms-api-create-template
     */
    public function createTemplate(
        $name,
        $template,
        $type,
        $description,
        $signature_id
    ) {
        $params = array();
        $params['name'] = $name;
        $params['template'] = $template;
        $params['type'] = $type;
        $params['description'] = $description;
        $params['signature_id'] = $signature_id;

        $body = json_encode($params);
        $url = $this->baseURL . 'template';
        return $this->post($url, $body);
    }

    /**
     * 列出模板
     *
     * @param string $audit_status 审核状态：passed (通过), rejected (未通过), reviewing (审核中)
     * @param int $page 页码。默认为 1
     * @param int $page_size 分页大小。默认为 20
     * @return array
     * @link https://developer.qiniu.com/sms/api/5894/sms-api-query-template
     */
    public function queryTemplate($audit_status = null, $page = 1, $page_size = 20)
    {

        $url = sprintf(
            "%s?audit_status=%s&page=%s&page_size=%s",
            $this->baseURL . 'template',
            $audit_status,
            $page,
            $page_size
        );
        return $this->get($url);
    }

    /**
     * 查询单个模版
     *
     * @param string $template_id 模版ID
     * @return array
     * @link https://developer.qiniu.com/sms/api/5969/query-a-single-template
     */
    public function querySingleTemplate($template_id)
    {

        $url = sprintf(
            "%s/%s",
            $this->baseURL . 'template',
            $template_id
        );
        return $this->get($url);
    }

    /**
     * 编辑模板
     *
     * @param string $id 模板 ID
     * @param string $name 模板名称
     * @param string $template 模板内容
     * @param string $description 申请理由简述
     * @param string $signature_id 已经审核通过的签名 ID
     * @return array
     * @link https://developer.qiniu.com/sms/api/5895/sms-api-edit-template
     */
    public function updateTemplate(
        $id,
        $name,
        $template,
        $description,
        $signature_id
    ) {
        $params = array();
        $params['name'] = $name;
        $params['template'] = $template;
        $params['description'] = $description;
        $params['signature_id'] = $signature_id;
        $body = json_encode($params);
        $url = $this->baseURL . 'template/' . $id;
        return $this->PUT($url, $body);
    }

    /**
     * 删除模板
     *
     * @param string $template_id 模板 ID
     * @return array
     * @link https://developer.qiniu.com/sms/api/5896/sms-api-delete-template
     */
    public function deleteTemplate($template_id)
    {
        $url = $this->baseURL . 'template/' . $template_id;
        return $this->delete($url);
    }

    /**
     * 发送短信
     *
     * @param string $template_id 模板 ID
     * @param array $mobiles 手机号
     * @param array $parameters 自定义模板变量，变量设置在创建模板时，参数template指定
     * @return array
     * @link https://developer.qiniu.com/sms/api/5897/sms-api-send-message
     */
    public function sendMessage($template_id, $mobiles, $parameters = null)
    {
        $params = array();
        $params['template_id'] = $template_id;
        $params['mobiles'] = $mobiles;
        if (!empty($parameters)) {
            $params['parameters'] = $parameters;
        }
        $body = json_encode($params);
        $url = $this->baseURL . 'message';
        return $this->post($url, $body);
    }

    /**
     * 查询发送记录
     *
     * @param string $job_id 发送任务返回的 id
     * @param string $message_id 单条短信发送接口返回的 id
     * @param string $mobile 接收短信的手机号码
     * @param string $status sending: 发送中，success: 发送成功，failed: 发送失败，waiting: 等待发送
     * @param string $template_id 模版 id
     * @param string $type marketing:营销，notification:通知，verification:验证码，voice:语音
     * @param string $start 开始时间，timestamp，例如: 1563280448
     * @param int $end 结束时间，timestamp，例如: 1563280471
     * @param int $page 页码，默认为 1
     * @param int $page_size 每页返回的数据条数，默认20，最大200
     * @return array
     * @link https://developer.qiniu.com/sms/api/5852/query-send-sms
     */
    public function querySendSms(
        $job_id = null,
        $message_id = null,
        $mobile = null,
        $status = null,
        $template_id = null,
        $type = null,
        $start = null,
        $end = null,
        $page = 1,
        $page_size = 20
    ) {
        $query = array();
        \Qiniu\setWithoutEmpty($query, 'job_id', $job_id);
        \Qiniu\setWithoutEmpty($query, 'message_id', $message_id);
        \Qiniu\setWithoutEmpty($query, 'mobile', $mobile);
        \Qiniu\setWithoutEmpty($query, 'status', $status);
        \Qiniu\setWithoutEmpty($query, 'template_id', $template_id);
        \Qiniu\setWithoutEmpty($query, 'type', $type);
        \Qiniu\setWithoutEmpty($query, 'start', $start);
        \Qiniu\setWithoutEmpty($query, 'end', $end);
        \Qiniu\setWithoutEmpty($query, 'page', $page);
        \Qiniu\setWithoutEmpty($query, 'page_size', $page_size);

        $url = $this->baseURL . 'messages?' . http_build_query($query);
        return $this->get($url);
    }


    public function imgToBase64($img_file)
    {
        $img_base64 = '';
        if (file_exists($img_file)) {
            $app_img_file = $img_file; // 图片路径
            $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等
            $fp = fopen($app_img_file, "r"); // 图片是否可读权限
            if ($fp) {
                $filesize = filesize($app_img_file);
                if ($filesize > 5 * 1024 * 1024) {
                    die("pic size < 5M !");
                }
                $img_type = null;
                $content = fread($fp, $filesize);
                $file_content = chunk_split(base64_encode($content)); // base64编码
                switch ($img_info[2]) {           //判读图片类型
                    case 1:
                        $img_type = 'gif';
                        break;
                    case 2:
                        $img_type = 'jpg';
                        break;
                    case 3:
                        $img_type = 'png';
                        break;
                }
                //合成图片的base64编码
                $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;
            }
            fclose($fp);
        }

        return $img_base64;
    }

    private function get($url, $contentType = 'application/x-www-form-urlencoded')
    {
        $headers = $this->auth->authorizationV2($url, "GET", null, $contentType);
        $headers['Content-Type'] = $contentType;
        $ret = Client::get($url, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function delete($url, $contentType = 'application/json')
    {
        $headers = $this->auth->authorizationV2($url, "DELETE", null, $contentType);
        $headers['Content-Type'] = $contentType;
        $ret = Client::delete($url, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function post($url, $body, $contentType = 'application/json')
    {
        $headers = $this->auth->authorizationV2($url, "POST", $body, $contentType);

        $headers['Content-Type'] = $contentType;
        $ret = Client::post($url, $body, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }

    private function PUT($url, $body, $contentType = 'application/json')
    {
        $headers = $this->auth->authorizationV2($url, "PUT", $body, $contentType);
        $headers['Content-Type'] = $contentType;
        $ret = Client::put($url, $body, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }
}
