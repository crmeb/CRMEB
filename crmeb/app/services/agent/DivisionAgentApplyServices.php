<?php


namespace app\services\agent;


use app\dao\agent\DivisionAgentApplyDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\app\MiniProgramService;
use crmeb\services\FormBuilder as Form;
use app\services\other\UploadService;
use think\facade\Config;
use think\facade\Log;
use think\facade\Route;

class DivisionAgentApplyServices extends BaseServices
{
    /**
     * DivisionAgentApplyServices constructor.
     * @param DivisionAgentApplyDao $dao
     */
    public function __construct(DivisionAgentApplyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 申请详情
     * @param $uid
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyInfo($uid)
    {
        $data = $this->dao->get(['uid' => $uid, 'is_del' => 0]);
        if (!$data) return ['status' => -1];
        $data = $data->toArray();
        $data['images'] = json_decode($data['images'], true);
        $data['add_time'] = date('Y-m-d H:i:s', $data['add_time']);
        return $data;
    }

    /**
     * 代理商申请
     * @param $data
     * @param int $id
     * @return bool
     */
    public function applyAgent($data, $id = 0)
    {
        $data['images'] = json_encode($data['images']);
        $data['add_time'] = time();
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $divisionId = $userServices->value(['division_invite' => $data['division_invite']], 'division_id');
        if (!$divisionId) throw new ApiException(410073);
        $data['division_id'] = $divisionId;
        if ($id) {
            $data['status'] = 0;
            $res = $this->dao->update(['id' => $id], $data);
        } else {
            $this->dao->update(['uid' => $data['uid']], ['is_del' => 1]);
            $res = $this->dao->save($data);
        }
        if (!$res) throw new ApiException(100018);
        return true;
    }


    /**
     * 管理端代理商申请列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function AdminApplyList($where)
    {
        $where['is_del'] = 0;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $divisionUids = array_column($list, 'division_id');
        $divisionArr = app()->make(UserServices::class)->getColumn([['division_id', 'in', $divisionUids]], 'division_name', 'uid');
        foreach ($list as &$item) {
            $item['images'] = json_decode($item['images'], true);
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['division_name'] = $divisionArr[$item['division_id']] ?? '';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 删除代理商审核
     * @param $id
     * @return bool
     */
    public function delApply($id)
    {
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException(100008);
        return true;
    }

    /**
     * 审核表单
     * @param $id
     * @param $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function examineApply($id, $type)
    {
        if (!$id) throw new AdminException(100100);
        $field = [];
        $field[] = Form::hidden('type', $type);
        $field[] = Form::hidden('id', $id);
        if ($type) {
            $field[] = Form::number('division_percent', '佣金比例', '')->placeholder('代理商佣金比例1-100')->info('填写1-100，如填写50代表返佣50%,但是不能高于上级事业部的比例')->style(['width' => '173px'])->min(0)->max(100)->required();
            $field[] = Form::date('division_end_time', '到期时间', '')->placeholder('代理商代理到期时间');
            $field[] = Form::radio('division_status', '代理状态', 1)->options([['label' => '开通', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
            $title = '同意申请';
        } else {
            $field[] = Form::textarea('refusal_reason', '拒绝原因', '')->rows(5);
            $title = '拒绝申请';
        }
        return create_form($title, $field, Route::buildUrl('/agent/division/apply_agent/save'), 'POST');
    }

    /**
     * 审核代理商
     * @param $data
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyAgentSave($data)
    {
        $applyInfo = $this->dao->get($data['id']);
        return $this->transaction(function () use ($applyInfo, $data) {
            if ($data['type'] == 1) {
                $agentData = [
                    'division_id' => $applyInfo['division_id'],
                    'agent_id' => $applyInfo['uid'],
                    'division_type' => 2,
                    'division_status' => $data['division_status'],
                    'is_agent' => 1,
                    'is_staff' => 0,
                    'division_percent' => $data['division_percent'],
                    'division_change_time' => time(),
                    'division_end_time' => strtotime($data['division_end_time']),
                    'spread_uid' => $applyInfo['division_id'],
                    'spread_time' => time()
                ];
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $division_info = $userServices->getUserInfo($applyInfo['division_id'], 'division_end_time,division_percent');
                if ($applyInfo['division_id'] != 0) {
                    if ($agentData['division_percent'] > $division_info['division_percent']) throw new AdminException(400448);
                    if ($agentData['division_end_time'] > $division_info['division_end_time']) throw new AdminException(400449);
                }
                $applyInfo->status = 1;
                $res = $applyInfo->save();
                $res = $res && $userServices->update($applyInfo['uid'], $agentData);
            } else {
                $applyInfo->status = 2;
                $applyInfo->refusal_reason = $data['refusal_reason'];
                $res = $applyInfo->save();
            }
            if (!$res) throw new AdminException(100005);
            return true;
        });
    }

    /**
     * 获取员工列表
     * @param $userInfo
     * @param $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStaffList($isRoutine, $where, $field = '*')
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        [$page, $limit] = $this->getPageValue();
        $count = $userService->getCount(['agent_id' => $where['agent_id'], 'is_staff' => 1, 'is_del' => 0]);
        $list = $userService->getList(['agent_id' => $where['agent_id'], 'is_staff' => 1, 'is_del' => 0], $field, $page, $limit);
        foreach ($list as &$item) {
            $item['division_change_time'] = date('Y-m-d', $item['division_change_time']);
            $item['division_end_time'] = date('Y-m-d', $item['division_end_time']);
            $item['childCount'] = $userService->getCount(['agent_id' => $where['agent_id'], 'spread_uid' => $item['uid']]);
            $item['orderCount'] = $item['pay_count'];
            $item['numberCount'] = $orderService->sum(['uid' => $item['uid']], 'pay_price');
        }
        $codeUrl = '';
        if ($isRoutine) {
            /** @var SystemAttachmentServices $systemAttachment */
            $systemAttachment = app()->make(SystemAttachmentServices::class);
            $name = 'routine_agent_' . $where['agent_id'] . '.jpg';
            $imageInfo = $systemAttachment->getInfo(['name' => $name]);
            //检测远程文件是否存在
            if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
                $imageInfo = null;
                $systemAttachment->delete(['name' => $name]);
            }
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                /** @var QrcodeServices $qrCode */
                $qrCode = app()->make(QrcodeServices::class);
                $resForever = $qrCode->qrCodeForever($where['agent_id'], 'agent', '', '');
                $resCode = MiniProgramService::appCodeUnlimitService($resForever->id, '', 280);
                if ($resCode) {
                    $res = ['res' => $resCode, 'id' => $resForever->id];
                } else {
                    $res = false;
                }
                if (!$res) return compact('list', 'count', 'codeUrl');
                $uploadType = (int)sys_config('upload_type', 1);
                $upload = UploadService::init();
                $uploadRes = $upload->to('routine/agent/code')->validate()->setAuthThumb(false)->stream($res['res'], $name);
                if ($uploadRes === false) return compact('list', 'count', 'codeUrl');
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = $uploadType;
                $systemAttachment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                $qrCode->setQrcodeFind($res['id'], ['status' => 1, 'url_time' => time(), 'qrcode_url' => $imageInfo['dir']]);
                $codeUrl = $imageInfo['dir'];
            } else $codeUrl = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1) $codeUrl = $siteUrl . $codeUrl;
        }
        return compact('list', 'count', 'codeUrl');

        //代理商邀请员工二维码为公众号渠道码，需要配置公众号并开启关注自动生成用户使用
