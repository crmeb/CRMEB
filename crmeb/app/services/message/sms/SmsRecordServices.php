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

namespace app\services\message\sms;


use app\dao\sms\SmsRecordDao;
use app\services\BaseServices;
use app\services\serve\ServeServices;
use crmeb\services\sms\Sms;

/**
 * 短信发送记录
 * Class SmsRecordServices
 * @package app\services\message\sms
 * @method save(array $data) 保存数据
 * @method getColumn(array $where, ?string $field, ?string $key = '')
 * @method update(int $id, array $data, ?string $field = '')
 * @method getCodeNull
 */
class SmsRecordServices extends BaseServices
{
    /**
     * 构造方法
     * SmsRecordServices constructor.
     * @param SmsRecordDao $dao
     */
    public function __construct(SmsRecordDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取短信发送列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getRecordList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('data', 'count');
    }

    /**
     * 修改短信发送记录短信状态
     */
    public function modifyResultCode()
    {
        $recordIds = $this->dao->getCodeNull();
        if (count($recordIds)) {
            /** @var ServeServices $smsHandle */
            $smsHandle = app()->make(ServeServices::class);
            $codeLists = $smsHandle->sms()->getStatus($recordIds);
            foreach ($codeLists as $item) {
                if (isset($item['id']) && isset($item['resultcode'])) {
                    if ($item['resultcode'] == '' || $item['resultcode'] == null) $item['resultcode'] = 134;
                    $this->dao->update($item['id'], ['resultcode' => $item['resultcode']], 'record_id');
                }
            }
            return true;
        }
        return true;
    }
}
