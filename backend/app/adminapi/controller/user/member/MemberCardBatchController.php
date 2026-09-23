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

namespace app\adminapi\controller\user\member;


use app\adminapi\controller\AuthController;
use app\services\other\AgreementServices;
use app\services\other\QrcodeServices;
use app\services\user\member\MemberCardBatchServices;
use think\facade\App;

/**
 * 会员卡批次控制器
 * 负责会员卡批次的后台管理操作，包括批次列表、创建批次、生成会员卡、兑换二维码等
 */
class MemberCardBatchController extends AuthController
{
    /**
     * @var MemberCardBatchServices
     */
    protected $services;

    /**
     * 初始化服务层实例
     * MemberCardBatchController constructor.
     * @param App $app
     * @param MemberCardBatchServices $memberCardBatchServices
     */
    public function __construct(App $app, MemberCardBatchServices $memberCardBatchServices)
    {
        parent::__construct($app);
        $this->services = $memberCardBatchServices;
    }

    /**
     * 获取会员卡批次列表
     * 查询所有会员卡批次数据，支持按批次名称搜索
     * @return mixed 返回分页后的批次列表数据
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['title', ''],
        ]);
        $data = $this->services->getList($where);
        return app('json')->success($data);
    }

    /**
     * 保存会员卡批次
     * 添加或编辑会员卡批次，创建批次时会自动生成对应数量的会员卡
     * @param int $id 批次ID，0表示新增
     * @return mixed 返回操作结果
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['use_day', 1],
            ['total_num', 1],
            ['status', 0],
            ['remark', '']
        ]);
        $this->services->save((int)$id, $data);
        return app('json')->success('卡片生成成功');
    }

    /**
     * 快速修改批次字段值
     * 通过字段名和值快速更新批次信息，如状态等
     * @param int $id 批次ID
     * @return mixed 返回操作结果
     */
    public function setValue($id)
    {

        $data = $this->request->getMore([
            ['value', ''],
            ['field', ''],
        ]);
        $this->services->setValue($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 生成兑换会员卡二维码
     * 生成H5和小程序端的会员卡兑换二维码，用户扫码可领取会员卡
     * @return mixed 返回二维码图片路径
     */
    public function memberScan()
    {
        //生成h5地址
        $weixinPage = "/pages/annex/vip_active/index";
        $weixinFileName = "wechat_member_card.png";
        /** @var QrcodeServices $QrcodeService */
        $QrcodeService = app()->make(QrcodeServices::class);
        $wechatQrcode = $QrcodeService->getWechatQrcodePath($weixinFileName,$weixinPage, false, false);
        //生成小程序地址
        $routineQrcode = $QrcodeService->getRoutineQrcodePath(4,6,4, [], false);
        return app('json')->success(['wechat_img' => $wechatQrcode, 'routine' => $routineQrcode ?: ""]);
    }

    /**
     * 保存会员协议
     * 添加或编辑会员协议内容
     * @param AgreementServices $agreementServices
     * @param int $id 协议ID，0表示新增
     * @return mixed 返回操作结果
     */
    public function saveMemberAgreement(AgreementServices $agreementServices, $id = 0)
    {
        $data = $this->request->postMore([
            ['type', 1],
            ['title', ""],
            ['content', ''],
            ['status', ''],
        ]);

        return app('json')->success($agreementServices->saveAgreement($data, $id));
    }

    /**
     * 获取会员协议
     * 查询会员协议详情信息
     * @param AgreementServices $agreementServices
     * @return mixed 返回会员协议数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgreement(AgreementServices $agreementServices)
    {
        $list = $agreementServices->getAgreementBytype(1);
        return app('json')->success($list);
    }

}