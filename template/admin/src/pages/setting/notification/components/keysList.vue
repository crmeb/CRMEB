<template>
  <div>
    <div class="virtual-data mb10" v-for="(item, index) in keyList" :key="index">
      <el-input
        class="mr10"
        type="text"
        v-model.trim="item.key"
        style="width: 150px"
        placeholder="请输入字段名称"
        disabled
      ></el-input>

      <span class="mr10 virtual-title">-></span>

      <el-select v-model="item.value" placeholder="请选择">
        <el-option v-for="item in variableList" :key="item.value" :label="item.label" :value="item.value"> </el-option>
      </el-select>

      <!-- <el-button v-if="keyList.length" class="deteal-btn mr10" v-db-click @click="remove(index)"> 删除 </el-button> -->
    </div>

    <div class="add-more">
      <!-- <el-button class="h-33" type="primary" v-db-click @click="add"> 新增 </el-button> -->
    </div>
  </div>
</template>

<script>
export default {
  props: {
    keyList: {
      type: Array,
      required: true,
    },
    variableList: {
      type: Array,
      required: true,
    },
  },
  methods: {
    add() {
      this.$emit('add');
    },
    remove(index) {
      this.$emit('remove', index);
    },
  },
};
</script>

<style scoped>
.virtual-title {
  font-size: 12px;
  color: #303133;
}
</style>
