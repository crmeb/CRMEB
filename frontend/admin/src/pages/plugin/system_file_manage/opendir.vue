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
      <el-table v-if="isShowList" :data="tabList" v-loading="loading" empty-text="暂无数据" class="mt14">
        <el-table-column label="文件/文件夹名" min-width="150">
          <template #default="scope">
            <div class="file-name" v-db-click @click="currentChange(scope.row)">
              <i v-if="scope.row.isDir" class="el-icon-folder mr5" />
              <i v-else class="el-icon-document mr5" />
              <span>{{ scope.row.filename }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="文件/文件夹大小" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.size }}</span>
          </template>
        </el-table-column>
        <el-table-column label="更新时间" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.mtime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="120">
          <template #default="scope">
            <div class="mark">
              <div v-if="scope.row.is_edit" class="table-mark" v-db-click @click="isEditMark(scope.row)">
                {{ scope.row.mark }}
              </div>
              <el-input ref="markRef" v-else v-model="scope.row.mark" @blur="isEditBlur(scope.row)"></el-input>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="60">
          <template #default="scope">
            <a v-db-click @click="open(scope.row)" v-if="scope.row.isDir">打开</a>
            <a v-db-click @click="edit(scope.row)" v-else>编辑</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog
      v-model="modals"
      :custom-class="className"
      :close-on-click-modal="false"
      width="80%"
      top="5vh"
      @close="editModalChange"
      append-to-body
      :title="editorIndex[indexEditor].title"
    >
      <template #header
        ><p class="diy-header">
          <span>{{ title }}</span>
          <i
            v-db-click
            @click="winChanges"
            class="diy-header-icon"
            :class="className ? 'el-icon-cpu' : 'el-icon-full-screen'"
            style="font-size: 20px"
          /></p
      ></template>
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
              :props="treeProps"
            >
              <!-- <template transfer #contextMenu>
                <DropdownItem v-if="contextData && contextData.isDir" v-db-click @click="handleContextCreateFolder()"
                  >新建文件夹</DropdownItem
                >
                <DropdownItem v-if="contextData && contextData.isDir" v-db-click @click="handleContextCreateFile()"
                  >新建文件</DropdownItem
                >
                <DropdownItem v-db-click @click="handleContextRename()">重命名</DropdownItem>
                <DropdownItem v-db-click @click="handleContextDelFolder()" style="color: #ed4014">删除</DropdownItem>
              </template> -->
            </el-tree>
          </div>
          <div class="file-fix"></div>
          <div class="file-content">
            <el-tabs
              type="card"
              v-model="indexEditor"
              style="height: 100%"
              @tab-change="toggleEditor"
              :animated="false"
              closable
              @tab-remove="handleTabRemove"
            >
              <template v-for="value in editorIndex" :key="value.index">
                <el-tab-pane v-if="value.tab" :name="value.index.toString()" :label="value.title" :icon="value.icon">
                  <div :id="'container_' + value.index" style="height: 100%; min-height: calc(80vh - 100px)"></div>
                </el-tab-pane>
              </template>
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
      <el-form ref="formInlineRef" :model="formFile" :rules="ruleInline" inline>
        <el-form-item prop="filename" class="diy-file">
          <el-input type="text" class="diy-file" v-model="formFile.filename" placeholder="请输入名字">
            <template #prepend><i class="el-icon-folder-opened"></i></template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button class="diy-button" v-db-click @click="handleSubmit">确定</el-button>
        </el-form-item>
        <el-form-item>
          <el-button class="diy-button" v-db-click @click="formExit()">取消</el-button>
        </el-form-item>
        <div class="form-mask" v-show="formShow"></div>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
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
} from './api';
import loginFrom from './components/loginFrom';
import { setCookies, getCookies, removeCookies } from '@/libs/util';
import modalForm from '@/utils/modalForm';
// import Fullscreen from '@/layout/components/fullscreen';
// 统一从按需加载入口引入（避免全量 monaco 语言包进入构建产物）
import * as monaco from '@/libs/monaco';

defineOptions({ name: 'opendir' });

const formInlineRef = ref(null);
const markRef = ref(null);

