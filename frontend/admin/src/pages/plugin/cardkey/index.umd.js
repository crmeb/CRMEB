/**
 * 卡密商品扩展 UMD 入口
 * 构建后通过 PageContainer 动态加载（window.__ext_cardkey）
 */
import CardkeyApp from './App.vue';
import routes from './routes.js';

export default CardkeyApp;
export { routes };
