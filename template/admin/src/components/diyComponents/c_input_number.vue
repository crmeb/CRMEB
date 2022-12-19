<template>
  <div class="numbox" v-if="datas[name]">
    <div class="c_row-item">
      <Col class="label" span="4">
        <span v-if="datas[name].show">{{ datas[name].title }}</span>
        <span v-else>数量</span>
      </Col>
      <Col span="19" class="slider-box">
        <Input
          v-model="datas[name].val"
          type="number"
          placeholder="请输入数量"
          style="text-align: right; width: 350px"
          @on-blur="numberVal(datas[name].val)"
          @on-change="maxNum(datas[name].val)"
        />
      </Col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_input_number',
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
      sliderWidth: 0,
      datas: this.configData[this.configNum],
    };
  },
  mounted() {},
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    numberVal(val) {
      let r = /^\+?[1-9][0-9]*$/;
      if (!r.test(val)) {
        this.datas[this.name].val = 3;
      }
    },
    maxNum(val) {
      if (val >= 50) {
        this.datas[this.name].val = 50;
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.numbox {
  display: flex;
  align-items: center;
  margin: 20px 0 10px 0;

  span {
    width: 80px;
    color: #999;
  }
}

/* font-size 12px */
.c_row-item {
  width: 100%;
}
</style>
