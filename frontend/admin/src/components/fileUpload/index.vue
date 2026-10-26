<template>
  <div class="file-upload">
    <el-upload
      ref="uploadRef"
      class="file-upload__trigger"
      :action="action"
      :headers="headers"
      :data="data"
      :name="uploadName"
      :accept="accept"
      :disabled="disabled || uploading"
      :show-file-list="false"
      :before-upload="handleBeforeUpload"
      :on-success="handleSuccess"
      :on-error="handleError"
    >
      <div
        class="file-upload__box"
        :class="{ 'is-filled': isFilled, 'is-image': showImage, 'is-disabled': disabled }"
        :title="isFilled ? fileName : uploadText || '点击上传'"
      >
        <img v-if="showImage" class="file-upload__image" :src="fileHref" alt="" @error="imageError = true" />
        <template v-else-if="isFilled">
          <el-icon class="file-upload__icon"><Document /></el-icon>
          <span class="file-upload__name">{{ fileName }}</span>
        </template>
        <el-icon v-else class="file-upload__icon"><Upload /></el-icon>
        <span v-if="uploading" class="file-upload__mask is-visible">
          <el-icon class="is-loading"><Loading /></el-icon>
        </span>
        <span
          v-else-if="isFilled && !disabled"
          class="file-upload__mask"
          title="删除"
          @click.stop.prevent="handleRemove"
        >
          <el-icon><Delete /></el-icon>
        </span>
      </div>
    </el-upload>
  </div>
</template>

<script setup>
/**
 * 单文件上传（form-create 自定义组件，后端 Form::fileUpload() 生成 type=FileUpload 规则）
 *
 * 替代内置 upload 组件：内置组件按图片卡片渲染文件，非图片文件没有缩略图会显示破图，
 * 且上传前后尺寸不一致。此组件空态为 80×80 上传图标卡片，已上传时：
 * - 图片类文件（含 ico）按缩略图展示；
 * - 其它文件展示文件图标 + 文件名（不按图片渲染，避免破图）。
 * 两种状态均为 80×80，与同页 frame 单/多图控件尺寸一致。值仍为文件相对地址字符串，
 * 提交与回显行为与原 upload 规则一致。
 */
import { computed, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { Delete, Document, Loading, Upload } from '@element-plus/icons-vue';
import Setting from '@/setting';

defineOptions({ name: 'FileUpload', inheritAttrs: false });

const props = defineProps({
  /** 已上传文件地址（单文件为字符串） */
  modelValue: {
    type: String,
    default: '',
  },
  /** 上传地址 */
  action: {
    type: String,
    default: '',
  },
  /** 上传请求头 */
  headers: {
    type: Object,
    default: () => ({}),
  },
  /** 上传附加参数 */
  data: {
    type: Object,
    default: () => ({}),
  },
  /** 上传文件字段名 */
  uploadName: {
    type: String,
    default: 'file',
  },
  /** 允许的文件类型 */
  accept: {
    type: String,
    default: '',
  },
  /** 是否禁用 */
  disabled: {
    type: Boolean,
    default: false,
  },
  /** 空态提示文案 */
  uploadText: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

/** 图片类扩展名：这些文件可安全用缩略图展示 */
const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];

const uploadRef = ref(null);
const uploading = ref(false);
const imageError = ref(false);

const isFilled = computed(() => !!props.modelValue);

/** 文件名（已上传文件地址的末段） */
const fileName = computed(() => {
  const path = String(props.modelValue || '')
    .split('#')[0]
    .split('?')[0];
  const name = path.substring(path.lastIndexOf('/') + 1);
  try {
    return decodeURIComponent(name);
  } catch (e) {
    return name;
  }
});

const isImageFile = computed(() => {
  const extension = (fileName.value.split('.').pop() || '').toLowerCase();
  return fileName.value.includes('.') && IMAGE_EXTENSIONS.includes(extension);
});

const showImage = computed(() => isFilled.value && isImageFile.value && !imageError.value);

/**
 * 文件访问地址：后端回显值通常已是完整地址（set_file_url 处理），
 * 刚上传时为站点相对路径，按接口地址的站点根补全，避免开发环境后台与接口不同源导致图片破图。
 */
const fileHref = computed(() => {
  const value = String(props.modelValue || '');
  if (!value || /^(https?:)?\/\//i.test(value) || value.startsWith('data:')) return value;
  try {
    return new URL(value, new URL(Setting.apiBaseURL, window.location.origin).origin).href;
  } catch (e) {
    return value;
  }
});

watch(
  () => props.modelValue,
  () => {
    imageError.value = false;
  },
);

function clearUploadList() {
  uploadRef.value?.clearFiles();
}

function handleBeforeUpload() {
  uploading.value = true;
  return true;
}

function handleSuccess(res) {
  uploading.value = false;
  clearUploadList();
  if (res && Number(res.status) === 200 && res.data && res.data.src) {
    emit('update:modelValue', res.data.src);
    return;
  }
  ElMessage.error((res && res.msg) || '上传失败');
}

function handleError(error) {
  uploading.value = false;
  clearUploadList();
  ElMessage.error((error && error.message) || '上传失败');
}

function handleRemove() {
  emit('update:modelValue', '');
}
</script>

<style scoped lang="scss">
.file-upload {
  display: inline-block;
  line-height: normal;
}

.file-upload__box {
  position: relative;
  box-sizing: border-box;
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  width: 80px;
  height: 80px;
  padding: 4px;
  overflow: hidden;
  border: 1px dashed var(--prev-border-color-base);
  border-radius: 4px;
  background-color: var(--prev-bg-color);
  color: var(--prev-color-text-secondary);
  cursor: pointer;
  transition: border-color 0.2s;
}

.file-upload__box:hover {
  border-color: var(--prev-color-primary);
}

.file-upload__box.is-filled {
  border-style: solid;
  background-color: var(--prev-bg-white);
}

.file-upload__box.is-image {
  padding: 0;
}

.file-upload__box.is-disabled {
  color: var(--prev-color-text-placeholder);
  cursor: not-allowed;
}

.file-upload__box.is-disabled:hover {
  border-color: var(--prev-border-color-base);
}

.file-upload__icon {
  font-size: 28px;
}

.file-upload__box.is-filled .file-upload__icon {
  font-size: 22px;
}

.file-upload__name {
  display: -webkit-box;
  max-width: 100%;
  overflow: hidden;
  color: var(--prev-color-text-regular);
  font-size: 12px;
  line-height: 14px;
  text-align: center;
  word-break: break-all;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.file-upload__image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.file-upload__mask {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.6);
  color: var(--prev-bg-white);
  font-size: 20px;
  opacity: 0;
  transition: opacity 0.3s;
}

.file-upload__box:hover .file-upload__mask,
.file-upload__mask.is-visible {
  opacity: 1;
}
</style>
