<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\services\easywechat\open3rd;


/**
 * Class ProgramWechatLive
 * @package crmeb\services\wechatlive
 */
class ProgramOpen3rd
{
    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * 预授权码
     */
    const PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';

    /**
     * 获取授权方的帐号基本信息
     */
    const GET_AUTHORIZER_INFO = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';
    /**
     * 获取体验真列表
     */
    const MEMBER_AUTH_LIST = 'https://api.weixin.qq.com/wxa/memberauth';
    /**
     * 绑定体验者
     */
    const BIND_MEMBER_AUTH = 'https://api.weixin.qq.com/wxa/bind_tester';
    /**
     * 解除绑定体验者
     */
    const UNBIND_MEMBER_AUTH = 'https://api.weixin.qq.com/wxa/unbind_tester';
    /**
     * 获取代码草稿列表
     */
    const DRAFT_LIST = 'https://api.weixin.qq.com/wxa/gettemplatedraftlist';
    /**
     * 将草稿添加到代码模板库
     */
    const ADD_TO_TEMPLATE = 'https://api.weixin.qq.com/wxa/addtotemplate';
    /**
     * 获取代码模版列表
     */
    const TEMPLATE_LIST = 'https://api.weixin.qq.com/wxa/gettemplatelist';
    /**
     * 删除指定代码模版
     */
    const DEL_TEMPLATE = 'https://api.weixin.qq.com/wxa/deletetemplate';
    /**
     * 上传代码
     */
    const COMMIT = 'https://api.weixin.qq.com/wxa/commit';
    /**
     * 获取已上传的代码页面列表
     */
    const GET_PAGE = 'https://api.weixin.qq.com/wxa/get_page';
    /**
     * 获取体验二维码
     */
    const GET_QRCODE = 'https://api.weixin.qq.com/wxa/get_qrcode';
    /**
     * 代码提交审核
     */
    const SUBMIT_AUDIT = 'https://api.weixin.qq.com/wxa/submit_audit';
    /**
     * 查询指定版本审核状态
     */
    const GET_AUDIT_STATUS = 'https://api.weixin.qq.com/wxa/get_auditstatus';
    /**
     * 查询最近一次提交审核状态
     */
    const GET_LATEST_AUDIT_STATUS = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus';
    /**
     * 撤回审核
     */
    const UNDO_CODE_AUDIT = 'https://api.weixin.qq.com/wxa/undocodeaudit';
    /**
     * 发布已经通过审核的小程序
     */
    const RELEASE = 'https://api.weixin.qq.com/wxa/release';
    /**
     * 分阶段发布
     */
    const GRAY_RELEASE = 'https://api.weixin.qq.com/wxa/grayrelease';
    /**
     * 版本回退
     */
    const REVERT_CODE_RELEASE = 'https://api.weixin.qq.com/wxa/revertcoderelease';

    /**
     * ProgramOpen3rd constructor.
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->config = $accessToken->getConfig();
    }


    /**
     * 获取预授权码
     * @return array|bool|mixed
     */
    public function getPreAuthCode()
    {
        return $this->accessToken->httpRequest(self::PRE_AUTH_CODE, [], false);
    }

    /**
     * 获取授权
     * @param $authorization_code
     * @return authorizer_appid
     */
    public function getAuth($authorization_code)
    {
        return $this->accessToken->getAuthorizationInfo($authorization_code);
    }

    /**
     * 获取授权方帐号基本信息
     * @param $authorizer_appid
     * @return array|bool|mixed
     */
    public function getAuthorizerinfo(string $authorizer_appid)
    {
        return $this->accessToken->httpRequest(self::GET_AUTHORIZER_INFO, ['authorizer_appid' => $authorizer_appid], false);
    }

    /**
     * 获取授权体验者列表
     * @return array|bool|mixed
     */
    public function getMemberAuthList()
    {
        return $this->accessToken->httpRequest(self::MEMBER_AUTH_LIST, ['action' => 'get_experiencer']);
    }

    /**
     * 绑定体验者
     * @param string $wechatid
     * @return array|bool|mixed
     */
    public function bindMemberAuth(string $wechatid)
    {
        return $this->accessToken->httpRequest(self::BIND_MEMBER_AUTH, ['wechatid' => $wechatid]);
    }

    /**
     * 解除绑定体验者
     * @param string $wechatid
     * @param string $userstr
     * @return array|bool|mixed
     */
    public function unBindMemberAuth(string $wechatid, string $userstr = '')
    {
        $data = ['wechatid' => $wechatid];
        if ($userstr) $data['userstr'] = $userstr;
        return $this->accessToken->httpRequest(self::UNBIND_MEMBER_AUTH, $data);
    }

