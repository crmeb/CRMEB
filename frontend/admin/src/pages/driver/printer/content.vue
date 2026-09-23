<template>
  <div class="printer-content-page">
    <pages-header
      ref="pageHeader"
      title="小票配置"
      :backUrl="$routeProStr + '/setting/printer'"
      description="勾选小票需打印的商家、商品、支付等信息项，保存后打印即时生效。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <div class="flex justify-between warpper" v-loading="loading">
        <el-form
          class="content-form"
          :model="formItem"
          :label-width="labelWidth"
          :label-position="labelPosition"
          :disabled="!loaded || submitLoading || !hasPermission('admin-printer-content')"
          @submit.prevent
        >
          <el-form-item label="小票头部：">
            <el-checkbox v-model="formItem.header" :true-label="1" :false-label="0">商家名称</el-checkbox>
          </el-form-item>
          <el-form-item label="配送信息：">
            <el-checkbox v-model="formItem.delivery" :true-label="1" :false-label="0">配送信息</el-checkbox>
          </el-form-item>
          <el-form-item label="买家备注：">
            <el-checkbox v-model="formItem.buyer_remarks" :true-label="1" :false-label="0">买家备注</el-checkbox>
          </el-form-item>
          <el-form-item label="商品信息：">
            <el-checkbox-group v-model="formItem.goods">
              <el-checkbox :label="0">商品基础信息</el-checkbox>
              <el-checkbox :label="1">规格编码</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="运费信息：">
            <el-checkbox v-model="formItem.freight" :true-label="1" :false-label="0">运费</el-checkbox>
          </el-form-item>
          <el-form-item label="优惠信息：">
            <el-checkbox v-model="formItem.preferential" :true-label="1" :false-label="0">优惠总计</el-checkbox>
          </el-form-item>
          <el-form-item label="支付信息：">
            <el-checkbox-group v-model="formItem.pay">
              <el-checkbox :label="0">支付方式</el-checkbox>
              <el-checkbox :label="1">实收金额</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="其他订单信息：">
            <el-checkbox-group v-model="formItem.order">
              <el-checkbox :label="0">订单编号</el-checkbox>
              <el-checkbox :label="1">下单时间</el-checkbox>
              <el-checkbox :label="2">支付时间</el-checkbox>
              <el-checkbox :label="3">打印时间</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="推广二维码：">
            <el-checkbox v-model="formItem.code" :true-label="1" :false-label="0">选择系统链接</el-checkbox>
            <div v-if="formItem.code" class="link">
              <div class="select-link">
                链接：{{ formItem.code_url }}
                <el-button
                  v-if="hasPermission('admin-printer-content')"
                  v-auth="['admin-printer-content']"
                  link
                  type="primary"
                  v-db-click
                  :disabled="!loaded || submitLoading"
                  @click="getLink"
                >
                  {{ formItem.code_url ? '修改' : '选择' }}
                </el-button>
              </div>
            </div>
          </el-form-item>
          <el-form-item label="底部公告：">
            <el-checkbox v-model="formItem.show_notice" :true-label="1" :false-label="0">底部公告</el-checkbox>
            <div v-if="formItem.show_notice" class="notice-field">
              <el-input
                v-model="formItem.notice_content"
                maxlength="80"
                show-word-limit
                type="textarea"
                placeholder="请输入公告内容"
                class="notice-input"
              />
            </div>
          </el-form-item>
        </el-form>
        <div class="ticket-preview">
          <div class="out-line"></div>
          <div class="ticket-content">
            <div v-if="formItem.header === 1" class="ticket-header">商家名称</div>
            <!-- 配送方式 -->
            <div class="delivery btn-line" v-if="formItem.delivery === 1">
              <div class="form-box">
                <div class="label">配送方式：</div>
                <div class="content">商家配送</div>
              </div>
              <div class="form-box">
                <div class="label">客户姓名：</div>
                <div class="content">收货人姓名</div>
              </div>
              <div class="form-box">
                <div class="label">客户电话：</div>
                <div class="content">13023354455</div>
              </div>
              <div class="form-box">
                <div class="label">收货地址：</div>
                <div class="content">上海市浦东新区世界大道25号B座309室</div>
              </div>
            </div>
            <!-- 备注 -->
            <div class="buyer-remarks btn-line" v-if="formItem.buyer_remarks === 1">
              <div class="form-box">
                <div class="label">买家备注：</div>
                <div class="content">请在收货时向商家留言，谢谢！</div>
              </div>
            </div>
            <!-- 商品 -->
            <div v-if="formItem.goods.includes(0)">
              <div class="goods btn-line">
                <div class="xing">*************************商品***********************</div>
                <div class="flex justify-between">
                  <span>商品</span>
                  <span>单价</span>
                  <span>数量</span>
                  <span>金额</span>
                </div>
              </div>
              <div class="goods-msg btn-line">
                <div class="flex justify-between">
                  <span>商品1</span>
                  <span>100.0</span>
                  <span>2</span>
                  <span>200.0</span>
                </div>
                <div class="flex justify-between">
                  <span>(规格1)</span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <div v-if="formItem.goods.includes(1)" class="flex py-10">
                  <span>规格编码：</span>
                  <span>FKXQW4567vw59</span>
                </div>
              </div>
              <div class="goods-msg pb-10 pt-10">
                <div class="flex justify-between">
                  <span>商品2</span>
                  <span>100.0</span>
                  <span>2</span>
                  <span>200.0</span>
                </div>
                <div class="flex justify-between">
                  <span>(规格2)</span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <div v-if="formItem.goods.includes(1)" class="flex py-10">
                  <span>规格编码：</span>
                  <span>FKXQW4567vw60</span>
                </div>
              </div>
              <div class="xing">**************************************************</div>
              <!-- 合计 -->
              <div class="pay flex flex-col align-end btn-line">
                <div class="fw-500">合计：400.00元</div>
              </div>
            </div>
            <!-- 运费优惠 -->
            <div
              class="pay flex flex-col align-end btn-line"
              v-if="formItem.freight === 1 || formItem.preferential === 1"
            >
              <div v-if="formItem.freight === 1">运费：+30.00元</div>
              <div v-if="formItem.preferential === 1">优惠：-80.00元</div>
              <div v-if="formItem.preferential === 1">抵扣：-20.00元</div>
            </div>
            <!-- 支付信息 -->

            <div class="pay flex flex-col align-end btn-line" v-if="formItem.pay.length > 0">
              <div v-if="formItem.pay.includes(0)">支付方式：微信支付</div>
              <div v-if="formItem.pay.includes(1)" class="fw-500">实际支付：330.00元</div>
            </div>
            <!-- 订单信息 -->

            <div class="order pt-10 btn-line" v-if="formItem.order.length > 0">
              <div v-if="formItem.order.includes(0)">订单编号：wx1234567890</div>
              <div v-if="formItem.order.includes(1)">下单时间：2022/06/18 12:00:00</div>
              <div v-if="formItem.order.includes(2)">支付时间：2022/06/18 12:00:00</div>
              <div v-if="formItem.order.includes(3)">打印时间：2022/06/18 14:20:00</div>
            </div>
            <!-- 二维码 -->
            <div class="code">
              <div v-show="formItem.code" ref="qrcodeRef" class="qrcode"></div>
              <div class="mt-20" v-if="formItem.show_notice">
                {{ formItem.notice_content }}
              </div>
            </div>
          </div>
          <div class="bottom-notice">
            <img class="image" src="../../../assets/images/p-btn.png" alt="" />
          </div>
        </div>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="fixed-card" :class="{ collapsed: menuCollapse, mobile: isMobile }">
      <el-button
        v-if="hasPermission('admin-printer-content')"
        v-auth="['admin-printer-content']"
        type="primary"
        class="submission"
        v-db-click
        :loading="submitLoading"
        :disabled="loading || !loaded"
        @click="save"
      >
        保存
      </el-button>
    </el-card>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { printerChannelContentApi, printerChannelSaveContentApi } from '@/api/driver/printer';
