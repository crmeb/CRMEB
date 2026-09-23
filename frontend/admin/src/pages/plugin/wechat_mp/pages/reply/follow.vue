<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/app/wechat/reply/keyword'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt-16">
      <!-- 公众号设置 -->
      <el-row :gutter="24">
        <el-col :span="24" class="ml40">
          <!-- 预览功能 -->
          <el-col :span="24">
            <el-col :xl="7" :lg="7" :md="22" :sm="22" :xs="22" class="left mb15">
              <img class="top" src="../../../../../assets/images/mobilehead.png" />
              <img class="bottom" src="../../../../../assets/images/mobilefoot.png" />
              <div class="centent">
                <div class="time-wrapper"><span class="time">9:36</span></div>
                <div class="view-item text-box clearfix" v-if="formValidate.type !== 'news'">
                  <div class="avatar fl"><img src="../../../../../assets/images/head.gif" /></div>
                  <div class="box-content fl">
                    <span v-text="formValidate.data.content" v-if="formValidate.type === 'text'"></span>
                    <div class="box-content_pic" v-if="formValidate.data.src">
                      <img :src="formValidate.data.src ? imgUrl + formValidate.data.src : ''" />
                    </div>
                  </div>
                </div>
                <div v-if="formValidate.type === 'news'">
                  <div v-for="(j, i) in formValidate.data.list" :key="i">
                    <div v-if="i === 0">
                      <div
                        class="news_pic"
                        :style="{ backgroundImage: 'url(' + j.image_input[0] + ')', backgroundSize: '100% 100%' }"
                      ></div>
                      <span class="news_sp">{{ j.title }}</span>
                    </div>
                    <div v-else class="news_cent">
                      <span class="news_sp1" v-if="j.synopsis">{{ j.title }}</span>
                      <div class="news_cent_img" v-if="j.image_input.length !== 0"><img :src="j.image_input[0]" /></div>
                    </div>
                  </div>
                </div>
              </div>
            </el-col>
            <el-col :xl="11" :lg="12" :md="22" :sm="22" :xs="22">
              <el-col :span="24" class="userAlert">
                <div class="box-card right">
                  <el-form
                    ref="formValidateRef"
                    :model="formValidate"
                    :rules="ruleValidate"
                    label-width="100px"
                    class="mt20"
                    @submit.prevent
                  >
                    <el-form-item label="关键字：" prop="val" v-if="$route.params.id">
                      <div class="arrbox">
                        <!--:closable="$route.params.id==='0'? true : false"-->
                        <el-tag
                          @close="handleClose(item)"
                          :name="item"
                          :closable="true"
                          v-for="(item, index) in labelarr"
                          :key="index"
                          >{{ item }}
                        </el-tag>
                        <!--:readonly="$route.params.id!=='0'"-->
                        <input
                          class="arrbox_ip"
                          v-model="val"
                          placeholder="输入后回车"
                          style="width: 90%"
                          @keyup.enter="addlabel"
                        />
                      </div>
                    </el-form-item>
                    <el-form-item label="消息状态：">
                      <el-radio-group v-model="formValidate.status">
                        <el-radio :label="1" :value="1">启用</el-radio>
                        <el-radio :label="0" :value="0">禁用</el-radio>
                      </el-radio-group>
                    </el-form-item>
                    <el-form-item label="消息类型：" prop="type">
                      <el-select
                        v-model="formValidate.type"
                        placeholder="请选择规则状态"
                        style="width: 90%"
                        @change="RuleFactor(formValidate.type)"
                      >
                        <el-option value="text" label="文字消息"></el-option>
                        <el-option value="image" label="图片消息"></el-option>
                        <el-option value="news" label="图文消息"></el-option>
                        <el-option value="voice" label="声音消息"></el-option>
                      </el-select>
                    </el-form-item>
                    <el-form-item label="消息内容：" prop="content" v-if="formValidate.type === 'text'">
                      <el-input
                        v-model="formValidate.data.content"
                        placeholder="请填写消息内容"
                        style="width: 90%"
                        type="textarea"
                        :rows="4"
                      ></el-input>
                    </el-form-item>
                    <el-form-item label="选取图文：" v-if="formValidate.type === 'news'">
                      <el-button v-db-click @click="changePic">选择图文消息</el-button>
                    </el-form-item>

                    <el-form-item
                      :label="formValidate.type === 'image' ? '图片地址：' : '语音地址：'"
                      prop="src"
                      v-if="formValidate.type === 'image' || formValidate.type === 'voice'"
                    >
                      <div class="acea-row row-middle">
                        <el-input
                          readonly="readonly"
                          placeholder="default size"
                          style="width: 75%"
                          class="mr15"
                          v-model="formValidate.data.src"
                        />
                        <el-upload
                          :show-file-list="false"
                          :action="fileUrl"
                          :on-success="handleSuccess"
                          :accept="formValidate.type === 'image' ? formatImg : formatVoice"
                          :max-size="2048"
                          :headers="header"
                          :on-format-error="handleFormatError"
                          :on-exceeded-size="handleMaxSize"
                          :before-upload="beforeUpload"
                          class="mr20"
                          style="margin-top: 1px"
                        >
                          <el-button type="primary">上传</el-button>
                        </el-upload>
                      </div>
                      <span v-show="formValidate.type === 'image'">文件最大2Mb，支持bmp/png/jpeg/jpg/gif格式</span>
                      <span v-show="formValidate.type === 'voice'">文件最大2Mb，支持mp3格式,播放长度不超过60s</span>
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary" class="mr20" v-db-click @click="submenus('formValidate')"
                        >保存并发布
                      </el-button>
                    </el-form-item>
                  </el-form>
                </div>
              </el-col>
              <!-- <el-col :span="24">
                <div class="acea-row row-center">
                  <el-button type="primary" class="mr20" v-db-click @click="submenus('formValidate')">保存并发布 </el-button>
                </div>
              </el-col> -->
            </el-col>
          </el-col>
        </el-col>
      </el-row>
    </el-card>

    <!--图文消息 -->
    <el-dialog v-model="modals" title="发送消息" width="1200px" :lock-scroll="false" class="modelBox">
      <news-category
        v-if="modals"
        @getCentList="getCentList"
        :scrollerHeight="scrollerHeight"
        :contentTop="contentTop"
        :contentWidth="contentWidth"
        :maxCols="maxCols"
      ></news-category>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import Setting from '@/setting';
