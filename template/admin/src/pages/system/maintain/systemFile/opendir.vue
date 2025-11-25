<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" v-loading="spinShow">
      <div v-if="isShowList" class="backs-box">
        <div class="backs">
          <span class="back" v-db-click @click="goBack(false)">
            <i class="el-icon-back icon" />
          </span>
          <span class="item" v-for="(item, index) in routeList" :key="index" v-db-click @click="jumpRoute(item)">
            <span class="key">{{ item.key }}</span>
            <i class="forward el-icon-arrow-right" v-if="index < routeList.length - 1" />
          </span>
        </div>
        <span class="refresh" v-db-click @click="refreshRoute">
          <i class="el-icon-refresh-right icon" />
        </span>
      </div>
      <el-table
        v-if="isShowList"
        ref="selection"
        :data="tabList"
        v-loading="loading"
        empty-text="暂无数据"
        class="mt14"
      >
        <el-table-column label="文件/文件夹名" min-width="150">
          <template slot-scope="scope">
            <div class="file-name" v-db-click @click="currentChange(scope.row)">
              <i v-if="scope.row.isDir" class="el-icon-folder mr5" />
              <i v-else class="el-icon-document mr5" />
              <span>{{ scope.row.filename }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="文件/文件夹大小" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.size }}</span>
          </template>
        </el-table-column>
        <el-table-column label="更新时间" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.mtime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="120">
          <template slot-scope="scope">
            <div class="mark">
              <div v-if="scope.row.is_edit" class="table-mark" v-db-click @click="isEditMark(scope.row)">
                {{ scope.row.mark }}
              </div>
              <el-input ref="mark" v-else v-model="scope.row.mark" @blur="isEditBlur(scope.row)"></el-input>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="60">
          <template slot-scope="scope">
            <el-button type="text" v-db-click @click="open(scope.row)" v-if="scope.row.isDir">打开</el-button>
            <el-button type="text" v-db-click @click="edit(scope.row)" v-else>编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog
      :visible.sync="modals"
      :custom-class="className"
      :close-on-click-modal="false"
      width="80%"
      top="5vh"
      @close="editModalChange"
      append-to-body
      :title="editorIndex[indexEditor].title"
    >
      <p slot="header" class="diy-header" ref="diyHeader">
        <span>{{ title }}</span>
        <i
          v-db-click
          @click="winChanges"
          class="diy-header-icon"
          :class="className ? 'el-icon-cpu' : 'el-icon-full-screen'"
          style="font-size: 20px"
        />
      </p>
      <div style="height: 100%">
        <div class="top-button">
          <el-button type="primary" id="savefile" class="diy-button" v-db-click @click="savefile(indexEditor)"
            >保存</el-button
          >
          <el-button id="refresh" class="diy-button" v-db-click @click="refreshfile">刷新</el-button>
        </div>
        <div class="file-box">
          <div class="show-info">
            <div class="show-text" :title="navItem.pathname">目录: {{ navItem.pathname }}</div>
            <div class="diy-button-list">
              <el-button class="diy-button" v-db-click @click="goBack(true)">返回上一级</el-button>
              <el-button class="diy-button" v-db-click @click="getList(true, true)">刷新</el-button>
            </div>
          </div>
          <div class="file-left">
            <el-tree
              class="diy-tree-render"
              :data="navList"
              :render-content="renderContent"
              :load="loadData"
              @node-contextmenu="handleContextMenu"
              expand-node
              lazy
              :props="props"
            >
              <!-- <template transfer slot="contextMenu">
                <DropdownItem v-if="contextData && contextData.isDir" v-db-click @click.native="handleContextCreateFolder()"
                  >新建文件夹</DropdownItem
                >
                <DropdownItem v-if="contextData && contextData.isDir" v-db-click @click.native="handleContextCreateFile()"
                  >新建文件</DropdownItem
                >
                <DropdownItem v-db-click @click.native="handleContextRename()">重命名</DropdownItem>
                <DropdownItem v-db-click @click.native="handleContextDelFolder()" style="color: #ed4014">删除</DropdownItem>
              </template> -->
            </el-tree>
          </div>
          <div class="file-fix"></div>
          <div class="file-content">
            <el-tabs
              type="card"
              v-model="indexEditor"
              style="height: 100%"
              @tab-click="toggleEditor"
              :animated="false"
              closable
              @tab-remove="handleTabRemove"
            >
              <el-tab-pane
                v-for="value in editorIndex"
                :key="value.index"
                :name="value.index.toString()"
                :label="value.title"
                :icon="value.icon"
                v-if="value.tab"
              >
                <div
                  ref="container"
                  :id="'container_' + value.index"
                  style="height: 100%; min-height: calc(80vh - 100px)"
                ></div>
              </el-tab-pane>
            </el-tabs>
          </div>
        </div>
      </div>
    </el-dialog>

    <div v-show="formShow" class="diy-from">
      <div class="diy-from-header">
        {{ formTitle
        }}<span :title="contextData ? contextData.pathname : ''">{{ contextData ? contextData.pathname : '' }}</span>
      </div>
      <el-form ref="formInline" :model="formFile" :rules="ruleInline" inline>
        <el-form-item prop="filename" class="diy-file">
          <el-input type="text" class="diy-file" v-model="formFile.filename" placeholder="请输入名字">
            <i class="el-icon-folder-opened" slot="prepend"></i>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button class="diy-button" v-db-click @click="handleSubmit('formInline')">确定</el-button>
        </el-form-item>
        <el-form-item>
          <el-button class="diy-button" v-db-click @click="formExit()">取消</el-button>
        </el-form-item>
        <div class="form-mask" v-show="formShow"></div>
      </el-form>
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
  fileMark,
  markSave,
} from '@/api/system';
import CodeMirror from 'codemirror/lib/codemirror';
import loginFrom from './components/loginFrom';
import { setCookies, getCookies, removeCookies } from '@/libs/util';
// import Fullscreen from '@/layout/components/fullscreen';
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
      routeList: [], //  打开文件路径
      props: {
        label: 'title',
        children: 'children',
        isLeaf: 'isLeaf',
      },
    };
  },

  components: {
    loginFrom,
  },
  mounted() {
    // this.initEditor();
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
          this.routeList = data.routeList;

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
    jumpRoute(item) {
      let data = {
        path: item.route,
        filename: '',
      };
      this.open(data);
    },
    refreshRoute() {
      let data = {
        path: this.routeList[this.routeList.length - 1].route,
        filename: '',
      };
      this.open(data);
    },
    // 编辑ß
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
        // this.initEditor();
      }
      this.openfile(row.pathname, false);
    },
    /**
     * 备注
     */
    mark(row) {
      this.$modalForm(
        fileMark({
          path: row.pathname,
          fileToken: this.fileToken,
        }),
      ).then(() => this.getList(true, false));
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
          that.$message.success(res.msg);
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
      if (!item.data.isLeaf) {
        this.formItem = {
          dir: item.data.path,
          superior: 0,
          filedir: item.data.title,
          fileToken: this.fileToken,
        };
        opendirListApi(this.formItem)
          .then(async (res) => {
            callback(res.data.navList);
          })
          .catch((res) => {
            if (res.status == 110008) {
              this.$message.error(res.msg);
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
    renderContent(h, { node, data, root }) {
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
                type: !data.isLeaf ? 'md-folder' : 'ios-document-outline',
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
          that.indexEditor = i.toString();
          that.toggleEditor();
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
              that.$message.success('删除成功');
            })
            .catch((res) => {
              that.catchFun(res);
            });
        },
        onCancel: () => {
          that.$message.info('取消删除');
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
          if (!is_edit) {
            that.modals = true;
            that.spinShow = false;
            this.initEditor();
          }
          let data = res.data;
          that.code = data.content;
          // 保存相对信息

          that.editorList[that.indexEditor].oldCode = that.code;
          this.$nextTick((e) => {
            that.editorList[that.indexEditor || 0].path = path;
            that.editorList[that.indexEditor || 0].pathname = path;
          });
          //改变属性
          that.changeModel(data.mode, that.code);
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
          path: this.pathname,
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
                  that.$message.success('创建成功');
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
                  that.$message.success('创建成功');
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
                  that.$message.success('修改成功');
                  if (that.formShow) that.formShow = false;
                })
                .catch((res) => {
                  that.catchFun(res);
                });
              break;
          }
        } else {
          this.$message.error('Fail!');
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
    toggleEditor() {
      let index = Number(this.indexEditor);
      this.code = this.editorList[index].oldCode; //设置文件打开时的代码
      this.editor = this.editorList[index].editor; //设置编辑器实例
    },
    isEditMark(row) {
      try {
        row.is_edit = true;
        this.$nextTick((e) => {
          this.$refs.mark.focus();
        });
      } catch (error) {
        console.log(error);
      }
    },
    isEditBlur(row) {
      row.is_edit = false;
      let data = {
        full_path: row.real_path,
        mark: row.mark,
      };
      markSave(this.fileToken, data)
        .then((res) => {
          // this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
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
            that.$message.info('取消保存');
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
            that.$message.info(`已取消${that.editorIndex[index].title}文件保存`);
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
.file-left ::v-deep .ivu-tree-title {
  font-weight: 500;
  font-family: SourceHanSansSC-regular, '微软雅黑', Arial, Helvetica, sans-serif;
}
.file-content ::v-deep .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab-active {
  border-bottom: 1px solid orange;
}
</style>
<style lang="scss" scoped>
.file-left {
  padding-left: 10px;
  color: #cccccc;
}
.mr5 {
  margin-right: 5px;
}
.backs-box {
  display: flex;
  justify-content: space-between;
  min-width: 800px;
  max-width: max-content;
  border: 1px solid #cfcfcf;
  background: #f6f6f6;
  .refresh {
    background: #fff;
    border-left: 1px solid #cfcfcf;
    padding: 0 8px 0 10px;
    font-size: 16px;
    font-weight: bold;
  }
  .refresh {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }
  .refresh:hover,
  .back:hover {
    background: #2d8cf0;
    border-color: #38983b;
    color: #fff;
  }
}
.file-name {
  cursor: pointer;
}
.backs {
  cursor: pointer;
  display: inline-block;
  display: flex;
  align-items: center;
  width: 100%;
  .back {
    height: 100%;
    background: #fff;
    border-right: 1px solid #cfcfcf;
    padding: 6px 8px 0 10px;
    font-size: 16px;
    font-weight: bold;
  }
  .item:last-child {
    padding-right: 5px !important;
  }
  .item {
    padding: 0 0 0 8px;
    font-size: 12px;
    line-height: 33px;
    color: #555;
    display: flex;
    align-items: center;
    .key {
      margin-right: 3px;
    }
  }
  .item:hover {
    background: #fff;
  }
}
::v-deep .CodeMirror {
  height: 70vh !important;
}
.file-box {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  position: relative;
  min-height: calc(100% - 35px);
  overflow: hidden;
}
.file-box {
  .file-left {
    position: absolute;
    top: 58px;
    left: 0;
    height: calc(100% - 58px);

    width: 25%;
    max-width: 250px;
    overflow: auto;
    background-color: #292929;
  }
  .file-fix {
    flex: 1;
    max-width: 250px;
    min-height: calc(100% - 35px);

    min-height: calc(100% - 35px);
    background-color: #292929;
  }
}
.file-box {
  .file-content {
    flex: 3;
    overflow: hidden;
    min-height: calc(100% - 35px);
    height: 100%;
  }
}
::v-deep .el-dialog__body {
  padding: 0 !important;
  height: 80vh;
  max-height: 80vh;
}
.diy-button {
  height: 35px;
  padding: 0 15px;
  font-size: 13px;
  text-align: center;
  color: #fff;
  border: 0;
  border-right: 1px solid #4c4c4c;
  cursor: pointer;
  border-radius: 0;
  background-color: #565656;
}
.form-mask {
  z-index: -1;
  width: 100%;
  height: 100%;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  margin: auto;
  background: rgba(0, 0, 0, 0.3);
}
.table-mark {
  cursor: text;
}
.table-mark:hover {
  border: 1px solid #c2c2c2;
  padding: 3px 5px;
}
.mark ::v-deep .el-input__inner {
  background: #fff;
  border-radius: 0.39rem;
}
.mark ::v-deep .el-input__inner,
.el-input__inner:hover,
.el-input__inner:focus {
  border: transparent;
  box-shadow: none;
}
.diy-from-header {
  height: 30px;
  line-height: 30px;
  background-color: #fff;
  text-align: left;
  padding-left: 20px;
  font-size: 16px;
  margin-bottom: 15px;

  span {
    display: inline-block;
    float: right;
    color: #999;
    text-align: right;
    font-size: 12px;
    width: 280px;
    word-break: keep-all; /* 不换行 */
    white-space: nowrap; /* 不换行 */
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
.diy-from {
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
}
.top-button {
  background-color: #292929;
}
.show-info {
  background-color: #292929;
  color: #fff;
  width: 25%;
  max-width: 250px;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1122;
  .diy-button {
    width: 50%;
    height: 25px;
    line-height: 8px;
  }
  .diy-button-list {
    display: flex;
    align-items: center;
  }
  .show-text {
    padding-left: 10px;
    word-break: keep-all; /* 不换行 */
    white-space: nowrap; /* 不换行 */
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 7px 5px;
  }
}

body ::v-deep .ivu-select-dropdown {
  background: #fff;
}
::v-deep .el-tabs__item {
  background-color: #fff;
}
::v-deep .el-tree {
  background-color: #292929 !important;
}
.file-box {
  .file-left::-webkit-scrollbar {
    width: 4px;
  }
}
.file-box {
  .file-left::-webkit-scrollbar-thumb {
    border-radius: 10px;
    -webkit-box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
    background: rgba(255, 255, 255, 0.2);
  }
}
.file-box {
  .file-left::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
    border-radius: 0;
    background: rgba(0, 0, 0, 0.1);
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
    min-height: 580px;
    height: 73vh;
    margin-top: -1px;
  }
  .ivu-tabs-tabpane {
    min-height: 580px;
    height: 73vh;
    margin-top: -1px;
  }
}
.ivu-tabs-nav .ivu-tabs-tab .ivu-icon {
  color: #f00;
}
::v-deepbody .ivu-select-dropdown .ivu-dropdown-transfer {
  background: red !important;
}
.file-left ::v-deep .ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover {
  background-color: #e5e5e5 !important;
}
::v-deep .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-nav-container {
  background-color: #333;
}
</style>
