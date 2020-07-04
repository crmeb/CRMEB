<?php

namespace app\models\routine;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 小程序二维码Model
 * Class RoutineQrcode
 * @package app\models\routine
 */
class RoutineQrcode extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'routine_qrcode';

    use ModelTrait;

    /**
     * TODO 添加二维码  存在直接获取
     * @param int $thirdId
     * @param string $thirdType
     * @param string $page
     * @param string $qrCodeLink
     * @return array|false|object|\PDOStatement|string|\think\Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function routineQrCodeForever($thirdId = 0, $thirdType = 'spread', $page = '', $qrCodeLink = '')
    {
        $count = self::where('third_id', $thirdId)->where('third_type', $thirdType)->count();
        if ($count) return self::where('third_id', $thirdId)->where('third_type', $thirdType)->field('id')->find();
        return self::setRoutineQrcodeForever($thirdId, $thirdType, $page, $qrCodeLink);
    }

    /**
     * 添加二维码记录
     * @param string $thirdType
     * @param int $thirdId
     * @return object
     */
    public static function setRoutineQrcodeForever($thirdId = 0, $thirdType = 'spread', $page = '', $qrCodeLink = '')
    {
        $data['third_type'] = $thirdType;
        $data['third_id'] = $thirdId;
        $data['status'] = 1;
        $data['add_time'] = time();
        $data['page'] = $page;
        $data['url_time'] = '';
        $data['qrcode_url'] = $qrCodeLink;
        return self::create($data);
    }

    /**
     * 修改二维码地址
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function setRoutineQrcodeFind($id = 0, $data = array())
    {
        if (!$id) return false;
        $count = self::getRoutineQrcodeFind($id);
        if (!$count) return false;
        return self::edit($data, $id, 'id');
    }

    /**
     * 获取二维码是否存在
     * @param int $id
     * @return int|string
     */
    public static function getRoutineQrcodeFind($id = 0)
    {
        if (!$id) return 0;
        return self::where('id', $id)->count();
    }

    /**
     * 获取小程序二维码信息
     * @param int $id
     * @param string $field
     * @return array|bool|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRoutineQrcodeFindType($id = 0, $field = 'third_type,third_id,page')
    {
        if (!$id) return false;
        $count = self::getRoutineQrcodeFind($id);
        if (!$count) return false;
        return self::where('id', $id)->where('status', 1)->field($field)->find();
    }


}