import { replyApi, keywordsinfoApi } from '../../api';
import newsCategory from '@/components/newsCategory/index';
import { getCookies } from '@/libs/util';

defineOptions({ name: 'follow' });

const route = useRoute();
const router = useRouter();

const delfromData = ref({});
const isShow = ref(false);
const maxCols = ref(4);
const scrollerHeight = ref('600');
const contentTop = ref('130');
const contentWidth = ref('98%');
const modals = ref(false);
const val = ref('');
const formatImg = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
const formatVoice = ['mp3', 'wma', 'wav', 'amr'];
const header = ref({});
const formValidate = ref({
  status: -1,
  type: '',
  key: route.params.key || '',
  data: {
    content: '',
    src: '',
    list: [],
  },
  id: 0,
});
const fileUrl = Setting.apiBaseURL + '/file/upload/1';
const labelarr = ref([]);

const validateContent = (rule, value, callback) => {
  if (formValidate.value.type === 'text') {
    if (formValidate.value.data.content === '') {
      callback(new Error('请填写规则内容'));
    } else {
      callback();
    }
  }
};
const validateSrc = (rule, value, callback) => {
  if (formValidate.value.type === 'image' && formValidate.value.data.src === '') {
    callback(new Error('请上传'));
  } else {
    callback();
  }
};
const validateVal = (rule, value, callback) => {
  if (labelarr.value.length === 0) {
    callback(new Error('请输入后回车'));
  } else {
    callback();
  }
};
const ruleValidate = ref({
  val: [{ required: true, validator: validateVal, trigger: 'change' }],
  type: [{ required: true, message: '请选择消息类型', trigger: 'change' }],
  content: [{ required: true, validator: validateContent, trigger: 'blur' }],
  src: [{ required: true, validator: validateSrc, trigger: 'change' }],
});

