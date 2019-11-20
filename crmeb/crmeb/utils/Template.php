<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/4/3 16:36
 */

namespace crmeb\utils;

use crmeb\exceptions\AuthException;
use crmeb\traits\LogicTrait;
use think\app\Url;

/** 消息发送
 * Class Template
 * @package crmeb\utils
 * @method $this setTemplateData(string $templateData) 设置发送模板消息数据
 * @method $this setTemplateOpenId(string $templateOpenId) 设置发送模板消息openid
 * @method $this setTemplateFormId(string $templateFormId) 设置发送模板消息formid
 * @method $this setTemplateDefaultColor(string $templateDefaultColor) 设置发送模板消息默认背景颜色
 * @method $this setTemplateCode(string $templateCode) 设置模板id
 * @method $this setSendType($sendType) 设置发送类型句柄 1 = 小程序 ， 2 = 公众号
 * @method $this setDefaultData($defaultData) 设置默认数据
 * @method $this setTemplateUrl(Url $url, string $sux = '') 设置跳转Url
 * @method $this routine() 设置当前发送类型句柄为 小程序
 * @method $this wechat() 设置当前发送类型句柄为 公众号
 */
class Template
{
    use LogicTrait;

    /**
     * 注册服务 会自动添加$providers对应的key名称方法方便设置$sendType
     * @var array
     */
    protected $providers = [
        'routine' => \crmeb\services\ProgramTemplateService::class,
        'wechat' => \crmeb\services\WechatTemplateService::class,
    ];

    /**
     * 可调用方法规则
     * @var array 'defaultData'=>[[],'array'] 生成的方法为 setDefaultData(array $value)
     */
    protected $propsRule = [
        'defaultData' => [null, 'string'],
        'templateCode' => [null, 'string'],
        'templateData' => [[], 'array'],
        'templateUrl' => [null, 'callable', 'postpositionUrl'],
        'templateFormId' => [null, 'string'],
        'sendType' => [null, 'string'],
        'templateOpenId' => [null, 'string'],
        'templateOpenId' => [null, 'string'],
    ];

    /**
     * 默认参数
     * @var array | int | bool
     */
    protected $defaultData;

    /**
     * 模板编号
     * @var string
     */
    protected $templateCode;

    /**
     * 模板消息数据
     * @var array
     */
    protected $templateData = [];

    /**
     * 模板消息跳转路径
     * @var string
     */
    protected $templateUrl = '';

    /**
     * 模板消息formid 为小程序提供
     * @var string
     */
    protected $templateFormId = '';

    /**
     * 发送类型 对应 $providers key
     * @var string | int
     */
    protected $sendType;

    /**
     * 接收人openid 小程序 和 公众号使用
     * @var string
     */
    protected $templateOpenId;

    /**
     * 模板消息默认颜色
     * @var string
     */
    protected $templateDefaultColor;

    /**
     * 实例化后操作
     */
    public function bool()
    {

    }

    /**
     * 自定义方法 后置改变Url
     * @param Url $url
     * @param string $suffix
     * @return string
     */
    public function postpositionUrl($url, string $suffix = '')
    {
        if($url instanceof Url)
            $url = $url->suffix($suffix)->domain(true)->build();
        return $url;
    }

    /**
     * 验证参数
     */
    protected function validate()
    {
        $keys = array_keys($this->providers);
        if (is_string($this->sendType)) {
            if (!in_array($this->sendType, $keys))
                throw new AuthException('设置的发送类型句柄不存在:' . $this->sendType);
        } elseif (is_int($this->sendType)) {
            if ($this->sendType > count($keys))
                throw new AuthException('设置的发送类型句柄不存在：' . $this->sendType);
            $this->sendType = $keys[$this->sendType - 1];
        }

        if (!$this->sendType)
            throw new AuthException('请设置发送类型句柄');

        if (!$this->templateData)
            throw new AuthException('请设置发送模板数据');

        if (!$this->templateOpenId)
            throw new AuthException('请设置发送模板OPENID');
    }

    /**
     * 发送消息
     * @param bool $excep 是否抛出异常
     * @return bool|null
     */
    public function send(bool $excep = false)
    {
        try {

            $this->validate();

            $resource = null;
            switch ($this->sendType) {
                case 'routine':
                    $resource = self::$instance->routine->sendTemplate(
                        $this->templateCode,
                        $this->templateOpenId,
                        $this->templateData,
                        $this->templateFormId,
                        $this->templateUrl,
                        $this->templateDefaultColor
                    );
                    break;
                case 'wechat':
                    $resource = self::$instance->wechat->sendTemplate(
                        $this->templateOpenId,
                        $this->templateCode,
                        $this->templateData,
                        $this->templateUrl,
                        $this->templateDefaultColor
                    );
                    break;
                default:
                    $resource = false;
                    break;
            }

            $this->clear();

            return $resource;
        } catch (\Throwable $e) {
            if ($excep)
                throw new AuthException($e->getMessage());
            return false;
        }
    }

    /**
     * 恢复默认值
     * @return $this
     */
    protected function clear()
    {
        $this->templateOpenId = null;
        $this->defaultData = null;
        $this->templateDefaultColor = null;
        $this->templateData = [];
        $this->templateUrl = null;
        $this->sendType = null;
        $this->templateFormId = null;
        $this->templateCode = null;
        return $this;
    }

    public function __destruct()
    {
        $this->clear();
    }

}