const modals = ref(false); //编辑器开关
const editorIndex = ref([
  //选项卡数组
  {
    tab: true,
    index: '0',
    title: '',
    icon: '',
  },
]);
// 编辑器实例数组（含 monaco 实例，保持非响应式避免 proxy 干扰）
let editorList = [];
let editor = null; //当前编辑器对象
const indexEditor = ref(0); //当前编辑器索引
const code = ref(''); //当前文件打开时的内容
const navList = ref([]); //左侧导航数据
const navItem = ref({}); //左侧导航点击是选中的数据
const contextData = ref(null); //左侧导航右键点击是产生的数据对象

const fileType = ref(''); // 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件
const className = ref(''); //全屏 class名
// fullscreen:false,  // 是否全屏
const isSave = ref(true); //当前文件是否保存

const isShowLogn = ref(false); // 登录
const isShowList = ref(false); // 登录之后列表

const spinShow = ref(false);
const loading = ref(false);
const tabList = ref([]);

const formItem = ref({
  //记录当前路径信息，获取文件列表时使用
  dir: '',
  superior: 0,
  filedir: '',
  fileToken: getCookies('file_token'),
});
const dir = ref(''); //当前完整文件路径
// rows: {},  //
const pathname = ref(''); // 当前文件路径
const title = ref(''); //当前文件标题

const formFile = ref({
  //重命名表单
  filename: '',
});
const ruleInline = {
  filename: [{ required: true, message: '请输入文件或文件夹的名字', trigger: 'blur' }],
};
const formShow = ref(false); //表单开关
const formTitle = ref(''); //表单标题
const fileToken = getCookies('file_token');
const routeList = ref([]); //  打开文件路径
const navListForTab = ref([]); // 表单页缓存导航（原挂实例属性）
const treeProps = {
  label: 'title',
  children: 'children',
  isLeaf: 'isLeaf',
};

getList();

onBeforeUnmount(() => {
  removeCookies('file_token');
});

// 点击行
function currentChange(currentRow) {
  if (currentRow.isDir) {
    open(currentRow);
  } else {
    edit(currentRow);
  }
}
/**
 * 文件列表
 * @param {Object} refresh   // 是否重新加载 bool
 * @param {Object} is_edit   // 是否是编辑器中的刷新 bool
 */
function getList(refresh, is_edit) {
  let params;
  if (refresh) {
    params = {
      dir: '',
      superior: 0,
      filedir: '',
      fileToken: fileToken,
    };
  } else {
    params = formItem.value;
    params.fileToken = fileToken;
  }
  if (!is_edit) loading.value = true;
  opendirListApi(params)
    .then(async (res) => {
      let data = res.data;
      routeList.value = data.routeList;

      if (is_edit) {
        navList.value = data.navList;
      } else {
        navListForTab.value = data.navList;
        tabList.value = data.list;
        // navList.value = data.navList;
        isShowList.value = true;
      }
      dir.value = data.dir;
      isShowLogn.value = false;
      loading.value = false;
    })
    .catch((res) => {
      catchFun(res);
    });
}
//新建文件后重新加载左侧导航
function getListItem(data) {
  opendirListApi(data)
    .then(async (res) => {
      contextData.value['children'] = res.data.navList;
    })
    .catch((res) => {
      catchFun(res);
    });
}

// 返回上级
function goBack(is_edit) {
  formItem.value = {
    dir: dir.value,
    superior: 1,
    filedir: '',
  };
  getList(false, is_edit);
}
// 打开
function open(row) {
  // rows.value = row;
  formItem.value = {
    dir: row.path,
    superior: 0,
    filedir: row.filename,
    fileToken: fileToken,
  };
  getList(false, false);
}
function jumpRoute(item) {
  let data = {
    path: item.route,
    filename: '',
  };
  open(data);
}
function refreshRoute() {
  let data = {
    path: routeList.value[routeList.value.length - 1].route,
    filename: '',
  };
  open(data);
}
// 编辑ß
function edit(row) {
  navItem.value = row;
  spinShow.value = true;
  pathname.value = row.pathname;
  title.value = row.filename;
  editorIndex.value[0].title = row.filename;
  editorIndex.value[0].pathname = row.pathname;
  navList.value = navListForTab.value;
  dir.value = row.path;
  // 创建代码容器
  if (editorList.length <= 0) {
    // initEditor();
  }
  openfile(row.pathname, false);
}
/**
 * 备注
 */
