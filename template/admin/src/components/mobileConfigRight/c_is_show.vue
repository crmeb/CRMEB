<template>
  <div class="c_row-item">
    <el-col
      class="c_label"
      :class="{ on: configData.type == 'form', on2: configData.type == 'ranges' }"
      :span="configData.type == 'form' || configData.type == 'ranges' ? 4 : ''"
      >{{ configData.title }}</el-col
    >
    <el-col :span="configData.type == 'form' || configData.type == 'ranges' ? 19 : ''">
      <el-switch
        class="defineSwitch"
        :active-value="1"
        :inactive-value="0"
        v-model="configData.val"
        active-text="开启"
        inactive-text="关闭"
      >
      </el-switch>
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

<style scoped lang="scss">
.c_row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  .c_label {
    &.on {
      color: #666;
      text-align: right;
    }
    &.on2 {
      text-align: left;
      color: #666;
    }
  }
}
</style>
