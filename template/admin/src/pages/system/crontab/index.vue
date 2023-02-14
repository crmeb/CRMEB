<template>
  <Card :bordered="false" dis-hover>
    <Button type="primary" @click="addTask">添加定时任务</Button>
    <Table :columns="columns" :data="tableData" :loading="loading" class="ivu-mt">
      <template slot-scope="{ row }" slot="execution_cycle">
        <span>{{ taskTrip(row) }}</span>
      </template>
      <template slot-scope="{ row }" slot="is_open">
        <i-switch v-model="row.is_open" :true-value="1" :false-value="0" size="large" @on-change="handleChange(row)">
          <span slot="open">开启</span>
          <span slot="close">关闭</span>
        </i-switch>
      </template>
      <template slot-scope="{ row }" slot="action">
        <a @click="edit(row.id)">编辑</a>

        <Divider type="vertical" />
        <a @click="handleDelete(row, '删除秒杀商品', index)">删除</a>
      </template>
    </Table>
    <div class="acea-row row-right page">
      <Page :total="total" :current="page" show-elevator show-total @on-change="pageChange" :page-size="limit" />
    </div>
    <creatTask ref="addTask" @submitAsk="getList"></creatTask>
  </Card>
</template>

<script>
import { timerIndex, showTimer } from '@/api/system';
import creatTask from './createModal.vue';
export default {
  name: 'system_crontab',
  components: { creatTask },
  data() {
    return {
      loading: false,
      columns: [
        {
          title: '名称',
          key: 'name',
          minWidth: 150,
        },
        {
          title: '任务说明',
          key: 'content',
          minWidth: 120,
        },
        // {
        //   title: '最后执行时间',
        //   key: 'last_execution_time',
        //   minWidth: 120,
        // },
        // {
        //   title: '下次执行时间',
        //   key: 'next_execution_time',
        //   minWidth: 120,
        // },
        {
          title: '执行周期',
          slot: 'execution_cycle',
          minWidth: 160,
        },
        {
          title: '是否开启',
          slot: 'is_open',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          align: 'center',
          fixed: 'right',
          minWidth: 100,
        },
      ],
      tableData: [],
      page: 1,
      limit: 15,
      total: 1,
    };
  },
  created() {
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
          this.$Message.error(res.msg);
        });
    },
    addTask() {
      console.log(this.$refs.addTask);
      this.$refs.addTask.modal = true;
    },
    edit(id) {
      console.log(id);
      this.$refs.addTask.timerInfo(id);
    },
    // 删除
    handleDelete(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/timer/del/${row.id}`,
        method: 'delete',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 是否开启
    handleChange({ id, is_open }) {
      showTimer(id, is_open)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.page = index;
      this.getList();
    },
  },
};
</script>

<style lang="stylus" scoped></style>
