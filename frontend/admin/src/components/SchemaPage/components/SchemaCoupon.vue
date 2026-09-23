<template>
  <div class="schema-coupon">
    <div class="schema-coupon__tags">
      <el-tag v-for="(item, index) in tags" :key="item.id" class="schema-coupon__tag" closable @close="remove(index)">
        {{ item.title || `ID:${item.id}` }}
      </el-tag>
      <span v-if="!tags.length" class="schema-coupon__empty">未选择</span>
      <el-button type="primary" plain size="small" v-db-click @click="open">选择优惠券</el-button>
    </div>

    <el-dialog v-model="visible" title="选择优惠券" width="950px" append-to-body destroy-on-close>
      <iframe
        v-if="visible"
        ref="frameRef"
        :src="iframeSrc"
        frameborder="0"
        class="schema-coupon__frame"
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
 * 通用页面优惠券标签选择器（form-create 自定义组件）
 *
 * el-tag 标签展示已选优惠券（可删除），弹窗以 iframe 打开系统选择优惠券页
 * （/admin/marketing.Coupon/list.html?fodder=<field>）。iframe 加载后向其
 * contentWindow 注入 form_create_helper（与 form-create frame 组件同协议），
 * 选择页确认时经 helper.set 回传选中行数据、弹窗页脚"确定"经 helper.onOk
 * 触发选择页的确认逻辑。值为对象数组 [{id, title, coupon_price, use_min_price}]，
 * 后端经 Form::couponSelect() 生成规则。
 */
import { computed, ref } from 'vue';
import setting from '@/setting';

defineOptions({ name: 'SchemaCoupon' });

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
});

const emit = defineEmits(['update:modelValue']);

/** 弹窗可见性 */
const visible = ref(false);
/** 弹窗页脚"确定"触发的选择页回调列表（选择页经 helper.onOk 注册） */
const okCallbacks = ref([]);
const frameRef = ref(null);

/** 回填协议标识：与规则 field 一致 */
const fodder = computed(() => props.field);
/** 选择优惠券页地址（带 .html 后缀命中前端路由） */
const iframeSrc = computed(() => `${setting.routePre}/marketing.Coupon/list.html?fodder=${fodder.value}`);

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
  visible.value = true;
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
    width: 100%;
    height: 540px;
    border: 0 none;
  }
}
</style>
