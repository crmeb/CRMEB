<template>
  <div class="left">
    <div class="wrapper" :style="'height:' + height + 'px;'">
      <div class="list" v-for="(item, index) in leftMenu" :key="index">
        <div class="tips" @click="item.isOpen = !item.isOpen">
          {{ item.title }}
          <div class="iconfont iconyou" v-if="!item.isOpen"></div>
          <div class="iconfont iconxia" v-else></div>
        </div>
        <draggable
          class="dragArea list-group"
          :list="item.list"
          :group="{ name: 'people', pull: 'clone', put: false }"
          :clone="cloneDog"
          dragClass="dragClass"
          filter=".search , .navbar , .homeComb , .service"
          :item-key="getDraggableItemKey"
        >
          <template #item="{ element }">
            <div
              class="list-group-item"
              :class="{
                search: element.cname == '搜索框',
                navbar: element.cname == '选项卡',
                homeComb: element.cname == '轮播搜索',
                service: element.cname == '悬浮按钮',
              }"
              @click="$emit('pick', element)"
              v-show="item.isOpen"
            >
              <div>
                <div class="position" style="display: none">释放鼠标将组建添加到此处</div>
                <svg class="conter iconfont icon svg-icon" aria-hidden="true">
                  <use :xlink:href="element.icon"></use>
                </svg>
                <p class="conter">{{ element.cname }}</p>
              </div>
            </div>
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script setup>
import draggable from 'vuedraggable';
import { getDraggableItemKey } from '@/utils/draggableKey';

defineProps({
  leftMenu: { type: Array, required: true },
  height: { type: [Number, String], default: '' },
  cloneDog: { type: Function, required: true },
});

defineEmits(['pick']);
</script>
