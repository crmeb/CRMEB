<template>
  <div>
    <div v-show="!monacoBox">
      <div ref="wang-editor" class="wang-editor" />
    </div>
    <div v-if="monacoBox">
      <Button type="primary" class="bottom" @click="getHtmlint">可视化界面</Button>
      <monaco @change="changeValue" :value="newHtml" />
    </div>

    <Modal
      v-model="modalPic"
      width="1024px"
      scrollable
      footer-hide
      closable
      title="上传图片"
      :mask-closable="false"
      :z-index="9"
    >
      <uploadPictures v-if="modalPic" :isChoice="isChoice" @getPic="getPic" @getPicD="getPicD"></uploadPictures>
    </Modal>
    <Modal
      v-model="modalVideo"
      width="800px"
      scrollable
      footer-hide
      closable
      title="上传视频"
      :mask-closable="false"
      :z-index="9"
    >
      <uploadVideo v-if="modalVideo" @getVideo="getvideo"></uploadVideo>
    </Modal>
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
    content(val) {
      this.editor.txt.html(val);
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
      _this.img = pc.att_dir;
      _this.modalPic = false;
      this.editor.cmd.do('insertHTML', `<img src="${_this.img}" style="max-width:100%;"/>`);
    },
    getimg() {
      this.modalPic = true;
      this.isChoice = '多选';
    },
    getvideoint() {
      this.modalVideo = true;
    },
    getHtmlint() {
      this.monacoBox = !this.monacoBox;
      this.value = this.newHtml;
      if (!this.monacoBox) {
        this.editor.txt.html(this.newHtml);
      }
    },
    getPicD(data) {
      let _this = this;
      _this.modalPic = false;

      data.map((d) => {
        this.editor.cmd.do('insertHTML', `<img src="${d.att_dir}" style="max-width:100%;"/>`);
      });
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
      this.editor.config.uploadImgShowBase64 = false;
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

<style lang="stylus" scoped>
.bottom {
  margin-bottom: 10px;
  cursor: pointer;
}
</style>
