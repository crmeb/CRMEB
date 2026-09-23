import Cache from '@/utils/cache';

/**
 * 判断传入的 key 是否在数组 arr 中存在
 * @param {string} key - 待判断的字符串
 * @returns {boolean} - 返回布尔值，表示是否有权限
 */
function ActivePermission(key) {
	// seckill 秒杀 bargain 砍价 combination 拼团
	let arr = Cache.get('BASIC_CONFIG').site_func || ['seckill', 'bargain', 'combination']; // 定义一个数组，包含三种类型
	let index = arr.indexOf(key); // 获取 key 在数组中的索引
	if (index > -1) {
		// 如果索引大于 -1，说明 key 存在于数组中
		return true; // 有权限
	} else {
		return false; // 无权限
	}
}


export default ActivePermission;