//        try {
//            /** @var SystemAttachmentServices $systemAttachment */
//            $systemAttachment = app()->make(SystemAttachmentServices::class);
//            $name = 'agent_' . $where['agent_id'] . '.jpg';
//            $siteUrl = sys_config('site_url', '');
//            $imageInfo = $systemAttachment->getInfo(['name' => $name]);
//            if (!$imageInfo) {
//                /** @var QrcodeServices $qrCode */
//                $qrCode = app()->make(QrcodeServices::class);
//                //公众号
//                $resCode = $qrCode->getForeverQrcode('agent', $where['agent_id']);
//                if ($resCode) {
//                    $res = ['res' => $resCode, 'id' => $resCode['id']];
//                } else {
//                    $res = false;
//                }
//                if (!$res) throw new ApiException(410167);
//                $imageInfo = $this->downloadImage($resCode['url'], $name);
//                $systemAttachment->attachmentAdd($name, $imageInfo['size'], $imageInfo['type'], $imageInfo['att_dir'], $imageInfo['att_dir'], 1, $imageInfo['image_type'], time(), 2);
//            }
//            $codeUrl = strpos($imageInfo['att_dir'], 'http') === false ? $siteUrl . $imageInfo['att_dir'] : $imageInfo['att_dir'];
//        } catch (\Exception $e) {
//            Log::error('邀请员工二维码生成失败，失败原因' . $e->getMessage());
//        }
    }

    /**
     * 下载图片
     * @param string $url
     * @param string $name
     * @param int $type
     * @param int $timeout
     * @param int $w
     * @param int $h
     * @return string
     */
    public function downloadImage($url = '', $name = '', $type = 0, $timeout = 30, $w = 0, $h = 0)
    {
        if (!strlen(trim($url))) return '';
        if (!strlen(trim($name))) {
            //TODO 获取要下载的文件名称
            $downloadImageInfo = $this->getImageExtname($url);
            $ext = $downloadImageInfo['ext_name'];
            $name = $downloadImageInfo['file_name'];
            if (!strlen(trim($name))) return '';
        } else {
            $ext = $this->getImageExtname($name)['ext_name'];
        }
        if (!in_array($ext, Config::get('upload.fileExt'))) {
            throw new AdminException(400558);
        }
        //TODO 获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //TODO 跳过证书检查
            if (stripos($url, "https://") !== FALSE) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  //TODO 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('user-agent:' . $_SERVER['HTTP_USER_AGENT']));
            if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//TODO 是否采集301、302之后的页面
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            try {
                ob_start();
                readfile($url);
                $content = ob_get_contents();
                ob_end_clean();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        $size = strlen(trim($content));
        if (!$content || $size <= 2) return '图片流获取失败';
        $upload_type = sys_config('upload_type', 1);
        $upload = UploadService::init();
        if ($upload->to('attach/spread/agent')->setAuthThumb(false)->stream($content, $name) === false) {
            return $upload->getError();
        }
        $imageInfo = $upload->getUploadInfo();
        $data['att_dir'] = $imageInfo['dir'];
        $data['name'] = $imageInfo['name'];
        $data['size'] = $imageInfo['size'];
        $data['type'] = $imageInfo['type'];
        $data['image_type'] = $upload_type;
        $data['is_exists'] = false;
        return $data;
    }

    /**
     * 获取即将要下载的图片扩展名
     * @param string $url
     * @param string $ex
     * @return array|string[]
     */
    public function getImageExtname($url = '', $ex = 'jpg')
    {
        $_empty = ['file_name' => '', 'ext_name' => $ex];
        if (!$url) return $_empty;
        if (strpos($url, '?')) {
            $_tarr = explode('?', $url);
            $url = trim($_tarr[0]);
        }
        $arr = explode('.', $url);
        if (!is_array($arr) || count($arr) <= 1) return $_empty;
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = !$ext_name ? $ex : $ext_name;
        return ['file_name' => md5($url) . '.' . $ext_name, 'ext_name' => $ext_name];
    }
}
