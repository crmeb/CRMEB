<template>
  <Modal v-model="modals_son" scrollable footer-hide closable :title="title" :mask-closable="false" width="900">
    <Button type="primary" id="savefile" class="mr5 mb15" @click="savefile">保存</Button>
    <Button id="undo" class="mr5 mb15" @click="undofile">撤销</Button>
    <Button id="redo" class="mr5 mb15" @click="redofile">回退</Button>
    <Button id="refresh" class="mb15" @click="refreshfile">刷新</Button>
    <textarea ref="mycode" class="codesql public_text" v-model="code" style="height: 80vh"></textarea>
    <Spin size="large" fix v-if="spinShow"></Spin>
  </Modal>
</template>

<script>
import { opendirLoginApi } from '@/api/system';
import CodeMirror from 'codemirror/lib/codemirror';
import 'codemirror/theme/ambiance.css';
import { setCookies, getCookies, removeCookies } from '@/libs/util';

// 核心样式
// import 'codemirror/lib/codemirror.css'
// 引入主题后还需要在 options 中指定主题才会生效
import 'codemirror/theme/cobalt.css';

// 需要引入具体的语法高亮库才会有对应的语法高亮效果
// codemirror 官方其实支持通过 /addon/mode/loadmode.js 和 /mode/meta.js 来实现动态加载对应语法高亮库
// 但 vue 貌似没有无法在实例初始化后再动态加载对应 JS ，所以此处才把对应的 JS 提前引入
// import 'codemirror/mode/javascript/javascript.js'
// import 'codemirror/mode/css/css.js'
// import 'codemirror/mode/xml/xml.js'
// import 'codemirror/mode/clike/clike.js'
// import 'codemirror/mode/markdown/markdown.js'
// import 'codemirror/mode/python/python.js'
// import 'codemirror/mode/r/r.js'
// import 'codemirror/mode/shell/shell.js'
// import 'codemirror/mode/sql/sql.js'
// import 'codemirror/mode/swift/swift.js'
// import 'codemirror/mode/vue/vue.js'

require('codemirror/mode/javascript/javascript');
export default {
  name: 'opendir',
  props: {
    rows: {
      type: Object,
      default: {},
    },
    code: {
      type: String,
      default: ' ',
    },
    modals: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      editor: '',
      isShowLogn: false, // 登录
      isShowList: false, // 登录之后列表
      spinShow: false,
      loading: false,

      formItem: {
        dir: '',
        superior: 0,
        filedir: '',
      },
      pathname: '',
      modals_son: this.modals,
      fileToken: getCookies('file_token'),
    };
  },
  watch: {
    code: {
      handler(newValue, oldValue) {
        this.editor.setValue(newValue);
      },
      deep: true, // 默认值是 false，代表是否深度监听
    },
    modals: {
      handler(newValue, oldValue) {
        this.modals_son = newValue;
      },
      deep: true, // 默认值是 false，代表是否深度监听
    },
  },
  mounted() {
    this.editor = CodeMirror.fromTextArea(this.$refs.mycode, {
      value: 'http://www.crmeb.com', // 文本域默认显示的文本
      mode: 'text/javascript',
      theme: 'ambiance', // CSS样式选择
      indentUnit: 8, // 缩进单位，默认2
      smartIndent: true, // 是否智能缩进
      tabSize: 4, // Tab缩进，默认4
      readOnly: false, // 是否只读，默认false
      showCursorWhenSelecting: true,
      lineNumbers: true, // 是否显示行号

      indentWithTabs: true,
      matchBrackets: true,
      extraKeys: {
        Ctrl: 'autocomplete',
      }, //自定义快捷键
    });
    //代码自动提示功能，记住使用cursorActivity事件不要使用change事件，这是一个坑，那样页面直接会卡死
    editor.on('cursorActivity', function () {
      editor.showHint();
    });
  },
  created() {
    // this.getList();
    this.onIsLogin();
  },
  methods: {
    // 保存
    savefile() {
      let data = {
        comment: this.editor.getValue(),
        filepath: this.pathname,
        fileToken: this.fileToken,
      };
      savefileApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.modals = false;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 撤销
    undofile() {
      this.editor.undo();
    },
    redofile() {
      this.editor.redo();
    },
    // 刷新
    refreshfile() {
      this.editor.refresh();
    },
  },
};
</script>
<style scoped lang="stylus">
>>>.CodeMirror
	height: 70vh !important
</style>
