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
namespace modules\channel\wechat_mini\service;

use crmeb\basic\BaseService;
use app\model\system\config\SystemConfigTab;
use crmeb\exceptions\AdminException;
use crmeb\utils\File;
use think\facade\Log;

/**
 * 小程序 CI (Continuous Integration) 核心服务类
 *
 * 功能概述:
 * 本服务类封装了微信官方 miniprogram-ci 工具的调用逻辑，
 * 实现小程序代码的自动化上传和预览功能。
 *
 * 主要功能:
 * 1. 上传密钥管理 - 保存/删除小程序代码上传密钥
 * 2. 项目准备 - 复制源码并替换 AppId、URL 等配置
 * 3. 代码上传 - 调用 miniprogram-ci upload 命令上传代码
 * 4. 预览二维码 - 调用 miniprogram-ci preview 命令生成预览码
 *
 * 依赖工具:
 * - Node.js >= 14.0.0
 * - npm (Node.js 包管理器)
 * - miniprogram-ci (全局安装: npm install miniprogram-ci -g)
 *
 * 密钥获取:
 * 微信公众平台 -> 开发管理 -> 开发设置 -> 小程序代码上传
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/devtools/ci.html
 * @package app\services\wechat
 */
class RoutineCIServices extends BaseService
{
    /**
     * 小程序项目文件存储路径
     *
     * 上传前会将源码复制到此目录，并进行配置替换后再上传。
     * 默认路径: public/statics/download
     *
     * @var string
     */
    protected $projectPath;

    /**
     * 小程序代码上传密钥文件存储路径
     *
     * RSA 私钥文件，用于 miniprogram-ci 的身份验证。
     * 默认路径: config/routine_private.key
     *
     * 安全注意: 此文件包含敏感信息，应设置适当的文件权限，
     * 并在 .gitignore 中排除，避免提交到版本控制系统。
     *
     * @var string
     */
    protected $privateKeyPath;

    /**
     * 小程序 AppId
     *
     * 从系统配置中读取，配置项为 routine_appId。
     * 用于标识目标小程序，上传时必须与密钥对应的小程序一致。
     *
     * @var string
     */
    protected $appId;

    /**
     * 构造函数 - 初始化配置路径
     *
     * 初始化小程序上传所需的各种路径配置:
     * - 项目文件存储路径
     * - 密钥文件存储路径
     * - 从系统配置读取 AppId
     */
    public function __construct()
    {
        // 设置项目文件存储目录 (public/statics/download)
        $this->projectPath = public_path() . 'statics' . DIRECTORY_SEPARATOR . 'download';
        // 设置密钥文件存储路径 (config/routine_private.key)
        $this->privateKeyPath = app()->getRootPath() . 'config' . DIRECTORY_SEPARATOR . 'routine_private.key';
        // 从系统配置读取小程序 AppId
        $this->appId = sys_config('routine_appId', '');
    }

    /**
     * 获取上传配置状态信息
     *
     * 返回当前小程序上传相关的所有配置状态，
     * 前端根据这些信息展示配置状态并引导用户完成配置。
     *
     * @return array 配置状态信息，包含:
     *               - app_id: 小程序 AppId
     *               - app_id_configured: AppId 是否已配置
     *               - private_key_exists: 密钥文件是否存在
     *               - private_key_path: 密钥文件路径
     *               - project_path: 项目文件路径
     *               - project_exists: 项目目录是否存在
     */
    public function getUploadConfig(): array
    {
        return [
            'app_id' => $this->appId,                           // 小程序 AppId
            'app_id_configured' => !empty($this->appId),        // AppId 是否已配置
            'private_key_exists' => file_exists($this->privateKeyPath), // 密钥文件是否存在
            'private_key_path' => $this->privateKeyPath,        // 密钥文件完整路径
            'project_path' => $this->projectPath,               // 项目文件存储路径
            'project_exists' => is_dir($this->projectPath),     // 项目目录是否存在
            'config_tab_id' => (int) SystemConfigTab::where('eng_title', 'routine')->value('id'), // 小程序配置父分类 id（与菜单 menu_path 一致，动态获取）
        ];
    }

