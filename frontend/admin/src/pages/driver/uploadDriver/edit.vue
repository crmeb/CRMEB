<template>
  <div class="upload-driver-edit">
    <template v-if="!editForm.is_builtin && helpDoc && helpDoc.title">
      <div class="help-doc">
        <div v-if="helpDoc.link" class="help-doc-link">
          <span>{{ helpDoc.title.replace('开通方法', '') }}开通方法：</span>
          <a :href="helpDoc.link" target="_blank" rel="noopener noreferrer">点击查看</a>
        </div>
        <div v-if="helpDoc.steps && helpDoc.steps.length" class="help-doc-steps">
          <div v-for="(step, index) in helpDoc.steps" :key="index" class="help-doc-step">
            {{ step }}
          </div>
        </div>
      </div>
    </template>

    <el-tabs v-model="activeTab">
      <el-tab-pane label="基础配置" name="basic">
        <el-form ref="formRef" :model="editForm" :rules="editRules" label-width="120px" v-loading="editLoading">
          <el-divider content-position="left">基本信息</el-divider>
          <el-form-item label="状态" prop="status">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="editForm.status"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            />
          </el-form-item>
          <el-form-item label="驱动名称" prop="driver_name">
            <el-input v-model="editForm.driver_name" placeholder="请输入驱动名称" />
          </el-form-item>
          <el-form-item label="排序" prop="sort">
            <el-input-number v-model="editForm.sort" :min="0" :max="9999" />
          </el-form-item>

          <template v-if="!editForm.is_builtin">
            <template v-if="configGroups.length > 0">
              <template v-for="(group, groupIndex) in configGroups" :key="'group-' + groupIndex">
                <el-divider content-position="left" style="margin-top: 20px">
                  {{ group.name }}
                </el-divider>
                <template v-for="(field, fieldIndex) in group.fields" :key="'field-' + groupIndex + '-' + fieldIndex">
                  <el-form-item :label="field.label">
                    <el-input
                      v-if="field.type === 'text'"
                      v-model="field.value"
                      :placeholder="field.placeholder || '请输入' + field.label"
                    />
                    <el-input
                      v-else-if="field.type === 'password'"
                      v-model="field.value"
                      type="password"
                      show-password
                      :placeholder="field.placeholder || '请输入' + field.label"
                    />
                    <el-select
                      v-else-if="field.type === 'select'"
                      v-model="field.value"
                      :placeholder="'请选择' + field.label"
                    >
                      <el-option v-for="opt in field.options" :key="opt.value" :label="opt.label" :value="opt.value" />
                    </el-select>
                    <div v-if="field.tip" class="form-tip">{{ field.tip }}</div>
                  </el-form-item>
                </template>
              </template>
            </template>
            <template v-else>
              <el-empty description="暂无配置字段" />
            </template>
          </template>
          <el-divider content-position="left">缩略图和水印设置</el-divider>
          <el-form-item label="是否开启缩略图：" label-width="120px">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="commonConfig.image_thumb_status"
              size="large"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            >
            </el-switch>
          </el-form-item>
          <el-alert closable type="info" class="mb20">
            <template #title>
              <p>上传图片时会生成缩略图</p>
              <p>未设置按照系统默认生成，系统默认：大图800*800，中图300*300，小图150*150</p>
            </template>
          </el-alert>
          <div v-if="commonConfig.image_thumb_status" class="thumb-config">
            <div class="thumb-item">
              <div class="thumb-label">
                <img class="thumb-img" src="../../../assets/images/abbreviationBig.png" alt="" />
                <div>缩略大图</div>
              </div>
              <div class="thumb-fields">
                <el-form-item label="宽：">
                  <el-input type="number" v-model="commonConfig.thumb_big_width" placeholder="请输入宽度">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
                <el-form-item label="高：">
                  <el-input type="number" v-model="commonConfig.thumb_big_height" placeholder="请输入高度">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
              </div>
            </div>
            <div class="thumb-item">
              <div class="thumb-label">
                <img class="thumb-img" src="../../../assets/images/abbreviation.png" alt="" />
                <div>缩略中图</div>
              </div>
              <div class="thumb-fields">
                <el-form-item label="宽：">
                  <el-input type="number" v-model="commonConfig.thumb_mid_width" placeholder="请输入宽度">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
                <el-form-item label="高：">
                  <el-input type="number" v-model="commonConfig.thumb_mid_height" placeholder="请输入高度">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
              </div>
            </div>
            <div class="thumb-item">
              <div class="thumb-label">
                <img class="thumb-img" src="../../../assets/images/abbreviationSmall.png" alt="" />
                <div>缩略小图</div>
              </div>
              <div class="thumb-fields">
                <el-form-item label="宽：">
                  <el-input type="number" v-model="commonConfig.thumb_small_width" placeholder="请输入宽度">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
                <el-form-item label="高：">
                  <el-input type="number" v-model="commonConfig.thumb_small_height" placeholder="请输入高度">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
              </div>
            </div>
          </div>

          <el-form-item label="是否开启水印：" label-width="120px">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="commonConfig.image_watermark_status"
              size="large"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            >
            </el-switch>
          </el-form-item>
          <el-alert closable type="info" class="mb20">
            <template #title>
              <p>水印只在上传图片时生成，原图，大中小缩略图上都按照比例存在。</p>
              <p>若上传图片时未开启水印，则该图在开启水印之后依旧无水印效果。</p>
            </template>
          </el-alert>
          <div v-if="commonConfig.image_watermark_status">
            <div class="watermark-config">
              <div class="watermark-row">
                <el-form-item label="位置：" label-width="120px">
                  <div class="position-container">
                    <div class="positionBox">
                      <div
                        class="position-item"
                        :class="commonConfig.watermark_position == item.id ? 'active' : ''"
                        v-for="item in positionList"
                        :key="item.id"
                        @click="setPosition(item)"
                      ></div>
                    </div>
                    <div class="position-label">{{ positionLabel }}</div>
                  </div>
                </el-form-item>
              </div>
              <div class="watermark-row">
                <el-form-item label="横坐标偏移量：" label-width="120px">
                  <el-input type="number" v-model="commonConfig.watermark_x" placeholder="请输入横坐标偏移量">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
                <el-form-item label="纵坐标偏移量：" label-width="120px">
                  <el-input type="number" v-model="commonConfig.watermark_y" placeholder="请输入纵坐标偏移量">
                    <template #append><span>px</span></template>
                  </el-input>
                </el-form-item>
              </div>
            </div>
            <el-form-item label="类型：" label-width="120px">
              <el-radio-group v-model="commonConfig.watermark_type">
                <el-radio :label="1" :value="1">图片</el-radio>
                <el-radio :label="2" :value="2">文字</el-radio>
              </el-radio-group>
            </el-form-item>
            <div v-if="commonConfig.watermark_type == 1" class="watermark-config">
              <div class="watermark-row">
                <el-form-item label="图片：" label-width="120px">
                  <div class="picBox" @click="modalPicTap('单选')">
                    <div class="pictrue" v-if="commonConfig.watermark_image">
                      <img :src="commonConfig.watermark_image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </div>
              <div class="watermark-row">
                <el-form-item label="透明度：" label-width="120px">
                  <el-input type="number" v-model="commonConfig.watermark_opacity" placeholder="请输入水印透明度" />
                </el-form-item>
              </div>
              <div class="watermark-row">
                <el-form-item label="倾斜度：" label-width="120px">
                  <el-input type="number" v-model="commonConfig.watermark_rotate" placeholder="请输入水印倾斜度" />
                </el-form-item>
              </div>
            </div>
            <div v-else class="watermark-config">
              <div class="watermark-row">
                <el-form-item label="文字：" label-width="120px">
                  <el-input v-model="commonConfig.watermark_text" placeholder="请输入水印文字" />
                </el-form-item>
              </div>
              <div class="watermark-row">
                <el-form-item label="文字大小：" label-width="120px">
                  <el-input type="number" v-model="commonConfig.watermark_text_size" placeholder="请输入水印文字大小" />
                </el-form-item>
              </div>
              <div class="watermark-row">
                <el-form-item label="字体颜色：" label-width="120px">
                  <el-color-picker v-model="commonConfig.watermark_text_color"></el-color-picker>
                </el-form-item>
              </div>
              <div class="watermark-row">
                <el-form-item label="字体旋转角度：" label-width="120px">
                  <el-input
                    type="number"
                    v-model="commonConfig.watermark_text_angle"
                    placeholder="请输入水印字体旋转角度"
                  />
                </el-form-item>
              </div>
            </div>
          </div>
        </el-form>
      </el-tab-pane>
      <el-tab-pane v-if="editForm.driver_key !== 'local'" label="空间配置" name="storage">
        <UploadDriverStorage v-if="activeTab === 'storage'" :driver-id="driverId" />
      </el-tab-pane>
    </el-tabs>

    <div class="form-footer">
      <el-button v-db-click @click="handleClose">返回</el-button>
      <el-button
        v-auth="['driver-upload-save-config']"
        type="primary"
        v-db-click
        @click="handleSubmit"
        :loading="submitLoading"
        :disabled="editLoading || loadFailed"
      >
        保存
      </el-button>
    </div>

    <el-dialog v-model="modalPic" width="min(950px, calc(100% - 32px))" title="上传图片" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import uploadPictures from '@/components/uploadPictures';
