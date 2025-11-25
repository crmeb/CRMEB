<template>
  <div class="c_radio" :class="configData.type == 'form' ? 'mb15' : 'on mb5'" v-if="configData">
    <div class="c_row-item">
      <el-col class="c_label" :class="configData.type == 'form' ? 'on' : ''" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col class="color-box" :span="configData.type == 'form' ? 19 : 18">
        <el-radio-group v-model="configData.tabVal" @input="radioChange()">
          <el-radio :label="key" v-for="(radio, key) in configData.tabList" :key="key">
            <span>{{ radio.name }}</span>
          </el-radio>
        </el-radio-group>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_radio',
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
      this.$emit('getConfig', e, 'radio');
    },
  },
};
</script>

<style scoped lang="scss">
.c_radio {
  &.on {
    padding: 0 15px;
    .c_label {
      color: #999999;
      font-size: 12px;
    }
    ::v-deep.ivu-radio-wrapper {
      margin: 5px 25px 15px 0;
    }
  }
  .c_row-item {
    align-items: unset;
  }
  .c_label {
    color: #000;
    margin-right: 15px;
    &.on {
      text-align: right;
      color: #666;
    }
  }
  ::v-deep.ivu-radio-wrapper {
    margin: 5px 25px 5px 0;
    &:nth-last-child(1) {
      margin-right: 0;
    }
  }
  ::v-deep.ivu-radio {
    margin-right: 6px;
  }
}
</style>
