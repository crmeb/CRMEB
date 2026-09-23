<template>
	<view :style="colorStyle">
		<view class="return-list">
			<!-- 顶部状态分组 tab：全部/处理中/已完成/已取消 -->
			<view class="top-tabs acea-row row-around">
				<view
					class="tabs"
					:class="{ btborder: activeTabIndex === index }"
					v-for="(tab, index) in tabs"
					:key="index"
					@tap="changeTab(index)"
				>{{ tab.name }}</view>
			</view>

			<!-- 售后单列表：卡片只留 状态 + 主商品 + 退款金额，申请原因/凭证/物流等进详情查看 -->
			<view class="list" v-if="list.length">
				<view
					class="item"
					v-for="(item, index) in list"
					:key="index"
					@click="goDetail(item)"
				>
					<!-- 头部：售后类型标签 + 申请时间 + 状态文案 -->
					<view class="title acea-row row-between-wrapper">
						<view class="acea-row row-middle">
							<text v-if="item.type_text" class="sign cart-color acea-row row-center-wrapper">{{ item.type_text }}</text>
							<view class="time">{{ formatTime(item.add_time) }}</view>
						</view>
						<!-- 状态文案按 status_group 着色，后端下发避免前端硬编码映射 -->
						<text class="status" :class="'status-' + item.status_group">{{ item.status_text }}</text>
					</view>

					<!-- 商品快照：仅展示主商品，多商品用图片角标 + 共N件提示 -->
					<view class="goods-info acea-row row-between row-top" v-if="getSnapshotItem(item) || (item.order_info && (item.order_info.title || item.order_info.image))">
						<view class="pictrue">
							<image :src="getGoodsImage(item)" mode="aspectFill"></image>
							<view class="pic-mask" v-if="getSnapshotItems(item).length > 1">+{{ getSnapshotItems(item).length - 1 }}</view>
						</view>
						<view class="text">
							<view class="name line2">{{ getGoodsName(item) }}</view>
							<!-- 产品类型标签 + 规格摘要合并一行，完整规格进详情 -->
							<view class="sub-line" v-if="item.product_type_text || getSpecText(item)">
								<text v-if="item.product_type_text" class="ptype-tag">{{ item.product_type_text }}</text>
								<text class="spec" v-if="getSpecText(item)">{{ getSpecText(item) }}</text>
							</view>
							<view class="acea-row row-between-wrapper money-row">
								<view class="num">{{ getNumText(item) }}</view>
								<view class="price" v-if="getSnapshotItem(item)">{{ $t(`￥`) }}{{ getSnapshotItem(item).true_price }}</view>
							</view>
						</view>
					</view>

					<!-- 底部：退款金额（已有实际退款优先展示） + 查看详情 -->
					<view class="bottom acea-row row-between-wrapper">
						<view class="refund" v-if="getRefund(item)">
							<text class="refund-label">{{ getRefund(item).label }}</text>
							<text class="refund-money" :class="{ 'is-actual': getRefund(item).actual }">{{ $t(`￥`) }}{{ getRefund(item).price }}</text>
						</view>
						<view class="bnt">{{ $t(`查看详情`) }}</view>
					</view>
				</view>
			</view>

			<!-- 加载更多 -->
			<view class="loadingicon acea-row row-center-wrapper" v-if="list.length > 0">
				<text class="loading iconfont icon-jiazai" :hidden="!loading"></text>
				{{ loadTitle }}
			</view>

			<!-- 空状态 -->
			<view v-if="list.length === 0 && !loading">
				<emptyPage :title="$t(`暂无售后记录`)"></emptyPage>
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onReachBottom } from '@dcloudio/uni-app';
import home from '@/components/home';
import emptyPage from '@/components/emptyPage';
import { useColorTheme } from '@/composables/useColorTheme';
import { getAfterSaleList } from '@/api/order-center.js';
import routes from '@/dynamic-pages/api/routes.js';
import i18n from '@/utils/lang.js';

const { colorStyle } = useColorTheme();

// ---------- data ----------
// 状态分组 tab：group 值与后端 STATUS_GROUP_MAP 键对应
const tabs = [
	{ name: i18n.t(`全部`), group: '' },
	{ name: i18n.t(`处理中`), group: 'processing' },
	{ name: i18n.t(`已完成`), group: 'completed' },
	{ name: i18n.t(`已取消`), group: 'canceled' },
];
const activeTabIndex = ref(0);
const list = ref([]);
const page = ref(1);
const limit = ref(10);
const loading = ref(false);
const finished = ref(false);
const loadTitle = ref('');

// ---------- 生命周期 ----------
onLoad(() => {
	loadTitle.value = i18n.t(`加载更多`);
	loadList();
});
onReachBottom(() => {
	loadList();
});

