<template>
  <div style="width: 100%">
    <el-drawer v-model="modals" title="用户详情" :wrapperClosable="false" :size="1100" @closed="draChange">
      <div class="acea-row head">
        <div class="avatar mr15"><img :src="psInfo.avatar" /></div>
        <div class="dashboard-workplace-header-tip">
          <p class="dashboard-workplace-header-tip-title f-w-b" v-text="psInfo.nickname || '-'"></p>
          <div class="dashboard-workplace-header-tip-desc">
            <span class="dashboard-workplace-header-tip-desc-sp" v-for="(item, index) in detailsData" :key="index">{{
              item.title + '：' + item.value
            }}</span>
          </div>
        </div>
        <div class="edit-btn" v-if="!psInfo.is_del">
          <el-button v-if="!isEdit" type="primary" v-db-click @click="edit">编辑</el-button>
          <el-button v-if="isEdit" v-db-click @click="edit">取消</el-button>
          <el-button v-if="isEdit" type="primary" v-db-click @click="editSave">保存</el-button>
        </div>
      </div>
      <el-row justify="space-between" class="mt14">
        <el-col :span="24">
          <!-- tab-click 回调拿到的是 pane 组件实例（tab.name 为 undefined），type 会丢参；tab-change 直接收 name 字符串 -->
          <el-tabs type="border-card" v-model="activeName" @tab-change="changeTab">
            <el-tab-pane name="user" label="用户信息">
              <userEditForm ref="editForm" :userId="userId" @success="getDetails(userId)" v-if="isEdit"></userEditForm>
              <user-info :ps-info="psInfo" v-else></user-info>
            </el-tab-pane>
            <el-tab-pane :name="item.val" v-for="(item, index) in list" :key="index" :label="item.label">
              <el-table
                class="mt20"
                :data="userLists"
                max-height="400"
                ref="table"
                v-loading="loading"
                no-userFrom-text="暂无数据"
                no-filtered-userFrom-text="暂无筛选结果"
              >
                <el-table-column :label="item.title" min-width="120" v-for="(item, index) in columns" :key="index">
                  <template #default="scope">
                    <template v-if="item.key">
                      <div>
                        <span>{{ scope.row[item.key] }}</span>
                      </div>
                    </template>
                    <template v-else-if="item.slot === 'number'">
                      <div :class="scope.row.pm ? 'plusColor' : 'reduceColor'">
                        {{ scope.row.pm ? '+' + scope.row.number : '-' + scope.row.number }}
                      </div>
                    </template>
                  </template>
                </el-table-column>
              </el-table>
              <div class="acea-row row-right page">
                <pagination
                  v-if="total"
                  :total="total"
                  v-model:page="userFrom.page"
                  v-model:limit="userFrom.limit"
                  @pagination="changeType"
                />
              </div>
            </el-tab-pane>
          </el-tabs>
        </el-col>

        <!-- <el-col :span="24">
          
        </el-col> -->
      </el-row>
    </el-drawer>
  </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { detailsApi, infoApi } from '@/api/user';
import userInfo from './userInfo';
import userEditForm from './userEditForm';

defineOptions({ name: 'userDetails' });

const isEdit = ref(false);
const theme2 = ref('light');
const list = [
  { val: 'order', label: '消费记录' },
  { val: 'integral', label: '积分明细' },
  { val: 'sign', label: '签到记录' },
  { val: 'coupon', label: '持有优惠券' },
  { val: 'balance_change', label: '余额变动' },
  { val: 'spread', label: '好友关系' },
];
const modals = ref(false);
const spinShow = ref(false);
const detailsData = ref([]);
const userId = ref(0);
const loading = ref(false);
const userFrom = ref({
  type: 'order',
  page: 1, // 当前页
  limit: 15, // 每页显示条数
});
const total = ref(0);
const columns = ref([]);
const userLists = ref([]);
const psInfo = ref({});
const activeName = ref('user');
const editForm = ref(null);

function edit() {
  activeName.value = 'user';
  isEdit.value = !isEdit.value;
}

function editSave() {
  editForm.value.setUser();
}

function draChange() {
  isEdit.value = false;
}

