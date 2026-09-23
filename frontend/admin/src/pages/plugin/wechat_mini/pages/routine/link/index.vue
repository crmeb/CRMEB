<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row mb14">
        <el-col :span="24">
          <el-button type="primary" v-db-click @click="add" class="mr10">创建链接</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="编号" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="名称" width="180">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="跳转地址" width="180">
          <template #default="scope">
            <span>{{ scope.row.path }}</span>
          </template>
        </el-table-column>
        <el-table-column label="系统链接(编辑不改变)" min-width="200">
          <template #default="scope">
            <span>{{ scope.row.http_url }}</span>
            <a class="ml10" v-db-click @click="onCopy(scope.row.http_url)">复制</a>
          </template>
        </el-table-column>
        <el-table-column label="微信链接(编辑改变)" min-width="200">
          <template #default="scope">
            <span>{{ scope.row.url }}</span>
            <a class="ml10" v-db-click @click="onCopy(scope.row.url)">复制</a>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="到期时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.expire_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除链接', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tableFrom.page"
          v-model:limit="tableFrom.limit"
          @pagination="routineSchemeList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { routineSchemeList as routineSchemeListApi, routineSchemeForm, routineSchemeDel } from '../../../api';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'index' });

const { proxy } = getCurrentInstance();

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const verModal = ref(false);
const total = ref(20);
const tableFrom = ref({
  page: 1,
  limit: 15,
});
const loading = ref(false);
const tableList = ref([]);

// 添加
function add() {
  modalForm(routineSchemeForm(0)).then((res) => {
    routineSchemeList();
  });
}
function onCopy(copyData) {
  proxy
    .$copyText(copyData)
    .then((message) => {
      ElMessage.success('复制成功');
    })
    .catch((err) => {
      ElMessage.error('复制失败');
    });
}
// 列表
function routineSchemeList() {
  loading.value = true;
  routineSchemeListApi(tableFrom.value)
    .then((res) => {
      tableList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      loading.value = false;
    });
}
// 添加
function edit(row) {
  modalForm(routineSchemeForm(row.id)).then((res) => {
    routineSchemeList();
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `app/routine/scheme_del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

routineSchemeList();
</script>

<style lang="scss" scoped></style>
