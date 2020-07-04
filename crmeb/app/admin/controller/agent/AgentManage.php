<?php

namespace app\admin\controller\agent;

use app\admin\controller\AuthController;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemAttachment;
use app\admin\model\user\User;
use app\models\user\UserBill;
use app\admin\model\wechat\WechatUser as UserModel;
use app\models\routine\{
    RoutineCode, RoutineQrcode
};
use crmeb\services\{
    JsonService, QrcodeService, UtilService as Util
};
use crmeb\services\upload\Upload;

/**
 * 分销商管理控制器
 * Class AgentManage
 * @package app\admin\controller\agent
 */
class AgentManage extends AuthController
{

    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign('year', get_month());
        $this->assign('store_brokerage_statu', sys_config('store_brokerage_statu'));
        return $this->fetch();
    }

    /**
     * 分销员列表
     */
    public function get_spread_list()
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['sex', ''],
            ['excel', ''],
            ['subscribe', ''],
            ['order', ''],
            ['page', 1],
            ['limit', 20],
            ['user_type', ''],
            ['data', '']
        ]);
        return JsonService::successlayui(UserModel::agentSystemPage($where));
    }

    /**
     * 分销员列表头部数据统计
     */
    public function get_badge()
    {
        $where = Util::postMore([
            ['data', ''],
            ['nickname', ''],
            ['excel', ''],
        ]);
        return JsonService::successful(UserModel::getSpreadBadge($where));
    }

    /**
     * 一级推荐人页面
     * @return mixed
     */
    public function stair($uid = '')
    {
        if ($uid == '') return $this->failed('参数错误');
        $this->assign('uid', $uid ?: 0);
        $this->assign('year', get_month());
        return $this->fetch();
    }

    /*
    *  统计推广订单页面
    * @param int $uid
    * */
    public function stair_order($uid = 0)
    {
        if ($uid == '') return $this->failed('参数错误');
        $this->assign('uid', $uid ?: 0);
        $this->assign('year', get_month());
        return $this->fetch();
    }

    /**
     * 统计推广订单列表
     */
    public function get_stair_order_list()
    {
        $where = Util::getMore([
            ['uid', $this->request->param('uid', 0)],
            ['data', ''],
            ['order_id', ''],
            ['type', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return JsonService::successlayui(UserModel::getStairOrderList($where));
    }

    /**
     * 统计推广订单列表头部统计数据
     */
    public function get_stair_order_badge()
    {
        $where = Util::getMore([
            ['uid', ''],
            ['data', ''],
            ['order_id', ''],
            ['type', ''],
        ]);
        return JsonService::successful(UserModel::getStairOrderBadge($where));
    }

    public function get_stair_list()
    {
        $where = Util::getMore([
            ['uid', $this->request->param('uid', 0)],
            ['data', ''],
            ['nickname', ''],
            ['type', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return JsonService::successlayui(UserModel::getStairList($where));
    }

    public function get_stair_badge()
    {
        $where = Util::getMore([
            ['uid', ''],
            ['data', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        return JsonService::successful(UserModel::getSairBadge($where));
    }

    /**
     * 二级推荐人页面
     * @return mixed
     */
    public function stair_two($uid = '')
    {
        if ($uid == '') return $this->failed('参数错误');
        $spread_uid = User::where('spread_uid', $uid)->column('uid', 'uid');
        if (count($spread_uid))
            $spread_uid_two = User::where('spread_uid', 'in', $spread_uid)->column('uid', 'uid');
        else
            $spread_uid_two = [0];
        $list = User::alias('u')
            ->where('u.uid', 'in', $spread_uid_two)
            ->field('u.avatar,u.nickname,u.now_money,u.spread_time,u.uid')
            ->where('u.status', 1)
            ->order('u.add_time DESC')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) $list[$key]['orderCount'] = StoreOrder::getOrderCount($value['uid']) ?: 0;
        $this->assign('list', $list);
        return $this->fetch('stair');
    }

    /*
     * 批量清除推广权限
     * */
    public function delete_promoter()
    {
        list($uids) = Util::postMore([
            ['uids', []]
        ], $this->request, true);
        if (!count($uids)) return JsonService::fail('请选择需要解除推广权限的用户！');
        User::beginTrans();
        try {
            if (User::where('uid', 'in', $uids)->update(['is_promoter' => 0])) {
                User::commitTrans();
                return JsonService::successful('解除成功');
            } else {
                User::rollbackTrans();
                return JsonService::fail('解除失败');
            }
        } catch (\PDOException $e) {
            User::rollbackTrans();
            return JsonService::fail('数据库操作错误', ['line' => $e->getLine(), 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            User::rollbackTrans();
            return JsonService::fail('系统错误', ['line' => $e->getLine(), 'message' => $e->getMessage()]);
        }

    }

    /*
     * 查看公众号推广二维码
     * @param int $uid
     * @return json
     * */
    public function look_code($uid = '', $action = '')
    {
        if (!$uid || !$action) return JsonService::fail('缺少参数');
        try {
            if (method_exists($this, $action)) {
                $res = $this->$action($uid);
                if ($res)
                    return JsonService::successful($res);
                else
                    return JsonService::fail(isset($res['msg']) ? $res['msg'] : '获取失败，请稍后再试！');
            } else
                return JsonService::fail('暂无此方法');
        } catch (\Exception $e) {
            return JsonService::fail('获取推广二维码失败，请检查您的微信配置', ['line' => $e->getLine(), 'messag' => $e->getMessage()]);
        }
    }

    /*
     * 获取小程序二维码
     * */
    public function routine_code($uid)
    {
        $userInfo = User::getUserInfos($uid);
        $name = $userInfo['uid'] . '_' . $userInfo['is_promoter'] . '_user.jpg';
        $imageInfo = SystemAttachment::getInfo($name, 'name');
        if (!$imageInfo) {
            $res = RoutineCode::getShareCode($uid, 'spread', '', '');
            if (!$res) throw new \think\Exception('二维码生成失败');
            $upload_type = sys_config('upload_type', 1);
            $upload = new Upload((int)$upload_type, [
                'accessKey' => sys_config('accessKey'),
                'secretKey' => sys_config('secretKey'),
                'uploadUrl' => sys_config('uploadUrl'),
                'storageName' => sys_config('storage_name'),
                'storageRegion' => sys_config('storage_region'),
            ]);
            $info = $upload->to('routine/spread/code')->validate()->stream($res['res'], $name);
            if ($info === false) {
                return $upload->getError();
            }
            $imageInfo = $upload->getUploadInfo();
            $imageInfo['image_type'] = $upload_type;
            SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
            RoutineQrcode::setRoutineQrcodeFind($res['id'], ['status' => 1, 'time' => time(), 'qrcode_url' => $imageInfo['dir']]);
            $urlCode = $imageInfo['dir'];
        } else $urlCode = $imageInfo['att_dir'];
        return ['code_src' => $urlCode];
    }

    /*
     * 获取公众号二维码
     * */
    public function wechant_code($uid)
    {
        $qr_code = QrcodeService::getForeverQrcode('spread', $uid);
        if (isset($qr_code['url']))
            return ['code_src' => $qr_code['url']];
        else
            throw new \think\Exception('获取失败，请稍后再试！');
    }

    /**
     * TODO 查看小程序推广二维码
     * @param string $uid
     */
    public function look_xcx_code($uid = '')
    {
        if (!strlen(trim($uid))) return JsonService::fail('缺少参数');
        try {
            $userInfo = User::getUserInfos($uid);
            $name = $userInfo['uid'] . '_' . $userInfo['is_promoter'] . '_user.jpg';
            $imageInfo = SystemAttachment::getInfo($name, 'name');
            if (!$imageInfo) {
                $res = RoutineCode::getShareCode($uid, 'spread', '', '');
                if (!$res) return JsonService::fail('二维码生成失败');
                $upload_type = sys_config('upload_type', 1);
                $upload = new Upload((int)$upload_type, [
                    'accessKey' => sys_config('accessKey'),
                    'secretKey' => sys_config('secretKey'),
                    'uploadUrl' => sys_config('uploadUrl'),
                    'storageName' => sys_config('storage_name'),
                    'storageRegion' => sys_config('storage_region'),
                ]);
                $info = $upload->to('routine/spread/code')->validate()->stream($res['res'], $name);
                if ($info === false) {
                    return $upload->getError();
                }
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = $upload_type;
                SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                RoutineQrcode::setRoutineQrcodeFind($res['id'], ['status' => 1, 'time' => time(), 'qrcode_url' => $imageInfo['dir']]);
                $urlCode = $imageInfo['dir'];
            } else $urlCode = $imageInfo['att_dir'];
            return JsonService::successful(['code_src' => $urlCode]);
        } catch (\Exception $e) {
            return JsonService::fail('查看推广二维码失败！', ['line' => $e->getLine(), 'meassge' => $e->getMessage()]);
        }
    }

    /*
     * 解除单个用户的推广权限
     * @param int $uid
     * */
    public function delete_spread($uid = 0)
    {
        if (!$uid) return JsonService::fail('缺少参数');
        if (User::where('uid', $uid)->update(['is_promoter' => 0]))
            return JsonService::successful('解除成功');
        else
            return JsonService::fail('解除失败');
    }

    /*
     * 清除推广人
     * */
    public function empty_spread($uid = 0)
    {
        if (!$uid) return JsonService::fail('缺少参数');
        $res = User::where('uid', $uid)->update(['spread_uid' => 0]);
        if ($res)
            return JsonService::successful('清除成功');
        else
            return JsonService::fail('清除失败');
    }

    /**
     * 个人资金详情页面
     * @return mixed
     */
    public function now_money($uid = '')
    {
        if ($uid == '') return $this->failed('参数错误');
        $list = UserBill::where('uid', $uid)->where('category', 'now_money')
            ->field('mark,pm,number,add_time')
            ->where('status', 1)->order('add_time DESC')->select()->toArray();
        foreach ($list as &$v) {
            $v['add_time'] = $v['add_time'] ? date('Y-m-d H:i:s', $v['add_time']) : '';
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

}
