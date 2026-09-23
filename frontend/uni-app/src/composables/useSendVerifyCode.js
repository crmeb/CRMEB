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
import i18n from '@/utils/lang.js';

/**
 * 验证码 60s 倒计时 composable（替代 mixins/SendVerifyCode.js）
 *
 * 用法：
 *   const { disabled, codeText, sendCode } = useSendVerifyCode();
 *   模板：{{ codeText }}、@click="sendCode"
 *
 * 注意：与原 mixin 的 onHide 清理不同，此处统一在组件卸载时清理定时器；
 * 页面如需在 onHide 时停止倒计时，可调用 stopCode()
 */
export function useSendVerifyCode() {
	const disabled = ref(false);
	const codeText = ref(i18n.t('验证码'));
	let runTime = undefined;
	const captchaType = 'blockPuzzle';

	function stopCode() {
		if (runTime !== undefined) {
			clearInterval(runTime);
			runTime = undefined;
		}
	}

	function sendCode() {
		if (disabled.value) return;
		disabled.value = true;
		let n = 60;
		codeText.value = i18n.t('剩余') + n + 's';
		runTime = setInterval(() => {
			n = n - 1;
			if (n < 0) {
				clearInterval(runTime);
				runTime = undefined;
				disabled.value = false;
				codeText.value = i18n.t('重新获取');
				return;
			}
			codeText.value = i18n.t('剩余') + n + 's';
		}, 1000);
	}

	onUnmounted(stopCode);

	return {
		disabled,
		codeText,
		captchaType,
		sendCode,
		stopCode
	};
}
