<?php
declare(strict_types=1);

namespace Fastknife\Service;

use Fastknife\Exception\ParamException;
use Fastknife\Utils\AesUtils;
use Fastknife\Utils\RandomUtils;

class ClickWordCaptchaService extends Service
{


    /**
     * 获取文字验证码
     */
    public function get(): array
    {
        $cacheEntity = $this->factory->getCacheInstance();
        $wordImage = $this->factory->makeWordImage();
        //执行创建
        $wordImage->run();
        $data = [
            'originalImageBase64' => $wordImage->response(),
            'secretKey' => RandomUtils::getRandomCode(16, 3),
            'token' => RandomUtils::getUUID(),
            'wordList' => $wordImage->getWordList()
        ];
        //缓存
        $cacheEntity->set($data['token'], [
            'secretKey' => $data['secretKey'],
            'point' => $wordImage->getPoint()
        ],7200);
        return $data;
    }


    /**
     * 验证
     * @param $token
     * @param $pointJson
     * @param null $callback
     */
    public function validate($token, $pointJson, $callback = null)
    {
        //获取并设置 $this->originData
        $this->setOriginData($token);

        //数据实例
        $wordData = $this->factory->makeWordData();
        //解码出来的前端坐标
        $pointJson = $this->encodePoint($this->originData['secretKey'], $pointJson);
        $targetPointList = $wordData->array2Point($pointJson);

        //检查
        $wordData->check($this->originData['point'], $targetPointList);
        if ($callback instanceof \Closure) {
            $callback();
        }
    }



}
