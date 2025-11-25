<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" v-loading="spinShow">
      <el-button type="primary" v-db-click @click="groupAdd()" class="mr20">添加功能</el-button>
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
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="菜单名" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="表名" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.table_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="表备注" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.table_comment }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template slot-scope="scope">
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
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <el-drawer
      :visible.sync="modals"
      :custom-class="className"
      title="Create"
      size="80%"
      :wrapperClosable="false"
      :styles="styles"
      @closed="editModalChange"
    >
      <p slot="header" class="diy-header" ref="diyHeader">
        <span>{{ title }}</span>
      </p>
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
            <el-tabs v-model="indexEditor" type="card" @tab-click="toggleEditor">
              <el-tab-pane v-for="value in editorIndex" :key="value.index">
                <span slot="label">
                  <el-tooltip effect="light" class="item" :content="value.title" placement="top">
                    <span>{{ value.file_name }}</span>
                  </el-tooltip>
                </span>
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
      :visible.sync="buildModals"
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
    <el-dialog
      :visible.sync="pwdModal"
      width="470px"
      title="文件管理密码"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <el-input v-model="pwd" type="password" placeholder="请输入文件管理密码"></el-input>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="pwdModal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="crudSaveFile">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { crudList, crudDet, crudDownload, crudSaveFile } from '@/api/systemCodeGeneration';
