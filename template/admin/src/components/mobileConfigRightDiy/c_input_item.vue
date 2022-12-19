<template>
  <div class="box" v-if="configData">
    <div class="c_row-item">
      <Col class="label" span="4">
        {{ configData.title }}
      </Col>
      <Col span="19" class="slider-box">
        <div @click="getLink(configData.title)">
          <Input
            :icon="configData.title == '链接' ? 'ios-arrow-forward' : ''"
            :readonly="configData.title == '链接' ? true : false"
            v-model="configData.value"
            :placeholder="configData.place"
            :maxlength="configData.max"
          />
        </div>
      </Col>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import linkaddress from '@/components/linkaddress';
export default {
  name: 'c_input_item',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  components: {
    linkaddress,
  },
  data() {
    return {
      value: '',
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
    linkUrl(e) {
      this.configData.value = e;
    },
    getLink(title) {
      if (title != '链接') {
        return;
      }
      this.$refs.linkaddres.modals = true;
    },
  },
};
</script>

<style scoped lang="stylus">
/deep/.ivu-input
    font-size 13px!important
.c_row-item
    margin-bottom 13px
</style>
