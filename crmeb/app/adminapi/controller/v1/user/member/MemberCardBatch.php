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

namespace app\adminapi\controller\v1\user\member;


use app\adminapi\controller\AuthController;
use app\services\other\AgreementServices;
use app\services\other\QrcodeServices;
use app\services\user\member\MemberCardBatchServices;
use think\facade\App;

/**
 * Class MemberCardBatch
 * @package app\adminapi\controller\v1\user\member
 */
class MemberCardBatch extends AuthController
{
    /**
     * MemberCardBatch constructor.
     * @param App $app
     * @param MemberCardBatchServices $memberCardBatchServices
     */
    public function __construct(App $app, MemberCardBatchServices $memberCardBatchServices)
    {
        parent::__construct($app);
        $this->services = $memberCardBatchServices;
    }

    /**
     * 会员卡批次资源列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['title', ''],
//            ['page', 1],
//            ['limit', 20],
        ]);
        $data = $this->services->getList($where);
        return app('json')->success($data);
    }

    /** 保存卡片资源
     * @param $id
     * @return mixed
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
        return app('json')->success(400313);
    }

    /**
     * 列表操作
     * @param $id
     * @return mixed
     */
    public function set_value($id)
    {

        $data = $this->request->getMore([
            ['value', ''],
            ['field', ''],
        ]);
        $this->services->setValue($id, $data);
        return app('json')->success(100001);
    }

    /**会员二维码，兑换卡
     * @return mixed
     */
    public function member_scan()
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

    /** 添加会员协议
     * @param int $id
     * @param AgreementServices $agreementServices
     * @return mixed
     */
    public function save_member_agreement($id = 0, AgreementServices $agreementServices)
    {
        $data = $this->request->postMore([
            ['type', 1],
            ['title', ""],
            ['content', ''],
            ['status', ''],
        ]);

        return app('json')->success($agreementServices->saveAgreement($data, $id));
    }

    /**获取会员协议
     * @param AgreementServices $agreementServices
     * @return mixed
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