    /**
     * 保存小程序代码上传密钥
     *
     * 将从微信公众平台下载的密钥内容保存到服务器。
     * 密钥用于 miniprogram-ci 工具的身份验证。
     *
     * 处理流程:
     * 1. 验证密钥格式 (必须以 -----BEGIN RSA PRIVATE KEY----- 开头)
     * 2. 确保密钥存储目录存在
     * 3. 将密钥内容写入文件
     * 4. 设置文件权限为 0600 (仅所有者可读写)
     *
     * @param string $keyContent 密钥内容 (RSA 私钥 PEM 格式)
     * @return bool 保存成功返回 true
     * @throws AdminException 密钥格式错误或保存失败时抛出异常
     */
    public function savePrivateKey(string $keyContent): bool
    {
        // 验证密钥格式: 必须是 RSA 私钥 PEM 格式
        if (strpos($keyContent, '-----BEGIN RSA PRIVATE KEY-----') === false) {
            throw new AdminException('无效的密钥格式，请上传正确的小程序代码上传密钥');
        }

        // 确保密钥存储目录存在
        $keyDir = dirname($this->privateKeyPath);
        if (!is_dir($keyDir)) {
            mkdir($keyDir, 0755, true);
        }

        // 将密钥内容写入文件
        $result = file_put_contents($this->privateKeyPath, $keyContent);
        if ($result === false) {
            throw new AdminException('密钥保存失败，请检查目录权限');
        }

        // 设置文件权限为 0600 (仅所有者可读写)，提高安全性
        chmod($this->privateKeyPath, 0600);

        return true;
    }

    /**
     * 删除小程序代码上传密钥
     *
     * 从服务器上删除已保存的密钥文件。
     * 如果密钥文件不存在，则直接返回成功。
     *
     * @return bool 删除成功或文件不存在时返回 true
     */
    public function deletePrivateKey(): bool
    {
        if (file_exists($this->privateKeyPath)) {
            return unlink($this->privateKeyPath);
        }
        return true;
    }

    /**
     * 准备小程序项目文件
     *
     * 上传前的项目准备工作，包括复制源码和替换配置:
     * 1. 清理旧的项目文件 (如果存在)
     * 2. 将小程序源码从 mp-weixin 目录复制到 download 目录
     * 3. 替换 project.config.json 中的 appid 和 projectname
     * 4. 根据是否开启直播决定是否移除直播插件配置
     * 5. 替换代码中的 API 域名为当前服务器域名
     *
     * @param bool $isLive 是否开启直播功能，默认关闭
     *                     关闭时会移除 app.json 中的直播插件配置
     * @return string 准备完成后的项目路径
     * @throws AdminException AppId 未配置或准备过程出错时抛出异常
     */
    public function prepareProject(bool $isLive = false): string
    {
        // 检查 AppId 是否已配置
        if (empty($this->appId)) {
            throw new AdminException('请先配置小程序 AppId');
        }

        try {
            // 步骤1: 清理旧的项目文件
            if (is_dir($this->projectPath)) {
                $this->deleteDirectory($this->projectPath);
            }

            // 步骤2: 复制小程序源码到目标目录
            // 源目录: public/statics/mp-weixin (小程序编译后的源码)
            /** @var File $fileService */
            $fileService = app(File::class);
            $fileService->copyDir(public_path() . 'statics/mp-weixin', $this->projectPath);

            // 步骤3: 替换 project.config.json 中的 appid 和 项目名称
            $this->updateConfigJson($this->appId, sys_config('routine_name', ''));

            // 步骤4: 如果不开启直播，移除 app.json 中的直播插件配置
            if (!$isLive) {
                $this->updateAppJson();
            }

            // 步骤5: 替换代码中的 API 域名为当前服务器域名
            $this->updateUrl('https://' . $_SERVER['HTTP_HOST']);

            return $this->projectPath;
        } catch (\Throwable $e) {
            throw new AdminException('准备项目失败: ' . $e->getMessage());
        }
    }

    /**
     * 上传小程序代码到微信开发版
     *
     * 调用 miniprogram-ci upload 命令将小程序代码上传到微信服务器。
     * 上传成功后，可在微信公众平台的版本管理中查看新版本。
     *
     * 执行流程:
     * 1. 检查运行环境 (Node.js、密钥文件、miniprogram-ci)
     * 2. 准备项目文件
     * 3. 构建并执行 upload 命令
     * 4. 记录命令输出日志
     * 5. 返回上传结果
     *
     * @param string $version 版本号，格式为 x.x.x (如 1.0.0)
     * @param string $desc 版本描述，默认为 "版本 {version}"
     * @param bool $isLive 是否开启直播功能，影响项目准备
     * @return array 上传结果，包含: success, version, desc, message, output
     * @throws AdminException 环境检查失败或上传失败时抛出异常
     */
    public function upload(string $version, string $desc = '', bool $isLive = false): array
    {
        // 检查运行环境是否满足要求
        $this->checkEnvironment();

        // 准备项目文件 (复制、替换配置)
        $projectPath = $this->prepareProject($isLive);

        // 构建 miniprogram-ci upload 命令
        $command = $this->buildUploadCommand($version, $desc);

        // 记录命令日志
        Log::info('miniprogram-ci upload command: ' . $command);

        // 执行命令
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        $outputStr = implode("\n", $output);
        Log::info('miniprogram-ci upload output: ' . $outputStr);

        // 检查执行结果，非零返回码表示失败
        if ($returnCode !== 0) {
            throw new AdminException('上传失败: ' . $outputStr);
        }

        return [
            'success' => true,
            'version' => $version,
            'desc' => $desc,
            'message' => '上传成功',
            'output' => $outputStr,
        ];
    }

