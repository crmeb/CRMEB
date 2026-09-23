<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="formValidate" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="语言分类：">
            <el-select v-model="formValidate.is_admin" clearable @change="selChange" class="form_content_width">
              <el-option
                v-for="(item, index) in langType.isAdmin"
                :key="index"
                :label="item.title"
                :value="item.value"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="语言类型：">
            <el-select v-model="formValidate.type_id" clearable @change="selChange" class="form_content_width">
              <el-option
                v-for="(item, index) in langType.langType"
                :key="index"
                :label="item.title"
                :value="item.value"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入语言备注"
              v-model="formValidate.remarks"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="userSearch" v-db-click @click="selChange">搜索</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-alert type="warning" :closable="false">
      <template #title>
        <p class="alert_title">页面语言</p>
        添加页面语言，添加完成之后状态码为中文文字，移动端页面使用 $t(`xxxx`)，js文件中使用 this.t(`xxxx`) 或者使用
        that.t(`xxxx`) 实现语言的切换<br />
        <br />
        <p class="alert_title">接口语言</p>
        添加接口语言，添加完成之后状态码为6位数字，接口返回提示信息时，直接返回对应的错误码即可实现语言的切换
      </template>
    </el-alert>
    <el-card class="mt14" :bordered="false" shadow="never">
      <el-row class="action-row mb14">
        <el-col>
          <el-button type="primary" v-db-click @click="add">添加语句</el-button>
        </el-col>
      </el-row>
      <el-table ref="table" :data="tabList" class="ivu-mt" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="编号" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="原语句" min-width="230">
          <template #default="scope">
            <span>{{ scope.row.remarks }}</span>
          </template>
        </el-table-column>
        <el-table-column label="对应语言翻译" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.lang_explain }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态码/文字(接口/页面调用参考)" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.code }}</span>
          </template>
        </el-table-column>
        <el-table-column label="语言类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.language_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除语言', scope.$index)">删除</a>
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
    <el-dialog v-model="addlangModal" width="720px" title="添加需要翻译的语句" @closed="modalChange">
      <el-form ref="langFormDataRef" :model="langFormData" :rules="ruleValidate">
        <el-form-item label="应用端：" class="mb20" label-width="120px">
          <el-radio-group type="button" v-model="langFormData.is_admin" class="mr15">
            <el-radio :label="item.value" :value="item.value" v-for="(item, index) in langType.isAdmin" :key="index">{{
              item.title
            }}</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-input v-model="langFormData.edit" v-show="false"></el-input>
        <el-form-item label="需要翻译的语句：" prop="remarks" class="mb20">
          <el-input
            v-model="langFormData.remarks"
            placeholder="请输入需要添加翻译的语句"
            style="width: 330px"
            search
            @on-search="translate"
          >
            <template #append><el-button type="primary" v-db-click @click="translate">翻译</el-button></template>
          </el-input>
        </el-form-item>
        <el-form-item prop="remark" class="mb20">
          <el-table ref="langTable" v-loading="traTabLoading" :data="langFormData.list" empty-text="暂无数据">
            <el-table-column label="语言类型" width="140">
              <template #default="scope">
                <span> {{ scope.row.language_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="对应语言翻译" min-width="250">
              <template #default="scope">
                <el-input v-model="scope.row.lang_explain" class="priceBox"></el-input>
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="addlangModal = false">取消</el-button>
          <el-button type="primary" v-db-click @click="ok">确定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { langCodeList, langCodeInfo, langCodeSettingSave, langCodeTranslate } from '@/api/setting';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

const mediaStore = useMediaStore();

const table = ref(null);
const langFormDataRef = ref(null);
const langTable = ref(null);

const addlangModal = ref(false);
const traTabLoading = ref(false);
const langType = ref({});
const formValidate = reactive({
  is_admin: 0,
  type_id: 1,
  remarks: '',
  page: 1,
  limit: 15,
});
const total = ref(0);
const FormLoading = ref(true);
const loading = ref(false);
const ruleValidate = {
  code: [{ required: true, message: '请输入状态码/文字', trigger: 'blur' }],
  remarks: [{ required: true, message: '请输入文字', trigger: 'blur' }],
};
const langColumns = [
  {
    title: '语言类型',
    key: 'language_name',
    width: 120,
  },
  {
    title: '对应语言翻译',
    slot: 'lang_explain',
    minWidth: 250,
  },
];
const langData = ref([]);
const langFormData = ref({
  is_admin: 0,
  code: '',
  remarks: '',
  edit: 0,
  list: [],
});
const tabList = ref([]);
const FromData = ref(null);
const extractId = ref(0);
const code = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

function translate() {
  if (!langFormData.value.remarks.trim()) {
    return ElMessage.warning('请先输入翻译内容');
  }
  traTabLoading.value = true;
  langCodeTranslate({
    text: langFormData.value.remarks,
  })
    .then((res) => {
      langFormData.value.list.map((e) => {
        e.lang_explain = res.data[e.type_id];
      });
      traTabLoading.value = false;
    })
    .catch((err) => {
      traTabLoading.value = false;
      ElMessage.error(err.msg);
    });
}
function add() {
  langFormData.value.list = langType.value.langType.map((e) => {
    return {
      language_name: e.title,
      lang_explain: '',
      remarks: '',
      type_id: e.value,
    };
  });
  addlangModal.value = true;
}
function ok() {
  if (!langFormData.value.remarks.trim()) {
    FormLoading.value = false;
    nextTick(() => {
      FormLoading.value = true;
    });
    return ElMessage.error('请先输入语言说明');
  }
  langCodeSettingSave(langFormData.value)
    .then((res) => {
      addlangModal.value = false;
      ElMessage.success(res.msg);
      getList();
    })
    .catch((err) => {
      FormLoading.value = false;
      nextTick(() => {
        FormLoading.value = true;
      });
      ElMessage.error(err.msg);
    });
}
function edit(row) {
  langFormData.value.is_admin = formValidate.is_admin;
  code.value = row.code;
  langCodeInfo({ code: row.code })
    .then((res) => {
      langFormData.value.list = res.data.list;
      langFormData.value.code = res.data.code;
      langFormData.value.remarks = res.data.remarks;
      langFormData.value.edit = 1;
      addlangModal.value = true;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/lang_code/del/${row.id}`,
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
function modalChange() {
  langFormData.value = {
    is_admin: 0,
    name: '',
    code: '',
    list: [],
  };
  code.value = null;
}
// 选择
function selChange() {
  formValidate.page = 1;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  langCodeList(formValidate)
    .then(async (res) => {
      loading.value = false;
      tabList.value = res.data.list;
      total.value = res.data.count;
      langType.value = res.data.langType;
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
.mb20 :deep(.ivu-table-wrapper > .ivu-spin-fix) {
  border: none;
}
</style>
