<template>
  <div>
    <el-card :bordered="false" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs>
        <el-tab-pane label="数据库列表">
          <!--          <el-card :bordered="false" shadow="never" class="tableBox">-->
          <div class="mb10">
            <!--              <span class="ivu-pl-8 mr10">数据库表列表</span>-->
            <el-button v-db-click @click="getBackup">备份</el-button>
            <el-button v-db-click @click="getOptimize">优化表</el-button>
            <el-button v-db-click @click="getRepair">修复表</el-button>
            <el-button v-db-click @click="exportData(1)">导出文件</el-button>
          </div>
          <el-table
            ref="selection"
            :data="tabList2"
            v-loading="loading"
            empty-text="暂无数据"
            @select="onSelectTab"
            @select-all="onSelectTab"
            class="mt14"
          >
            <el-table-column type="selection" width="55"> </el-table-column>
            <el-table-column label="表名称" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="备注" min-width="100">
              <template #default="scope">
                <div class="mark">
                  <div v-if="scope.row.is_edit" class="table-mark" v-db-click @click="isEditMark(scope.row)">
                    {{ scope.row.comment }}
                  </div>
                  <el-input
                    ref="markRef"
                    v-else
                    v-model="scope.row.comment"
                    @blur="isEditBlur(scope.row, 0)"
                  ></el-input>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="类型" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.engine }}</span>
              </template>
            </el-table-column>
            <el-table-column label="大小" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.data_length }}</span>
              </template>
            </el-table-column>
            <el-table-column label="更新时间" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.update_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="行数" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.rows }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="70">
              <template #default="scope">
                <a v-db-click @click="Info(scope.row)">详情</a>
              </template>
            </el-table-column>
          </el-table>
          <!--          </el-card>-->
          <!-- 详情模态框-->
          <el-drawer
            v-model="modals"
            :wrapperClosable="false"
            :size="1000"
            :title="'[ ' + rows.name + ' ]' + rows.comment"
          >
            <!-- 详情为纯表格，内边距收窄对齐 header 的 15px；滚动交给抽屉 body（全局 overflow:auto），滚动条贴边 -->
            <el-table ref="selection" :data="tabList3" v-loading="loading2" empty-text="暂无数据">
              <el-table-column label="字段名" min-width="100">
                <template #default="scope">
                  <span>{{ scope.row.COLUMN_NAME }}</span>
                </template>
              </el-table-column>
              <el-table-column label="数据类型" min-width="100">
                <template #default="scope">
                  <span>{{ scope.row.COLUMN_TYPE }}</span>
                </template>
              </el-table-column>
              <el-table-column label="默认值" min-width="100">
                <template #default="scope">
                  <span>{{ scope.row.COLUMN_DEFAULT }}</span>
                </template>
              </el-table-column>
              <el-table-column label="允许非空" min-width="100">
                <template #default="scope">
                  <span>{{ scope.row.IS_NULLABLE }}</span>
                </template>
              </el-table-column>
              <el-table-column label="自动递增" min-width="100">
                <template #default="scope">
                  <span>{{ scope.row.EXTRA }}</span>
                </template>
              </el-table-column>
              <el-table-column label="备注" min-width="100">
                <template #default="scope">
                  <div class="mark">
                    <div v-if="scope.row.is_edit" class="table-mark" v-db-click @click="isEditMark(scope.row)">
                      {{ scope.row.COLUMN_COMMENT }}
                    </div>
                    <el-input
                      ref="markRef"
                      v-else
                      v-model="scope.row.COLUMN_COMMENT"
                      @blur="isEditBlur(scope.row, 1)"
                    ></el-input>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-drawer>
        </el-tab-pane>
        <el-tab-pane label="备份列表">
          <el-table ref="selection" :data="tabList" v-loading="loading3" empty-text="暂无数据" highlight-current-row>
            <el-table-column label="备份名称" min-width="200">
              <template #default="scope">
                <span>{{ scope.row.filename }}</span>
              </template>
            </el-table-column>
            <el-table-column label="part" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.part }}</span>
              </template>
            </el-table-column>
            <el-table-column label="大小" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.size }}</span>
              </template>
            </el-table-column>
            <el-table-column label="compress" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.compress }}</span>
              </template>
            </el-table-column>
            <el-table-column label="时间" min-width="100">
              <template #default="scope">
                <span>{{ scope.row.backtime }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="140">
              <template #default="scope">
                <a v-db-click @click="ImportFile(scope.row)">导入</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除该备份', scope.$index)">删除</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="download(scope.row)">下载</a>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>
      </el-tabs>
    </el-card>
    <el-dialog v-model="markModal" width="470px" title="修改备注" @closed="cancel">
      <el-input v-model="mark"></el-input>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="ok">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import {
  backupListApi,
  backupReadListApi,
  backupBackupApi,
  backupOptimizeApi,
  backupRepairApi,
  filesListApi,
  filesDownloadApi,
  filesImportApi,
  updateMark,
} from '@/api/system';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'systemDatabackup' });

const selection = ref(null);
const markRef = ref(null);

