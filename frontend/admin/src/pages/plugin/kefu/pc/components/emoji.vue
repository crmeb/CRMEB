<template>
  <div class="emoji">
    <ul class="emoji-controller">
      <li
        v-for="(pannel, index) in pannels"
        :key="index"
        v-db-click
        @click="changeActive(index)"
        :class="{ active: index === activeIndex }"
      >
        {{ pannel }}
      </li>
    </ul>
    <ul class="emoji-container">
      <template v-for="(emojiGroup, index) in emojis" :key="index">
        <li v-if="index === activeIndex" style="padding: 0">
          <a
            href="javascript:;"
            v-for="(emoji, index) in emojiGroup"
            :key="index"
            v-db-click
            @click="selectItem(emoji)"
          >
            <span class="emoji-item" :title="emoji" :class="'sprite-' + getPureName(emoji)"></span>
          </a>
        </li>
      </template>
    </ul>
  </div>
</template>
<script setup>
import { ref, computed } from 'vue';

defineOptions({ name: 'emoji' });

const emit = defineEmits(['select']);

// 原代码即引用未定义的全局变量 data（该组件在全库中未被引用，为死代码，保持原状）
const emojiData = data;
const pannels = ref(['表情', '自然', '物品', '地点', '符号']);
const activeIndex = ref(0);

function changeActive(index) {
  activeIndex.value = index;
}
function getPureName(name) {
  return name.replace(/:/g, '');
}
function selectItem(emoji) {
  emit('select', emoji);
}

const emojis = computed(() => {
  return pannels.value.map((item) => {
    return Object.keys(emojiData[item]);
  });
});
</script>

<style lang="scss" scoped>
.emoji {
  width: 380px;
  height: 186px;
  bottom: 30px;
  background: #fff;
  z-index: 10;
  padding: 10px;
  margin-right: 10px;

  .emoji-controller {
    height: 36px;
    overflow: hidden;
    margin-bottom: 0;

    li {
      float: left;
      width: 76px;
      font-size: 12px;
      line-height: 36px;
      cursor: pointer;
      text-align: center;
      position: relative;

      &.active::after {
        content: '';
        width: 100%;
        height: 1px;
        background: #0689dd;
        left: 0;
        bottom: 4px;
        position: absolute;
      }
    }
  }

  .emoji-container {
    height: 140px;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;

    li {
      font-size: 0;
      padding: 5px;

      a {
        float: left;
        overflow: hidden;
        height: 35px;
        transition: all ease-out 0.2s;
        border-radius: 4px;

        &:hover {
          background-color: #d8d8d8;
          border-color: #d8d8d8;
        }

        span {
          width: 25px;
          height: 25px;
          display: inline-block;
          border: 1px solid transparent;
          cursor: pointer;
        }
      }
    }
  }
}
</style>
