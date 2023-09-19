<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="datas[name].title">
        {{ datas[name].title }}
      </el-col>
      <el-col :span="19" class="slider-box">
        <el-select v-model="datas[name].activeValue" clearable style="width: 350px" @change="sliderChange">
          <el-option
            v-for="(item, index) in datas[name].list"
            :value="item.activeValue"
            :key="index"
            :label="item.title"
          ></el-option>
        </el-select>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_select',
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
  },
  data() {
    return {
      defaults: {},
      datas: this.configData[this.configNum],
    };
  },
  mounted() {
    if (this.name === 'selectConfig') {
      this.bus.$on('upData', (data) => {
        this.datas[this.name].list = data;
        this.bus.$off();
      });
    }
  },
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
      },
      deep: true,
    },
  },
  methods: {
    sliderChange(e) {
      this.$emit('getConfig', { name: 'select', values: e });
    },
  },
};
</script>

<style scoped>
.slider-box {
  margin-top: 10px;
}
</style>
