<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="是否显示：">
            <el-select v-model="formValidate.status" clearable @change="search" class="form_content_width">
              <el-option value="" label="全部"></el-option>
              <el-option :value="1" label="显示"></el-option>
              <el-option :value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="等级名称：">
            <el-input
              clearable
              placeholder="请输入等级名称"
              v-model="formValidate.keyword"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="search">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button type="primary" class="action-row" v-db-click @click="groupAdd">添加等级</el-button>
      <el-table
        class="mt14"
        :data="tabList"
        ref="table"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="50">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图片" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="等级" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.grade }}</span>
          </template>
        </el-table-column>
        <el-table-column label="一级分佣比例" min-width="150">
          <template #default="scope">
            <span
              >{{
                scope.row.one_brokerage_percent == '0.00'
                  ? scope.row.one_brokerage_ratio
                  : scope.row.one_brokerage_percent
              }}%</span
            >
          </template>
        </el-table-column>
        <el-table-column label="二级分佣比例" min-width="150">
          <template #default="scope">
            <span
              >{{
                scope.row.two_brokerage_percent == '0.00'
                  ? scope.row.two_brokerage_ratio
                  : scope.row.two_brokerage_percent
              }}%</span
            >
          </template>
        </el-table-column>
        <el-table-column label="任务总数" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.task_total_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="需完成数量" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.task_num }}</span>
          </template>
        </el-table-column>
        <!-- <el-table-column label="一级上浮比例" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.one_brokerage }}%</span>
          </template>
        </el-table-column>
        <el-table-column label="一级分佣比例(上浮后)" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.one_brokerage_ratio }}%</span>
          </template>
        </el-table-column>
        <el-table-column label="二级上浮比例" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.two_brokerage }}%</span>
          </template>
        </el-table-column>
        <el-table-column label="二级分佣比例(上浮后)" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.two_brokerage_ratio }}%</span>
          </template>
        </el-table-column> -->
        <el-table-column label="是否显示" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="addTask(scope.row)">等级任务</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
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
    <div class="task-modal">
      <el-dialog v-model="modal2" title="添加任务" width="1000px">
        <el-form :model="taskData" :label-width="labelWidth" :label-position="labelPosition" inline>
          <el-form-item label="是否显示：">
            <el-select v-model="taskData.status" class="form_content_width" clearable>
              <el-option :value="1" label="显示"></el-option>
              <el-option :value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="任务名称：">
            <el-input v-model="taskData.keyword" placeholder="请输入任务名称" clearable class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="searchTask">查询</el-button>
          </el-form-item>
        </el-form>
        <div>
          <div class="add-task">
            <el-button type="primary" v-db-click @click="taskAdd()">添加等级任务</el-button>
            <el-button type="primary" v-db-click @click="taskEdit()">设置完成数量</el-button>
          </div>
          <div>
            <el-table
              :data="taskTabList"
              ref="table"
              class="mt14"
              v-loading="taskLoading"
              highlight-current-row
              no-userFrom-text="暂无数据"
              no-filtered-userFrom-text="暂无筛选结果"
            >
              <el-table-column label="ID" width="80">
                <template #default="scope">
                  <span>{{ scope.row.id }}</span>
                </template>
              </el-table-column>
              <el-table-column label="名称" min-width="130">
                <template #default="scope">
                  <span>{{ scope.row.name }}</span>
                </template>
              </el-table-column>
              <el-table-column label="任务类型" min-width="80">
                <template #default="scope">
                  <span>{{ scope.row.type_name }}</span>
                </template>
              </el-table-column>
              <el-table-column label="限定数量" min-width="80">
                <template #default="scope">
                  <span>{{ scope.row.number }}</span>
                </template>
              </el-table-column>
              <el-table-column label="是否显示" min-width="80">
                <template #default="scope">
                  <el-switch
                    class="defineSwitch"
                    :active-value="1"
                    :inactive-value="0"
                    v-model="scope.row.status"
                    :value="scope.row.status"
                    @change="onchangeTaskIsShow(scope.row)"
                    active-text="开启"
                    inactive-text="关闭"
                  >
                    <template #open><span>开启</span></template>
                    <template #close><span>关闭</span></template>
                  </el-switch>
                </template>
              </el-table-column>
              <el-table-column label="排序" min-width="50">
                <template #default="scope">
                  <span>{{ scope.row.sort }}</span>
                </template>
              </el-table-column>
              <el-table-column label="操作" fixed="right" width="170">
                <template #default="scope">
                  <a v-db-click @click="editTask(scope.row, '编辑')">编辑</a>
                  <el-divider direction="vertical"></el-divider>
                  <a v-db-click @click="delTask(scope.row, '删除这条信息', scope.$index)">删除</a>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </el-dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  membershipDataAddApi,
  membershipDataListApi,
  membershipDataEditApi,
  membershipSetApi,
  levelTaskSetApi,
  levelTaskListDataAddApi,
  levelTaskDataEditApi,
  levelTaskDataAddApi,
  getTaskNumFormApi,
} from '@/api/membershipLevel';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useLayoutStore } from '@/store/module/layout';

defineOptions({ name: 'list' });

const route = useRoute();
// 原 mapState('admin/layout') 模块不存在，isMobile 恒为 undefined，保持等价
const layoutStore = useLayoutStore();

