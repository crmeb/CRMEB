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
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-row class="action-row mb14">
        <el-col :xs="24" :sm="24" ref="rightBox">
          <el-button type="primary" v-db-click @click="groupAdd('0')">添加事业部</el-button>
          <el-tooltip placement="right-start">
            <i class="el-icon-question ml10"></i>
            <template #content
              ><div>
                <div>
                  事业部层级说明：事业部-代理商-员工。事业部相当于总代理或者区域代理，设置成为事业部之后，关联的用户会清除上级推广人
                </div>
                <div>
                  添加时候的管理员身份需要在，设置-管理权限-角色管理中设置对应的角色，事业部可以使用添加时设置的管理员账号密码登录后台
                </div>
              </div></template
            >
          </el-tooltip>
          <el-table
            :data="userLists"
            ref="table"
            class="mt14"
            v-loading="loading"
            highlight-current-row
            no-formValidate-text="暂无数据"
            no-filtered-formValidate-text="暂无筛选结果"
          >
            <el-table-column label="用户UID" width="100">
              <template #default="scope">
                <span>{{ scope.row.uid }}</span>
              </template>
            </el-table-column>
            <el-table-column label="头像" min-width="90">
              <template #default="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.avatar" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="名称" min-width="130">
              <template #default="scope">
                <div class="acea-row">
                  <div v-text="scope.row.division_name"></div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="邀请码" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.division_invite }}</span>
              </template>
            </el-table-column>
            <el-table-column label="分销比例" min-width="130">
              <template #default="scope">
                <span> {{ scope.row.division_percent }}%</span>
              </template>
            </el-table-column>
            <el-table-column label="代理商数量" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.agent_count }}</span>
              </template>
            </el-table-column>
            <el-table-column label="截止时间" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.division_end_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="状态" min-width="130">
              <template #default="scope">
                <el-switch
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.division_status"
                  :value="scope.row.division_status"
                  @change="onchangeIsShow(scope.row)"
                  size="large"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="170">
              <template #default="scope">
                <a v-db-click @click="jump(scope.row.uid)">查看代理商</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="groupAdd(scope.row.uid)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除事业部', scope.$index)">删除</a>
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
    <el-dialog v-model="staffModal" title="代理商列表" class="order_box" width="1000px">
      <el-table
        :data="clerkLists"
        ref="table"
        class="mt20"
        v-loading="loading"
        highlight-current-row
        no-formValidate-text="暂无数据"
        no-filtered-formValidate-text="暂无筛选结果"
      >
        <el-table-column label="用户UID" width="80">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="名称" min-width="130">
          <template #default="scope">
            <div class="acea-row">
              <div v-text="scope.row.division_name" class="ml10"></div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="分销比例" min-width="130">
          <template #default="scope">
            <span> {{ scope.row.division_percent }}%</span>
          </template>
        </el-table-column>
        <el-table-column label="到期时间" min-width="130">
          <template #default="scope">
            <span> {{ $filters.formatDate(scope.row.division_end_time) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="员工数量" min-width="130">
          <template #default="scope">
            <span> {{ scope.row.agent_count }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total2"
          :total="total2"
          v-model:page="clerkReqData.page"
          v-model:limit="clerkReqData.limit"
          @pagination="getClerkList"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { regionList, regionFrom, isShowApi, clerkList } from '../../api';
import modalForm from '@/utils/modalForm';
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
const FromData = ref(null);
const loading = ref(false);
const current = ref(0);
const formValidate = ref({
  page: 1,
  limit: 15,
  keyword: '',
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

// 搜索
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
function jump(uid) {
  clerkReqData.value.uid = uid;
  getClerkList();
}
function getClerkList() {
  clerkReqData.value.division_type = 2;
  clerkList(clerkReqData.value).then((res) => {
    clerkLists.value = res.data.list;
    total2.value = res.data.count;
    staffModal.value = true;
  });
}
// 列表
function getList() {
  loading.value = true;
  formValidate.value.division_type = 1;
  regionList(formValidate.value)
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
// 添加表单
function groupAdd(id) {
  modalForm(regionFrom(id))
    .then((res) => {
      getList();
    })
    .catch((err) => {});
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.uid,
    status: row.division_status,
  };
  isShowApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    method: 'DELETE',
    uid: row.uid,
    url: `distribution/division/del/1/${row.uid}`,
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
</style>
