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
namespace app\services\system;

use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use think\facade\Log;

/**
 * Node.js 环境管理服务类
 *
 * 功能概述:
 * 本服务类负责检测和管理小程序 CI 上传所需的运行环境，
 * 包括 Node.js、npm 和 miniprogram-ci 工具的检测与安装指导。
 *
 * 主要功能:
 * 1. 环境检测 - 检测 Node.js、npm、miniprogram-ci 的安装状态和版本
 * 2. 系统识别 - 识别操作系统类型 (CentOS/Ubuntu/macOS/Windows)
 * 3. 安装指南 - 根据操作系统提供对应的安装命令
 * 4. exec 函数检测 - 检查 PHP 的 exec() 函数是否可用
 *
 * 环境要求:
 * - Node.js >= 14.0.0 (推荐 18.x LTS)
 * - npm (随 Node.js 一起安装)
 * - miniprogram-ci (通过 npm install -g miniprogram-ci 全局安装)
 * - PHP exec() 函数未被禁用
 *
 * @package app\services\system
 */
class NodeEnvironmentServices extends BaseService
{
    /**
     * Node.js 最低版本要求
     *
     * miniprogram-ci 工具需要 Node.js 14.0.0 或更高版本。
     */
    const MIN_NODE_VERSION = '14.0.0';

    /**
     * 推荐的 Node.js 版本
     *
     * 推荐使用 Node.js 18 LTS 版本，提供更好的性能和安全性。
     */
    const RECOMMENDED_NODE_VERSION = '18';

    /**
     * 获取完整的环境状态信息
     *
     * 检测并返回小程序 CI 上传所需的所有环境信息，
     * 前端根据这些信息展示环境就绪状态或引导用户完成环境配置。
     *
     * @return array 完整的环境状态信息，包含:
     *               - os: 操作系统信息 {family, type, version}
     *               - node: Node.js 状态 {installed, version, path, meets_requirement}
     *               - npm: npm 状态 {installed, version}
     *               - miniprogram_ci: CI工具状态 {installed, version}
     *               - ready: 环境是否完全就绪
     *               - can_install: 是否支持自动安装
     *               - exec_enabled: exec 函数是否可用
     *               - message: 提示信息
     */
    public function getEnvironmentStatus(): array
    {
        // 检测各项环境
        $nodeInfo = $this->checkNodeInstalled();       // Node.js 状态
        $npmInfo = $this->checkNpmInstalled();         // npm 状态
        $ciInfo = $this->checkMiniprogramCIInstalled(); // miniprogram-ci 状态
        $osInfo = $this->getOsInfo();                  // 操作系统信息
        $execEnabled = $this->isExecEnabled();         // exec 函数可用性

        return [
            'os' => $osInfo,                           // 操作系统信息
            'node' => $nodeInfo,                       // Node.js 状态
            'npm' => $npmInfo,                         // npm 状态
            'miniprogram_ci' => $ciInfo,               // miniprogram-ci 状态
            // 环境就绪条件: Node.js + npm + miniprogram-ci 均已安装且 exec 可用
            'ready' => $nodeInfo['installed'] && $npmInfo['installed'] && $ciInfo['installed'] && $execEnabled,
            'can_install' => $this->canAutoInstall(),  // 是否支持自动安装
            'exec_enabled' => $execEnabled,            // exec 函数是否可用
            'message' => $execEnabled ? '' : '服务器禁用了 exec 函数，无法使用小程序上传功能。请联系服务器管理员启用 exec 函数。',
        ];
    }

