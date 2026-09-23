<template>
  <div class="c-card-select">
    <div class="card-wrapper">
      <div class="card-title" v-if="defaults.cname">{{ defaults.cname }}</div>
      <div class="card-desc" v-if="defaults.desc">{{ defaults.desc }}</div>
      <div class="card-list" v-if="configData.tabList && configData.tabList.length">
        <div
          class="card-item"
          :class="{ active: configData.tabVal === index }"
          v-for="(item, index) in configData.tabList"
          :key="index"
          @click="handleSelect(index)"
        >
          <div class="card-content">
            <span class="card-name">{{ item.name }}</span>
          </div>
          <div class="card-check" v-if="configData.tabVal === index">
            <i class="el-icon-check"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_card_select' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const defaults = ref({});
const configData = ref({});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

function handleSelect(index) {
  configData.value.tabVal = index;
  emit('getConfig', index);
}
</script>

<style scoped lang="scss">
.c-card-select {
  padding: 0 15px 15px;

  .card-wrapper {
    background: #ffffff;
    border-radius: 8px;

    .card-title {
      padding: 15px 0 10px;
      font-size: 14px;
      color: #333333;
      font-weight: 500;
    }
    .card-desc {
      padding: 0 0 10px;
      font-size: 13px;
      color: #bbbbbb;
    }
    .card-list {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;

      .card-item {
        position: relative;
        flex: 0 0 calc(25% - 8px);
        height: 32px;
        background: #f7f8fa;
        border-radius: 6px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;

        &:hover {
          background: #f0f2f5;
        }

        &.active {
          background: #fff;
          border-color: var(--prev-color-primary);

          .card-name {
            color: var(--prev-color-primary);
            font-weight: 500;
          }
        }

        .card-content {
          text-align: center;

          .card-name {
            font-size: 14px;
            color: #666666;
            display: block;
          }
        }

        .card-check {
          position: absolute;
          bottom: 0;
          right: 0;
          width: 13px;
          height: 13px;
          background: var(--prev-color-primary);
          border-radius: 6px 0 3px 0px;
          display: flex;
          align-items: center;
          justify-content: center;

          i {
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
          }
        }
      }
    }
  }
}
</style>
