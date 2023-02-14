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
declare (strict_types=1);

namespace app\services\other;

use app\services\BaseServices;
use app\dao\other\QrcodeDao;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\exceptions\AdminException;
use crmeb\services\app\MiniProgramService;
use crmeb\services\app\WechatService;
use Guzzle\Http\EntityBody;

/**
 *
 * Class QrcodeServices
 * @package app\services\other
 * @method getQrcode($id, $type)
 * @method scanQrcode($id, $type)
 */
class QrcodeServices extends BaseServices
{

    /**
     * QrcodeServices constructor.
     * @param QrcodeDao $dao
     */
    public function __construct(QrcodeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取临时二维码
     * @param $type
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTemporaryQrcode($type, $id)
    {
        $where['third_id'] = $id;
        $where['third_type'] = $type;
        $res = $this->dao->getOne($where);
        if (!$res) {
            $this->createTemporaryQrcode($id, $type);
            $res = $this->getTemporaryQrcode($type, $id);
        } else if (empty($res['expire_seconds']) || $res['expire_seconds'] < time()) {
            $this->createTemporaryQrcode($id, $type, $res['id']);
            $res = $this->getTemporaryQrcode($type, $id);
        }
        if (!$res['ticket']) throw new AdminException(400552);
        return $res;
    }

    /**
     * 临时二维码生成
     * @param $id
     * @param $type
     * @param string $qrcode_id
     */
    public function createTemporaryQrcode($id, $type, $qrcode_id = '')
    {
        $qrcode = WechatService::qrcodeService();
        $data = $qrcode->temporary($id, 30 * 24 * 3600)->toArray();
        $data['qrcode_url'] = $data['url'];
        $data['expire_seconds'] = $data['expire_seconds'] + time();
        $data['url'] = $qrcode->url($data['ticket']);
        $data['status'] = 1;
        $data['third_id'] = $id;
        $data['third_type'] = $type;
        if ($qrcode_id) {
            $this->dao->update($qrcode_id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
    }

    /**
     * 获取永久二维码
     * @param $type
     * @param $id
     * @return array|mixed|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getForeverQrcode($type, $id)
    {
        $where['third_id'] = $id;
        $where['third_type'] = $type;
        $res = $this->dao->getOne($where);
        if (!$res) {
            $this->createForeverQrcode($id, $type);
            $res = $this->getForeverQrcode($type, $id);
        }
        if (!$res['ticket']) throw new AdminException(400553);
        return $res;
    }

    /**
     * 永久二维码生成
     * @param $id
     * @param $type
     */
    public function createForeverQrcode($id, $type)
    {
        $qrcode = WechatService::qrcodeService();
        $data = $qrcode->forever($id)->toArray();
        $data['qrcode_url'] = $data['url'];
        $data['url'] = $qrcode->url($data['ticket']);
        $data['expire_seconds'] = 0;
        $data['status'] = 1;
        $data['third_id'] = $id;
        $data['third_type'] = $type;
        $data['add_time'] = time();
        $this->dao->save($data);
    }

    /**
     * 获取二维码完整路径，不存在则自动生成
     * @param string $name 路径名
     * @param string $link 需要生成二维码的跳转路径
     * @param int $type https 1 = http , 0 = https
     * @param bool $force 是否返回false
     * @return bool|mixed|string
     */
    public function getWechatQrcodePathAgent(string $name, string $link, bool $force = false)
    {
        /** @var SystemAttachmentServices $systemAttchment */
        $systemAttchment = app()->make(SystemAttachmentServices::class);
        try {
            $imageInfo = $systemAttchment->getInfo(['name'=>$name]);
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $codeUrl = PosterServices::setHttpType($siteUrl . $link, request()->isSsl() ? 0 : 1);//二维码链接
                $imageInfo = PosterServices::getQRCodePath($codeUrl, $name);
                if (is_string($imageInfo) && $force)
                    return false;
                if (is_array($imageInfo)) {
                    $systemAttchment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else {
                    $url = '';
                    $imageInfo = ['image_type' => 0];
                }
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1 && $url) $url = $siteUrl . $url;
            return $url;
        } catch (\Throwable $e) {
            if ($force)
                return false;
            else
                return '';
        }
    }
    /**
     * 获取二维码完整路径，不存在则自动生成
     * @param string $name
     * @param string $link
     * @param bool $force
     * @return bool|mixed|string
     */
    public function getWechatQrcodePath(string $name, string $link, bool $force = false, bool $isSaveAttach = true)
    {
        /** @var SystemAttachmentServices $systemAttachmentService */
        $systemAttachmentService = app()->make(SystemAttachmentServices::class);
        try {
            if (!$isSaveAttach) {
                $imageInfo = "";
            } else {
                $imageInfo = $systemAttachmentService->getOne(['name' => $name]);
            }
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $codeUrl = PosterServices::setHttpType($siteUrl . $link, request()->isSsl() ? 0 : 1);//二维码链接
                $imageInfo = PosterServices::getQRCodePath($codeUrl, $name);
                if (is_string($imageInfo) && $force)
                    return false;
                if (is_array($imageInfo)) {
                    if ($isSaveAttach) {
                        $systemAttachmentService->save([
                            'name' => $imageInfo['name'],
                            'att_dir' => $imageInfo['dir'],
                            'satt_dir' => $imageInfo['thumb_path'],
                            'att_size' => $imageInfo['size'],
                            'att_type' => $imageInfo['type'],
                            'image_type' => $imageInfo['image_type'],
                            'module_type' => 2,
                            'time' => time(),
                            'pid' => 1,
                            'type' => 1
                        ]);
                    }
                    $url = $imageInfo['dir'];
                } else {
                    $url = '';
                    $imageInfo = ['image_type' => 0];
                }
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1 && $url) $url = $siteUrl . $url;
            return $url;
        } catch (\Throwable $e) {
            if ($force)
                return false;
            else
                return '';
        }
    }

    /**
     * 获取小程序分享二维码
     * @param int $id
     * @param int $uid
     * @param int $type
     * @param array $param
     * @param bool $isSaveAttach
     * @return false|mixed|string
     */
    public function getRoutineQrcodePath(int $id, int $uid, int $type, array $param = [], bool $isSaveAttach = true)
    {
        /** @var SystemAttachmentServices $systemAttachmentService */
        $systemAttachmentService = app()->make(SystemAttachmentServices::class);
        $page = '';
        $namePath = '';
        $data = 'id=' . $id . '&pid=' . $uid;
        switch ($type) {
            case 0:
                $page = 'pages/goods_details/index';
                $namePath = $id . '_' . $uid . '_' . $param['is_promoter'] . '_product.jpg';
                break;
            case 1:
                $page = 'pages/activity/goods_combination_details/index';
                $namePath = 'combination_' . $id . '_' . $uid . '.jpg';
                break;
            case 2:
                $page = 'pages/activity/goods_seckill_details/index';
                $namePath = 'seckill_' . $id . '_' . $uid . '.jpg';
                break;
            case 3:
                $page = 'pages/annex/offline_pay/index';
                $namePath = 'routine_offline_scan.jpg';
                break;
            case 4:
                $page = 'pages/annex/vip_active/index';
                $namePath = 'routine_member_card.jpg';
                break;
            case 5:
                $page = 'pages/annex/vip_paid/index';
                $namePath = 'routine_pay_vip_code.jpg';
                break;
            case 6:
                $page = 'pages/annex/special/index';
                $namePath = $id . 'routine_index_code.jpg';
                break;
        }
        if (!$page || !$namePath) {
            return false;
        }
        try {
            if (!$isSaveAttach) {
                $imageInfo = "";
            } else {
                $imageInfo = $systemAttachmentService->getOne(['name' => $namePath]);
            }
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $res = MiniProgramService::appCodeUnlimitService($data, $page, 280);
                if (!$res) return false;
                if ($res->getSize() < 100) return 'unpublished';
                $uploadType = (int)sys_config('upload_type', 1);
                $upload = UploadService::init();
                $res = (string)EntityBody::factory($res);
                $res = $upload->to('routine/product')->validate()->setAuthThumb(false)->stream($res, $namePath);
                if ($res === false) {
                    return false;
                }
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = $uploadType;
                if ($imageInfo['image_type'] == 1) $remoteImage = PosterServices::remoteImage($siteUrl . $imageInfo['dir']);
                else $remoteImage = PosterServices::remoteImage($imageInfo['dir']);
                if (!$remoteImage['status']) return false;
                if ($isSaveAttach) {
                    $systemAttachmentService->save([
                        'name' => $imageInfo['name'],
                        'att_dir' => $imageInfo['dir'],
                        'satt_dir' => $imageInfo['thumb_path'],
                        'att_size' => $imageInfo['size'],
                        'att_type' => $imageInfo['type'],
                        'image_type' => $imageInfo['image_type'],
                        'module_type' => 2,
                        'time' => time(),
                        'pid' => 1,
                        'type' => 2
                    ]);
                }
                $url = $imageInfo['dir'];
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            return $url;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * TODO 添加二维码  存在直接获取
     * @param int $thirdId
     * @param string $thirdType
     * @param string $page
     * @param string $qrCodeLink
     * @return array|false|object|\PDOStatement|string|\think\Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function qrCodeForever($thirdId = 0, $thirdType = 'spread', $page = '', $qrCodeLink = '')
    {
        $qrcode = $this->dao->getOne(['third_id' => $thirdId, 'third_type' => $thirdType]);
        if ($qrcode) {
            return $qrcode;
        }
        return $this->setQrcodeForever($thirdId, $thirdType, $page, $qrCodeLink);
    }

    /**
     * 添加二维码记录
     * @param string $thirdType
     * @param int $thirdId
     * @return object
     */
    public function setQrcodeForever($thirdId = 0, $thirdType = 'spread', $page = '', $qrCodeLink = '')
    {
        $data['third_type'] = $thirdType;
        $data['third_id'] = $thirdId;
        $data['status'] = 1;
        $data['add_time'] = time();
        $data['page'] = $page;
        $data['url_time'] = '';
        $data['qrcode_url'] = $qrCodeLink;
        if (!$re = $this->dao->save($data)) {
            throw new AdminException(400237);
        }
        return $re;
    }

    /**
     * 修改二维码地址
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function setQrcodeFind($id = 0, $data = array())
    {
        if (!$id) return false;
        if (!$this->dao->get((int)$id)) {
            throw new AdminException(100026);
        }
        if (!$re = $this->dao->update($id, $data, 'id')) {
            throw new AdminException(100007);
        }
        return $re;
    }

    /**
     * 检测是否存在
     * @param int $thirdId
     * @param string $thirdType
     * @return bool
     */
    public function qrCodeExist($thirdId = 0, $thirdType = 'spread')
    {
        return !!$this->dao->getCount(['third_id' => $thirdId, 'third_type' => $thirdType]);
    }
}
