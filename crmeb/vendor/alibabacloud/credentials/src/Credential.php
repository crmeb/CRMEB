<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Credential\Config;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Class Credential
 *
 * @package AlibabaCloud\Credentials
 *
 * @mixin AccessKeyCredential
 * @mixin BearerTokenCredential
 * @mixin EcsRamRoleCredential
 * @mixin RamRoleArnCredential
 * @mixin RsaKeyPairCredential
 */
class Credential
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $types = [
        'access_key'   => AccessKeyCredential::class,
        'sts'          => StsCredential::class,
        'ecs_ram_role' => EcsRamRoleCredential::class,
        'ram_role_arn' => RamRoleArnCredential::class,
        'rsa_key_pair' => RsaKeyPairCredential::class,
    ];

    /**
     * @var AccessKeyCredential|BearerTokenCredential|EcsRamRoleCredential|RamRoleArnCredential|RsaKeyPairCredential
     */
    protected $credential;

    /**
     * @var string
     */
    protected $type;

    /**
     * Credential constructor.
     *
     * @param array|Config $config
     *
     * @throws ReflectionException
     */
    public function __construct($config = [])
    {
        if ($config instanceof Config) {
            $config = $this->parse($config);
        }
        if ($config !== []) {
            $this->config = array_change_key_case($config);
            $this->parseConfig();
        } else {
            $this->credential = Credentials::get()->getCredential();
        }
    }

    /**
     * @param Config $config
     *
     * @return array
     */
    private function parse($config)
    {
        $config = get_object_vars($config);
        $res    = [];
        foreach ($config as $key => $value) {
            $res[$this->toUnderScore($key)] = $value;
        }
        return $res;
    }

    private function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $str);
        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }

    /**
     * @throws ReflectionException
     */
    private function parseConfig()
    {
        if (!isset($this->config['type'])) {
            throw  new InvalidArgumentException('Missing required type option');
        }

        $this->type = $this->config['type'];
        if (!isset($this->types[$this->type])) {
            throw  new InvalidArgumentException(
                'Invalid type option, support: ' .
                implode(', ', array_keys($this->types))
            );
        }

        $class      = new ReflectionClass($this->types[$this->type]);
        $parameters = [];
        /**
         * @var $parameter ReflectionParameter
         */
        foreach ($class->getConstructor()->getParameters() as $parameter) {
            $parameters[] = $this->getValue($parameter);
        }

        $this->credential = $class->newInstance(...$parameters);
    }

    /**
     * @param ReflectionParameter $parameter
     *
     * @return string|array
     * @throws ReflectionException
     */
    protected function getValue(ReflectionParameter $parameter)
    {
        if ($parameter->name === 'config' || $parameter->name === 'credential') {
            return $this->config;
        }

        foreach ($this->config as $key => $value) {
            if (strtolower($parameter->name) === $key) {
                return $value;
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new InvalidArgumentException("Missing required {$parameter->name} option in config for {$this->type}");
    }

    /**
     * @return AccessKeyCredential|BearerTokenCredential|EcsRamRoleCredential|RamRoleArnCredential|RsaKeyPairCredential
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->credential->$name($arguments);
    }
}
