<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="levelFromRef"
          :model="levelFrom"
          :label-width="labelWidth"
          label-position="right"
          @submit.prevent
          inline
        >
          <el-form-item label="是否显示：">
            <el-select
              v-model="levelFrom.is_show"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="" label="全部"></el-option>
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：" label-for="keyword">
            <el-input class="form_content_width" v-model="levelFrom.keyword" placeholder="请输入物流公司名称或者编码" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" class="action-row" v-db-click @click="syncExpress">同步物流公司</el-button>
      <el-table
        :data="levelLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="物流公司名称" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="编码" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.code }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="100">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row)"
              size="large"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="80">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="levelFrom.page"
          v-model:limit="levelFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>
<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import {
  freightCreateApi,
  freightListApi,
  freightEditApi,
  freightStatusApi,
  freightSyncExpressApi,
} from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'user_level' });

const mediaStore = useMediaStore();

const table = ref(null);
const levelFromRef = ref(null);

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
    title: 'ID',
    key: 'id',
    width: 80,
  },
  {
    title: '物流公司名称',
    key: 'name',
    minWidth: 100,
  },
  {
    title: '编码',
    key: 'code',
    minWidth: 120,
  },
  {
    title: '排序',
    key: 'sort',
    sortable: true,
    minWidth: 100,
  },
  {
    title: '是否显示',
    slot: 'is_shows',
    minWidth: 120,
  },
  {
    title: '操作',
    slot: 'action',
    fixed: 'right',
    minWidth: 120,
  },
];
const levelFrom = reactive({
  keyword: '',
  is_show: '',
  page: 1,
  limit: 15,
});
const levelLists = ref([]);
const total = ref(0);
const FromData = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `freight/express/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      levelLists.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.is_show,
  };
  freightStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 等级列表
function getList() {
  loading.value = true;
  freightListApi(levelFrom)
    .then(async (res) => {
      let data = res.data;
      levelLists.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 添加
function add() {
  modalForm(freightCreateApi()).then(() => getList());
  // freightCreateApi().then(async res => {
  //     this.FromData = res.data;
  //     this.$refs.edits.modals = true;
  // }).catch(res => {
  //     this.$message.error(res.msg);
  // })
}
// 编辑
function edit(row) {
  modalForm(freightEditApi(row.id)).then(() => getList());
  // freightEditApi(row.id).then(async res => {
  //     this.FromData = res.data;
  //     this.$refs.edits.modals = true;
  // }).catch(res => {
  //     this.$message.error(res.msg);
  // })
}
// 表格搜索
function userSearchs() {
  levelFrom.page = 1;
  getList();
}
function syncExpress() {
  freightSyncExpressApi()
    .then(async (res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

getList();
</script>

<style lang="scss" scoped>
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
