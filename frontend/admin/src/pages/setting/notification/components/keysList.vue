<template>
  <div class="keys-list">
    <!-- 字段→变量映射：字段名可手动输入或从模板内容 {{字段名.data}} 自动提取，用户为每个字段选择业务变量 -->
    <!-- 行顺序支持拖拽调整（仅左侧手柄可拖，避免干扰输入框），顺序即后端组装模板变量的顺序 -->
    <vuedraggable class="keys-drag" :list="keyList" :item-key="getItemKey" handle=".drag-handle" animation="300">
      <template #item="{ element: item, index }">
        <div class="virtual-data mb10">
          <el-icon class="drag-handle mr10"><Rank /></el-icon>
          <el-input
            class="mr10"
            type="text"
            v-model.trim="item.key"
            style="width: 150px"
            placeholder="请输入字段名称"
          ></el-input>
          <span class="mr10 virtual-title">-></span>
          <el-select v-model="item.value" placeholder="请选择" filterable clearable style="width: 220px">
            <el-option v-for="v in variableList" :key="v.key" :label="v.label" :value="v.key"> </el-option>
          </el-select>
          <el-button v-if="keyList.length" class="deteal-btn ml10" type="text" @click="remove(index)">删除</el-button>
        </div>
      </template>
    </vuedraggable>
    <!-- 新增字段行 -->
    <div class="add-more">
      <el-button class="h-33" type="primary" @click="add">新增</el-button>
    </div>
  </div>
</template>

<script setup>
/**
 * 模板字段→业务变量映射组件
 *
 * 字段名（item.key）可由用户手动输入，也可从模板内容 {{字段名.data}} 自动提取；
 * 用户为每个字段选择对应的业务变量（item.value），发送时后端按映射组装模板数据。
 * 行顺序可通过左侧手柄拖拽调整：vuedraggable :list 模式原地改写数组，
 * 与父级 keyList ref 共享同一引用，无需事件同步。
 */
import vuedraggable from 'vuedraggable';

defineOptions({ name: 'keysList' });

// 字段映射列表 [{key: 模板字段名, value: 业务变量名}, ...]
defineProps({
  keyList: {
    type: Array,
    required: true,
  },
  // 可选业务变量列表 [{value, label}, ...]
  variableList: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['add', 'remove']);

// 行对象无稳定 id，用 WeakMap 按对象身份生成拖拽 key：重排后保持稳定，也不污染行数据
const keyIds = new WeakMap();
let keySeq = 0;
function getItemKey(item) {
  if (!keyIds.has(item)) {
    keyIds.set(item, ++keySeq);
  }
  return keyIds.get(item);
}

function add() {
  emit('add');
}
function remove(index) {
  emit('remove', index);
}
</script>

<style scoped lang="scss">
.drag-handle {
  cursor: move;
  color: var(--prev-color-text-placeholder);
  flex-shrink: 0;
  &:hover {
    color: var(--prev-color-primary);
  }
}
.virtual-title {
  font-size: 12px;
  color: var(--prev-color-text-regular);
}
.deteal-btn {
  color: #ed4014;
}
.add-more {
  margin-top: 5px;
}
.h-33 {
  height: 33px;
}
</style>
