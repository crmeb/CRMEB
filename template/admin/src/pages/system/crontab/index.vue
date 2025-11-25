<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px' }">
      <div>
        <el-tabs v-model="currentTab" @tab-click="getList">
          <el-tab-pane
            :label="item.label"
            :name="item.value.toString()"
            v-for="(item, index) in headerList"
            :key="index"
          />
        </el-tabs>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-alert type="warning" :closable="false">
        <template slot="title">
          启动定时任务两种方式：<br />
          1、使用命令启动：php think timer start
          --d；如果更改了执行周期、编辑是否开启、删除定时任务需要重新启动下定时任务确保生效；<br />
          2、使用接口触发定时任务，建议每分钟调用一次，接口地址 {{ apiBaseURL }}api/crontab/run <br />
        </template>
      </el-alert>
      <el-button v-if="currentTab === '1'" type="primary" v-db-click @click="addTask" class="mt14"
        >添加定时任务</el-button
      >
      <el-table :data="tableData" v-loading="loading" class="ivu-mt">
        <el-table-column label="标题" min-width="150">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="任务说明" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.content }}</span>
          </template>
        </el-table-column>
        <el-table-column label="执行周期" min-width="130">
          <template slot-scope="scope">
            <span>{{ taskTrip(scope.row) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否开启" min-width="130">
          <template slot-scope="scope">
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
        <el-table-column label="操作" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical" v-if="currentTab === '1'"></el-divider>
            <a
              v-if="currentTab === '1'"
              v-permission="'seckill'"
              v-db-click
              @click="handleDelete(scope.row, '删除定时任务', scope.$index)"
              >删除</a
            >
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" :page.sync="page" :limit.sync="limit" @pagination="getList" />
      </div>
      <creatTask ref="addTask" :currentTab="currentTab" @submitAsk="getList"></creatTask>
    </el-card>
  </div>
</template>

<script>
import { timerIndex, showTimer } from '@/api/system';
import creatTask from './createModal.vue';
import setting from '@/setting';
export default {
  name: 'system_crontab',
  components: { creatTask },
  data() {
    return {
      loading: false,
      tableData: [],
      page: 1,
      limit: 15,
      total: 1,
      apiBaseURL: '',
      headerList: [
        { label: '系统任务', value: '0' },
        { label: '自定义任务', value: '1' },
      ],
      currentTab: '0',
    };
  },
  created() {
    this.apiBaseURL = setting.apiBaseURL.replace(/adminapi/, '');
    this.getList();
  },
  methods: {
    taskTrip(row) {
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
    },
    // 列表
    getList() {
      this.loading = true;
      timerIndex({
        page: this.page,
        limit: this.limit,
        custom: this.currentTab === '1' ? 1 : 0,
      })
        .then((res) => {
          this.loading = false;
          let { count, list } = res.data;
          this.total = count;
          this.tableData = list;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    addTask() {
      this.$refs.addTask.timerInfo(0);
    },
    edit(id) {
      this.$refs.addTask.timerInfo(id);
    },
    // 删除
    handleDelete(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/crontab/del/${row.id}`,
        method: 'delete',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 是否开启
    handleChange({ id, is_open }) {
      showTimer(id, is_open)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.ivu-mt {
  padding-top: 10px;
}
</style>
