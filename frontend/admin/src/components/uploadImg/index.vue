<template>
  <div>
    <el-dialog
      title="上传图片"
      v-model="uploadModal"
      :append-to-body="true"
      :width="isIframe ? '100%' : 'min(1024px, calc(100vw - 24px))'"
      :fullscreen="isIframe"
      :close-on-click-modal="false"
      :close-on-press-escape="!loading && !preparing"
      :show-close="!loading && !preparing"
      :before-close="beforeClose"
      @close="closed"
    >
      <div class="main" v-loading="loading || preparing > 0">
        <el-form :model="ruleForm" :rules="rules" ref="formRef" label-width="100px" class="demo-ruleForm">
          <el-form-item label="上传方式：" prop="type">
            <el-radio-group
              v-model="ruleForm.type"
              :disabled="loading || preparing > 0"
              @change="radioChange(ruleForm.type)"
            >
              <el-radio :label="0" :value="0">本地上传</el-radio>
              <el-radio :label="1" :value="1">网络上传</el-radio>
              <el-radio :label="2" :value="2">扫码上传</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="上传至分组：" prop="region" v-show="ruleForm.type == 0 || ruleForm.type == 1">
            <el-cascader
              class="form-width"
              v-model="ruleForm.region"
              :props="cascaderProps"
              :options="categoryList"
              :disabled="loading || preparing > 0"
              @change="handleChange"
            ></el-cascader>
          </el-form-item>
          <el-form-item label="网络图片：" prop="region" v-if="ruleForm.type == 1">
            <el-input class="form-width" v-model="webImgUrl" placeholder="请网络图片地址"></el-input>
            <span class="tq-text" v-db-click @click="getImg">提取照片</span>
          </el-form-item>
          <el-form-item label="上传图片：" prop="region" v-if="ruleForm.type == 0">
            <div class="acea-row">
              <div class="uploadCont">
                <el-upload
                  ref="upload"
                  :action="fileUrl"
                  list-type="picture-card"
                  :on-change="fileChange"
                  :file-list="ruleForm.imgList"
                  :auto-upload="false"
                  :disabled="loading || preparing > 0"
                  :data="uploadData"
                  :headers="header"
                  :multiple="true"
                  :limit="limit"
                >
                  <template #default><i class="el-icon-plus"></i></template>
                  <template #file="{ file }"
                    ><div
                      draggable="false"
                      @dragstart="handleDragStart($event, file)"
                      @dragover="handleDragOver($event, file)"
                      @dragenter="handleDragEnter($event, file)"
                      @dragend="handleDragEnd($event, file)"
                    >
                      <img class="el-upload-list__item-thumbnail" :src="file.url" alt="" />
                      <i class="el-icon-error btndel" v-db-click @click="handleWebRemove(file)" /></div
                  ></template>
                </el-upload>
                <div class="tips">
                  建议上传图片最大宽度750px，不超过3MB；仅支持jpeg、jpg、png格式，可拖拽调整上传顺序
                </div>
              </div>
            </div>
          </el-form-item>
          <template v-if="ruleForm.type == 1">
            <div class="img-box pl100">
              <div
                v-for="(item, index) in ruleForm.imgList"
                :key="index"
                class="pictrue"
                draggable="false"
                @dragstart="handleDragStart($event, item)"
                @dragover.prevent="handleDragOver($event, item)"
                @dragenter="handleDragEnter($event, item)"
                @dragend="handleDragEnd($event, item)"
              >
                <img :src="item.url" />
                <i class="el-icon-error btndel" v-db-click @click="handleRemove(index)" />
              </div>
            </div>
          </template>
          <div class="code-image" v-if="ruleForm.type == 2">
            <div class="left">
              <el-form-item label="上传至分组：" prop="region">
                <el-cascader
                  class="form-width"
                  v-model="ruleForm.region"
                  :props="cascaderProps"
                  :options="categoryList"
                  :disabled="loading || preparing > 0"
                  @change="handleChange"
                ></el-cascader>
              </el-form-item>
              <el-form-item label="二维码：" prop="region">
                <div class="code" ref="qrCodeUrl"></div>
                <div class="trip">扫描二维码，快速上传手机图片</div>
                <div class="trip-small">建议使用手机浏览器</div>
              </el-form-item>
            </div>
            <div class="right">
              <el-button v-db-click @click="scanUploadGetFn">刷新图库</el-button>
              <div class="tip">刷新图库按钮，可显示移动端上传成功的图片</div>
              <div class="img-box">
                <div
                  v-for="(item, index) in ruleForm.imgList"
                  :key="index"
                  class="pictrue"
                  draggable="false"
                  @dragstart="handleDragStart($event, item)"
                  @dragover.prevent="handleDragOver($event, item)"
                  @dragenter="handleDragEnter($event, item)"
                  @dragend="handleDragEnd($event, item)"
                >
                  <img :src="item.att_dir" />
                  <i class="el-icon-error btndel" v-db-click @click="handleWebRemove(item)" />
                </div>
              </div>
            </div>
          </div>
        </el-form>
      </div>

      <template #footer
        ><span class="dialog-footer">
          <el-button :disabled="loading || preparing > 0" v-db-click @click="clear">取 消</el-button>
          <el-button
            type="primary"
            :loading="loading || preparing > 0"
            :disabled="!ruleForm.imgList.length"
            v-db-click
            @click="submitUpload"
            >确 定</el-button
          >
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { onBeforeUnmount, reactive, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { getCategoryListApi, moveApi, onlineUpload, scanUploadCode } from '@/api/uploadPictures';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { fileUpload, scanUploadQrcode, scanUploadGet } from '@/api/setting';
import QRCode from '@/vendor/qrcodejs2';
import compressImg from '@/utils/compressImg.js';
import { isPicUpload } from '@/utils/index';

defineOptions({ name: 'uploadImg' });

const emit = defineEmits(['uploadSuccess']);

const props = defineProps({
  categoryList: {
    default: () => {
      return [];
    },
  },
  categoryId: {
    default: '',
  },
  isPage: {
    default: false,
  },
  isIframe: {
    default: false,
  },
});

const webImgUrl = ref('');
const uploadModal = ref(false);
const fileUrl = Setting.apiBaseURL + '/file/upload';
const header = ref({
  'Authori-zation': 'Bearer ' + getCookies('token'),
});
const uploadData = ref({});
const cascaderProps = { checkStrictly: true, emitPath: false, label: 'title', value: 'id' };
const disabled = ref(false);
const formRef = ref(null); // 表单实例（勿与 reactive 数据 ruleForm 同名，同名会被模板 ref 覆盖）
const ruleForm = reactive({
  type: 0,
  region: '',
  imgList: [],
});
const rules = { type: [{ required: true, message: '请选择活动资源', trigger: 'change' }] };
const qrcode = ref('');
const scanToken = ref('');
const limit = 20;
const loading = ref(false);
const preparing = ref(0);
const excludedScanAttachments = new Set();
let time = undefined;
let scanRequest = 0;
let scanPollRequest = null;
let scanRevocation = Promise.resolve();
let scanCreation = Promise.resolve();
// 拖拽排序临时状态
let dragging = null;
let dialogImageUrl = '';
let dialogVisible = false;

const qrCodeUrl = ref(null);

watch(
  uploadModal,
  (newVal) => {
    if (newVal) ruleForm.region = props.categoryId;
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  scanRequest++;
  stopScanPolling();
});

function stopScanPolling() {
  clearInterval(time);
  time = undefined;
}
function radioChange(type) {
  ruleForm.type = type;
  ruleForm.imgList = [];
  scanRequest++;
  scanToken.value = '';
  stopScanPolling();
  if (type == 2) {
    scanUploadQrcodeFn();
  }
}
async function scanUploadQrcodeFn() {
  const request = ++scanRequest;
  const previousCreation = scanCreation;
  const pendingRevocation = scanRevocation;
  let completeCreation;
  scanCreation = new Promise((resolve) => {
    completeCreation = resolve;
  });
  scanToken.value = '';
  ruleForm.imgList = [];
  excludedScanAttachments.clear();
  if (qrCodeUrl.value) qrCodeUrl.value.innerHTML = '';
  stopScanPolling();
  try {
    // 顺序签发，避免旧请求在服务端后完成时撤销较新的扫码能力。
    await previousCreation;
    await pendingRevocation;
    if (request !== scanRequest || !uploadModal.value || ruleForm.type !== 2) return;
    const res = await scanUploadQrcode(ruleForm.region || 0);
    if (request !== scanRequest || !uploadModal.value || ruleForm.type !== 2) return;
    const url = new URL(res.data.url, location.origin);
    const token = url.searchParams.get('token');
    if (!/^[a-f0-9]{64}$/.test(token || '')) throw new Error('扫码上传链接无效');
    creatQrCode(url.href);
    scanToken.value = token;
    time = setInterval(scanUploadGetFn, 2000);
  } catch (err) {
    if (request === scanRequest && uploadModal.value && ruleForm.type === 2) {
      ElMessage.error(err.msg || err.message || '生成扫码上传链接失败');
    }
  } finally {
    completeCreation();
  }
}
async function scanUploadGetFn() {
  const request = scanRequest;
  const token = scanToken.value;
  if (!token || !uploadModal.value || ruleForm.type !== 2 || scanPollRequest === request) return;
  scanPollRequest = request;
  try {
    const res = await scanUploadGet(token);
    if (request !== scanRequest || token !== scanToken.value || !uploadModal.value || ruleForm.type !== 2) return;
    if (!Array.isArray(res.data)) throw new Error('扫码上传图片数据无效');
    // 轮询保留本次移除记录和拖拽顺序，只更新现有选择并追加新图片。
    const incoming = res.data.filter((item) => !excludedScanAttachments.has(String(item.att_id)));
    const byId = new Map(incoming.map((item) => [String(item.att_id), item]));
    const retained = ruleForm.imgList
      .filter((item) => byId.has(String(item.att_id)))
      .map((item) => byId.get(String(item.att_id)));
    const retainedIds = new Set(retained.map((item) => String(item.att_id)));
    ruleForm.imgList = [...retained, ...incoming.filter((item) => !retainedIds.has(String(item.att_id)))];
  } catch (err) {
    if (request === scanRequest && uploadModal.value && ruleForm.type === 2) {
      stopScanPolling();
      ElMessage.error(err.msg || err.message || '获取扫码上传图片失败');
    }
  } finally {
    if (scanPollRequest === request) scanPollRequest = null;
  }
}

function getImg() {
  if (loading.value || preparing.value) return;
  if (!webImgUrl.value) {
    ElMessage.error('请先输入图片地址');
    return;
  }
  if (webImgUrl.value.indexOf('.php') != -1) {
    ElMessage.error('请先输入其他图片地址');
    return;
  }
  ruleForm.imgList.push({
    url: webImgUrl.value,
  });
}
async function submitUpload() {
  if (loading.value || preparing.value) return;
  if (!ruleForm.imgList.length) return ElMessage.warning('请先选择图片');
  const images = [...ruleForm.imgList];
  const pid = ruleForm.region || 0;
  loading.value = true;
  try {
    if (ruleForm.type == 0) {
      for (const item of images) {
        if (item._uploaded) continue;
        await uploadItem(item.raw, pid);
        item._uploaded = true;
      }
    } else if (ruleForm.type == 1) {
      await onlineUpload({ pid, images: images.map((item) => item.url) });
    } else if (ruleForm.type == 2) {
      await moveApi({ pid, images: images.map((item) => item.att_id) });
    } else {
      throw new Error('上传方式错误');
    }
    ElMessage.success('上传成功');
    emit('uploadSuccess');
    uploadModal.value = false;
    initData();
  } catch (err) {
    ElMessage.error(err.msg || err.message || '上传失败');
  } finally {
    loading.value = false;
  }
}
function uploadItem(file, pid) {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('pid', pid);
  return fileUpload(formData).then((res) => {
    if (res.status !== 200) throw res;
  });
}
function beforeUpload(file) {
  console.log(file);
}
function creatQrCode(url) {
  qrCodeUrl.value.innerHTML = '';
  var qrcode = new QRCode(qrCodeUrl.value, {
    text: url, // 需要转换为二维码的内容
    width: 160,
    height: 160,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}
function handleWebRemove(file) {
  if (loading.value || preparing.value) return;
  const index = ruleForm.imgList.findIndex((item) =>
    ruleForm.type === 2 ? String(item.att_id) === String(file.att_id) : item.url === file.url,
  );
  if (index < 0) return;
  if (ruleForm.type === 2) excludedScanAttachments.add(String(file.att_id));
  ruleForm.imgList.splice(index, 1);
}
function handleRemove(index) {
  if (loading.value || preparing.value) return;
  ruleForm.imgList.splice(index, 1);
}
function handlePictureCardPreview(file) {
  dialogImageUrl = file.url;
  dialogVisible = true;
}
function handleDownload(file) {
  console.log(file);
}
async function fileChange(file, fileList) {
  if (loading.value) return;
  if (isPicUpload(file)) {
    ruleForm.imgList = [...fileList];
    if (file.size >= 2097152) {
      preparing.value++;
      try {
        const compressed = await compressImg(file.raw);
        const item = ruleForm.imgList.find((item) => item.uid === file.uid);
        if (item) item.raw = compressed;
      } catch (err) {
        ruleForm.imgList = ruleForm.imgList.filter((item) => item.uid !== file.uid);
        ElMessage.error(err.msg || err.message || '图片处理失败');
      } finally {
        preparing.value--;
      }
    }
  } else {
    ruleForm.imgList = fileList.filter((item) => item.uid !== file.uid);
  }
}
function loadData(item, callback) {
  getCategoryListApi({
    pid: item.value,
  })
    .then(async (res) => {
      const data = res.data.list;
      callback(data);
    })
    .catch((res) => {});
}
function handleChange(e) {
  if (ruleForm.type == 2) scanUploadQrcodeFn();
}
// 移动
function handleDragStart(e, item) {
  dragging = item;
}
function handleDragEnd(e, item) {
  dragging = null;
}
function handleDragOver(e) {
  e.dataTransfer.dropEffect = 'move';
}
function handleDragEnter(e, item) {
  if (loading.value || preparing.value) return;
  e.dataTransfer.effectAllowed = 'move';
  if (item === dragging) {
    return;
  }
  const newItems = [...ruleForm.imgList];
  const src = newItems.indexOf(dragging);
  const dst = newItems.indexOf(item);
  newItems.splice(dst, 0, ...newItems.splice(src, 1));
  ruleForm.imgList = newItems;
}
function closed() {
  initData();
  scanRevocation = Promise.all([scanCreation, scanRevocation])
    .then(() => scanUploadCode())
    .catch((err) => {
      ElMessage.error(err.msg || err.message || '取消扫码上传失败');
    });
}
function beforeClose(done) {
  if (!loading.value && !preparing.value) done();
}
function clear() {
  uploadModal.value = false;
  initData();
}
function initData() {
  scanRequest++;
  scanPollRequest = null;
  ruleForm.type = 0;
  ruleForm.region = 0;
  scanToken.value = '';
  excludedScanAttachments.clear();
  webImgUrl.value = '';
  ruleForm.imgList = [];
  stopScanPolling();
}

// 父组件（uploadPictures/uploadVideo2）通过 $refs.upload.uploadModal = true 驱动
defineExpose({ uploadModal });
</script>
<style lang="scss" scoped>
:deep(.el-dialog__title) {
  font-size: 16px;
}
.main {
  min-height: 410px;
}
.pictrue {
  width: 60px !important;
  height: 60px !important;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
  position: relative;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
.btndel {
  position: absolute;
  z-index: 1;
  font-size: 18px;
  right: -5px;
  top: -5px;
  color: #999;
}
.form-width {
  width: 280px;
  max-width: 100%;
}
.tq-text {
  margin-left: 14px;
  font-size: 12px;
  font-weight: 400;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.uploadCont :deep(.el-upload--picture-card),
:deep(.el-upload-list--picture-card .el-upload-list__item) {
  width: 64px;
  height: 64px;
  line-height: 72px;
  overflow: inherit;
}
.uploadCont :deep(.el-upload--picture-card),
:deep(.el-upload-list--picture-card .el-upload-list__item img) {
  width: 64px !important;
  height: 64px !important;
  border-radius: 6px;
  object-fit: cover;
}
.pl100 {
  padding-left: 100px;
}
.img-box {
  display: flex;
  flex-wrap: wrap;
}
.tips {
  font-size: 12px;
  color: #bbbbbb;
}
.code-image {
  display: flex;
  margin-top: 12px;
  .left {
    display: flex;
    flex-direction: column;
    margin-right: 20px;
    align-items: center;
    .code {
      border: 1px solid #dddddd;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 200px;
      height: 200px;
      border-radius: 4px;
      .code-img {
        width: 160px;
        height: 160px;
      }
    }
    .form-width {
      width: 200px;
    }
    .code {
      margin-bottom: 14px;
    }
    .trip {
      color: var(--prev-color-text-regular);
      text-align: center;
      line-height: 18px;
    }
    .trip-small {
      font-size: 12px;
      font-weight: 400;
      color: #bbbbbb;
      text-align: center;
      line-height: 16px;
    }
  }
  .right {
    margin-top: 62px;
    .tip {
      margin: 10px 0;
    }
  }
}
@media (max-width: 620px) {
  .main {
    min-height: 300px;
  }
  .demo-ruleForm :deep(.el-form-item) {
    display: block;
  }
  .demo-ruleForm :deep(.el-form-item__label) {
    display: block;
    width: auto !important;
    height: auto;
    margin-bottom: 6px;
    text-align: left;
  }
  .demo-ruleForm :deep(.el-form-item__content) {
    min-width: 0;
    margin-left: 0 !important;
  }
  .demo-ruleForm :deep(.el-radio-group) {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
  }
  .demo-ruleForm :deep(.el-radio) {
    margin-right: 0;
  }
  .form-width,
  .uploadCont {
    width: 100%;
    min-width: 0;
  }
  .uploadCont :deep(.el-upload-list--picture-card) {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
  }
  .pl100 {
    padding-left: 0;
  }
  .code-image {
    flex-direction: column;
    gap: 16px;
    .left {
      width: 100%;
      min-width: 0;
      margin-right: 0;
      align-items: stretch;
      .form-width {
        width: 100%;
      }
      .code {
        max-width: 100%;
        box-sizing: border-box;
      }
      .trip,
      .trip-small {
        width: 100%;
        text-align: left;
      }
    }
    .right {
      width: 100%;
      min-width: 0;
      margin-top: 0;
    }
  }
}
</style>
