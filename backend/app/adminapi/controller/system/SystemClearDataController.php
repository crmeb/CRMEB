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
namespace app\adminapi\controller\system;


use think\facade\App;
use think\facade\Db;
use think\facade\Env;
use app\adminapi\controller\AuthController;
use app\services\system\SystemClearServices;


/**
 * 清除默认数据理控制器
 * Class SystemClearData
 * @package app\admin\controller\system
 */
class SystemClearDataController extends AuthController
{
    /**
     * @var SystemClearServices
     */
    protected $services;


    /**
     * 构造方法
     * SystemClearData constructor.
     * @param App $app
     * @param SystemClearServices $services
     */
    public function __construct(App $app, SystemClearServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 危险清理统一门禁：开发模式 + 超管 + 登录密码
     * @return mixed|null 失败时返回 json 响应，通过返回 null
     */
    protected function guardDangerousAction()
    {
        if (!Env::get('app_debug', false)) {
            return app('json')->fail('生产环境无法使用此功能，如需使用请修改.env文件中app_debug项为true');
        }
        if (!$this->adminInfo || (int)($this->adminInfo['level'] ?? -1) !== 0) {
            return app('json')->fail('仅超级管理员可以操作');
        }
        [$pwd] = $this->request->postMore([
            ['pwd', ''],
        ], true);
        if ($pwd === '' || $pwd === null) {
            return app('json')->fail('请输入登录密码');
        }
        $admin = Db::name('system_admin')->where('id', (int)$this->adminId)->where('is_del', 0)->find();
        if (!$admin || !password_verify((string)$pwd, (string)($admin['pwd'] ?? ''))) {
            return app('json')->fail('登录密码错误');
        }
        return null;
    }

    /**
     * 统一清除入口
     * @param string $type
     * @return mixed
     */
    public function index($type)
    {
        if ($deny = $this->guardDangerousAction()) {
            return $deny;
        }
        switch ($type) {
            case 'temp':
                return $this->userTemp();
            case 'recycle':
                return $this->recycleProduct();
            case 'store':
                return $this->storeData();
            case 'category':
                return $this->categoryData();
            case 'order':
                return $this->orderData();
            case 'kefu':
                return $this->kefuData();
            case 'wechat':
                return $this->wechatData();
            case 'article':
                return $this->articleData();
            case 'attachment':
                return $this->attachmentData();
            case 'system':
                return $this->systemData();
            case 'user':
                return $this->userRelevantData();
            default:
                return app('json')->fail('参数错误');
        }
    }

    /**
     * 清除用户生成的临时附件
     * @return mixed
     */
    public function userTemp()
    {
        $this->services->clearTempData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除回收站商品
     * @return mixed
     */
    public function recycleProduct()
    {
        $this->services->clearRecycleProduct();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除用户数据
     * @return mixed
     */
    public function userRelevantData()
    {
        $this->services->clearUserRelevantData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除商城数据
     * @return mixed
     */
    public function storeData()
    {
        $this->services->clearStoreData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除商品分类
     * @return mixed
     */
    public function categoryData()
    {
        $this->services->clearCategoryData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除订单数据
     * @return mixed
     */
    public function orderData()
    {
        $this->services->clearOrderData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除客服数据
     * @return mixed
     */
    public function kefuData()
    {
        $this->services->clearKefuData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除微信管理数据
     * @return mixed
     */
    public function wechatData()
    {
        $this->services->clearWechatData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除所有附件
     * @return mixed
     */
    public function attachmentData()
    {
        $this->services->clearAttachmentData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除内容分类
     * @return mixed
     */
    public function articleData()
    {
        $this->services->clearArticleData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 清除系统记录
     * @return mixed
     */
    public function systemData()
    {
        $this->services->clearSystemLogData();
        return app('json')->success('清除数据成功');
    }

    /**
     * 恢复出厂设置
     * @return mixed
     */
    public function factoryReset()
    {
        if ($deny = $this->guardDangerousAction()) {
            return $deny;
        }
        $steps = $this->services->factoryReset((int)$this->adminId);
        return app('json')->success('恢复出厂成功，请重新登录', ['steps' => $steps, 'relogin' => true]);
    }

    /**
     * 获取当前网站域名（用于更换域名弹窗默认原域名）
     * @return mixed
     */
    public function getSiteUrl()
    {
        return app('json')->success(['site_url' => (string)sys_config('site_url')]);
    }

    /**
     * 替换域名方法
     * @return mixed
     */
    public function replaceSiteUrl()
    {
        if ($deny = $this->guardDangerousAction()) {
            return $deny;
        }
        [$url, $oldUrl] = $this->request->postMore([
            ['url', ''],
            ['old_url', ''],
        ], true);
        $url = trim((string)$url);
        $oldUrl = trim((string)$oldUrl);
        if ($oldUrl === '') {
            return app('json')->fail('请输入要被替换的域名');
        }
        if ($url === '') {
            return app('json')->fail('请输入新的域名');
        }
        if (!verify_domain($oldUrl)) {
            return app('json')->fail('要被替换的域名不合法，格式为：http://或https://域名');
        }
        if (!verify_domain($url)) {
            return app('json')->fail('新域名不合法，格式为：http://或https://域名');
        }
        $this->services->replaceSiteUrl($url, $oldUrl);
        return app('json')->success('替换成功');
    }
}
