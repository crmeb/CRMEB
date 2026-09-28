<template>
  <div>
    <!--    <div class="i-layout-page-header header-title">-->
    <!--      <div class="fl_header">-->
    <!--        <router-link v-if="$route.params.id != 49" :to="{ path: $routeProStr + '/system/config/system_group/index' }"-->
    <!--          ><el-button type="text">返回</el-button></router-link-->
    <!--        >-->
    <!--        <el-divider direction="vertical" v-if="$route.params.id != 49" />-->
    <!--        <span class="ivu-page-header-title mr20" style="padding: 0" v-text="$route.meta.title"></span>-->
    <!--      </div>-->
    <!--    </div>-->
    <pages-header
      class="mb16"
      ref="pageHeader"
      :title="$route.meta.title"
      description="维护当前数据组的子项数据，支持增删改与显示开关，常用于幻灯片、热搜词等配置。"
    ></pages-header>
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
          <el-form-item label="是否显示：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" v-db-click @click="groupAdd('添加数据')" class="action-row mr20">添加数据</el-button>
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column :label="item.title" :min-width="item.minWidth" v-for="(item, index) in columns1" :key="index">
          <template #default="scope">
            <template v-if="item.key">
              <div v-if="item.type !== 'img'">
                <span>{{ scope.row[item.key] }}</span>
              </div>
              <div v-else>
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row[item.key][0]" />
                </div>
              </div>
            </template>
            <template v-else-if="item.slot === 'status'">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="scope.row.status"
                :value="scope.row.status"
                @change="onchangeIsShow(scope.row)"
                size="large"
              >
              </el-switch>
            </template>
            <template v-else-if="item.slot === 'action'">
              <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
            </template>
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import editFrom from '@/components/from/from';
import {
  groupDataListApi,
  groupDataAddApi,
  groupDataEditApi,
  groupDataHeaderApi,
  groupDataSetApi,
  groupAllApi,
} from '@/api/system';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'list' });

const route = useRoute();
const router = useRouter();
const mediaStore = useMediaStore();

const pageHeader = ref(null);
const table = ref(null);
const formValidateRef = ref(null);

const treeId = ref('');
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const formValidate = reactive({
  status: '',
  page: 1,
  limit: 15,
  gid: 0,
});
const total = ref(0);
const tabList = ref([]);
const columns1 = ref([]);
const FromData = ref(null);
const loading = ref(false);
const titleType = ref('group');
const groupAll = ref([]);
const theme3 = ref('light');
const labelSort = ref([]);
const sortName = ref(null);
const current = ref(0);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

watch(route, (to, from) => {
  if (route.params.id) {
    getList();
    getListHeader();
  } else {
    getGroupAll();
  }
});

function bindMenuItem(name, index) {
  current.value = index;
  formValidate.gid = name.id;
  getListHeader();
  getList();
}
function getGroupAll() {
  groupAllApi()
    .then(async (res) => {
      groupAll.value = res.data;
      sortName.value = res.data[0].id;
      formValidate.gid = res.data[0].id;
      getListHeader();
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 返回
function back() {
  router.push({ path: Setting.routePre + '/system/config/system_group/index' });
}
function getUrl(type) {
  let url = 'setting/group_data' + type;
  if (route.params.id) {
    let arr = {
      setting_groupDataSign: 'setting/sign_data' + type,
      setting_groupDataOrder: 'setting/order_data' + type,
      setting_groupDataUser: 'setting/usermenu_data' + type,
      setting_groupDataPoster: 'setting/poster_data' + type,
      marketing_storeSeckillData: 'setting/seckill_data' + type,
    };
    if (arr[route.name] === undefined) return url;
    return arr[route.name];
  } else {
    return url;
  }
}
// 列表
function getList() {
  loading.value = true;
  formValidate.gid = route.params.id ? route.params.id : formValidate.gid;
  formValidate.status = formValidate.status || '';
  groupDataListApi(formValidate, getUrl(''))
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
// 表格头部
function getListHeader() {
  loading.value = true;
  let data = {
    gid: route.params.id ? route.params.id : formValidate.gid,
  };
  groupDataHeaderApi(data, getUrl('/header'))
    .then(async (res) => {
      let data = res.data;
      let header = data.header;
      let index = [];
      header.forEach(function (item, i) {
        if (item.type === 'img') {
          index.push(i);
        }
      });
      index.forEach(function (item) {
        header[item].render = (h, params) => {
          let arr = params.row[header[item].key];
          let newArr = [];
          if (arr !== undefined && arr.length) {
            arr.forEach(function (e, i) {
              newArr.push(
                h(
                  'div',
                  {
                    style: {
                      width: '36px',
                      height: '36px',
                      'border-radius': '4px',
                      cursor: 'pointer',
                      display: 'inline-block',
                    },
                  },
                  [
                    h('img', {
                      attrs: {
                        src: params.row[header[item].key][i],
                      },
                      style: {
                        width: '100%',
                        height: '100%',
                      },
                    }),
                  ],
                ),
              );
            });
          }
          return h('viewer', newArr);
        };
      });
      columns1.value = header;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
// 添加表单
function groupAdd() {
  modalForm(groupDataAddApi({ gid: route.params.id ? route.params.id : formValidate.gid }, getUrl('/create'))).then(
    () => getList(),
  );
}
// 修改是否显示
function onchangeIsShow(row) {
  groupDataSetApi(getUrl(`/set_status/${row.id}/${row.status}`))
    .then(async (res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {
  let data = {
    gid: row.gid,
  };
  modalForm(groupDataEditApi(data, getUrl(`/${row.id}/edit`))).then(() => getList());
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: getUrl(`/${row.id}`),
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

onMounted(() => {
  if (route.params.id) {
    getList();
    getListHeader();
  } else {
    getGroupAll();
  }
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
  z-index: 50;
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
