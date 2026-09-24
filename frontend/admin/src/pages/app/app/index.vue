<template>
  <div :style="bgcolors">
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          维护 APP 服务协议与隐私政策内容，右侧实时预览移动端弹窗效果。
        </span>
      </div>
      <div style="float: right">
        <el-button class="bnt" type="primary" v-db-click @click="onsubmit('formValidate')">保存</el-button>
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
          <div>
            <div v-bind="grid">
              <div class="title">隐私权限页面展示：</div>
            </div>
          </div>
          <div>
            <el-form
              class="form"
              ref="formValidateRef"
              :model="formValidate"
              :rules="ruleValidate"
              :label-position="labelPosition"
              @submit.prevent
            >
              <div class="goodsTitle acea-row"></div>
              <el-form-item label="" style="margin: 0px">
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.content"
                  @editorContent="getEditorContent"
                ></WangEditor>
              </el-form-item>
            </el-form>
          </div>
        </div>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import WangEditor from '@/components/wangEditor/index.vue';
import Setting from '@/setting';
import { getColorChange } from '@diy/api';
import { groupDataHeaderApi, getAgreement, setAgreement } from '@/api/system';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
import { getCookies } from '@/libs/util';
import { useMediaStore } from '@/store/module/media';
import { useLayoutStore } from '@/store/module/layout';

defineOptions({ name: 'list' });

const mediaStore = useMediaStore();
const layoutStore = useLayoutStore();

const formValidateRef = ref(null);
const linkaddres = ref(null);

const formValidate = ref({
  content: '',
});
const ruleValidate = reactive({});
const myConfig = reactive({
  autoHeightEnabled: false, // 编辑器不自动被内容撑高
  initialFrameHeight: 500, // 初始容器高度
  initialFrameWidth: '100%', // 初始容器宽度
  UEDITOR_HOME_URL: '/UEditor/',
  serverUrl: '',
});
const a = ref(1); //判断的隐私协议
const guide = ref(0);
const bgimg = ref(0);
const columns1 = ref([]);
const bgCol = ref('');
const name = ref('');
const content = ref('');
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const sginList = ref([]);
const progress = ref(0); // 进度条默认0
const url = ref('');
const BaseURL = Setting.apiBaseURL.replace(/adminapi/, '');
const pageId = ref(0);
const groupAll = ref([]);
const activeIndex = ref(0);
const sortName = ref(null);
const activeIndexs = ref(0);
const cmsList = ref([]);
const loadingExist = ref(false);
const formItem = reactive({
  time: '',
  type: 'pic',
  status: 1,
  value: [],
  video_link: '',
});
const header = ref({});
const type = ref(0);

const bgcolors = computed(() => {
  return {
    '--color-theme': bgCol.value,
  };
});
const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));
const menuCollapse = computed(() => layoutStore.menuCollapse);

// created
color();
a.value = 1;
guide.value = 0;
getAgreementData();

function linkUrl(e) {
  tabList.list[activeIndexs.value].link = e;
}
function getEditorContent(data) {
  content.value = data;
}
function color() {
  getColorChange('color_change').then((res) => {
    switch (res.data.status) {
      case 1:
        bgCol.value = '#3875EA';
        bgimg.value = 1;
        break;
      case 2:
        bgCol.value = '#00C050';
        bgimg.value = 2;
        break;
      case 3:
        bgCol.value = '#E93323';
        bgimg.value = 3;
        break;
      case 4:
        bgCol.value = '#FF448F';
        bgimg.value = 4;
        break;
      case 5:
        bgCol.value = '#FE5C2D';
        bgimg.value = 5;
        break;
    }
  });
}
function link(index) {
  activeIndexs.value = index;
  linkaddres.value.modals = true;
}
function getListHeader() {
  loading.value = true;
  groupDataHeaderApi({ config_name: name.value }, 'setting/sign_data/header')
    .then((res) => {
      let data = res.data;
      let header = data.header;
      let index = [];
      columns1.value = header;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function getContent(val) {
  formValidate.value.content = val;
}
// 提交数据
function onsubmit(name) {
  formValidate.value.content = content.value;
  setAgreement(formValidate.value)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
//详情
function getAgreementData() {
  getAgreement()
    .then(async (res) => {
      let data = res.data;
      formValidate.value = {
        content: data.content,
      };
      content.value = data.content;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.save {
  width: 100%;
  margin: 0 auto;
  text-align: center;
  background-color: #fff;
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
    border-bottom: 2px solid var(--prev-color-primary);
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
    color: var(--prev-color-primary);
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
  border: 1px solid #eeeeee;
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
        color: #ffffff;
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
  border-left: 2px solid var(--prev-color-primary);
  height: 23px;
  padding-left: 10px;
}
.content {
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
    background-color: var(--prev-color-primary);
    color: #ffffff;
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

  background: #ffffff;
  border: 1px solid #eeeeee;
  opacity: 1;
  border-radius: 10px;
}
.iconfont {
  color: #dddddd;
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
