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

namespace app\services\yihaotong;


use app\dao\sms\SmsRecordDao;
use app\services\BaseServices;
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
}
