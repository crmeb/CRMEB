<template>
  <div>
    <div v-show="!monacoBox">
      <div ref="wang-editor" class="wang-editor" />
    </div>
    <div v-if="monacoBox">
      <el-button type="primary" class="bottom" v-db-click @click="getHtmlint">可视化界面</el-button>
      <monaco class="monaco-box" @change="changeValue" :value="newHtml" />
    </div>

    <el-dialog :visible.sync="modalPic" width="1024px" title="上传图片" :close-on-click-modal="false">
      <uploadPictures v-if="modalPic" :isChoice="isChoice" @getPic="getPic"></uploadPictures>
    </el-dialog>
    <el-dialog :visible.sync="modalVideo" width="1024px" title="上传视频" :close-on-click-modal="false">
      <uploadVideo v-if="modalVideo" @getVideo="getvideo"></uploadVideo>
    </el-dialog>
  </div>
</template>
<script>
import monaco from './monaco';
import E from 'wangeditor';
import AlertMenu from './editor';
import HtmlMenu from './html';
import uploadPictures from '@/components/uploadPictures';
import uploadVideo from '@/components/uploadVideo2';
import { getCookies } from '@/libs/util';

import util from '../../utils/bus';
export default {
  name: 'Index',
  components: {
    uploadPictures,
    uploadVideo,
    monaco,
  },
  props: {
    content: {
      type: String,
      default: '',
    },
  },

  data() {
    return {
      monacoBox: false,
      value: '',
      modalPic: false,
      isChoice: '多选',
      picTit: 'danFrom',
      img: '',
      modalVideo: false,
      editor: null,
      uploadSize: 2,
      video: '',

      // header: {
      //   "Authori-zation": "Bearer " + getCookies("token"),
      // },
    };
  },
  watch: {
    initEditor(val) {
      if (val) {
        this.editor.txt.html(this.content);
      } else {
        this.editor.txt.html('');
      }
    },
  },
  computed: {
    initEditor() {
      return this.content && this.editor;
    },
  },
  created() {
    // window.getvideoint = this.getvideoint;
    // window.getHtmlint = this.getHtmlint;
  },
  mounted() {
    this.createEditor();
    util.$on('Video', (Video) => {
      this.getvideoint();
    });
    util.$on('Html', (Html) => {
      this.getHtmlint();
    });
  },

  methods: {
    changeValue(value) {
      this.newHtml = value;
      this.$emit('editorContent', value);

      this.$emit('input', value);
    },
    // 获取多张图信息
    getPic(pc) {
      let _this = this;
      _this.img = pc;
      _this.modalPic = false;
      _this.img.map((d) => {
        this.editor.cmd.do('insertHTML', `<img src="${d.att_dir}" style="max-width:100%;"/>`);
      });
    },
    getimg() {
      this.modalPic = true;
      this.isChoice = '多选';
    },
    getvideoint() {
      // this.modalVideo = true;
      this.$videoModal((e) => {
        console.log(e);
        let _this = this;
        _this.modalVideo = false;
        this.video = e;
        let videoHTML =
          '<video src="' + this.video + '" controls style="max-width:100%;min-height:500rpx"></video><p><br></p>';
        this.editor.cmd.do('insertHTML', videoHTML);
      });
    },
    getHtmlint() {
      this.monacoBox = !this.monacoBox;
      this.value = this.newHtml;
      if (!this.monacoBox) {
        this.editor.txt.html(this.newHtml);
      }
    },
    getvideo(data) {
      let _this = this;
      _this.modalVideo = false;
      this.video = data;
      let videoHTML =
        '<video src="' + this.video + '" controls style="max-width:100%;min-height:500rpx"></video><p><br></p>';
      this.editor.cmd.do('insertHTML', videoHTML);
    },

    createEditor() {
      let _this = this;
      const menuKey = 'alertMenuKey';
      const html = 'alertHtml';
      this.editor = new E(this.$refs['wang-editor']);

      this.editor.menus.extend(menuKey, AlertMenu);
      this.editor.menus.extend(html, HtmlMenu);
      this.editor.config.menus = this.editor.config.menus.concat(html);
      this.editor.config.menus = this.editor.config.menus.concat(menuKey);
      this.editor.config.showLinkImg = false;
      this.editor.config.uploadImgFromMedia = function () {
        _this.getimg();
      };
      // this.editor.config.uploadVideoHeaders = _this.header;
      this.editor.config.height = 600;
      this.editor.config.menus = [
        'alertHtml',
        'head',
        'bold',
        'fontSize',
        'fontName',
        'italic',
        'underline',
        'strikeThrough',
        'indent',
        'lineHeight',
        'foreColor',
        'backColor',
        'link',
        'list',
        // "todo",
        'justify',
        'quote',
        'emoticon',
        'image',
        'alertMenuKey',
        // "table",
        'code',
        'splitLine',
      ];
      // 配置全屏功能按钮是否展示
      //   this.editor.config.showFullScreen = false
      this.editor.config.uploadImgShowBase64 = true;
      //   this.editor.config.uploadImgAccept = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']
      this.editor.config.zIndex = 0;
      //   this.editor.config.uploadImgMaxSize = this.uploadSize * 1024 * 1024
      this.editor.config.compatibleMode = () => {
        // 返回 true 表示使用兼容模式；返回 false 使用标准模式
        return true;
      };
      this.editor.config.onchange = (newHtml) => {
        this.newHtml = newHtml;
        this.$emit('editorContent', newHtml);
      };
      this.editor.config.onchangeTimeout = 300; // change后多久更新数据

      this.editor.create();
    },
  },
};
</script>

<style lang="scss" scoped>
.bottom {
  margin-bottom: 10px;
  cursor: pointer;
}
.monaco-box ::v-deep .el-textarea__inner {
  height: 600px;
}
::v-deep .w-e-toolbar {
  z-index: 2 !important;
}
</style>
