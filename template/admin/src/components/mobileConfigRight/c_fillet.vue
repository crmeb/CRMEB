<template>
  <div class="fillets" v-if="configData">
    <div class="c_row-item flex justify-between">
      <div class="c_label">
        {{ configData.title }}
        <span>{{ configData.list[configData.type].val }}</span>
      </div>
      <el-radio-group v-model="configData.type" type="button" size="mini" @input="radioChange($event)">
        <el-radio-button :label="key" v-for="(radio, key) in configData.list" :key="key">
          <span class="iconfont-diy" :class="radio.icon" v-if="radio.icon"></span>
          <span v-else>{{ radio.val }}</span>
        </el-radio-button>
      </el-radio-group>
    </div>
    <div class="c_row-item on" v-if="configData.type">
      <div class="c_label">{{ configData.valName }}</div>
      <div class="right">
        <el-input
          class="input"
          :class="index > 1 ? '' : 'on'"
          v-model="configData.valList[index].val"
          v-for="(item, index) in configData.valList"
          :key="index"
        >
          <template #prefix>
            <span class="iconfont iconzuoshangjiao" v-if="index == 0"></span>
            <span class="iconfont iconyoushangjiao" v-if="index == 1"></span>
            <span class="iconfont iconzuoxiajiao" v-if="index == 2"></span>
            <span class="iconfont iconyouxiajiao" v-if="index == 3"></span>
          </template>
        </el-input>
      </div>
    </div>
    <div class="c_row-item" v-else>
      <el-col class="c_label" :span="4" v-if="configData.valName">
        {{ configData.valName }}
      </el-col>
      <el-col :span="18">
        <el-slider v-model="configData.val" show-input :min="configData.min"></el-slider>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_fillet',
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
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    radioChange(e) {
      this.$emit('getConfig', { name: 'radio', values: e });
    },
    sliderChange(e) {},
  },
};
</script>

<style scoped lang="scss">
.fillets {
  padding: 0 15px;
}
.txt_tab {
  margin-top: 20px;
}
.c_row-item {
  margin-bottom: 20px;
  &.on {
    align-items: flex-start;
    .c_label {
      margin-top: 8px;
    }
  }
  .c_label {
    font-size: 12px;
    span {
      margin-left: 34px;
    }
  }
  .right {
    width: 204px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    ::v-deep .el-input__inner {
      text-align: center;
    }
    .input {
      width: 95px;
      &.on {
        margin-bottom: 14px;
      }
    }
  }
  ::v-deep .el-input__prefix {
    top: 6px;
    left: 10px;
  }
}
.row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.iconfont {
  font-size: 10px;
  color: #666666;
}
</style>
