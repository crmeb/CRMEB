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
namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\Request;
use app\services\system\config\SystemConfigServices;
use app\services\system\config\SystemConfigTabServices;
use app\services\system\SystemPemServices;
use crmeb\services\CacheService;
use crmeb\services\easywechat\orderShipping\MiniOrderService;
use think\facade\App;

/**
 * 系统配置
 * Class SystemConfig
 * @package app\adminapi\controller\v1\setting
 */
class SystemConfig extends AuthController
{

    /**
     * SystemConfig constructor.
     * @param App $app
     * @param SystemConfigServices $services
     */
    public function __construct(App $app, SystemConfigServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['tab_id', 0],
            ['config_name', ''],
            ['status', -1]
        ]);
        if (!$where['tab_id'] && $where['config_name'] == '') {
            return app('json')->fail(100100);
        }
        if ($where['status'] == -1) {
            unset($where['status']);
        }
        return app('json')->success($this->services->getConfigList($where));
    }

    /**
     * 显示创建资源表单页.
     * @return \think\Response
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create()
    {
        [$type, $tabId] = $this->request->getMore([
            [['type', 'd'], ''],
            [['tab_id', 'd'], 1]
        ], true);
        return app('json')->success($this->services->createFormRule($type, $tabId));
    }

    /**
     * 保存新建的资源
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['menu_name', ''],
            ['type', ''],
            ['input_type', 'input'],
            ['config_tab_id', 0],
            ['parameter', ''],
            ['upload_type', 1],
            ['required', ''],
            ['width', 0],
            ['high', 0],
            ['value', ''],
            ['info', ''],
            ['desc', ''],
            ['sort', 0],
            ['level', 0],
            ['link_data', []],
            ['status', 0]
        ]);
        if (is_array($data['config_tab_id'])) $data['config_tab_id'] = end($data['config_tab_id']);
        if (!$data['info']) return app('json')->fail(400274);
        if (!$data['menu_name']) return app('json')->fail(400275);
        if (!$data['desc']) return app('json')->fail(400276);
        if ($data['sort'] < 0) {
            $data['sort'] = 0;
        }
        if ($data['type'] == 'text') {
            if (!$data['width']) return app('json')->fail(400277);
            if ($data['width'] <= 0) return app('json')->fail(400278);
        }
        if ($data['type'] == 'textarea') {
            if (!$data['width']) return app('json')->fail(400279);
            if (!$data['high']) return app('json')->fail(400280);
            if ($data['width'] < 0) return app('json')->fail(400281);
            if ($data['high'] < 0) return app('json')->fail(400282);
        }
        if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
            if (!$data['parameter']) return app('json')->fail(400283);
            $this->services->valiDateRadioAndCheckbox($data);
        }
        if ($data['level'] == 1) {
            if (!$data['link_data']) return app('json')->fail('请选择关联顶级选项');
            $data['link_id'] = $data['link_data'][0];
            $data['link_value'] = $data['link_data'][1];
        }
        $data['value'] = json_encode($data['value']);
        $config = $this->services->getOne(['menu_name' => $data['menu_name']]);
        if ($config) {
            $this->services->update($config['id'], $data, 'id');
        } else {
            $this->services->save($data);
        }
        CacheService::clear();
        return app('json')->success(400284);
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        $info = $this->services->getReadList((int)$id);
        return app('json')->success(compact('info'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        return app('json')->success($this->services->editConfigForm((int)$id));
    }

    /**
     * 保存更新的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        $type = request()->post('type');
        if ($type == 'text' || $type == 'textarea' || $type == 'radio' || ($type == 'upload' && (request()->post('upload_type') == 1 || request()->post('upload_type') == 3))) {
            $value = request()->post('value');
        } else {
            $value = request()->post('value/a');
        }
        if (!$value) $value = request()->post(request()->post('menu_name'));
        $data = $this->request->postMore([
            ['menu_name', ''],
            ['type', ''],
            ['input_type', 'input'],
            ['config_tab_id', 0],
            ['parameter', ''],
            ['upload_type', 1],
            ['required', ''],
            ['width', 0],
            ['high', 0],
            ['value', $value],
            ['info', ''],
            ['desc', ''],
            ['sort', 0],
            ['level', 0],
            ['link_data', []],
            ['status', 0]
        ]);
        if (is_array($data['config_tab_id'])) $data['config_tab_id'] = end($data['config_tab_id']);
        if (!$this->services->get($id)) {
            return app('json')->fail(100026);
        }
        if ($data['level'] == 1) {
            if (!$data['link_data']) return app('json')->fail('请选择关联顶级选项');
            $data['link_id'] = $data['link_data'][0];
            $data['link_value'] = $data['link_data'][1];
        }
        $data['value'] = json_encode($data['value']);
        $this->services->update($id, $data);
        CacheService::clear();
        return app('json')->success(100001);
    }

    /**
     * 删除指定资源
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$this->services->delete($id))
            return app('json')->fail(100008);
        else {
            CacheService::clear();
            return app('json')->success(100002);
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) {
            return app('json')->fail(100100);
        }
        $this->services->update($id, ['status' => $status]);
        CacheService::clear();
        return app('json')->success(100014);
    }

    /**
     * 基础配置
     * */
    public function edit_basics(Request $request)
    {
        $tabId = $this->request->param('tab_id', 1);
        if (!$tabId) {
            return app('json')->fail(100100);
        }
        $url = $request->baseUrl();
        return app('json')->success($this->services->getConfigForm($url, $tabId));
    }

    /**
     * 保存数据    true
     * */
    public function save_basics(Request $request)
    {
        $post = $this->request->post();
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $res = $this->services->getUploadTypeList($k);
                foreach ($res as $kk => $vv) {
                    if ($kk == 'upload') {
                        if ($vv == 1 || $vv == 3) {
                            $post[$k] = $v[0];
                        }
                    }
                }
            }
        }
        $this->validate($post, \app\adminapi\validate\setting\SystemConfigValidata::class);
        if (isset($post['upload_type'])) {
            $this->services->checkThumbParam($post);
        }
        if (isset($post['extract_type']) && !count($post['extract_type'])) {
            return app('json')->fail(400753);
        }
        if (isset($post['store_brokerage_binding_status'])) {
            $this->services->checkBrokerageBinding($post);
        }
        if (isset($post['store_brokerage_ratio']) && isset($post['store_brokerage_two'])) {
            $num = $post['store_brokerage_ratio'] + $post['store_brokerage_two'];
            if ($num > 100) {
                return app('json')->fail(400285);
            }
        }
        if (isset($post['spread_banner'])) {
            $num = count($post['spread_banner']);
            if ($num > 5) {
                return app('json')->fail(400286);
            }
        }
        if (isset($post['user_extract_min_price'])) {
            if (!preg_match('/[0-9]$/', $post['user_extract_min_price'])) {
                return app('json')->fail(400287);
            }
        }
        if (isset($post['wss_open'])) {
            $this->services->saveSslFilePath((int)$post['wss_open'], $post['wss_local_pk'] ?? '', $post['wss_local_cert'] ?? '');
        }
        if (isset($post['store_brokerage_price']) && $post['store_brokerage_statu'] == 3) {
            if ($post['store_brokerage_price'] === '') {
                return app('json')->fail(400288);
            }
            if ($post['store_brokerage_price'] < 0) {
                return app('json')->fail(400289);
            }
        }
        if (isset($post['store_brokerage_binding_time']) && $post['store_brokerage_binding_status'] == 2) {
            if (!preg_match("/^[0-9][0-9]*$/", $post['store_brokerage_binding_time'])) {
                return app('json')->fail(400290);
            }
        }
        if (isset($post['uni_brokerage_price']) && $post['uni_brokerage_price'] < 0) {
            return app('json')->fail(400756);
        }
        if (isset($post['day_brokerage_price_upper']) && $post['day_brokerage_price_upper'] < -1) {
            return app('json')->fail(400757);
        }
        if (isset($post['pay_new_weixin_open']) && (bool)$post['pay_new_weixin_open']) {
            if (empty($post['pay_new_weixin_mchid'])) {
                return app('json')->fail(400763);
            }
        }
        if (isset($post['uni_brokerage_price']) && preg_match('/\.[0-9]{2,}[1-9][0-9]*$/', (string)$post['uni_brokerage_price']) > 0) {
            return app('json')->fail(500029);
        }

        if (isset($post['weixin_ckeck_file'])) {
            $from = public_path() . $post['weixin_ckeck_file'];
            $to = public_path() . array_reverse(explode('/', $post['weixin_ckeck_file']))[0];
            @copy($from, $to);
        }
        if (isset($post['ico_path'])) {
            $from = public_path() . $post['ico_path'];
            $toAdmin = public_path('admin') . 'favicon.ico';
            $toHome = public_path('home') . 'favicon.ico';
            $toPublic = public_path() . 'favicon.ico';
            @copy($from, $toAdmin);
            @copy($from, $toHome);
            @copy($from, $toPublic);
        }
        if (isset($post['reward_integral']) || isset($post['reward_money'])) {
            if ($post['reward_money'] < 0) return app('json')->fail('赠送余额不能小于0元');
            if ($post['reward_integral'] < 0) return app('json')->fail('赠送积分不能小于0');
        }

        if (isset($post['sign_give_point'])) {
            if (!is_int($post['sign_give_point']) || $post['sign_give_point'] < 0) {
                return app('json')->fail('签到赠送积分请填写大于等于0的整数');
            }
        }
        if (isset($post['sign_give_exp'])) {
            if ((int)$post['sign_give_exp'] < 0) {
                return app('json')->fail('签到赠送经验请填写大于等于0的整数');
            }
        }
        if (isset($post['integral_frozen'])) {
            if (!ctype_digit($post['integral_frozen']) || $post['integral_frozen'] < 0) {
                return app('json')->fail('积分冻结天数请填写大于等于0的整数');
            }
        }
        if (isset($post['store_free_postage'])) {
            if (!is_int($post['store_free_postage']) || $post['store_free_postage'] < 0) {
                return app('json')->fail('满额包邮请填写大于等于0的整数');
            }
        }
        if (isset($post['withdrawal_fee'])) {
            if ($post['withdrawal_fee'] < 0 || $post['withdrawal_fee'] > 100) {
                return app('json')->fail('提现手续费范围在0-100之间');
            }
        }
        if (isset($post['routine_auth_type']) && count($post['routine_auth_type']) == 0) {
            return app('json')->fail('微信和手机号登录开关至少开启一个');
        }
        if (isset($post['integral_max_num'])) {
            if (!ctype_digit($post['integral_max_num']) || $post['integral_max_num'] < 0) {
                return app('json')->fail('积分抵扣上限请填写大于等于0的整数');
            }
        }
        if (isset($post['customer_phone'])) {
            if (!ctype_digit($post['customer_phone']) || strlen($post['customer_phone']) > 11) {
                return app('json')->fail('客服手机号为11位数字');
            }
        }
        if (isset($post['refund_time_available'])) {
            if (!ctype_digit($post['refund_time_available'])) {
                return app('json')->fail('售后期限必须为大于0的整数');
            }
        }
        if (isset($post['sms_save_type']) && sys_config('sms_account', '') != '') {
            return app('json')->success(100001);
        }
        if (isset($post['param_filter_data'])) {
            $post['param_filter_data'] = base64_encode($post['param_filter_data']);
        }
        if (isset($post['product_type_config'])) {
            if (count($post['product_type_config']) == 0) {
                return app('json')->fail('商品类型至少选择一项');
            }
        }
        if (isset($post['yue_pay_status']) && $post['yue_pay_status'] == 1) {
            $post['balance_func_status'] = 1;
        }
        if (isset($post['pay_weixin_client_cert'])) {
            $certData = [
                'type' => 'wechat',
                'name' => 'pay_weixin_client_cert',
                'path' => 'cert' . time() . rand(1000, 9999),
                'content' => $post['pay_weixin_client_cert'] != '' ? file_get_contents($this->getPemPath($post['pay_weixin_client_cert'])) : '',
            ];
            $keyData = [
                'type' => 'wechat',
                'name' => 'pay_weixin_client_key',
                'path' => 'key' . time() . rand(1000, 9999),
                'content' => $post['pay_weixin_client_key'] != '' ? file_get_contents($this->getPemPath($post['pay_weixin_client_key'])) : '',
            ];
            $systemPemServices = app()->make(SystemPemServices::class);
            $systemPemServices->savePem($certData);
            $systemPemServices->savePem($keyData);
        }

        if (isset($post['merchant_cert_path'])) {
            $merchantCertData = [
                'type' => 'alipay',
                'name' => 'merchant_cert_path',
                'path' => 'merchant_cert' . time() . rand(1000, 9999),
                'content' => $post['merchant_cert_path'] != '' ? file_get_contents($this->getPemPath($post['merchant_cert_path'])) : '',
            ];
            $alipayCertData = [
                'type' => 'alipay',
                'name' => 'alipay_cert_path',
                'path' => 'alipay_cert' . time() . rand(1000, 9999),
                'content' => $post['alipay_cert_path'] != '' ? file_get_contents($this->getPemPath($post['alipay_cert_path'])) : '',
            ];
            $alipayRootCertData = [
                'type' => 'alipay',
                'name' => 'alipay_root_cert_path',
                'path' => 'alipay_root_cert' . time() . rand(1000, 9999),
                'content' => $post['alipay_root_cert_path'] != '' ? file_get_contents($this->getPemPath($post['alipay_root_cert_path'])) : '',
            ];
            $systemPemServices = app()->make(SystemPemServices::class);
            $systemPemServices->savePem($merchantCertData);
            $systemPemServices->savePem($alipayCertData);
            $systemPemServices->savePem($alipayRootCertData);
        }


        foreach ($post as $k => $v) {
            $config_one = $this->services->getOne(['menu_name' => $k]);
            if ($config_one) {
                $config_one['value'] = $v;
                $this->services->valiDateValue($config_one);
                $this->services->update($k, ['value' => json_encode($v)], 'menu_name');
            }
        }
        CacheService::clear();
        return app('json')->success(100001);
    }

    /**
     * 获取证书文件路径
     * @param string $path
     * @return string
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/10/21
     */
    public function getPemPath(string $path)
    {
        if (strstr($path, 'http://') || strstr($path, 'https://')) {
            $path = parse_url($path)['path'] ?? '';
        }
        $path = root_path('runtime/pem') . ltrim($path, '/');
        if (!file_exists($path)) {
            $path = public_path('uploads') . ltrim($path, '/');
        }
        return $path;
    }

    /**
     * 获取系统设置头部分类
     * @param SystemConfigTabServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function header_basics(SystemConfigTabServices $services)
    {
        [$type, $pid] = $this->request->getMore([
            [['type', 'd'], 0],
            [['pid', 'd'], 0]
        ], true);
        if ($type == 3) {//其它分类
            $config_tab = [];
        } else {
            $config_tab = $services->getConfigTab($pid);
            if (empty($config_tab)) $config_tab[] = $services->get($pid, ['id', 'id as value', 'title as label', 'pid', 'icon', 'type']);
        }
        return app('json')->success(compact('config_tab'));
    }

    /**
     * 获取单个配置的值
     * @param $name
     * @return mixed
     */
    public function get_system($name)
    {
        $value = sys_config($name);
        return app('json')->success(compact('value'));
    }

    /**
     * 获取某个分类下的所有配置
     * @param $tabId
     * @return mixed
     */
    public function get_config_list($tabId)
    {
        $list = $this->services->getConfigTabAllList($tabId);
        $data = [];
        foreach ($list as $item) {
            $data[$item['menu_name']] = json_decode($item['value']);
        }
        return app('json')->success($data);
    }

    /**
     * 获取版本号信息
     * @return mixed
     */
    public function getVersion()
    {
        $version = get_crmeb_version();
        return app('json')->success([
            'version' => $version,
            'label' => 19
        ]);
    }
}
