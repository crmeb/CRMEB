<template>
  <div style="width: 100%">
    <el-dialog v-model="modals" title="用户详情" :close-on-click-modal="false" width="720px">
      <div class="" v-loading="spinShow">
        <div class="dashboard-workplace-header-tip">
          <div class="dashboard-workplace-header-tip-desc">
            <span class="dashboard-workplace-header-tip-desc-sp">姓名：{{ detailsData.nickname }}</span>
            <span class="dashboard-workplace-header-tip-desc-sp"
              >上级推广人：{{ detailsData.spread_name ? detailsData.spread_name : '无' }}</span
            >
            <span class="dashboard-workplace-header-tip-desc-sp">佣金总收入：{{ detailsData.number }}</span>
            <span class="dashboard-workplace-header-tip-desc-sp">用户余额：{{ detailsData.now_money }}</span>
            <span class="dashboard-workplace-header-tip-desc-sp">创建时间：{{ detailsData.add_time }}</span>
          </div>
        </div>
      </div>
      <el-divider direction="vertical" dashed />
      <el-form ref="formRef" label-width="75px" :label-position="labelPosition" class="tabform" @submit.prevent>
        <el-row :gutter="24">
          <!--                    <el-col :span="8">-->
          <!--                        <el-form-item label="订单号/昵称：">-->
          <!--                            <el-input enter-button placeholder="请输入" element-id="name" v-model="formValidate.nickname"-->
          <!--                                   clearable/>-->
          <!--                        </el-form-item>-->
          <!--                    </el-col>-->
          <el-col :span="12">
            <el-form-item label="时间范围：" class="tab_data">
              <date-range-picker
                width="250px"
                clearable
                :editable="false"
                @change="onchangeTime"
                format="YYYY-MM-DD"
                value-format="YYYY-MM-DD"
              ></date-range-picker>
            </el-form-item>
          </el-col>
          <el-col :span="4">
            <el-button type="primary" v-db-click @click="userSearchs">搜索</el-button>
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="tabList"
        ref="table"
        v-loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        class="table"
      >
        <el-table-column label="佣金金额" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.number }}</span>
          </template>
        </el-table-column>
        <el-table-column label="获得时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row._add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.mark }}</span>
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
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { commissionDetailApi, extractlistApi } from '@/api/finance';

defineOptions({ name: 'commissionDetails' });

const { isMobile } = storeToRefs(useMediaStore());

const modals = ref(false);
const spinShow = ref(false);
const detailsData = ref({});
const Ids = ref(0);
const loading = ref(false);
const formValidate = ref({
  nickname: '',
  start_time: '',
  end_time: '',
  page: 1, // 当前页
  limit: 15, // 每页显示条数
});
const total = ref(0);
const tabList = ref([]);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

onMounted(() => {
  if (Ids.value) {
    getList();
  }
});

// 时间
function onchangeTime(e) {
  formValidate.value.start_time = e[0];
  formValidate.value.end_time = e[1];
}
// 详情
function getDetails(id) {
  Ids.value = id;
  spinShow.value = true;
  commissionDetailApi(id)
    .then(async (res) => {
      if (res.status === 200) {
        let data = res.data;
        detailsData.value = data.user_info;
        spinShow.value = false;
      } else {
        spinShow.value = false;
        ElMessage.error(res.msg);
      }
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  extractlistApi(Ids.value, formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.data;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 搜索
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}

// 供父组件通过 $refs 调用（原 Options API 方法默认公开，script setup 需显式暴露）
defineExpose({ modals, getDetails, getList });
</script>

<style lang="scss">
.table {
  .ivu-table-default {
    overflow-y: auto;
    max-height: 350px;
  }
}
.dashboard-workplace {
  &-header {
    &-avatar {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      margin-right: 16px;
      font-weight: 600;
    }

    &-tip {
      width: 100%;
      display: inline-block;
      vertical-align: middle;

      &-title {
        font-size: 13px;
        color: #000000;
        margin-bottom: 12px;
      }

      &-desc {
        &-sp {
          width: 33.33%;
          color: var(--prev-color-text-regular);
          font-size: 12px;
          display: inline-block;
          padding-bottom: 10px;
        }
      }
    }

    &-extra {
      .ivu-col {
        p {
          text-align: right;
        }

        p:first-child {
          span:first-child {
            margin-right: 4px;
          }

          span:last-child {
            color: #808695;
          }
        }

        p:last-child {
          font-size: 22px;
        }
      }
    }
  }
}
</style>
