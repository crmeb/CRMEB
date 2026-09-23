<?php

namespace Overtrue\Socialite;

use Closure;
use JetBrains\PhpStorm\Pure;

class SocialiteManager implements Contracts\FactoryInterface
{
    protected Config $config;

    protected array $resolved = [];

    protected static array $customCreators = [];

    protected const PROVIDERS = [
        Providers\Apple::NAME => Providers\Apple::class,
        Providers\Alipay::NAME => Providers\Alipay::class,
        Providers\Azure::NAME => Providers\Azure::class,
        Providers\Coding::NAME => Providers\Coding::class,
        Providers\DingTalk::NAME => Providers\DingTalk::class,
        Providers\DouYin::NAME => Providers\DouYin::class,
        Providers\Douban::NAME => Providers\Douban::class,
        Providers\Facebook::NAME => Providers\Facebook::class,
        Providers\FeiShu::NAME => Providers\FeiShu::class,
        Providers\Figma::NAME => Providers\Figma::class,
        Providers\GitHub::NAME => Providers\GitHub::class,
        Providers\Gitee::NAME => Providers\Gitee::class,
        Providers\Google::NAME => Providers\Google::class,
        Providers\Lark::NAME => Providers\Lark::class,
        Providers\Line::NAME => Providers\Line::class,
        Providers\Linkedin::NAME => Providers\Linkedin::class,
        Providers\OpenWeWork::NAME => Providers\OpenWeWork::class,
        Providers\Outlook::NAME => Providers\Outlook::class,
        Providers\QCloud::NAME => Providers\QCloud::class,
        Providers\QQ::NAME => Providers\QQ::class,
        Providers\Taobao::NAME => Providers\Taobao::class,
        Providers\Tapd::NAME => Providers\Tapd::class,
        Providers\TouTiao::NAME => Providers\TouTiao::class,
        Providers\WeChat::NAME => Providers\WeChat::class,
        Providers\WeWork::NAME => Providers\WeWork::class,
        Providers\Weibo::NAME => Providers\Weibo::class,
        Providers\XiGua::NAME => Providers\XiGua::class,
        Providers\PayPal::NAME => Providers\PayPal::class,
        Providers\Twitter::NAME => Providers\Twitter::class,
    ];

    #[Pure]
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    public function config(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function create(string $name): Contracts\ProviderInterface
    {
        $name = \strtolower($name);

        if (! isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->createProvider($name);
        }

        return $this->resolved[$name];
    }

    public function extend(string $name, Closure $callback): self
    {
        self::$customCreators[\strtolower($name)] = $callback;

        return $this;
    }

    public function getResolvedProviders(): array
    {
        return $this->resolved;
    }

    public function buildProvider(string $provider, array $config): Contracts\ProviderInterface
    {
        $instance = new $provider($config);

        $instance instanceof Contracts\ProviderInterface || throw new Exceptions\InvalidArgumentException("The {$provider} must be instanceof ProviderInterface.");

        return $instance;
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function createProvider(string $name): Contracts\ProviderInterface
    {
        $config = $this->config->get($name, []);
        $provider = $config['provider'] ?? $name;

        if (isset(self::$customCreators[$provider])) {
            return $this->callCustomCreator($provider, $config);
        }

        if (! $this->isValidProvider($provider)) {
            throw new Exceptions\InvalidArgumentException("Provider [{$name}] not supported.");
        }

        return $this->buildProvider(self::PROVIDERS[$provider] ?? $provider, $config);
    }

    protected function callCustomCreator(string $name, array $config): Contracts\ProviderInterface
    {
        return self::$customCreators[$name]($config);
    }

    protected function isValidProvider(string $provider): bool
    {
        return isset(self::PROVIDERS[$provider]) || \is_subclass_of($provider, Contracts\ProviderInterface::class);
    }
}
