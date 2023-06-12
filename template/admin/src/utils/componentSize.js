import { Local } from '@/utils/storage.js';

// 全局组件大小
export const globalComponentSize = Local.get('themeConfigPrev') ? Local.get('themeConfigPrev').globalComponentSize : '';
