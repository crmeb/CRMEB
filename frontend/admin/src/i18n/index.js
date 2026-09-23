/*
 * @Author: From-wh from-wh@hotmail.com
 * @Date: 2023-03-09 18:02:23
 * @FilePath: /admin/src/i18n/index.js
 * @Description:
 */
import { createI18n } from 'vue-i18n';
import pinia from '@/store';
import { useThemeConfigStore } from '@/store/module/themeConfig';

import nextZhcn from '@/i18n/lang/zh-cn.js';
import nextEn from '@/i18n/lang/en.js';
import nextZhtw from '@/i18n/lang/zh-tw.js';

import pagesHomeZhcn from '@/i18n/pages/home/zh-cn.js';
import pagesHomeEn from '@/i18n/pages/home/en.js';
import pagesHomeZhtw from '@/i18n/pages/home/zh-tw.js';
import pagesLoginZhcn from '@/i18n/pages/login/zh-cn.js';
import pagesLoginEn from '@/i18n/pages/login/en.js';
import pagesLoginZhtw from '@/i18n/pages/login/zh-tw.js';

// 定义语言国际化内容
/**
 * 说明：
 * /src/i18n/lang 下的 js 为框架的国际化内容
 * /src/i18n/pages 下的 js 为各界面的国际化内容
 */
const messages = {
  'zh-cn': {
    message: {
      ...nextZhcn,
      ...pagesHomeZhcn,
      ...pagesLoginZhcn,
    },
  },
  en: {
    message: {
      ...nextEn,
      ...pagesHomeEn,
      ...pagesLoginEn,
    },
  },
  'zh-tw': {
    message: {
      ...nextZhtw,
      ...pagesHomeZhtw,
      ...pagesLoginZhtw,
    },
  },
};

// 导出语言国际化（legacy 模式保留 $t / this.$t 用法）
// 注意：本模块会被 App.vue/router 的依赖链在 main.js 装载 pinia 之前求值，
// 必须显式传入 pinia 实例，不能依赖 getActivePinia()
export const i18n = createI18n({
  legacy: true,
  globalInjection: true,
  locale: useThemeConfigStore(pinia).themeConfig.globalI18n || 'zh-cn',
  fallbackLocale: 'zh-cn',
  messages,
  silentTranslationWarn: true, // 去除国际化警告
  silentFallbackWarn: true,
});
