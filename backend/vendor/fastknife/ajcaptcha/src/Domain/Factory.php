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
use Fastknife\Domain\Template\DrawingTemplateProvider;
use Fastknife\Domain\Template\ResourceTemplateProvider;
use Fastknife\Domain\Template\TemplateProviderInterface;
use Fastknife\Domain\Vo\ImageVo;

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
        $data = $this->makeBlockData();
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
        $data = $this->makeWordData();
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

        // 使用注入的 Provider 获取 TemplateVo
        $templateVo = $data->getTemplateVo($backgroundVo);

        $image->setTemplateVo($templateVo);

        // 干扰图逻辑 (已适配 Drawing/Resource 双模式)
        if (
            isset($this->config['block_puzzle']['is_interfere']) &&
            $this->config['block_puzzle']['is_interfere'] == true
        ) {
            $interfereVo = $data->getInterfereVo($backgroundVo, $templateVo);
            $image->setInterfereVo($interfereVo);
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
        $backgroundVo = $data->getBackgroundVo($this->config['click_word']['backgrounds']);
        $image->setBackgroundVo($backgroundVo);

        // 干扰字数量
        $distractNum = $this->config['click_word']['distract_num'] ?? 2;
        // 目标字数量
        $wordNum = $this->config['click_word']['word_num'] ?? 3;
        
        // 限制
        if($wordNum > 5) $wordNum = 5;
        if($wordNum < 2) $wordNum = 2;

        // 总字数
        $totalNum = $wordNum + $distractNum;

        // 获取文字列表 (目标 + 干扰)
        $wordList = $data->getWordList($totalNum);

        // 随机文字坐标 (碰撞检测)
        $pointList = $data->getPointList(
            imagesx($image->getBackgroundVo()->image),
            imagesy($image->getBackgroundVo()->image),
            $totalNum
        );
        
        // 图标替换逻辑
        $iconMap = $this->config['click_word']['icons'] ?? [];
        $displayList = $wordList; // 前端显示列表
        $drawList = $wordList;    // 图片绘制列表
        $iconIndexes = [];        // 图标索引列表
        
        // 检查是否启用图标功能
        $enableIcons = $this->config['click_word']['enable_icons'] ?? true;
        
        if ($enableIcons && !empty($iconMap)) {
            $iconChars = array_keys($iconMap);
            
            // 获取图标模式
            $iconMode = $this->config['click_word']['icon_mode'] ?? 'random';
            $minIcons = $this->config['click_word']['min_icons'] ?? 0;
            $maxIcons = $this->config['click_word']['max_icons'] ?? 2;
            $maxIcons = min($maxIcons, $totalNum);
            
            // 根据模式决定图标数量
            $iconCount = 0;
            switch ($iconMode) {
                case 'always':
                    // 每次都有图标，使用最大值
                    $iconCount = $maxIcons;
                    break;
                case 'never':
                    // 从不出现图标
                    $iconCount = 0;
                    break;
                case 'random':
                default:
                    // 随机出现，在 min 到 max 之间随机
                    $iconCount = \Fastknife\Utils\RandomUtils::getRandomInt($minIcons, $maxIcons);
                    break;
            }
            
            // 用于去重，避免同一位置被多次替换
            $replacedIndexes = [];
            
            for ($i = 0; $i < $iconCount; $i++) {
                // 随机选择替换位置（仅在目标字范围内）
                $attempts = 0;
                do {
                    $index = \Fastknife\Utils\RandomUtils::getRandomInt(0, $wordNum - 1);
                    $attempts++;
                } while (in_array($index, $replacedIndexes) && $attempts < 100);
                
                // 如果超过尝试次数，跳过这次替换
                if ($attempts >= 100) {
                    continue;
                }
                
                $replacedIndexes[] = $index;
                
                // 随机选择图标
                $iconChar = $iconChars[\Fastknife\Utils\RandomUtils::getRandomInt(0, count($iconChars) - 1)];
                $iconLabel = $iconMap[$iconChar] ?? '';
                
                // 绘制列表：使用真实 Unicode 图标
                $drawList[$index] = $iconChar;
                
                // 显示列表：使用尖括号包裹的文字说明
                if ($iconLabel !== '') {
                    $displayList[$index] = '<' . $iconLabel . '>';
                } else {
                    $displayList[$index] = $iconChar;
                }
                
                // 记录图标索引
                $iconIndexes[] = $index;
            }
        }
        
        // WordImage 需要渲染所有字
        $image
            ->setWordList($displayList)      // 前端显示用
            ->setDrawWordList($drawList)     // 图片绘制用
            ->setIconIndexes($iconIndexes)   // 图标索引
            ->setPoint($pointList);
        
        // 设置图标字体放大比例
        $iconFontSizeScale = $this->config['click_word']['icon_font_size_scale'] ?? 1.3;
        $image->setIconFontSizeScale($iconFontSizeScale);
            
        // 我们可以在 WordImage 中增加一个属性 targetCount
        $image->setTargetCount($wordNum);
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
        $blockData = new BlockData();
        $blockData->setFaultOffset($this->config['block_puzzle']['offset']);
        
        // 注入 TemplateProvider
        $provider = $this->makeTemplateProvider();
        $blockData->setTemplateProvider($provider);
        
        return $blockData;
    }

    public function makeTemplateProvider(): TemplateProviderInterface
    {
        $mode = $this->config['block_puzzle']['mode'] ?? 'drawing';
        if ($mode === 'resource') {
            $provider = new ResourceTemplateProvider($this->config);
            $provider->setCache($this->getCacheInstance());
            return $provider;
        }
        return new DrawingTemplateProvider($this->config);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

}
