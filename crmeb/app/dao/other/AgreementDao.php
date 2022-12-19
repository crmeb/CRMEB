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

namespace app\dao\other;


use app\dao\BaseDao;
use app\model\other\Agreement;

/**
 * Class AgreementDao
 * @package app\dao\other
 */
class AgreementDao extends BaseDao
{

    /**
     * @return string
     */
    public function setModel(): string
    {
        return Agreement::class;
    }

    /**修改协议内容
     * @param array $where
     * @param $agreement
     * @return bool|\crmeb\basic\BaseModel
     */
    public function saveAgreement(array $agreement, $id = 0)
    {
        if (!$agreement) return false;
        $agreement['add_time'] = time();
        if($id){
            return $this->getModel()->update($agreement,['id' => $id]);
        }
        return $this->getModel()->save($agreement);
    }

}
