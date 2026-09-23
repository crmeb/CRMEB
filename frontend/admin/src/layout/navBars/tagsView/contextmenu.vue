<template>
  <div>
    <transition name="el-zoom-in-center">
      <ul
        class="el-dropdown-menu el-popper el-dropdown-menu--medium custom-contextmenu"
        :style="`top: ${dropdowns.y}px;left: ${dropdowns.x}px;`"
        x-placement="bottom-end"
        id="contextmenu"
        v-show="isShow"
      >
        <li
          class="el-dropdown-menu__item"
          v-for="(v, k) in dropdownList"
          :key="k"
          v-db-click
          @click="onCurrentContextmenuClick(v.id)"
        >
          <template v-if="!v.affix">
            <i :class="v.icon"></i>
            <span>{{ $t(v.txt) }}</span>
          </template>
        </li>
        <div x-arrow class="popper__arrow" :style="{ left: `${arrowLeft}px` }"></div>
      </ul>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

defineOptions({ name: 'layoutTagsViewContextmenu' });

const props = defineProps({
  dropdown: {
    type: Object,
  },
});

const emit = defineEmits(['currentContextmenuClick']);

const isShow = ref(false);
const dropdownList = ref([
  { id: 0, txt: 'message.tagsView.refresh', affix: false, icon: 'el-icon-refresh-right' },
  { id: 1, txt: 'message.tagsView.close', affix: false, icon: 'el-icon-close' },
  { id: 2, txt: 'message.tagsView.closeOther', affix: false, icon: 'el-icon-circle-close' },
  { id: 3, txt: 'message.tagsView.closeAll', affix: false, icon: 'el-icon-folder-delete' },
]);
const path = ref({});
const arrowLeft = ref(5);

const dropdowns = computed(() => {
  // 99 为 `Dropdown 下拉菜单` 的宽度
  if (props.dropdown.x + 99 > document.documentElement.clientWidth) {
    return {
      x: document.documentElement.clientWidth - 99 - 5,
      y: props.dropdown.y,
    };
  } else {
    return props.dropdown;
  }
});

onMounted(() => {
  // 监听页面监听进行右键菜单的关闭
  document.body.addEventListener('click', closeContextmenu);
});

onUnmounted(() => {
  // 页面卸载时，移除右键菜单监听事件
  document.body.removeEventListener('click', closeContextmenu);
});

// 当前项菜单点击
function onCurrentContextmenuClick(id) {
  emit('currentContextmenuClick', { id, path: path.value });
}

// 打开右键菜单：判断是否固定，固定则不显示关闭按钮
function openContextmenu(item) {
  path.value = item.path;
  item.meta.isAffix ? (dropdownList.value[1].affix = true) : (dropdownList.value[1].affix = false);
  closeContextmenu();
  setTimeout(() => {
    isShow.value = true;
  }, 80);
}

// 关闭右键菜单
function closeContextmenu() {
  isShow.value = false;
}

// 监听下拉菜单位置
watch(
  () => props.dropdown,
  ({ x }) => {
    if (x + 99 > document.documentElement.clientWidth)
      arrowLeft.value = 99 - (document.documentElement.clientWidth - x);
    else arrowLeft.value = 10;
  },
  { deep: true },
);

defineExpose({ openContextmenu, closeContextmenu });
</script>

<style scoped lang="scss">
.custom-contextmenu {
  transform-origin: center top;
  z-index: 2190;
  position: fixed;
  .el-dropdown-menu__item {
    font-size: 12px !important;
    white-space: nowrap;
    i {
      font-size: 12px !important;
    }
  }
}
</style>