import * as monaco from 'monaco-editor';
import { getCookies, removeCookies } from '@/libs/util';
import Setting from '@/setting';
export default {
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        page: 1,
        limit: 20,
        title: '',
      },
      styles: {
        height: 'calc(100% - 55px)',
        overflow: 'auto',
        paddingBottom: '53px',
        position: 'static',
      },
      loading: false,
      pwdModal: false,
      buildModals: false,
      pwd: '',
      tabList: [],
      codeBuildList: [],
      total: 0,
      columns1: [
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
      ],
      FromData: null,
      titleFrom: '',
      groupId: 0,
      addId: '',
      editorList: [], //编辑器数组
      indexEditor: 0, //当前编辑器索引
      code: '', //当前文件打开时的内容
      contextData: null, //左侧导航右键点击是产生的数据对象

      fileType: '', // 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件
      className: '', //全屏 class名
      spinShow: false,
      modals: false, //编辑器开关
      editor: '', //当前编辑器对象
      editorIndex: [],
      title: '',
      editId: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getList();
  },
  beforeDestroy() {
    if (this.source) {
      this.source.close(); //关闭EventSource
    }
  },
  methods: {
    crudSaveFile() {
      let data = {
        filepath: this.editorIndex[this.indexEditor].pathname,
        comment: this.editorList[this.indexEditor].editor.getValue(),
        pwd: this.pwd,
      };
      crudSaveFile(this.editId, data)
        .then((res) => {
          this.pwd = '';
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    downLoad(row) {
      crudDownload(row.id).then((res) => {
        window.open(res.data.download_url, '_blank');
      });
    },
    buildCode() {
      this.buildModals = true;
      if (typeof EventSource !== 'undefined') {
        //支持eventSource
        var postURL = Setting.apiBaseURL + '/system/crud/npm?token=' + getCookies('token');
        this.source = new EventSource(postURL);
        let self = this; //因EventSource中this的指向变了，所以要提前存储一下
        this.source.onopen = function (res) {};
        this.source.onmessage = function (data) {};
        this.source.onerror = function (err) {
          //链接失败后EventSource会每隔三秒左右重新发起链接
        };
      } else {
        console.log('暂不支持EventSource');
      }
    },
    // 跳转到组合数据列表页面
    goList(row) {
      this.$router.push({
        path: this.$routeProStr + '/system/config/system_group/list/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      crudList(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 点击添加
    groupAdd() {
      this.$router.push({
        name: 'system_code_generation',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/crud/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tabList.splice(num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      this.spinShow = true;
      // 创建代码容器
      this.title = row.name;
      this.$nextTick((e) => {
        this.openfile(row.id, false);
      });
    },
    editItem(row) {
      this.$router.push({
        name: 'system_code_generation',
        query: {
          id: row.id,
        },
      });
    },
    //打开文件
    openfile(id) {
      try {
        this.editId = id;
        let that = this;
        this.editorIndex = [];
        this.editorList = [];
        crudDet(id)
          .then(async (res) => {
            let data = res.data.file[0];
            res.data.file.map((i, index) => {
              let data = i;
              this.editorIndex.push({
                tab: true,
                index: index + '',
                title: data.name,
                file_name: data.file_name,
                pathname: data.path,
              });
              that.code = data.content;
              this.initEditor(index, data.content);
              this.$nextTick((e) => {
                // 保存相对信息
                that.editorList[index].path = data.path;
                that.editorList[index].oldCode = that.content;
                that.editorIndex[index].title = data.name;
                that.editorIndex[index].file_name = data.file_name;
              });
            });
            that.modals = true;
            that.spinShow = false;
          })
          .catch((res) => {
            that.catchFun(res);
          });
      } catch (error) {
        console.log(error);
      }
    },
    /**
     * 窗口最大化
     */
    winChanges() {
      if (this.className) {
        this.className = '';
      } else {
        this.className = 'diy-fullscreen';
      }
    },
    /**
     * 初始化编辑器
     */
    initEditor(index, conetnt) {
      try {
        let that = this;
        that.$nextTick(() => {
          // 初始化编辑器，确保dom已经渲染
          that.editor = monaco.editor.create(document.getElementById('container_' + index), {
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
          that.editorList.push({
            editor: that.editor,
            oldCode: that.code,
            path: '',
            index: index,
          });
        });
      } catch (error) {
        console.log(error);
      }
    },

    /**
     * 处理接口回调
     * @param {Object} res
     */
    catchFun(res) {
      if (res.status) {
        if (res.status == 400) this.$message.error(res.msg);
        if (res.status == 110008) {
          // this.$message.error(res.msg);
          this.isShowLogn = true;
          this.isShowList = false;
          this.loading = false;
        }
      } else {
        // this.$message.error('文件编码不被兼容，无法正确读取文件!');
      }
      //关闭蒙版层
      if (this.spinShow) this.spinShow = false;
      // 关闭文件列表展示
      if (this.loading) this.loading = false;
    },
    //编辑器状态变化
    editModalChange() {
      let that = this;
      that.editorList.forEach(function (value, index) {
        // 销毁当前编辑器
        that.editorList[index].editor.dispose();
        that.editorList[index].editor = null;
      });
      // 初始话数据
      that.modals = false; //编辑器开关
      that.editor = ''; //当前编辑器对象
      that.editorIndex = [
        //选项卡数组
        {
          tab: true,
          index: '0',
          title: '',
          icon: '',
        },
      ];
      that.editorList = []; //编辑器数组
      that.indexEditor = '0'; //当前编辑器索引
      that.code = ''; //当前文件打开时的内容
      that.contextData = null; //左侧导航右键点击是产生的数据对象
    },
    /**
     * 切换选项卡
     * @param {Object} index
     */
    toggleEditor(index) {
      index = Number(index);
      this.code = this.editorList[index].oldCode; //设置文件打开时的代码
      this.editor = this.editorList[index].editor; //设置编辑器实例
    },
    handleTabRemove(index) {
      let that = this;
      // 关闭选项卡
      that.editorIndex[index].tab = false; // 关闭选项卡
    },
  },
};
</script>

<style lang="scss" scoped>
// 自定义方法缩小
::v-deep .diy-fullscreen {
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
::v-deep .ivu-modal {
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

::v-deepbody .ivu-select-dropdown .ivu-dropdown-transfer {
  background: red !important;
}

// 导航栏右键样式 无效
.file-left ::v-deep .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover {
  background-color: #e5e5e5 !important;
}

// 选项卡头部
::v-deep .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-nav-container {
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
