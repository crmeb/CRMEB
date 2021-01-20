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
namespace crmeb\services\easywechat\wechatlive;

use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;

/**
 * Class ProgramWechatLive
 * @package crmeb\services\wechatlive
 */
class ProgramWechatLive extends AbstractAPI
{

    /**
     * 获取直播列表信息
     */
    const API_WECHAT_LIVE = 'https://api.weixin.qq.com/wxa/business/getliveinfo';
    /**
     * 创建直播间
     */
    const CREATE_LIVE_ROOM = 'https://api.weixin.qq.com/wxaapi/broadcast/room/create';
    /**
     * 直播间导入商品
     */
    const LIVE_ROOM_ADD_GOODS = 'https://api.weixin.qq.com/wxaapi/broadcast/room/addgoods';

    /**
     * 获取商品列表信息
     */
    const GOODS_LIST = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved';
    /**
     * 商品添加并审核
     */
    const GOODS_ADD = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/add';
    /**
     * 撤回审核
     */
    const GOODS_RESET_AUDIT = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/resetaudit';
    /**
     * 重新提交审核
     */
    const GOODS_AUDIT = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/autdit';
    /**
     * 删除商品
     */
    const GOODS_DELETE = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/delete';
    /**
     * 更新商品
     */
    const GOODS_UPDATE = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/update';
    /**
     * 获取商品状态
     */
    const GOODS_INFO = 'https://api.weixin.qq.com/wxa/business/getgoodswarehouse';
    /**
     * 获取成员列表
     */
    const ROLE_LIST = 'https://api.weixin.qq.com/wxaapi/broadcast/role/getrolelist';
    /**
     * 添加直播间参数
     * @var array
     */
    protected $create_data = [
        'name' => '',  // 房间名字
        'coverImg' => '',   // 通过 uploadfile 上传，填写 mediaID
        'startTime' => 0,   // 开始时间
        'endTime' => 0, // 结束时间
        'anchorName' => '',  // 主播昵称
        'anchorWechat' => '',  // 主播微信号
        'shareImg' => '',  //通过 uploadfile 上传，填写 mediaID
        'feedsImg' => '',   //通过 uploadfile 上传，填写 mediaID
        'isFeedsPublic' => 1, // 是否开启官方收录，1 开启，0 关闭
        'type' => 1, // 直播类型，1 推流 0 手机直播
        'screenType' => 0,  // 1：横屏 0：竖屏
        'closeLike' => 0, // 是否 关闭点赞 1 关闭
        'closeGoods' => 0, // 是否 关闭商品货架，1：关闭
        'closeComment' => 0, // 是否开启评论，1：关闭
        'closeReplay' => 1, // 是否关闭回放 1 关闭
        'closeShare' => 0,   //  是否关闭分享 1 关闭
        'closeKf' => 0 // 是否关闭客服，1 关闭
    ];

    /**
     * ProgramWechatLive constructor.
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        parent::__construct($accessToken);
    }

    /**
     * 获取直播间列表
     * @param int $page
     * @param int $limit
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getLiveInfo(int $page = 1, int $limit = 10)
    {
        $page = ($page - 1) * $limit;
        $params = [
            'start' => $page,
            'limit' => $limit
        ];
        return $this->parseJSON('json', [self::API_WECHAT_LIVE, $params]);
    }

    /**
     * 获取直播间回放
     * @param int $room_id
     * @param int $page
     * @param int $limit
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getLivePlayback(int $room_id, int $page = 1, int $limit = 10)
    {
        $page = ($page - 1) * $limit;
        $params = [
            'action' => 'get_replay',
            'room_id' => $room_id,
            'start' => $page,
            'limit' => $limit
        ];
        return $this->parseJSON('json', [self::API_WECHAT_LIVE, $params]);
    }

    /**
     * 创建直播间
     * @param $data
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function createRoom(array $data)
    {
        $params = array_merge($this->create_data, $data);
        return $this->parseJSON('json', [self::CREATE_LIVE_ROOM, $params]);
    }

    /**
     * 直播间导入商品
     * @param int $room_id
     * @param $ids
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function roomAddGoods(int $room_id, $ids)
    {
        $params = [
            'ids' => $ids,
            'roomId' => $room_id
        ];
        return $this->parseJSON('json', [self::LIVE_ROOM_ADD_GOODS, $params]);
    }

    /**
     * 获取商品列表
     * @param $status
     * @param int $page
     * @param int $limit
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getGoodsList($status, int $page = 0, $limit = 30)
    {
        $params = [
            'offset' => $page * $limit,
            'limit' => $limit,
            'status' => $status
        ];
        return $this->parseJSON('json', [self::GOODS_LIST, $params]);
    }

    /**
     * 获取商品详情
     * @param $ids
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getGooodsInfo($ids)
    {
        $params = [
            'goods_ids' => $ids
        ];
        return $this->parseJSON('json', [self::GOODS_INFO, $params]);
    }

    /**
     * 添加商品
     * @param string $coverImgUrl
     * @param string $name
     * @param int $priceType
     * @param string $url
     * @param $price
     * @param string $price2
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function addGoods(string $coverImgUrl, string $name, int $priceType, string $url, $price, $price2 = '')
    {
        $params = ['goodsInfo' => [
            'coverImgUrl' => $coverImgUrl,
            'name' => $name,
            'priceType' => $priceType,
            'price' => $price,
            'url' => $url
        ]];
        if ($priceType != 1) $params['goodsInfo']['price2'] = $price2;
        return $this->parseJSON('json', [self::GOODS_ADD, $params]);
    }

    /**
     * 商品撤回审核
     * @param int $goodsId
     * @param int $auditId
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function resetauditGoods(int $goodsId, int $auditId)
    {
        $params = [
            'goodsId' => $goodsId,
            'auditId' => $auditId
        ];
        return $this->parseJSON('json', [self::GOODS_RESET_AUDIT, $params]);
    }

    /**
     * 商品重新提交审核
     * @param int $goodsId
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function auditGoods(int $goodsId)
    {
        $params = [
            'goodsId' => $goodsId
        ];
        return $this->parseJSON('json', [self::GOODS_AUDIT, $params]);
    }

    /**
     * 删除商品
     * @param int $goodsId
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function deleteGoods(int $goodsId)
    {
        $params = [
            'goodsId' => $goodsId
        ];
        return $this->parseJSON('json', [self::GOODS_DELETE, $params]);
    }

    /**
     * 更新商品
     * @param int $goodsId
     * @param string $coverImgUrl
     * @param string $name
     * @param int $priceType
     * @param string $url
     * @param $price
     * @param string $price2
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function updateGoods(int $goodsId, string $coverImgUrl, string $name, int $priceType, string $url, $price, $price2 = '')
    {
        $params = ['goodsInfo' => [
            'goodsId' => $goodsId,
            'coverImgUrl' => $coverImgUrl,
            'name' => $name,
            'priceType' => $priceType,
            'price' => $price,
            'url' => $url
        ]];
        if ($priceType != 1) $params['goodsInfo']['price2'] = $price2;
        return $this->parseJSON('json', [self::GOODS_UPDATE, $params]);
    }

    /**
     * 获取成员列表
     * @param int $goodsId
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getRoleList($role = 2, int $page = 0, $limit = 30, $keyword = '')
    {
        $params = [
            'role' => $role,
            'offset' => $page * $limit,
            'limit' => $limit,
            'keyword' => $keyword
        ];
        return $this->parseJSON('get', [self::ROLE_LIST, $params]);
    }
}