// ---------- methods ----------
/**
 * 切换状态分组 tab
 * 重置分页与列表后重新加载
 */
function changeTab(index) {
	if (index === activeTabIndex.value) return;
	activeTabIndex.value = index;
	page.value = 1;
	finished.value = false;
	list.value = [];
	loadList();
}
/**
 * 加载售后列表
 * 调用 after_sale/list 接口，按当前 tab 的 status_group 筛选
 */
async function loadList() {
	if (loading.value || finished.value) return;
	loading.value = true;
	loadTitle.value = i18n.t(`加载更多`);
	try {
		const group = tabs[activeTabIndex.value].group;
		const res = await getAfterSaleList({
			page: page.value,
			limit: limit.value,
			status_group: group,
		});
		const data = res.data || {};
		const rows = data.list || [];
		if (rows.length < limit.value) finished.value = true;
		list.value = page.value === 1 ? rows : [...list.value, ...rows];
		page.value++;
		loadTitle.value = finished.value ? i18n.t(`没有更多内容啦~`) : i18n.t(`加载更多`);
	} catch (e) {
		// 接口异常静默处理，保留已有列表
	} finally {
		loading.value = false;
	}
}
/**
 * 取售后商品快照第一项
 * product_snapshot 结构：{ items: [{ store_name, image, sku, cart_num, true_price }] }
 * 列表页仅展示主商品，详情页展示完整快照
 * @param {Object} item 售后单
 * @return {Object|null}
 */
function getSnapshotItem(item) {
	const snap = item && item.product_snapshot || {};
	const items = snap.items || [];
	return items[0] || null;
}
/**
 * 取售后商品快照全部 items
 * 用于多商品汇总展示（共N件、图片角标）
 * @param {Object} item 售后单
 * @return {Array}
 */
function getSnapshotItems(item) {
	const snap = (item && item.product_snapshot) || {};
	return snap.items || [];
}
/**
 * 取商品名称：优先售后快照，缺失时兜底订单标题
 * @param {Object} item 售后单
 * @return {String}
 */
function getGoodsName(item) {
	const snapItem = getSnapshotItem(item);
	if (snapItem && snapItem.store_name) return snapItem.store_name;
	return (item.order_info && item.order_info.title) || '';
}
/**
 * 取商品图片：优先售后快照，缺失时兜底订单主图
 * @param {Object} item 售后单
 * @return {String}
 */
function getGoodsImage(item) {
	const snapItem = getSnapshotItem(item);
	if (snapItem && snapItem.image) return snapItem.image;
	return (item.order_info && item.order_info.image) || '';
}
/**
 * 取规格摘要：spec_info 各项值合并为一行（如：黑色 · L）
 * 后端按产品类型差异化下发（normal=规格；hotel=预订时间+床型；ticket=预订时间+票种），完整信息进详情查看
 * @param {Object} item 售后单
 * @return {String}
 */
function getSpecText(item) {
	const specs = (item && item.spec_info) || [];
	return specs
		.map((spec) => spec && spec.value)
		.filter((value) => value !== '' && value !== null && value !== undefined)
		.join(' · ');
}
/**
 * 取商品数量文案：多商品显示共N件，单商品显示 xN
 * @param {Object} item 售后单
 * @return {String}
 */
function getNumText(item) {
	const items = getSnapshotItems(item);
	if (items.length > 1) return i18n.t(`共`) + items.length + i18n.t(`件`);
	const snapItem = getSnapshotItem(item);
	return 'x' + ((snapItem && snapItem.cart_num) || (item.order_info && item.order_info.total_num) || 1);
}
/**
 * 取底部退款金额展示项：已有实际退款金额时优先展示「实际退款」，否则展示「申请退款」
 * @param {Object} item 售后单
 * @return {Object|null} { label, price, actual }
 */
function getRefund(item) {
	if (!item) return null;
	if (Number(item.actual_refund_price) > 0) {
		return { label: i18n.t(`实际退款`), price: item.actual_refund_price, actual: true };
	}
	if (Number(item.refund_price) > 0) {
		return { label: i18n.t(`申请退款`), price: item.refund_price, actual: false };
	}
	return null;
}
/**
 * 格式化时间戳为 YYYY-MM-DD HH:mm
 */
function formatTime(timestamp) {
	if (!timestamp) return '';
	const date = new Date(timestamp * 1000);
	const y = date.getFullYear();
	const m = String(date.getMonth() + 1).padStart(2, '0');
	const d = String(date.getDate()).padStart(2, '0');
	const h = String(date.getHours()).padStart(2, '0');
	const mi = String(date.getMinutes()).padStart(2, '0');
	return `${y}-${m}-${d} ${h}:${mi}`;
}
/**
 * 跳转售后详情页
 * 带上 after_sale_no 进入详情模式（AfterSaleEngine 详情模式）
 */
