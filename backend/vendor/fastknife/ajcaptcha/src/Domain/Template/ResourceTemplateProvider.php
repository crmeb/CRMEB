<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template;

use Fastknife\Domain\Logic\BaseData;
use Fastknife\Domain\Logic\Cache;
use Fastknife\Domain\Vo\OffsetVo;
use Fastknife\Domain\Vo\TemplateVo;
use Fastknife\Utils\RandomUtils;

class ResourceTemplateProvider extends BaseData implements TemplateProviderInterface
{
    private $templates;
    private $isCachePixel = false;
    
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(array $config)
    {
        $this->templates = $config['block_puzzle']['templates'] ?? [];
        $this->isCachePixel = $config['block_puzzle']['is_cache_pixel'] ?? false;
    }
    
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getTemplateVo(int $bgWidth, int $bgHeight): TemplateVo
    {
        // 初始偏移量，让模板图在背景的右1/2位置
        $bgHalfWidth = intval($bgWidth / 2);

        // 随机获取一张图片
        $src = $this->getRandImage($this->getTemplateImages($this->templates));

        $templateVo = new TemplateVo($src);
        
        // 处理像素缓存
        $this->handlePixelCache($templateVo, $src);

        // 随机获取偏移量
        // x: 在右半部分随机
        $maxOffsetX = $bgHalfWidth - imagesx($templateVo->image) - 1;
        // 防止负数（图片太宽时）
        $maxOffsetX = max(0, $maxOffsetX);
        
        $offset = RandomUtils::getRandomInt(0, $maxOffsetX);
        
        $templateVo->setOffset(new OffsetVo($offset + $bgHalfWidth, 0));
        
        return $templateVo;
    }
    
    public function getInterfereVo(int $bgWidth, int $bgHeight, TemplateVo $targetVo): TemplateVo
    {
        // 干扰图逻辑 (复用旧逻辑)
        // 1. 随机选一张图
        $src = $this->getRandImage($this->getTemplateImages($this->templates));
        $interfereVo = new TemplateVo($src);
        
        // 处理像素缓存
        $this->handlePixelCache($interfereVo, $src);
        
        // 2. 随机位置 (要避开目标)
        $width = imagesx($interfereVo->image);
        $bgHalfWidth = intval($bgWidth / 2);
        
        $maxOffsetX = $bgHalfWidth - $width - 5;
        $maxOffsetX = max(0, $maxOffsetX);
        
        $x = RandomUtils::getRandomInt(5, $maxOffsetX); // 左侧
        
        // y 轴随机 (0 ~ bgHeight - tmplHeight)
        $height = imagesy($interfereVo->image);
        $maxOffsetY = $bgHeight - $height;
        $maxOffsetY = max(0, $maxOffsetY);
        
        // 确保 Y 轴不与目标重叠 (minDistance = height)
        $minDistance = $height;
        $targetY = $targetVo->offset->y;
        $maxRetries = 20;
        $retry = 0;
        
        do {
            $y = RandomUtils::getRandomInt(0, $maxOffsetY);
            $retry++;
        } while (abs($y - $targetY) < $minDistance && $retry < $maxRetries);
        
        $interfereVo->setOffset(new OffsetVo($x, $y));
        
        return $interfereVo;
    }


    protected function getTemplateImages($templates = [])
    {
        $dir = dirname(__DIR__, 3) . '/resources/defaultImages/jigsaw/slidingBlock/';
        return $this->getDefaultImage($dir, $templates);
    }
    
    /**
     * 处理像素缓存逻辑
     */
    private function handlePixelCache(TemplateVo $vo, string $src)
    {
        // 只有开启了缓存且 Cache 实例已注入才执行
        if (!$this->isCachePixel || !$this->cache) {
            return;
        }

        // 生成缓存 Key (避免文件名冲突)
        $cacheKey = 'aj_pixel_' . md5($src);
        
        // 尝试从缓存读取
        $pickMaps = $this->cache->get($cacheKey);
        
        if (!empty($pickMaps) && is_array($pickMaps)) {
            $vo->setPickMaps($pickMaps);
            return;
        }
        
        // 缓存未命中，计算并写入
        $vo->preparePickMaps();
        $pickMaps = $vo->getPickMaps();
        
        // 永久缓存 (或者设置一个较长的过期时间，如 30 天)
        $this->cache->set($cacheKey, $pickMaps, 86400 * 30);
    }
}
