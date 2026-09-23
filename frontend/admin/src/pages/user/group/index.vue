<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row">
        <el-col v-bind="grid">
          <el-button v-auth="['admin-user-group']" type="primary" v-db-click @click="add">添加分组</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="groupLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="120">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="分组" min-width="80">
          <template #default="scope">
            <span class="group-name">{{ scope.row.group_name }}</span>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row.id)">修改</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除分组', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="groupFrom.page"
          v-model:limit="groupFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { userGroupApi, groupAddApi } from '@/api/user';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'user_group' });

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);

const groupFrom = ref({
  page: 1,
  limit: 15,
});
const groupLists = ref([]);
const total = ref(0);

// created
getList();

// 添加
function add() {
  modalForm(groupAddApi(0))
    .then(() => getList())
    .catch(() => {
      console.log('error');
    });
}

// 分组列表
function getList() {
  loading.value = true;
  userGroupApi(groupFrom.value)
    .then(async (res) => {
      let data = res.data;
      groupLists.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

// 修改
function edit(id) {
  modalForm(groupAddApi(id)).then(() => getList());
}

// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `user/user_group/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      groupLists.value.splice(num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.group-name {
  display: inline-block;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: middle;
}
</style>
