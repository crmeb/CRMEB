<template>
  <el-card :bordered="false" shadow="never">
    <el-alert type="warning" :closable="false">
      <template slot="title">
        启动定时任务两种方式：<br />
        1、使用命令启动：php think timer start
        --d；如果更改了执行周期、编辑是否开启、删除定时任务需要重新启动下定时任务确保生效；<br />
        2、使用接口触发定时任务，建议每分钟调用一次，接口地址 {{apiBaseURL}}/api/crontab/run <br />
        开发说明：新增定时任务在 /crmeb/app/services/system/crontab/CrontabRunServices.php 文件中新增任务方法代码
      </template>
    </el-alert>
    <el-button type="primary" @click="addTask" class="mt14">添加定时任务</el-button>
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
          <a @click="edit(scope.row.id)">编辑</a>
          <el-divider direction="vertical"></el-divider>
          <a v-permission="'seckill'" @click="handleDelete(scope.row, '删除秒杀商品', scope.$index)">删除</a>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination v-if="total" :total="total" :page.sync="page" :limit.sync="limit" @pagination="getList" />
    </div>
    <creatTask ref="addTask" @submitAsk="getList"></creatTask>
  </el-card>
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
    };
  },
  created() {
    this.apiBaseURL = setting.apiBaseURL
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
      }
    },
    // 列表
    getList() {
      this.loading = true;
      timerIndex({
        page: this.page,
        limit: this.limit,
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
      this.$refs.addTask.modal = true;
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

<style lang="stylus" scoped>
.ivu-mt {
  padding-top:10px
}
</style>
