<?php
declare(strict_types=1);

namespace Fastknife\Domain;

use Fastknife\Domain\Logic\BaseData;
use Fastknife\Domain\Logic\BaseImage;
use Fastknife\Domain\Logic\BlockImage;
use Fastknife\Domain\Logic\Cache;
use Fastknife\Domain\Logic\WordImage;
use Fastknife\Domain\Logic\BlockData;
use Fastknife\Domain\Logic\WordData;
use Fastknife\Domain\Vo\ImageVo;
use Intervention\Image\ImageManagerStatic;

class Factory
{
    protected $config;

    protected $cacheInstance;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return BlockImage
     */
    public function makeBlockImage(): BlockImage
    {
        $data = new BlockData();
        $image = new BlockImage();
        $this->setCommon($image, $data);
        $this->setBlock($image, $data);
        return $image;
    }

    /**
     * @return WordImage
     */
    public function makeWordImage(): WordImage
    {
        $data = new WordData();
        $image = new WordImage();
        $this->setCommon($image, $data);
        $this->setWord($image, $data);
        return $image;
    }


    /**
     * 设置公共配置
     * @param BaseImage $image
     * @param BaseData $data
     */
    protected function setCommon(BaseImage $image, BaseData $data)
    {
        //固定驱动，少量图片处理场景gd性能远远大于imagick
        ImageManagerStatic::configure(['driver' => 'gd']);

        //获得字体数据
        $fontFile = $data->getFontFile($this->config['font_file']);
        $image
            ->setFontFile($fontFile)
            ->setWatermark($this->config['watermark']);
    }

    /**
     * 设置滑动验证码的配置
     * @param BlockImage $image
     * @param BlockData $data
     */
    protected function setBlock(BlockImage $image, BlockData $data)
    {
        //设置背景
        $backgroundVo = $data->getBackgroundVo($this->config['block_puzzle']['backgrounds']);
        $image->setBackgroundVo($backgroundVo);

        $templateVo = $data->getTemplateVo($backgroundVo, $this->config['block_puzzle']['templates']);

        $image->setTemplateVo($templateVo);

        $pixelMaps = [$backgroundVo, $templateVo];
        if (
            isset($this->config['block_puzzle']['is_interfere']) &&
            $this->config['block_puzzle']['is_interfere'] == true
        ) {
            $interfereVo = $data->getInterfereVo($backgroundVo, $templateVo, $this->config['block_puzzle']['templates']);
            $image->setInterfereVo($interfereVo);
            $pixelMaps[] = $interfereVo;
        }

        if (
            isset($this->config['block_puzzle']['is_cache_pixel']) &&
            $this->config['block_puzzle']['is_cache_pixel'] === true
        ) {
            $cache = $this->getCacheInstance();
            foreach ($pixelMaps as $vo) {
                /**@var ImageVo $vo * */
                $key = 'image_pixel_map_' . $vo->src;
                $result = $cache->get($key);
                if (!empty($result) && is_array($result)) {
                    $vo->setPickMaps($result);
                } else {
                    $vo->preparePickMaps();
                    $vo->setFinishCallback(function (ImageVo $imageVo) use ($cache, $key) {
                        $cache->set($key, $imageVo->getPickMaps(), 0);
                    });
                }
            }
        }


    }

    /**
     * 设置文字验证码的配置
     * @param WordImage $image
     * @param WordData $data
     */
    protected function setWord(WordImage $image, WordData $data)
    {
        //设置背景
        $backgroundVo = $data->getBackgroundVo($this->config['click_world']['backgrounds']);
        $image->setBackgroundVo($backgroundVo);

        //随机文字坐标
        $pointList = $data->getPointList(
            $image->getBackgroundVo()->image->getWidth(),
            $image->getBackgroundVo()->image->getHeight(),
            3
        );
        $worldList = $data->getWordList(count($pointList));
        $image
            ->setWordList($worldList)
            ->setWordList($worldList)
            ->setPoint($pointList);
    }

    /**
     * 创建缓存实体
     */
    public function getCacheInstance(): Cache
    {
        if (empty($this->cacheInstance)) {
            $this->cacheInstance = new Cache($this->config['cache']);
        }
        return $this->cacheInstance;
    }

    public function makeWordData(): WordData
    {
        return new WordData();
    }

    public function makeBlockData(): BlockData
    {
        return (new BlockData())->setFaultOffset($this->config['block_puzzle']['offset']);
    }
}