import linkaddress from '@/components/linkaddress';
import QRCode from '@/vendor/qrcodejs2';
import { useLayoutStore } from '@/store/module/layout';
import { useMediaStore } from '@/store/module/media';
import { usePrinterAuth } from './usePrinterAuth';

defineOptions({ name: 'PrinterContent' });

const route = useRoute();
const { hasPermission } = usePrinterAuth();

// 原 mapState('admin/layout') 模块不存在；按语义映射到现有 layout/media store
const layoutStore = useLayoutStore();
const mediaStore = useMediaStore();

const pageHeader = ref(null);
const linkaddres = ref(null);
const qrcodeRef = ref(null);
const loading = ref(false);
const loaded = ref(false);
const submitLoading = ref(false);
const siteUrl = ref('');

const formItem = ref({
  header: 1,
  delivery: 1,
  buyer_remarks: 1,
  goods: [0],
  freight: 1,
  preferential: 1,
  pay: [0, 1],
  order: [0, 1],
  code: 0,
  code_url: '',
  show_notice: 0,
  notice_content: '',
});
const id = route.query.id;

const isMobile = computed(() => mediaStore.isMobile);
const menuCollapse = computed(() => layoutStore.menuCollapse);
const labelWidth = computed(() => (isMobile.value ? undefined : 120));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// created
if (id) getPrintContent();

watch([() => formItem.value.code, () => formItem.value.code_url, siteUrl], () =>
  nextTick(() => drawCode(resolveCodeUrl(formItem.value.code_url))),
);

