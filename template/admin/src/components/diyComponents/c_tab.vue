<template>
  <div>
    <div class="title-tips" v-if="datas[name].tabList">
      <span>选择模板</span>{{ datas[name].tabList[datas[name].tabVal].name }}
    </div>
    <div class="radio-box" :class="{ on: datas[name].type == 1 }">
      <el-radio-group v-model="datas[name].tabVal" size="mini" type="button" @input="radioChange()">
        <el-radio-button :label="index" v-for="(item, index) in datas[name].tabList" :key="index">
          <span class="iconfont" :class="item.icon" v-if="item.icon"></span>
          <span v-else>{{ item.name }}</span>
        </el-radio-button>
      </el-radio-group>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_tab',
  props: {
    name: {
      type: String,
    },
    configData: {
      type: null,
    },
    configNum: {
      type: Number | String,
      default: 'default',
    },
    moduleName: {
      type: String,
    },
  },
  data() {
    return {
      formData: {
        type: 0,
      },
      defaults: {},
      datas: this.configData[this.configNum],
    };
  },
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
        this.$store.commit('moren/upDataGoodList', { name: this.moduleName, type: this.datas.tabConfig.tabVal });
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {});
  },
  methods: {
    radioChange(e) {
      this.$emit('getConfig', this.datas[this.name].tabVal);
      this.$store.commit('moren/upDataGoodList', { name: this.moduleName, type: this.datas[this.name].tabVal });
    },
  },
};
</script>

<style lang="scss" scoped>
.radio-box {
  ::v-deep .ivu-radio-group-button {
    display: flex;
    width: 100%;
    .ivu-radio-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
  &.on {
    ::v-deep .ivu-radio-group-button {
      .ivu-radio-wrapper {
        flex: 1;
      }
    }
  }
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
.iconfont {
  font-size: 20px;
  line-height: 18px;
}
</style>
