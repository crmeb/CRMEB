import { createI18n } from 'vue-i18n';
import Cache from '@/utils/cache';

let lang = '';
// #ifdef MP || APP-PLUS
lang = Cache.has('locale') ? Cache.get('locale') : 'zh-CN';
// #endif
// #ifdef H5
lang = Cache.has('locale') ? Cache.get('locale') : navigator.language;
// #endif

/**
 * vue-i18n v9（uni-app Vue3 运行时内置）
 * legacy: true 保留 Options API 的 this.$t / this.$i18n 用法，业务代码零改动
 */
const i18n = createI18n({
	legacy: true,
	locale: lang,
	fallbackLocale: 'zh-CN',
	messages: uni.getStorageSync('localeJson'),
	silentTranslationWarn: true, // 去除国际化警告
	silentFallbackWarn: true
});

// vue-i18n v9 legacy 模式下 createI18n 实例自身无 t 方法（挂在 global 上），
// 全站历史用法是 `import i18n from '@/utils/lang.js'; i18n.t('中文')`（含 utils/request.js），
// 此处补一个绑定到 global 的 t，保证两种写法（i18n.t / i18n.global.t）都可用
i18n.t = i18n.global.t.bind(i18n.global);

export default i18n;
