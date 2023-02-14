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

namespace app\services\activity\live;


use app\dao\activity\live\LiveAnchorDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use app\jobs\LiveJob;
use crmeb\services\FormBuilder as Form;
use crmeb\services\app\MiniProgramService;
use FormBuilder\components\Validate;
use think\facade\Route as Url;


/**
 * Class LiveGoodsServices
 * @package app\services\activity\live
 */
class LiveAnchorServices extends BaseServices
{
    /**
     * LiveAnchorServices constructor.
     * @param LiveAnchorDao $dao
     */
    public function __construct(LiveAnchorDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某个主播
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLiveAnchor(int $id)
    {
        return $this->dao->get($id);
    }

    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, '*', $page, $limit);
        $count = $this->dao->count($where);
        return compact('count', 'list');
    }

    /**
     * 添加修改标签表单
     * @param int $id
     * @return mixed
     */
    public function add(int $id)
    {
        $anchor = $this->getLiveAnchor($id);
        $field = array();
        if (!$anchor) {
            $title = '添加主播';
            $field[] = Form::input('name', '主播名称', '')->maxlength(20)->required('请填写名称');
            $field[] = Form::input('wechat', '主播微信号', '')->maxlength(32)->required('请填写微信号');
            $field[] = Form::input('phone', '主播手机号', '')->maxlength(20)->required('请填写手机号');
            $field[] = Form::frameImage('cover_img', '主播图像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'cover_img')), '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true])->appendValidate(Validate::str()->required('请选择图像'));
        } else {
            $title = '修改主播';
            $field[] = Form::hidden('id', $anchor->getData('id'));
            $field[] = Form::input('name', '主播名称', $anchor->getData('name'))->maxlength(20)->required('请填写名称');
            $field[] = Form::input('wechat', '主播微信号', $anchor->getData('wechat'))->maxlength(32)->required('请填写微信号');
            $field[] = Form::input('phone', '主播手机号', $anchor->getData('phone'))->maxlength(20)->required('请填写手机号');
            $field[] = Form::frameImage('cover_img', '主播图像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'cover_img')), $anchor->getData('cover_img'))->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true])->appendValidate(Validate::str()->required('请选择图像'));
        }
        return create_form($title, $field, $this->url('/live/anchor/save'), 'POST');
    }

    /**
     * 保存标签表单数据
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function save(int $id, array $data)
    {
        $liveAnchor = $this->dao->get(['wechat' => $data['wechat']]);
        if (!MiniProgramService::getRoleList(2, 0, 30, $data['wechat'])) {
            throw new AdminException(400426);
        }
        if ($id) {
            if ($liveAnchor && $id != $liveAnchor['id']) {
                throw new AdminException(400425);
            }
            if ($this->dao->update($id, $data)) {
                return true;
            } else {
                throw new AdminException(100007);
            }
        } else {
            unset($data['id']);
            if ($liveAnchor) {
                throw new AdminException(400425);
            }
            if ($this->dao->save($data)) {
                return true;
            } else {
                throw new AdminException(100022);
            }
        }
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delAnchor(int $id)
    {
        if ($anchor = $this->getLiveAnchor($id)) {
            if (!$this->dao->update($id, ['is_del' => 1])) {
                throw new AdminException(100008);
            }
            /** @var LiveRoomServices $liveRoom */
            $liveRoom = app()->make(LiveRoomServices::class);
            $room = $liveRoom->get(['anchor_wechat' => $anchor['wechat'], 'is_del' => 0], ['id']);
            if ($room) {
                $liveRoom->delete((int)$room->id);
            }
        }
        return true;
    }

    /**
     * 设置是否显示
     * @param int $id
     * @param $is_show
     * @return mixed
     */
    public function setShow(int $id, $is_show)
    {
        if (!$this->getLiveAnchor($id))
            throw new AdminException(100026);
        if ($this->dao->update($id, ['is_show' => $is_show])) {
            return true;
        } else {
            throw new AdminException(100015);
        }
    }

    public function syncAnchor($is_job = false)
    {
        $key = md5('Live_sync_status');
        $res = CacheService::get($key);
        if (!$res || $is_job) {
            $start = 0;
            $limit = 30;
            $data = $dataAll = [];
            $anchors = $this->dao->getColumn([], 'id,wechat', 'wechat');
            do {
                $wxAnchor = MiniProgramService::getRoleList(2, $start, $limit);
                foreach ($wxAnchor as $anchor) {
                    if (isset($anchors[$anchor['username']])) {
                        $this->dao->update($anchors[$anchor['username']]['id'], ['cover_img' => $anchor['headingimg'], 'name' => $anchor['nickname']]);
                    } else {
                        $data['name'] = $anchor['nickname'];
                        $data['wechat'] = $anchor['username'];
                        $data['cover_img'] = $anchor['headingimg'];
                        $data['add_time'] = $anchor['updateTimestamp'] ?? time();
                        $dataAll[] = $data;
                    }
                }
                $start++;
            } while (count($wxAnchor) >= $limit);
            if ($dataAll) {
                $this->dao->saveAll($dataAll);
            }
            //支付成功后发送消息
            if (!$is_job) LiveJob::dispatchSecs(120);
            CacheService::set($key, 1, 0);
        }
        return true;
    }
}
