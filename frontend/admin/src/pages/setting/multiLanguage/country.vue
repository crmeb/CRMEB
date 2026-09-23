<template>
  <div>
    <el-card :bordered="false" shadow="never" class="mb10" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          @submit.prevent
          inline
        >
          <el-form-item label="搜索：">
            <div class="acea-row row-middle">
              <el-input
                clearable
                placeholder="请输入语言Code"
                v-model="formValidate.keyword"
                class="form_content_width"
              />
            </div>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">搜索</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-row class="action-row">
        <el-col v-bind="grid">
          <el-button type="primary" v-db-click @click="add">添加语言地区</el-button>
        </el-col>
      </el-row>
      <el-table ref="table" :data="tabList" class="mt14" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="编号" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="浏览器语言识别码" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.code }}</span>
          </template>
        </el-table-column>
        <el-table-column label="语言说明" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联语言" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.link_lang }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除地区语言', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="formValidate.page"
          v-model:limit="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>
<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { langCountryList, langCountryForm } from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

const mediaStore = useMediaStore();

const table = ref(null);
const formValidateRef = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const formValidate = reactive({
  keyword: '',
  page: 1,
  limit: 15,
});
const total = ref(0);
const loading = ref(false);
const tabList = ref([]);
const code = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 添加
function add() {
  modalForm(langCountryForm(0)).then(() => getList());
}
function edit(row) {
  modalForm(langCountryForm(row.id)).then(() => getList());
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/lang_country/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(num, 1);
      // getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function selChange() {
  formValidate.page = 1;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  langCountryList(formValidate)
    .then(async (res) => {
      loading.value = false;
      tabList.value = res.data.list;
      total.value = res.data.count;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

onMounted(() => {
  getList();
});
</script>
<style lang="scss" scoped>
.ivu-mt .type .item {
  margin: 3px 0;
}
.tabform {
  margin-bottom: 10px;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.ivu-form-item {
  margin-bottom: 10px;
}
.status :deep(.item ~ .item) {
  margin-left: 6px;
}
.status :deep(.statusVal) {
  margin-bottom: 7px;
}

/* .ivu-mt :deep(.ivu-table-header */
/* border-top:1px dashed #ddd!important */
:deep(.type) {
  padding: 3px 0;
  box-sizing: border-box;
}
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
