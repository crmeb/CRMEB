<?php

namespace crmeb\services\subscribe;

use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Exceptions\InvalidArgumentException;

/**
 * 小程序订阅消息
 * Class ProgramSubscribe
 * @package crmeb\utils
 * @method $this
 * @method $this template(string $template_id) 设置模板id
 * @method $this withTemplateId(string $template_id) 设置模板id
 * @method $this andTemplateId(string $template_id) 设置模板id
 * @method $this andTemplate(string $template_id) 设置模板id
 * @method $this andUses(string $template_id) 设置模板id
 * @method $this to(string $touser) 设置opendid
 * @method $this andReceiver(string $touser) 设置opendid
 * @method $this withReceiver(string $touser) 设置opendid
 * @method $this with(array $data) 设置发送内容
 * @method $this andData(array $data) 设置发送内容
 * @method $this withData(array $data) 设置发送内容
 * @method $this data(array $data) 设置发送内容
 * @method $this withUrl(string $page) 设置跳转路径
 */
class ProgramSubscribe extends AbstractAPI
{

    /**
     * 添加模板接口
     */
    const API_SET_TEMPLATE_ADD = 'https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate';

    /**
     * 删除模板消息接口
     */
    const API_SET_TEMPLATE_DEL = 'https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate';

    /**
     * 获取模板消息列表
     */
    const API_GET_TEMPLATE_LIST = 'https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate';

    /**
     * 获取模板消息分类
     */
    const API_GET_TEMPLATE_CATE = 'https://api.weixin.qq.com/wxaapi/newtmpl/getcategory';

    /**
     * 获取模板消息关键字
     */
    const API_GET_TEMPLATE_KEYWORKS = 'https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatekeywords';

    /**
     * 获取公共模板
     */
    const API_GET_PUBLIC_TEMPLATE = 'https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatetitles';

    /**
     * 发送模板消息
     */
    const API_SUBSCRIBE_SEND = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send';

    /**
     * Attributes
     * @var array
     */
    protected $message = [
        'touser' => '',
        'template_id' => '',
        'page' => '',
        'data' => [],
    ];

    /**
     * Message backup.
     *
     * @var array
     */
    protected $messageBackup;

    protected $required = ['template_id', 'touser'];

    /**
     * ProgramSubscribeService constructor.
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        parent::__construct($accessToken);

        $this->messageBackup = $this->message;

    }

    /**
     * 获取当前拥有的模板列表
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getTemplateList()
    {
        return $this->parseJSON('get', [self::API_GET_TEMPLATE_LIST]);
    }

    /**
     * 获取公众模板列表
     * @param string $ids
     * @param int $start
     * @param int $limit
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getPublicTemplateList(string $ids, int $start = 0, int $limit = 10)
    {
        $params = [
            'ids' => $ids,
            'start' => $start,
            'limit' => $limit
        ];
        return $this->parseJSON('get', [self::API_GET_PUBLIC_TEMPLATE, $params]);
    }

    /**
     * 获取模板分类
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getTemplateCate()
    {
        return $this->parseJSON('get', [self::API_GET_TEMPLATE_CATE]);
    }

    /**
     * 获取模板标题下的关键词列表
     * @param string $tid 模板标题 id，可通过接口获取
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function getPublicTemplateKeywords(string $tid)
    {
        $params = [
            'tid' => $tid
        ];
        return $this->parseJSON('get', [self::API_GET_TEMPLATE_KEYWORKS, $params]);
    }

    /**
     * 添加订阅模板消息
     * @param string $tid 模板标题 id，可通过接口获取，也可登录小程序后台查看获取
     * @param array $kidList 模板序列号 关键词顺序可以自由搭配（例如 [3,5,4] 或 [4,5,3]），最多支持5个，最少2个关键词组合
     * @param string $sceneDesc 服务场景描述，15个字以内
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function addTemplate(string $tid, array $kidList, string $sceneDesc = '')
    {
        $params = [
            'tid' => $tid,
            'kidList' => $kidList,
            'sceneDesc' => $sceneDesc,
        ];
        return $this->parseJSON('json', [self::API_SET_TEMPLATE_ADD, $params]);
    }

    /**
     * 删除模板消息
     * @param string $priTmplId
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function delTemplate(string $priTmplId)
    {
        $params = [
            'priTmplId' => $priTmplId
        ];
        return $this->parseJSON('json', [self::API_SET_TEMPLATE_DEL, $params]);
    }

    /**
     * 发送订阅消息
     * @param array $data
     * @return \EasyWeChat\Support\Collection|null
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    public function send(array $data = [])
    {
        $params = array_merge($this->message, $data);

        foreach ($params as $key => $value) {
            if (in_array($key, $this->required, true) && empty($value) && empty($this->message[$key])) {
                throw new InvalidArgumentException("Attribute '$key' can not be empty!");
            }

            $params[$key] = empty($value) ? $this->message[$key] : $value;
        }

        $params['data'] = $this->formatData($params['data']);

        $this->message = $this->messageBackup;

        return $this->parseJSON('json', [self::API_SUBSCRIBE_SEND, $params]);
    }

    /**
     * 设置订阅消息发送data
     * @param array $data
     * @return array
     */
    protected function formatData(array $data)
    {
        $return = [];

        foreach ($data as $key => $item) {
            if (is_scalar($item)) {
                $value = $item;
            } elseif (is_array($item) && !empty($item)) {
                if (isset($item['value'])) {
                    $value = strval($item['value']);
                } elseif (count($item) < 2) {
                    $value = array_shift($item);
                } else {
                    [$value] = $item;
                }
            } else {
                $value = 'error data item.';
            }

            $return[$key] = ['value' => $value];
        }

        return $return;
    }


    /**
     * Magic access..
     *
     * @param $method
     * @param $args
     * @return $this
     */
    public function __call($method, $args)
    {
        $map = [
            'template' => 'template_id',
            'templateId' => 'template_id',
            'uses' => 'template_id',
            'to' => 'touser',
            'receiver' => 'touser',
            'url' => 'page',
            'link' => 'page',
            'data' => 'data',
            'with' => 'data',
        ];

        if (0 === stripos($method, 'with') && strlen($method) > 4) {
            $method = lcfirst(substr($method, 4));
        }

        if (0 === stripos($method, 'and')) {
            $method = lcfirst(substr($method, 3));
        }

        if (isset($map[$method])) {
            $this->message[$map[$method]] = array_shift($args);
        }

        return $this;
    }

}