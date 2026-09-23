<template>
  <div>
    <pages-header
      :title="$route.meta.title"
      :backUrl="routePre + '/marketing/channel_code/channelCodeIndex'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form :model="formData" label-width="100px" :rules="ruleValidate">
        <el-form-item label="渠道码名称：">
          <el-input
            clearable
            v-model="formData.name"
            placeholder="请输入渠道码名称"
            class="content_width"
            maxlength="20"
            show-word-limit
          ></el-input>
        </el-form-item>
        <el-form-item label="渠道码分组：">
          <el-select clearable v-model="formData.cate_id" class="content_width">
            <el-option
              :value="item.id"
              v-for="(item, index) in labelSort"
              :key="index"
              :label="item.cate_name"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="用户标签：">
          <div style="display: flex">
            <div class="labelInput acea-row row-between-wrapper" v-db-click @click="openLabel">
              <div style="width: 90%">
                <div v-if="dataLabel.length">
                  <el-tag closable v-for="(item, index) in dataLabel" @close="closeLabel(item)" :key="index">{{
                    item.label_name
                  }}</el-tag>
                </div>
                <span class="span" v-else>选择用户关联标签</span>
              </div>
              <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
            </div>
            <span class="addfont" v-db-click @click="addLabel">新增标签</span>
          </div>
        </el-form-item>
        <el-form-item label="关联推广员：">
          <div class="picBox" v-db-click @click="customer">
            <div class="pictrue" v-if="formData.avatar">
              <img v-lazy="formData.avatar" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <i class="el-icon-user" style="font-size: 24px"></i>
            </div>
          </div>
          <div class="trip">扫码注册的新用户,将自动成为此推广员的下级,与分销推广功能一致</div>
        </el-form-item>
        <el-form-item label="有效期：">
          <el-radio-group v-model="isReceiveTime">
            <el-radio :label="0" :value="0">永久</el-radio>
            <el-radio :label="1" :value="1">有效期</el-radio>
          </el-radio-group>
          <div v-show="isReceiveTime">
            <el-input-number
              :controls="false"
              :max="10000"
              :precision="0"
              v-model="formData.time"
              placeholder="请输入天数"
              class="input-number-unit-class"
              class-unit="天"
            ></el-input-number>
          </div>
          <div class="trip">临时码过期后不能再扫码,永久二维码最大创建数量为10万个</div>
        </el-form-item>
        <el-form-item label="回复内容：">
          <el-radio-group v-model="formData.type">
            <el-radio label="text" value="text">文字内容</el-radio>
            <el-radio label="voice" value="voice">声音消息</el-radio>
            <el-radio label="image" value="image">图片消息</el-radio>
            <el-radio label="news" value="news">图文消息</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="消息内容：" prop="content" v-if="formData.type === 'text' || formData.type === 'url'">
          <el-input
            type="textarea"
            rows="2"
            clearable
            v-model="formData.content.content"
            :placeholder="formData.type === 'text' ? '请填写消息内容' : '请填写网址链接'"
            class="content_width"
          ></el-input>
        </el-form-item>
        <el-form-item label="选取图文：" v-if="formData.type === 'news'">
          <el-button v-db-click @click="modals = true">选择图文消息</el-button>
          <div class="news-box" v-if="formData.content.list.title">
            <img class="news_pic" :src="formData.content.list.image_input[0]" />
            <span>{{ formData.content.list.title }}</span>
            <i class="el-icon-error del_icon" v-db-click @click="delContent"></i>
          </div>
        </el-form-item>
        <el-form-item
          :label="formData.type === 'image' ? '图片地址：' : '语音地址：'"
          prop="src"
          v-if="formData.type === 'image' || formData.type === 'voice'"
        >
          <div class="acea-row row-middle">
            <el-input
              readonly="readonly"
              placeholder="请填入链接地址"
              class="content_width mr15"
              v-model="formData.content.src"
            />
            <el-upload
              :show-file-list="false"
              :action="fileUrl"
              :on-success="handleSuccess"
              :format="formData.type === 'image' ? formatImg : formatVoice"
              :max-size="2048"
              :headers="header"
              :on-format-error="handleFormatError"
              :on-exceeded-size="handleMaxSize"
              class="mr20"
              style="margin-top: 1px"
              accept="image/*,.mp3"
              :before-upload="beforeUpload"
            >
              <el-button type="primary">上传</el-button>
            </el-upload>
          </div>
          <span v-show="formData.type === 'image'">文件最大2Mb，支持bmp/png/jpeg/jpg/gif格式</span>
          <span v-show="formData.type === 'voice'">文件最大2Mb，支持mp3格式,播放长度不超过60s</span>
        </el-form-item>
        <el-form-item>
          <el-button class="submit" type="primary" v-db-click @click="save" :loading="loading" :disabled="disabled"
            >立即提交</el-button
          >
        </el-form-item>
      </el-form>
    </el-card>
    <el-dialog v-model="customerShow" title="请选择商城用户" :show-close="true" width="1000px">
      <customerInfo v-if="customerShow" @imageObject="imageObject"></customerInfo>
    </el-dialog>
    <!--图文消息 -->
    <el-dialog v-model="modals" title="发送消息" width="1200px" class="modelBox">
      <news-category
        v-if="modals"
        @getCentList="getCentList"
        :scrollerHeight="scrollerHeight"
        :contentTop="contentTop"
        :contentWidth="contentWidth"
        :maxCols="maxCols"
      ></news-category>
    </el-dialog>
    <el-dialog
      v-model="labelShow"
      scrollable
      title="请选择用户标签"
      :closable="false"
      width="540px"
      :footer-hide="true"
      :mask-closable="false"
    >
      <userLabel ref="userLabelRef" @activeData="activeData" @close="labelClose"></userLabel>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import userLabel from '@/components/labelList';

