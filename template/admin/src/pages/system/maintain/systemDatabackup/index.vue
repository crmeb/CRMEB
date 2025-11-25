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
              <template slot-scope="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="备注" min-width="100">
              <template slot-scope="scope">
                <div class="mark">
                  <div v-if="scope.row.is_edit" class="table-mark" v-db-click @click="isEditMark(scope.row)">
                    {{ scope.row.comment }}
                  </div>
                  <el-input ref="mark" v-else v-model="scope.row.comment" @blur="isEditBlur(scope.row, 0)"></el-input>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="类型" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.engine }}</span>
              </template>
            </el-table-column>
            <el-table-column label="大小" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.data_length }}</span>
              </template>
            </el-table-column>
            <el-table-column label="更新时间" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.update_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="行数" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.rows }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="70">
              <template slot-scope="scope">
                <a v-db-click @click="Info(scope.row)">详情</a>
              </template>
            </el-table-column>
          </el-table>
          <!--          </el-card>-->
          <!-- 详情模态框-->
          <el-drawer
            :visible.sync="modals"
            :wrapperClosable="false"
            :size="740"
            :title="'[ ' + rows.name + ' ]' + rows.comment"
          >
            <el-table
              ref="selection"
              :data="tabList3"
              v-loading="loading2"
              empty-text="暂无数据"
              max-height="600"
              size="small"
            >
              <el-table-column label="字段名" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.COLUMN_NAME }}</span>
                </template>
              </el-table-column>
              <el-table-column label="数据类型" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.COLUMN_TYPE }}</span>
                </template>
              </el-table-column>
              <el-table-column label="默认值" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.COLUMN_DEFAULT }}</span>
                </template>
              </el-table-column>
              <el-table-column label="允许非空" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.IS_NULLABLE }}</span>
                </template>
              </el-table-column>
              <el-table-column label="自动递增" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.EXTRA }}</span>
                </template>
              </el-table-column>
              <el-table-column label="备注" min-width="100">
                <template slot-scope="scope">
                  <div class="mark">
                    <div v-if="scope.row.is_edit" class="table-mark" v-db-click @click="isEditMark(scope.row)">
                      {{ scope.row.COLUMN_COMMENT }}
                    </div>
                    <el-input
                      ref="mark"
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
          <el-table
            ref="selection"
            :data="tabList"
            v-loading="loading3"
            empty-text="暂无数据"
            highlight-current-row
            size="small"
          >
            <el-table-column label="备份名称" min-width="200">
              <template slot-scope="scope">
                <span>{{ scope.row.filename }}</span>
              </template>
            </el-table-column>
            <el-table-column label="part" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.part }}</span>
              </template>
            </el-table-column>
            <el-table-column label="大小" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.size }}</span>
              </template>
            </el-table-column>
            <el-table-column label="compress" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.compress }}</span>
              </template>
            </el-table-column>
            <el-table-column label="时间" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.backtime }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="140">
              <template slot-scope="scope">
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
    <el-dialog :visible.sync="markModal" width="470px" title="修改备注" @closed="cancel">
      <el-input v-model="mark"></el-input>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="cancel">取 消</el-button>
        <el-button type="primary" v-db-click @click="ok">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
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