    /**
     * 检查 PHP exec() 函数是否可用
     *
     * 小程序 CI 上传功能依赖 PHP 的 exec() 函数来执行命令行工具。
     * 很多服务器出于安全考虑会禁用该函数，需要检测其可用性。
     *
     * 检测步骤:
     * 1. 检查 exec 函数是否存在
     * 2. 检查 disable_functions 配置中是否包含 exec
     * 3. 尝试执行简单命令验证实际可用性
     *
     * @return bool exec 函数可用返回 true，否则返回 false
     */
    public function isExecEnabled(): bool
    {
        // 检查 exec 函数是否存在
        if (!function_exists('exec')) {
            return false;
        }

        // 检查 disable_functions 配置中是否禁用了 exec
        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        if (in_array('exec', $disabled)) {
            return false;
        }

        // 尝试执行一个简单命令验证实际可用性
        $output = [];
        $code = 0;
        @exec('echo test 2>&1', $output, $code);

        return $code === 0 && !empty($output);
    }

    /**
     * 检查 Node.js 是否已安装
     *
     * 通过执行 node -v 命令获取 Node.js 的安装状态和版本信息。
     *
     * @return array Node.js 状态信息，包含:
     *               - installed: 是否已安装
     *               - version: 版本号 (如 18.17.0)
     *               - path: 可执行文件路径
     *               - meets_requirement: 是否满足最低版本要求
     */
    public function checkNodeInstalled(): array
    {
        $result = [
            'installed' => false,
            'version' => '',
            'path' => '',
            'meets_requirement' => false,
        ];

        // 执行 node -v 获取版本信息
        $output = $this->execCommand('node -v 2>&1');
        if ($output && preg_match('/v?(\d+\.\d+\.\d+)/', $output, $matches)) {
            $result['installed'] = true;
            $result['version'] = $matches[1];
            // 检查版本是否满足最低要求 (>= 14.0.0)
            $result['meets_requirement'] = version_compare($matches[1], self::MIN_NODE_VERSION, '>=');

            // 获取 node 可执行文件的完整路径
            $path = $this->execCommand('which node 2>&1');
            $result['path'] = trim($path);
        }

        return $result;
    }

    /**
     * 检查 npm 是否已安装
     *
     * npm 是 Node.js 的包管理器，通常随 Node.js 一起安装。
     * 用于安装 miniprogram-ci 等 npm 包。
     *
     * @return array npm 状态信息，包含:
     *               - installed: 是否已安装
     *               - version: 版本号
     */
    public function checkNpmInstalled(): array
    {
        $result = [
            'installed' => false,
            'version' => '',
        ];

        // 执行 npm -v 获取版本信息
        $output = $this->execCommand('npm -v 2>&1');
        if ($output && preg_match('/(\d+\.\d+\.\d+)/', $output, $matches)) {
            $result['installed'] = true;
            $result['version'] = $matches[1];
        }

        return $result;
    }

    /**
     * 检查 miniprogram-ci 是否已全局安装
     *
     * miniprogram-ci 是微信官方提供的小程序代码上传工具。
     * 需要通过 npm install -g miniprogram-ci 全局安装。
     *
     * @return array miniprogram-ci 状态信息，包含:
     *               - installed: 是否已安装
     *               - version: 版本号
     */
    public function checkMiniprogramCIInstalled(): array
    {
        $result = [
            'installed' => false,
            'version' => '',
        ];

        // 通过 npm list -g 检查全局安装的包
        $output = $this->execCommand('npm list -g miniprogram-ci --depth=0 2>&1');
        if ($output && preg_match('/miniprogram-ci@(\d+\.\d+\.\d+)/', $output, $matches)) {
            $result['installed'] = true;
            $result['version'] = $matches[1];
        }

        return $result;
    }