const modals = ref(false);
const loading = ref(false);
const tabList = ref([]);
const tabList2 = ref([]);
const selectionList = ref([]);
const tabList3 = ref([]);
const rows = ref({});
const dataList = ref({});
const loading2 = ref(false);
const loading3 = ref(false);
const markModal = ref(false);
const mark = ref('');
const header = ref({});
const Token = ref('');
const changeMarkData = reactive({
  table: '',
  mark: '',
  type: '',
  field: '',
});

const fileUrl = computed(() => {
  const search = '/adminapi/';
  const start = Setting.apiBaseURL.indexOf(search);
  return Setting.apiBaseURL.substring(0, start); // 截取字符串
});

function editMark(row, type) {
  changeMarkData.table = row.name || row.TABLE_NAME;
  changeMarkData.field = row.COLUMN_NAME || '';
  changeMarkData.type = row.COLUMN_TYPE || '';
  changeMarkData.is_field = type;
  markModal.value = true;
}
function ok() {
  changeMarkData.mark = mark.value;
  updateMark(changeMarkData).then((res) => {
    ElMessage.success(res.msg);
    if (changeMarkData.is_field) {
      Info({ name: changeMarkData.table, comment: rows.value.comment });
    } else {
      getList();
    }
  });
}
function cancel() {
  mark.value = '';
}
// 导入
function ImportFile(row) {
  filesImportApi({
    part: row.part,
    time: row.time,
  })
    .then(async (res) => {
      ElMessage.success(res.msg);
      getfileList();
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 删除备份记录表
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `system/backup/del_file`,
    method: 'DELETE',
    ids: {
      filename: row.time,
    },
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
// 上传头部token
function getToken() {
  Token.value = getCookies('token');
}
function download(row) {
  let data = {
    time: row.time,
  };
  filesDownloadApi(data)
    .then((res) => {
      if (res.data.key) {
        window.open(Setting.apiBaseURL + '/download?key=' + res.data.key);
      }
    })
    .catch((res) => {
      ElMessage.error(res);
    });
}
// 导出备份记录表
// TODO(vue3): 原实现依赖 iview 表格的 exportCsv 与未定义的 this.columns，element-plus 下需重写导出逻辑
function exportData() {
  const columns = undefined.slice(1, 7);
  selection.value.exportCsv({
    filename: '导出',
    columns: columns,
    data: tabList2.value,
  });
}
// 全选
function onSelectTab(sel) {
  selectionList.value = sel;
  let tables = [];
  selectionList.value.map((item) => {
    tables.push(item.name);
  });
  dataList.value = {
    tables: tables.join(','),
  };
}
// 备份表
function getBackup() {
  if (selectionList.value.length === 0) {
    return ElMessage.warning('请选择表');
  }
  backupBackupApi(dataList.value)
    .then(async (res) => {
      ElMessage.success(res.msg);
      getfileList();
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 备份记录表列表
function getfileList() {
  loading3.value = true;
  filesListApi()
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      loading3.value = false;
    })
    .catch((res) => {
      loading3.value = false;
      ElMessage.error(res.msg);
    });
}
// 优化表
function getOptimize() {
  if (selectionList.value.length === 0) {
    return ElMessage.warning('请选择表');
  }
  backupOptimizeApi(dataList.value)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修复表
function getRepair() {
  if (selectionList.value.length === 0) {
    return ElMessage.warning('请选择表');
  }
  backupRepairApi(dataList.value)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 数据库列表
function getList() {
  loading.value = true;
  backupListApi()
    .then(async (res) => {
      let data = res.data;
      tabList2.value = data.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 详情
function Info(row) {
  rows.value = row;
  modals.value = true;
  loading2.value = true;
  let data = {
    tablename: row.name,
  };
  backupReadListApi(data)
    .then(async (res) => {
      let data = res.data;
      tabList3.value = data.list;
      loading2.value = false;
    })
    .catch((res) => {
      loading2.value = false;
      ElMessage.error(res.msg);
    });
}
function isEditMark(row) {
  row.is_edit = true;
  nextTick((e) => {
    markRef.value.focus();
  });
}
function isEditBlur(row, type) {
  row.is_edit = false;
  changeMarkData.table = row.name || row.TABLE_NAME;
  changeMarkData.field = row.COLUMN_NAME || '';
  changeMarkData.type = row.COLUMN_TYPE || '';
  changeMarkData.is_field = type;
  changeMarkData.mark = type ? row.COLUMN_COMMENT : row.comment;

  updateMark(changeMarkData)
    .then((res) => {
      // ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

getToken();
getList();
getfileList();
</script>

<style lang="scss" scoped>
/* 数据库详情抽屉为纯表格展示，覆盖全局 20px 35px 的大内边距 */
:deep(.el-drawer__body) {
  padding: 15px;
}
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}
.tableBox :deep(.ivu-table-header table) {
  border: none !important;
}
.table-mark {
  cursor: text;
}
.table-mark:hover {
  border: 1px solid #c2c2c2;
  padding: 3px 5px;
}
.mark :deep(.ivu-input) {
  background: #fff;
  border-radius: 0.39rem;
}
.mark :deep(.ivu-input),
.ivu-input:hover,
.ivu-input:focus {
  border: transparent;
  box-shadow: none;
}
</style>
