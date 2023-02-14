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
namespace app\api\controller\v1\store;

use app\services\product\product\StoreCategoryServices;
use think\Request;

/**
 * Class CategoryController
 * @package app\api\controller\v1\store
 */
class CategoryController
{
    protected $services;

    public function __construct(StoreCategoryServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取分类列表
     * @return mixed
     */
    public function category(Request $request)
    {
        $where = $request->getMore([
            ['pid', 0],
        ]);
        $category = $this->services->getCategory($where);
        return app('json')->success($category);
    }

    /**
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/11
     * @return mixed
     */
    public function getCategoryVersion()
    {
        return app('json')->success(['version' => $this->services->getCategoryVersion()]);
    }
}
