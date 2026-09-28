// 模块路由常量：模块内外跳转统一引用，禁止硬编码路径（命名细则见 docs/命名规范.md §2.1）。
// 复制本目录后，把 MODULE_NAME 换成实际插件标识（与目录名一致，如 seckill），按页面追加条目。
// 跳转用法：uni.navigateTo({ url: routes.goodsDetails + '?id=' + id });
const MODULE_NAME = '_template';

export default {
	/** 模块首页 */
	index: `/pages-modules/${MODULE_NAME}/index`,
	// 按实际页面追加（示例）：
	// /** 商品详情页 */
	// goodsDetails: `/pages-modules/${MODULE_NAME}/goods_details/index`,
	// /** 参与记录页 */
	// record: `/pages-modules/${MODULE_NAME}/record/index`,
};
