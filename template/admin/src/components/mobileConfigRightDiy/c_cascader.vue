<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="19" class="slider-box">
        <el-cascader
          :options="configData.list"
          placeholder="请选择商品分类"
          v-model="configData.activeValue"
          filterable
          @change="sliderChange"
          :props="{ multiple: false, checkStrictly: true, emitPath: false }"
          clearable
        ></el-cascader>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_cascader',
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
      this.$emit('getConfig', { name: 'cascader', values: e });
    },
  },
};
</script>

<style scoped lang="stylus">
.c_row-item {
  margin-top: 20px;
  margin-bottom: 20px;
  .label{
    color: #999;
  }
}
</style>
