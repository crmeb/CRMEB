<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <el-button class="btn-back" icon="ArrowLeft" type="text" v-db-click @click="$router.go(-1)">返回</el-button>
        <el-divider direction="vertical"></el-divider>
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['system-crud-data_dictionary']" type="primary" v-db-click @click="add" class="action-row"
        >添加数据字典</el-button
      >
      <el-table
        :data="dictionaryList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column label="ID" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="字典名称" min-width="100"> </el-table-column>
        <el-table-column prop="value" label="字典数据" min-width="100"> </el-table-column>
        <el-table-column prop="sort" label="排序" min-width="100"> </el-table-column>
        <el-table-column prop="add_time" label="添加时间" min-width="200"> </el-table-column>
        <el-table-column fixed="right" label="操作" width="200">
          <template #default="scope">
            <a v-db-click @click="addSub(scope.row.id)">添加下级</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="eidtOptions(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="from.page"
          v-model:limit="from.limit"
          @pagination="getCrudDataDictionary"
        />
      </div>
    </el-card>
  </div>
</template>
<script setup>
import { ref, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { getDataDictionaryInfoList, getDataDictionaryInfo } from '@/api/systemCodeGeneration';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'user_level' });

const route = useRoute();
const router = useRouter();
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
const from = reactive({
  name: '',
  page: 1,
  limit: 15,
});
const dictionaryList = ref([]);
const optionsModal = ref(false);
const dictionaryName = ref('');
const optionsList = ref([]);
const levelLists = ref([]);
const total = ref(0);
const FromData = ref(null);
const imgName = ref('');
const visible = ref(false);
const titleType = ref('level');
const dictionaryId = ref(0);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

function addSub(pid) {
  modalForm(getDataDictionaryInfo(route.query.id, 0, pid))
    .then((res) => {
      getCrudDataDictionary();
    })
    .catch((err) => {});
}
function eidtOptions(id) {
  modalForm(getDataDictionaryInfo(route.query.id, id, 0))
    .then((res) => {
      getCrudDataDictionary();
    })
    .catch((err) => {});
}
function getCrudDataDictionary() {
  getDataDictionaryInfoList(from).then((res) => {
    dictionaryList.value = res.data.list;
    total.value = res.data.count;
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `system/crud/data_dictionary/info_del/${row.id}`,
    method: 'delete',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getCrudDataDictionary();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加
function add() {
  modalForm(getDataDictionaryInfo(route.query.id, 0, 0))
    .then((res) => {
      getCrudDataDictionary();
    })
    .catch((err) => {});
}
// 表格搜索
function searchs() {
  from.page = 1;
  getCrudDataDictionary();
}
function dataOptions(id) {
  router.push({
    path: Setting.routePre + '/system/code_data_dictionary_datalist',
    query: { id: id },
  });
}

from.id = route.query.id;
getCrudDataDictionary();
</script>

<style lang="scss" scoped>
.options-list {
  .item {
    display: flex;
    align-items: center;
    .add {
      font-size: 18px;
      cursor: pointer;
      margin-right: 5px;
      // color: #2d8cf0;
    }
    .delete {
      font-size: 18px;
      cursor: pointer;
      color: #fb0144;
    }
  }
}
</style>
