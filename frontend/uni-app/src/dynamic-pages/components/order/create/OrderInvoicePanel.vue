<!--
  发票信息面板（抬头选择模式）
  仅负责"选择已保存抬头"，编辑/新增走原 user_invoice_form 页面
  参考 pages/goods/components/invoicePicker/index.vue 实现
-->
<template>
  <view class="invoice-section" v-if="visible">
    <!-- 顶部入口：点击打开抬头选择弹窗 -->
    <view class="item acea-row row-between-wrapper" @tap="onOpen">
      <view class="acea-row row-middle">
        <text class="iconfont icon-fapiao2 invoice-icon"></text>
        <text>{{ $t('开具发票') }}</text>
      </view>
      <view class="discount">
        {{ invoiceTitle || $t('不开发票') }}
        <text class="iconfont icon-jiantou"></text>
      </view>
    </view>

    <!-- 抬头选择弹窗 -->
    <view :class="{ mask: popupShow }" @tap="onClose" @touchmove.stop.prevent></view>
    <view class="popup" :class="{ on: popupShow }">
      <view class="popup-hd">
        {{ $t('抬头选择') }}
        <text class="iconfont icon-guanbi" @tap="onClose"></text>
      </view>
      <scroll-view class="popup-bd" scroll-y="true">
        <radio-group v-if="list.length" name="inv" @change="onChange">
          <template v-for="item in list" :key="item.id">
            <label
              v-if="item.type === 1 || (item.type === 2 && specialInvoice)"
              class="acea-row row-middle item"
            >
              <radio class="radio" :value="item.id" :checked="item.id === checked" />
              <view class="text">
                <view class="acea-row row-middle">
                  <view class="name-wrap acea-row row-middle">
                    <view class="name-group">
                      <view class="name">{{ item.name }}</view>
                      <view v-if="item.is_default" class="default">{{ $t('默认') }}</view>
                    </view>
                  </view>
                  <view class="type" :class="{ special: item.type === 2 }">
                    {{ item.header_type === 1 ? $t('个人') : $t('企业') }}
                    {{ item.type === 1 ? $t('普通') : $t('专用') }}
                  </view>
                </view>
                <view class="acea-row row-bottom">
                  <view class="info-wrap">
                    <view class="email">{{ $t('联系邮箱') }} {{ item.email }}</view>
                    <view v-if="item.header_type === 1" class="tel">{{ $t('联系电话') }} {{ item.drawer_phone }}</view>
                    <view v-else class="number">{{ $t('企业税号') }} {{ item.duty_number }}</view>
                  </view>
                  <!-- 编辑按钮跳转原 user_invoice_form 页面 -->
                  <navigator :render-link="false"
                    class="navigator"
                    :url="editUrl(item.id)"
                    hover-class="none"
                    @click.stop="onClose"
                  >
                    <text class="iconfont icon-bianji"></text>{{ $t('编辑') }}
                  </navigator>
                </view>
              </view>
            </label>
          </template>
        </radio-group>
        <view v-else class="empty">
          <image class="empty-img" :src="imgHost + '/statics/images/noInvoice.png'"></image>
          <view>{{ $t('您还没有添加发票信息哟~') }}</view>
        </view>
      </scroll-view>
      <view class="popup-ft">
        <!-- 添加新抬头：跳转原 user_invoice_form 页面 -->
        <navigator :render-link="false"
          class="add-navigator"
          :url="addUrl"
          hover-class="none"
          @click="onClose"
        >
          <text class="iconfont icon-fapiao"></text>{{ $t('添加新的抬头') }}
        </navigator>
        <button class="button cancel" plain @tap="onCancel">{{ $t('不开发票') }}</button>
        <button class="button confirm" plain @tap="onConfirm">{{ $t('确认提交') }}</button>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { HTTP_REQUEST_URL } from '@/config/app';
import i18n from '@/utils/lang.js';
import routes from '../../../api/routes.js';

defineOptions({ name: 'OrderInvoicePanel' });

const props = defineProps({
  /** 是否显示（受 features.invoice 控制） */
  visible: { type: Boolean, default: true },
  /** 已保存的抬头列表（由父组件调用 invoiceList 接口获取） */
  list: {
    type: Array,
    default() {
      return [];
    },
  },
  /** 当前选中的抬头 ID（字符串） */
  checked: { type: String, default: '' },
  /** 是否启用专用发票（受 features.special_invoice 控制，缺省关闭） */
  specialInvoice: { type: Boolean, default: false },
  /** 跳转 user_invoice_form 时携带的回跳参数（不含 ?） */
  urlQuery: { type: String, default: '' },
  /** 弹窗显隐（支持 v-model:popupShow） */
  popupShow: { type: Boolean, default: false },
});

const emit = defineEmits(['open', 'update:popupShow', 'close', 'change', 'cancel']);

const imgHost = HTTP_REQUEST_URL;
// 弹窗内临时选中ID，关闭或确认前不写入父组件
const tempId = ref('');

/** 顶部展示文案 */
const invoiceTitle = computed(() => {
  if (!props.checked) return '';
  var item = props.list.find(i => i.id === props.checked);
  if (!item) return '';
  var name = '';
  name += item.header_type === 1 ? i18n.t('个人') : i18n.t('企业');
  name += item.type === 1 ? i18n.t('普通') : i18n.t('专用');
  name += i18n.t('发票');
  return name;
});

/** 添加抬头跳转地址 */
const addUrl = computed(() => {
  var query = 'from=order_confirm';
  if (props.urlQuery) query += '&' + props.urlQuery;
  return routes.invoiceForm + '?' + query;
});

