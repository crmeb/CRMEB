// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from "@/utils/request.js";

/**
 * 获取抽奖详情信息
 * 
 */
export function getLotteryData(type, lottery_id) {
	return request.get(`v2/lottery/info/${type}${lottery_id ? '/' + lottery_id : ''}`);
}

/**
 * 参与抽奖
 * 
 */
export function startLottery(data) {
	return request.post(`v2/lottery`, data);
}

/**
 * 领奖
 * 
 */
export function receiveLottery(data) {
	return request.post(`v2/lottery/receive`, data);
}

/**
 * 获取中奖记录
 * 
 */
export function getLotteryList(data) {
	return request.get(`v2/lottery/record`, data);
}