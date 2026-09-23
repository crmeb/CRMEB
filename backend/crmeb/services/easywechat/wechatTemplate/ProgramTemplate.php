<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\services\easywechat\wechatTemplate;

use crmeb\services\easywechat\AbstractApiClient;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 公众号模板消息
 * Class ProgramTemplate
 * @package crmeb\services\easywechat\wechatTemplate
 * @method $this template(string $template_id) 设置模板id
 * @method $this andTemplateId(string $template_id) 设置模板id
 * @method $this to(string $touser) 设置opendid
 * @method $this andReceiver(string $touser) 设置opendid
 * @method $this with(array $data) 设置发送内容
 * @method $this andData(array $data) 设置发送内容
 * @method $this url(string $url) 设置跳转链接
 */
class ProgramTemplate extends AbstractApiClient
{
    /**
     * Attributes
     * @var array
     */
    protected $message = [
        'touser' => '',
        'template_id' => '',
        'url' => '',
        'data' => [],
        'miniprogram' => '',
    ];

    /**
     * 默认字体颜色
     * @var string
     */
    protected $defaultColor = '';

    /**
     * Required attributes.
     *
     * @var array
     */
    protected $required = ['touser', 'template_id'];

    /**
     * Message backup.
     *
     * @var array
     */
    protected $messageBackup;

    const API_SEND_NOTICE = 'cgi-bin/message/template/send';
    const API_SET_INDUSTRY = 'cgi-bin/template/api_set_industry';
    const API_ADD_TEMPLATE = 'cgi-bin/template/api_add_template';
    const API_GET_INDUSTRY = 'cgi-bin/template/get_industry';
    const API_GET_ALL_PRIVATE_TEMPLATE = 'cgi-bin/template/get_all_private_template';
    const API_DEL_PRIVATE_TEMPLATE = 'cgi-bin/template/del_private_template';

    public function __construct(HttpClientInterface $api)
    {
        parent::__construct($api);
        $this->messageBackup = $this->message;
    }

    /**
     * Set default color.
     *
     * @param string $color example: #0f0f0f
     *
     * @return $this
     */
    public function defaultColor($color)
    {
        $this->defaultColor = $color;

        return $this;
    }

    /**
     * Set miniprogram.
     * @param $data
     * @return $this
     */
    public function setMiniprogram($data)
    {
        $this->message['miniprogram'] = $data;

        return $this;
    }

    /**
     * Set industry.
     *
     * @param int $industryOne
     * @param int $industryTwo
     *
     * @return array
     */
    public function setIndustry($industryOne, $industryTwo)
    {
        return $this->postJson(self::API_SET_INDUSTRY, [
            'industry_id1' => $industryOne,
            'industry_id2' => $industryTwo,
        ]);
    }

    /**
     * Get industry.
     *
     * @return array
     */
    public function getIndustry()
    {
        return $this->postJson(self::API_GET_INDUSTRY);
    }

    /**
     * Add a template and get template ID.
     * @param $shortId
     * @param array|null $content 可选的关键字列表，不传则使用模板默认关键字
     * @return array
     */
    public function addTemplate($shortId, $content = null)
    {
        $params = ['template_id_short' => $shortId];
        if ($content !== null) {
            $params['keyword_name_list'] = $content;
        }
        return $this->postJson(self::API_ADD_TEMPLATE, $params);
    }

    /**
     * Get private templates.
     *
     * @return array
     */
    public function getPrivateTemplates()
    {
        return $this->postJson(self::API_GET_ALL_PRIVATE_TEMPLATE);
    }

    /**
     * Delete private template.
     *
     * @param string $templateId
     *
     * @return array
     */
    public function deletePrivateTemplate($templateId)
    {
        return $this->postJson(self::API_DEL_PRIVATE_TEMPLATE, [
            'template_id' => $templateId,
        ]);
    }

    /**
     * Send a notice message.
     *
     * @param $data
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function send($data = [])
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

        return $this->postJson(self::API_SEND_NOTICE, $params);
    }

    /**
     * Magic access..
     *
     * @param string $method
     * @param array $args
     *
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
            'url' => 'url',
            'link' => 'url',
            'data' => 'data',
            'with' => 'data',
            'formId' => 'form_id',
            'prepayId' => 'form_id',
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

    /**
     * Format template data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function formatData($data)
    {
        $return = [];

        foreach ($data as $key => $item) {
            if (is_scalar($item)) {
                $value = $item;
                $color = $this->defaultColor;
            } elseif (is_array($item) && !empty($item)) {
                if (isset($item['value'])) {
                    $value = strval($item['value']);
                    $color = empty($item['color']) ? $this->defaultColor : strval($item['color']);
                } elseif (count($item) < 2) {
                    $value = array_shift($item);
                    $color = $this->defaultColor;
                } else {
                    [$value, $color] = $item;
                }
            } else {
                $value = 'error data item.';
                $color = $this->defaultColor;
            }

            $return[$key] = array_filter([
                'value' => $value,
                'color' => $color,
            ], static function ($v) {
                return $v !== '';
            });
        }

        return $return;
    }
}
