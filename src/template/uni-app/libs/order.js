let app = getApp()

export function goShopDetail(item,uid) {
	return new Promise(resolve => {
		if (item.activity && item.activity.type === "1") {
			uni.navigateTo({
				url: `/pages/activity/goods_seckill_details/index?id=${item.activity.id}&time=${item.activity.time}&status=1`
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



