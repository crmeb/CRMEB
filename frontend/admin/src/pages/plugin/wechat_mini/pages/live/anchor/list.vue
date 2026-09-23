<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row">
        <el-col v-bind="grid">
          <el-button v-auth="['admin-user-label_add']" type="primary" v-db-click @click="add">添加主播</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="labelLists"
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
        <el-table-column label="名称" min-width="300">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="电话" min-width="300">
          <template #default="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="微信号" min-width="300">
          <template #default="scope">
            <span>{{ scope.row.wechat }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row.id)">修改</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除主播', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="labelFrom.page"
          v-model:limit="labelFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { liveAnchorListApi, liveAnchorAddApi } from '../../../api';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'LiveAnchorList' });

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const grid = { xl: 7, lg: 7, md: 12, sm: 24, xs: 24 };
const loading = ref(false);
const labelFrom = ref({ kerword: '', page: 1, limit: 15 });
const labelLists = ref([]);
const total = ref(0);

const table = ref(null);

/** 添加 */
function add() {
  modalForm(liveAnchorAddApi(0)).then(() => getList());
}
/** 修改 */
function edit(id) {
  modalForm(liveAnchorAddApi(id)).then(() => getList());
}
/** 删除 */
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `live/anchor/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      labelLists.value.splice(num, 1);
      if (!labelLists.value.length && labelFrom.value.page != 1) {
        labelFrom.value.page -= 1;
        getList();
      } else {
        getList();
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
/** 主播列表 */
function getList() {
  loading.value = true;
  liveAnchorListApi(labelFrom.value)
    .then(async (res) => {
      let data = res.data;
      labelLists.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

getList();
</script>

<style scoped></style>
