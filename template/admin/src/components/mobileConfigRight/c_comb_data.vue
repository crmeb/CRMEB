<template>
  <div class="c_radio mb15" v-if="configData">
    <div class="c_row-item" :class="{ on: configData.type == 'ranges' }">
      <el-col class="c_label on" :span="configData.type == 'ranges' ? '' : 4">
        {{ configData.title }}
      </el-col>
      <el-col class="color-box" :span="configData.type == 'ranges' ? 24 : 19">
        <div>
          <el-radio-group v-model="configData.tabVal" @input="radioChange()">
            <el-radio :label="key" v-for="(radio, key) in configData.tabList" :key="key">
              <span>{{ radio.name }}</span>
            </el-radio>
          </el-radio-group>
        </div>
        <div>
          <el-radio-group v-model="configData.tabData" @input="radioDataChange()" v-if="configData.tabVal == 0">
            <el-radio :label="key" v-for="(radio, key) in configData.dataList" :key="key + 'data'">
              <span>{{ radio.name }}</span>
            </el-radio>
          </el-radio-group>
        </div>
        <DatePicker
          type="date"
          v-model="configData.specifyDate"
          placeholder="请选择"
          style="margin-top: 6px"
          v-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'data'"
        />
        <TimePicker
          type="time"
          format="HH:mm"
          v-model="configData.specifyDate"
          placeholder="请选择"
          style="margin-top: 6px"
          v-else-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'time'"
        />
        <DatePicker
          type="daterange"
          placement="bottom-end"
          v-model="configData.specifyDate"
          format="yyyy/MM/dd"
          placeholder="请选择"
          style="margin-top: 6px"
          v-else-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'daterange'"
          @change="getDaterange"
        />
        <TimePicker
          format="HH:mm"
          type="timerange"
          v-model="configData.specifyDate"
          placement="bottom-end"
          placeholder="请选择"
          style="margin-top: 6px"
          v-else-if="configData.tabData == 1 && configData.tabVal == 0 && configData.type == 'timerange'"
        />
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_comb_data',
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
    getDaterange(e) {
      this.$emit('getConfig', { type: 2, val: e });
    },
    radioChange(e) {
      this.$emit('getConfig', { type: 0, val: e });
    },
    radioDataChange(e) {
      this.$emit('getConfig', { type: 1, val: e });
    },
  },
};
</script>

<style scoped lang="scss">
.ivu-date-picker {
  width: 100%;
}
.c_radio {
  .c_row-item {
    align-items: unset;
    &.on {
      display: block;
      .c_label {
        text-align: left;
        margin-bottom: 3px;
      }
    }
  }
  .c_label {
    color: #000;
    margin-right: 15px;
    margin-top: 4px;
    &.on {
      text-align: right;
      color: #666;
    }
  }
  ::v-deep.ivu-radio-wrapper {
    margin: 5px 25px 5px 0;
  }
  ::v-deep.ivu-radio {
    margin-right: 6px;
  }
}
</style>