    /**
     * 生成小程序预览二维码
     *
     * 调用 miniprogram-ci preview 命令生成预览二维码。
     * 扫描二维码可在手机上预览小程序效果。
     *
     * 执行流程:
     * 1. 检查运行环境
     * 2. 准备项目文件
     * 3. 构建并执行 preview 命令
     * 4. 生成二维码图片并保存
     * 5. 返回二维码图片 URL
     *
     * @param string $pagePath 预览的页面路径 (如 pages/index/index)
     *                         为空时默认预览小程序首页
     * @return array 预览结果，包含: success, qrcode_url, message, output
     * @throws AdminException 环境检查失败或预览失败时抛出异常
     */
    public function preview(string $pagePath = ''): array
    {
        // 检查运行环境是否满足要求
        $this->checkEnvironment();

        // 准备项目文件
        $projectPath = $this->prepareProject();

        // 设置二维码图片保存路径
        $qrcodePath = public_path() . 'statics' . DIRECTORY_SEPARATOR . 'routine_preview.jpg';

        // 构建 miniprogram-ci preview 命令
        $command = $this->buildPreviewCommand($qrcodePath, $pagePath);

        // 记录命令日志
        Log::info('miniprogram-ci preview command: ' . $command);

        // 执行命令
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        $outputStr = implode("\n", $output);
        Log::info('miniprogram-ci preview output: ' . $outputStr);

        // 检查执行结果
        if ($returnCode !== 0) {
            throw new AdminException('预览失败: ' . $outputStr);
        }

        // 拼接二维码图片的访问 URL，添加时间戳防止缓存
        $qrcodeUrl = sys_config('site_url') . '/statics/routine_preview.jpg?t=' . time();

        return [
            'success' => true,
            'qrcode_url' => $qrcodeUrl,
            'message' => '预览二维码生成成功',
            'output' => $outputStr,
        ];
    }

    /**
     * 构建 miniprogram-ci upload 命令
     *
     * 构建用于上传小程序代码的命令行字符串。
     *
     * 命令参数说明:
     * - --pp: 项目路径 (project path)
     * - --pkp: 密钥文件路径 (private key path)
     * - --appid: 小程序 AppId
     * - --uv: 上传版本号 (upload version)
     * - -r: 上传的机器人编号，默认 1
     * - --desc: 版本描述
     *
     * @param string $version 版本号
     * @param string $desc 版本描述，默认为 "版本 {version}"
     * @return string 完整的命令行字符串
     */
    protected function buildUploadCommand(string $version, string $desc = ''): string
    {
        // 默认版本描述
        $desc = $desc ?: '版本 ' . $version;

        // 构建 miniprogram-ci upload 命令
        $command = sprintf(
            'miniprogram-ci upload --pp "%s" --pkp "%s" --appid "%s" --uv "%s" -r 1 --desc "%s"',
            $this->projectPath,    // 项目路径
            $this->privateKeyPath, // 密钥文件路径
            $this->appId,          // 小程序 AppId
            $version,              // 版本号
            addslashes($desc)      // 版本描述 (转义特殊字符)
        );

        return $command;
    }

    /**
     * 构建 miniprogram-ci preview 命令
     *
     * 构建用于生成预览二维码的命令行字符串。
     *
     * 命令参数说明:
     * - --pp: 项目路径
     * - --pkp: 密钥文件路径
     * - --appid: 小程序 AppId
     * - --qrcode-format: 二维码输出格式 (image)
     * - --qrcode-output-dest: 二维码输出路径
     * - --compile-condition: 编译条件，用于指定预览页面
     *
     * @param string $qrcodePath 二维码图片保存路径
     * @param string $pagePath 预览的页面路径 (可选)
     * @return string 完整的命令行字符串
     */
    protected function buildPreviewCommand(string $qrcodePath, string $pagePath = ''): string
    {
        // 构建基本的 preview 命令
        $command = sprintf(
            'miniprogram-ci preview --pp "%s" --pkp "%s" --appid "%s" --qrcode-format image --qrcode-output-dest "%s"',
            $this->projectPath,    // 项目路径
            $this->privateKeyPath, // 密钥文件路径
            $this->appId,          // 小程序 AppId
            $qrcodePath            // 二维码输出路径
        );

        // 如果指定了预览页面，添加编译条件参数
        if ($pagePath) {
            $command .= sprintf(' --compile-condition \'{"pathName":"%s"}\'', addslashes($pagePath));
        }

        return $command;
    }

