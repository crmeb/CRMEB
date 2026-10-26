// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from "@/utils/request.js";

/**
 * 订单核销（店员/配送员）
 * @param {string} verify_code 核销码
 * @param {number} is_confirm 0=查询确认信息 1=执行核销
 */
export function orderVerific(verify_code, is_confirm = 0) {
	return request.post("order/order_verific", {
		verify_code,
		is_confirm,
	});
}
