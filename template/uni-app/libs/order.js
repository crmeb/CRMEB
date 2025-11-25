// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

let app = getApp()

export function goShopDetail(item, uid) {
	return new Promise((resolve, reject) => {
		if (item.activity && item.activity.type === "1") {
			uni.navigateTo({
				url: `/pages/activity/goods_seckill_details/index?id=${item.activity.id}&time_id=${item.activity.time_id}`
			})
		} else if (item.activity && item.activity.type === "2") {
			uni.navigateTo({
				url: `/pages/activity/goods_bargain_details/index?id=${item.activity.id}&bargain=${uid}`
			})
		} else if (item.activity && item.activity.type === "3") {
			uni.navigateTo({
				url: `/pages/activity/goods_combination_details/index?id=${item.activity.id}`
			})
		} else {
			resolve(item);
		}
	});
}


export function goPage() {
	return new Promise(resolve => {
		if (app.globalData.isIframe == false) {
			resolve(true);
		}else{
			return false
		}
	});
}
