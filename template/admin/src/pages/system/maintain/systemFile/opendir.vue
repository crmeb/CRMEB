<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div v-if="isShowList" class="backs" @click="goBack(false)">
        <Icon type="ios-folder-outline" class="mr5 icon" /><span>返回上级</span>
      </div>
      <Table
        v-if="isShowList"
        ref="selection"
        :columns="columns4"
        :data="tabList"
        :loading="loading"
        no-data-text="暂无数据"
        highlight-row
        class="mt20"
        @on-current-change="currentChange"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="filename">
          <Icon type="ios-folder-outline" v-if="row.isDir" class="mr5" />
          <Icon type="ios-document-outline" v-else class="mr5" />
          <span>{{ row.filename }}</span>
        </template>
        <template slot-scope="{ row }" slot="isWritable">
          <span v-text="row.isWritable ? '是' : '否'"></span>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="open(row)" v-if="row.isDir">打开</a>
          <a @click="edit(row)" v-else>编辑</a>
        </template>
      </Table>
    </Card>
    <Modal
      :class-name="className"
      v-model="modals"
      scrollable
      footer-hide
      closable
      :mask-closable="false"
      width="80%"
      :before-close="editModalChange"
    >
      <p slot="header" class="diy-header" ref="diyHeader">
        <span>{{ title }}</span>
        <Icon
          @click="winChanges"
          class="diy-header-icon"
          :type="className ? 'ios-contract' : 'ios-qr-scanner'"
          size="20"
        />
      </p>
      <div style="height: 100%">
        <Button type="primary" id="savefile" class="diy-button" @click="savefile(indexEditor)">保存</Button>
        <Button id="refresh" class="diy-button" @click="refreshfile">刷新</Button>

        <div class="file-box">
          <div class="show-info">
            <div class="show-text" :title="navItem.pathname">目录: {{ navItem.pathname }}</div>
            <div class="diy-button-list">
              <Button class="diy-button" @click="goBack(true)">返回上一级</Button>
              <Button class="diy-button" @click="getList(true, true)">刷新</Button>
            </div>
          </div>
          <div class="file-left">
            <Tree
              class="diy-tree-render"
              :data="navList"
              :render="renderContent"
              :load-data="loadData"
              @on-contextmenu="handleContextMenu"
              expand-node
            >
              <template transfer slot="contextMenu">
                <DropdownItem v-if="contextData && contextData.isDir" @click.native="handleContextCreateFolder()"
                  >新建文件夹</DropdownItem
                >
                <DropdownItem v-if="contextData && contextData.isDir" @click.native="handleContextCreateFile()"
                  >新建文件</DropdownItem
                >
                <DropdownItem @click.native="handleContextRename()">重命名</DropdownItem>
                <DropdownItem @click.native="handleContextDelFolder()" style="color: #ed4014">删除</DropdownItem>
              </template>
            </Tree>
          </div>
          <div class="file-fix"></div>
          <div class="file-content">
            <Tabs
              type="card"
              v-model="indexEditor"
              style="height: 100%"
              @on-click="toggleEditor"
              :animated="false"
              closable
              @on-tab-remove="handleTabRemove"
            >
              <TabPane
                v-for="value in editorIndex"
                :key="value.index"
                :name="value.index.toString()"
                :label="value.title"
                :icon="value.icon"
                v-if="value.tab"
              >
                <div ref="container" :id="'container_' + value.index" style="height: 100%; min-height: 560px"></div>
              </TabPane>
            </Tabs>
          </div>
          <Spin size="large" fix v-if="spinShow"></Spin>
        </div>
      </div>
    </Modal>

    <div v-show="formShow" class="diy-from">
      <div class="diy-from-header">
        {{ formTitle
        }}<span :title="contextData ? contextData.pathname : ''">{{ contextData ? contextData.pathname : '' }}</span>
      </div>
      <Form ref="formInline" :model="formFile" :rules="ruleInline" inline>
        <FormItem prop="filename" class="diy-file">
          <Input type="text" class="diy-file" v-model="formFile.filename" placeholder="请输入名字">
            <Icon type="ios-folder-open-outline" slot="prepend"></Icon>
          </Input>
        </FormItem>
        <FormItem>
          <Button class="diy-button" @click="handleSubmit('formInline')">确定</Button>
        </FormItem>
        <FormItem>
          <Button class="diy-button" @click="formExit()">取消</Button>
        </FormItem>
        <div class="form-mask" v-show="formShow"></div>
      </Form>
    </div>
  </div>
