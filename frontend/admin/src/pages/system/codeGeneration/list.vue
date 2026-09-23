<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" v-loading="spinShow">
      <el-button type="primary" v-db-click @click="groupAdd()" class="action-row mr20">添加功能</el-button>
      <!-- <el-button type="success" v-db-click @click="buildCode()" class="mr20">重新发布</el-button> -->
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="菜单名" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="表名" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.table_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="表备注" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.table_comment }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row, '编辑')">查看代码</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="editItem(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="downLoad(scope.row)">下载</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
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
    <el-drawer
      v-model="modals"
      :custom-class="className"
      title="Create"
      size="80%"
      :wrapperClosable="false"
      :styles="styles"
      @closed="editModalChange"
    >
      <template #header
        ><p class="diy-header" ref="diyHeader">
          <span>{{ title }}</span>
        </p></template
      >
      <div class="file" style="height: 100%">
        <el-button class="save" type="primary" v-db-click @click="pwdModal = true">保存</el-button>

        <div class="file-box">
          <div class="file-fix"></div>
          <div class="file-content">
            <!-- <el-tabs
              type="card"
              v-model="indexEditor"
              style="height: 100%"
              @on-click="toggleEditor"
              :animated="false"
              closable
              @on-tab-remove="handleTabRemove"
            >
              <el-tab-pane
                v-for="value in editorIndex"
                :key="value.index"
                :name="value.index.toString()"
                :label="value.title"
                :icon="value.icon"
              >
                <div
                  ref="container"
                  :id="'container_' + value.index"
                  style="height: 100%; min-height: calc(100vh - 110px)"
                ></div>
              </el-tab-pane>
            </el-tabs> -->
            <el-tabs v-model="indexEditor" type="card" @tab-change="toggleEditor">
              <el-tab-pane v-for="value in editorIndex" :key="value.index">
                <template #label
                  ><span>
                    <el-tooltip effect="light" class="item" :content="value.title" placement="top">
                      <span>{{ value.file_name }}</span>
                    </el-tooltip>
                  </span></template
                >
                <div
                  ref="container"
                  :id="'container_' + value.index"
                  style="height: 100%; min-height: calc(100vh - 110px)"
                ></div>
              </el-tab-pane>
              <!-- <el-tab-pane label="用户管理" name="first">用户管理</el-tab-pane>
              <el-tab-pane label="配置管理" name="second">配置管理</el-tab-pane>
              <el-tab-pane label="角色管理" name="third">角色管理</el-tab-pane>
              <el-tab-pane label="定时任务补偿" name="fourth">定时任务补偿</el-tab-pane> -->
            </el-tabs>
          </div>
        </div>
      </div>
    </el-drawer>
    <el-dialog
      v-model="buildModals"
      title="终端"
      :show-close="true"
      :close-on-click-modal="false"
      width="720px"
      @close="editModalChange"
    >
      <el-alert type="warning" title="当前终端未运行于安装服务下，部分命令可能无法执行."></el-alert>
      <div>
        <div v-for="(item, index) in codeBuildList" :key="index">{{ item }}</div>
      </div>
    </el-dialog>
    <el-dialog v-model="pwdModal" width="470px" title="文件管理密码" :show-close="true" :close-on-click-modal="false">
      <el-input v-model="pwd" type="password" placeholder="请输入文件管理密码"></el-input>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="pwdModal = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="crudSaveFileLocal">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { crudList, crudDet, crudDownload, crudSaveFile as crudSaveFileApi } from '@/api/systemCodeGeneration';
import * as monaco from '@/libs/monaco';
import { getCookies, removeCookies } from '@/libs/util';
import Setting from '@/setting';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

const router = useRouter();
const mediaStore = useMediaStore();

const table = ref(null);
const diyHeader = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const formValidate = reactive({
  page: 1,
  limit: 15,
  title: '',
});
const styles = {
  height: 'calc(100% - 55px)',
  overflow: 'auto',
  paddingBottom: '53px',
  position: 'static',
};
const loading = ref(false);
const pwdModal = ref(false);
const buildModals = ref(false);
const pwd = ref('');
const tabList = ref([]);
const codeBuildList = ref([]);
const total = ref(0);
const columns1 = [
  {
    title: 'ID',
    key: 'id',
    width: 80,
  },
  {
    title: '菜单名',
    key: 'name',
    minWidth: 130,
  },
  {
    title: '表名',
    key: 'table_name',
    minWidth: 130,
  },
  {
    title: '字符集',
    key: 'table_collation',
    minWidth: 130,
  },
  {
    title: '表备注',
    key: 'table_comment',
    minWidth: 130,
  },
  {
    title: '添加时间',
    key: 'add_time',
    minWidth: 130,
  },
  {
    title: '操作',
    slot: 'action',
    fixed: 'right',
    minWidth: 150,
  },
];
const FromData = ref(null);
const titleFrom = ref('');
const groupId = ref(0);
const addId = ref('');
const editorList = ref([]); //编辑器数组
const indexEditor = ref(0); //当前编辑器索引
const code = ref(''); //当前文件打开时的内容
const contextData = ref(null); //左侧导航右键点击是产生的数据对象

const fileType = ref(''); // 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件
const className = ref(''); //全屏 class名
const spinShow = ref(false);
const modals = ref(false); //编辑器开关
const editor = ref(''); //当前编辑器对象
const editorIndex = ref([]);
const title = ref('');
const editId = ref(0);
let source = null;
let isShowLogn = false;
let isShowList = false;
let content = null;

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

function crudSaveFileLocal() {
  let data = {
    filepath: editorIndex.value[indexEditor.value].pathname,
    comment: editorList.value[indexEditor.value].editor.getValue(),
    pwd: pwd.value,
  };
  crudSaveFileApi(editId.value, data)
    .then((res) => {
      pwd.value = '';
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function downLoad(row) {
  crudDownload(row.id).then((res) => {
    window.open(res.data.download_url, '_blank');
  });
}
function buildCode() {
  buildModals.value = true;
  if (typeof EventSource !== 'undefined') {
    //支持eventSource
    var postURL = Setting.apiBaseURL + '/system/crud/npm?token=' + getCookies('token');
    source = new EventSource(postURL);
    source.onopen = function (res) {};
    source.onmessage = function (data) {};
    source.onerror = function (err) {
      //链接失败后EventSource会每隔三秒左右重新发起链接
    };
  } else {
    console.log('暂不支持EventSource');
  }
}
// 跳转到组合数据列表页面
function goList(row) {
  router.push({
    path: Setting.routePre + '/system/config/system_group/list/' + row.id,
  });
}
// 列表
function getList() {
  loading.value = true;
  crudList(formValidate)
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
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
// 点击添加
function groupAdd() {
  router.push({
    name: 'system_code_generation',
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `system/crud/${row.id}`,
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
// 编辑
function edit(row) {
  spinShow.value = true;
  // 创建代码容器
  title.value = row.name;
  nextTick((e) => {
    openfile(row.id, false);
  });
}
function editItem(row) {
  router.push({
    name: 'system_code_generation',
    query: {
      id: row.id,
    },
  });
}
//打开文件
function openfile(id) {
  try {
    editId.value = id;
    editorIndex.value = [];
    editorList.value = [];
    crudDet(id)
      .then(async (res) => {
        let data = res.data.file[0];
        res.data.file.map((i, index) => {
          let data = i;
          editorIndex.value.push({
            tab: true,
            index: index + '',
            title: data.name,
            file_name: data.file_name,
            pathname: data.path,
          });
          code.value = data.content;
          initEditor(index, data.content);
          nextTick((e) => {
            // 保存相对信息
            editorList.value[index].path = data.path;
            editorList.value[index].oldCode = content;
            editorIndex.value[index].title = data.name;
            editorIndex.value[index].file_name = data.file_name;
          });
        });
        modals.value = true;
        spinShow.value = false;
      })
      .catch((res) => {
        catchFun(res);
      });
  } catch (error) {
    console.log(error);
  }
}
/**
 * 窗口最大化
 */
function winChanges() {
  if (className.value) {
    className.value = '';
  } else {
    className.value = 'diy-fullscreen';
  }
}
/**
 * 初始化编辑器
 */
function initEditor(index, conetnt) {
  try {
    nextTick(() => {
      // 初始化编辑器，确保dom已经渲染
      editor.value = monaco.editor.create(document.getElementById('container_' + index), {
        value: conetnt, //编辑器初始显示文字
        language: 'sql', //语言支持自行查阅demo
        automaticLayout: true, //自动布局
        theme: 'vs', //官方自带三种主题vs, hc-black, or vs-dark
        foldingStrategy: 'indentation', // 代码可分小段折叠
        overviewRulerBorder: false, // 不要滚动条的边框
        scrollbar: {
          // 滚动条设置
          verticalScrollbarSize: 4, // 竖滚动条
          horizontalScrollbarSize: 10, // 横滚动条
        },
        autoIndent: true, // 自动布局
        tabSize: 4, // tab缩进长度
        autoClosingOvertype: 'always',
        readOnly: false,
      });
      editorList.value.push({
        editor: editor.value,
        oldCode: code.value,
        path: '',
        index: index,
      });
    });
  } catch (error) {
    console.log(error);
  }
}

/**
 * 处理接口回调
 * @param {Object} res
 */
function catchFun(res) {
  if (res.status) {
    if (res.status == 400) ElMessage.error(res.msg);
    if (res.status == 110008) {
      // ElMessage.error(res.msg);
      isShowLogn = true;
      isShowList = false;
      loading.value = false;
    }
  } else {
    // ElMessage.error('文件编码不被兼容，无法正确读取文件!');
  }
  //关闭蒙版层
  if (spinShow.value) spinShow.value = false;
  // 关闭文件列表展示
  if (loading.value) loading.value = false;
}
//编辑器状态变化
function editModalChange() {
  editorList.value.forEach(function (value, index) {
    // 销毁当前编辑器
    editorList.value[index].editor.dispose();
    editorList.value[index].editor = null;
  });
  // 初始话数据
  modals.value = false; //编辑器开关
  editor.value = ''; //当前编辑器对象
  editorIndex.value = [
    //选项卡数组
    {
      tab: true,
      index: '0',
      title: '',
      icon: '',
    },
  ];
  editorList.value = []; //编辑器数组
  indexEditor.value = '0'; //当前编辑器索引
  code.value = ''; //当前文件打开时的内容
  contextData.value = null; //左侧导航右键点击是产生的数据对象
}
/**
 * 切换选项卡
 * @param {Object} index
 */
function toggleEditor(index) {
  index = Number(index);
  code.value = editorList.value[index].oldCode; //设置文件打开时的代码
  editor.value = editorList.value[index].editor; //设置编辑器实例
}
function handleTabRemove(index) {
  // 关闭选项卡
  editorIndex.value[index].tab = false; // 关闭选项卡
}

onMounted(() => {
  getList();
});

onBeforeUnmount(() => {
  if (source) {
    source.close(); //关闭EventSource
  }
});
</script>

<style lang="scss" scoped>
// 自定义方法缩小
:deep(.diy-fullscreen) {
  overflow: hidden;

  .ivu-modal {
    top: 0px;
    left: 0px;
    right: 0px;
    bottom: 0px;
    height: 100%;
    width: 100% !important;

    .ivu-modal-content {
      height: 100%;

      .ivu-modal-body {
        height: 100%;
      }
    }

    .ivu-tabs {
      .ivu-tabs-content-animated {
        height: 92%;
        background-color: #2f2f2f !important;
      }
    }

    .ivu-tabs-content {
      height: 100%;
    }

    .ivu-tabs {
      .ivu-tabs-tabpane {
        height: 92%;
      }
    }
  }
}
.diy-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  .diy-header-icon {
    margin-right: 30px;
    cursor: pointer;
  }

  .diy-header-icon:hover {
    opacity: 0.8;
  }
}
:deep(.ivu-modal) {
  top: 70px;
}

.ivu-modal-content {
  .ivu-modal-body {
    min-height: 632px;
    height: 80vh;
    overflow: hidden;
  }
}

.ivu-tabs {
  .ivu-tabs-content-animated {
    min-height: 560px;
    height: 73vh;
    margin-top: -1px;
  }

  .ivu-tabs-tabpane {
    min-height: 560px;
    margin-top: -1px;
  }
}

.ivu-tabs-nav .ivu-tabs-tab .ivu-icon {
  color: #f00;
}

:deep(body .ivu-select-dropdown .ivu-dropdown-transfer) {
  background: red !important;
}

// 导航栏右键样式 无效
.file-left :deep(.ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover) {
  background-color: #e5e5e5 !important;
}

// 选项卡头部
:deep(.ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-nav-container) {
  background-color: #fff;
}
.demo-drawer-footer {
  width: 100%;
  position: absolute;
  bottom: 0;
  left: 0;
  border-top: 1px solid #e8e8e8;
  padding: 10px 16px;
  text-align: right;
  background: #fff;
}
.file {
  position: relative;
  .save {
    position: absolute;
    left: 50%;
    bottom: -10px;
    z-index: 99;
  }
}
.file-box {
  height: 100%;
}
</style>