async function getPrintContent() {
  if (!hasPermission('admin-printer-content-read')) return;
  loading.value = true;
  try {
    const res = await printerChannelContentApi(id);
    const { site_url: baseUrl, ...content } = res.data;
    siteUrl.value = baseUrl || '';
    formItem.value = { ...formItem.value, ...content };
    loaded.value = true;
  } catch (err) {
    loaded.value = false;
    ElMessage.error(err.msg || '小票配置加载失败');
  } finally {
    loading.value = false;
  }
}
async function save() {
  if (submitLoading.value || loading.value || !loaded.value || !hasPermission('admin-printer-content')) return;
  if (formItem.value.code && !resolveCodeUrl(formItem.value.code_url)) {
    ElMessage.warning('请选择有效二维码链接，并配置商城地址');
    return;
  }
  submitLoading.value = true;
  try {
    const res = await printerChannelSaveContentApi(id, { ...formItem.value });
    ElMessage.success(res.msg || '保存成功');
  } catch (err) {
    ElMessage.error(err.msg || '小票配置保存失败');
  } finally {
    submitLoading.value = false;
  }
}
function getLink() {
  if (!loaded.value || submitLoading.value || !hasPermission('admin-printer-content')) return;
  if (linkaddres.value) linkaddres.value.modals = true;
}
function linkUrl(e) {
  if (!hasPermission('admin-printer-content')) return;
  formItem.value.code_url = e;
}

/**
 * @description 与服务端一致：保留绝对链接，相对链接使用商城地址
 */
function resolveCodeUrl(url) {
  const path = String(url || '').trim();
  if (!path) return '';
  if (/^https?:\/\//i.test(path)) {
    try {
      return new URL(path).hostname ? path : '';
    } catch {
      return '';
    }
  }
  if (/^[a-z][a-z\d+.-]*:/i.test(path) || path.startsWith('//')) return '';
  const baseUrl = siteUrl.value.trim().replace(/\/+$/, '');
  if (!/^https?:\/\//i.test(baseUrl)) return '';
  try {
    return new URL(baseUrl).hostname ? baseUrl + '/' + path.replace(/^\/+/, '') : '';
  } catch {
    return '';
  }
}

function drawCode(url) {
  const obj = qrcodeRef.value;
  if (!obj) return;
  obj.innerHTML = '';
  if (!url || !formItem.value.code) return;
  try {
    new QRCode(obj, {
      text: url,
      width: 128,
      height: 128,
      colorDark: '#000000',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });
  } catch {
    ElMessage.error('二维码链接无法预览');
  }
}
</script>
<style lang="scss" scoped>
:deep(.ivu-checkbox-wrapper) {
  font-size: 12px;
  margin-right: 30px;
}
:deep(.el-checkbox__input) {
  margin-right: 6px;
}
:deep(.el-checkbox__input) {
  width: 12px;
  height: 12px;
  font-size: 12px;
}
.warpper {
  max-width: 1200px;
  gap: 30px;
}
.printer-content-page {
  padding-bottom: 80px;
}
.content-form {
  flex: 1;
  min-width: 0;
}
.notice-field {
  width: 100%;
  max-width: 500px;
  min-width: 0;
}
.notice-input {
  width: 100%;
}
.fixed-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 180px !important;
  z-index: 45;
  box-shadow: 0 -1px 2px var(--prev-border-color-light);
  &.collapsed {
    left: 64px !important;
  }
  &.mobile {
    left: 0 !important;
  }
}
.link {
  background: var(--prev-bg-color);
  border-radius: 4px;
  padding: 15px;
}
.select-link {
  font-size: 12px;
  overflow-wrap: anywhere;
}
.ticket-content::-webkit-scrollbar {
  display: none;
}
.ticket-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  width: 271px;
  max-width: 100%;
}
.out-line {
  width: 271px;
  max-width: 100%;
  height: 7px;
  background: var(--prev-border-color-lighter);
  border-radius: 4px;
}

@-webkit-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@-moz-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@-ms-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@-o-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}
.ticket-preview {
  overflow: hidden;
  height: 70vh;
}
.ticket-content {
  position: relative;
  top: -3px;
  animation: show 2s ease-in-out forwards;
  width: 260px;
  max-width: 100%;
  max-height: 70vh;
  overflow-y: scroll;
  overflow-x: hidden;
  background-color: var(--prev-bg-white);
  padding: 20px 15px 15px 15px;
  box-shadow: 0px 4px 10px 0px var(--prev-color-hover);
  border-radius: 4px;
  font-size: 12px;
  font-weight: 400;
  color: var(--prev-color-text-regular);
  line-height: 18px;
  .form-box {
    display: flex;
    .label {
      white-space: nowrap;
    }
  }
  .ticket-header {
    font-weight: 500;
    font-size: 18px;
    text-align: center;
    margin-bottom: 20px;
  }
  .btn-line {
    border-bottom: 1px dashed var(--prev-border-color-lighter);
    padding: 10px 0;
    word-break: break-all;
  }
  .fw-500 {
    font-weight: 500;
  }
  .code {
    display: flex;
    flex-direction: column;
    align-items: center;

    text-align: center;
    .qrcode {
      width: 128px;
      height: 128px;
      margin: 25px 0 0px;
    }
  }
}
.bottom-notice {
  width: 260px;
  max-width: 100%;
  margin-left: 1px;
  height: 13px;
  position: relative;
}
.image {
  width: 100%;
  height: 100%;
  position: absolute;
  top: -6px;
}
.xing {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  white-space: nowrap;
}
@media (max-width: 768px) {
  .warpper {
    flex-direction: column;
  }
  .ticket-preview {
    align-self: center;
  }
}
</style>
