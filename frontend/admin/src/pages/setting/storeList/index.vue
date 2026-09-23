<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="artFromRef"
          :model="artFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="门店搜索：">
            <el-input
              clearable
              placeholder="请输入门店名称,电话"
              v-model="artFrom.keywords"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px 20px' }">
      <div v-if="headeNum.show.name">
        <el-tabs v-model="artFrom.type" @tab-change="onClickTab">
          <el-tab-pane :label="headeNum.show.name + '(' + headeNum.show.num + ')'" name="0" />
          <el-tab-pane :label="headeNum.hide.name + '(' + headeNum.hide.num + ')'" name="1" />
          <el-tab-pane :label="headeNum.recycle.name + '(' + headeNum.recycle.num + ')'" name="2" />
        </el-tabs>
      </div>
      <el-row v-auth="['setting-merchant-system_store-save']">
        <el-col v-bind="grid">
          <el-button v-auth="['setting-merchant-system_store-save']" type="primary" v-db-click @click="add"
            >添加门店</el-button
          >
        </el-col>
      </el-row>
      <el-table
        :data="storeLists"
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
        <el-table-column label="门店图片" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="门店名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="门店电话" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="地址" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.detailed_address }}</span>
          </template>
        </el-table-column>
        <el-table-column label="营业时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.day_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row.id, scope.row.is_show)"
              size="large"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-if="scope.row.is_del == 0" v-db-click @click="del(scope.row, '删除门店', scope.$index)">删除</a>
            <a v-else v-db-click @click="del(scope.row, '恢复门店', scope.$index)">恢复</a>
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
    <system-store ref="template" @success="submitSuccess"></system-store>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { storeGetHeaderApi, merchantStoreApi, storeSetShowApi } from '@/api/setting';
import systemStore from '@/components/systemStore/index';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';

defineOptions({ name: 'setting_store' });

const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();

const table = ref(null);
const artFromRef = ref(null);
const template = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const categoryId = computed(() => userLevelStore.categoryId);
const labelWidth = computed(() => (isMobile.value ? undefined : '90px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const grid = {
  xl: 10,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const headeNum = ref({
  show: { name: '', num: 0 },
  hide: { name: '', num: 0 },
  recycle: { name: '', num: 0 },
});
const artFrom = reactive({
  page: 1,
  limit: 15,
  type: '0',
  keywords: '',
});
const loading = ref(false);
const storeLists = ref([]);
const total = ref(0);

// 获取表单头部信息；
function storeHeade() {
  storeGetHeaderApi()
    .then((res) => {
      headeNum.value = res.data.count;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function getList() {
  loading.value = true;
  merchantStoreApi(artFrom)
    .then((res) => {
      loading.value = false;
      storeLists.value = res.data.list;
      total.value = res.data.count;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 搜索；
function userSearchs() {
  artFrom.page = 1;
  getList();
}
// 切换导航；
function onClickTab() {
  artFrom.page = 1;
  artFrom.keywords = '';
  getList();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `merchant/store/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      storeLists.value.splice(num, 1);
      storeHeade();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加门店；
function add() {
  template.value.isTemplate = true;
}
function onchangeIsShow(id, is_show) {
  storeSetShowApi(id, is_show).then((res) => {
    ElMessage.success(res.msg);
    getList();
    storeHeade();
  });
}
function edit(id) {
  template.value.isTemplate = true;
  template.value.getInfo(id);
}
// 新增/编辑门店成功回调
function submitSuccess() {
  getList();
  storeHeade();
}

onMounted(() => {
  storeHeade();
  getList();
});
</script>

<style lang="scss" scoped>
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
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
