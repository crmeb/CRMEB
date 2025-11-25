<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-select v-model="configData.activeValue" @change="sliderChange">
          <el-option v-for="(item, index) in configData.list" :value="item.activeValue" :key="index" :label="item.title"></el-option>
        </el-select>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_select',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
    number: {
      type: null,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {},
      timeStamp: '',
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
    number(nVal) {
      this.timeStamp = nVal;
    },
  },
  methods: {
    sliderChange(e) {
      let storage = window.localStorage;
      this.configData.activeValue = e ? e : storage.getItem(this.timeStamp);
      this.$emit('getConfig', { name: 'select', values: e });
    },
  },
};
</script>

<style scoped lang="scss">
.slider-box {
  padding: 0 15px;
  .label {
    color: #999999;
    font-size: 12px;
  }
}
.c_row-item {
  margin-bottom: 20px;
}
</style>
