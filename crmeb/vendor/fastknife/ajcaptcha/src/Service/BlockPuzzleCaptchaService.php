<?php
declare(strict_types=1);

namespace Fastknife\Service;

use Fastknife\Domain\Vo\PointVo;
use Fastknife\Utils\RandomUtils;

class BlockPuzzleCaptchaService extends Service
{
    /**
     * 获取验证码图片信息
     * @return array
     */
    public function get(): array
    {
        $cacheEntity = $this->factory->getCacheInstance();
        $blockImage = $this->factory->makeBlockImage();
        $blockImage->run();
        $data = [
            'originalImageBase64' => $blockImage->response(),
            'jigsawImageBase64' => $blockImage->response('template'),
            'secretKey' => RandomUtils::getRandomCode(16, 3),
            'token' => RandomUtils::getUUID(),
        ];
        //缓存
        $cacheEntity->set($data['token'], [
            'secretKey' => $data['secretKey'],
            'point' => $blockImage->getPoint()
        ], 7200);
        return $data;
    }




    /**
     * 验证
     * @param string $token
     * @param string $pointJson
     * @param null $callback
     */
    public function validate( $token,  $pointJson, $callback = null)
    {
        //获取并设置 $this->originData
        $this->setOriginData($token);

        //数据处理类
        $blockData = $this->factory->makeBlockData();

        //解码出来的前端坐标
        $targetPoint = $this->encodePoint($this->originData['secretKey'], $pointJson);;
        $targetPoint = new PointVo($targetPoint['x'], $targetPoint['y']);

        //检查
        $blockData->check($this->originData['point'], $targetPoint);
        if($callback instanceof \Closure){
            $callback();
        }
    }
}
