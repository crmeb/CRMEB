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
namespace app\adminapi\controller\diy;

use app\adminapi\controller\AuthController;
use app\jobs\ThemeExportJob;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\article\ArticleServices;
use app\services\diy\ThemeDownloadServices;
use app\services\diy\ThemeServices;
use app\services\product\CenterProductServices;
use app\services\system\module\ModuleInstaller;
use SplFileInfo;
use think\facade\App;

/**
 * 主题管理控制器
 * 处理主题的列表、详情、保存、导入导出等功能
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2025/12/18
 */
class ThemeController extends AuthController
{

    /**
     * @var ThemeServices 主题服务类
     */
    protected $services;

    /**
     * 构造方法
     * 注入 ThemeServices 服务
     * @param App $app 应用容器实例
     * @param ThemeServices $services 主题服务实例
     */
    public function __construct(App $app, ThemeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /** 获取主题所需活动插件安装状态
     * @return \think\Response
     */
    public function getPluginStatus(ModuleInstaller $moduleInstaller)
    {
        $result = [];
        foreach ($moduleInstaller->getStatusList() as $status) {
            // 主题活动插件由插件清单声明为 mode 类型并位于 trade 目录。
            if (($status['type'] ?? '') !== 'mode' || !str_starts_with((string)($status['path'] ?? ''), 'modules/trade/')) {
                continue;
            }
            $result[$status['key']] = (bool)($status['installed'] && $status['enabled']);
        }
        return app('json')->success($result);
    }

    /**
     * 获取主题列表
     * 支持根据标题、类型、状态筛选
     * @return \think\Response JSON格式的响应
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function getThemeList()
    {
        // 获取请求参数，设置默认值
        $where = $this->request->getMore([
            ['title', ''],
            ['type', ''],
            ['page_type', ''],
            ['is_del', 0],
        ]);
        // 调用服务层获取列表数据
        $data = $this->services->getThemeList($where);
        return app('json')->success($data);
    }

    /**
     * 获取主题详情
     * @param int $id 主题ID
     * @param string $type 查询类型（可选）
     * @return \think\Response JSON格式的响应
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function getThemeInfo($id, $type = '')
    {
        $data = $this->services->getThemeInfo($id, $type);
        return app('json')->success($data);
    }

    /**
     * 主题预览小程序二维码
     * @param int $id 主题ID
     * @return \think\Response
     */
    public function getRoutineCode($id)
    {
        $pageType = (string)$this->request->param('page_type', 'home');
        $image = $this->services->getRoutineCode((int)$id, $pageType);
        return app('json')->success(compact('image'));
    }

    /**
     * 保存主题基本信息
     * @param int $id 主题ID
     * @return \think\Response JSON格式的响应
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function saveTheme($id)
    {
        $data = $this->request->getMore([
            ['tid', 0],
            ['title', ''],
            ['type', ''],
            ['value', ''],
            ['page_type', 'theme'],
        ]);
        $id = $this->services->saveTheme($id, $data);
        return app('json')->success('保存成功', ['id' => $id]);
    }

    /**
     * 保存主题标题信息
     * @param int $id 主题ID
     * @return \think\Response JSON格式的响应
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function saveThemeTitle($id)
    {
        $data = $this->request->getMore([
            ['tid', 0],
            ['title', ''],
            ['info', ''],
            ['page_type', 'theme'],
        ]);
        $id = $this->services->saveThemeTitle($id, $data);
        return app('json')->success('保存成功', ['id' => $id]);
    }

    /**
     * 保存主题图片信息
     * @param int $id 主题ID
     * @return \think\Response JSON格式的响应
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function saveThemeImage($id)
    {
        $data = $this->request->getMore([
            ['image', ''],
            ['type', ''],
        ]);
        $id = $this->services->saveThemeImage($id, $data);
        return app('json')->success('保存成功', ['id' => $id]);
    }

    /**
     * 获取自定义组件-文章列表
     * 用于DIY页面选择文章组件的数据源
     * @return \think\Response JSON格式的响应
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/12
     */
    public function getThemeArticleList()
    {
        $where = $this->request->getMore([
            ['ids', ''],
            ['cid', ''],
            ['order', 0],
            ['sort', 0],
            ['limit', 10],
        ]);
        $data = app()->make(ArticleServices::class)->getThemeArticle($where);
        return app('json')->success($data);
    }

    /**
     * 获取自定义组件-优惠券列表
     * 用于DIY页面选择优惠券组件的数据源
     * @return \think\Response JSON格式的响应
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/13
     */
    public function getThemeCouponList()
    {
        $where = $this->request->getMore([
            ['ids', ''],
            ['type', ''],
            ['user_type', ''],
            ['send_type', ''],
            ['is_min_price', 0],
            ['min_price', 0],
            ['start_time', ''],
            ['end_time', ''],
            ['order', 0],
            ['sort', 0],
            ['limit', 10],
        ]);
        $data = app()->make(StoreCouponIssueServices::class)->getThemeCoupon($where);
        return app('json')->success($data);
    }

    /**
     * 获取自定义组件-商品列表
     * 用于DIY页面选择商品组件的数据源
     * @return \think\Response JSON格式的响应
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/13
     */
    public function getThemeProductList()
    {
        $where = $this->request->getMore([
            ['ids', ''],
            ['cate_ids', ''],
            ['order', 0],
            ['sort', 0],
            ['limit', 10],
        ]);
        $data = app()->make(CenterProductServices::class)->getThemeProduct($where);
        return app('json')->success($data);
    }

    /**
     * 导出主题数据
     * 1. 在 eb_theme_download 中写入一条待处理记录（不含 download_url）
     * 2. 将实际打包任务推入队列异步执行
     * 3. 队列完成后回填 download_url
     *
     * @param int $id 主题ID
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function exportTheme($id)
    {
        // 判断是否使用 Redis 缓存且开启了消息队列
        $queueEnabled = sys_config('queue_open', 0) == 1 && \think\facade\Env::get('cache.driver', 'file') == 'redis';
        if (!$queueEnabled) {
            return app('json')->fail('导出功能需要开启 Redis 缓存并开启消息队列，请先到系统设置中开启对应配置');
        }

        $id = (int)$id;

        // 1. 查询主题基本信息，获取标题
        $info = $this->services->getThemeInfo($id);

        // 2. 创建主题打包目录
        $dir = public_path() . 'theme/download/' . $id . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // 3. 清理打包目录，防止数据污染
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                @rmdir($fileInfo->getRealPath());
            } else {
                @unlink($fileInfo->getRealPath());
            }
        }

        // 4. 创建主题图片目录
        $imagesDir = $dir . 'images/';
        if (!is_dir($imagesDir)) mkdir($imagesDir, 0755, true);

        // 5. 向 eb_theme_download 写入待处理记录（download_url 暂不填写）
        /** @var ThemeDownloadServices $themeDownloadServices */
        $themeDownloadServices = app()->make(ThemeDownloadServices::class);
        $recordId = $themeDownloadServices->addDownloadRecord($id, $info['title'], '');

        // 6. 将打包任务推入队列
        ThemeExportJob::dispatch('export', [$info, $recordId]);

        return app('json')->success('正在导出中，请勿操作页面！', ['record_id' => $recordId]);
    }

