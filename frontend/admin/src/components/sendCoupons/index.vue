<template>
  <div>
    <el-dialog v-model="modals" :z-index="100" title="发送优惠券" :close-on-click-modal="false" width="1000px">
      <div class="acea-row">
        <span class="sp">优惠券名称：</span
        ><el-input clearable v-model="page.coupon_title" placeholder="请输入优惠券名称" class="form_content_width" />
        <el-button type="primary" v-db-click @click="userSearchs" class="ml15">查询</el-button>
      </div>
      <el-table
        :data="couponList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="优惠券名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券面值" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.coupon_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券最低消费" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.use_min_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券有效期限" min-width="130">
          <template #default="scope">
            <div v-if="scope.row.coupon_time">{{ scope.row.coupon_time }}天</div>
            <div v-else>{{ scope.row.use_time }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="90">
          <template #default="scope">
            <a v-db-click @click="sendGrant(scope.row, '发送优惠券', index)">发送</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="page.page"
          v-model:limit="page.limit"
          @pagination="getList"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { couponApi } from '@/api/user';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'send' });

const props = defineProps({
  userIds: {
    type: String,
    default: '',
  },
});

const modals = ref(false);
const loading = ref(false);
const couponList = ref([]);
const page = reactive({
  page: 1, // 当前页
  limit: 15,
  coupon_title: '',
  receive_type: 3,
});
const total = ref(0); // 总条数

// 优惠券列表
function getList(id) {
  loading.value = true;
  couponApi(page)
    .then(async (res) => {
      if (res.status === 200) {
        let data = res.data;
        couponList.value = data.list;
        total.value = data.count;
        loading.value = false;
      } else {
        loading.value = false;
        ElMessage.error(res.msg);
      }
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  getList();
}
// 发送
function sendGrant(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/coupon/user/grant`,
    method: 'post',
    ids: {
      id: row.id,
      uid: props.userIds,
    },
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 页面通过 $refs.sends/$refs.sendRef 的 modals/getList 驱动
defineExpose({ modals, getList });
</script>

<style lang="scss" scoped>
.sp {
  font-size: 12px;
  color: var(--prev-color-text-primary);
  line-height: 32px;
}
</style>
