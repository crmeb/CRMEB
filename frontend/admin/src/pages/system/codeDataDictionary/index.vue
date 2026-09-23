<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="levelFrom"
          :model="from"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.prevent
        >
          <el-form-item label="字典名称：" label-for="name">
            <el-input clearable v-model="from.name" placeholder="请输入字典名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="searchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
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
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="字典名称" min-width="100"> </el-table-column>
        <el-table-column prop="mark" label="数据标识" min-width="200"> </el-table-column>
        <el-table-column prop="level" label="类型" min-width="200">
          <template #default="scope">
            <span>{{ scope.row.level ? '多级' : '一级' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="add_time" label="添加时间" min-width="200"> </el-table-column>
        <el-table-column fixed="right" label="操作" width="200">
          <template #default="scope">
            <a v-db-click @click="eidtOptions(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="dataOptions(scope.row.id)">数据管理</a>
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
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  getDataDictionaryList,
  getDataDictionaryForm,
  crudDataDictionaryList,
  saveCrudDataDictionaryList,
} from '@/api/systemCodeGeneration';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'user_level' });

const router = useRouter();
const mediaStore = useMediaStore();

const table = ref(null);
const levelFrom = ref(null);

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

function eidtOptions(id) {
  modalForm(getDataDictionaryForm(id))
    .then((res) => {
      getCrudDataDictionary();
    })
    .catch((err) => {});
}
function getCrudDataDictionary() {
  getDataDictionaryList(from).then((res) => {
    dictionaryList.value = res.data.list;
    total.value = res.data.count;
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `system/crud/data_dictionary_list/del/${row.id}`,
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
  modalForm(getDataDictionaryForm(0))
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
