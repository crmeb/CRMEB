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
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入姓名、UID"
              v-model="formValidate.keyword"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-row class="box-wrapper">
        <el-col :xs="24" :sm="24" ref="rightBox">
          <el-tabs v-model="formValidate.status" @tab-change="userSearchs">
            <el-tab-pane name="all" label="全部"></el-tab-pane>
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
          >
            <el-table-column label="编号" width="80">
              <template #default="scope">
                <span>{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="用户UID" width="80">
              <template #default="scope">
                <span>{{ scope.row.uid }}</span>
              </template>
            </el-table-column>
            <el-table-column label="用户昵称" min-width="150">
              <template #default="scope">
                <span>{{ scope.row.nickname }}</span>
              </template>
            </el-table-column>
            <el-table-column label="分销员电话" min-width="150">
              <template #default="scope">
                <span>{{ scope.row.phone }}</span>
              </template>
            </el-table-column>
            <el-table-column label="分销员姓名" min-width="150">
              <template #default="scope">
                <span>{{ scope.row.real_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="申请状态" min-width="150">
              <template #default="scope">
                <el-tag>{{ scope.row.status == 0 ? '申请中' : scope.row.status == 1 ? '已同意' : '已拒绝' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="申请时间" min-width="150">
              <template #default="scope">
                <span>{{ scope.row.add_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="申请理由" min-width="150">
              <template #default="scope">
                <span>{{ scope.row.content }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="170">
              <template #default="scope">
                <a v-if="scope.row.status == 0" v-db-click @click="examine(scope.row.id, scope.row.uid, 1)">同意</a>
                <el-divider v-if="scope.row.status == 0" direction="vertical" />
                <a v-if="scope.row.status == 0" v-db-click @click="examine(scope.row.id, scope.row.uid, 2)">拒绝</a>
                <el-divider direction="vertical" v-if="scope.row.status == 0" />
                <a v-db-click @click="del(scope.row, '删除申请', scope.$index)">删除</a>
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
import { ElMessage, ElMessageBox } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { spreadList, spreadFrom, clerkList } from '../api';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'agent_extra' });

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '50px'));
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
const userLists = ref([]);
const formInline = ref({
  uid: 0,
  proportion: 0,
  image: '',
});
const statusList = ref([
  {
    status_name: '申请中',
    id: '0',
  },
  {
    status_name: '已同意',
    id: '1',
  },
  {
    status_name: '已拒绝',
    id: '2',
  },
]);
const FromData = ref(null);
const loading = ref(false);
const current = ref(0);
const formValidate = ref({
  page: 1,
  limit: 15,
  keyword: '',
  status: 'all',
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

function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  spreadList(formValidate.value)
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
// 审核
function examine(id, uid, type) {
  if (type == 1) {
    let data = {
      title: type == 1 ? '通过分销员申请' : '拒绝分销员申请',
      url: `distribution/spread/apply/examine/${id}/${uid}/${type}`,
      method: 'post',
      ids: '',
    };
    modalSure(data)
      .then((res) => {
        ElMessage.success(res.msg);
        getList();
      })
      .catch((res) => {
        ElMessage.error(res.msg);
      });
  } else {
    ElMessageBox.prompt('请输入拒绝理由', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
    })
      .then(({ value }) => {
        let data = {
          refusal_reason: value,
        };
        spreadFrom(id, uid, type, data).then((res) => {
          ElMessage.success(res.msg);
          getList();
        });
      })
      .catch(() => {
        ElMessage({
          type: 'info',
          message: '取消输入',
        });
      });
  }
}
// 编辑
function edit(row) {}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    method: 'DELETE',
    uid: row.id,
    url: `distribution/spread/apply/del/${row.id}`,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      userLists.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

onMounted(() => {
  getList();
});
</script>

<style lang="scss" scoped>
.ivu-form-item {
  margin-bottom: 0;
}
.picBox {
  display: inline-block;
  cursor: pointer;
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
}
:deep(.ivu-menu-vertical .ivu-menu-item-group-title) {
  display: none;
}
:deep(.ivu-menu-vertical.ivu-menu-light:after) {
  display: none;
}
.left-wrapper {
  height: 904px;
  background: #fff;
  border-right: 1px solid #f2f2f2;
}
.menu-item {
  z-index: 50;
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;
  &:hover .icon-box {
    display: block;
  }
}
.icon-box {
  z-index: 3;
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  display: none;
}

:deep(.el-tabs__item) {
  height: 54px;
  line-height: 54px;
}
.right-menu {
  z-index: 10;
  position: absolute;
  right: -106px;
  top: -11px;
  width: auto;
  min-width: 121px;
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
.pictrue-box {
  display: flex;
  align-item: center;
}
.pictrue {
  width: 25px;
  height: 25px;
}
</style>
