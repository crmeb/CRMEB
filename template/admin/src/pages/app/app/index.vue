<template>
  <div :style="bgcolors">
    <div class="i-layout-page-header">
      <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
      <div style="float: right">
        <Button class="bnt" type="primary" @click="onsubmit('formValidate')">保存</Button>
      </div>
    </div>
    <div class="box-wrapper">
      <div class="iframe" :bordered="false">
        <div class="agreement-box">
          <div class="template"></div>
          <div class="htmls_box">
            <div class="htmls_top">服务协议与隐私政策</div>
            <div class="htmls_font">
              <div class="ok">我同意</div>
              <div>不同意</div>
            </div>
            <div class="htmls" v-html="content"></div>
          </div>
        </div>
      </div>
      <div style="margin-left: 40px">
        <div class="table_box">
          <div type="flex">
            <div v-bind="grid">
              <div class="title">隐私权限页面展示：</div>
            </div>
          </div>
          <div>
            <Form
              class="form"
              ref="formValidate"
              :model="formValidate"
              :rules="ruleValidate"
              :label-position="labelPosition"
              @submit.native.prevent
            >
              <div class="goodsTitle acea-row"></div>
              <FormItem label="" style="margin: 0px">
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.content"
                  @editorContent="getEditorContent"
                ></WangEditor>
              </FormItem>
            </Form>
          </div>
        </div>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';
import Setting from '@/setting';
import { getColorChange } from '@/api/diy';
import { mapState } from 'vuex';
import editFrom from '@/components/from/from';
import { productGetTempKeysApi, uploadType } from '@/api/product';
import {
  groupAllApi,
  groupDataListApi,
  groupSaveApi,
  openAdvSave,
  groupDataAddApi,
  groupDataHeaderApi,
  groupDataEditApi,
  groupDataSetApi,
  getAgreement,
  setAgreement,
  getOpenAdv,
} from '@/api/system';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
import { getCookies } from '@/libs/util';

