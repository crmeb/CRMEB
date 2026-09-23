<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="时间选择：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="拼团状态：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option :value="1" label="进行中"></el-option>
              <el-option :value="2" label="已完成"></el-option>
              <el-option :value="3" label="未完成"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table :data="tableList" v-loading="loading" highlight-current-row>
        <el-table-column label="头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer><img v-lazy="scope.row.avatar" /></div>
          </template>
        </el-table-column>
        <el-table-column label="开团团长" min-width="150">
          <template #default="scope"
            ><span>{{ scope.row.nickname + ' / ' + scope.row.uid }}</span></template
          >
        </el-table-column>
        <el-table-column label="开团时间" min-width="150">
          <template #default="scope"
            ><span>{{ $filters.formatDate(scope.row.add_time) }}</span></template
          >
        </el-table-column>
        <el-table-column label="拼团商品" min-width="180">
          <template #default="scope"
            ><span>{{ scope.row.title + ' / ' + scope.row.cid }}</span></template
          >
        </el-table-column>
        <el-table-column label="几人团" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.people }}</span></template
          >
        </el-table-column>
        <el-table-column label="几人参加" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.count_people }}</span></template
          >
        </el-table-column>
        <el-table-column label="结束时间" min-width="120">
          <template #default="scope"
            ><span>{{ $filters.formatDate(scope.row.stop_time) }}</span></template
          >
        </el-table-column>
        <el-table-column label="状态" min-width="120">
          <template #default="scope">
            <el-tag type="info" v-show="scope.row.status === 1">进行中</el-tag>
            <el-tag v-show="scope.row.status === 2">已完成</el-tag>
            <el-tag type="warning" v-show="scope.row.status === 3">未完成</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a v-db-click @click="Info(scope.row)">查看详情</a>
            <el-divider v-if="scope.row.status === 1" direction="vertical"></el-divider>
            <a v-if="scope.row.status === 1" v-db-click @click="joinCombination(scope.row)">立即成团</a>
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
    <!-- 详情模态框-->
    <el-dialog v-model="modals" class="tableBox" title="查看详情" :close-on-click-modal="false" width="720px">
      <el-table
        ref="selection"
        :data="tabList3"
        v-loading="loading2"
        empty-text="暂无数据"
        highlight-current-row
        max-height="600"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope"
            ><span>{{ scope.row.id }}</span></template
          >
        </el-table-column>
        <el-table-column label="用户名称" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.nickname + ' / ' + scope.row.uid }}</span></template
          >
        </el-table-column>
        <el-table-column label="用户头像" min-width="150">
          <template #default="scope">
            <div class="tabBox_img" v-viewer><img v-lazy="scope.row.avatar" /></div>
          </template>
        </el-table-column>
        <el-table-column label="订单编号" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.order_id }}</span></template
          >
        </el-table-column>
        <el-table-column label="金额" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.total_price }}</span></template
          >
        </el-table-column>
        <el-table-column label="订单状态" min-width="100">
          <template #default="scope">
            <el-tag v-show="scope.row.is_refund != 0">已退款</el-tag>
            <el-tag type="danger" v-show="scope.row.is_refund === 0">未退款</el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import cardsData from '@/components/cards/cards';
import { combineListApi, orderPinkListApi, statisticsApi, combineJoinApi } from '../../api';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'combinationPinkList' });

const mediaStore = useMediaStore();

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const formRef = ref(null);
const cardLists = ref([]);
const modals = ref(false);
const loading = ref(false);
const formValidate = ref({ status: '', data: '', page: 1, limit: 15 });
const tableList = ref([]);
const total = ref(0);
const timeVal = ref([]);
const loading2 = ref(false);
const tabList3 = ref([]);
const rows = ref({});

onMounted(() => {
  getList();
  getStatistics();
});

function getStatistics() {
  statisticsApi()
    .then(async (res) => {
      cardLists.value = res.data.res;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function Info(row) {
  modals.value = true;
  rows.value = row;
  orderPinkListApi(row.id)
    .then(async (res) => {
      tabList3.value = res.data.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function joinCombination(row) {
  ElMessageBox.confirm('确认成团？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  })
    .then(() => {
      combineJoinApi(row.id)
        .then((res) => {
          ElMessage.success(res.msg);
          getList();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    })
    .catch(() => {
      ElMessage.info('已取消');
    });
}
function onchangeTime(e) {
  timeVal.value = e || [];
  formValidate.value.data = timeVal.value[0] === '' ? '' : timeVal.value ? timeVal.value.join('-') : '';
  formValidate.value.page = 1;
  getList();
}
function getList() {
  loading.value = true;
  formValidate.value.status = formValidate.value.status || '';
  combineListApi(formValidate.value)
    .then(async (res) => {
      tableList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
</script>

<style lang="scss" scoped>
.article-manager {
  margin-top: 3px;
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
