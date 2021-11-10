<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\application\routine;

use app\services\other\QrcodeServices;
use app\services\other\TemplateMessageServices;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;
use think\facade\App;
use think\Request;
use think\facade\Route as Url;
use app\adminapi\controller\AuthController;
use crmeb\services\{FileService, FormBuilder as Form, MiniProgramService, template\Template, UploadService};
use think\facade\Cache;

/**
 * Class RoutineTemplate
 * @package app\adminapi\controller\v1\application\routine
 */
class RoutineTemplate extends AuthController
{
    protected $cacheTag = '_system_wechat';

    /**
     * 构造方法
     * WechatTemplate constructor.
     * @param App $app
     * @param TemplateMessageServices $services
     */
    public function __construct(App $app, TemplateMessageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['status', '']
        ]);
        $where['type'] = 0;
        $data = $this->services->getTemplateList($where);
        $industry = Cache::tag($this->cacheTag)->remember('_wechat_industry', function () {
            try {
                $cache = (new Template('wechat'))->getIndustry();
                if (!$cache) return [];
                Cache::tag($this->cacheTag, ['_wechat_industry']);
                return $cache->toArray();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }, 0) ?: [];
        !is_array($industry) && $industry = [];
        $industry['primary_industry'] = isset($industry['primary_industry']) ? $industry['primary_industry']['first_class'] . ' | ' . $industry['primary_industry']['second_class'] : '未选择';
        $industry['secondary_industry'] = isset($industry['secondary_industry']) ? $industry['secondary_industry']['first_class'] . ' | ' . $industry['secondary_industry']['second_class'] : '未选择';
        $lst = [
            'industry' => $industry,
            'count' => $data['count'],
            'list' => $data['list']
        ];
        return app('json')->success($lst);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('tempkey', '模板编号');
        $f[] = Form::input('tempid', '模板ID');
        $f[] = Form::input('name', '模板名');
        $f[] = Form::input('content', '回复内容')->type('textarea');
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return app('json')->success(create_form('添加模板消息', $f, Url::buildUrl('/app/routine'), 'POST'));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = $this->request->postMore([
            'tempkey',
            'tempid',
            'name',
            'content',
            ['status', 0]
        ]);
        if ($data['tempkey'] == '') return app('json')->fail('请输入模板编号');
        if ($data['tempkey'] != '' && $this->services->getOne(['tempkey' => $data['tempkey']]))
            return app('json')->fail('请输入模板编号已存在,请重新输入');
        if ($data['tempid'] == '') return app('json')->fail('请输入模板ID');
        if ($data['name'] == '') return app('json')->fail('请输入模板名');
        if ($data['content'] == '') return app('json')->fail('请输入回复内容');
        $data['add_time'] = time();
        $this->services->save($data);
        return app('json')->success('添加模板消息成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail('数据不存在');
        $product = $this->services->get($id);
        if (!$product) return app('json')->fail('数据不存在!');
        $f = array();
        $f[] = Form::input('tempkey', '模板编号', $product->getData('tempkey'))->disabled(1);
        $f[] = Form::input('name', '模板名', $product->getData('name'))->disabled(1);
        $f[] = Form::input('tempid', '模板ID', $product->getData('tempid'));
        $f[] = Form::radio('status', '状态', $product->getData('status'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return app('json')->success(create_form('编辑模板消息', $f, Url::buildUrl('/app/routine/' . $id), 'PUT'));
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = $this->request->postMore([
            'tempid',
            ['status', 0]
        ]);
        if ($data['tempid'] == '') return app('json')->fail('请输入模板ID');
        if (!$id) return app('json')->fail('数据不存在');
        $product = $this->services->get($id);
        if (!$product) return app('json')->fail('数据不存在!');
        $this->services->update($id, $data, 'id');
        return app('json')->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('数据不存在!');
        if (!$this->services->delete($id))
            return app('json')->fail('删除失败,请稍候再试!');
        else
            return app('json')->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return app('json')->fail('参数错误');
        $this->services->update($id, ['status' => $status], 'id');
        return app('json')->success($status == 0 ? '关闭成功' : '开启成功');
    }

    /**
     * 同步订阅消息
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function syncSubscribe()
    {
        if (!sys_config('routine_appId') || !sys_config('routine_appsecret')) {
            throw new AdminException('请先配置小程序appid、appSecret等参数');
        }
        $all = $this->services->getTemplateList(['status' => 1, 'type' => 0]);
        $errData = [];
        $errMessage = [
            '-1' => '系统繁忙，此时请稍候再试',
            '40001' => 'AppSecret错误或者AppSecret不属于这个小程序，请确认AppSecret 的正确性',
            '40002' => '请确保grant_type字段值为client_credential',
            '40013' => '不合法的AppID，请检查AppID的正确性，避免异常字符，注意大小写',
            '40125' => '小程序配置无效，请检查配置',
            '41002' => '缺少appid参数',
            '41004' => '缺少secret参数',
            '43104' => 'appid与openid不匹配',
            '45009' => '达到微信api每日限额上限',
            '200011' => '此账号已被封禁，无法操作',
            '200012' => '个人模版数已达上限，上限25个',
            '200014' => '请检查小程序所属类目',
        ];
        if ($all['list']) {
            $time = time();
            foreach ($all['list'] as $template) {
                if ($template['tempkey']) {
                    if (!isset($template['kid'])) {
                        return app('json')->fail('数据库模版表(template_message)缺少字段：kid');
                    }
                    if (isset($template['kid']) && $template['kid']) {
                        continue;
                    }
                    $works = [];
                    try {
                        $works = MiniProgramService::getSubscribeTemplateKeyWords($template['tempkey']);
                    } catch (\Throwable $e) {
                        $wechatErr = $e->getMessage();
                        if (is_string($wechatErr)) throw new AdminException($wechatErr);
                        if (in_array($wechatErr->getCode(), array_keys($errMessage))) {
                            throw new AdminException($errMessage[$wechatErr->getCode()]);
                        }
                        $errData[1] = '获取关键词列表失败：' . $wechatErr->getMessage();
                    }
                    $kid = [];
                    if ($works) {
                        $works = array_combine(array_column($works, 'name'), $works);
                        $content = is_array($template['content']) ? $template['content'] : explode("\n", $template['content']);
                        foreach ($content as $c) {
                            $name = explode('{{', $c)[0] ?? '';
                            if ($name && isset($works[$name])) {
                                $kid[] = $works[$name]['kid'];
                            }
                        }
                    }
                    if ($kid && isset($template['kid']) && !$template['kid']) {
                        $tempid = '';
                        try {
                            $tempid = MiniProgramService::addSubscribeTemplate($template['tempkey'], $kid, $template['name']);
                        } catch (\Throwable $e) {
                            $wechatErr = $e->getMessage();
                            if (is_string($wechatErr)) throw new AdminException($wechatErr);
                            if (in_array($wechatErr->getCode(), array_keys($errMessage))) {
                                throw new AdminException($errMessage[$wechatErr->getCode()]);
                            }
                            $errData[2] = '添加订阅消息模版失败：' . $wechatErr->getMessage();
                        }
                        if ($tempid != $template['tempid']) {
                            $this->services->update($template['id'], ['tempid' => $tempid, 'kid' => json_encode($kid), 'add_time' => $time], 'id');
                        }
                    }
                }
            }
        }
        $msg = $errData ? implode('\n', $errData) : '同步成功';
        return app('json')->success($msg);
    }

    /**
     * 下载小程序
     * @return mixed
     */
    public function downloadTemp()
    {
        [$name, $is_live] = $this->request->postMore([
            ['name', ''],
            ['is_live', 0]
        ], true);
        if (sys_config('routine_appId', '') == '') throw new AdminException('请先配置小程序appId');
        try {
            @unlink(public_path() . 'statics/download/routine.zip');
            //拷贝源文件
            /** @var FileService $fileService */
            $fileService = app(FileService::class);
            $fileService->copyDir(public_path() . 'statics/mp_view', public_path() . 'statics/download');
            //替换appid和名称
            $this->updateConfigJson(sys_config('routine_appId'), $name != '' ? $name : sys_config('routine_name'));
            //是否开启直播
            if ($is_live == 0) $this->updateAppJson();
            //替换url
            $this->updateUrl('https://' . $_SERVER['HTTP_HOST']);
            //压缩文件
            $fileService->addZip(public_path() . 'statics/download', public_path() . 'statics/download/routine.zip', public_path() . 'statics/download');
            $data['url'] = sys_config('site_url') . '/statics/download/routine.zip';
            return app('json')->success($data);
        } catch (\Throwable $throwable) {

        }
    }

    /**
     * 替换url
     * @param $url
     */
    public function updateUrl($url)
    {
        $fileUrl = app()->getRootPath() . "public/statics/download/common/vendor.js";
        $string = file_get_contents($fileUrl); //加载配置文件
        $string = str_replace('https://demo.crmeb.com', $url, $string); // 正则查找然后替换
        $newFileUrl = app()->getRootPath() . "public/statics/download/common/vendor.js";
        @file_put_contents($newFileUrl, $string); // 写入配置文件

    }

    /**
     * 判断是否开启直播(弃用)
     * @param int $iszhibo
     */
    public function updateAppJson()
    {
        $fileUrl = app()->getRootPath() . "public/statics/download/app.json";
        $string = file_get_contents($fileUrl); //加载配置文件
        $pats = '/,
      "plugins": \{
        "live-player-plugin": \{
          "version": "(.*?)",
          "provider": "(.*?)"
        }
      }/';
        $string = preg_replace($pats, '', $string); // 正则查找然后替换
        $newFileUrl = app()->getRootPath() . "public/statics/download/app.json";
        @file_put_contents($newFileUrl, $string); // 写入配置文件
    }

    /**
     * 替换appid
     * @param string $appid
     * @param string $projectanme
     */
    public function updateConfigJson($appId = '', $projectName = '')
    {
        $fileUrl = app()->getRootPath() . "public/statics/download/project.config.json";
        $string = file_get_contents($fileUrl); //加载配置文件
        // 替换appid
        $appIdOld = '/"appid"(.*?),/';
        $appIdNew = '"appid"' . ': ' . '"' . $appId . '",';
        $string = preg_replace($appIdOld, $appIdNew, $string); // 正则查找然后替换
        // 替换小程序名称
        $projectNameOld = '/"projectname"(.*?),/';
        $projectNameNew = '"projectname"' . ': ' . '"' . $projectName . '",';
        $string = preg_replace($projectNameOld, $projectNameNew, $string); // 正则查找然后替换
        $newFileUrl = app()->getRootPath() . "public/statics/download/project.config.json";
        @file_put_contents($newFileUrl, $string); // 写入配置文件
    }

    public function getDownloadInfo()
    {
        $data['routine_name'] = sys_config('routine_name', '');
        if (sys_config('routine_appId') == '') {
            $data['code'] = '';
        } else {
            $name = $data['routine_name'] . '.jpg';
            /** @var SystemAttachmentServices $systemAttachmentModel */
            $systemAttachmentModel = app()->make(SystemAttachmentServices::class);
            $imageInfo = $systemAttachmentModel->getInfo(['name' => $name]);
            if (!$imageInfo) {
                /** @var QrcodeServices $qrcode */
                $qrcode = app()->make(QrcodeServices::class);
                $resForever = $qrcode->qrCodeForever(0, 'code');
                if ($resForever) {
                    $resCode = MiniProgramService::qrcodeService()->appCodeUnlimit($resForever->id, '', 280);
                    $res = ['res' => $resCode, 'id' => $resForever->id];
                } else {
                    $res = false;
                }
                if (!$res) throw new ValidateException('二维码生成失败');
                $upload = UploadService::init(1);
                if ($upload->to('routine/code')->stream((string)$res['res'], $name) === false) {
                    return $upload->getError();
                }
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = 1;
                $systemAttachmentModel->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                $qrcode->update($res['id'], ['status' => 1, 'time' => time(), 'qrcode_url' => $imageInfo['dir']]);
                $data['code'] = sys_config('site_url') . $imageInfo['dir'];
            } else $data['code'] = sys_config('site_url') . $imageInfo['att_dir'];
        }
        $data['appId'] = sys_config('routine_appId');
        $data['help'] = 'https://help.crmeb.net/crmeb-v4/1863455';
        return app('json')->success($data);
    }
}