export default {
  name: 'list',
  components: {
    editFrom,
    draggable,
    uploadPictures,
    linkaddress,
    WangEditor,
  },
  computed: {
    bgcolors() {
      return {
        '--color-theme': this.bgCol,
      };
    },
    labelWidth() {
      return this.isMobile ? undefined : 120;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    ...mapState('admin/layout', ['menuCollapse']),
  },
  data() {
    return {
      formValidate: {
        content: '',
      },
      ruleValidate: {},
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      a: 1, //判断的隐私协议
      guide: 0,
      bgimg: 0,
      columns1: [],
      bgCol: '',
      name: '',
      content: '',
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      sginList: [],
      progress: 0, // 进度条默认0
      url: '',
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      pageId: 0,
      groupAll: [],
      activeIndex: 0,
      sortName: null,
      activeIndexs: 0,
      cmsList: [],
      loadingExist: false,
      formItem: {
        time: '',
        type: 'pic',
        status: 1,
        value: [],
        video_link: '',
      },
      header: {},
      type: 0,
    };
  },
  created() {
    this.color();
    this.a = 1;
    this.guide = 0;
    this.getAgreement();
  },
  mounted() {},
  methods: {
    linkUrl(e) {
      this.tabList.list[this.activeIndexs].link = e;
    },
    getEditorContent(data) {
      this.content = data;
    },
    color() {
      getColorChange('color_change').then((res) => {
        switch (res.data.status) {
          case 1:
            this.bgCol = '#3875EA';
            this.bgimg = 1;
            break;
          case 2:
            this.bgCol = '#00C050';
            this.bgimg = 2;
            break;
          case 3:
            this.bgCol = '#E93323';
            this.bgimg = 3;
            break;
          case 4:
            this.bgCol = '#FF448F';
            this.bgimg = 4;
            break;
          case 5:
            this.bgCol = '#FE5C2D';
            this.bgimg = 5;
            break;
        }
      });
    },
    link(index) {
      this.activeIndexs = index;
      this.$refs.linkaddres.modals = true;
    },
    getListHeader() {
      this.loading = true;
      groupDataHeaderApi({ config_name: this.name }, 'setting/sign_data/header')
        .then((res) => {
          let data = res.data;
          let header = data.header;
          let index = [];
          this.columns1 = header;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    getContent(val) {
      this.formValidate.content = val;
    },
    // 提交数据
    onsubmit(name) {
      this.formValidate.content = this.content;
      setAgreement(this.formValidate)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    //详情
    getAgreement() {
      getAgreement()
        .then(async (res) => {
          let data = res.data;
          this.formValidate = {
            content: data.content,
          };
          this.content = data.content;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.save {
  width: 100%;
  margin: 0 auto;
  text-align: center;
  background-color: #FFF;
  bottom: 0;
  padding: 16px;
  border-top: 3px solid #f5f7f9;
}

.form {
  .goodsTitle {
    margin-bottom: 25px;
  }

  .goodsTitle ~ .goodsTitle {
    margin-top: 20px;
  }

  .goodsTitle .title {
    border-bottom: 2px solid #1890ff;
    padding: 0 8px 12px 5px;
    color: #000;
    font-size: 14px;
  }

  .goodsTitle .icons {
    font-size: 15px;
    margin-right: 8px;
    color: #999;
  }

  .add {
    font-size: 12px;
    color: #1890ff;
    padding: 0 12px;
    cursor: pointer;
  }

  .radio {
    margin-right: 20px;
  }

  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }

  .iconfont {
    color: #898989;
  }

  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;
  }

  .pictrue img {
    width: 100%;
    height: 100%;
  }
}

.agreement-box {
  width: 310px;
  height: 550px;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0);
  border: 1px solid #EEEEEE;
  opacity: 1;
  position: relative;

  .template {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    border-radius: 10px;
    background-color: #817e81;
  }

  .htmls_box {
    font-size: 12px;
    width: 259px;
    height: 430px;
    border-radius: 10px;
    background-color: #fff;
    position: absolute;
    top: 58px;
    left: 26px;

    .htmls_top {
      position: absolute;
      top: 8px;
      left: 0;
      height: 34px;
      text-align: center;
      width: 100%;
      line-height: 35px;
      font-weight: 600;
      font-size: 20px;
    }

    .htmls_font {
      position: absolute;
      bottom: 0;
      left: 0;
      padding: 15px 15px;
      text-align: center;
      width: 100%;

      div {
        height: 35px;
        line-height: 35px;
        border-radius: 20px;
      }

      .ok {
        background-color: #f33316;
        color: #FFFFFF;
      }
    }

    .htmls {
      position: absolute;
      background-color: #fff;
      top: 50px;
      left: 0;
      width: 259px;
      height: 281px;
      border-radius: 4px;
      overflow: auto;
      padding: 5px 15px;
      word-break: break-word;
    }

    .htmls::-webkit-scrollbar {
      display: none;
    }
  }
}

.item {
  margin-right: 15px;
  border: 1px dashed #dbdbdb;
  padding-bottom: 10px;
  padding-right: 15px;
  padding-top: 20px;
}

.swiperimg {
  width: 310px;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;

  img {
    width: 100%;
    height: 100%;
  }
}

.title {
  padding: 0 0 13px 0;
  font-weight: bold;
  font-size: 15px;
  border-left: 2px solid #1890FF;
  height: 23px;
  padding-left: 10px;
}

.content {
  // width 510px;
  .right-box {
    margin-left: 40px;
  }
}

.box {
  border-top: 3px solid #f5f7f9;
  padding: 10px;
  padding-top: 25px;
  width: 100%;

  .save {
    background-color: #1890FF;
    color: #FFFFFF;
    width: 71px;
    height: 30px;
    margin: 0 auto;
    text-align: center;
    line-height: 30px;
    cursor: pointer;
  }
}

.iframe {
  margin-left: 20px;
  position: relative;
  width: 310px;
  // height: 550px;
  background: #FFFFFF;
  border: 1px solid #EEEEEE;
  opacity: 1;
  border-radius: 10px;
}

.iconfont {
  color: #DDDDDD;
  font-size: 28px;
}

.box-wrapper {
  display: flex;
  flex-wrap: nowrap;
  padding: 20px;
  background-color: #fff;
  border-radius: 5px;
  margin: 20px;
}
</style>
