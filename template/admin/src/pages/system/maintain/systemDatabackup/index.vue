<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt listbox">
      <Tabs class="mb30">
        <TabPane label="数据库列表">
          <Card :bordered="false" dis-hover class="tableBox mt10">
            <div slot="title">
              <!--              <span class="ivu-pl-8 mr10">数据库表列表</span>-->
              <Button type="primary" class="mr10" @click="getBackup">备份</Button>
              <Button type="primary" class="mr10" @click="getOptimize">优化表</Button>
              <Button type="primary" class="mr10" @click="getRepair">修复表</Button>
              <Button type="primary" class="mr10" @click="exportData(1)">导出文件</Button>
            </div>
            <Table
              ref="selection"
              :columns="columns"
              :data="tabList2"
              :loading="loading"
              no-data-text="暂无数据"
              @on-selection-change="onSelectTab"
              size="small"
              no-filtered-data-text="暂无筛选结果"
            >
              <template slot-scope="{ row, index }" slot="comment">
                <div class="mark">
                  <div v-if="row.is_edit" class="table-mark" @click="isEditMark(row)">{{ row.comment }}</div>
                  <Input ref="mark" v-else v-model="row.comment" @on-blur="isEditBlur(row, 0)"></Input>
                </div>
              </template>
              <template slot-scope="{ row }" slot="action">
                <a @click="Info(row)">详情</a>
              </template>
            </Table>
          </Card>
          <!-- 详情模态框-->
          <Drawer :closable="false" width="740" v-model="modals" :title="'[ ' + rows.name + ' ]' + rows.comment">
            <Table
              ref="selection"
              :columns="columns2"
              :data="tabList3"
              :loading="loading2"
              no-data-text="暂无数据"
              max-height="600"
              size="small"
              no-filtered-data-text="暂无筛选结果"
            >
              <template slot-scope="{ row, index }" slot="COLUMN_COMMENT">
                <div class="mark">
                  <div v-if="row.is_edit" class="table-mark" @click="isEditMark(row)">{{ row.COLUMN_COMMENT }}</div>
                  <Input ref="mark" v-else v-model="row.COLUMN_COMMENT" @on-blur="isEditBlur(row, 1)"></Input>
                </div>
              </template>
            </Table>
          </Drawer>
        </TabPane>
        <TabPane label="备份列表">
          <Card :bordered="false" dis-hover class="">
            <Table
              ref="selection"
              :columns="columns4"
              :data="tabList"
              :loading="loading3"
              no-data-text="暂无数据"
              highlight-row
              size="small"
              no-filtered-data-text="暂无筛选结果"
            >
              <template slot-scope="{ row, index }" slot="action">
                <a @click="ImportFile(row)">导入</a>
                <Divider type="vertical" />
                <a @click="del(row, '删除该备份', index)">删除</a>
                <Divider type="vertical" />
                <a @click="download(row)">下载</a>
              </template>
            </Table>
          </Card>
        </TabPane>
      </Tabs>
    </Card>
    <Modal v-model="markModal" title="修改备注" @on-ok="ok" @on-cancel="cancel" @on-visible-change="cancel">
      <Input v-model="mark"></Input>
    </Modal>
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
      columns4: [
        {
          title: '备份名称',
          key: 'filename',
          minWidth: 200,
          sortable: true,
        },
        {
          title: 'part',
          key: 'part',
          minWidth: 100,
        },
        {
          title: '大小',
          key: 'size',
          minWidth: 150,
        },
        {
          title: 'compress',
          key: 'compress',
          minWidth: 100,
        },
        {
          title: '时间',
          key: 'backtime',
          minWidth: 150,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 80,
        },
      ],
      tabList2: [],
      columns: [
        {
          type: 'selection',
          width: 60,
          align: 'center',
        },
        {
          title: '表名称',
          key: 'name',
          minWidth: 200,
          sortable: true,
        },
        {
          title: '备注',
          slot: 'comment',
          minWidth: 200,
        },
        {
          title: '类型',
          key: 'engine',
          minWidth: 130,
          sortable: true,
        },
        {
          title: '大小',
          key: 'data_length',
          minWidth: 130,
          sortable: true,
        },
        {
          title: '更新时间',
          key: 'update_time',
          minWidth: 150,
          sortable: true,
        },
        {
          title: '行数',
          key: 'rows',
          minWidth: 100,
          sortable: true,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 80,
        },
      ],
      selectionList: [],
      tabList3: [],
      columns2: [
        {
          title: '字段名',
          key: 'COLUMN_NAME',
        },
        {
          title: '数据类型',
          key: 'COLUMN_TYPE',
        },
        {
          title: '默认值',
          key: 'COLUMN_DEFAULT',
        },
        {
          title: '允许非空',
          key: 'IS_NULLABLE',
        },
        {
          title: '自动递增',
          key: 'EXTRA',
        },
        {
          title: '备注',
          slot: 'COLUMN_COMMENT',
        },
      ],
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
        this.$Message.success(res.msg);
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
          this.$Message.success(res.msg);
          this.getfileList();
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
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
          this.$Message.success(res.msg);
          this.tabList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
          this.$Message.error(res);
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
        return this.$Message.warning('请选择表');
      }
      backupBackupApi(this.dataList)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.getfileList();
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
        });
    },
    // 优化表
    getOptimize() {
      if (this.selectionList.length === 0) {
        return this.$Message.warning('请选择表');
      }
      backupOptimizeApi(this.dataList)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修复表
    getRepair() {
      if (this.selectionList.length === 0) {
        return this.$Message.warning('请选择表');
      }
      backupRepairApi(this.dataList)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
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
          // this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.tableBox >>> .ivu-table-header table
   border none !important

.table-mark{
  cursor: text;
}
.table-mark:hover{
  border:1px solid #c2c2c2;
  padding: 3px 5px
}
.mark /deep/ .ivu-input{
    background: #fff;
    border-radius: .39rem;
}
.mark /deep/ .ivu-input, .ivu-input:hover, .ivu-input:focus {
    border: transparent;
    box-shadow: none;
}
</style>
