// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

export default {
	data() {
		return {};
	},
	created() {},
	methods: {
		Debounce(fn, t) {
			const delay = t || 500
			let timer
			return function() {
				const args = arguments
				if (timer) {
					clearTimeout(timer)
				}
				timer = setTimeout(() => {
					timer = null
					fn.apply(this, args)
				}, delay)
			}
		}
	}
};