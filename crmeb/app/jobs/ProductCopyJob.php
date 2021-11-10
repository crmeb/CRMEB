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

namespace app\jobs;


use app\services\product\product\CopyTaobaoServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class ProductCopyJob extends BaseJobs
{
    use QueueTrait;
    /**
     * @param $id
     * @return bool
     */
    public function doJob($id)
    {
        try {
            /** @var CopyTaobaoServices $copyTaobao */
            $copyTaobao = app()->make(CopyTaobaoServices::class);
            $copyTaobao->uploadDescriptionImage((int)$id);
        }catch (\Throwable $e){
            Log::error('下载商品详情图片失败，失败原因:' . $e->getMessage());
        }
        return true;
    }
}
