<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          inline
          @submit.prevent
          class="tabform"
        >
          <el-form-item label="直播状态：">
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
              placeholder="请输入直播间名称/ID/主播昵称/微信号"
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
        @click="menusAdd('添加直播间')"
        >添加直播间</el-button
      >
      <el-button v-auth="['setting-system_menus-add']" v-db-click @click="syncRoom" style="margin-left: 20px"
        >同步直播间</el-button
      >
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="直播间ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="直播间名称" min-width="35">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="主播昵称" min-width="35">
          <template #default="scope">
            <span>{{ scope.row.anchor_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="主播微信号" min-width="35">
          <template #default="scope">
            <span>{{ scope.row.anchor_wechat }}</span>
          </template>
        </el-table-column>
        <el-table-column label="直播开始时间" min-width="35">
          <template #default="scope">
            <span>{{ scope.row.start_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="计划结束时间" min-width="35">
          <template #default="scope">
            <span>{{ scope.row.end_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="35">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="显示状态" min-width="35">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="直播状态" min-width="35">
          <template #default="scope">
            <div>{{ $filters.liveReviewStatusFilter(scope.row.live_status) }}</div>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="35">
          <template #default="scope">
            <div>{{ scope.row.sort }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="detail(scope.row, '详情')">详情</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
            <el-divider direction="vertical" v-if="scope.row.live_status == 102" />
            <a v-if="scope.row.live_status == 102" v-db-click @click="addGoods(scope.row)">添加商品</a>
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
    <el-dialog v-model="modals" title="直播间详情" class="paymentFooter" width="720px">
      <details-from ref="studioDetail" />
    </el-dialog>
    <!-- 添加商品 -->
    <el-dialog v-model="isShowBox" title="添加商品" class="paymentFooter" width="720px">
      <goods-list
        ref="goodslist"
        @getProductId="getProductId"
        v-if="isShowBox"
        :selectIds="selectIds"
        :ischeckbox="true"
        :liveStatus="true"
      ></goods-list>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { liveListApi, liveShowApi, liveRoomGoodsAddApi, liveSyncRoomApi } from '../../../api';
import detailsFrom from './detail.vue';
import goodsList from '@/components/goodsList';
import { modalSure } from '@/utils/public';
import Setting from '@/setting';

defineOptions({ name: 'LiveRoomList' });

const router = useRouter();

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const isShowBox = ref(false);
const modals = ref(false);
const total = ref(0);
const grid = ref({
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
});
const formValidate = ref({
  status: '',
  kerword: '',
  page: 1,
  limit: 15,
});
const treeData = ref({
  withdrawal: [
    { title: '全部', value: '' },
    { title: '直播中', value: 1 },
    { title: '未开始', value: 2 },
    { title: '已结束', value: 3 },
  ],
});
const tabList = ref([]);
const loading = ref(false);
const activeItem = ref({});
const selectIds = ref([]);

const table = ref(null);
const studioDetail = ref(null);
const goodslist = ref(null);

/** 获取直播列表 */
function getList() {
  loading.value = true;
  liveListApi(formValidate.value).then((res) => {
    total.value = res.data.count;
    tabList.value = res.data.list;
    loading.value = false;
  });
}
/** 选择查询 */
function selChange() {
  formValidate.value.page = 1;
  getList();
}
/** 添加直播间 */
function menusAdd() {
  router.push({
    path: Setting.routePre + '/marketing/live/add_live_room',
  });
}
/** 直播间显示隐藏 */
function onchangeIsShow({ id, is_show }) {
  liveShowApi(id, is_show)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
/** 详情 */
function detail(row) {
  modals.value = true;
  studioDetail.value.getData(row.id);
}
/** 直播间添加商品 */
function addGoods(row) {
  selectIds.value = row.product_ids;
  activeItem.value = row;
  isShowBox.value = true;
}
function getData(data) {
  liveRoomGoodsAddApi({
    room_id: activeItem.value.id,
    goods_ids: data,
  })
    .then((res) => {
      ElMessage.success(res.msg);
      isShowBox.value = false;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
      isShowBox.value = false;
    });
}
/** 同步直播间 */
function syncRoom() {
  liveSyncRoomApi()
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
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
    url: `live/room/del/${row.id}`,
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
function getProductId(data) {
  let arr = [];
  data.map((el) => {
    arr.push(el.product_id);
  });
  getData(arr);
}

getList();
</script>

<style lang="scss" scoped>
:deep(.goodList .ivu-input-group) {
  width: 200% !important;
}
</style>
