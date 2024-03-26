<template>
  <div class="setUp">
    <div class="tab">
      <div class="item" :class="{ on: tabVal == 0 }" @click="onClickTab(0)"><span class="text">内容</span></div>
      <div class="item" :class="{ on: tabVal == 1 }" @click="onClickTab(1)"><span class="text">样式</span></div>
      <div :class="tabVal == 0 ? 'bg-left' : 'bg-right'"></div>
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
      configData: {},
      tabVal: 0,
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.$nextTick((e) => {
          this.defaults = nVal;
          this.configData = nVal[this.configNme];
          this.tabVal = this.configData.tabVal + '';
        });
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      // this.configData = this.configObj[this.configNme];
    });
  },
  methods: {
    onClickTab(e) {
      this.configData.tabVal = e;
    },
  },
};
</script>

<style scoped lang="scss">
.setUp {
  margin-bottom: 16px;
}
.tab {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  background: #f2f2f2;
  font-size: 12px;
  border-radius: 20px;
  height: 30px;
  width: 100%;
  position: relative;

  .item {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50%;
    padding: 5px 10px;
    height: 30px;
    cursor: pointer;
    transition: all 0.3s;
    .text {
      z-index: 2;
    }
  }
  .item.on {
    color: #fff;
    border-radius: 20px;
  }
  .bg-left,
  .bg-right {
    position: absolute;
    left: 0;
    width: 50%;
    padding: 5px 10px;
    height: 30px;
    background-color: var(--prev-color-primary);
    z-index: 1;
    transition: 0.3s ease-in-out;
  }
  .bg-left {
    border-radius: 20px 0 0 20px;
    transform: translateX(0%);
  }
  .bg-right {
    border-radius: 0 20px 20px 0;
    transform: translateX(100%);
  }
}
.setUp ::v-deep .ivu-tabs-nav-scroll {
  padding: 0 30px;
}
.setUp ::v-deep .ivu-tabs-nav .ivu-tabs-tab {
  padding: 8px 45px;
}
</style>
