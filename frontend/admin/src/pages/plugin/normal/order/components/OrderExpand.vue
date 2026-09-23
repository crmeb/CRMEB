<template>
  <div class="expand">
    <el-row class="expand-row">
      <el-col :span="6">
        <span class="expand-key">商品总价：</span>
        <span class="expand-value">¥{{ row.total_price || '0.00' }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">下单时间：</span>
        <span class="expand-value">{{ row.add_time ? formatTime(row.add_time) : '--' }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">推广人：</span>
        <span class="expand-value">{{ row.spread_nickname || '无' }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">事业部：</span>
        <span class="expand-value">{{ row.division_name || '无' }}</span>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="6">
        <span class="expand-key">用户备注：</span>
        <span class="expand-value">{{ row.mark || '无' }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">商家备注：</span>
        <span class="expand-value">{{ row.remark || '无' }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">支付方式：</span>
        <span class="expand-value">{{ row.pay_type_name || row.pay_type || '--' }}</span>
      </el-col>
      <el-col :span="6">
        <span class="expand-key">快递公司：</span>
        <span class="expand-value">{{ row.delivery_name || '--' }}</span>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
defineOptions({ name: 'OrderExpand' });

defineProps({
  row: {
    type: Object,
    default: () => ({}),
  },
});

function formatTime(ts) {
  if (!ts) return '--';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const m = (d.getMonth() + 1).toString().padStart(2, '0');
  const day = d.getDate().toString().padStart(2, '0');
  const h = d.getHours().toString().padStart(2, '0');
  const min = d.getMinutes().toString().padStart(2, '0');
  const s = d.getSeconds().toString().padStart(2, '0');
  return `${y}/${m}/${day} ${h}:${min}:${s}`;
}
</script>

<style scoped>
.expand {
  font-size: 13px;
  color: #606266;
}
.expand-row {
  margin-bottom: 16px;
}
.expand-key {
  color: #909399;
  margin-right: 4px;
}
.expand-value {
  color: #303133;
}
</style>