const formValidateRef = ref(null);
const pageHeader = ref(null);

const imgUrl = computed(() => {
  const search = '/adminapi/';
  const start = Setting.apiBaseURL.indexOf(search);
  return Setting.apiBaseURL.substring(0, start); // 截取字符串
});

watch(route, (to, from) => {
  if (route.params.key || route.params.id !== '0') {
    formValidate.value.key = route.params.key;
    details();
  } else {
    labelarr.value = [];
    formValidateRef.value.resetFields();
  }
});

onMounted(() => {
  getToken();
  if (route.params.key || (route.params.id && route.params.id !== '0')) {
    details();
  }
});

function beforeUpload(file) {}
function getCentList(v) {
  formValidate.value.data.list = v.new;
  modals.value = false;
}
function addlabel() {
  let count = labelarr.value.indexOf(val.value);
  if (count === -1) {
    labelarr.value.push(val.value);
  }
  val.value = '';
}
function handleClose(name) {
  const index = labelarr.value.indexOf(name);
  labelarr.value.splice(index, 1);
}
// 详情
function details() {
  let url = '';
  let data = {};
  if (route.params.id) {
    url = 'app/wechat/keyword/' + route.params.id;
    data = {};
  } else {
    url = 'app/wechat/reply';
    data = {
      key: {
        key: formValidate.value.key,
      },
    };
  }
  keywordsinfoApi(url, data)
    .then(async (res) => {
      if (res.data.info.data instanceof Array) {
        formValidate.value.status = 0;
        return;
      }
      let info = res.data.info || {};
      let data = info.data || {};
      formValidate.value = {
        status: info.status,
        type: info.type,
        key: info.key,
        data: {
          content: data.content,
          src: data.src,
          list: data.list,
        },
        id: info.id,
      };
      if (route.params.id) {
        labelarr.value = formValidate.value.key.split(',') || [];
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 选择图文
function changePic() {
  modals.value = true;
}
// 下拉选择
function RuleFactor(type) {
  switch (type) {
    case 'text':
      formValidate.value.data.src = '';
      formValidate.value.data.list = [];
      break;
    case 'news':
      formValidate.value.data.src = '';
      formValidate.value.data.content = '';
      break;
    default:
      formValidate.value.data.list = [];
      formValidate.value.data.content = '';
      formValidate.value.data.src = '';
  }
}
// 上传头部token
function getToken() {
  header.value['Authori-zation'] = 'Bearer ' + getCookies('token');
}
// 上传成功
function handleSuccess(res, file) {
  if (res.status === 200) {
    formValidate.value.data.src = res.data.src;
    ElMessage.success(res.msg);
  } else {
    ElMessage.error(res.msg);
  }
}
function handleFormatError(file) {
  if (formValidate.value.type === 'image') {
    ElMessage.warning('请上传bmp/png/jpeg/jpg/gif格式的图片');
  } else {
    ElMessage.warning('请上传mp3/wma/wav/amr格式的语音');
  }
}
function handleMaxSize(file) {
  ElMessage.warning('请上传文件2M以内的文件');
}
// 保存
function submenus(name) {
  formValidateRef.value.validate((valid) => {
    if (valid) {
      let data = {};
      if (route.params.id) {
        formValidate.value.key = labelarr.value.join(',');
        data = {
          url: 'app/wechat/keyword/' + route.params.id,
          key: formValidate.value,
        };
      } else {
        data = {
          url: 'app/wechat/keyword/' + formValidate.value.id,
          key: formValidate.value,
        };
      }
      replyApi(data)
        .then(async (res) => {
          operation();
          ElMessage.success(res.msg);
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
// 保存成功操作
function operation() {
  if (route.params.id && route.params.id === '0') {
    ElMessageBox({
      title: '提示',
      message: '是否继续添加',
      showCancelButton: true,
      cancelButtonText: '否',
      confirmButtonText: '是',
      iconClass: 'el-icon-warning',
      confirmButtonClass: 'btn-custom-cancel',
    })
      .then(() => {
        setTimeout(() => {
          labelarr.value = [];
          val.value = '';
          formValidateRef.value.resetFields();
        }, 1000);
      })
      .catch(() => {
        setTimeout(() => {
          router.push({ path: Setting.routePre + '/app/wechat/reply/keyword' });
        }, 500);
      });
  } else if (route.params.id && route.params.id !== '0') {
    router.push({ path: Setting.routePre + '/app/wechat/reply/keyword' });
  }
}
</script>

<style lang="scss" scoped>
* {
  -moz-user-select: none; /* 火狐 */
  -webkit-user-select: none; /* webkit浏览器 */
  -ms-user-select: none; /* IE10 */
  -khtml-user-select: none; /* 早期浏览器 */
  user-select: none;
}
.arrbox {
  background-color: white;
  font-size: 12px;
  border: 1px solid #dcdee2;
  border-radius: 6px;
  margin-bottom: 0px;
  padding: 0 5px;
  text-align: left;
  box-sizing: border-box;
  width: 90%;
  .el-tag {
    margin-right: 3px;
  }
}
.arrbox_ip {
  font-size: 12px;
  border: none;
  box-shadow: none;
  outline: none;
  background-color: transparent;
  padding: 0;
  margin: 0;
  width: auto !important;
  max-width: inherit;
  min-width: 80px;
  vertical-align: top;
  height: 30px;
  color: #34495e;
  margin: 2px;
  line-height: 30px;
}
.left {
  min-width: 390px;
  min-height: 550px;
  position: relative;
  padding-left: 40px;
}
.top {
  position: absolute;
  top: 0px;
}
.bottom {
  position: absolute;
  bottom: 0px;
}
.centent {
  background: #f4f5f9;
  min-height: 438px;
  position: absolute;
  top: 63px;
  width: 320px;
  height: 60%;
  overflow-y: auto;
  padding: 15px;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
}
.right {
  background: #fff;
  min-height: 300px;
}
.box-content {
  position: relative;
  max-width: 60%;
  min-height: 40px;
  margin-left: 15px;
  padding: 10px;
  box-sizing: border-box;
  border: 1px solid #ccc;
  word-break: break-all;
  word-wrap: break-word;
  line-height: 1.5;
  border-radius: 5px;
}
.box-content_pic {
  width: 100%;
}
.box-content_pic img {
  width: 100%;
  height: auto;
}
.box-content:before {
  content: '';
  position: absolute;
  left: -13px;
  top: 11px;
  display: block;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-top: 10px solid #ccc;
  -webkit-transform: rotate(90deg);
  transform: rotate(90deg);
}
.box-content:after {
  content: '';
  content: '';
  position: absolute;
  left: -12px;
  top: 11px;
  display: block;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-top: 10px solid #f5f5f5;
  -webkit-transform: rotate(90deg);
  transform: rotate(90deg);
}
.time-wrapper {
  margin-bottom: 10px;
  text-align: center;
}
.time {
  display: inline-block;
  color: #f5f5f5;
  background: rgba(0, 0, 0, 0.3);
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 12px;
}
.text-box {
  display: flex;
}
.avatar {
  width: 40px;
  height: 40px;
}
.avatar img {
  width: 100%;
  height: 100%;
}
.modelBox {
  :deep(.ivu-modal-body) {
    padding: 0 16px 16px 16px !important;
  }
}
.news_pic {
  width: 100%;
  height: 150px;
  overflow: hidden;
  position: relative;
  background-size: 100%;
  background-position: center center;
  border-radius: 5px 5px 0 0;
  padding: 10px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.news_sp {
  font-size: 12px;
  color: #000000;
  background: #fff;
  width: 100%;
  height: 38px;
  line-height: 38px;
  padding: 0 12px;
  box-sizing: border-box;
  display: block;
}
.news_cent {
  width: 100%;
  height: auto;
  background: #fff;
  border-top: 1px dashed #eee;
  display: flex;
  padding: 10px;
  box-sizing: border-box;
  justify-content: space-between;
  .news_sp1 {
    font-size: 12px;
    color: #000000;
    width: 71%;
  }
  .news_cent_img {
    width: 81px;
    height: 46px;
    border-radius: 6px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
    }
  }
}
</style>
