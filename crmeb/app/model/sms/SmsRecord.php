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

namespace app\model\sms;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 *  短信记录Model
 * Class SmsRecord
 * @package app\model\sms
 */
class SmsRecord extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'sms_record';

    /**
     * 短信状态
     * @var array
     */
    protected $resultcode = ['100' => '成功', '130' => '失败', '131' => '空号', '132' => '停机', '133' => '关机', '134' => '无状态'];

    /**
     * 时间获取器
     * @param $value
     * @return false|string
     */
    protected function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 状态码获取器
     * @param $value
     * @return mixed|string
     */
    protected function getResultcodeAttr($value)
    {
        return $this->resultcode[$value] ?? '无状态';
    }

    /**
     * 电话号码搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchPhoneAttr($query, $value)
    {
        $query->where('phone', $value);
    }

    /**
     * 短信状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchResultcodeAttr($query, $value)
    {
        $query->where('resultcode', $value);
    }

    /**
     * uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value) {
            $query->where('uid', $value);
        }
    }

    /**
     * ip
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchAddIpAttr($query, $value)
    {
        $query->where('add_ip', $value);
    }

    /**
     * resultcode
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('resultcode', $value)->when(in_array('134', $value), function ($query) {
                    $query->whereOr('resultcode', NULL);
                });
            } else {
                $query->where('resultcode', $value)->when($value == 134, function ($query) {
                    $query->whereOr('resultcode', NULL);
                });
            }
        }
    }
}
