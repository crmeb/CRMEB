<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\printer;

use crmeb\basic\BaseStorage;

/**
 * Class BasePrinter
 * @package crmeb\services\printer
 */
abstract class BasePrinter extends BaseStorage
{

    /**
     * token句柄
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * 打印内容
     * @var string
     */
    protected $printerContent;

    /**
     * 打印次数
     * @var string
     */
    protected $times;

    /**
     * 同一设备提交任务的稳定标识
     * @var string
     */
    protected string $jobId = '';

    /**
     * 请求已发送但无法确认云端是否接单
     * @var bool
     */
    protected bool $submissionUncertain = false;

    /**
     * 设置提交标识，重试时沿用同一标识
     * @param string $jobId 任务标识
     * @return static
     */
    public function setJobId(string $jobId): self
    {
        $this->jobId = $jobId;
        return $this;
    }

    /**
     * 是否需要人工确认提交结果，避免重复出纸
     * @return bool
     */
    public function isSubmissionUncertain(): bool
    {
        return $this->submissionUncertain;
    }

    /**
     * BasePrinter constructor.
     * @param string $name
     * @param AccessToken $accessToken
     * @param string $configFile
     */
    public function __construct(string $name, AccessToken $accessToken, string $configFile)
    {
        parent::__construct($name, [], $configFile);
        $this->accessToken = $accessToken;
    }

    /**
     * 开始打印
     * @return mixed
     */
    abstract public function startPrinter();

    /**
     * 设置打印内容
     * @param $content
     * @return mixed
     */
    abstract public function setPrinterContent($content);

}
