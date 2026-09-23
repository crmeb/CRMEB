<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row">
        <el-col v-bind="grid">
          <el-button type="primary" v-db-click @click="add">添加语言</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="list"
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
        <el-table-column label="语言名称" min-width="200">
          <template #default="scope">
            <div class="acea-scope.row scope.row-middle">
              <span>{{ scope.row.language_name }}</span>
              <el-tag class="ml10" color="default" v-if="scope.row.is_default">默认</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="浏览器语言识别码" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.file_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="150">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="changeSwitch(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row, '编辑语言', index)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除语言', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="langFrom.page"
          v-model:limit="langFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { langTypeList, langTypeForm, langTypeStatus } from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'user_group' });

const mediaStore = useMediaStore();

const table = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);

const langFrom = reactive({
  page: 1,
  limit: 15,
});
const list = ref([]);
const total = ref(0);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 添加
function add() {
  modalForm(langTypeForm(0)).then(() => getList());
}
// 分组列表
function getList() {
  loading.value = true;
  langTypeList(langFrom)
    .then(async (res) => {
      let data = res.data;
      list.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {
  modalForm(langTypeForm(row.id)).then(() => getList());
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/lang_type/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      list.value.splice(num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改状态
function changeSwitch(row) {
  langTypeStatus(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      row.status = !row.status ? 1 : 0;
      ElMessage.error(res.msg);
    });
}

getList();
</script>

<style lang="scss" scoped></style>
