<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
          class="tabform"
        >
          <el-form-item :label="fromList.title + '：'">
            <date-range-picker
              width="250px"
              clearable
              :editable="false"
              @change="onchangeTime"
              v-model="timeVal"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="名称：">
            <el-select v-model="formValidate.admin_id" clearable @change="userSearchs" class="form_content_width">
              <el-option
                :value="item.id"
                v-for="(item, index) in dataList"
                :key="index"
                :label="item.real_name"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="链接：">
            <el-input
              placeholder="请输入链接"
              v-model="formValidate.path"
              class="form_content_width"
              clearable
            ></el-input>
          </el-form-item>
          <el-form-item label="IP：">
            <el-input placeholder="请输入IP" v-model="formValidate.ip" clearable class="form_content_width"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="userSearch" v-db-click @click="userSearchs">搜索</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table ref="selection" :data="tabList" v-loading="loading" empty-text="暂无数据" highlight-current-row>
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="ID/名称" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.admin_id + ' / ' + scope.row.admin_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.path_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="链接" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.path }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作ip" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.ip }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作时间" min-width="100">
          <template #default="scope">
            <span> {{ $filters.formatDate(scope.row.add_time) }}</span>
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
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { searchAdminApi, systemListApi } from '@/api/system';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'systemLog' });

const mediaStore = useMediaStore();

const formValidateRef = ref(null);
const selection = ref(null);

const fromList = {
  title: '选择时间',
  custom: true,
  fromTxt: [
    { text: '全部', val: '' },
    { text: '今天', val: 'today' },
    { text: '昨天', val: 'yesterday' },
    { text: '最近7天', val: 'lately7' },
    { text: '最近30天', val: 'lately30' },
    { text: '本月', val: 'month' },
    { text: '本年', val: 'year' },
  ],
};
const timeVal = ref([]);
const formValidate = reactive({
  limit: 15,
  page: 1,
  pages: '',
  data: '',
  path: '',
  ip: '',
  admin_id: '',
});
const loading = ref(false);
const tabList = ref([]);
const total = ref(0);

const dataList = ref([]);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.data = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.page = 1;
  getList();
}
// 选择时间
function selectChange(tab) {
  formValidate.data = tab;
  timeVal.value = [];
  formValidate.page = 1;
  getList();
}
// 搜索条件
function getSearchAdmin() {
  searchAdminApi()
    .then(async (res) => {
      dataList.value = res.data.info;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  systemListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}

getSearchAdmin();
getList();
</script>

<style scoped></style>
