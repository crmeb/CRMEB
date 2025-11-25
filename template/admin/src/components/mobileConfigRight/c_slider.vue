<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-slider v-model="configData.val" show-input :min="configData.min"></el-slider>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_slider',
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
  },
  methods: {
    sliderChange(e) {
      this.$emit('getConfig', e);
    },
  },
};
</script>

<style scoped lang="scss">
.c_row-item {
  margin: 0 15px 20px 15px;
  .label {
    color: #999;
    font-size: 12px;
  }
}
</style>
