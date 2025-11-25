<template>
  <div>
    <div class="setUpTop"></div>
    <div class="title" v-if="configData">{{ configData }}</div>
  </div>
</template>

<script>
export default {
  name: 'c_title',
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
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  },
  methods: {},
};
</script>

<style scoped lang="scss">
.setUpTop {
  height: 6px;
  background: rgb(240, 242, 245);
}
.title {
  padding: 20px 15px;
  font-size: 14px;
  color: #333333;
}
</style>
