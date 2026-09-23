<template>
  <div>
    <el-row class="expand-row">
      <el-col :span="6">
        <span class="expand-key">首次访问：</span>
        <span class="expand-value"> {{ $filters.formatDate(row.add_time) }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">近次访问：</span>
        <span class="expand-value">{{ $filters.formatDate(row.last_time) }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">身份证号：</span>
        <span class="expand-value">{{ row.card_id }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">真实姓名：</span>
        <span class="expand-value">{{ row.real_name }}</span>
      </el-col>
    </el-row>
    <el-row class="expand-row">
      <el-col :span="6">
        <span class="expand-key">标签：</span>
        <span class="expand-value">{{ row.labels }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">生日：</span>
        <span class="expand-value">{{ row.birthday }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">推荐人：</span>
        <span class="expand-value">{{ row.spread_uid_nickname }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">地址：</span>
        <span class="expand-value">{{ row.addres }}</span>
      </el-col>
    </el-row>
    <el-row class="expand-row">
      <el-col :span="6">
        <span class="expand-key">备注：</span>
        <span class="expand-value">{{ row.mark }}</span>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
defineOptions({ name: 'table-expand' });

defineProps({
  row: Object,
});
</script>

<style scoped>
.expand-row {
  margin-bottom: 16px;
}
</style>
