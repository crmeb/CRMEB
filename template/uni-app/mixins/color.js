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
		return {
			colorStyle: '',
			colorStatus: ''
		};
	},
	created() {
		this.colorStyle = uni.getStorageSync('viewColor')
		uni.$on('ok', (data, status) => {
			this.colorStyle = data
			this.colorStatus = status
		})
	},
	methods: {}
};