    /**
     * 检查运行环境是否满足要求
     *
     * 在执行上传或预览前检查必要的环境条件:
     * 1. 小程序 AppId 已配置
     * 2. 上传密钥文件已存在
     * 3. miniprogram-ci 工具已全局安装
     *
     * @throws AdminException 任一条件不满足时抛出异常
     */
    protected function checkEnvironment(): void
    {
        // 检查1: AppId 是否已配置
        if (empty($this->appId)) {
            throw new AdminException('请先配置小程序 AppId');
        }

        // 检查2: 密钥文件是否存在
        if (!file_exists($this->privateKeyPath)) {
            throw new AdminException('请先上传小程序代码上传密钥');
        }

        // 检查3: miniprogram-ci 是否已全局安装
        $output = [];
        exec('which miniprogram-ci 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            throw new AdminException('miniprogram-ci 未安装，请先安装运行环境');
        }
    }

    /**
     * 替换项目代码中的 API 域名
     *
     * 将小程序代码中的默认 API 域名 (https://demo.crmeb.com)
     * 替换为当前服务器的域名，确保小程序能正确调用后端接口。
     *
     * @param string $url 要替换成的新域名 (如 https://your-domain.com)
     */
    protected function updateUrl(string $url): void
    {
        // 构建 vendor.js 文件路径 (包含 API 域名配置)
        $fileUrl = $this->projectPath . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'vendor.js';
        if (!file_exists($fileUrl)) {
            return;
        }

        // 读取文件内容
        $string = file_get_contents($fileUrl);
        // 替换默认域名为当前服务器域名
        $string = str_replace('https://demo.crmeb.com', $url, $string);
        // 写回文件
        file_put_contents($fileUrl, $string);
    }

    /**
     * 更新 app.json 配置 - 移除直播插件
     *
     * 当不需要直播功能时，移除 app.json 中的 live-player-plugin 插件配置。
     * 这样可以避免在不使用直播的情况下引入不必要的依赖。
     */
    protected function updateAppJson(): void
    {
        // app.json 文件路径
        $fileUrl = $this->projectPath . DIRECTORY_SEPARATOR . 'app.json';
        if (!file_exists($fileUrl)) {
            return;
        }

        $string = file_get_contents($fileUrl);
        // 使用正则表达式移除 live-player-plugin 插件配置
        // 匹配格式: , "plugins": { "live-player-plugin": { ... } }
        $pattern = '/,\s*"plugins"\s*:\s*\{\s*"live-player-plugin"\s*:\s*\{[^}]*\}\s*\}/s';
        $string = preg_replace($pattern, '', $string);
        file_put_contents($fileUrl, $string);
    }

    /**
     * 更新 project.config.json 配置
     *
     * 替换项目配置文件中的 appid 和 projectname，
     * 确保上传的小程序使用正确的身份标识。
     *
     * @param string $appId 小程序 AppId
     * @param string $projectName 项目名称 (可选)
     */
    protected function updateConfigJson(string $appId, string $projectName = ''): void
    {
        // project.config.json 文件路径
        $fileUrl = $this->projectPath . DIRECTORY_SEPARATOR . 'project.config.json';
        if (!file_exists($fileUrl)) {
            return;
        }

        $string = file_get_contents($fileUrl);

        // 替换 appid
        $appIdPattern = '/"appid"\s*:\s*"[^"]*"/';
        $string = preg_replace($appIdPattern, '"appid": "' . $appId . '"', $string);

        // 替换项目名称 (如果提供了)
        if ($projectName) {
            $namePattern = '/"projectname"\s*:\s*"[^"]*"/';
            $string = preg_replace($namePattern, '"projectname": "' . $projectName . '"', $string);
        }

        file_put_contents($fileUrl, $string);
    }

    /**
     * 递归删除目录及其所有内容
     *
     * 用于在准备新项目前清理旧的项目文件。
     * 会递归删除指定目录下的所有文件和子目录。
     *
     * @param string $dir 要删除的目录路径
     * @return bool 删除成功返回 true
     */
    protected function deleteDirectory(string $dir): bool
    {
        // 目录不存在则直接返回成功
        if (!is_dir($dir)) {
            return true;
        }

        // 遍历目录下的所有文件和子目录 (排除 . 和 ..)
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            // 递归删除子目录，直接删除文件
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        // 删除空目录
        return rmdir($dir);
    }

    /**
     * 获取上传历史记录 (待实现)
     *
     * 该方法用于返回小程序代码的历史上传记录，
     * 包括版本号、上传时间、上传人等信息。
     *
     * @return array 上传历史记录数组
     */
    public function getUploadHistory(): array
    {
        // TODO: 实现上传历史记录功能
        // 可以将上传记录保存到数据库，包括:
        // - 版本号、版本描述
        // - 上传时间、上传人
        // - 上传结果 (成功/失败)
        // - 命令输出日志
        return [];
    }
}
