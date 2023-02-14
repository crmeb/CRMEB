<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\application\routine;

use app\jobs\notice\SyncMessageJob;
use app\services\other\QrcodeServices;
use app\services\message\TemplateMessageServices;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;
use think\facade\App;
use app\adminapi\controller\AuthController;
use crmeb\services\FileService;
use app\services\other\UploadService;
use crmeb\services\app\MiniProgramService;

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
     * 同步订阅消息
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function syncSubscribe()
    {
        if (!sys_config('routine_appId') || !sys_config('routine_appsecret')) {
            throw new AdminException(400236);
        }
        $all = $this->services->getTemplateList(['status' => 1, 'type' => 0]);
        $list = MiniProgramService::getSubscribeTemplateList();
        foreach ($list->data as $v) {
            MiniProgramService::delSubscribeTemplate($v['priTmplId']);
        }
        if ($all['list']) {
            foreach ($all['list'] as $template) {
                SyncMessageJob::dispatch('SyncSubscribe', [$template]);
            }
        }
        return app('json')->success(100038);
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
        if (sys_config('routine_appId', '') == '') throw new AdminException(400236);
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
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
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

    /**
     * 获取小程序码
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
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
                    $resCode = MiniProgramService::appCodeUnlimitService($resForever->id, '', 280);
                    $res = ['res' => $resCode, 'id' => $resForever->id];
                } else {
                    $res = false;
                }
                if (!$res) throw new ValidateException(400237);
                $upload = UploadService::init(1);
                if ($upload->to('routine/code')->setAuthThumb(false)->stream((string)$res['res'], $name) === false) {
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
        $data['help'] = 'https://doc.crmeb.com/web/single/crmeb_v4/978';
        return app('json')->success($data);
    }
}