</template>

<script>
import { resolveComponent } from 'vue';
import {
  opendirListApi,
  openfileApi,
  savefileApi,
  opendirLoginApi,
  createFolder,
  createFile,
  delFolder,
  rename,
} from '@/api/system';
import CodeMirror from 'codemirror/lib/codemirror';
import loginFrom from './components/loginFrom';
import { setCookies, getCookies, removeCookies } from '@/libs/util';
// import Fullscreen from '@/components/main/components/fullscreen';
import * as monaco from 'monaco-editor';
export default {
  name: 'opendir',
  data() {
    return {
      modals: false, //编辑器开关
      editor: '', //当前编辑器对象
      editorIndex: [
        //选项卡数组
        {
          tab: true,
          index: '0',
          title: '',
          icon: '',
        },
      ],
      editorList: [], //编辑器数组
      indexEditor: 0, //当前编辑器索引
      code: '', //当前文件打开时的内容
      navList: [], //左侧导航数据
      navItem: {}, //左侧导航点击是选中的数据
      contextData: null, //左侧导航右键点击是产生的数据对象

      fileType: '', // 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件
      className: '', //全屏 class名
      // fullscreen:false,  // 是否全屏
      isSave: true, //当前文件是否保存

      isShowLogn: false, // 登录
      isShowList: false, // 登录之后列表

      spinShow: false,
      loading: false,
      tabList: [],
      columns4: [
        {
          title: '文件/文件夹名',
          slot: 'filename',
          minWidth: 150,
          back: '返回上级',
        },
        {
          title: '文件/文件夹路径',
          key: 'real_path',
          minWidth: 150,
        },
        {
          title: '文件/文件夹大小',
          key: 'size',
          minWidth: 100,
        },
        {
          title: '是否可写',
          slot: 'isWritable',
          minWidth: 100,
        },
        {
          title: '更新时间',
          key: 'mtime',
          minWidth: 150,
        },
        {
          title: '操作',
          slot: 'action',
          minWidth: 150,
        },
      ],
      formItem: {
        //记录当前路径信息，获取文件列表时使用
        dir: '',
        superior: 0,
        filedir: '',
        fileToken: getCookies('file_token'),
      },
      dir: '', //当前完整文件路径
      // rows: {},  //
      pathname: '', // 当前文件路径
      title: '', //当前文件标题

      formFile: {
        //重命名表单
        filename: '',
      },
      ruleInline: {
        filename: [{ required: true, message: '请输入文件或文件夹的名字', trigger: 'blur' }],
      },
      formShow: false, //表单开关
      formTitle: '', //表单标题
      fileToken: getCookies('file_token'),
    };
  },

  components: {
    loginFrom,
  },
  mounted() {
    this.initEditor();
  },
  created() {
    this.getList();
  },
  beforeDestroy() {
    removeCookies('file_token');
  },
  computed: {},
  methods: {
    // 点击行
    currentChange(currentRow) {
      if (currentRow.isDir) {
        this.open(currentRow);
      } else {
        this.edit(currentRow);
      }
    },
    /**
     * 文件列表
     * @param {Object} refresh   // 是否重新加载 bool
     * @param {Object} is_edit   // 是否是编辑器中的刷新 bool
     */
    getList(refresh, is_edit) {
      let params;
      if (refresh) {
        params = {
          dir: '',
          superior: 0,
          filedir: '',
          fileToken: this.fileToken,
        };
      } else {
        params = this.formItem;
        params.fileToken = this.fileToken;
      }
      if (!is_edit) this.loading = true;
      opendirListApi(params)
        .then(async (res) => {
          let data = res.data;
          if (is_edit) {
            this.navList = data.navList;
          } else {
            this.navListForTab = data.navList;
            this.tabList = data.list;
            // this.navList = data.navList;
            this.isShowList = true;
          }
          this.dir = data.dir;
          this.isShowLogn = false;
          this.loading = false;
        })
        .catch((res) => {
          this.catchFun(res);
        });
    },
    //新建文件后重新加载左侧导航
    getListItem(data) {
      opendirListApi(data)
        .then(async (res) => {
          this.$set(this.contextData, 'children', res.data.navList);
        })
        .catch((res) => {
          this.catchFun(res);
        });
    },

    // 返回上级
    goBack(is_edit) {
      this.formItem = {
        dir: this.dir,
        superior: 1,
        filedir: '',
      };
      this.getList(false, is_edit);
    },
    // 打开
    open(row) {
      // this.rows = row;
      this.formItem = {
        dir: row.path,
        superior: 0,
        filedir: row.filename,
        fileToken: this.fileToken,
      };
      this.getList(false, false);
    },
    // 编辑
    edit(row) {
      this.navItem = row;
      this.spinShow = true;
      this.pathname = row.pathname;
      this.title = row.filename;
      this.editorIndex[0].title = row.filename;
      this.editorIndex[0].pathname = row.pathname;
      this.navList = this.navListForTab;
      this.dir = row.path;
      // 创建代码容器
      if (this.editorList.length <= 0) {
        this.initEditor();
      }
      this.openfile(row.pathname, false);
    },
    /**
     * 保存
     * @param {Object} index   // 当前索引
     * @param {Object} type    // true 不更新当前本地数据，false或者为空 更新当前数据
     */
    savefile(index, type) {
      let code = this.editorList[index].editor.getValue();
      let data = {
        comment: code,
        filepath: this.editorList[index].path,
        fileToken: this.fileToken,
      };
      let that = this;
      savefileApi(data)
        .then(async (res) => {
          if (!type) {
            that.code = code;
            that.isSave = true;
            that.editorIndex[index].icon = '';
            that.editorList[index].isSave = true;
          }
          that.$Message.success(res.msg);
          that.$Modal.remove();
        })
        .catch((res) => {
          that.catchFun(res);
        });
    },
    // 刷新
    refreshfile() {
      // 刷新编辑器
      if (this.editorList[this.indexEditor]) this.openfile(this.editorList[this.indexEditor].path, true);
    },
    //计算token过期时间
    getExpiresTime(expiresTime) {
      let nowTimeNum = Math.round(new Date() / 1000);
      let expiresTimeNum = expiresTime - nowTimeNum;
      return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
    },
    // 侧边栏异步加载
    loadData(item, callback) {
      if (item.isDir) {
        this.formItem = {
          dir: item.path,
          superior: 0,
          filedir: item.title,
          fileToken: this.fileToken,
        };
        opendirListApi(this.formItem)
          .then(async (res) => {
            callback(res.data.navList);
          })
          .catch((res) => {
            if (res.status == 110008) {
              this.$Message.error(res.msg);
              this.isShowLogn = true;
              this.isShowList = false;
              this.loading = false;
            } else {
              this.catchFun(res);
            }
          });
      }
    },
    // 自定义显示
    renderContent(h, { root, node, data }) {
      let that = this;
      return h(
        'span',
        {
          style: {
            display: 'inline-block',
            cursor: 'pointer',
            userSelect: 'null',
            color: '#cccccc',
            display: 'inline-block',
            width: '100%',
            borderRadis: '5px',
          },
          on: {
            click: () => {
              that.clickDir(data, root, node);
            },
            contextmenu: () => {
              // that.handleContextDelFolder(data,root,node);
            },
          },
        },
        [
          h('span', [
            h('Icon', {
              props: {
                type: data.isDir ? 'md-folder' : 'ios-document-outline',
              },
              style: {
                marginRight: '8px',
              },
            }),
            h(
              'span',
              {
                attrs: {
                  title: data.title,
                },
              },
              data.title,
            ),
          ]),
        ],
      );
    },
    /**
     * 侧边栏点击事件
     * @param {Object} data
     */
    clickDir(data, root, node) {
      let that = this;
      that.navItem = data;
      that.pathname = data.pathname;

      if (!data.isDir) {
        let i = that.editorIndex.findIndex((e) => {
          return e.pathname === data.pathname;
        });
        if (i > -1) {
          that.toggleEditor(i);
          that.indexEditor = i.toString();
        } else {
          let index = that.editorIndex.length;
          // 创建tabs
          that.editorIndex.push({
            tab: true,
            index: index.toString(),
            title: data.title,
            icon: '',
            pathname: data.pathname,
          });
          that.indexEditor = index.toString();
          // 创建代码容器
          that.initEditor();
          that.openfile(data.pathname, true);
        }
      }
    },
    //侧边栏右键点击事件
    handleContextMenu(data, event, position) {
      position.left = Number(position.left.slice(0, -2)) + 75 + 'px';
      this.contextData = data;
    },
    // 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件 renameFile|文件重命名
    //创建文件夹
    handleContextCreateFolder() {
      this.formFile.filename = '';
      this.formTitle = '创建文件夹';
      this.formShow = true;
      this.fileType = 'createFolder';
    },
    //创建文件
    handleContextCreateFile() {
      this.formFile.filename = '';
      this.formTitle = '创建文件';
      this.formShow = true;
      this.fileType = 'createFile';
    },
    //删除文件
    handleContextDelFolder() {
      let that = this;
      that.$Modal.confirm({
        title: '删除文件夹和文件',
        content: '您确定要删除改文件？',
        loading: true,
        onOk: () => {
          let data = {
            path: that.contextData.pathname,
            fileToken: this.fileToken,
          };
          delFolder(data)
            .then(async (res) => {
              that.loopDel(that.navList, that.contextData.nodeKey);
              that.$Modal.remove();
              that.$Message.success('删除成功');
            })
            .catch((res) => {
              that.catchFun(res);
            });
        },
        onCancel: () => {
          that.$Message.info('取消删除');
        },
      });
    },
    //重命名
    handleContextRename() {
      this.formFile.filename = this.contextData.title;
      this.formTitle = '重命名文件';
      this.formShow = true;
      this.fileType = 'renameFile';
    },
    //打开文件
    openfile(path, is_edit) {
      let that = this;
      let params = {
        filepath: path,
        fileToken: this.fileToken,
      };
      openfileApi(params)
        .then(async (res) => {
          let data = res.data;
          that.code = data.content;
          // 保存相对信息
          that.editorList[that.indexEditor].path = path;
          that.editorList[that.indexEditor].oldCode = that.code;
          //改变属性
          that.changeModel(data.mode, that.code);
          if (!is_edit) {
            that.modals = true;
            that.spinShow = false;
          }
        })
        .catch((res) => {
          that.catchFun(res);
        });
    },
    /**
     * 初始化编辑器
     */
    initEditor() {
      let that = this;
      that.$nextTick(() => {
        console.log(monaco);
        // 初始化编辑器，确保dom已经渲染
        that.editor = monaco.editor.create(document.getElementById('container_' + that.indexEditor), {
          value: that.code, //编辑器初始显示文字
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
        });
        //添加按键监听
        that.editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function () {
          that.savefile(that.indexEditor);
        });
        that.editor.onKeyUp(() => {
          // 当键盘按下，判断当前编辑器文本与已保存的编辑器文本是否一致
          if (that.editor.getValue() != that.code) {
            that.isSave = false;
            that.editorIndex[that.indexEditor].icon = 'md-warning';
            that.editorList[that.indexEditor].isSave = false;
          }
        });
        that.editorList.push({
          editor: that.editor,
          oldCode: that.code,
          path: '',
          isSave: true,
          index: that.indexEditor,
        });
      });
    },
    /**
     * 切换语言
     * @param {Object} mode
     */
    changeModel(mode, value) {
      var oldModel = this.editorList[this.indexEditor].editor.getModel(); //获取旧模型
      // var value = this.editor.getValue();//获取旧的文本
      //创建新模型，value为旧文本，id为modeId，即语言（language.id）
      //modesIds即为支持语言
      // var modesIds = monaco.languages.getLanguages().map(function(lang) { return lang.id; });
      if (!mode) mode = oldModel.getLanguageId();
      // if(!value) value = this.editor.getValue();

      var newModel = monaco.editor.createModel(value, mode);
      //将旧模型销毁
      if (oldModel) {
        oldModel.dispose();
      }
      //设置新模型
      this.editorList[this.indexEditor].editor.setModel(newModel);
    },
    // 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件
    handleSubmit(name) {
      let that = this;
      let data = '';
      let dataItem = '';
      this.$refs[name].validate((valid) => {
        if (valid) {
          switch (that.fileType) {
            case 'createFolder':
              data = {
                path: that.contextData.pathname,
                name: that.formFile.filename,
                fileToken: this.fileToken,
              };
              createFolder(data)
                .then(async (res) => {
                  dataItem = {
                    dir: that.contextData.path,
                    superior: 0,
                    filedir: that.contextData.title,
                    fileToken: this.fileToken,
                  };
                  that.getListItem(dataItem);
                  if (that.formShow) that.formShow = false;
                  that.$Message.success('创建成功');
                })
                .catch((res) => {
                  that.catchFun(res);
                });
              break;
            case 'createFile':
              data = {
                path: that.contextData.pathname,
                name: that.formFile.filename,
                fileToken: this.fileToken,
              };
              createFile(data)
                .then(async (res) => {
                  dataItem = {
                    dir: that.contextData.path,
                    superior: 0,
                    filedir: that.contextData.title,
                    fileToken: this.fileToken,
                  };
                  that.getListItem(dataItem);
                  if (that.formShow) that.formShow = false;
                  that.$Message.success('创建成功');
                })
                .catch((res) => {
                  that.catchFun(res);
                });
              break;
            case 'renameFile':
              data = {
                newname: that.contextData.path + '\\' + that.formFile.filename,
                oldname: that.contextData.pathname,
                fileToken: this.fileToken,
              };
              rename(data)
                .then(async (res) => {
                  that.$set(that.contextData, 'title', that.formFile.filename);
                  that.$Message.success('修改成功');
                  if (that.formShow) that.formShow = false;
                })
                .catch((res) => {
                  that.catchFun(res);
                });
              break;
          }
        } else {
          this.$Message.error('Fail!');
        }
      });
    },
    /**
     * 退出表单
     */
    formExit() {
      this.formShow = false;
    },

    /**
     * 处理接口回调
     * @param {Object} res
     */
    catchFun(res) {
      if (res.status) {
        if (res.status == 400) this.$Message.error(res.msg);
        if (res.status == 110008) {
          // this.$Message.error(res.msg);
          this.isShowLogn = true;
          this.isShowList = false;
          this.loading = false;
        }
      } else {
        // this.$Message.error('文件编码不被兼容，无法正确读取文件!');
      }
      //关闭蒙版层
      if (this.spinShow) this.spinShow = false;
      // 关闭文件列表展示
      if (this.loading) this.loading = false;
    },
    loopDel(data, nodeKey) {
      data.forEach((item, index) => {
        if (item.nodeKey === nodeKey) {
          return data.splice(index, 1);
        }
        if (item.children.length > 0) {
          return this.loopDel(item.children, nodeKey);
        }
      });
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
      // 判断当前文件有没有保存
      if (!that.editorList[index].isSave) {
        that.$Modal.confirm({
          title: '文件未保存',
          content: '您是否需要保存当前文件',
          loading: true,
          onOk: () => {
            // 保存文件
            that.savefile(index);
          },
          onCancel: () => {
            that.$Message.info('取消保存');
          },
        });
      }
    },
    //编辑器状态变化
    editModalChange() {
      let that = this;
      that.editorList.forEach(function (value, index) {
        if (value.isSave === false) {
          if (confirm(`${that.editorIndex[index].title}文件未保存,是否要保存该文件`)) {
            // 保存当前文件
            that.savefile(index, true);
          } else {
            that.$Message.info(`已取消${that.editorIndex[index].title}文件保存`);
          }
        }
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
      that.navList = []; //左侧导航数据
      that.navItem = {}; //左侧导航点击是选中的数据
      that.contextData = null; //左侧导航右键点击是产生的数据对象
    },
  },
};
</script>
<style scoped>
.file-left /deep/ .ivu-tree-title {
  font-weight: 500;
  font-family: SourceHanSansSC-regular, '微软雅黑', Arial, Helvetica, sans-serif;
}
.file-content /deep/ .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab-active {
  border-bottom: 1px solid orange;
}
</style>
<style scoped lang="stylus">
.file-left
    padding-left 10px
    >>>.ivu-icon-ios-arrow-forward
       font-size 18px !important;
	   color #cccccc
    >>>.ivu-icon-ios-folder-outline
       font-size 14px !important;
    >>>.ivu-icon-ios-document-outline
       font-size 18px !important;
	>>>.ivu-icon-md-folder{
     	font-size 18px !important;
	   color #d6ab34 !important;
	}
    >>> .ivu-table-row
       cursor pointer;
.mr5
   margin-right 5px
.backs
   cursor pointer;
   display inline-block;
   .icon
    margin-bottom 3px
>>>.CodeMirror
 height: 70vh !important;

.file-box
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	position: relative;
	height: 95%;
	min-height: 600px;
	overflow: hidden;
.file-box
	.file-left
		position: absolute;
		top: 53px;
		left: 0;
		height: 90%;
		// height: 100%;
		// min-height: 600px;
		width:25%;
		max-width: 250px;
		overflow: auto;
		background-color: #222222;
		box-shadow: #000000 -6px 0 6px -6px inset;
	.file-fix
		flex: 1;
		max-width: 250px;
		height: 76vh;
		min-height: 600px;
		// bottom: 0px;
		// overflow: auto;
		min-height: 600px;
		background-color: #222222;
.file-box
	.file-content
		// position: absolute;
		// top: 53px;
		// left: 25%;
		flex: 3;
		overflow: hidden;
		min-height: 600px;
		height: 100%;
>>>.ivu-modal-body
		padding: 0;
>>>.ivu-modal-content
	background-color: #292929
.diy-button
	// float: left;
	height: 35px;
	padding: 0 15px;
	font-size: 13px;
	text-align: center;
	color: #fff;
	border: 0;
	border-right: 1px solid #4c4c4c;
	cursor: pointer;
	border-radius: 0
	background-color: #565656

.form-mask
	z-index: -1;
	width: 100%;
	height: 100%;
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	margin: auto;
	background: rgba(0,0,0,0.3);
.diy-from-header
	height: 30px
	line-height: 30px;
	background-color: #fff;
	text-align: left;
	padding-left: 20px
	font-size: 16px;
	margin-bottom: 15px;
	span
		display: inline-block;
		float: right;
		color: #999;
		text-align: right;
		font-size: 12px;
		width: 280px;
		word-break:keep-all;/* 不换行 */
		white-space:nowrap;/* 不换行 */
		overflow:hidden;
		text-overflow:ellipsis;
.diy-from
	z-index: 9999;
	width: 400px;
	height: 100px;
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	margin: auto;
	text-align: center;
	background-color: #2f2f2f;
.show-info
	background-color: #383838;
	color: #FFF;
	width: 25%;
	max-width: 250px;
	position: absolute;
	top: 0;
	left: 0;
	z-index: 1122;
	.diy-button
		width: 50%;
		height: 25px;
	.diy-button-list
		display: flex;
		align-items: center;
	.show-text
		padding-left: 10px;
		word-break:keep-all;/* 不换行 */
		white-space:nowrap;/* 不换行 */
		overflow:hidden;
		text-overflow:ellipsis;
		padding: 7px 5px;
body >>>.ivu-select-dropdown{
	background: #fff;
}
.diy-tree-render
	>>>li
		overflow: hidden;
	>>>.ivu-tree-title
		width: 90%;
		max-width:250px;
		padding: 0;
		padding-left: 5px
>>>.ivu-tree-children
		.ivu-tree-title:hover
			background-color:#2f2f2f !important;
.file-box
	.file-left::-webkit-scrollbar
		width: 4px;
.file-box
	.file-left::-webkit-scrollbar-thumb
		border-radius: 10px;
		-webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
		background: rgba(255, 255, 255, 0.2);
.file-box
	.file-left::-webkit-scrollbar-track
		-webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
		border-radius: 0;
		background: rgba(0,0,0,0.1);
.diy-header
	display: flex;
	align-items: center;
	justify-content: space-between;
	.diy-header-icon
		margin-right: 30px;
		cursor: pointer;
	.diy-header-icon:hover
		opacity: 0.8;
// 自定义方法缩小
>>>.diy-fullscreen
		overflow: hidden;
		.ivu-modal
			top: 0px;
			left: 0px;
			right: 0px;
			bottom: 0px;
			height: 100%;
			width: 100% !important;
			.ivu-modal-content
				height: 100%;
				.ivu-modal-body
					height: 100%;
			.ivu-tabs
				.ivu-tabs-content-animated
					height: 92%;
					background-color:#2f2f2f !important;
			.ivu-tabs-content
				height: 100%
			.ivu-tabs
				.ivu-tabs-tabpane
					height: 92%
>>>.ivu-modal
		top: 70px;
	.ivu-modal-content
		.ivu-modal-body
			min-height: 632px;
			height: 80vh;
			overflow: hidden;
	.ivu-tabs
		.ivu-tabs-content-animated
			min-height:560px;
			height: 73vh;
			margin-top: -1px;
		.ivu-tabs-tabpane
			min-height:560px;
			height: 73vh;
			margin-top: -1px;
	.ivu-tabs-nav .ivu-tabs-tab .ivu-icon
		color: #f00;
>>>body .ivu-select-dropdown .ivu-dropdown-transfer
		background:red !important;
// 导航栏右键样式 无效


.file-left /deep/ .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover{
	background-color: #e5e5e5 !important;
}
// 选项卡头部
>>>.ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-nav-container
	background-color: #333;
</style>
