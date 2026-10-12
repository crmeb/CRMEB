<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="查看各支付方式的支持端与驱动状态并启停，支付参数在各驱动的配置中维护。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table :data="list" ref="table" v-loading="loading" highlight-current-row row-key="id">
        <el-table-column label="排序" width="80" prop="sort">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>

        <el-table-column label="图标" width="80">
          <template #default="scope">
            <img v-if="isImageUrl(scope.row.icon)" :src="scope.row.icon" style="width: 24px; height: 24px" />
            <span v-else-if="scope.row.icon" class="iconfont" :class="scope.row.icon"></span>
            <span v-else>--</span>
          </template>
        </el-table-column>

        <el-table-column label="支付名称" min-width="150">
          <template #default="scope">
            <span class="font-weight-bold">{{ scope.row.channel_name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="支付标识" min-width="100">
          <template #default="scope">
            <el-tag>{{ scope.row.channel_key }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="所属平台" min-width="100">
          <template #default="scope">
            <el-tag :type="getPlatformType(scope.row.platform)">
              {{ getPlatformName(scope.row.platform) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="支持端" min-width="200">
          <template #default="scope">
            <el-tag v-for="item in parsePlatforms(scope.row.supported_platforms)" :key="item" class="mr5">
              {{ getPlatformLabel(item) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="驱动状态" width="100">
          <template #default="scope">
            <template v-if="scope.row.platform === 'self'">
              <el-tag type="info">自有</el-tag>
            </template>
            <template v-else>
              <el-tag :type="scope.row.driver_status ? 'success' : 'danger'">
                {{ scope.row.driver_status ? '可用' : '不可用' }}
              </el-tag>
            </template>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="80">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              @change="handleStatusChange(scope.row)"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            />
          </template>
        </el-table-column>

        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a v-db-click @click="handleEdit(scope.row)">设置</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 设置抽屉 -->
    <el-drawer v-model="drawerVisible" title="设置支付方式" size="50%" direction="rtl">
      <el-form ref="editFormRef" :model="editForm" :rules="editRules" label-width="140px" v-loading="editLoading">
        <!-- 基本信息 -->
        <el-divider content-position="left">基本信息</el-divider>
        <el-form-item label="状态" prop="status">
          <el-switch
            class="defineSwitch"
            :active-value="1"
            :inactive-value="0"
            v-model="editForm.status"
            active-text="开启"
            inactive-text="关闭"
          />
        </el-form-item>
        <el-form-item label="支付名称" prop="channel_name">
          <el-input v-model="editForm.channel_name" placeholder="请输入支付名称" />
        </el-form-item>
        <el-form-item label="支付图标" prop="icon">
          <div class="picBox" v-db-click @click="modalPicTap">
            <div class="pictrue" v-if="isImageUrl(editForm.icon)">
              <img v-lazy="editForm.icon" />
            </div>
            <div class="pictrue payment-default-icon iconfont" :class="editForm.icon" v-else-if="editForm.icon"></div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <i class="el-icon-picture-outline" style="font-size: 24px"></i>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="editForm.sort" :min="0" :max="9999" />
        </el-form-item>

        <!-- 端配置 -->
        <el-divider content-position="left">端配置</el-divider>
        <el-form-item label="已开启端">
          <el-checkbox-group v-model="enabledPlatforms">
            <el-checkbox v-for="item in supportedPlatforms" :key="item.value" :label="item.value">
              {{ item.label }}
            </el-checkbox>
          </el-checkbox-group>
        </el-form-item>

        <!-- 动态配置字段 -->
        <template v-if="configGroups.length > 0">
          <template v-for="(group, groupIndex) in configGroups" :key="'group-' + groupIndex">
            <el-divider content-position="left">
              {{ group.name }}
            </el-divider>
            <template v-for="(field, fieldIndex) in group.fields" :key="'field-' + groupIndex + '-' + fieldIndex">
              <el-form-item v-if="showField(field)" :label="field.label">
                <!-- Switch 类型 -->
                <el-switch
                  v-if="field.type === 'switch'"
                  :active-value="'1'"
                  :inactive-value="'0'"
                  v-model="field.value"
                />

                <!-- 文本类型 -->
                <el-input
                  v-else-if="field.type === 'text'"
                  v-model="field.value"
                  :placeholder="field.placeholder || '请输入' + field.label"
                />

                <!-- 密码类型 -->
                <el-input
                  v-else-if="field.type === 'password'"
                  v-model="field.value"
                  type="password"
                  :placeholder="field.placeholder || '请输入' + field.label"
                  show-password
                />

                <!-- 多行文本 -->
                <el-input
                  v-else-if="field.type === 'textarea'"
                  v-model="field.value"
                  type="textarea"
                  :rows="3"
                  :placeholder="field.placeholder || '请输入' + field.label"
                />

                <!-- 单选 -->
                <el-radio-group v-else-if="field.type === 'radio'" v-model="field.value">
                  <el-radio v-for="opt in field.options" :key="opt.value" :label="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </el-radio>
                </el-radio-group>

                <!-- 下拉选择 -->
                <el-select
                  v-else-if="field.type === 'select'"
                  v-model="field.value"
                  :placeholder="'请选择' + field.label"
                >
                  <el-option v-for="opt in field.options" :key="opt.value" :label="opt.label" :value="opt.value" />
                </el-select>

                <!-- 文件上传 -->
                <el-upload
                  v-else-if="field.type === 'upload'"
                  :action="uploadUrl"
                  :headers="uploadHeaders"
                  :on-success="(res) => handleUploadSuccess(res, field.field)"
                  :on-error="handleUploadError"
                  :on-exceed="handleUploadExceed"
                  :on-remove="() => handleUploadRemove(field.field)"
                  :file-list="getFileList(field.field)"
                  :limit="1"
                >
                  <el-button type="primary">点击上传</el-button>
                </el-upload>

                <!-- 帮助提示 -->
                <div v-if="field.tip" class="form-tip">{{ field.tip }}</div>
              </el-form-item>
            </template>
          </template>
        </template>
      </el-form>
      <div class="drawer-footer">
        <el-button type="primary" v-db-click @click="handleSubmit" :loading="submitLoading"> 保存 </el-button>
        <el-button @click="drawerVisible = false">取消</el-button>
      </div>
    </el-drawer>

    <!-- 图片选择弹窗 -->
    <el-dialog v-model="modalPic" title="选择图片" width="1024px" :close-on-click-modal="false">
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
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { payChannelListApi, payChannelDetailApi, payChannelUpdateApi, payChannelSetStatusApi } from '@/api/driver/pay';
import { getCookies } from '@/libs/util';
import Setting from '@/setting';
import uploadPictures from '@/components/uploadPictures';

defineOptions({ name: 'PayChannelList' });

const loading = ref(false);
const list = ref([]);
// 图片选择相关
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
// 设置相关
const editFormRef = ref(null);
const drawerVisible = ref(false);
const editLoading = ref(false);
const submitLoading = ref(false);
const editForm = ref({
  channel_name: '',
  icon: '',
  sort: 0,
  status: 0,
  enabled_platforms: '',
  config_fields: [],
});
const enabledPlatforms = ref([]);
const supportedPlatforms = ref([]);
const editRules = ref({
  channel_name: [{ required: true, message: '请输入支付名称', trigger: 'blur' }],
});
const uploadUrl = Setting.apiBaseURL + '/file/upload/1';
const uploadHeaders = {
  'Authori-zation': 'Bearer ' + getCookies('token'),
};
const platforms = [
  { value: 'wechat_mini', label: '微信小程序' },
  { value: 'wechat_mp', label: '微信公众号' },
  { value: 'h5', label: 'H5浏览器' },
  { value: 'app', label: 'APP' },
];

function isImageUrl(icon) {
  if (!icon || typeof icon !== 'string') return false;
  return (
    /^(https?:)?\/\//i.test(icon) || icon.startsWith('/') || /\.(png|jpe?g|gif|webp|bmp|svg)(?:[?#].*)?$/i.test(icon)
  );
}

// 按分组整理配置字段
const configGroups = computed(() => {
  const groups = {};
  const fields = editForm.value.config_fields || [];

  fields.forEach((field) => {
    const groupName = field.group || '其他';
    if (!groups[groupName]) {
      groups[groupName] = {
        name: groupName,
        fields: [],
      };
    }
    groups[groupName].fields.push(field);
  });

  return Object.values(groups);
});

getList();

// 获取列表
function getList() {
  loading.value = true;
  payChannelListApi()
    .then((res) => {
      list.value = res.data;
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}

// 解析支持的端
function parsePlatforms(platforms) {
  if (!platforms) return [];
  return platforms.split(',').map((p) => p.trim());
}

// 获取平台名称
function getPlatformName(platform) {
  const map = {
    wechat: '微信',
    alipay: '支付宝',
    allinpay: '通联',
    self: '自有',
  };
  return map[platform] || platform;
}

// 获取平台类型
function getPlatformType(platform) {
  const map = {
    wechat: 'success',
    alipay: 'primary',
    allinpay: 'warning',
    self: 'info',
  };
  return map[platform] || '';
}

// 获取端标签
function getPlatformLabel(value) {
  const item = platforms.find((p) => p.value === value);
  return item ? item.label : value;
}

// 状态变更
function handleStatusChange(row) {
  payChannelSetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch(() => {
      row.status = row.status === 1 ? 0 : 1;
    });
}

// 选择图片
function modalPicTap() {
  modalPic.value = true;
}

// 选中图片
function getPic(pc) {
  editForm.value.icon = pc.att_dir;
  modalPic.value = false;
}

// 编辑
function handleEdit(row) {
  drawerVisible.value = true;
  editLoading.value = true;
  getDetail(row.id);
}

// 获取详情
function getDetail(id) {
  payChannelDetailApi(id)
    .then((res) => {
      const data = res.data;
      editForm.value = {
        id: data.id,
        channel_name: data.channel_name,
        icon: data.icon,
        sort: data.sort,
        status: data.status,
        enabled_platforms: data.enabled_platforms,
        config_fields: data.config_fields || [],
      };

      // 解析已开启的端
      enabledPlatforms.value = data.enabled_platforms ? data.enabled_platforms.split(',').map((p) => p.trim()) : [];

      // 解析支持的端
      supportedPlatforms.value = data.supported_platforms
        ? data.supported_platforms.split(',').map((p) => {
            const platform = platforms.find((item) => item.value === p.trim());
            return {
              value: p.trim(),
              label: platform ? platform.label : p.trim(),
            };
          })
        : [];

      editLoading.value = false;
    })
    .catch((error) => {
      editLoading.value = false;
      ElMessage.error(error.msg || '支付配置加载失败');
    });
}

// 提交表单
function handleSubmit() {
  editFormRef.value.validate((valid) => {
    if (!valid) return;

    const id = editForm.value.id;
    const data = {
      ...editForm.value,
      enabled_platforms: enabledPlatforms.value.join(','),
    };

    submitLoading.value = true;
    payChannelUpdateApi(id, data)
      .then((res) => {
        ElMessage.success(res.msg);
        submitLoading.value = false;
        drawerVisible.value = false;
        getList();
      })
      .catch(() => {
        submitLoading.value = false;
      });
  });
}

// 上传成功回调
function handleUploadSuccess(res, field) {
  if (res.status === 200) {
    const configFields = editForm.value.config_fields || [];
    const targetField = configFields.find((f) => f.field === field);
    if (targetField) {
      // 上传接口（file/upload）返回的文件路径在 data.src
      targetField.value = res.data.src;
    }
  } else {
    ElMessage.error(res.msg || '上传失败');
  }
}

// 上传失败回调（网络错误/接口异常时 el-upload 的 response 可能为空）
function handleUploadError(error) {
  let msg = '上传失败';
  try {
    const res = JSON.parse(error?.message || '');
    if (res && res.msg) msg = res.msg;
  } catch (e) {
    // 非 JSON 响应，保留默认提示
  }
  ElMessage.error(msg);
}

// 已有文件时再次选择（limit=1）
function handleUploadExceed() {
  ElMessage.warning('最多上传 1 个文件，请先删除已上传的文件');
}

// 移除文件时同步清空配置值
function handleUploadRemove(field) {
  const configFields = editForm.value.config_fields || [];
  const targetField = configFields.find((item) => item.field === field);
  if (targetField) {
    targetField.value = '';
  }
}

// 获取文件列表
function getFileList(field) {
  const configFields = editForm.value.config_fields || [];
  const targetField = configFields.find((f) => f.field === field);
  const value = targetField ? targetField.value : '';
  if (value) {
    const fileUrl = String(value);
    return [{ name: fileUrl.split('/').pop(), url: fileUrl }];
  }
  return [];
}

// 判断字段是否显示（支持联动条件）
function showField(field) {
  if (!field.show_if) {
    return true;
  }

  const { show_if } = field;
  const targetFieldName = show_if.field;
  const configFields = editForm.value.config_fields || [];
  const targetField = configFields.find((f) => f.field === targetFieldName);
  const currentValue = targetField ? targetField.value : '';

  if (Array.isArray(show_if.values)) {
    const allowedValues = show_if.values.map((value) => String(value));
    return allowedValues.includes(String(currentValue));
  }

  if (!Object.prototype.hasOwnProperty.call(show_if, 'value')) {
    // 兼容旧版配置：历史 show_if 只记录 field，语义为控制项开启时显示。
    return String(currentValue) === '1';
  }

  return String(currentValue) === String(show_if.value);
}
</script>

<style scoped>
.font-weight-bold {
  font-weight: bold;
}
.mr5 {
  margin-right: 5px;
}
.form-tip {
  width: 100%;
  flex-basis: 100%;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
  line-height: 1.5;
  margin-top: 5px;
}
.picBox {
  display: inline-block;
  cursor: pointer;
}
.picBox .pictrue {
  width: 80px;
  height: 80px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  overflow: hidden;
}
.picBox .pictrue img {
  width: 100%;
  height: 100%;
}
.picBox .payment-default-icon {
  font-size: 32px;
  line-height: 80px;
  text-align: center;
}
.picBox .upLoad {
  width: 80px;
  height: 80px;
  line-height: 80px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background-color: rgba(0, 0, 0, 0.02);
}
.drawer-footer {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.drawer-footer .el-button + .el-button {
  margin-left: 0;
}
</style>