import goodsList from '@/components/goodsList/index';
import newsCategory from '@/components/newsCategory/index';
import { labelListApi } from '@/api/product';
import { userLabelAddApi } from '@/api/user';
import { wechatQrcodeSaveApi, wechatQrcodeTree, wechatQrcodeDetail } from './../api';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import customerInfo from '@/components/customerInfo';
import { isPicUpload, isVoiceUpload } from '@/utils';
import modalForm from '@/utils/modalForm';

defineOptions({ name: 'createCode' });

const route = useRoute();
const router = useRouter();
const routePre = Setting.routePre;

const userLabelRef = ref(null);
const customerShow = ref(false);
const labelShow = ref(false);
const disabled = ref(false);
const maxCols = 4;
const labelSelect = ref([]);

const scrollerHeight = '600';
const contentTop = '10';
const contentWidth = '98%';
const formatImg = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
const formatVoice = ['mp3', 'wma', 'wav', 'amr'];
const header = ref({});
const fileUrl = Setting.apiBaseURL + '/file/upload/1';

const formData = ref({
  name: '',
  type: 'text',
  time: undefined,
  label_id: [],
  image: '',
  cate_id: '',
  content: {
    content: '',
    src: '',
    list: {},
  },
});
const labelSort = ref([]);
const isReceiveTime = ref(0);
const modals = ref(false);
const ruleValidate = {
  name: [
    {
      required: true,
      message: '请填写二维码名称',
      trigger: 'blur',
    },
  ],
  cate_id: [
    {
      required: true,
      message: '请选择二维码分组',
      trigger: 'change',
    },
  ],
};
const id = ref(0);
const dataLabel = ref([]);
const loading = ref(false);

getUserLabelAll();
getLabelList();
getToken();
if (route.query.id) {
  id.value = route.query.id;
  getDetail();
}

