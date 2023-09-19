<template>
  <div class="c_row-item">
    <el-col class="c_label">{{ configData.title }}</el-col>
    <el-col class="acea-row row-right row-middle">
      <el-switch :active-value="1"  :inactive-value="0" v-model="configData.val" />
    </el-col>
  </div>
</template>

<script>
export default {
  name: 'c_is_show',
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
  methods: {},
};
</script>

<style scoped lang="stylus">
.c_row-item{
    display flex
    justify-content space-between
    align-items center
    margin-bottom 20px
}
</style>
