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
namespace modules\channel\wechat_mini\controller\admin;

use app\adminapi\controller\AuthController;
use app\services\system\NodeEnvironmentServices;
use modules\channel\wechat_mini\service\RoutineCIServices;
use think\facade\App;

/**
 * 小程序 CI 自动化上传控制器
 *
 * 功能概述:
 * 本控制器提供微信小程序代码自动化上传的 API 接口，基于微信官方的 miniprogram-ci 工具实现。
 * 通过这些接口，管理员可以在后台直接将小程序代码上传到微信服务器，无需使用微信开发者工具。
 *
 * 主要功能:
 * 1. 环境检测 - 检测服务器是否已安装 Node.js 和 miniprogram-ci
 * 2. 安装指南 - 提供不同操作系统的环境安装说明
 * 3. 配置管理 - 管理小程序上传密钥和 AppId 配置
 * 4. 代码上传 - 将小程序代码上传到微信开发版
 * 5. 预览功能 - 生成小程序预览二维码进行测试
 *
 * 使用前提:
 * - 服务器已安装 Node.js (>=14.0.0) 和 npm
 * - 已全局安装 miniprogram-ci (npm install miniprogram-ci -g)
 * - 已在微信公众平台获取小程序代码上传密钥
 * - 服务器 PHP 的 exec() 函数未被禁用
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/devtools/ci.html 微信官方 CI 文档
 * @package app\adminapi\controller\application\routine
 */
class RoutineCIController extends AuthController
{
    /**
     * Node.js 环境检测服务实例
     *
     * 用于检测服务器环境是否满足小程序上传的要求:
     * - Node.js 版本检测
     * - npm 可用性检测
     * - miniprogram-ci 安装状态检测
     * - 操作系统类型识别
     *
     * @var NodeEnvironmentServices
     */
    protected $envServices;

    /**
     * 小程序 CI 核心服务实例
     *
     * 处理小程序代码上传的核心业务逻辑:
     * - 上传密钥管理
     * - 项目文件准备
     * - 执行 miniprogram-ci 命令
     * - 生成预览二维码
     *
     * @var RoutineCIServices
     */
    protected $ciServices;

    /**
     * 构造方法 - 初始化服务依赖
     *
     * 通过依赖注入方式注入所需的服务类实例，
     * ThinkPHP 的容器会自动解析并注入这些依赖。
     *
     * @param App $app ThinkPHP 应用实例
     * @param NodeEnvironmentServices $envServices 环境检测服务
     * @param RoutineCIServices $ciServices CI 上传服务
     */
    public function __construct(App $app, NodeEnvironmentServices $envServices, RoutineCIServices $ciServices)
    {
        parent::__construct($app);
        $this->envServices = $envServices;
        $this->ciServices = $ciServices;
    }

    /**
     * 获取服务器运行环境状态
     *
     * 检测并返回小程序上传所需的所有环境信息，前端根据返回结果
     * 展示环境就绪状态或引导用户完成环境配置。
     *
     * 返回数据结构:
     * - os: 操作系统信息 (family, type, version)
     * - node: Node.js 状态 (installed, version, path, meets_requirement)
     * - npm: npm 状态 (installed, version)
     * - miniprogram_ci: CI工具状态 (installed, version)
     * - ready: 布尔值，环境是否完全就绪
     * - can_install: 是否支持自动安装
     * - exec_enabled: exec函数是否可用
     * - message: 提示信息
     *
     * @return mixed JSON 响应，包含完整的环境状态信息
     */
    public function environment()
    {
        $data = $this->envServices->getEnvironmentStatus();
        return app('json')->success($data);
    }

    /**
     * 获取环境安装指南
     *
     * 根据服务器操作系统类型返回对应的 Node.js 和 miniprogram-ci 安装步骤。
     * 支持的操作系统: CentOS/RHEL、Ubuntu/Debian、macOS、Windows
     *
     * 返回数据结构:
     * - title: 指南标题 (如 "CentOS/RHEL 安装指南")
     * - steps: 安装步骤数组，包含命令行指令
     * - script_url: 一键安装脚本的 URL 地址
     *
     * @return mixed JSON 响应，包含适合当前系统的安装指南
     */
    public function installGuide()
    {
        $guide = $this->envServices->getInstallGuide();
        return app('json')->success($guide);
    }

