<template>
  <div class="steps df-cc">
    <div
      class="steps-item"
      :class="index <= isActive ? 'active' : ''"
      v-for="(step, index) in stepList"
      :key="index"
      v-db-click
      @click="stepActive(index)"
    >
      <div class="dot df-cc">{{ index + 1 }}</div>
      <span class="title">{{ step }}</span>
      <div class="line" :class="lineWidth()" v-if="index < stepList.length - 1"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: '',

  props: {
    stepList: {
      type: Array,
      default: () => {
        return [];
      },
    },
    isActive: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {};
  },
  created() {},
  mounted() {},
  methods: {
    lineWidth() {
      let i = this.stepList.length;
      let width;
      switch (i) {
        case 3:
          width = 'wd160';
        case 4:
          width = 'wd120';
        default:
          width = 'wd100';
      }
      return width;
    },
    stepActive(index) {
      this.$emit('stepActive', index);
    },
  },
};
</script>
<style lang="scss" scoped>
.df-cc {
  display: flex;
  align-items: center;
  justify-content: center;
}
.steps {
  &-item {
    display: flex;
    align-items: center;
    width: max-content;
    .dot {
      width: 28px;
      height: 28px;
      color: #c0c4cc;
      border: 1px solid #c0c4cc;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 8px;
    }
    .title {
      font-size: 16px;
      font-weight: 400;
      color: #909399;
      line-height: 16px;
      white-space: nowrap;
    }
    .line {
      height: 1px;
      margin: 0 20px;
      background: #dddddd;
    }
    .wd160 {
      width: 160px;
    }
    .wd120 {
      width: 120px;
    }
    .wd100 {
      width: 100px;
    }
  }
  &-item.active {
    .title {
      font-size: 16px;
      font-weight: 500;
      color: #303133;
      line-height: 16px;
    }
    .dot {
      width: 28px;
      height: 28px;
      background: var(--prev-color-primary);
      border: 1px solid var(--prev-color-primary);
      color: #fff;
    }
  }
}
</style>
