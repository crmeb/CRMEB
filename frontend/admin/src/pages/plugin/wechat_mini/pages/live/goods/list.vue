<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          inline
          class="tabform"
          @submit.prevent
        >
          <el-form-item label="审核状态：">
            <el-select v-model="formValidate.status" clearable @change="selChange" class="form_content_width">
              <el-option
                v-for="(item, index) in treeData.withdrawal"
                :value="item.value"
                :key="index"
                :label="item.title"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入商品名称/ID"
              v-model="formValidate.kerword"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button
        v-auth="['setting-system_menus-add']"
        class="action-row"
        type="primary"
        v-db-click
        @click="menusAdd('添加商品')"
        >添加商品
      </el-button>
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="商品ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.product_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品名称" min-width="120">
          <template #default="scope">
            <div class="product_box">
              <div v-viewer>
                <img :src="scope.row.product.image" alt="" />
              </div>
              <div class="txt">{{ scope.row.name }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="直播价" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.cost_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="库存" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.product.stock }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="80">
          <template #default="scope">
            <div>{{ $filters.liveStatusFilter(scope.row.audit_status) }}</div>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="80">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row)"
              size="large"
              :disabled="scope.row.audit_status != 2"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row, '编辑')">详情</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
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
    <!-- 详情 -->
    <el-dialog v-model="modals" title="商品详情" class="paymentFooter" scrollable width="720px">
      <goodsFrom :FormData="FormData" />
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { liveGoodsListApi, liveGoodsShowApi, liveGoodsDetailApi } from '../../../api';
import goodsFrom from './detail.vue';
import { modalSure } from '@/utils/public';
import Setting from '@/setting';

defineOptions({ name: 'LiveGoodsList' });

const router = useRouter();

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const grid = { xl: 7, lg: 7, md: 12, sm: 24, xs: 24 };
const formValidate = ref({
  status: '',
  kerword: '',
  page: 1,
  limit: 15,
});
const treeData = ref({
  withdrawal: [
    { title: '全部', value: '' },
    { title: '待审核', value: 0 },
    { title: '已通过', value: 1 },
    { title: '未通过', value: -1 },
  ],
});
const tabList = ref([]);
const loading = ref(false);
const modals = ref(false);
const total = ref(0);
const FormData = ref({});

const formValidateRef = ref(null);
const table = ref(null);

onMounted(() => {
  getList();
});

/** 直播间显示隐藏 */
function onchangeIsShow({ id, is_show }) {
  liveGoodsShowApi(id, is_show)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
/** 列表数据 */
function getList() {
  loading.value = true;
  liveGoodsListApi(formValidate.value)
    .then((res) => {
      total.value = res.data.count;
      tabList.value = res.data.list;
      loading.value = false;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
      loading.value = false;
    });
}
/** 选择 */
function selChange() {
  formValidate.value.page = 1;
  getList();
}
/** 添加商品 */
function menusAdd() {
  router.push({
    path: Setting.routePre + '/marketing/live/add_live_goods',
  });
}
function edit(row) {
  liveGoodsDetailApi(row.id)
    .then((res) => {
      FormData.value = res.data;
      modals.value = true;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
/** 删除 */
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `live/goods/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.product_box {
  display: flex;
  align-items: center;
  img {
    width: 36px;
    height: 36px;
  }
  .txt {
    margin-left: 10px;
    color: #000;
    font-size: 12px;
  }
}
</style>
