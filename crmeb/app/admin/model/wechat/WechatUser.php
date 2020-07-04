<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/28
 */

namespace app\admin\model\wechat;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\facade\{Cache, Config};
use crmeb\services\{WechatService, PHPExcelService};
use app\admin\model\user\{User, UserBill, UserExtract};
use app\admin\model\order\{StoreOrder, StoreOrderStatus};

/**
 * 微信用户 model
 * Class WechatUser
 * @package app\admin\model\wechat
 */
class WechatUser extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'uid';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'wechat_user';

    use ModelTrait;

    protected $insert = ['add_time'];

    /**
     * 用uid获得 微信openid
     * @param $uid
     * @return mixed
     * @throws \Exception
     */
    public static function uidToOpenid($uid)
    {
        $openid = self::where('uid', $uid)->value('openid');
        if (!$openid) exception('对应的openid不存在!');
        return $openid;
    }

    /**
     * 用uid获得 小程序 openid
     * @param $uid
     * @return mixed
     * @throws \Exception
     */
    public static function uidToRoutineOpenid($uid)
    {
        $openid = self::where('uid', $uid)->value('routine_openid');
        if (!$openid) exception('对应的routine_openid不存在!');
        return $openid;
    }

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    /**
     * .添加新用户
     * @param $openid
     * @return object
     */
    public static function setNewUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        $userInfo['tagid_list'] = implode(',', $userInfo['tagid_list']);
        return self::create($userInfo);
    }

    /**
     * 更新用户信息
     * @param $openid
     * @return bool
     */
    public static function updateUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        $userInfo['tagid_list'] = implode(',', $userInfo['tagid_list']);
        return self::edit($userInfo, $openid, 'openid');
    }

    /**
     * 用户存在就更新 不存在就添加
     * @param $openid
     */
    public static function saveUser($openid)
    {
        self::be($openid, 'openid') == true ? self::updateUser($openid) : self::setNewUser($openid);
    }

    /**
     * 用户取消关注
     * @param $openid
     * @return bool
     */
    public static function unSubscribe($openid)
    {
        return self::edit(['subscribe' => 0], $openid, 'openid');
    }

    /**
     * 获取微信用户
     * @param array $where
     * @return array
     */
    public static function systemPage($where = [], $isall = false)
    {
        $model = new self;
        $model = $model->where('openid|routine_openid', 'NOT NULL');
        if ($where['nickname'] !== '') $model = $model->where('nickname', 'LIKE', "%$where[nickname]%");
        if (isset($where['data']) && $where['data'] !== '') $model = self::getModelTime($where, $model, 'add_time');
        if (isset($where['tagid_list']) && $where['tagid_list'] !== '') {
            $tagid_list = explode(',', $where['tagid_list']);
            foreach ($tagid_list as $v) {
                $model = $model->where('tagid_list', 'LIKE', "%$v%");
            }
        }
        if (isset($where['groupid']) && $where['groupid'] !== '-1') $model = $model->where('groupid', "$where[groupid]");
        if (isset($where['sex']) && $where['sex'] !== '') $model = $model->where('sex', "$where[sex]");
        if (isset($where['subscribe']) && $where['subscribe'] !== '') $model = $model->where('subscribe', "$where[subscribe]");
        $model = $model->order('uid desc');
        if (isset($where['export']) && $where['export'] == 1) {
            $list = $model->select()->toArray();
            $export = [];
            foreach ($list as $index => $item) {
                $export[] = [
                    $item['nickname'],
                    $item['sex'],
                    $item['country'] . $item['province'] . $item['city'],
                    $item['subscribe'] == 1 ? '关注' : '未关注',
                ];
                $list[$index] = $item;
            }
            PHPExcelService::setExcelHeader(['名称', '性别', '地区', '是否关注公众号'])
                ->setExcelTile('微信用户导出', '微信用户导出' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time()))
                ->setExcelContent($export)
                ->ExcelSave();
        }
        return self::page($model, function ($item) {
            $item['time'] = $item['add_time'] ? date('Y-m-d H:i', $item['add_time']) : '暂无';
        }, $where);
    }

    public static function setSpreadWhere($where = [], $alias = 'a', $model = null)
    {
        $model = is_null($model) ? new  self() : $model;
        if ($alias) {
            $model = $model->alias($alias)->join('user u', 'a.uid=u.uid')->order('u.uid desc');
            $alias .= '.';
        }
        $status = (int)sys_config('store_brokerage_statu');
        if ($status == 1) {
            if ($Listuids = User::where(['is_promoter' => 1])->field('uid')->select()) {
                $newUids = [];
                foreach ($Listuids as $item) {
                    $newUids[] = $item['uid'];
                }
                $uids = $newUids;
                unset($uid, $newUids);
                $model = $model->where($alias . 'uid', 'in', implode(',', $uids));
            } else
                $model = $model->where($alias . 'uid', -1);
        }
        if ($where['nickname'] !== '') $model = $model->where("{$alias}nickname|{$alias}uid|u.phone", 'LIKE', "%$where[nickname]%");
        if ((isset($where['start_time']) && isset($where['end_time'])) && $where['start_time'] !== '' && $where['end_time'] !== '') {
            $model = $model->where("{$alias}add_time", 'between', [strtotime($where['start_time']), strtotime($where['end_time'])]);
        }
        if (isset($where['sex']) && $where['sex'] !== '') $model = $model->where($alias . 'sex', $where['sex']);
        if (isset($where['subscribe']) && $where['subscribe'] !== '') $model = $model->where($alias . 'subscribe', $where['subscribe']);
        if (isset($where['order']) && $where['order'] != '') $model = $model->order($where['order']);
        if (isset($where['user_type']) && $where['user_type'] != '') {
            if ($where['user_type'] == 1) {
                $model = $model->where($alias . 'unionid', 'neq', 'NULL');
            } else if ($where['user_type'] == 2)
                $model = $model->where($alias . 'openid', 'neq', 'NULL')->where($alias . 'unionid', 'NULL');
            else if ($where['user_type'] == 3)
                $model = $model->where($alias . 'routine_openid', 'neq', 'NULL')->where($alias . 'unionid', 'NULL');
        }
        if (isset($where['is_time']) && isset($where['data']) && $where['data']) $model = self::getModelTime($where, $model, $alias . 'add_time');
        return $model;
    }

    /**
     * 获取分销用户
     * @param array $where
     * @return array
     */
    public static function agentSystemPage($where = [])
    {
        //提现数据
        $exteactSql = UserExtract::where(['status' => 1])
            ->group('uid')
            ->field(['sum(extract_price) as extract_count_price', 'count(id) as extract_count_num', 'uid as euid'])
            ->fetchSql(true)
            ->select();
        //订单数据
        $orderSql = StoreOrder::alias('o')
            ->where(['o.paid' => 1, 'o.refund_status' => 0])
            ->field(['sum(o.pay_price) as order_price', 'count(o.id) as order_count', 'o.uid as ouid'])
            ->group('o.uid')
            ->fetchSql(true)
            ->select();
        //佣金数据
        $billSql = UserBill::where(['status' => 1])
            ->where('type', 'brokerage')
            ->where('pm', 1)
            ->group('uid')
            ->field(['sum(number) as brokerage_money', 'uid as buid'])
            ->fetchSql(true)
            ->select();
        $model = User::where(['status' => 1])->where('is_promoter', 1);
        $model = $model->alias('u')
            ->join('(' . $orderSql . ') o', 'o.ouid = u.uid', 'left')
            ->join('(' . $billSql . ') b', 'b.buid = u.uid', 'left')
            ->join('(' . $exteactSql . ') e', 'e.euid = u.uid', 'left');
        if ($where['nickname'] !== '') {
            $model = $model->where("u.nickname|u.uid|u.phone", 'LIKE', "%$where[nickname]%");
        }
        if ($where['data']) $model = self::getModelTime($where, $model, 'u.add_time');
        $count = $model->count();
        $data = $model->field(['avatar as headimgurl', 'brokerage_price as new_money', 'u.*', 'o.*', 'e.*', 'b.*'])
            ->page((int)$where['page'], (int)$where['limit'])
            ->order('u.uid desc')
            ->select();
        $data = count($data) ? $data->toArray() : [];
        $export = [];
        $broken_time = intval(sys_config('extract_time'));
        $search_time = time() - 86400 * $broken_time;
        foreach ($data as &$item) {
            if ($spread_uid = User::where('uid', $item['uid'])->value('spread_uid')) {
                if ($user = User::where('uid', $spread_uid)->field(['uid', 'nickname'])->find()) {
                    $item['spread_name'] = $user['nickname'] . '/' . $user['uid'];
                }
            }
            $item['spread_count'] = User::where('spread_uid', $item['uid'])->count();
            //返佣 +
            $brokerage_commission = UserBill::where(['uid' => $item['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
                ->where('add_time', '>', $search_time)
                ->where('pm', 1)
                ->sum('number');
            //退款退的佣金 -
            $refund_commission = UserBill::where(['uid' => $item['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
                ->where('add_time', '>', $search_time)
                ->where('pm', 0)
                ->sum('number');
            $item['broken_commission'] = bcsub($brokerage_commission, $refund_commission, 2);
            if ($item['broken_commission'] < 0)
                $item['broken_commission'] = 0;
            if ($item['brokerage_money'] > $refund_commission)
                $item['brokerage_money'] = bcsub($item['brokerage_money'], $refund_commission, 2);
            else
                $item['brokerage_money'] = 0;
            //导出数据
            $export[] = [
                $item['uid'],
                $item['nickname'],
                $item['phone'],
                $item['spread_count'],
                $item['order_count'],
                $item['order_price'],
                $item['brokerage_money'],
                $item['extract_count_price'],
                $item['extract_count_num'],
                $item['new_money'],
                $item['spread_name'] ?? '',
            ];
        }
        if (isset($where['excel']) && $where['excel'] == 1) {
            PHPExcelService::setExcelHeader(['用户编号', '昵称', '电话号码', '推广用户数量', '订单数量', '推广订单金额', '佣金金额', '已提现金额', '提现次数', '未提现金额', '上级推广人'])
                ->setExcelTile('推广用户', '推广用户导出' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time()))
                ->setExcelContent($export)
                ->ExcelSave();
        }

        return compact('data', 'count');
    }

    public static function setSairOrderWhere($where, $model = null, $alias = '')
    {
        $model = $model === null ? new self() : $model;
        if (!isset($where['uid'])) return $model;
        if ($alias) {
            $model = $model->alias($alias);
            $alias .= '.';
        }
        if (isset($where['type'])) {
            switch ((int)$where['type']) {
                case 1:
                    $uids = User::where('spread_uid', $where['uid'])->column('uid');
                    if (count($uids))
                        $model = $model->where("{$alias}uid", 'in', $uids);
                    else
                        $model = $model->where("{$alias}uid", 0);
                    break;
                case 2:
                    $uids = User::where('spread_uid', $where['uid'])->column('uid');
                    if (count($uids))
                        $spread_uid_two = User::where('spread_uid', 'in', $uids)->column('uid');
                    else
                        $spread_uid_two = [0];
                    if (count($spread_uid_two))
                        $model = $model->where("{$alias}uid", 'in', $spread_uid_two);
                    else
                        $model = $model->where("{$alias}uid", 0);
                    break;
                default:
                    $uids = User::where('spread_uid', $where['uid'])->column('uid');
                    if (count($uids)) {
                        if ($spread_uid_two = User::where('spread_uid', 'in', $uids)->column('uid')) {
                            $uids = array_merge($uids, $spread_uid_two);
                            $uids = array_unique($uids);
                            $uids = array_merge($uids);
                        }
                        $model = $model->where("{$alias}uid", 'in', $uids);
                    } else
                        $model = $model->where("{$alias}uid", 0);
                    break;
            }
        }
        if (isset($where['data']) && $where['data']) $model = self::getModelTime($where, $model, "{$alias}add_time");
        return $model->where("{$alias}is_del", 0)->where("{$alias}is_system_del", 0)->where($alias . 'paid', 1);
    }

    /*
    *  推广订单统计
    * @param array $where
    * @return array
    * */
    public static function getStairOrderBadge($where)
    {
        if (!isset($where['uid'])) return [];
        $data['order_count'] = self::setSairOrderWhere($where, new StoreOrder())->count();
        $data['order_price'] = self::setSairOrderWhere($where, new StoreOrder())->sum('pay_price');
        $ids = self::setSairOrderWhere($where, new StoreOrder())
            ->where(['paid' => 1, 'is_del' => 0, 'refund_status' => 0])
            ->where('status', '>', 1)
            ->column('id');
        $data['number_price'] = 0;
        if (count($ids)) $data['number_price'] = UserBill::where(['category' => 'now_money', 'type' => 'brokerage', 'uid' => $where['uid']])->where('link_id', 'in', $ids)->sum('number');
        $where['type'] = 1;
        $data['one_price'] = self::setSairOrderWhere($where, new StoreOrder())->sum('pay_price');
        $data['one_count'] = self::setSairOrderWhere($where, new StoreOrder())->count();
        $where['type'] = 2;
        $data['two_price'] = self::setSairOrderWhere($where, new StoreOrder())->sum('pay_price');
        $data['two_count'] = self::setSairOrderWhere($where, new StoreOrder())->count();
        return [
            [
                'name' => '总金额',
                'field' => '元',
                'count' => $data['order_price'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
            [
                'name' => '订单总数',
                'field' => '单',
                'count' => $data['order_count'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
            [
                'name' => '返佣总金额',
                'field' => '元',
                'count' => $data['number_price'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
            [
                'name' => '一级总金额',
                'field' => '元',
                'count' => $data['one_price'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
            [
                'name' => '一级订单数',
                'field' => '单',
                'count' => $data['one_count'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
            [
                'name' => '二级总金额',
                'field' => '元',
                'count' => $data['two_price'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
            [
                'name' => '二级订单数',
                'field' => '单',
                'count' => $data['two_count'],
                'background_color' => 'layui-bg-cyan',
                'col' => 3,
            ],
        ];
    }

    /*
     * 设置查询条件
     * @param array $where
     * @param object $model
     * @param string $alias
     * */
    public static function setSairWhere($where, $model = null, $alias = '')
    {
        $model = $model === null ? new self() : $model;
        if (!isset($where['uid'])) return $model;
        if ($alias) {
            $model = $model->alias($alias);
            $alias .= '.';
        }
        if (isset($where['type'])) {
            switch ((int)$where['type']) {
                case 1:
                    $uids = User::where('spread_uid', $where['uid'])->column('uid');
                    if (count($uids))
                        $model = $model->where("{$alias}uid", 'in', $uids);
                    else
                        $model = $model->where("{$alias}uid", 0);
                    break;
                case 2:
                    $uids = User::where('spread_uid', $where['uid'])->column('uid');
                    if (count($uids))
                        $spread_uid_two = User::where('spread_uid', 'in', $uids)->column('uid');
                    else
                        $spread_uid_two = [0];
                    if (count($spread_uid_two))
                        $model = $model->where("{$alias}uid", 'in', $spread_uid_two);
                    else
                        $model = $model->where("{$alias}uid", 0);
                    break;
                default:
                    $uids = User::where('spread_uid', $where['uid'])->column('uid');
                    if (count($uids)) {
                        if ($spread_uid_two = User::where('spread_uid', 'in', $uids)->column('uid')) {
                            $uids = array_merge($uids, $spread_uid_two);
                            $uids = array_unique($uids);
                            $uids = array_merge($uids);
                        }
                        $model = $model->where("{$alias}uid", 'in', $uids);
                    } else
                        $model = $model->where("{$alias}uid", 0);
                    break;
            }
        }
        if (isset($where['data']) && $where['data']) $model = self::getModelTime($where, $model, "{$alias}add_time");
        if (isset($where['nickname']) && $where['nickname']) $model = $model->where("{$alias}phone|{$alias}nickname|{$alias}real_name|{$alias}uid", 'LIKE', "%$where[nickname]%");
        return $model->where($alias . 'status', 1);
    }

    public static function getSairBadge($where)
    {
        $data['number'] = self::setSairWhere($where, new User())->count();
        $where['type'] = 1;
        $data['one_number'] = self::setSairWhere($where, new User())->count();
        $where['type'] = 2;
        $data['two_number'] = self::setSairWhere($where, new User())->count();
        $col = $data['two_number'] > 0 ? 4 : 6;
        return [
            [
                'name' => '总人数',
                'field' => '人',
                'count' => $data['number'],
                'background_color' => 'layui-bg-cyan',
                'col' => $col,
            ],
            [
                'name' => '一级人数',
                'field' => '人',
                'count' => $data['one_number'],
                'background_color' => 'layui-bg-cyan',
                'col' => $col,
            ],
            [
                'name' => '二级人数',
                'field' => '人',
                'count' => $data['two_number'],
                'background_color' => 'layui-bg-cyan',
                'col' => $col,
            ],
        ];
    }

    public static function getStairList($where)
    {
        if (!isset($where['uid'])) return [];
        $data = self::setSairWhere($where, new User())->order('add_time desc')->page((int)$where['page'], (int)$where['limit'])->select();
        $data = count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            $item['spread_count'] = User::where('spread_uid', $item['uid'])->count();
            $item['order_count'] = StoreOrder::where('uid', $item['uid'])->where(['paid' => 1, 'is_del' => 0])->count();
            $item['promoter_name'] = $item['is_promoter'] ? '是' : '否';
            $item['add_time'] = $item['add_time'] ? date("Y-m-d H:i:s", $item['add_time']) : '';
        }
        $count = self::setSairWhere($where, new User())->count();
        return compact('data', 'count');
    }

    /*
    * 推广订单
    * @param array $where
    * @return array
    * */
    public static function getStairOrderList($where)
    {
        if (!isset($where['uid'])) return [];
        $data = self::setSairOrderWhere($where, new StoreOrder())
            ->page((int)$where['page'], (int)$where['limit'])
            ->order('add_time desc')
            ->select();
        $data = count($data) ? $data->toArray() : [];
        $Info = User::where('uid', $where['uid'])->find();
        foreach ($data as &$item) {
            $userInfo = User::where('uid', $item['uid'])->find();
            $item['user_info'] = '';
            $item['avatar'] = '';
            if ($userInfo) {
                $item['user_info'] = $userInfo->nickname . '|' . ($userInfo->phone ? $userInfo->phone . '|' : '') . $userInfo->real_name;
                $item['avatar'] = $userInfo->avatar;
            }
            $item['spread_info'] = $Info->nickname . "|" . ($Info->phone ? $Info->phone . "|" : '') . $Info->uid;
            $item['number_price'] = UserBill::where(['uid'=>$Info['uid'], 'category' => 'now_money', 'type' => 'brokerage', 'link_id' => $item['id']])->value('number');
            $item['_pay_time'] = date('Y-m-d H:i:s', $item['pay_time']);
            $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['take_time'] = ($change_time = StoreOrderStatus::where(['change_type' => 'user_take_delivery', 'oid' => $item['id']])->value('change_time')) ?
                date('Y-m-d H:i:s', $change_time) : '暂无';
        }
        $count = self::setSairOrderWhere($where, new StoreOrder())->count();
        return compact('data', 'count');
    }

    /*
    * 获取分销员列表头部统计
    * */
    public static function getSpreadBadge($where)
    {
        $where['is_time'] = 1;
        $listuids = self::setSpreadWhere($where)->field('u.uid')->select();
        $newUids = [];
        foreach ($listuids as $item) {
            $newUids[] = $item['uid'];
        }
        $uids = $newUids;
        unset($uid, $newUids);
        //分销员人数
        $data['sum_count'] = count($uids);
        $data['spread_sum'] = 0;
        $data['order_count'] = 0;
        $data['pay_price'] = 0;
        $data['number'] = 0;
        $data['extract_count'] = 0;
        $data['extract_price'] = 0;
        if ($data['sum_count']) {
            //发展会员人数
            $data['spread_sum'] = User::where('spread_uid', 'in', $uids)->count();
            //订单总数
            $data['order_count'] = StoreOrder::where('uid', 'in', $uids)->where(['paid' => 1, 'refund_status' => 0])->count();
            //订单金额
            $data['pay_price'] = StoreOrder::where('uid', 'in', $uids)->where(['paid' => 1, 'refund_status' => 0])->sum('pay_price');
            //可提现金额
            $data['number'] = User::where('uid', 'in', $uids)->sum('brokerage_price');
            //提现次数
            $data['extract_count'] = UserExtract::where('uid', 'in', $uids)->count();
            //获取某个用户可提现金额
            $data['extract_price'] = User::getextractPrice($uids, $where);
        }

        return [
            [
                'name' => '分销员人数',
                'field' => '人',
                'count' => $data['sum_count'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
            [
                'name' => '发展会员人数',
                'field' => '人',
                'count' => $data['spread_sum'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
            [
                'name' => '分销订单数',
                'field' => '单',
                'count' => $data['order_count'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
            [
                'name' => '订单金额',
                'field' => '元',
                'count' => $data['pay_price'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
            [
                'name' => '提现金额',
                'field' => '元',
                'count' => $data['number'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
            [
                'name' => '提现次数',
                'field' => '次',
                'count' => $data['extract_count'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
            [
                'name' => '未提现金额',
                'field' => '元',
                'count' => $data['number'],
                'background_color' => 'layui-bg-cyan',
                'col' => 2,
            ],
        ];
    }

    /**
     * 获取筛选后的所有用户uid
     * @param array $where
     * @return array
     */
    public static function getAll($where = [])
    {
        $model = new self;
        if ($where['nickname'] !== '') $model = $model->where('nickname', 'LIKE', "%$where[nickname]%");
        if ($where['data'] !== '') $model = self::getModelTime($where, $model, 'add_time');
        if ($where['tagid_list'] !== '') {
            $model = $model->where('tagid_list', 'LIKE', "%$where[tagid_list]%");
        }
        if ($where['groupid'] !== '-1') $model = $model->where('groupid', "$where[groupid]");
        if ($where['sex'] !== '') $model = $model->where('sex', "$where[sex]");
        return $model->column('uid', 'uid');
    }

    /**
     * 获取已关注的用户
     * @param $field
     */
    public static function getSubscribe($field)
    {
        return self::where('subscribe', 1)->column($field);
    }

    public static function getUserAll($field)
    {
        return self::column($field);
    }

    public static function getUserTag()
    {
        $tagName = Config::get('system_wechat_tag');
        return Cache::tag($tagName)->remember('_wechat_tag', function () use ($tagName) {
            Cache::tag($tagName, ['_wechat_tag']);
            $tag = WechatService::userTagService()->lists()->toArray()['tags'] ?: [];
            $list = [];
            foreach ($tag as $g) {
                $list[$g['id']] = $g;
            }
            return $list;
        });
    }

    public static function clearUserTag()
    {
        Cache::delete('_wechat_tag');
    }

    public static function getUserGroup()
    {
        $tagName = Config::get('system_wechat_tag');
        return Cache::tag($tagName)->remember('_wechat_group', function () use ($tagName) {
            Cache::tag($tagName, ['_wechat_group']);
            $tag = WechatService::userGroupService()->lists()->toArray()['groups'] ?: [];
            $list = [];
            foreach ($tag as $g) {
                $list[$g['id']] = $g;
            }
            return $list;
        });
    }

    public static function clearUserGroup()
    {

        Cache::delete('_wechat_group');
    }

    /**
     * 获取推广人数
     * @param $uid //用户的uid
     * @param int $spread
     * $spread 0 一级推广人数  1 二级推广人数
     * @return int|string
     */
    public static function getUserSpreadUidCount($uid, $spread = 1)
    {
        $userStair = User::where('spread_uid', $uid)->column('uid', 'uid');//获取一级推家人
        if ($userStair) {
            if (!$spread) return count($userStair);//返回一级推人人数
            else return User::where('spread_uid', 'IN', implode(',', $userStair))->count();//二级推荐人数
        } else return 0;
    }

    /**
     * 获取推广人的订单
     * @param $uid
     * @param int $spread
     * $spread 0 一级推广总订单  1 所有推广总订单
     * @return int|string
     */
    public static function getUserSpreadOrderCount($uid, $spread = 1)
    {
        $userStair = User::where('spread_uid', $uid)->column('uid', 'uid');//获取一级推家人uid
        if ($userStair) {
            if (!$spread) {
                return StoreOrder::where('uid', 'IN', implode(',', $userStair))->where('paid', 1)->where('refund_status', 0)->where('status', 2)->count();//获取一级推广人订单数
            } else {
                $userSecond = User::where('spread_uid', 'IN', implode(',', $userStair))->column('uid', 'uid');//二级推广人的uid
                if ($userSecond) {
                    return StoreOrder::where('uid', 'IN', implode(',', $userSecond))->where('paid', 1)->where('refund_status', 0)->where('status', 2)->count();//获取二级推广人订单数
                } else return 0;
            }
        } else return 0;
    }

}