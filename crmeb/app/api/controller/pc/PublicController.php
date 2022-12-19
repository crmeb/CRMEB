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

namespace app\api\controller\pc;


use app\Request;
use app\services\pc\PublicServices;

class PublicController
{
    protected $services;

    public function __construct(PublicServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取城市数据
     * @param Request $request
     * @return mixed
     */
    public function getCity(Request $request)
    {
        list($pid) = $request->getMore([
            [['pid', 'd'], 0],
        ], true);
        return app('json')->success($this->services->getCity($pid));
    }

    /**
     * 获取公司信息
     * @return mixed
     */
    public function getCompanyInfo()
    {
        $data['contact_number'] = sys_config('contact_number');
        $data['company_address'] = sys_config('company_address');
        $data['copyright'] = sys_config('nncnL_crmeb_copyright', '');
        $data['record_No'] = sys_config('record_No');
        $data['site_name'] = sys_config('site_name');
        $data['site_keywords'] = sys_config('site_keywords');
        $data['site_description'] = sys_config('site_description');
        $logoUrl = sys_config('pc_logo');
        if (strstr($logoUrl, 'http') === false && $logoUrl) {
            $logoUrl = sys_config('site_url') . $logoUrl;
        }
        $logoUrl = str_replace('\\', '/', $logoUrl);
        $data['logoUrl'] = $logoUrl;
        return app('json')->success($data);
    }

    /**
     * 获取关注微信二维码
     * @return mixed
     */
    public function getWechatQrcode()
    {
        $data['wechat_qrcode'] = sys_config('wechat_qrcode');
        return app('json')->success($data);
    }
}
