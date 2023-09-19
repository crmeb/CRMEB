<template>
  <div>
    <el-row class="expand-row">
      <el-col :span="6" class="mr20">
        <span class="expand-key">商品分类：</span>
        <span class="expand-value">{{ row.cate_name }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">商品市场价格：</span>
        <span class="expand-value">{{ row.ot_price }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">成本价：</span>
        <span class="expand-value">{{ row.cost }}</span>
      </el-col>
    </el-row>
    <el-row class="expand-row">
      <el-col :span="6" class="mr20">
        <span class="expand-key">收藏：</span>
        <span class="expand-value">{{ row.collect }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">虚拟销量：</span>
        <span class="expand-value">{{ row.ficti }}</span>
      </el-col>
    </el-row>
  </div>
</template>
<style scoped>
.expand-row {
  margin-bottom: 16px;
}
</style>
<script>
export default {
  props: {
    row: Object,
  },
};
</script>
