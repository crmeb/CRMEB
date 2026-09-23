<template>
  <div>
    <el-card :bordered="false" shadow="never" class="save_from ivu-mt">
      <el-button type="primary" class="action-row" v-db-click @click="add">{{ '添加' + $route.meta.title }}</el-button>
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="标签名" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="人数" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.count }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除标签', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  wechatTagListApi,
  wechatTagCreateApi,
  wechatTagEditApi,
  wechatGroupListApi,
  wechatGroupCreateApi,
  wechatGroupEditApi,
} from '../../api';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import Setting from '@/setting';

defineOptions({ name: 'tag' });

const route = useRoute();

const FromData = ref(null);
const loading = ref(false);
const tabList = ref([]);

const table = ref(null);

watch(
  () => route.path,
  (to, from) => {
    getList();
  },
);

// 添加
function add() {
  if (route.path === Setting.routePre + '/app/wechat/wechat_user/user/tag') {
    modalForm(wechatTagCreateApi()).then(() => getList());
  } else {
    modalForm(wechatGroupCreateApi()).then(() => getList());
  }
}
// 编辑
function edit(row) {
  if (route.path === Setting.routePre + '/app/wechat/wechat_user/user/tag') {
    modalForm(wechatTagEditApi(row.id)).then(() => getList());
  } else {
    modalForm(wechatGroupEditApi(row.id)).then(() => getList());
  }
}
// 删除
function del(row, tit, num) {
  let delfromData = null;
  if (route.path === Setting.routePre + '/app/wechat/wechat_user/user/tag') {
    delfromData = {
      title: tit,
      num: num,
      url: `app/wechat/tag/${row.id}`,
      method: 'DELETE',
      ids: '',
    };
  } else {
    delfromData = {
      title: tit,
      num: num,
      url: `app/wechat/group/${row.id}`,
      method: 'DELETE',
      ids: '',
    };
  }
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  let fountion;
  if (route.path === Setting.routePre + '/app/wechat/wechat_user/user/tag') {
    fountion = wechatTagListApi();
  } else {
    fountion = wechatGroupListApi();
  }
  fountion
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function pageChange(index) {
  formValidate.value.page = index;
  getList();
}

getList();
</script>

<style scoped></style>