export default {
  name: 'systemDatabackup',
  data() {
    return {
      modals: false,
      loading: false,
      tabList: [],
      tabList2: [],
      selectionList: [],
      tabList3: [],
      rows: {},
      dataList: {},
      loading2: false,
      loading3: false,
      markModal: false,
      mark: '',
      header: {},
      Token: '',
      changeMarkData: {
        table: '',
        mark: '',
        type: '',
        field: '',
      },
    };
  },
  computed: {
    fileUrl() {
      const search = '/adminapi/';
      const start = Setting.apiBaseURL.indexOf(search);
      return Setting.apiBaseURL.substring(0, start); // 截取字符串
    },
  },
  created() {
    this.getToken();
    this.getList();
    this.getfileList();
  },
  methods: {
    editMark(row, type) {
      this.changeMarkData.table = row.name || row.TABLE_NAME;
      this.changeMarkData.field = row.COLUMN_NAME || '';
      this.changeMarkData.type = row.COLUMN_TYPE || '';
      this.changeMarkData.is_field = type;
      this.markModal = true;
    },
    ok() {
      this.changeMarkData.mark = this.mark;
      updateMark(this.changeMarkData).then((res) => {
        this.$message.success(res.msg);
        if (this.changeMarkData.is_field) {
          this.Info({ name: this.changeMarkData.table, comment: this.rows.comment });
        } else {
          this.getList();
        }
      });
    },
    cancel() {
      this.mark = '';
    },
    // 导入
    ImportFile(row) {
      filesImportApi({
        part: row.part,
        time: row.time,
      })
        .then(async (res) => {
          this.$message.success(res.msg);
          this.getfileList();
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 删除备份记录表
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/backup/del_file`,
        method: 'DELETE',
        ids: {
          filename: row.time,
        },
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tabList.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 上传头部token
    getToken() {
      this.Token = getCookies('token');
    },
    download(row) {
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
          this.$message.error(res);
        });
    },
    // 导出备份记录表
    exportData() {
      const columns = this.columns.slice(1, 7);
      this.$refs.selection.exportCsv({
        filename: '导出',
        columns: columns,
        data: this.tabList2,
      });
    },
    // 全选
    onSelectTab(selection) {
      this.selectionList = selection;
      let tables = [];
      this.selectionList.map((item) => {
        tables.push(item.name);
      });
      this.dataList = {
        tables: tables.join(','),
      };
    },
    // 备份表
    getBackup() {
      if (this.selectionList.length === 0) {
        return this.$message.warning('请选择表');
      }
      backupBackupApi(this.dataList)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.getfileList();
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 备份记录表列表
    getfileList() {
      this.loading3 = true;
      filesListApi()
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.loading3 = false;
        })
        .catch((res) => {
          this.loading3 = false;
          this.$message.error(res.msg);
        });
    },
    // 优化表
    getOptimize() {
      if (this.selectionList.length === 0) {
        return this.$message.warning('请选择表');
      }
      backupOptimizeApi(this.dataList)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 修复表
    getRepair() {
      if (this.selectionList.length === 0) {
        return this.$message.warning('请选择表');
      }
      backupRepairApi(this.dataList)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 数据库列表
    getList() {
      this.loading = true;
      backupListApi()
        .then(async (res) => {
          let data = res.data;
          this.tabList2 = data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 详情
    Info(row) {
      this.rows = row;
      this.modals = true;
      this.loading2 = true;
      let data = {
        tablename: row.name,
      };
      backupReadListApi(data)
        .then(async (res) => {
          let data = res.data;
          this.tabList3 = data.list;
          this.loading2 = false;
        })
        .catch((res) => {
          this.loading2 = false;
          this.$message.error(res.msg);
        });
    },
    isEditMark(row) {
      row.is_edit = true;
      this.$nextTick((e) => {
        this.$refs.mark.focus();
      });
    },
    isEditBlur(row, type) {
      row.is_edit = false;
      this.changeMarkData.table = row.name || row.TABLE_NAME;
      this.changeMarkData.field = row.COLUMN_NAME || '';
      this.changeMarkData.type = row.COLUMN_TYPE || '';
      this.changeMarkData.is_field = type;
      this.changeMarkData.mark = type ? row.COLUMN_COMMENT : row.comment;

      updateMark(this.changeMarkData)
        .then((res) => {
          // this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.tableBox ::v-deep .ivu-table-header table {
  border: none !important;
}
.table-mark {
  cursor: text;
}
.table-mark:hover {
  border: 1px solid #c2c2c2;
  padding: 3px 5px;
}
.mark ::v-deep .ivu-input {
  background: #fff;
  border-radius: 0.39rem;
}
.mark ::v-deep .ivu-input,
.ivu-input:hover,
.ivu-input:focus {
  border: transparent;
  box-shadow: none;
}
</style>