    /**
     * 获取操作系统信息
     *
     * 识别服务器的操作系统类型，用于提供对应的安装指南。
     * 使用命令行方式检测，避免受 open_basedir 限制影响。
     *
     * 支持识别的系统:
     * - Linux: CentOS/RHEL/Rocky/Ubuntu/Debian/Alpine 等
     * - macOS: 通过 sw_vers 获取版本
     * - Windows: 通过 PHP_OS_FAMILY 识别
     *
     * @return array 操作系统信息，包含:
     *               - family: 系统家族 (Linux/Darwin/Windows)
     *               - type: 具体类型 (centos/ubuntu/debian/macos/windows)
     *               - version: 系统版本号
     */
    public function getOsInfo(): array
    {
        $os = PHP_OS_FAMILY;  // 获取 PHP 识别的操作系统家族
        $type = 'unknown';
        $version = '';

        if ($os === 'Linux') {
            // Linux 系统: 通过命令行读取 /etc/os-release 获取发行版信息
            $osRelease = $this->execCommand('cat /etc/os-release 2>/dev/null');

            if ($osRelease) {
                // 解析 os-release 文件内容
                $lines = explode("\n", $osRelease);
                $osInfo = [];
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $osInfo[trim($key)] = trim($value, '"\' ');
                    }
                }

                $id = strtolower($osInfo['ID'] ?? '');
                $version = $osInfo['VERSION_ID'] ?? '';

                // 映射操作系统类型
                if (in_array($id, ['centos', 'rhel', 'rocky', 'almalinux', 'fedora'])) {
                    $type = 'centos';  // Red Hat 系列
                } elseif ($id === 'ubuntu') {
                    $type = 'ubuntu';
                } elseif (in_array($id, ['debian', 'raspbian'])) {
                    $type = 'debian';
                } elseif ($id === 'alpine') {
                    $type = 'alpine';
                } else {
                    $type = $id ?: 'linux';
                }
            } else {
                // 备用方案: 使用 uname 命令
                $uname = $this->execCommand('uname -a 2>/dev/null');
                $type = 'linux';
                $version = $uname ?: '';
            }
        } elseif ($os === 'Darwin') {
            // macOS 系统
            $type = 'macos';
            $version = $this->execCommand('sw_vers -productVersion 2>&1') ?: '';
        } elseif ($os === 'Windows') {
            // Windows 系统
            $type = 'windows';
        }

        return [
            'family' => $os,            // 系统家族
            'type' => $type,            // 具体类型
            'version' => trim($version), // 版本号
        ];
    }

    /**
     * 检查是否支持自动安装
     *
     * 检查服务器环境是否支持自动安装 Node.js 和相关工具。
     * 自动安装功能依赖于操作系统类型和 PHP 函数可用性。
     *
     * 支持的操作系统: CentOS/RHEL、Ubuntu、Debian、Alpine、macOS
     *
     * @return bool 支持自动安装返回 true
     */
    public function canAutoInstall(): bool
    {
        $os = $this->getOsInfo();

        // 支持自动安装的操作系统列表
        $supportedOs = ['centos', 'rhel', 'ubuntu', 'debian', 'alpine', 'macos'];

        if (!in_array($os['type'], $supportedOs)) {
            return false;
        }

        // 检查是否有执行命令的权限 (exec 或 shell_exec)
        if (!function_exists('exec') && !function_exists('shell_exec')) {
            return false;
        }

        return true;
    }

    /**
     * 执行命令并返回输出
     *
     * 封装的命令执行方法，优先使用 exec，如果不可用则尝试 shell_exec。
     *
     * @param string $command 要执行的命令
     * @return string|null 命令输出，执行失败返回 null
     */
    protected function execCommand(string $command): ?string
    {
        // 优先使用 exec 函数
        if (function_exists('exec')) {
            $output = [];
            exec($command, $output);
            return implode("\n", $output);
        }
        // 备用: 使用 shell_exec 函数
        elseif (function_exists('shell_exec')) {
            return shell_exec($command);
        }

        return null;
    }

    /**
     * 获取一键安装脚本 URL
     *
     * 返回用于自动安装 Node.js 环境的 Shell 脚本地址。
     *
     * @return string 安装脚本的 URL 地址
     */
    public function getInstallScriptUrl(): string
    {
//        return sys_config('site_url', '') . '/statics/scripts/install_node_env.sh';
        return 'https://www.crmeb.com/static/upgrade/install_node_env.sh';
    }

    /**
     * 获取安装指南 (手动安装说明)
     *
     * 根据服务器操作系统类型返回对应的 Node.js 和 miniprogram-ci 安装步骤。
     * 每个操作系统都有专门优化的安装命令。
     *
     * 支持的操作系统:
     * - CentOS/RHEL: 使用 NodeSource 的 rpm 仓库
     * - Ubuntu/Debian: 使用 NodeSource 的 deb 仓库
     * - macOS: 使用 Homebrew 包管理器
     * - Windows: 从 Node.js 官网下载安装包
     *
     * @return array 安装指南信息，包含:
     *               - title: 指南标题 (如 "CentOS/RHEL 安装指南")
     *               - steps: 安装步骤数组，包含具体的命令行指令
     *               - script_url: 一键安装脚本的 URL 地址
     */
    public function getInstallGuide(): array
    {
        // 获取当前操作系统信息
        $os = $this->getOsInfo();

        // 各操作系统的安装指南
        $guides = [
            // CentOS/RHEL 系列 - 使用 yum 包管理器
            'centos' => [
                'title' => 'CentOS/RHEL 安装指南',
                'steps' => [
                    '1. 添加 NodeSource 仓库:',
                    '   curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -',
                    '2. 安装 Node.js:',
                    '   sudo yum install -y nodejs',
                    '3. 验证安装:',
                    '   node -v && npm -v',
                    '4. 安装 miniprogram-ci:',
                    '   sudo npm install miniprogram-ci -g',
                ],
            ],
            // Ubuntu - 使用 apt 包管理器
            'ubuntu' => [
                'title' => 'Ubuntu/Debian 安装指南',
                'steps' => [
                    '1. 添加 NodeSource 仓库:',
                    '   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -',
                    '2. 安装 Node.js:',
                    '   sudo apt-get install -y nodejs',
                    '3. 验证安装:',
                    '   node -v && npm -v',
                    '4. 安装 miniprogram-ci:',
                    '   sudo npm install miniprogram-ci -g',
                ],
            ],
            // Debian - 与 Ubuntu 相同
            'debian' => [
                'title' => 'Ubuntu/Debian 安装指南',
                'steps' => [
                    '1. 添加 NodeSource 仓库:',
                    '   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -',
                    '2. 安装 Node.js:',
                    '   sudo apt-get install -y nodejs',
                    '3. 验证安装:',
                    '   node -v && npm -v',
                    '4. 安装 miniprogram-ci:',
                    '   sudo npm install miniprogram-ci -g',
                ],
            ],
            // macOS - 使用 Homebrew
            'macos' => [
                'title' => 'macOS 安装指南',
                'steps' => [
                    '1. 安装 Homebrew (如果未安装):',
                    '   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"',
                    '2. 安装 Node.js:',
                    '   brew install node@18',
                    '3. 验证安装:',
                    '   node -v && npm -v',
                    '4. 安装 miniprogram-ci:',
                    '   npm install miniprogram-ci -g',
                ],
            ],
            // Windows - 从官网下载安装
            'windows' => [
                'title' => 'Windows 安装指南',
                'steps' => [
                    '1. 下载 Node.js 安装包:',
                    '   访问 https://nodejs.org/zh-cn/download/',
                    '2. 运行安装程序，按提示完成安装',
                    '3. 打开命令提示符，验证安装:',
                    '   node -v && npm -v',
                    '4. 安装 miniprogram-ci:',
                    '   npm install miniprogram-ci -g',
                ],
            ],
        ];

        // 根据操作系统类型获取对应指南，未知系统使用通用指南
        $type = $os['type'] ?: 'unknown';
        $guide = isset($guides[$type]) ? $guides[$type] : [
            'title' => '通用安装指南',
            'steps' => [
                '1. 访问 Node.js 官网下载安装包:',
                '   https://nodejs.org/zh-cn/download/',
                '2. 按照官方文档完成安装',
                '3. 验证安装:',
                '   node -v && npm -v',
                '4. 安装 miniprogram-ci:',
                '   npm install miniprogram-ci -g',
            ],
        ];

        // 添加一键安装脚本 URL
        $guide['script_url'] = $this->getInstallScriptUrl();

        return $guide;
    }
}