    /**
     * 获取草稿列表
     * @return array|bool|mixed
     */
    public function getDraftList()
    {
        return $this->accessToken->httpRequest(self::DRAFT_LIST);
    }

    /**
     * 将草稿添加到代码模版
     * @param $draft_id
     * @return array|bool|mixed
     */
    public function addToTemplate($draft_id)
    {
        return $this->accessToken->httpRequest(self::ADD_TO_TEMPLATE, ['draft_id' => $draft_id]);
    }

    /**
     * 获取代码模版列表
     * @return array|bool|mixed
     */
    public function getTemplateList()
    {
        return $this->accessToken->httpRequest(self::TEMPLATE_LIST);
    }

    /**
     * 删除指定模版
     * @param $template_id
     * @return array|bool|mixed
     */
    public function delTemplate($template_id)
    {
        return $this->accessToken->httpRequest(self::DEL_TEMPLATE, ['template_id' => $template_id]);
    }

    /**
     * 代码上传
     * @param $template_id
     * @param string $ext_json
     * @param string $user_version
     * @param string $user_desc
     * @return array|bool|mixed
     */
    public function commit($template_id, string $ext_json, string $user_version, string $user_desc = '')
    {
        return $this->accessToken->httpRequest(self::COMMIT, ['template_id' => $template_id, 'ext_json' => $ext_json, 'user_version' => $user_version, 'user_desc' => $user_desc]);
    }

    /**
     * 获取已上传代码列表
     * @return array|bool|mixed
     */
    public function getPage()
    {
        return $this->accessToken->httpRequest(self::GET_PAGE, []);
    }

    /**
     * 获取体验二维码
     * @return array|bool|mixed
     */
    public function getQrcode($path = '')
    {
        return $this->accessToken->httpRequest(self::GET_QRCODE, ['path' => $path], true, 'GET');
    }

    /**
     * 提交审核
     * @param array $data
     * @param data = [
     * 'item_list' => [],//审核项列表（选填，至多填写 5 项）
     * 'preview_info' => (object)[],//预览信息（小程序页面截图和操作录屏）
     * 'version_desc' => '',//小程序版本说明和功能解释
     * 'feedback_info' => '',//反馈内容，至多 200 字
     * 'feedback_stuff' => '',//用 | 分割的 media_id 列表，至多 5 张图片, 可以通过新增临时素材接口上传而得到
     * 'ugc_declare' => (object)[],//用户生成内容场景（UGC）信息安全声明
     * ];
     * @return array|bool|mixed
     */
    public function submitAudit($data = [])
    {
        $base = [
            'item_list' => [],
            'preview_info' => (object)[],
            'version_desc' => '',
            'feedback_info' => '',
            'feedback_stuff' => '',
            'ugc_declare' => (object)[],
        ];
        $data = array_merge($base, $data);
        return $this->accessToken->httpRequest(self::SUBMIT_AUDIT, $data);
    }

    /**
     * 查询指定版本的审核状态
     * @param string $auditid
     * @return array|bool|mixed
     */
    public function getAuditStatus(string $auditid)
    {
        return $this->accessToken->httpRequest(self::GET_AUDIT_STATUS, ['auditid' => $auditid]);
    }

    /**
     * 获取最后一次提交审核状态
     * @return array|bool|mixed
     */
    public function getLatestAuditStatus()
    {
        return $this->accessToken->httpRequest(self::GET_LATEST_AUDIT_STATUS, [], true, 'GET');
    }

    /**
     * 审核撤回
     * @return array|bool|mixed
     */
    public function undoAudit()
    {
        return $this->accessToken->httpRequest(self::UNDO_CODE_AUDIT, [], true, 'GET');
    }

    /**
     * 发布已通过审核小程序
     * @return array|bool|mixed
     */
    public function release()
    {
        return $this->accessToken->httpRequest(self::RELEASE);
    }

    /**
     * 分阶段发布
     * @param int $gray_percentage 1-100整数
     * @return mixed
     */
    public function grayRelease(int $gray_percentage)
    {
        return $this->accessToken->httpRequest(self::GRAY_RELEASE, ['gray_percentage' => $gray_percentage]);
    }

    /**
     * 版本回退
     * @return array|bool|mixed
     */
    public function revertCodeRelease()
    {
        return $this->accessToken->httpRequest(self::REVERT_CODE_RELEASE, [], true, 'GET');
    }
}