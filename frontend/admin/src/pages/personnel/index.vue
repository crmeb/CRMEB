<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row mb14">
        <el-col :span="24">
          <el-button v-auth="['setting-service-personnel']" type="primary" v-db-click @click="add" class="mr10"
            >添加服务人员</el-button
          >
        </el-col>
      </el-row>
      <el-table
        :data="data1"
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
        <el-table-column label="头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="手机号码" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除服务人员', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tableOptions.page"
          v-model:limit="tableOptions.limit"
          @pagination="getServicePersonnelList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { ElMessage } from 'element-plus';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import {
  getServicePersonnelList as getServicePersonnelListApi,
  getServicePersonnelAddForm,
  getServicePersonnelEditForm,
  setServicePersonnelStatus,
} from '@/api/personnel';

defineOptions({ name: 'index' });

const data1 = ref([]);
const total = ref(0);
const tableOptions = reactive({
  page: 1,
  limit: 15,
});
const loading = ref(false);

// 服务人员列表
function getServicePersonnelList() {
  loading.value = true;
  getServicePersonnelListApi(tableOptions)
    .then((res) => {
      data1.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}
// 添加服务人员
function add() {
  modalForm(getServicePersonnelAddForm()).then(() => getServicePersonnelList());
}
// 编辑
function edit(row) {
  modalForm(getServicePersonnelEditForm(row.id)).then(() => getServicePersonnelList());
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/setting/service_personnel/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      data1.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 是否显示
function onchangeIsShow(row) {
  setServicePersonnelStatus(row)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

// created
getServicePersonnelList();
</script>

<style lang="scss" scoped>
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
