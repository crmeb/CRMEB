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

namespace app\api\controller\v2\user;


use app\services\user\UserSearchServices;
use think\Request;

/**
 * Class UserInvoiceController
 * @package app\api\controller\v2\user
 */
class UserSearchController
{
    /**
     * @var UserInvoiceServices
     */
    protected $services;

    /**
     * UserSearchController constructor.
     * @param UserSearchServices $services
     */
    public function __construct(UserSearchServices $services)
    {
        $this->services = $services;
    }

    public function getUserSeachList(Request $request)
    {
        return app('json')->successful($this->services->getUserList((int)$request->uid()));
    }

    public function cleanUserSearch(Request $request)
    {
        $uid = (int)$request->uid();
        $this->services->update(['uid' => $uid], ['is_del' => 1]);
        return app('json')->successful('删除成功');
    }
}
