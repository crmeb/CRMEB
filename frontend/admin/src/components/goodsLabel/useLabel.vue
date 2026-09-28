<template>
  <div class="list">
    <vuedraggable
      class="flex"
      group="label"
      :disabled="labelList.length < 2"
      :list="labelList"
      item-key="id"
      handle=".label"
      @end="onMoveSpec"
      animation="300"
    >
      <template #item="{ element: label, index: j }">
        <div class="label" :key="j">
          <div
            class="label-item"
            :style="{
              backgroundColor: label.bg_color,
              color: label.font_color,
              border: label.border_color ? '1px solid ' + label.border_color : 'none',
            }"
            v-if="!label.image"
          >
            {{ label.name }}
          </div>
          <img :src="label.image" class="img-tag" v-else />
          <i class="el-icon-error" @click="close(label)"></i>
        </div>
      </template>
    </vuedraggable>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import vuedraggable from 'vuedraggable';

defineOptions({ name: 'userLabel' });

const emit = defineEmits(['activeData', 'update:activeId']);

const props = defineProps({
  activeId: {
    type: Array,
    default: () => {
      [];
    },
  },
  listData: {
    type: Array,
    default: () => {
      [];
    },
  },
});

const labelList = ref([]);

watch(
  () => props.activeId,
  (nVal, oVal) => {
    if (nVal != oVal) {
      if (nVal.length) {
        labelList.value = [];
        // 根据nVal 去listData 中查找 不改变查找出来的顺序
        nVal.forEach((item) => {
          props.listData.forEach((item2) => {
            if (item == item2.id) {
              labelList.value.push(item2);
            }
          });
        });
      }
    }
  },
  { deep: true, immediate: true },
);

async function onMoveSpec(event) {
  const { newIndex, oldIndex } = event;
  const label = props.activeId[oldIndex];
  const newLabelList = [...props.activeId];
  newLabelList.splice(oldIndex, 1);
  newLabelList.splice(newIndex, 0, label);
  emit('update:activeId', newLabelList);
}
function close(label) {
  let index = labelList.value.indexOf(label);
  labelList.value.splice(index, 1);
  let activeIndex = props.activeId.findIndex((item) => item == label.id);
  props.activeId.splice(activeIndex, 1);
  emit('activeData', JSON.parse(JSON.stringify(labelList.value)));
  const newLabelList = [...props.activeId];
  emit('update:activeId', newLabelList);
}
</script>

<style lang="scss" scoped>
.list {
  display: flex;
  flex-wrap: wrap;
  .label {
    padding: 2px;
    border-radius: 4px;
    margin: 0 8px 0px 0;
    cursor: move;
    display: flex;
    align-items: center;
    position: relative;
  }
  .label-item {
    height: 22px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0px 8px;

    background: var(--prev-bg-color);
    color: var(--prev-color-text-regular);
    border-radius: 2px;
    cursor: pointer;
    font-size: 12px;
  }
  .el-icon-error {
    position: absolute;
    top: 0;
    right: 0;
    font-size: 14px;
    color: var(--prev-color-text-regular);
    cursor: pointer;
  }

  .img-tag {
    height: 22px;
    border-radius: 2px;
  }
}
</style>