import UploadDriverStorage from './storage.vue';
import { uploadDriverDetailApi, getUploadCommonConfigApi, saveDriverConfigApi } from '@/api/driver/uploadDriver';

defineOptions({ name: 'UploadDriverEdit' });

const props = defineProps({
  driverId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits(['close', 'success']);

const route = useRoute();

const formRef = ref(null);
const activeTab = ref('basic');
const editLoading = ref(false);
const loadFailed = ref(false);
let detailRequest = 0;
const submitLoading = ref(false);
const editForm = ref({
  id: 0,
  driver_name: '',
  icon: '',
  sort: 0,
  status: 1,
  is_builtin: 0,
  driver_key: '',
  config_fields: [],
});
const editRules = ref({
  driver_name: [{ required: true, message: '请输入驱动名称', trigger: 'blur' }],
});
const commonConfig = ref({
  image_thumb_status: 0,
  thumb_big_width: 800,
  thumb_big_height: 800,
  thumb_mid_width: 300,
  thumb_mid_height: 300,
  thumb_small_width: 150,
  thumb_small_height: 150,
  image_watermark_status: 0,
  watermark_type: 1,
  watermark_opacity: '',
  watermark_rotate: '',
  watermark_position: 1,
  watermark_x: '',
  watermark_y: '',
  watermark_image: '',
  watermark_text: '',
  watermark_text_size: '',
  watermark_text_color: '#000000',
  watermark_text_angle: '',
});
const positionList = [
  { content: '左上', id: 1 },
  { content: '上', id: 2 },
  { content: '右上', id: 3 },
  { content: '左中', id: 4 },
  { content: '中', id: 5 },
  { content: '右中', id: 6 },
  { content: '左下', id: 7 },
  { content: '下', id: 8 },
  { content: '右下', id: 9 },
];
const positionLabel = ref('');
const helpDoc = ref(null);
const modalPic = ref(false);
const isChoice = ref('单选');
const gridBtn = {
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
};
const gridPic = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
};
const configGroups = ref([]);

watch(
  () => props.driverId,
  () => {
    getDetail();
  },
  { immediate: true },
);

function handleClose() {
  emit('close');
}
function getDetail() {
  const id = props.driverId || (route && route.params.id);
  if (!id) return;
  const requestId = ++detailRequest;
  editLoading.value = true;
  loadFailed.value = false;
  uploadDriverDetailApi(id)
    .then(async (res) => {
      if (requestId !== detailRequest) return;
      const data = res.data;
      editForm.value = {
        id: data.id,
        driver_name: data.driver_name,
        icon: data.icon,
        sort: data.sort,
        status: data.status,
        is_builtin: data.is_builtin,
        driver_key: data.driver_key,
        config_fields: data.config_fields || [],
      };
      const fields = (data.config_fields || []).map((field) => ({
        ...field,
        value:
          field.value !== undefined && field.value !== null && field.value !== ''
            ? field.value
            : field.default !== undefined
            ? field.default
            : '',
      }));
      parseConfigFields(fields);
      helpDoc.value = data.help_doc || null;
      await getCommonConfig(data.driver_key, requestId);
    })
    .catch((err) => {
      if (requestId !== detailRequest) return;
      loadFailed.value = true;
      ElMessage.error(err.msg || err.message || '配置加载失败');
    })
    .finally(() => {
      if (requestId === detailRequest) editLoading.value = false;
    });
}
function getCommonConfig(driverKey, requestId) {
  return getUploadCommonConfigApi(driverKey).then((res) => {
    if (requestId !== detailRequest) return;
    if (res.data) {
      commonConfig.value = {
        image_thumb_status: res.data.image_thumb_status ?? 0,
        thumb_big_width: res.data.thumb_big_width ?? 800,
        thumb_big_height: res.data.thumb_big_height ?? 800,
        thumb_mid_width: res.data.thumb_mid_width ?? 300,
        thumb_mid_height: res.data.thumb_mid_height ?? 300,
        thumb_small_width: res.data.thumb_small_width ?? 150,
        thumb_small_height: res.data.thumb_small_height ?? 150,
        image_watermark_status: res.data.image_watermark_status ?? 0,
        watermark_type: res.data.watermark_type ?? 1,
        watermark_opacity: res.data.watermark_opacity ?? '',
        watermark_rotate: res.data.watermark_rotate ?? '',
        watermark_position: res.data.watermark_position ?? 1,
        watermark_x: res.data.watermark_x ?? '',
        watermark_y: res.data.watermark_y ?? '',
        watermark_image: res.data.watermark_image ?? '',
        watermark_text: res.data.watermark_text ?? '',
        watermark_text_size: res.data.watermark_text_size ?? '',
        watermark_text_color: res.data.watermark_text_color ?? '#000000',
        watermark_text_angle: res.data.watermark_text_angle ?? '',
      };
      const pos = positionList.find((p) => p.id === commonConfig.value.watermark_position);
      positionLabel.value = pos ? pos.content : '';
    }
  });
}
function setPosition(item) {
  commonConfig.value.watermark_position = item.id;
  positionLabel.value = item.content;
}
function modalPicTap() {
  modalPic.value = true;
}
function getPic(pc) {
  commonConfig.value.watermark_image = pc.att_dir;
  modalPic.value = false;
}
function parseConfigFields(fields) {
  configGroups.value = [];
  if (!fields || !Array.isArray(fields) || fields.length === 0) {
    return;
  }
  const groups = Object.create(null);
  fields.forEach((field) => {
    const groupName = field.group || '默认配置';
    if (!groups[groupName]) {
      groups[groupName] = [];
    }
    groups[groupName].push(field);
  });
  Object.keys(groups).forEach((groupName) => {
    configGroups.value.push({
      name: groupName,
      fields: groups[groupName],
    });
  });
}
function handleSubmit() {
  if (submitLoading.value || editLoading.value || loadFailed.value) return;
  formRef.value.validate((valid) => {
    if (!valid || submitLoading.value) return;
    const id = props.driverId || (route && route.params.id);
    const driverData = {
      driver_name: editForm.value.driver_name,
      icon: editForm.value.icon,
      sort: editForm.value.sort,
      status: editForm.value.status,
      thumb_watermark_config: { ...commonConfig.value },
    };
    configGroups.value.forEach((group) => {
      group.fields.forEach((field) => {
        driverData[field.field] = field.value;
      });
    });
    submitLoading.value = true;
    saveDriverConfigApi(id, driverData)
      .then(() => {
        emit('success');
        submitLoading.value = false;
      })
      .catch((err) => {
        ElMessage.error(err.msg || err.message || '保存失败');
        submitLoading.value = false;
      });
  });
}
</script>

<style scoped lang="scss">
.form-tip {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 1.5;
  margin-top: 5px;
}
.form-footer {
  padding: 20px;
  text-align: center;
  border-top: 1px solid #e8e8e8;
  margin-top: 20px;
}
:deep(.el-tabs__content) {
  padding-top: 0;
}
:deep(.el-tab-pane) {
  padding: 0;
}
:deep(.el-card) {
  margin: 0;
}
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}
.help-doc {
  padding: 15px 20px;
  background: #f5f7fa;
  border-radius: 4px;
  .help-doc-link {
    margin-bottom: 12px;
    font-size: 14px;
    a {
      color: #409eff;
      text-decoration: none;
      &:hover {
        text-decoration: underline;
      }
    }
  }
  .help-doc-steps {
    .help-doc-step {
      font-size: 13px;
      color: var(--prev-color-text-primary);
      line-height: 2;
      padding-left: 10px;
    }
  }
}
.mb20 {
  margin-bottom: 20px;
}
.mt16 {
  margin-top: 16px;
}
.thumb-config {
  display: flex;
  justify-content: flex-start;
  flex-wrap: wrap;
  .thumb-item {
    display: flex;
    margin-right: 30px;
    margin-bottom: 20px;
    .thumb-label {
      width: 80px;
      text-align: center;
      font-size: 13px;
      .thumb-img {
        width: 70px;
        height: 51px;
        display: block;
        margin: 0 auto 8px;
      }
    }
    .thumb-fields {
      width: 300px;
      margin-left: 15px;
    }
  }
}
.watermark-config {
  .watermark-row {
    display: flex;
    flex-wrap: wrap;
    .el-form-item {
      margin-right: 20px;
    }
  }
}
.picBox {
  display: inline-block;
  cursor: pointer;
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
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
.position-container {
  display: flex;
  .positionBox {
    display: flex;
    flex-wrap: wrap;
    width: 100px;
    height: 99px;
    border-right: 1px solid #dddddd;
    .position-item {
      width: 33px;
      height: 33px;
      border: 1px solid #dddddd;
      cursor: pointer;
      &.active {
        background: rgba(24, 144, 255, 0.1);
      }
    }
  }
  .position-label {
    width: 30px;
    margin-top: 70px;
    margin-left: 6px;
    font-size: 12px;
  }
}
@media (max-width: 576px) {
  .upload-driver-edit :deep(.el-form-item) {
    flex-direction: column;
    align-items: stretch;
    min-width: 0;
  }
  .upload-driver-edit :deep(.el-form-item__label) {
    justify-content: flex-start;
  }
  .upload-driver-edit :deep(.el-form-item__content) {
    width: 100%;
    min-width: 0;
  }
  .thumb-config .thumb-item {
    width: 100%;
    min-width: 0;
    margin-right: 0;
    .thumb-label {
      flex: 0 0 80px;
    }
    .thumb-fields {
      flex: 1 1 0;
      width: auto;
      min-width: 0;
    }
  }
  .watermark-config .watermark-row .el-form-item {
    width: 100%;
    margin-right: 0;
  }
}
</style>
