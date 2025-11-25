<template>
  <div>
    <div class="c_row-item" v-if="configData[this.configNum]">
      <el-col :span="8" class="c_label">{{ configData[this.configNum][name].title }}</el-col>
      <el-col :span="14" class="color-box">
        <div
          class="color-item acea-row row-middle"
          v-for="(color, key) in configData[this.configNum][name].color"
          :key="key"
        >
          <el-color-picker v-model="color.item" @change="changeColor($event, color)"></el-color-picker
          ><span class="white-space-nowrap" v-db-click @click="resetBgA(color, index, key)">重置</span>
        </div>
      </el-col>
    </div>
  </div>
</template>

<script>
let restColor = '';
export default {
  name: 'c_bg_color',
  props: {
    configData: {
      type: Object,
    },
    name: {
      type: String,
    },
    configNum: {
      type: Number | String,
      default: 'default',
    },
  },
  data() {
    return {
      defaults: {},
      bgColor: {
        bgStar: '',
        bgEnd: '',
      },
      oldColor: {
        bgStar: '',
        bgEnd: '',
      },
      index: 0,
    };
  },
  created() {
    this.defaults = this.configData[this.configNum][this.configNum];
  },
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.defaults = nVal[this.configNum];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    changeColor(e, color) {
      if (!e) {
        // color.item = 'transparent';
      }
      // this.$emit('getConfig', this.defaults)
    },
    // 重置
    resetBgA(color, index, key) {
      color.item = this.configData[this.configNum][this.name].default[key].item;
    },
  },
};
</script>

<style lang="scss" scoped>
.c_row-item {
  margin-top: 10px;
  margin-bottom: 10px;
  ::v-deep .ivu-select-dropdown {
    left: -27px !important;
  }
}
.color-box {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  .color-item {
    margin-left: 15px;

    span {
      margin-left: 5px;
      color: #999;
      font-size: 13px;
      cursor: pointer;
    }
  }
}
</style>
