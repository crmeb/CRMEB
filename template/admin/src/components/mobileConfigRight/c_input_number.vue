<template>
  <div
    class="numbox"
    :class="configData.type == 'form' ? 'on' : configData.type == 'words' ? 'on2' : ''"
    v-if="configData"
  >
    <div class="c_row-item">
      <el-col class="label" :span="4">
        <span>{{ configData.title || '商品数量' }}</span>
      </el-col>
      <el-col :span="configData.type == 'form' ? 19 : 18" class="slider-box">
        <!--<el-input v-model="configData.val" type="number" placeholder="请输入数量" @change="bindChange" style="text-align: right;"/>-->
        <div class="acea-row row-middle">
          <el-input-number
            v-model="configData.val"
            :placeholder="configData.placeholder"
            :step="1"
            :max="100"
            :min="1"
            @change="bindChange"
          ></el-input-number>
        </div>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_input_number',
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
      sliderWidth: 0,
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
    bindChange() {
      this.$emit('getConfig', { name: 'number', numVal: this.configData.val });
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .ivu-input-number {
  width: 100%;
  font-size: 12px !important;
}

.numbox {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  padding: 0 15px;

  &.on2 {
    border-top: 1px dashed #eeeeee;
    padding: 20px 0 0 0;
    margin-top: 20px;
    margin-left: 15px;
    margin-right: 15px;

    ::v-deep .ivu-input-number {
      width: 91%;
    }

    .slider-box {
      .unit {
        font-size: 12px;
        color: #bbbbbb;
        margin-left: 10px;
      }
    }
  }

  &.on {
    padding: 0;
    ::v-deep .ivu-input-number {
      font-size: 13px !important;
    }

    .c_row-item {
      .label {
        text-align: right;
        font-size: 13px;

        span {
          color: #666;
        }
      }
    }
  }

  span {
    width: 80px;
    color: #999;
  }
}

.c_row-item {
  width: 100%;

  .label {
    color: #999999;
    font-size: 12px;
  }
}
</style>
