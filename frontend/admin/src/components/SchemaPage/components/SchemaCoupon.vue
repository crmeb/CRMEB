<template>
  <div class="schema-coupon">
    <div class="schema-coupon__tags">
      <el-tag v-for="(item, index) in tags" :key="item.id" class="schema-coupon__tag" closable @close="remove(index)">
        {{ item.title || `ID:${item.id}` }}
      </el-tag>
      <span v-if="!tags.length" class="schema-coupon__empty">未选择</span>
      <el-button type="primary" plain size="small" v-db-click @click="open">选择优惠券</el-button>
    </div>

    <el-dialog
      v-model="visible"
      title="选择优惠券"
      width="980px"
      append-to-body
      destroy-on-close
      class="schema-coupon-dialog"
      align-center
      @closed="onClosed"
    >
      <iframe
        v-if="visible"
        ref="frameRef"
        :src="iframeSrc"
        frameborder="0"
        class="schema-coupon__frame"
        :style="{ height: `${frameHeight}px` }"
        @load="onFrameLoad"
      ></iframe>
      <template #footer>
        <el-button v-db-click @click="visible = false">取消</el-button>
        <el-button type="primary" v-db-click @click="onOk">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
/**
 * 通用页面优惠券标签选择器（form-create 自定义组件 · 契约组件）
 *
 * el-tag 标签展示已选优惠券（可删除），弹窗以 iframe 打开系统选择优惠券页
 * （/admin/marketing.Coupon/list.html?fodder=<field>&embed=1&receive_type=<n>）。
 * iframe 加载后向其 contentWindow 注入 form_create_helper（与 form-create frame
 * 组件同协议），选择页确认时经 helper.set 回传选中行数据、弹窗页脚"确定"经
 * helper.onOk 触发选择页的确认逻辑。值为对象数组
 * [{id, title, coupon_price, use_min_price}]，后端经 Form::couponSelect() 生成规则。
 *
 * 向后兼容扩展 props：
 * - receiveType：筛选发送方式（1 用户领取 / 3 系统赠送），默认 '' 不限
 *
 * 嵌入页通过 postMessage({ type: 'schema-coupon-resize', height }) 回传内容高度，
 * 弹窗 iframe 随数据行数收缩（表格最多 6 行后出滚动条）。
 */
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import setting from '@/setting';

defineOptions({ name: 'SchemaCoupon' });

/** iframe 初始高度（搜索区 + 空表提示），随后由嵌入页 resize 消息校正 */
const FRAME_HEIGHT_MIN = 160;
const FRAME_HEIGHT_MAX = 520;

const props = defineProps({
  /** 已选优惠券对象数组 [{id, title, ...}]（form-create field 值绑定） */
  modelValue: {
    type: Array,
    default: () => [],
  },
  /** 字段名（后端规则 props 传入，用于 iframe 回填协议的 fodder 标识） */
  field: {
    type: String,
    default: '',
  },
  /**
   * 发送方式筛选：1 用户领取 / 3 系统赠送；空字符串表示不限
   * （新人礼等系统赠送场景由 Form::couponSelect props.receiveType=3 传入）
   */
  receiveType: {
    type: [Number, String],
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

/** 弹窗可见性 */
const visible = ref(false);
/** 弹窗页脚"确定"触发的选择页回调列表（选择页经 helper.onOk 注册） */
const okCallbacks = ref([]);
const frameRef = ref(null);
const frameHeight = ref(280);

/** 回填协议标识：与规则 field 一致 */
const fodder = computed(() => props.field);
/** 选择优惠券页地址（embed=1 隐藏页内重复页脚，由本弹窗页脚确认） */
const iframeSrc = computed(() => {
  const query = new URLSearchParams({
    fodder: fodder.value || '',
    embed: '1',
  });
  if (props.receiveType !== '' && props.receiveType !== null && props.receiveType !== undefined) {
    query.set('receive_type', String(props.receiveType));
  }
  return `${setting.routePre}/marketing.Coupon/list.html?${query.toString()}`;
});

/** 已选标签数据（兼容历史纯 id 格式，无券名时显示 ID 兜底） */
const tags = computed(() =>
  (props.modelValue || []).map((item) => (typeof item === 'object' && item !== null ? item : { id: item })),
);

/** 删除标签 */
function remove(index) {
  const next = [...(props.modelValue || [])];
  next.splice(index, 1);
  emit('update:modelValue', next);
}

/** 打开选择弹窗（destroy-on-close 会重建 iframe，历史回调一并失效清空） */
function open() {
  okCallbacks.value = [];
  frameHeight.value = 280;
  visible.value = true;
}

function onClosed() {
  okCallbacks.value = [];
  frameHeight.value = 280;
}

/**
 * iframe 加载完成：向其 contentWindow 注入 form_create_helper
 * （与 form-create frame 组件相同的回填协议）
 */
function onFrameLoad() {
  try {
    const win = frameRef.value?.contentWindow;
    if (!win) return;
    win.form_create_helper = {
      valid: () => {},
      get: () => props.modelValue,
      set: (f, value) => {
        emit('update:modelValue', value || []);
      },
      close: () => {
        visible.value = false;
      },
      onOk: (fn) => okCallbacks.value.push(fn),
      onClose: (fn) => {},
    };
  } catch (e) {
    console.error('[SchemaCoupon] 注入 form_create_helper 失败', e);
  }
}

/** 弹窗页脚"确定"：触发选择页注册的确认逻辑（回填并关闭） */
function onOk() {
  if (!okCallbacks.value.length) {
    visible.value = false;
    return;
  }
  okCallbacks.value.forEach((fn) => fn());
}

/** 接收嵌入页高度回传，弹窗随列表行数收缩 */
function onMessage(event) {
  const data = event?.data;
  if (!data || data.type !== 'schema-coupon-resize') return;
  const height = Number(data.height);
  if (!Number.isFinite(height) || height <= 0) return;
  frameHeight.value = Math.min(FRAME_HEIGHT_MAX, Math.max(FRAME_HEIGHT_MIN, Math.ceil(height)));
}

onMounted(() => {
  window.addEventListener('message', onMessage);
});

onBeforeUnmount(() => {
  window.removeEventListener('message', onMessage);
});
</script>

<style lang="scss" scoped>
.schema-coupon {
  &__tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
  }

  &__tag {
    height: 26px;
    padding: 0 10px;
    font-size: 13px;
  }

  &__empty {
    color: var(--el-text-color-placeholder);
    font-size: 13px;
  }

  &__frame {
    display: block;
    width: 100%;
    border: 0 none;
    vertical-align: top;
  }
}
</style>

<style lang="scss">
/* append-to-body 弹窗样式需非 scoped */
.schema-coupon-dialog {
  .el-dialog__body {
    padding: 8px 12px 0;
    overflow: hidden;
  }
}
</style>
