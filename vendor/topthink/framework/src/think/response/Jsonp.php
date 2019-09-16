<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\response;

use think\Request;
use think\Response;

/**
 * Jsonp Response
 */
class Jsonp extends Response
{
    // 输出参数
    protected $options = [
        'var_jsonp_handler'     => 'callback',
        'default_jsonp_handler' => 'jsonpReturn',
        'json_encode_param'     => JSON_UNESCAPED_UNICODE,
    ];

    protected $contentType = 'application/javascript';

    protected $request;

    public function __construct(Request $request, $data = '', int $code = 200)
    {
        parent::__construct($data, $code);

        $this->request = $request;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return string
     * @throws \Exception
     */
    protected function output($data): string
    {
        try {
            // 返回JSON数据格式到客户端 包含状态信息 [当url_common_param为false时是无法获取到$_GET的数据的，故使用Request来获取<xiaobo.sun@qq.com>]
            $var_jsonp_handler = $this->request->param($this->options['var_jsonp_handler'], "");
            $handler           = !empty($var_jsonp_handler) ? $var_jsonp_handler : $this->options['default_jsonp_handler'];

            $data = json_encode($data, $this->options['json_encode_param']);

            if (false === $data) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

            $data = $handler . '(' . $data . ');';

            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }

}
