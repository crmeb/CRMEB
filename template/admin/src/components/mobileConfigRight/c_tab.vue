<template>
  <div style="margin-bottom: 20px">
    <div class="title-tips" v-if="configData.tabList">
      <span>{{ configData.title }}</span
      >{{ configData.tabList[configData.tabVal].name }}
    </div>
    <div class="radio-box" :class="{ on: configData.type == 1 }">
      <el-radio-group v-model="configData.tabVal" type="button" size="large" @input="radioChange($event)">
        <el-radio :label="index" v-for="(item, index) in configData.tabList" :key="index">
          <span class="iconfont-diy" :class="item.icon" v-if="item.icon"></span>
          <span v-else>{{ item.name }}</span>
        </el-radio>
      </el-radio-group>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_tab',
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
      formData: {
        type: 0,
      },
      defaults: {},
      configData: {},
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  },
  methods: {
    radioChange(e) {
      if (this.defaults.picStyle) {
        this.defaults.picStyle.tabVal = 0;
      }
      this.$emit('getConfig', e);
    },
  },
};
</script>

<style scoped lang="scss">
.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper:after {
  height: 0;
}
.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper {
  height: 32px;
  width: 120px;
  text-align: center;
}
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
.iconfont-diy {
  font-size: 20px;
  line-height: 18px;
}
</style>
