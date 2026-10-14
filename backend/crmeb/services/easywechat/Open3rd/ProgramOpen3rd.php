<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\services\easywechat\Open3rd;

use EasyWeChat\MiniApp\Application as MiniAppApplication;
use EasyWeChat\OpenPlatform\Application as OpenPlatformApplication;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 第三方平台(开放平台代开发)
 *
 * 基于 EasyWeChat 6.x OpenPlatform 重建:
 * - 组件级接口(cgi-bin/component/*)走开放平台客户端(component_access_token 自动携带);
 * - 代小程序接口(wxa/*)走 getMiniAppWithRefreshToken 获取的授权小程序客户端
 *   (authorizer_access_token 自动管理,修复旧实现 ticket/token 错位问题);
 * - component_verify_ticket 由开放平台 Server 推送后经 6.x VerifyTicket 机制缓存。
 *
 * 命名空间统一为 Open3rd(原文件存在 open3rd/Open3rd 大小写混用,Linux 下自动加载脆弱)。
 *
 * Class ProgramOpen3rd
 * @package crmeb\services\easywechat\Open3rd
 */
class ProgramOpen3rd
{
    /**
     * 授权方小程序 appid(代开发对象)
     * @var string
     */
    protected string $authorizerAppId;

    /**
     * 授权方刷新令牌
     * @var string
     */
    protected string $authorizerRefreshToken;

    public function __construct(
        protected OpenPlatformApplication $platform,
        string $authorizerAppId = '',
        string $authorizerRefreshToken = ''
    ) {
        $this->authorizerAppId = $authorizerAppId;
        $this->authorizerRefreshToken = $authorizerRefreshToken;
    }

    // ================= 组件级接口(component_access_token) =================

    /**
     * 获取预授权码
     * @return array
     */
    public function getPreAuthCode(): array
    {
        return $this->platformPost('cgi-bin/component/api_create_preauthcode', [
            'component_appid' => $this->platform->getAccount()->getAppId(),
        ]);
    }

    /**
     * 使用授权码换取授权信息(authorizer_appid / authorizer_access_token / authorizer_refresh_token)
     * @param string $authorization_code
     * @return array
     */
    public function getAuth(string $authorization_code): array
    {
        $authorization = $this->platform->getAuthorization($authorization_code);

        return [
            'authorization_info' => [
                'authorizer_appid' => $authorization->getAppId(),
                'authorizer_access_token' => (string) $authorization->getAccessToken(),
                'authorizer_refresh_token' => $authorization->getRefreshToken(),
            ],
        ];
    }

    /**
     * 获取授权方帐号基本信息
     * @param string $authorizer_appid
     * @return array
     */
    public function getAuthorizerinfo(string $authorizer_appid): array
    {
        return $this->platformPost('cgi-bin/component/api_get_authorizer_info', [
            'component_appid' => $this->platform->getAccount()->getAppId(),
            'authorizer_appid' => $authorizer_appid,
        ]);
    }

    // ================= 代小程序接口(authorizer_access_token) =================

    /**
     * 获取体验者列表
     * @return array
     */
    public function getMemberAuthList(): array
    {
        return $this->miniPost('wxa/memberauth', ['action' => 'get_experiencer']);
    }

    /**
     * 绑定体验者
     * @param string $wechatid
     * @return array
     */
    public function bindMemberAuth(string $wechatid): array
    {
        return $this->miniPost('wxa/bind_tester', ['wechatid' => $wechatid]);
    }

    /**
     * 解除绑定体验者
     * @param string $wechatid
     * @param string $userstr
     * @return array
     */
    public function unBindMemberAuth(string $wechatid, string $userstr = ''): array
    {
        return $this->miniPost('wxa/unbind_tester', ['wechatid' => $wechatid, 'userstr' => $userstr]);
    }

    /**
     * 获取代码草稿列表
     * @return array
     */
    public function getDraftList(): array
    {
        return $this->miniGet('wxa/gettemplatedraftlist');
    }

    /**
     * 将草稿添加到代码模板库
     * @param int $draft_id
     * @return array
     */
    public function addToTemplate($draft_id): array
    {
        return $this->miniPost('wxa/addtotemplate', ['draft_id' => $draft_id]);
    }

    /**
     * 获取代码模板列表
     * @return array
     */
    public function getTemplateList(): array
    {
        return $this->miniGet('wxa/gettemplatelist');
    }

    /**
     * 删除指定代码模板
     * @param int $template_id
     * @return array
     */
    public function delTemplate($template_id): array
    {
        return $this->miniPost('wxa/deletetemplate', ['template_id' => $template_id]);
    }

    /**
     * 上传代码(为授权小程序上传代码)
     * @param int $template_id
     * @param string $ext_json 第三方自定义的配置
     * @param string $user_version 代码版本号
     * @param string $user_desc 代码描述
     * @return array
     */
    public function commit($template_id, string $ext_json, string $user_version, string $user_desc = ''): array
    {
        return $this->miniPost('wxa/commit', [
            'template_id' => $template_id,
            'ext_json' => $ext_json,
            'user_version' => $user_version,
            'user_desc' => $user_desc,
        ]);
    }

    /**
     * 获取已上传的代码页面列表
     * @return array
     */
    public function getPage(): array
    {
        return $this->miniGet('wxa/get_page');
    }

    /**
     * 获取体验二维码图片内容
     * @param string $path
     * @return string 二进制内容
     */
    public function getQrcode($path = ''): string
    {
        return (string) $this->miniAppClient()->get('wxa/get_qrcode', array_filter(['path' => $path]))->getContent(false);
    }

    /**
     * 代码提交审核
     * @param array $data
     * @return array
     */
    public function submitAudit($data = []): array
    {
        return $this->miniPost('wxa/submit_audit', $data);
    }

    /**
     * 查询指定版本审核状态
     * @param string $auditid
     * @return array
     */
    public function getAuditStatus(string $auditid): array
    {
        return $this->miniPost('wxa/get_auditstatus', ['auditid' => $auditid]);
    }

    /**
     * 查询最新版本审核状态
     * @return array
     */
    public function getLatestAuditStatus(): array
    {
        return $this->miniGet('wxa/get_latest_auditstatus');
    }

    /**
     * 撤回审核
     * @return array
     */
    public function undoAudit(): array
    {
        return $this->miniGet('wxa/undocodeaudit');
    }

    /**
     * 发布已通过审核的代码
     * @return array
     */
    public function release(): array
    {
        return $this->miniPost('wxa/release', []);
    }

    /**
     * 分阶段发布
     * @param int $gray_percentage
     * @return array
     */
    public function grayRelease(int $gray_percentage): array
    {
        return $this->miniPost('wxa/grayrelease', ['gray_percentage' => $gray_percentage]);
    }

    /**
     * 版本回退
     * @return array
     */
    public function revertCodeRelease(): array
    {
        return $this->miniGet('wxa/revertcoderelease');
    }

    // ================= 内部工具 =================

    /**
     * 授权小程序客户端(代调用,authorizer_access_token 自动携带)
     */
    protected function miniAppClient(): HttpClientInterface
    {
        return $this->platform
            ->getMiniAppWithRefreshToken($this->authorizerAppId, $this->authorizerRefreshToken)
            ->getClient();
    }

    protected function platformPost(string $uri, array $data): array
    {
        $res = $this->platform->getClient()->postJson($uri, $data)->toArray(false);
        return is_array($res) ? $res : [];
    }

    protected function miniPost(string $uri, array $data): array
    {
        $res = $this->miniAppClient()->postJson($uri, $data)->toArray(false);
        return is_array($res) ? $res : [];
    }

    protected function miniGet(string $uri, array $query = []): array
    {
        $res = $this->miniAppClient()->get($uri, $query)->toArray(false);
        return is_array($res) ? $res : [];
    }
}