    /**
     * 查询主题导出记录
     * 前端轮询该接口，待 download_url 不为空时说明队列已完成
     *
     * @param int $record_id 下载记录ID
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function getExportRecord($record_id)
    {
        /** @var ThemeDownloadServices $themeDownloadServices */
        $themeDownloadServices = app()->make(ThemeDownloadServices::class);
        $record = $themeDownloadServices->getDownloadInfo((int)$record_id);
        return app('json')->success([
            'download_url' => $record['download_url'] ?? '',
        ]);
    }

    /**
     * 导入主题
     * 上传 Zip 包，解压并还原主题配置
     * 1. 解压 Zip 包到 theme/import/
     * 2. 读取 config.json
     * 3. 提取包内图片移动到 uploads/theme/ 目录
     * 4. 递归遍历配置，修正图片路径并替换域名
     *
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/15
     */
    public function importTheme()
    {
        // 1 获取文件
        [$importUrl] = $this->request->postMore([
            ['url', ''],
        ], true);
        $realPath = public_path() . $importUrl;
        if (!file_exists($realPath)) return app('json')->fail('文件不存在');

        // 2. 解压文件到 theme/import/ 目录
        $dir = 'theme/import/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        // 清理导入目录，防止数据污染
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                @rmdir($fileInfo->getRealPath());
            } else {
                @unlink($fileInfo->getRealPath());
            }
        }
        $zip = new \ZipArchive();
        $zip->open($realPath);
        $zip->extractTo($dir);
        $zip->close();

        // 3. 读取解压后的 config.json 文件
        $configPath = $dir . 'config.json';
        if (!file_exists($configPath)) return app('json')->fail('文件不存在');
        $config = json_decode(file_get_contents($configPath), true);
        if (!is_array($config)) return app('json')->fail('文件内容错误');

        // 4. 处理图片资源迁移
        // 将压缩包里面的所有图片，移动到 uploads/theme/{时间戳}/ 文件夹下
        $timestamp = date('YmdHis');
        $themeDir = 'uploads/theme/' . $timestamp . '/';
        if (!is_dir($themeDir)) mkdir($themeDir, 0755, true);

        $rootPath = realpath($dir);
        $imageMap = []; // 记录相对路径到新上传路径的映射

        // 递归扫描解压目录中的所有图片
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) continue;
            $filePath = $fileInfo->getRealPath();
            // 跳过配置文件
            if (basename($filePath) === 'config.json') continue;
            // 只处理指定扩展名的图片
            if (!preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $filePath)) continue;

            // 获取文件相对于解压根目录的相对路径
            $relative = ltrim(str_replace($rootPath, '', $filePath), DIRECTORY_SEPARATOR);
            $basename = basename($filePath);

            // 目标路径
            $target = $themeDir . $basename;
            // 移动/复制文件
            if (@copy($filePath, $target)) {
                $imageMap[$relative] = $target; // 记录映射：包内相对路径 => 新系统路径
            }
        }

        // 5. 准备域名替换的基础 URL
        $siteUrl = rtrim(sys_config('site_url'), '/');
        $siteParts = parse_url($siteUrl);
        $base = '';
        if ($siteParts && isset($siteParts['host'])) {
            $scheme = $siteParts['scheme'] ?? 'http';
            $base = $scheme . '://' . $siteParts['host'];
            if (isset($siteParts['port'])) {
                $base .= ':' . $siteParts['port'];
            }
        }

        $rewriteArray = function (&$data) use (&$rewriteArray, $imageMap, $base) {
            if (!is_array($data)) return;

            foreach ($data as $k => &$v) {
                if (is_array($v)) {
                    $rewriteArray($v);
                } elseif (is_string($v)) {
                    $decoded = json_decode($v, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $rewriteArray($decoded);
                        $v = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                        continue;
                    }

                    // 检查是否在图片映射表中（处理本地导入的图片）
                    // 如果在映射表中，说明该图片已从压缩包解压并上传到 uploads/theme/ 目录
                    // 此时需要将其路径替换为带当前站点域名的完整 URL
                    if (isset($imageMap[$v])) {
                        if ($base !== '') {
                            // 拼接域名 + 新路径
                            $v = rtrim($base, '/') . '/' . ltrim($imageMap[$v], '/');
                        } else {
                            // 如果无法获取域名，则仅使用相对路径
                            $v = $imageMap[$v];
                        }
                        continue;
                    }

                    // 兼容处理 exportTheme 导出时带有的 theme/download/ 前缀
                    // 导出时，home_image 等字段被赋值为 theme/download/xxx.png
                    // 而压缩包内的文件实际上是 xxx.png，导致直接匹配 imageMap 失败
                    // 因此需要去掉 theme/download/ 前缀再次尝试匹配
                    if (strpos($v, 'theme/download/') === 0) {
                        $rel = substr($v, strlen('theme/download/'));
                        if (isset($imageMap[$rel])) {
                            if ($base !== '') {
                                // 同样拼接域名 + 新路径
                                $v = rtrim($base, '/') . '/' . ltrim($imageMap[$rel], '/');
                            } else {
                                $v = $imageMap[$rel];
                            }
                            continue;
                        }
                    }

                    // 处理域名替换（将旧域名的链接替换为当前站点域名）
                    // 防止导入的主题配置中包含旧站点的域名，导致图片无法加载

                    $parts = parse_url($v);
                    if (!$parts || !isset($parts['host']) || $base === '') {
                        continue;
                    }
                    $path = $parts['path'] ?? '';
                    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
                    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
                    $v = rtrim($base, '/') . $path . $query . $fragment;
                }
            }
            unset($v);
        };

        // 执行替换逻辑
        $rewriteArray($config);

        // 执行数据写入
        $themeId = $this->services->importThemeData($config);

        // 返回成功
        return app('json')->success('导入成功', ['theme_id' => $themeId]);
    }

    /**
     * @description: 使用主题
     * @param int $id 主题ID
     * @return array
     */
    public function useTheme(int $id)
    {
        $this->services->useTheme($id);
        return app('json')->success('使用成功');
    }

    /**
     * @description: 使用主题数据
     * @param array $data 主题数据
     * @return array
     */
    public function useThemeData($id)
    {
        [$theme_id, $type] = $this->request->getMore([
            ['theme_id', 0],
            ['type', ''],
        ], true);
        $this->services->useThemeData($id, $theme_id, $type);
        return app('json')->success('使用成功');
    }

    /**
     * @description: 获取正在使用的主题
     * @return array
     */
    public function getUsingTheme()
    {
        $theme = $this->services->getUsingTheme();
        return app('json')->success($theme);
    }

    /**
     * @description: 还原主题
     * @param int $id 主题ID
     * @return array
     */
    public function restoreTheme(int $id)
    {
        $this->services->restoreTheme($id);
        return app('json')->success('还原成功');
    }

    /**
     * @description: 删除主题
     * @param int $id 主题ID
     * @return array
     */
    public function deleteTheme(int $id)
    {
        $this->services->deleteTheme($id);
        return app('json')->success('删除成功');
    }

    /**
     * 获取微页面数据列表
     * @return \think\Response JSON格式的响应
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/20
     */
    public function getMicroPageList()
    {
        $data = $this->services->getMicroPageList();
        return app('json')->success($data);
    }
}
