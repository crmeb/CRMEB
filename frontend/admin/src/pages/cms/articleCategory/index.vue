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
          <el-form-item label="是否显示：" label-for="status">
            <el-select v-model="status" placeholder="请选择" clearable @change="userSearchs" class="form_content_width">
              <el-option value="all" label="全部"></el-option>
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="分类名称：" prop="title" label-for="status2">
            <el-input clearable placeholder="请输入分类名称" v-model="formValidate.title" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['cms-category-create']" type="primary" class="action-row" v-db-click @click="add"
        >添加文章分类</el-button
      >
      <vxe-table
        class="vxeTable mt14"
        :loading="loading"
        header-row-class-name="false"
        :tree-config="{ childrenField: 'children' }"
        :data="categoryList"
        :row-config="{ isHover: true }"
      >
        <vxe-table-column field="id" title="ID" tooltip width="80"></vxe-table-column>
        <vxe-table-column field="title" tree-node title="分类名称" min-width="130">
          <template v-slot="{ row }">
            <span>{{ row.title }}</span>
          </template>
        </vxe-table-column>
        <vxe-table-column field="image" title="分类图片" min-width="130">
          <template v-slot="{ row }">
            <div class="tabBox_img" v-viewer v-if="row.image">
              <img v-lazy="row.image" />
            </div>
            <div v-else>--</div>
          </template>
        </vxe-table-column>
        <vxe-table-column field="status" title="状态" min-width="120">
          <template v-slot="{ row }">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="row.status"
              :value="row.status"
              @change="onchangeIsShow(row)"
              size="large"
            >
            </el-switch>
          </template>
        </vxe-table-column>
        <vxe-table-column field="date" title="操作" width="160" fixed="right">
          <template v-slot="{ row }">
            <a v-db-click @click="edit(row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(row, '删除文章分类')">删除</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="lookUp(row)">查看文章</a>
          </template>
        </vxe-table-column>
      </vxe-table>
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
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';
import Setting from '@/setting';
import { categoryAddApi, categoryEditApi, categoryListApi, statusApi } from '@/api/cms';

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
  status: '',
  page: 1,
  limit: 15,
  type: 0,
});
const status = ref('');
const total = ref(0);

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
  modalForm(categoryAddApi()).then(() => getList());
}
// 编辑
function edit(row) {
  modalForm(categoryEditApi(row.id)).then(() => getList());
}
// 删除
function del(row, tit) {
  let delfromData = {
    title: tit,
    num: 0,
    url: `cms/category/${row.id}`,
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
  categoryListApi(formValidate)
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
  statusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 查看文章
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
