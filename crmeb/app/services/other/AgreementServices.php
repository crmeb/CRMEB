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

namespace app\services\other;


use app\dao\other\AgreementDao;
use app\services\BaseServices;
use think\exception\ValidateException;

/**
 * Class AgreementServices
 * @package app\services\other
 */
class AgreementServices extends BaseServices
{

    public function __construct(AgreementDao $dao)
    {
        $this->dao = $dao;
    }

    /** 修改协议内容
     * @param array $where
     * @param $content
     * @return bool|\crmeb\basic\BaseModel
     */
    public function saveAgreement(array $data, $id = 0)
    {
        if (!$data) return false;
        if (!isset($data['type']) || !$data['type'] || $data['type'] == 0) throw new ValidateException('协议类型缺失');
        if (!isset($data['title']) || !$data['title']) throw new ValidateException('请填写协议名称');
        if (!isset($data['content']) || !$data['content']) throw new ValidateException('请填写协议内容');
        if (!$id){
            $getOne = $this->getAgreementBytype($data['type']);
            if ($getOne) throw new ValidateException('该类型协议已经存在');
        }
        return $this->dao->saveAgreement($data, $id);
    }

    /**获取会员协议
     * @param $type
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgreementBytype($type)
    {
        if (!$type) return [];
        $data = $this->dao->getOne(['type' => $type]);
        return $data ? $data->toArray() : [];

    }


}