    /**
     * 获取小程序上传配置状态
     *
     * 返回当前的上传配置信息，用于前端展示配置状态和引导配置流程。
     *
     * 返回数据结构:
     * - app_id: 小程序 AppId
     * - app_id_configured: AppId 是否已配置
     * - private_key_exists: 上传密钥文件是否存在
     * - private_key_path: 密钥文件存储路径
     * - project_path: 小程序项目路径
     * - project_exists: 项目目录是否存在
     *
     * @return mixed JSON 响应，包含上传配置状态信息
     */
    public function uploadConfig()
    {
        $config = $this->ciServices->getUploadConfig();
        return app('json')->success($config);
    }

    /**
     * 保存小程序代码上传密钥
     *
     * 接收并保存从微信公众平台下载的小程序代码上传密钥。
     * 密钥用于 miniprogram-ci 工具的身份验证，确保只有授权用户才能上传代码。
     *
     * 请求参数:
     * - key_content: string, 必填，RSA 私钥内容 (以 -----BEGIN RSA PRIVATE KEY----- 开头)
     *
     * 密钥获取方式:
     * 微信公众平台 -> 开发管理 -> 开发设置 -> 小程序代码上传 -> 下载密钥
     *
     * 安全说明:
     * - 密钥文件保存在 config/routine_private.key
     * - 文件权限设置为 0600，仅所有者可读写
     * - 请勿将密钥文件提交到版本控制系统
     *
     * @return mixed JSON 响应，成功返回提示信息，失败返回错误原因
     */
    public function savePrivateKey()
    {
        // 获取 POST 请求中的密钥内容
        $keyContent = $this->request->post('key_content', '');

        // 验证密钥内容不能为空
        if (empty($keyContent)) {
            return app('json')->fail('请提供密钥内容');
        }

        // 调用服务层保存密钥（服务层会验证密钥格式）
        $this->ciServices->savePrivateKey($keyContent);
        return app('json')->success('密钥保存成功');
    }

    /**
     * 上传小程序代码到微信开发版
     *
     * 将本地小程序项目代码上传到微信服务器的开发版本。
     * 上传成功后，可在微信公众平台的版本管理中看到新上传的开发版本。
     *
     * 请求参数:
     * - version: string, 必填，版本号，格式为 x.x.x (如 1.0.0)
     * - desc: string, 可选，版本描述，默认为 "版本 {version}"
     * - is_live: int, 可选，是否开启直播功能，0=关闭 1=开启，默认关闭
     *
     * 执行流程:
     * 1. 验证版本号格式
     * 2. 检查运行环境 (Node.js、密钥文件等)
     * 3. 准备项目文件 (复制、替换配置)
     * 4. 执行 miniprogram-ci upload 命令
     * 5. 返回上传结果
     *
     * 返回数据结构:
     * - success: 是否成功
     * - version: 版本号
     * - desc: 版本描述
     * - message: 提示信息
     * - output: 命令执行输出
     *
     * @return mixed JSON 响应，包含上传结果信息
     */
    public function upload()
    {
        // 批量获取请求参数
        [$version, $desc, $isLive] = $this->request->postMore([
            ['version', ''],     // 版本号
            ['desc', ''],        // 版本描述
            ['is_live', 0],      // 是否开启直播
        ], true);

        // 验证版本号必填
        if (empty($version)) {
            return app('json')->fail('请输入版本号');
        }

        // 验证版本号格式：必须是 x.x.x 格式 (如 1.0.0, 2.1.3)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return app('json')->fail('版本号格式错误，请使用 x.x.x 格式');
        }

        // 调用服务层执行上传
        $result = $this->ciServices->upload($version, $desc, (bool)$isLive);
        return app('json')->success($result);
    }

    /**
     * 获取小程序预览二维码
     *
     * 生成小程序预览二维码，扫码后可在手机上预览小程序效果。
     * 预览版本不会影响线上版本，适合开发测试使用。
     *
     * 请求参数:
     * - page_path: string, 可选，预览的页面路径 (如 pages/index/index)
     *              为空时默认预览首页
     *
     * 执行流程:
     * 1. 检查运行环境
     * 2. 准备项目文件
     * 3. 执行 miniprogram-ci preview 命令
     * 4. 生成二维码图片
     * 5. 返回二维码图片 URL
     *
     * 返回数据结构:
     * - success: 是否成功
     * - qrcode_url: 二维码图片的访问 URL
     * - message: 提示信息
     * - output: 命令执行输出
     *
     * 注意事项:
     * - 预览二维码有效期较短，过期需重新生成
     * - 只有小程序的开发者和体验者才能扫码预览
     *
     * @return mixed JSON 响应，包含预览二维码信息
     */
    public function preview()
    {
        // 获取预览页面路径参数
        $pagePath = $this->request->post('page_path', '');

        // 调用服务层生成预览二维码
        $result = $this->ciServices->preview($pagePath);
        return app('json')->success($result);
    }
}