const table = ref(null);
const formValidateRef = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const modal1 = ref(false);
const modal2 = ref(false);
const formValidate = reactive({
  status: '',
  page: 1,
  limit: 15,
  gid: 0,
});
const taskData = reactive({
  keyword: '',
  page: 1,
  limit: 15,
  status: '',
});
const total = ref(0);
const taskTotal = ref(0);
const tabList = ref([]);
const taskTabList = ref([]);
const columns1 = [
  {
    key: 'id',
    minWidth: 35,
    title: 'ID',
  },
  {
    slot: 'image',
    minWidth: 35,
    title: '背景图',
  },
  {
    key: 'name',
    minWidth: 35,
    title: '名称',
  },
  {
    key: 'grade',
    minWidth: 35,
    title: '等级',
  },
  {
    slot: 'one_brokerage',
    minWidth: 35,
    title: '一级上浮比例',
  },
  {
    slot: 'one_brokerage_ratio',
    minWidth: 35,
    title: '一级分佣比例(上浮后)',
  },
  {
    slot: 'two_brokerage',
    minWidth: 35,
    title: '二级上浮比例',
  },
  {
    slot: 'two_brokerage_ratio',
    minWidth: 35,
    title: '二级分佣比例(上浮后)',
  },
  {
    slot: 'status',
    minWidth: 35,
    title: '是否显示',
  },
  {
    minWidth: 120,
    slot: 'action',
    title: '操作',
  },
];
const columns2 = [
  {
    key: 'id',
    minWidth: 35,
    title: 'ID',
  },
  {
    key: 'name',
    minWidth: 35,
    title: '名称',
  },
  {
    key: 'type_name',
    minWidth: 35,
    title: '任务类型',
  },
  {
    key: 'number',
    minWidth: 35,
    title: '限定数量',
  },
  {
    slot: 'status',
    minWidth: 35,
    title: '是否显示',
  },
  {
    key: 'sort',
    minWidth: 35,
    title: '排序',
  },
  {
    fixed: 'right',
    minWidth: 120,
    slot: 'action',
    title: '操作',
  },
];
const FromData = ref(null);
const loading = ref(false);
const taskLoading = ref(false);
const titleType = ref('group');
const groupAll = ref([]);
const theme3 = ref('light');
const labelSort = ref([]);
const sortName = ref(null);
const current = ref(0);
const model1 = ref('');
const value1 = ref('');

const isMobile = computed(() => layoutStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

watch(route, (to, from) => {
  if (route.params.id) {
  } else {
  }
});

let id = null;
let getListHeader = () => {};

function bindMenuItem(name, index) {
  current.value = index;
  formValidate.gid = name.id;
  getListHeader();
  getList();
}
// 列表
function getList() {
  loading.value = true;
  membershipDataListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 列表
function getTaskList() {
  taskLoading.value = true;
  levelTaskListDataAddApi(taskData)
    .then(async (res) => {
      let data = res.data;
      taskTabList.value = data.list;
      taskTotal.value = data.count;
      taskLoading.value = false;
    })
    .catch((res) => {
      taskLoading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function search() {
  formValidate.page = 1;
  getList();
}
function searchTask() {
  taskData.page = 1;
  getTaskList();
}
function taskEdit() {
  modalForm(getTaskNumFormApi(id)).then(() => getList());
}
// 添加表单
function groupAdd() {
  modalForm(membershipDataAddApi({}, '/distribution/level/create')).then(() => getList());
}
function taskAdd() {
  modalForm(levelTaskDataAddApi({}, '/distribution/level_task/create?level_id=' + taskData.id)).then(() =>
    getTaskList(),
  );
}
// 修改是否显示
function onchangeIsShow(row) {
  membershipSetApi(`distribution/level/set_status/${row.id}/${row.status}`)
    .then(async (res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改是否显示
function onchangeTaskIsShow(row) {
  levelTaskSetApi(`distribution/level_task/set_status/${row.id}/${row.status}`)
    .then(async (res) => {
      ElMessage.success(res.msg);
      getTaskList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
//添加等级任务
function addTask(row) {
  id = row.id;
  modal2.value = true;
  taskData.id = row.id;
  getTaskList();
}
// 编辑
function edit(row) {
  let data = {
    gid: row.gid,
  };
  modalForm(membershipDataEditApi(data, `distribution/level/${row.id}/edit`)).then(() => getList());
}
// 编辑
function editTask(row) {
  let data = {
    gid: row.gid,
  };
  modalForm(levelTaskDataEditApi(data, `distribution/level_task/${row.id}/edit`)).then(() => getTaskList());
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `distribution/level/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 删除
function delTask(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `distribution/level_task/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      taskTabList.value.splice(num, 1);
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
:deep(.ivu-menu-vertical .ivu-menu-item-group-title) {
  display: none;
}
:deep(.ivu-menu-vertical.ivu-menu-light:after) {
  display: none;
}
.left-wrapper {
  height: 904px;
}
.menu-item {
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;
  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }
  &:hover .icon-box {
    display: block;
  }
  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
  }
}
.tabBox-img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
.ivu-menu {
  z-index: auto;
}
.header,
.headers {
  display: flex;
  flex-direction: column;
  background-color: #f2f2f2;
  padding: 8px;
  .search {
    display: flex;
    align-items: center;
    > div {
      margin-right: 10px;
    }
  }
}
.search :deep(.ivu-select-selection) {
  border: 1px solid #dcdee2 !important;
}
.headers {
  background-color: var(--prev-bg-white);
  margin-bottom: 20px;
}
:deep(.ivu-modal-mask) {
  z-index: 100 !important;
}
:deep(.ivu-modal-wrap) {
  z-index: 100 !important;
}
.add-task {
  margin: 10px 0;
}
</style>
