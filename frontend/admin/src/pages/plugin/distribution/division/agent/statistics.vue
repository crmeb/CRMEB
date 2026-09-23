<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="时间范围：">
            <date-range-picker
              clearable
              v-model="timeVal"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-row class="ivu-mt box-wrapper">
        <el-col :xs="24" :sm="24" ref="rightBox">
          <el-tabs v-model="formValidate.type" @tab-change="userSearchs">
            <el-tab-pane
              v-for="(item, index) in statusList"
              :key="index"
              :label="item.status_name"
              :name="item.id"
            ></el-tab-pane>
          </el-tabs>
          <el-table
            :data="userLists"
            ref="table"
            v-loading="loading"
            highlight-current-row
            no-formValidate-text="暂无数据"
            no-filtered-formValidate-text="暂无筛选结果"
            @sort-change="handleSortChange"
          >
            <el-table-column label="用户UID" width="150">
              <template #default="scope">
                <span>{{ scope.row.uid }}</span>
              </template>
            </el-table-column>
            <el-table-column label="头像" min-width="120">
              <template #default="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.avatar" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="名称" min-width="130">
              <template #default="scope">
                <div class="acea-row">
                  <div v-text="scope.row.name"></div>
                </div>
              </template>
            </el-table-column>
            <el-table-column
              label="订单数"
              min-width="150"
              sortable="custom"
              :sort-orders="['ascending', 'descending']"
              prop="order_sum"
            >
              <template #default="scope">
                <span>{{ scope.row.order_sum }}</span>
              </template>
            </el-table-column>
            <el-table-column
              label="订单金额"
              min-width="150"
              sortable="custom"
              :sort-orders="['ascending', 'descending']"
              prop="order_sum_price"
            >
              <template #default="scope">
                <span>{{ scope.row.order_sum_price }}</span>
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
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { divisionStatistics } from '../../api';

defineOptions({ name: 'agent_extra' });

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '70px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const total = ref(0);
const total2 = ref(0);
const statusList = ref([
  {
    status_name: '事业部',
    id: '1',
  },
  {
    status_name: '代理商',
    id: '2',
  },
  {
    status_name: '员工',
    id: '3',
  },
]);
const userLists = ref([]);
const formInline = ref({
  uid: 0,
  proportion: 0,
  image: '',
});
const timeVal = ref([]);

const FromData = ref(null);
const loading = ref(false);
const current = ref(0);
const formValidate = ref({
  page: 1,
  limit: 15,
  time: '',
  type: '1',
  sort: '',
  order: '',
});
const staffModal = ref(false);
const clerkReqData = ref({
  uid: 0,
  page: 1,
  limit: 15,
});
const clerkLists = ref([]);

const formValidateRef = ref(null);
const table = ref(null);
const rightBox = ref(null);

// 具体日期
function onchangeTime(e) {
  timeVal.value = e || [];
  getList();
}
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  formValidate.value.time = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  divisionStatistics(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      userLists.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function pageChange(index) {
  formValidate.value.page = index;
  getList();
}
// 添加排序方法
function handleSortChange({ prop, order }) {
  formValidate.value.sort = prop;
  formValidate.value.order = order === 'ascending' ? 'asc' : 'desc';
  getList();
}

onMounted(() => {
  getList();
});
</script>

<style lang="scss" scoped>
:deep(.el-tabs__item) {
  height: 54px;
  line-height: 54px;
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
