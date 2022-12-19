<template>
  <div class="slider-box">
    <div class="c_row-item">
      <Col class="label" span="4" v-if="configData.title">
        {{ configData.title }}
      </Col>
      <Col span="19" class="slider-box">
        <Select v-model="configData.activeValue" @on-change="sliderChange">
          <Option v-for="(item, index) in configData.list" :value="item.activeValue" :key="index">{{
            item.title
          }}</Option>
        </Select>
      </Col>
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

<style scoped lang="stylus">
.c_row-item {
  margin-top: 20px;
  margin-bottom: 20px;
}
</style>