// 会员详情
function getDetails(id) {
  activeName.value = 'user';
  userId.value = id;
  spinShow.value = true;
  isEdit.value = false;
  detailsApi(id)
    .then(async (res) => {
      if (res.status === 200) {
        let data = res.data;
        detailsData.value = data.headerList;
        psInfo.value = data.ps_info;
        // changeType('user');
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

function changeTab(name) {
  activeName.value = name;
  changeType();
}

// tab选项
function changeType() {
  loading.value = true;
  userFrom.value.type = activeName.value;
  isEdit.value = false;
  if (activeName.value == 'user') return;
  if (userFrom.value.type === '') {
    userFrom.value.type = 'order';
  }
  let data = {
    id: userId.value,
    datas: userFrom.value,
  };
  infoApi(data)
    .then(async (res) => {
      if (res.status === 200) {
        switch (userFrom.value.type) {
          case 'order':
            columns.value = [
              {
                title: '订单ID',
                key: 'order_id',
                minWidth: 160,
              },
              {
                title: '收货人',
                key: 'real_name',
                minWidth: 100,
              },
              {
                title: '商品数量',
                key: 'total_num',
                minWidth: 90,
              },
              {
                title: '实付金额',
                key: 'pay_price',
                minWidth: 120,
              },
              {
                title: '交易完成时间',
                key: 'pay_time',
                minWidth: 120,
              },
            ];
            break;
          case 'integral':
            columns.value = [
              {
                title: '来源/用途',
                key: 'title',
                minWidth: 120,
              },
              {
                title: '积分变化',
                slot: 'number',
                minWidth: 120,
              },
              {
                title: '变化后积分',
                key: 'balance',
                minWidth: 120,
              },
              {
                title: '日期',
                key: 'add_time',
                minWidth: 120,
              },
              {
                title: '备注',
                key: 'mark',
                minWidth: 120,
              },
            ];
            break;
          case 'sign':
            columns.value = [
              {
                title: '动作',
                key: 'title',
                minWidth: 120,
              },
              {
                title: '获得积分',
                key: 'number',
                minWidth: 120,
              },
              {
                title: '签到时间',
                key: 'add_time',
                minWidth: 120,
              },
              {
                title: '备注',
                key: 'mark',
                minWidth: 120,
              },
            ];
            break;
          case 'coupon':
            columns.value = [
              {
                title: '优惠券名称',
                key: 'coupon_title',
                minWidth: 120,
              },
              {
                title: '面值',
                key: 'coupon_price',
                minWidth: 120,
              },
              {
                title: '有效期(天)',
                key: 'coupon_time',
                minWidth: 120,
              },
              {
                title: '领取时间',
                key: '_add_time',
                minWidth: 120,
              },
            ];
            break;
          case 'balance_change':
            columns.value = [
              {
                title: '动作',
                key: 'title',
                minWidth: 120,
              },
              {
                title: '余额变动',
                slot: 'number',
                minWidth: 120,
              },
              {
                title: '当前余额',
                key: 'balance',
                minWidth: 120,
              },
              {
                title: '创建时间',
                key: 'add_time',
                minWidth: 120,
              },
              {
                title: '备注',
                key: 'mark',
                minWidth: 120,
              },
            ];
            break;
          default:
            columns.value = [
              {
                title: 'ID',
                key: 'uid',
                minWidth: 120,
              },
              {
                title: '昵称',
                key: 'nickname',
                minWidth: 120,
              },
              {
                title: '等级',
                key: 'type',
                minWidth: 120,
              },
              {
                title: '加入时间',
                key: 'add_time',
                minWidth: 120,
              },
            ];
        }
        nextTick((e) => {
          let data = res.data;
          userLists.value = data.list;
          total.value = data.count;
        });
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

// 列表页通过 userDetailsRef.modals / userDetailsRef.getDetails 驱动
defineExpose({ modals, getDetails });
</script>

<style lang="scss" scoped>
.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  img {
    width: 100%;
    height: 100%;
  }
}
:deep(.el-drawer__body) {
  padding: 20px 0 !important;
}
:deep(.el-tabs--border-card > .el-tabs__content) {
  padding: 0 35px;
}
:deep(.el-tabs--border-card > .el-tabs__header),
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item:active) {
  border: none;
  height: 40px;
}
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active) {
  border: none;
  border-top: 2px solid var(--prev-color-primary);
  font-size: 13px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  line-height: 16px;
}
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item) {
  border: none;
}
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item) {
  margin-top: 0;
  transition: none;
  height: 40px !important;
  line-height: 40px !important;
  width: 92px !important;
  font-size: 13px;
  font-weight: 400;
  color: var(--prev-color-text-regular);
  line-height: 16px;
}
:deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;
}
.head {
  position: relative;
  padding: 0 15px;
  .edit-btn {
    position: absolute;
    right: 20px;
    top: 0px;
    display: flex;
    align-items: center;
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
      width: 82%;
      display: inline-block;
      vertical-align: middle;

      &-title {
        font-size: 13px;
        color: var(--prev-color-text-regular);
        margin-bottom: 12px;
      }

      &-desc {
        &-sp {
          width: 33.33%;
          color: var(--prev-color-text-regular);
          font-size: 13px;
          display: inline-block;
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
            color: var(--prev-color-text-secondary);
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
<style lang="scss" scoped>
.user_menu :deep(.ivu-menu) {
  width: 100% !important;
}
</style>
