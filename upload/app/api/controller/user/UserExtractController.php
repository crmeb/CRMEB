<?php
namespace app\api\controller\user;

use app\models\store\StoreOrder;
use app\models\user\UserBill;
use app\models\user\UserExtract;
use app\Request;
use crmeb\services\SystemConfigService;
use crmeb\services\UtilService;

/**
 * 提现类
 * Class UserExtractController
 * @package app\api\controller\user
 */
class UserExtractController
{
    /**
     * 提现银行
     * @param Request $request
     * @return mixed
     */
    public function bank(Request $request)
    {
        $user = $request->user();
        $data['commissionCount'] = $user['brokerage_price'];//可提现佣金
        $extractBank = SystemConfigService::get('user_extract_bank') ?? []; //提现银行
        $extractBank = str_replace("\r\n","\n",$extractBank);//防止不兼容
        $data['extractBank'] = explode("\n",is_array($extractBank)  ? ( isset($extractBank[0]) ? $extractBank[0]: $extractBank): $extractBank);
        $data['minPrice'] = SystemConfigService::get('user_extract_min_price');//提现最低金额
        return app('json')->successful($data);
    }

    /**
     * 提现申请
     * @param Request $request
     * @return mixed
     */
    public function cash(Request $request)
    {
        $extractInfo = UtilService::postMore([
            ['alipay_code',''],
            ['extract_type',''],
            ['money',0],
            ['name',''],
            ['bankname',''],
            ['cardnum',''],
            ['weixin',''],
        ],$request);
        if(UserExtract::userExtract($request->user(),$extractInfo))
            return app('json')->successful('申请提现成功!');
        else
            return app('json')->fail(UserExtract::getErrorInfo('提现失败'));
    }
}