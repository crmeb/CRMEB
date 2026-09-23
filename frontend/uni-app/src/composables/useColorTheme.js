// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不可去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { ref, onUnmounted } from 'vue';

/**
 * 主题色 composable（替代 mixins/color.js）
 *
 * 用法：
 *   const { colorStyle } = useColorTheme();
 *   模板中 :style="colorStyle"（与原 mixin 的 data.colorStyle 用法一致）
 *
 * 修复原 mixin 的监听泄漏：组件卸载时解绑 uni.$on("ok")
 */
const themeListeners = [];

export function useColorTheme() {
	const colorStyle = ref(uni.getStorageSync('viewColor'));

	const handler = (data) => {
		colorStyle.value = data;
	};

	uni.$on('ok', handler);
	themeListeners.push(handler);

	onUnmounted(() => {
		uni.$off('ok', handler);
		const idx = themeListeners.indexOf(handler);
		if (idx > -1) themeListeners.splice(idx, 1);
	});

	return {
		colorStyle
	};
}