function goDetail(item) {
	if (!item || !item.after_sale_no) return;
	uni.navigateTo({
		url: routes.aftersale + '?after_sale_no=' + item.after_sale_no,
	});
}
</script>

<style scoped>
	.return-list {
		min-height: 100vh;
	}

	/* 顶部 tab 栏 */
	.top-tabs {
		background-color: #fff;
		height: 88rpx;
		position: sticky;
		top: 0;
		z-index: 10;
	}

	.top-tabs .tabs {
		text-align: center;
		font-size: 28rpx;
		color: #282828;
		height: 88rpx;
		line-height: 88rpx;
		position: relative;
		flex: 1;
	}

	.top-tabs .tabs.btborder {
		font-weight: bold;
		color: var(--view-theme);
	}

	.top-tabs .tabs.btborder::after {
		content: '';
		position: absolute;
		bottom: 0;
		left: 50%;
		transform: translateX(-50%);
		width: 48rpx;
		height: 4rpx;
		background-color: var(--view-theme);
		border-radius: 2rpx;
	}

	/* 售后单列表 */
	.list {
		width: 690rpx;
		margin: 14rpx auto 0 auto;
	}

	.list .item {
		background-color: #fff;
		border-radius: 6rpx;
		margin-bottom: 14rpx;
		padding: 0 30rpx;
	}

	.list .item .title {
		height: 80rpx;
		border-bottom: 1rpx solid #eee;
		font-size: 28rpx;
		color: #282828;
	}

	.list .item .title .sign {
		font-size: 24rpx;
		padding: 0 7rpx;
		height: 36rpx;
		margin-right: 15rpx;
		color: #fff;
		border-radius: 4rpx;
	}

	.list .item .title .time {
		font-size: 26rpx;
		color: #999;
	}

	.list .item .title .status {
		font-size: 26rpx;
		font-weight: bold;
	}

	/* 状态着色：按后端下发的 status_group */
	.list .item .title .status.status-processing {
		color: #f90;
	}

	.list .item .title .status.status-completed {
		color: #07c160;
	}

	.list .item .title .status.status-canceled {
		color: #999;
	}

	/* 商品快照 */
	.list .item .goods-info {
		margin-top: 22rpx;
		padding-bottom: 22rpx;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.list .item .goods-info .pictrue {
		position: relative;
		width: 120rpx;
		height: 120rpx;
	}

	.list .item .goods-info .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.list .item .goods-info .pic-mask {
		position: absolute;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.6);
		color: #fff;
		font-size: 22rpx;
		padding: 2rpx 8rpx;
		border-radius: 6rpx 0 6rpx 0;
	}

	.list .item .goods-info .text {
		width: 486rpx;
		font-size: 28rpx;
		color: #999;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.list .item .goods-info .text .name {
		color: #282828;
		line-height: 39rpx;
	}

	/* 类型标签 + 规格摘要行 */
	.list .item .goods-info .text .sub-line {
		display: flex;
		align-items: center;
		margin-top: 8rpx;
		overflow: hidden;
	}

	.list .item .goods-info .text .sub-line .ptype-tag {
		flex-shrink: 0;
		display: inline-block;
		font-size: 20rpx;
		color: #2b8eff;
		background: rgba(43, 142, 255, 0.1);
		padding: 2rpx 12rpx;
		border-radius: 20rpx;
		margin-right: 12rpx;
	}

	.list .item .goods-info .text .sub-line .spec {
		font-size: 24rpx;
		color: #999;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}

	.list .item .goods-info .text .money-row {
		margin-top: 10rpx;
	}

	.list .item .goods-info .text .num {
		font-size: 26rpx;
		color: #999;
	}

	.list .item .goods-info .text .price {
		font-size: 28rpx;
		font-weight: bold;
		color: var(--view-priceColor);
	}

	/* 底部：退款金额 + 查看详情 */
	.list .item .bottom {
		height: 96rpx;
		font-size: 24rpx;
	}

	.list .item .bottom .refund {
		display: flex;
		align-items: baseline;
	}

	.list .item .bottom .refund-label {
		color: #999;
		margin-right: 10rpx;
	}

	.list .item .bottom .refund-money {
		font-size: 30rpx;
		font-weight: bold;
		color: var(--view-priceColor);
	}

	.list .item .bottom .refund-money.is-actual {
		color: #07c160;
	}

	.list .item .bottom .bnt {
		width: 176rpx;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
		border: 1rpx solid #ddd;
		color: #aaa;
		border-radius: 50rpx;
		font-size: 27rpx;
	}

	/* 加载更多 */
	.loadingicon {
		padding: 30rpx 0;
		font-size: 26rpx;
		color: #999;
	}

	.loading {
		margin-right: 10rpx;
	}
</style>
