<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\exceptions;

/**
 * 通知渠道发送异常
 *
 * 渠道实现发送失败（配置缺失、驱动报错、网关拒绝等）时抛出，
 * 由 Notice::deliver() 统一捕获并写入通知日志（eb_notice_log），
 * 不向上传播、不中断同批次其他渠道的发送。
 */
class NoticeSendException extends \Exception
{
}
