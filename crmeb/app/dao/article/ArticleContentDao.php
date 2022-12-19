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

namespace app\dao\article;


use app\dao\BaseDao;
use app\model\article\ArticleContent;

/**
 * 文章详情
 * Class ArticleContentDao
 * @package app\dao\article
 */
class ArticleContentDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ArticleContent::class;
    }

    /**
     * 根据id删除数据
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function del(int $id)
    {
        return $this->getModel()->where('nid',$id)->delete();
    }
}
