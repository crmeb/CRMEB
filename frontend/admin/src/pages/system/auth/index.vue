<template>
  <div>
    <!-- 二开移除商业授权 / 去版权服务 / 自定义版权模块（含购买授权与版权编辑弹窗），本页仅保留系统信息，恢复时参考上游 CRMEB -->
    <el-card v-for="(value, key, index) in tableList" :key="index" :bordered="false" shadow="never" class="ivu-mt mt16">
      <div class="head acea-row row-between-wrapper">{{ headText(key) }}</div>
      <el-table :data="tableList[key]" empty-text="暂无数据">
        <el-table-column :label="key == 'permissions' ? '文件/目录' : '环境'" minWidth="180">
          <template #default="scope">{{ scope.row.name }} </template>
        </el-table-column>
        <el-table-column label="要求" minWidth="180">
          <template #default="scope">
            <span>{{ scope.row.require }} </span>
            <el-tooltip placement="top" v-if="key == 'process' && !scope.row.value">
              <template #content><div v-html="trips[scope.$index].message"></div></template>
              <i class="el-icon-warning-outline"></i>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="180">
          <template #default="scope">
            <span v-if="typeof scope.row.value === 'boolean'">
              <i v-if="scope.row.value === true" class="el-icon-check"></i>
              <i v-else class="el-icon-close"></i>
            </span>
            <span v-else>{{ scope.row.value }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { copyrightList as copyrightListApi } from '@/api/system';

defineOptions({ name: 'system_auth' });

const tableList = ref([]);
// 与后端 process 数组顺序一一对应：0=长链接 1=定时任务 2=消息队列
const trips = [
  {
    title: '温馨提示',
    message:
      '您的【长连接】未开启，没有开启会导致系统默认客服无法使用,后台订单通知无法收到。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
  },
  {
    title: '温馨提示',
    message:
      '您的【定时任务】未开启，没有开启会导致自动收货、未支付自动取消订单、订单自动好评、拼团到期退款等任务无法正常执行。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
  },
  {
    title: '温馨提示',
    message:
      '您的【消息队列】未开启，没有开启会导致异步任务无法执行。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
  },
];

function headText(z) {
  if (z === 'server') {
    return '服务器信息';
  } else if (z === 'environment') {
    return '系统环境要求';
  } else if (z === 'permissions') {
    return '权限状态';
  } else if (z === 'process') {
    return '启动进程';
  }
}

onMounted(() => {
  copyrightListApi().then((res) => {
    tableList.value = res.data;
  });
});
</script>
<style scoped lang="scss">
.head {
  font-weight: 400;
  font-size: 14px;
  color: var(--prev-color-text-regular);
  margin-bottom: 20px;
}

.el-icon-check {
  color: var(--prev-color-primary);
  font-size: 22px;
  font-weight: 600;
}

.el-icon-close {
  color: #f5222d;
  font-size: 22px;
  font-weight: 600;
}

.el-icon-warning-outline {
  font-size: 13px;
}
</style>
