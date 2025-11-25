<template>
  <!-- 此组件目前没用，留着方便以后开发再用 -->
  <div class="acea-row row-top" style="margin-bottom: 20px" v-if="configData">
    <el-checkbox-group v-model="configData.type" @change="checkboxChange">
      <div>
        <el-checkbox :label="1">
          <span>商品分类</span>
        </el-checkbox>
        <el-cascader
          :data="configData.list"
          placeholder="请选择商品分类"
          :props="{ multiple: true, checkStrictly: true, emitPath: false }"
          v-model="configData.activeValue"
          filterable
          @change="sliderChange"
        ></el-cascader>
      </div>
    </el-checkbox-group>
  </div>
</template>

<script>
export default {
  name: 'c_goods_search',
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
      formData: {
        type: 0,
      },
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
  methods: {
    checkboxChange(e) {
      console.log('dfdf', e);
      this.$emit('getConfig', e);
    },
    sliderChange(e) {
      let storage = window.localStorage;
      this.configData.activeValue = e ? e : storage.getItem(this.timeStamp);
      this.$emit('getConfig', { name: 'cascader', values: e });
    },
  },
};
</script>

<style></style>
