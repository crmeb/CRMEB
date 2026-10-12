<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div>
        <el-tabs v-model="currentTab" @tab-change="getList">
          <el-tab-pane
            :label="item.label"
            :name="item.value.toString()"
            v-for="(item, index) in headerList"
            :key="index"
          />
        </el-tabs>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: '10px 20px 20px' }">
      <el-alert type="warning" :closable="false">
        <template #title>
          启动定时任务两种方式：<br />
          1、使用命令启动：php think timer start
          --d；如果更改了执行周期、编辑是否开启、删除定时任务需要重新启动下定时任务确保生效；<br />
          2、使用接口触发定时任务，建议每分钟调用一次，接口地址 {{ apiBaseURL }}api/crontab/run <br />
        </template>
      </el-alert>
      <el-button v-if="currentTab === '1'" type="primary" v-db-click @click="addTask" class="mt14"
        >添加定时任务</el-button
      >
      <el-table :data="tableData" v-loading="loading" class="mt14">
        <el-table-column label="标题" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="任务说明" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.content }}</span>
          </template>
        </el-table-column>
        <el-table-column label="执行周期" min-width="130">
          <template #default="scope">
            <span>{{ taskTrip(scope.row) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否开启" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_open"
              size="large"
              @change="handleChange(scope.row)"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical" v-if="currentTab === '1'"></el-divider>
            <a v-if="currentTab === '1'" v-db-click @click="handleDelete(scope.row, '删除定时任务', scope.$index)"
              >删除</a
            >
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" v-model:page="page" v-model:limit="limit" @pagination="getList" />
      </div>
      <creatTask ref="addTaskRef" :currentTab="currentTab" @submitAsk="getList"></creatTask>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { timerIndex, showTimer } from '@/api/system';
import creatTask from './createModal.vue';
import setting from '@/setting';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'system_crontab' });

const addTaskRef = ref(null);

const loading = ref(false);
const tableData = ref([]);
const page = ref(1);
const limit = ref(15);
const total = ref(1);
const apiBaseURL = ref('');
const headerList = [
  { label: '系统任务', value: '0' },
  { label: '自定义任务', value: '1' },
];
const currentTab = ref('0');

function taskTrip(row) {
  switch (row.type) {
    case 1:
      return `每隔${row.second}秒执行一次`;
    case 2:
      return `每隔${row.minute}分钟执行一次`;
    case 3:
      return `每隔${row.hour}小时执行一次`;
    case 4:
      return `每隔${row.day}天执行一次`;
    case 5:
      return `每天${row.hour}时${row.minute}分${row.second}秒执行一次`;
    case 6:
      return `每个星期${row.week}的${row.hour}时${row.minute}分${row.second}秒执行一次`;
    case 7:
      return `每月${row.day}日的${row.hour}时${row.minute}分${row.second}秒执行一次`;
    case 8:
      return `每年${row.month}月${row.day}日的${row.hour}时${row.minute}分${row.second}秒执行一次`;
  }
}
// 列表
function getList() {
  loading.value = true;
  timerIndex({
    page: page.value,
    limit: limit.value,
    custom: currentTab.value === '1' ? 1 : 0,
  })
    .then((res) => {
      loading.value = false;
      let { count, list } = res.data;
      total.value = count;
      tableData.value = list;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function addTask() {
  addTaskRef.value.timerInfo(0);
}
function edit(id) {
  addTaskRef.value.timerInfo(id);
}
// 删除
function handleDelete(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `system/crontab/del/${row.id}`,
    method: 'delete',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 是否开启
function handleChange({ id, is_open }) {
  showTimer(id, is_open)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

apiBaseURL.value = setting.apiBaseURL.replace(/adminapi/, '');
getList();
</script>

<style lang="scss" scoped>
/* 页签条通栏贴卡片顶边（与实物订单/文件管理页同一模式）：左右留白由 header 自身控制，灰线横贯整卡 */
:deep(.el-tabs__header) {
  margin-bottom: 0;
  padding: 0 20px;
}
</style>