function mark(row) {
  modalForm(
    fileMark({
      path: row.pathname,
      fileToken: fileToken,
    }),
  ).then(() => getList(true, false));
}
/**
 * 保存
 * @param {Object} index   // 当前索引
 * @param {Object} type    // true 不更新当前本地数据，false或者为空 更新当前数据
 */
function savefile(index, type) {
  let codeData = editorList[index].editor.getValue();
  let data = {
    comment: codeData,
    filepath: editorList[index].path,
    fileToken: fileToken,
  };
  savefileApi(data)
    .then(async (res) => {
      if (!type) {
        code.value = codeData;
        isSave.value = true;
        editorIndex.value[index].icon = '';
        editorList[index].isSave = true;
      }
      ElMessage.success(res.msg);
      ElMessageBox.close();
    })
    .catch((res) => {
      catchFun(res);
    });
}
// 刷新
function refreshfile() {
  // 刷新编辑器
  if (editorList[indexEditor.value]) openfile(editorList[indexEditor.value].path, true);
}
//计算token过期时间
function getExpiresTime(expiresTime) {
  let nowTimeNum = Math.round(new Date() / 1000);
  let expiresTimeNum = expiresTime - nowTimeNum;
  return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
}
// 侧边栏异步加载
function loadData(item, callback) {
  if (!item.data.isLeaf) {
    formItem.value = {
      dir: item.data.path,
      superior: 0,
      filedir: item.data.title,
      fileToken: fileToken,
    };
    opendirListApi(formItem.value)
      .then(async (res) => {
        callback(res.data.navList);
      })
      .catch((res) => {
        if (res.status == 110008) {
          ElMessage.error(res.msg);
          isShowLogn.value = true;
          isShowList.value = false;
          loading.value = false;
        } else {
          catchFun(res);
        }
      });
  }
}
// 自定义显示（element-plus 树已移除 render-content，保留原函数）
function renderContent(h, { node, data, root }) {
  return h(
    'span',
    {
      style: {
        display: 'inline-block',
        cursor: 'pointer',
        userSelect: 'null',
        color: '#cccccc',
        width: '100%',
        borderRadis: '5px',
      },
      onClick: () => {
        clickDir(data, root, node);
      },
      onContextmenu: () => {
        // handleContextDelFolder(data,root,node);
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
}
/**
 * 侧边栏点击事件
 * @param {Object} data
 */
function clickDir(data, root, node) {
  navItem.value = data;
  pathname.value = data.pathname;

  if (!data.isDir) {
    let i = editorIndex.value.findIndex((e) => {
      return e.pathname === data.pathname;
    });
    if (i > -1) {
      indexEditor.value = i.toString();
      toggleEditor();
    } else {
      let index = editorIndex.value.length;
      // 创建tabs
      editorIndex.value.push({
        tab: true,
        index: index.toString(),
        title: data.title,
        icon: '',
        pathname: data.pathname,
      });
      indexEditor.value = index.toString();
      // 创建代码容器
      initEditor();
      openfile(data.pathname, true);
    }
  }
}
//侧边栏右键点击事件
function handleContextMenu(data, event, position) {
  position.left = Number(position.left.slice(0, -2)) + 75 + 'px';
  contextData.value = data;
}
// 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件 renameFile|文件重命名
//创建文件夹
function handleContextCreateFolder() {
  formFile.value.filename = '';
  formTitle.value = '创建文件夹';
  formShow.value = true;
  fileType.value = 'createFolder';
}
//创建文件
function handleContextCreateFile() {
  formFile.value.filename = '';
  formTitle.value = '创建文件';
  formShow.value = true;
  fileType.value = 'createFile';
}
//删除文件
function handleContextDelFolder() {
  ElMessageBox.confirm('您确定要删除改文件？', '删除文件夹和文件')
    .then(() => {
      let data = {
        path: contextData.value.pathname,
        fileToken: fileToken,
      };
      delFolder(data)
        .then(async (res) => {
          loopDel(navList.value, contextData.value.nodeKey);
          ElMessageBox.close();
          ElMessage.success('删除成功');
        })
        .catch((res) => {
          catchFun(res);
        });
    })
    .catch(() => {
      ElMessage.info('取消删除');
    });
}
//重命名
function handleContextRename() {
  formFile.value.filename = contextData.value.title;
  formTitle.value = '重命名文件';
  formShow.value = true;
  fileType.value = 'renameFile';
}
//打开文件
function openfile(path, is_edit) {
  let params = {
    filepath: path,
    fileToken: fileToken,
  };

  openfileApi(params)
    .then(async (res) => {
      if (!is_edit) {
        modals.value = true;
        spinShow.value = false;
        initEditor();
      }
      let data = res.data;
      code.value = data.content;
      // 保存相对信息

      editorList[indexEditor.value].oldCode = code.value;
      nextTick((e) => {
        editorList[indexEditor.value || 0].path = path;
        editorList[indexEditor.value || 0].pathname = path;
      });
      //改变属性
      changeModel(data.mode, code.value);
    })
    .catch((res) => {
      catchFun(res);
    });
}
/**
 * 初始化编辑器
 */
function initEditor() {
  nextTick(() => {
    // 初始化编辑器，确保dom已经渲染
    editor = monaco.editor.create(document.getElementById('container_' + indexEditor.value), {
      value: code.value, //编辑器初始显示文字
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
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S, function () {
      savefile(indexEditor.value);
    });
    editor.onKeyUp(() => {
      // 当键盘按下，判断当前编辑器文本与已保存的编辑器文本是否一致
      if (editor.getValue() != code.value) {
        isSave.value = false;
        editorIndex.value[indexEditor.value].icon = 'md-warning';
        editorList[indexEditor.value].isSave = false;
      }
    });
    editorList.push({
      editor: editor,
      oldCode: code.value,
      path: pathname.value,
      isSave: true,
      index: indexEditor.value,
    });
  });
}
/**
 * 切换语言
 * @param {Object} mode
 */
function changeModel(mode, value) {
  var oldModel = editorList[indexEditor.value].editor.getModel(); //获取旧模型
  // var value = editor.getValue();//获取旧的文本
  //创建新模型，value为旧文本，id为modeId，即语言（language.id）
  //modesIds即为支持语言
  // var modesIds = monaco.languages.getLanguages().map(function(lang) { return lang.id; });
  if (!mode) mode = oldModel.getLanguageId();
  // if(!value) value = editor.getValue();

  var newModel = monaco.editor.createModel(value, mode);
  //将旧模型销毁
  if (oldModel) {
    oldModel.dispose();
  }
  //设置新模型
  editorList[indexEditor.value].editor.setModel(newModel);
}
// 文件操作类型 createFolder|创建文件夹 createFile|创建文件 delFolder|删除文件夹或者文件
function handleSubmit() {
  let data = '';
  let dataItem = '';
  formInlineRef.value.validate((valid) => {
    if (valid) {
      switch (fileType.value) {
        case 'createFolder':
          data = {
            path: contextData.value.pathname,
            name: formFile.value.filename,
            fileToken: fileToken,
          };
          createFolder(data)
            .then(async (res) => {
              dataItem = {
                dir: contextData.value.path,
                superior: 0,
                filedir: contextData.value.title,
                fileToken: fileToken,
              };
              getListItem(dataItem);
              if (formShow.value) formShow.value = false;
              ElMessage.success('创建成功');
            })
            .catch((res) => {
              catchFun(res);
            });
          break;
        case 'createFile':
          data = {
            path: contextData.value.pathname,
            name: formFile.value.filename,
            fileToken: fileToken,
          };
          createFile(data)
            .then(async (res) => {
              dataItem = {
                dir: contextData.value.path,
                superior: 0,
                filedir: contextData.value.title,
                fileToken: fileToken,
              };
              getListItem(dataItem);
              if (formShow.value) formShow.value = false;
              ElMessage.success('创建成功');
            })
            .catch((res) => {
              catchFun(res);
            });
          break;
        case 'renameFile':
          data = {
            newname: contextData.value.path + '\\' + formFile.value.filename,
            oldname: contextData.value.pathname,
            fileToken: fileToken,
          };
          rename(data)
            .then(async (res) => {
              contextData.value['title'] = formFile.value.filename;
              ElMessage.success('修改成功');
              if (formShow.value) formShow.value = false;
            })
            .catch((res) => {
              catchFun(res);
            });
          break;
      }
    } else {
      ElMessage.error('Fail!');
    }
  });
}
/**
 * 退出表单
 */
function formExit() {
  formShow.value = false;
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
      isShowLogn.value = true;
      isShowList.value = false;
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
function loopDel(data, nodeKey) {
  data.forEach((item, index) => {
    if (item.nodeKey === nodeKey) {
      return data.splice(index, 1);
    }
    if (item.children.length > 0) {
      return loopDel(item.children, nodeKey);
    }
  });
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
 * 切换选项卡
 * @param {Object} index
 */
function toggleEditor() {
  let index = Number(indexEditor.value);
  code.value = editorList[index].oldCode; //设置文件打开时的代码
  editor = editorList[index].editor; //设置编辑器实例
}
function isEditMark(row) {
  try {
    row.is_edit = true;
    nextTick((e) => {
      markRef.value.focus();
    });
  } catch (error) {
    console.log(error);
  }
}
function isEditBlur(row) {
  row.is_edit = false;
  let data = {
    full_path: row.real_path,
    mark: row.mark,
  };
  markSave(fileToken, data)
    .then((res) => {
      // ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function handleTabRemove(index) {
  // 关闭选项卡
  editorIndex.value[index].tab = false; // 关闭选项卡
  // 判断当前文件有没有保存
  if (!editorList[index].isSave) {
    ElMessageBox.confirm('您是否需要保存当前文件', '文件未保存')
      .then(() => {
        // 保存文件
        savefile(index);
      })
      .catch(() => {
        ElMessage.info('取消保存');
      });
  }
}
//编辑器状态变化
function editModalChange() {
  editorList.forEach(function (value, index) {
    if (value.isSave === false) {
      if (confirm(`${editorIndex.value[index].title}文件未保存,是否要保存该文件`)) {
        // 保存当前文件
        savefile(index, true);
      } else {
        ElMessage.info(`已取消${editorIndex.value[index].title}文件保存`);
      }
    }
    // 销毁当前编辑器
    editorList[index].editor.dispose();
    editorList[index].editor = null;
  });
  // 初始话数据
  modals.value = false; //编辑器开关
  editor = null; //当前编辑器对象
  editorIndex.value = [
    //选项卡数组
    {
      tab: true,
      index: '0',
      title: '',
      icon: '',
    },
  ];
  editorList = []; //编辑器数组
  indexEditor.value = '0'; //当前编辑器索引
  code.value = ''; //当前文件打开时的内容
  navList.value = []; //左侧导航数据
  navItem.value = {}; //左侧导航点击是选中的数据
  contextData.value = null; //左侧导航右键点击是产生的数据对象
}
</script>
<style scoped>
.file-left :deep(.ivu-tree-title) {
  font-weight: 500;
  font-family: SourceHanSansSC-regular, '微软雅黑', Arial, Helvetica, sans-serif;
}
.file-content :deep(.ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab-active) {
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
:deep(.CodeMirror) {
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
:deep(.el-dialog__body) {
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
.mark :deep(.el-input__inner) {
  background: #fff;
  border-radius: 0.39rem;
}
.mark :deep(.el-input__inner),
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

body :deep(.ivu-select-dropdown) {
  background: #fff;
}
:deep(.el-tabs__item) {
  background-color: #fff;
}
:deep(.el-tree) {
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
:deep(body .ivu-select-dropdown .ivu-dropdown-transfer) {
  background: red !important;
}
.file-left :deep(.ivu-select-dropdown.ivu-dropdown-transfer .ivu-dropdown-menu .ivu-dropdown-item:hover) {
  background-color: #e5e5e5 !important;
}
:deep(.ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-nav-container) {
  background-color: #333;
}
</style>
