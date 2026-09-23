<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row mb14">
        <el-col :span="24">
          <el-button type="primary" v-db-click @click="add" class="mr10">发布版本</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="版本号" width="80">
          <template #default="scope">
            <el-tooltip
              effect="light"
              v-if="scope.row.is_new"
              trigger="hover"
              placement="top-start"
              content="当前为最新线上版本!"
            >
              <i class="el-icon-s-promotion" style="font-size: 16px; color: red"></i>
            </el-tooltip>
            {{ scope.row.version }}
          </template>
        </el-table-column>
        <el-table-column label="平台类型" min-width="90">
          <template #default="scope">
            <div>
              <span>{{ scope.row.platform === 1 ? '安卓' : '苹果' }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="升级信息" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.info }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否强制" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.is_force === 1 ? '强制' : '非强制' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发布日期" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="下载地址" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.url }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除版本', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tableFrom.page"
          v-model:limit="tableFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, getCurrentInstance } from 'vue';
import { ElMessage } from 'element-plus';
import { versionList, versionCrate } from '@/api/system';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';

defineOptions({ name: 'index' });

const { proxy } = getCurrentInstance();
const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();

const verModal = ref(false);
const total = ref(20);
const tableFrom = reactive({
  page: 1,
  limit: 15,
});
const loading = ref(false);
const tableList = ref([]);

const isMobile = computed(() => mediaStore.isMobile);
const categoryId = computed(() => userLevelStore.categoryId);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// created
getList();

// 修改成功
function submitFail() {
  getList();
}
// 聊天记录
function record(row) {
  rows.value = row;
  modals3.value = true;
  isChat.value = true;
  getListRecord();
}
// 添加
function add() {
  modalForm(versionCrate(0)).then((res) => {
    getList();
  });
}
// 版本信息列表
function getList() {
  loading.value = true;
  versionList()
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
  modalForm(versionCrate(row.id)).then((res) => {
    getList();
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `system/version_del/${row.id}`,
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
function handleSubmit(name) {
  proxy.$refs[name].validate((valid) => {
    if (valid) {
      ElMessage.success('成功!');
    } else {
      ElMessage.error('失败!');
    }
  });
}
function handleReset(name) {
  proxy.$refs[name].resetFields();
}
function pageChange() {}
</script>

<style lang="scss" scoped></style>
