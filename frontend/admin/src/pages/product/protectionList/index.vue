<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="保障名称：">
            <el-input
              clearable
              placeholder="请输入保障名称"
              v-model="formValidate.title"
              class="form_content_width"
              @change="userSearchs"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['cms-category-create']" type="primary" class="action-row" v-db-click @click="add"
        >添加保障</el-button
      >

      <el-table
        :data="categoryList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        row-key="id"
        :tree-props="{ children: 'children' }"
      >
        <el-table-column label="ID" prop="id" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="保障名称" prop="title" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="保障图片" prop="image" min-width="130">
          <template #default="scope">
            <div class="tabBox_img" v-viewer v-if="scope.row.image">
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="状态" prop="status" min-width="120">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="排序" prop="sort" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除保障')">删除</a>
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
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { productProtectionFormApi, productProtectionListApi, protectionStatusApi } from '@/api/product';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';
import Setting from '@/setting';

defineOptions({ name: 'articleCategory' });

const router = useRouter();
const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();

const formValidateRef = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const formValidate = reactive({
  page: 1,
  limit: 15,
  title: '',
});
const status = ref('');
const total = ref(0);
const columns1 = [
  {
    title: 'ID',
    key: 'id',
    width: 80,
  },
  {
    title: '保障名称',
    key: 'title',
    minWidth: 130,
  },
  {
    title: '保障内容',
    key: 'content',
    minWidth: 130,
  },
  {
    title: '图标',
    slot: 'images',
    minWidth: 130,
  },
  {
    title: '状态',
    slot: 'statuss',
    minWidth: 130,
  },
  {
    title: '排序',
    key: 'sort',
    minWidth: 130,
  },
  {
    title: '操作',
    slot: 'action',
    fixed: 'right',
    minWidth: 120,
  },
];
const FromData = ref(null);
const modalTitleSs = ref('');
const categoryId = ref(0);
const categoryList = ref([]);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

onMounted(() => {
  getList();
});

// 添加
function add() {
  modalForm(productProtectionFormApi(0)).then(() => getList());
}
// 编辑
function edit(row) {
  modalForm(productProtectionFormApi(row.id)).then(() => getList());
}
// 删除
function del(row, tit) {
  let delfromData = {
    title: tit,
    num: 0,
    url: `product/protection/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  formValidate.status = status.value === 'all' ? '' : status.value;
  productProtectionListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      categoryList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  protectionStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 查看保障
function lookUp(row) {
  router.push({
    path: Setting.routePre + '/cms/article/index',
    query: {
      id: row.id,
    },
  });
  //xia mian chu cun mei yong;
  userLevelStore.getCategoryId(row.id);
}
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
