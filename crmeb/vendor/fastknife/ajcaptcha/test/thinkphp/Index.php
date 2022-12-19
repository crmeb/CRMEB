<?php

namespace app\controller;

use Fastknife\Exception\ParamException;
use Fastknife\Service\ClickWordCaptchaService;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\Service;
use think\exception\HttpResponseException;
use think\facade\Validate;
use think\Response;

/**
 * 该文件位于controller目录下
 * Class Index
 * @package app\controller
 */
class Index
{
    public function index()
    {
        try {
            $service = $this->getCaptchaService();
            $data = $service->get();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success($data);
    }

    /**
     * 一次验证
     */
    public function check()
    {
        $data = request()->post();
        try {
            $this->validate($data);
            $service = $this->getCaptchaService();
            $service->check($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success([]);
    }

    /**
     * 二次验证
     */
    public function verification()
    {
        $data = request()->post();
        try {
            $this->validate($data);
            $service = $this->getCaptchaService();
            $service->verification($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success([]);
    }

    protected function getCaptchaService()
    {
        $captchaType = request()->post('captchaType', null);
        $config = config('captcha');
        switch ($captchaType) {
            case "clickWord":
                $service = new ClickWordCaptchaService($config);
                break;
            case "blockPuzzle":
                $service = new BlockPuzzleCaptchaService($config);
                break;
            default:
                throw new ParamException('captchaType参数不正确！');
        }
        return $service;
    }

    protected function validate($data)
    {
        $rules = [
            'token' => ['require'],
            'pointJson' => ['require']
        ];
        $validate = Validate::rule($rules)->failException(true);
        $validate->check($data);
    }

    protected function success($data)
    {
        $response = [
            'error' => false,
            'repCode' => '0000',
            'repData' => $data,
            'repMsg' => null,
            'success' => true,
        ];
        throw new HttpResponseException(Response::create($response, 'json'));
    }


    protected function error($msg)
    {
        $response = [
            'error' => true,
            'repCode' => '6111',
            'repData' => null,
            'repMsg' => $msg,
            'success' => false,
        ];
        throw new HttpResponseException(Response::create($response, 'json'));
    }


}