function beforeUpload(file) {
  if (formData.value.type === 'image') {
    return isPicUpload(file);
  }
  if (formData.value.type === 'voice') {
    return isVoiceUpload(file);
  }
}
function activeData(val) {
  labelShow.value = false;
  dataLabel.value = val;
}
// 标签弹窗关闭
function labelClose() {
  labelShow.value = false;
}
function openLabel(row) {
  labelShow.value = true;
  if (dataLabel.value.length) userLabelRef.value.userLabel(JSON.parse(JSON.stringify(dataLabel.value)));
}
function closeLabel(label) {
  let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
  dataLabel.value.splice(index, 1);
}
function getDetail() {
  wechatQrcodeDetail(id.value).then((res) => {
    formData.value = res.data;
    if (res.data.time > 0) {
      isReceiveTime.value = 1;
    }
    if (res.data.label_id.length) {
      dataLabel.value = res.data.label_id;
    }
  });
}
function customer() {
  customerShow.value = true;
}
function addLabel() {
  modalForm(userLabelAddApi(0)).then(() => getLabelList());
}
// 用户标签
function getLabelList() {
  labelListApi()
    .then((res) => {
      labelSelect.value = res.data.list;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function imageObject(e) {
  customerShow.value = false;
  formData.value.uid = e.uid;
  formData.value.avatar = e.image;
}
function getCentList(val) {
  formData.value.content.list = val.new[0];
  modals.value = false;
}
function delContent() {
  formData.value.content.list = {};
}
// 上传成功
function handleSuccess(res, file) {
  if (res.status === 200) {
    formData.value.content.src = res.data.src;
    ElMessage.success(res.msg);
  } else {
    ElMessage.error(res.msg);
  }
}
function handleFormatError(file) {
  if (formData.value.type === 'image') {
    ElMessage.warning('请上传bmp/png/jpeg/jpg/gif格式的图片');
  } else {
    ElMessage.warning('请上传mp3/wma/wav/amr格式的语音');
  }
}
function handleMaxSize(file) {
  ElMessage.warning('请上传文件2M以内的文件');
}
// 上传头部token
function getToken() {
  header.value['Authori-zation'] = 'Bearer ' + getCookies('token');
}
function selectMenu(name) {
  formData.value.type = name;
}
// 获取分类
function getUserLabelAll() {
  wechatQrcodeTree().then((res) => {
    let data = res.data.data;
    labelSort.value = data;
  });
}
// 创建
function save() {
  if (!formData.value.name) {
    return ElMessage.error('请输入二维码名称');
  }
  if (!formData.value.cate_id) {
    return ElMessage.error('请选择分组');
  }
  if (!dataLabel.value.length) {
    return ElMessage.error('请选择用户标签');
  } else {
    let ids = [];
    dataLabel.value.map((i) => {
      ids.push(i.id);
    });
    formData.value.label_id = ids;
  }
  if (!formData.value.uid) {
    return ElMessage.error('请选择推广员');
  }
  if (isReceiveTime.value) {
    if (formData.value.time < 1) {
      return ElMessage.error('使用有效期限不能小于1天');
    }
  } else {
    formData.value.time = 0;
  }
  if (formData.value.type === 'text' || formData.value.type === 'url') {
    if (!formData.value.content.content.trim()) {
      return ElMessage.error('请输入内容');
    }
  }
  if (formData.value.type === 'voice' || formData.value.type === 'image') {
    if (!formData.value.content.src.trim()) {
      return ElMessage.error('请先上传消息');
    }
  }
  if (formData.value.type === 'news') {
    if (!formData.value.content.list.title.trim()) {
      return ElMessage.error('请选择图文消息');
    }
  }
  disabled.value = false;
  loading.value = true;
  wechatQrcodeSaveApi(id.value, formData.value)
    .then((res) => {
      disabled.value = true;
      ElMessage.success(res.msg);
      setTimeout(() => {
        router.push({
          path: routePre + '/marketing/channel_code/channelCodeIndex',
        });
      }, 1000);
    })
    .catch((err) => {
      disabled.value = true;
      ElMessage.error(err.msg);
    });
}
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.ivu-row {
  border: 1px solid #f2f2f2;
}
.ivu-form-item {
  padding: 10px 0;
  max-width: 1100px;
}
.ivu-form :deep(.ivu-form-item-label) {
  font-weight: 700;
  font-size: 14px !important;
}
.ivu-input-wrapper {
  width: 320px;
}
.ivu-radio-wrapper {
  margin-right: 30px;
  font-size: 14px !important;
}
.ivu-radio-wrapper :deep(.ivu-radio) {
  margin-right: 10px;
}
.ivu-input-number {
  width: 160px;
}
.ivu-date-picker {
  width: 320px;
}
.ivu-icon-ios-camera-outline {
  width: 58px;
  height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background-color: rgba(0, 0, 0, 0.02);
  line-height: 58px;
  cursor: pointer;
  vertical-align: middle;
}
.upload-list {
  width: 58px;
  height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  margin-right: 15px;
  display: inline-block;
  position: relative;
  cursor: pointer;
  vertical-align: middle;
}
:deep(.el-tag) {
  margin-right: 5px;
}
.upload-list img {
  display: block;
  width: 100%;
  height: 100%;
}
.ivu-icon-ios-close-circle {
  position: absolute;
  top: 0;
  right: 0;
  transform: translate(50%, -50%);
}
.modelBox :deep(.ivu-modal-body) {
  padding: 0 16px 16px 16px !important;
}
.header-save {
  display: flex;
  justify-content: space-between;
}
.trip {
  font-size: 12px;
  color: #ccc;
}

textarea {
  padding: 0 5px;
  border-radius: 3px;
  border-color: #c5c8ce;
  outline-color: #2d8cf0;
  font-size: 14px;
}
.picBox {
  display: inline-block;
  cursor: pointer;
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
  .iconfont {
    color: #898989;
  }
}
.addfont {
  display: inline-block;
  font-size: 12px;
  font-weight: 400;
  color: var(--prev-color-primary);
  margin-left: 14px;
  cursor: pointer;
  margin-left: 10px;
}
.iconxiayi {
  font-size: 14px;
}
.ivu-page-header-title {
  padding-bottom: 0;
}
.news-box {
  width: 200px;
  background-color: #f2f2f2;
  padding: 10px;
  border-radius: 10px;
  margin-top: 20px;
  position: relative;
  .news_pic {
    width: 100%;
    height: 150px;
  }
  .del_icon {
    position: absolute;
    right: -8px;
    top: -8px;
    cursor: pointer;
  }
}
.labelInput {
  border: 1px solid #dcdee2;
  width: 460px;
  padding: 0 15px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  .span {
    font-size: 12px;
    color: #c5c8ce;
  }
  .ivu-icon-ios-arrow-down {
    font-size: 14px;
    color: #808695;
  }
}
</style>
