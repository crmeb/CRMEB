<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="acea-row row-between-wrapper">
        <el-row>
          <el-col v-bind="grid">
            <div class="button acea-row row-middle">
              <el-button type="primary" v-db-click @click="add(0)">添加省份</el-button>
              <el-button v-db-click @click="cleanCache">清除缓存</el-button>
            </div>
          </el-col>
        </el-row>
      </div>
      <el-table
        class="mt14"
        row-key="id"
        :load="handleLoadData"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
        :data="cityLists"
        lazy
      >
        <el-table-column label="编号" width="120">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="地区名称" min-width="300">
          <template #default="scope">
            <span>{{ scope.row.label }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上级名称" min-width="300">
          <template #default="scope">
            <span>{{ scope.row.parent_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-if="scope.row.hasOwnProperty('children')" v-db-click @click="add(scope.row.city_id)">添加</a>
            <el-divider direction="vertical" v-if="scope.row.hasOwnProperty('children')" />
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除城市', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { cityListApi, cityAddApi, cityApi, cityCleanCacheApi } from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'setting_dada' });

const mediaStore = useMediaStore();

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const columns1 = [
  {
    title: '编号',
    key: 'id',
    width: 80,
  },
  {
    title: '地区名称',
    key: 'label',
    minWidth: 300,
    tree: true,
  },
  {
    title: '上级名称',
    key: 'parent_name',
    minWidth: 300,
  },
  {
    title: '操作',
    slot: 'action',
    fixed: 'right',
    minWidth: 120,
  },
];
const cityLists = ref([]);
const cityId = ref(0); // 城市id

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 清除缓存；
function cleanCache() {
  cityCleanCacheApi()
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.success(res.msg);
    });
}
// 添加
function add(pid) {
  modalForm(cityAddApi(pid)).then(() => getList(0));
}
// 添加下级；
function lower(pid) {
  cityId.value = pid;
  getList(pid);
}
// 城市列表
function getList(parentId) {
  loading.value = true;
  cityListApi(parentId)
    .then(async (res) => {
      cityLists.value = res.data;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 返回
function goBack() {
  cityId.value = 0;
  getList(0);
}
// 修改
function edit(id) {
  modalForm(cityApi(id)).then(() => getList(cityId.value));
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/city/del/${row.city_id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      cityLists.value.splice(num, 1);
      getList(cityId.value);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function handleLoadData(item, node, callback) {
  cityListApi(item.city_id).then((res) => {
    callback(res.data);
  });
}

getList(0);
</script>

<style lang="scss" scoped>
:deep(.ivu-table-cell-tree) {
  border: 0;
  font-size: 15px;
  background-color: unset;
}
:deep(.ivu-table-cell-tree .ivu-icon-ios-add:before) {
  content: '\F11F';
}
:deep(.ivu-table-cell-tree .ivu-icon-ios-remove:before) {
  content: '\F116';
}
.button {
  width: 300px;
}
</style>
