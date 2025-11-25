<template>
  <div>
    <el-card :bordered="false" shadow="never">
      <el-alert type="warning" :closable="false">
        <template slot="title">
          自定义事件说明：<br />
          1、新增的事件会在对应的事件类型相关的流程中触发，例：选择用户登录，则在用户登录时执行代码。<br />
          2、可以使用对应事件类型中对应的参数，例：$data['nickname']、$data['phone']等。<br />
          3、调用类的时候请写入完整路径，例：\think\facade\Db、\app\services\other\CacheServices::class等。<br />
        </template>
      </el-alert>
      <el-button type="primary" v-db-click @click="addTask" class="mt14">新增系统事件</el-button>
      <el-table :data="tableData" v-loading="loading" class="ivu-mt">
        <el-table-column label="编号" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="事件名" min-width="150">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="事件类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.mark_name }}</span>
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
        <el-table-column label="创建时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-permission="'seckill'" v-db-click @click="handleDelete(scope.row, '删除自定事件', scope.$index)"
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
import { eventIndex, eventShowTimer } from '@/api/system';
import creatTask from './createModal.vue';
import setting from '@/setting';
export default {
  name: 'system_event',
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
    this.apiBaseURL = setting.apiBaseURL;
    this.getList();
  },
  methods: {
    // 列表
    getList() {
      this.loading = true;
      eventIndex({
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
      this.$refs.addTask.eventInfo();
    },
    edit(id) {
      this.$refs.addTask.eventInfo(id);
    },
    // 删除
    handleDelete(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/event/del/${row.id}`,
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
      eventShowTimer(id, is_open)
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
