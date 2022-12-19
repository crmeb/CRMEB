<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Fastknife\Exception\ParamException;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;
use Fastknife\Service\Service;
use Illuminate\Support\Facades\Request;

class IndexController
{
    public function index()
    {
        try {
            $service = $this->getCaptchaService();
            $data = $service->get();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        return $this->success($data);
    }

    /**
     * 一次验证
     * @return array
     */
    public function check()
    {
        try {
            $data = $this->validate();
            $service = $this->getCaptchaService();
            $service->check($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
           return  $this->error($e->getMessage());
        }
        return $this->success([]);
    }

    /**
     * 二次验证
     * @return array
     */
    public function verification()
    {
        try {
            $data = $this->validate();
            $service = $this->getCaptchaService();
            $service->verification($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
           return  $this->error($e->getMessage());
        }
        return $this->success([]);
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

    protected function validate()
    {
        return Request::instance()->validate([
            'token' => 'bail|required',
            'pointJson' => 'required',
        ]);

    }

    protected function success($data)
    {
        return [
            'error' => false,
            'repCode' => '0000',
            'repData' => $data,
            'repMsg' => null,
            'success' => true,
        ];
    }


    protected function error($msg)
    {
        return [
            'error' => true,
            'repCode' => '6111',
            'repData' => null,
            'repMsg' => $msg,
            'success' => false,
        ];
    }

}
