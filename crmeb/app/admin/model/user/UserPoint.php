<?php
/**
 * Created by PhpStorm.
 * User: liying
 * Date: 2018/7/20
 * Time: 18:08
 */

namespace app\admin\model\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\services\PHPExcelService;

class UserPoint extends BaseModel
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
    protected $name = 'user_point';

    use ModelTrait;

    /*
     * 获取积分信息
     * */
    public static function systemPage($where)
    {
        $model = new UserBill();
        if ($where['status'] != '') UserBill::where('status', $where['status']);
        if ($where['title'] != '') UserBill::where('title', 'like', "%$where[status]%");
        $model->where('category', 'integral')->select();
        return $model::page($model);
    }

    /*
     *
     * 异步获取积分信息
     * */
    public static function getpointlist($where)
    {
        $list = self::setWhere($where)
            ->order('a.add_time desc')
            ->field(['a.*', 'b.nickname'])
            ->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->toArray();
        foreach ($list as $key => $item) {
            $list[$key]['add_time'] = date('Y-m-d', $item['add_time']);
        }
        $count = self::setWhere($where)->field(['a.*', 'b.nickname'])->count();
        return ['count' => $count, 'data' => $list];
    }

    //生成Excel表格并下载
    public static function SaveExport($where)
    {
        $list = self::setWhere($where)->field(['a.*', 'b.nickname'])->select();
        $Export = [];
        foreach ($list as $key => $item) {
            $Export[] = [
                $item['id'],
                $item['title'],
                $item['balance'],
                $item['number'],
                $item['mark'],
                $item['nickname'],
                date('Y-m-d H:i:s', $item['add_time']),
            ];
        }
        PHPExcelService::setExcelHeader(['编号', '标题', '积分余量', '明细数字', '备注', '用户微信昵称', '添加时间'])
            ->setExcelTile('积分日志', '积分日志' . time(), '生成时间：' . date('Y-m-d H:i:s', time()))
            ->setExcelContent($Export)
            ->ExcelSave();
    }

    public static function setWhere($where)
    {
        $model = UserBill::alias('a')->join('user b', 'a.uid=b.uid', 'left')->where('a.category', 'integral');
        $time['data'] = '';
        if ($where['start_time'] != '' && $where['end_time'] != '') {
            $time['data'] = $where['start_time'] . ' - ' . $where['end_time'];
        }
        $model = self::getModelTime($time, $model, 'a.add_time');
        if ($where['nickname'] != '') {
            $model = $model->where('b.nickname|b.uid', 'like', $where['nickname']);
        }
        return $model;
    }

    //获取积分头部信息
    public static function getUserpointBadgelist($where)
    {
        return [
            [
                'name' => '总积分',
                'field' => '个',
                'count' => self::setWhere($where)->sum('a.number'),
                'background_color' => 'layui-bg-blue',
            ],
            [
                'name' => '客户签到次数',
                'field' => '个',
                'count' => self::setWhere($where)->where('a.type', 'sign')->group('a.uid')->count(),
                'background_color' => 'layui-bg-cyan',
            ],
            [
                'name' => '签到送出积分',
                'field' => '个',
                'count' => self::setWhere($where)->where('a.type', 'sign')->sum('a.number'),
                'background_color' => 'layui-bg-cyan',
            ],
            [
                'name' => '使用积分',
                'field' => '个',
                'count' => self::setWhere($where)->where('a.type', 'deduction')->sum('a.number'),
                'background_color' => 'layui-bg-cyan',
            ],
        ];
    }
}