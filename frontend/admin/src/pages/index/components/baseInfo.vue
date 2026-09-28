<template>
  <el-row :gutter="16">
    <el-col v-bind="grid" class="ivu-mb" v-for="(item, index) in infoList" :key="index">
      <el-card shadow="never" :padding="0">
        <template #header
          ><p>
            <span v-text="item.title"></span>
            <el-tag style="float: right" type="success">{{ item.date }}</el-tag>
          </p></template
        >
        <div>
          <div class="number">{{ item.today }}</div>
          <div class="ivu-pt-8" style="height: 42px">
            <span>昨日 {{ item.yesterday }}</span>
            <span class="ivu-mr">
              日环比 {{ Number(item.today_ratio) }}%
              <i
                class="iconColor"
                :class="[
                  Number(item.today_ratio) >= 0 ? ' ' : 'on',
                  Number(item.today_ratio) >= 0 ? 'el-icon-caret-top' : 'el-icon-caret-bottom\n',
                ]"
              />
            </span>
          </div>
          <el-divider />
          <div class="total">
            <el-row>
              <el-col :span="12">{{ item.total_name }}</el-col>
              <el-col :span="12" class="ivu-text-right">{{ item.total }}</el-col>
            </el-row>
          </div>
        </div>
      </el-card>
    </el-col>
  </el-row>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { headerApi } from '@/api/index';

const infoList = ref([]);
const grid = {
  xl: 6,
  lg: 6,
  md: 12,
  sm: 12,
  xs: 24,
};
const excessStyle = {
  color: '#f56a00',
  backgroundColor: '#fde3cf',
};
const avatarList = ref([]);

// 统计
function getStatistics() {
  headerApi()
    .then(async (res) => {
      let data = res.data;
      infoList.value = data.info;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

onMounted(() => {
  getStatistics();
});
</script>
<style lang="scss" scoped>
.ivu-mb {
  margin-bottom: 14px;
}
.number {
  margin-bottom: 10px;
  font-size: 32px;
  font-weight: 400;
  color: var(--prev-color-text-regular);
  line-height: 40px;
}

.iconColor {
  color: #f53f3f;
}

.iconColor.on {
  color: #0fc6c2;
}

.ivu-mr {
  display: inline-block;
  margin-left: 16px !important;
}

.ivu-text-right {
  text-align: right;
}
:deep(.el-card__header) {
  border-bottom: none !important;
  padding-bottom: 0;
}
.el-divider--horizontal {
  margin: 0 0 12px 0;
}
.total {
  font-size: 14px;
  font-weight: 400;
  color: #999999;
  line-height: 22px;
}
</style>
