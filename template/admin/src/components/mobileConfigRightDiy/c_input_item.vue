<template>
  <div class="box" v-if="configData">
    <div class="c_row-item">
      <el-col class="label" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col :span="19" class="slider-box">
        <div @click="getLink(configData.title)">
          <el-input
            :suffix-icon="configData.title == '链接' ? 'el-icon-arrow-right' : ''"
            :readonly="configData.title == '链接' ? true : false"
            v-model="configData.value"
            :placeholder="configData.place"
            :maxlength="configData.max"
          />
        </div>
      </el-col>
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
::v-deep .ivu-input
    font-size 13px!important
.c_row-item
    margin-bottom 13px
</style>
