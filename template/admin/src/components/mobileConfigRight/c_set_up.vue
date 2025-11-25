<template>
  <div>
    <div class="setUpTop"></div>
    <div class="setUp">
      <div class="label" v-if="defaults.cname">
        {{ defaults.cname }}
      </div>
      <div class="title acea-row">
        <div
          class="item"
          :class="index == current ? 'on' : ''"
          v-for="(item, index) in list"
          :key="index"
          @click="onClickTab(index)"
        >
          {{ item }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_set_up',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {
        tabVal: 0,
      },
      list: ['内容', '样式'],
      current: 0,
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
        this.current = this.configData.tabVal;
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      this.$nextTick((e) => {
        this.current = this.configData.tabVal;
      });
    });
  },
  methods: {
    onClickTab(index) {
      this.configData.tabVal = index;
      this.current = index;
    },
    // onClickTab (e) {
    //     this.$emit('getConfig', e);
    // }
  },
};
</script>

<style scoped lang="scss">
.setUpTop {
  height: 6px;
  background: rgb(240, 242, 245);
}
.setUp {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 56px;
  padding: 0 15px;

  .label {
    font-size: 16px;
    color: #333333;
  }

  .title {
    width: 140px;
    height: 28px;
    background: #f9f9f9;
    border-radius: 14px;
    line-height: 28px;
    font-size: 12px;
    color: #333333;
    .item {
      width: 50%;
      text-align: center;
      cursor: pointer;
      &.on {
        width: 70px;
        background: var(--prev-color-primary);
        border-radius: 14px;
        color: #ffffff;
        font-size: 12px;
      }
    }
  }
}
.setUp ::v-deep.ivu-tabs-nav-scroll {
  padding: 0 30px;
}
.setUp ::v-deep.ivu-tabs-nav .ivu-tabs-tab {
  padding: 8px 45px;
}
</style>
