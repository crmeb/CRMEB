<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/28
 */

namespace app\admin\model\wechat;

use app\admin\model\user\User;
use think\facade\Cache;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\admin\model\wechat\WechatUser as UserModel;

/**
 * 微信用户行为记录  model
 * Class WechatMessage
 * @package app\admin\model\wechat
 */
class WechatMessage extends BaseModel
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
    protected $name = 'wechat_message';

    use ModelTrait;

    protected $insert = ['add_time'];

    /**
     * 微信用户操作的基本所有操作
     * @var array
     */
    public static $mold = array(
        'event_subscribe' => '关注微信号',
        'event_unsubscribe' => '取消关注微信号',
        'event_scan' => '扫码',
        'event_templatesendjobfinish' => '进入小程序',
        'event_location' => '获取位置',
        'event_click' => '点击微信菜单关键字',
        'event_view' => '点击微信菜单链接',
        'event_view_miniprogram' => '点击微信菜单进入小程序',
        'text' => '收到文本消息',
        'image' => '收到图片消息',
        'video' => '收到视频消息',
        'voice' => '收到声音消息',
        'location' => '收到位置消息',
        'link' => '收到链接消息',
        'event_scan_subscribe' => '扫码关注'
    );

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    public static function setMessage($result, $openid, $type)
    {
        if (is_object($result) || is_array($result)) $result = json_encode($result);
        $add_time = time();
        $data = compact('result', 'openid', 'type', 'add_time');
        return self::create($data);
    }

    public static function setOnceMessage($result, $openid, $type, $unique, $cacheTime = 172800)
    {
        $cacheName = 'wechat_message_' . $type . '_' . $unique;
        if (Cache::has($cacheName)) return true;
        $res = self::setMessage($result, $openid, $type);
        if ($res) Cache::set($cacheName, 1, $cacheTime);
        return $res;
    }

    /**
     * 按钮事件
     * @param $Event
     * @return mixed
     */
    public static function tidyEvent($Event)
    {
        $res = array(
            'msg' => $Event['EventKey'],
        );
        return $res;
    }

    /**
     * 取消关注事件扫码
     * @param $Event
     * @return mixed
     */
    public static function tidyNull()
    {
        $res = array(
            'msg' => '无',
        );
        return $res;
    }

    /**
     * 整理文本显示的数据
     * @param $text 收到的文本消息
     * return 返回收到的消息
     */
    public static function tidyText($text)
    {
        $res = array(
            'rep_id' => '1',
            'MsgId' => $text['MsgId'],
            'Content' => $text['Content'],
            'msg' => $text['Content'],
        );
        return $res;
    }

    /**
     * 整理图片显示的数据
     * @param $image
     * @return mixed
     */
    public static function tidyImage($image)
    {
        $res = array(
            'rep_id' => '2',
            'MsgId' => $image['MsgId'],
            'PicUrl' => $image['PicUrl'],
            'MediaId' => $image['MediaId'],
            'msg' => '媒体ID：' . $image['MediaId'],
        );
        return $res;
    }

    /**
     * 整理视屏显示的数据
     * @param $video
     * @return mixed
     */
    public static function tidyVideo($video)
    {
        $res = array(
            'rep_id' => '3',
            'MsgId' => $video['MsgId'],
            'MediaId' => $video['MediaId'],
            'msg' => '媒体ID：' . $video['MediaId'],
        );
        return $res;
    }

    /**
     * 整理声音显示的数据
     * @param $voice
     * @return mixed
     */
    public static function tidyVoice($voice)
    {
        $res = array(
            'rep_id' => '4',
            'MsgId' => $voice['MsgId'],
            'MediaId' => $voice['MediaId'],
            'msg' => '媒体ID：' . $voice['MediaId'],
        );
        return $res;
    }

    /**
     * 地理位置
     * @param $location
     * @return array
     */
    public static function tidyLocation($location)
    {
        $res = array(
            'rep_id' => '5',
            'MsgId' => $location['MsgId'],
            'Label' => $location['Label'],
            'msg' => $location['Label'],
        );
        return $res;
    }

    /**
     * 获取用户扫码点击事件
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array())
    {
        $model = new self;
        $model = $model->alias('m');
        if ($where['nickname'] !== '') {
            $user = UserModel::where('nickname', 'LIKE', "%$where[nickname]%")->field('openid')->select();
            if (empty($user->toArray())) $model = $model->where('m.id', 0);
            foreach ($user as $v) {
                $model = $model->where('m.openid', $v['openid']);
            }
        }
        if ($where['type'] !== '') $model = $model->where('m.type', $where['type']);
        if ($where['data'] !== '') {
            list($startTime, $endTime) = explode(' - ', $where['data']);
            $model = $model->where('m.add_time', '>', strtotime($startTime));
            $model = $model->where('m.add_time', '<', strtotime($endTime));
        }
        $model = $model->field('u.nickname,m.*')->join('WechatUser u', 'u.openid=m.openid')->order('m.id desc');
        return self::page($model, function ($item) {
            switch ($item['type']) {
                case 'text':
                    $item['result_arr'] = self::tidyText(json_decode($item['result'], true));
                    break;
                case 'image':
                    $item['result_arr'] = self::tidyImage(json_decode($item['result'], true));
                    break;
                case 'video':
                    $item['result_arr'] = self::tidyVideo(json_decode($item['result'], true));
                    break;
                case 'voice':
                    $item['result_arr'] = self::tidyVoice(json_decode($item['result'], true));
                    break;
                case 'location':
                    $item['result_arr'] = self::tidyLocation(json_decode($item['result'], true));
                    break;
                case 'event_click':
                    $item['result_arr'] = self::tidyEvent(json_decode($item['result'], true));
                    break;
                case 'event_view':
                    $item['result_arr'] = self::tidyEvent(json_decode($item['result'], true));
                    break;
                case 'event_subscribe':
                    $item['result_arr'] = self::tidyNull();
                    break;
                case 'event_unsubscribe':
                    $item['result_arr'] = self::tidyNull();
                    break;
                case 'event_scan':
                    $item['result_arr'] = self::tidyNull();
                    break;
                default :
                    $item['result_arr'] = ['msg' => $item['type']];
                    break;
            }
            $item['type_name'] = isset(self::$mold[$item['type']]) ? self::$mold[$item['type']] : '未知';
        }, $where);
    }

    /*
    * 获取应为记录数据
     *
     */
    public static function getViweList($date, $class = [])
    {
        $model = new self();
        switch ($date) {
            case null:
            case 'today':
            case 'week':
            case 'year':
                if ($date == null) $date = 'month';
                $model = $model->whereTime('add_time', $date);
                break;
            case 'quarter':
                $time = User::getMonth('n');
                $model = $model->where('add_time', 'between', $time);
                break;
            default:
                list($startTime, $endTime) = explode('-', $date);
                $model = $model->where('add_time', '>', strtotime($startTime));
                $model = $model->where('add_time', '<', strtotime($endTime));
                break;
        }
        $list = $model->field(['type', 'count(*) as num', 'result'])->group('type')->limit(0, 20)->select()->toArray();
        $viwe = [];
        foreach ($list as $key => $item) {
            $now_list['name'] = isset(self::$mold[$item['type']]) ? self::$mold[$item['type']] : '未知';
            $now_list['value'] = $item['num'];
            $now_list['class'] = isset($class[$key]) ? $class[$key] : '';
            $viwe[] = $now_list;
        }
        return $viwe;
    }
}