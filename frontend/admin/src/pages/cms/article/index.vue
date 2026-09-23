<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="artFrom" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="文章分类：" label-for="pid">
            <el-cascader
              v-model="artFrom.pid"
              placeholder="请选择"
              class="treeSel"
              @change="handleCheckChange"
              :options="treeData"
              :props="cascaderProps"
              style="width: 250px"
            >
            </el-cascader>
          </el-form-item>
          <el-form-item label="文章搜索：" label-for="title">
            <el-input clearable placeholder="请输入" v-model="artFrom.title" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <router-link :to="Setting.routePre + '/cms/article/add_article'" v-auth="['cms-article-creat']"
        ><el-button type="primary" class="action-row bnt">添加文章</el-button></router-link
      >
      <el-table
        :data="cmsList"
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
        <el-table-column label="文章图片" min-width="90">
          <template #default="scope">
            <div v-if="scope.row.image_input.length !== 0" v-viewer>
              <div class="tabBox_img" v-for="(item, index) in scope.row.image_input" :key="index">
                <img v-lazy="item" />
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="文章名称" min-width="130">
          <template #default="scope">
            <el-tooltip placement="top" :open-delay="600">
              <template #content
                ><div>{{ ' [ ' + scope.row.catename + ' ] ' + scope.row.title }}</div></template
              >
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="关联商品" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.store_name || '--' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="浏览量" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.visit }}</span>
          </template>
        </el-table-column>
        <el-table-column label="时间" min-width="130">
          <template #default="scope">
            <span>{{ $filters.formatDate(scope.row.add_time) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="300">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="artRelation(scope.row, '取消关联', index)">{{
              scope.row.product_id === 0 ? '关联' : '取消关联'
            }}</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除文章', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
            <el-dropdown @command="onCopy(scope.row, $event)" :transfer="true">
              <span class="el-dropdown-link">复制链接<i class="el-icon-arrow-down el-icon--right"></i></span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item command="1">移动端链接</el-dropdown-item>
                </el-dropdown-menu></template
              >
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="artFrom.page"
          v-model:limit="artFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!--关联-->
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="1000px" @closed="cancel">
      <goods-list ref="goodslist" @getProductId="getProductId" v-if="modals"></goods-list>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onActivated, getCurrentInstance } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { cmsListApi, categoryListApi, relationApi } from '@/api/cms';
import relationList from './relation';
import goodsList from '@/components/goodsList/index';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'cms_article' });

const { proxy } = getCurrentInstance();
const route = useRoute();
const router = useRouter();
const mediaStore = useMediaStore();

const goodslist = ref(null);

const modalTitleSs = ref('');
const loading = ref(false);
const artFrom = reactive({
  pid: 0,
  title: '',
  page: 1,
  limit: 15,
});
const total = ref(0);
const cmsList = ref([]);
const treeData = ref([]);
const list = ref([]);
const cid = ref(0); // 移动分类id
const cmsId = ref(0);
const formValidate = reactive({
  type: 1,
});
const rows = ref({});
const modal_loading = ref(false);
const modals = ref(false);
const cascaderProps = {
  value: 'id',
  label: 'title',
  emitPath: false,
};

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

onActivated(() => {
  artFrom.pid = route.query.id ? route.query.id : 0;
  getList();
  getClass();
});

// 关联成功
function getProductId(row) {
  let data = {
    product_id: row.id,
  };
  relationApi(data, rows.value.id)
    .then(async (res) => {
      ElMessage.success(res.msg);
      row.id = 0;
      modal_loading.value = false;
      modals.value = false;
      setTimeout(() => {
        getList();
      }, 500);
    })
    .catch((res) => {
      modal_loading.value = false;
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function cancel() {
  modals.value = false;
}
// 等级列表
function getList() {
  loading.value = true;
  cmsListApi(artFrom)
    .then(async (res) => {
      let data = res.data;
      cmsList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 分类
function getClass() {
  categoryListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      treeData.value = data;
      let obj = {
        id: 0,
        title: '全部',
      };
      treeData.value.unshift(obj);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 下拉树
function handleCheckChange(data) {
  artFrom.pid = data ? data : 0;
  artFrom.page = 1;
  getList();
}
// 编辑
function edit(row) {
  router.push({ path: Setting.routePre + '/cms/article/add_article/' + row.id });
}
// 关联
function artRelation(row, tit, num) {
  rows.value = row;
  if (row.product_id === 0) {
    modals.value = true;
  } else {
    let delfromData = {
      title: tit,
      num: num,
      url: `/cms/cms/unrelation/${row.id}`,
      method: 'PUT',
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
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `cms/cms/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      cmsList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  artFrom.page = 1;
  getList();
}
function onCopy(row, type) {
  let copy_url = row.copy_url;
  proxy
    .$copyText(copy_url)
    .then((message) => {
      ElMessage.success('复制成功');
    })
    .catch((err) => {
      ElMessage.error('复制失败');
    });
}
</script>

<style lang="scss" scoped>
.treeSel :deep(.ivu-select-dropdown-list) {
  padding: 0 10px !important;
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