/** 打开弹窗时同步当前选中ID到临时变量 */
watch(
  () => props.popupShow,
  (val) => {
    if (val) tempId.value = props.checked;
  }
);

/** 顶部点击：打开弹窗，通知父组件刷新抬头列表 */
function onOpen() {
  emit('open');
  emit('update:popupShow', true);
}
/** 关闭弹窗：通知父组件刷新（编辑/新增后回显最新数据） */
function onClose() {
  emit('update:popupShow', false);
  emit('close');
}
/** 选中抬头：仅更新临时ID，等待"确认提交" */
function onChange(e) {
  tempId.value = e.detail.value;
}
/** 确认提交：将临时ID同步给父组件 */
function onConfirm() {
  if (!tempId.value) {
    uni.showToast({ title: i18n.t('请选择发票抬头'), icon: 'none' });
    return;
  }
  emit('change', tempId.value);
  emit('update:popupShow', false);
}
/** 不开发票 */
function onCancel() {
  tempId.value = '';
  emit('cancel');
  emit('update:popupShow', false);
}
/** 编辑抬头跳转地址 */
function editUrl(id) {
  var query = 'from=order_confirm&id=' + id;
  if (props.urlQuery) query += '&' + props.urlQuery;
  return routes.invoiceForm + '?' + query;
}
</script>

<style scoped>
/* 顶部入口 */
.invoice-section {
  margin: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  overflow: hidden;
}
.invoice-section .item {
  padding: 27rpx 30rpx;
  font-size: 30rpx;
  color: #282828;
}
.invoice-section .item .invoice-icon {
  margin-right: 14rpx;
  font-size: 32rpx;
  color: var(--view-theme, #e93323);
}
.invoice-section .item .discount {
  font-size: 28rpx;
  color: #999;
}
.invoice-section .item .discount .iconfont {
  margin-left: 8rpx;
  font-size: 24rpx;
}

/* 遮罩 */
.mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 99;
}

/* 抬头选择弹窗 */
.popup {
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 100;
  width: 100%;
  border-top-left-radius: 16rpx;
  border-top-right-radius: 16rpx;
  background: #f5f5f5;
  transform: translateY(100%);
  transition: 0.3s;
}
.popup.on {
  transform: translateY(0);
}
.popup-hd {
  position: relative;
  height: 120rpx;
  font-size: 32rpx;
  line-height: 120rpx;
  text-align: center;
  color: #000;
}
.popup-hd .iconfont {
  position: absolute;
  top: 50%;
  right: 30rpx;
  transform: translateY(-50%);
  font-size: 32rpx;
  color: #707070;
}
.popup-bd {
  max-height: 600rpx;
  padding: 0 30rpx;
  box-sizing: border-box;
}
.popup-bd .item {
  padding: 30rpx;
  margin-bottom: 14rpx;
  background: #fff;
  border-radius: 12rpx;
}
.popup-bd .radio {
  margin-right: 20rpx;
}
.popup-bd .text {
  flex: 1;
  min-width: 0;
}
.popup-bd .name-wrap {
  flex: 1;
  min-width: 0;
}
.popup-bd .name-group {
  display: flex;
  align-items: center;
}
.popup-bd .name {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-weight: bold;
  font-size: 30rpx;
  color: #282828;
}
.popup-bd .default {
  margin-left: 20rpx;
  padding: 0 8rpx;
  border: 1rpx solid var(--view-theme, #e93323);
  border-radius: 6rpx;
  font-size: 20rpx;
  line-height: 28rpx;
  color: var(--view-theme, #e93323);
}
.popup-bd .type {
  margin-left: 20rpx;
  padding: 0 12rpx;
  height: 42rpx;
  background: #fcf0e0;
  border-radius: 4rpx;
  font-size: 24rpx;
  line-height: 42rpx;
  text-align: center;
  color: #d67300;
}
.popup-bd .type.special {
  background: var(--view-op-ten);
  color: var(--view-theme, #e93323);
}
.popup-bd .email,
.popup-bd .tel,
.popup-bd .number {
  margin-top: 12rpx;
  font-size: 24rpx;
  color: #666;
}
.popup-bd .info-wrap {
  flex: 1;
}
.popup-bd .navigator {
  margin-left: 20rpx;
  font-size: 26rpx;
  color: #282828;
}
.popup-bd .navigator .iconfont {
  margin-right: 6rpx;
  font-size: 26rpx;
  color: #000;
}
.popup-bd .empty {
  padding-top: 80rpx;
  padding-bottom: 40rpx;
  font-size: 26rpx;
  text-align: center;
  color: #999;
}
.popup-bd .empty-img {
  width: 400rpx;
  height: 260rpx;
  margin: 0 auto 20rpx;
  display: block;
}

/* 弹窗底部操作区 */
.popup-ft {
  padding: 14rpx 30rpx 44rpx;
  padding-bottom: calc(44rpx + env(safe-area-inset-bottom));
}
.popup-ft .add-navigator {
  display: block;
  height: 86rpx;
  border-radius: 43rpx;
  background: var(--view-theme, #e93323);
  font-size: 30rpx;
  line-height: 86rpx;
  text-align: center;
  color: #fff;
}
.popup-ft .add-navigator .iconfont {
  margin-right: 14rpx;
  font-size: 30rpx;
}
.popup-ft .button {
  height: 86rpx;
  border: 1rpx solid var(--view-theme, #e93323);
  border-radius: 43rpx;
  margin-top: 26rpx;
  font-size: 30rpx;
  line-height: 84rpx;
  color: var(--view-theme, #e93323);
}
</style>
