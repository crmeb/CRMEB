<template>
  <div>
    <el-form ref="formRef" :model="editForm" :rules="editRules" label-width="100px" v-loading="editLoading">
      <el-divider content-position="left">基本信息</el-divider>

      <el-form-item label="驱动名称" prop="driver_name">
        <el-input v-model="editForm.driver_name" placeholder="请输入驱动名称" />
      </el-form-item>

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
                :disabled="field.readonly"
                :placeholder="field.placeholder || '请输入' + field.label"
              />
              <el-input
                v-else-if="field.type === 'password'"
                v-model="field.value"
                type="text"
                :disabled="field.readonly"
                :placeholder="field.placeholder || '请输入' + field.label"
              />
              <el-input
                v-else-if="field.type === 'textarea'"
                v-model="field.value"
                type="textarea"
                :rows="3"
                :disabled="field.readonly"
                :placeholder="field.placeholder || '请输入' + field.label"
              />
              <el-select
                v-else-if="field.type === 'select'"
                v-model="field.value"
                :disabled="field.readonly"
                :placeholder="'请选择' + field.label"
              >
                <el-option v-for="opt in field.options" :key="opt.value" :label="opt.label" :value="opt.value" />
              </el-select>
              <el-radio-group v-else-if="field.type === 'radio'" v-model="field.value" :disabled="field.readonly">
                <el-radio v-for="opt in field.options" :key="opt.value" :label="opt.value" :value="opt.value">{{
                  opt.label
                }}</el-radio>
              </el-radio-group>
              <el-switch
                v-else-if="field.type === 'switch'"
                v-model="field.value"
                :disabled="field.readonly"
                active-value="1"
                inactive-value="0"
                class="defineSwitch"
              />
              <el-input v-else v-model="field.value" :disabled="field.readonly" :placeholder="'请输入' + field.label" />
              <div v-if="field.tip" class="form-tip">{{ field.tip }}</div>
            </el-form-item>
          </template>
        </template>
      </template>

      <el-divider content-position="left">其它设置</el-divider>

      <el-form-item label="状态" prop="status">
        <el-switch
          :active-value="1"
          :inactive-value="0"
          v-model="editForm.status"
          active-text="开启"
          inactive-text="关闭"
          class="defineSwitch"
        />
        <div class="form-tip">机器人通知驱动支持同时启用多个驱动</div>
      </el-form-item>

      <el-form-item label="排序" prop="sort">
        <el-input-number v-model="editForm.sort" :min="0" :max="9999" />
      </el-form-item>

      <template v-if="helpDocList.length > 0">
        <el-divider content-position="left">帮助文档</el-divider>
        <div v-for="(doc, index) in helpDocList" :key="index" class="help-doc-module">
          <div class="help-doc-title">{{ doc.title || '参考文档' }}</div>
          <div class="help-doc-content">
            <el-tag v-if="doc.url" type="info" class="help-doc-tag">
              <a :href="doc.url" target="_blank" class="help-doc-link">查看帮助文档</a>
            </el-tag>
          </div>
        </div>
      </template>

      <div class="form-actions">
        <el-button type="primary" v-db-click @click="handleSubmit"> 保存 </el-button>
        <el-button v-db-click @click="$emit('close')"> 取消 </el-button>
      </div>
    </el-form>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { botDriverDetailApi, saveBotDriverConfigApi } from '@/api/driver/botDriver';

defineOptions({ name: 'BotDriverEdit' });

const props = defineProps({
  driverId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits(['close', 'success']);

const route = useRoute();

const formRef = ref(null);
const editLoading = ref(false);
const editForm = ref({
  driver_name: '',
  sort: 0,
  status: 0,
});
const editRules = ref({
  driver_name: [{ required: true, message: '请输入驱动名称', trigger: 'blur' }],
  sort: [{ type: 'number', required: true, message: '请输入排序', trigger: 'blur' }],
});
const configFields = ref([]);
const helpDocList = ref([]);

const configGroups = computed(() => {
  const groups = {};
  configFields.value.forEach((field) => {
    const groupName = field.group || '配置';
    if (!groups[groupName]) {
      groups[groupName] = { name: groupName, fields: [] };
    }
    groups[groupName].fields.push(field);
  });
  return Object.values(groups);
});

onMounted(() => {
  getDetail();
});

function getDetail() {
  const id = props.driverId || (route && route.params.id);
  if (!id) return;
  editLoading.value = true;
  botDriverDetailApi(id)
    .then((res) => {
      const data = res.data;
      editForm.value = {
        driver_name: data.driver_name || '',
        sort: data.sort || 0,
        status: data.status || 0,
      };
      configFields.value = (data.config_fields || []).map((field) => ({
        ...field,
        value:
          field.value !== undefined && field.value !== null && field.value !== ''
            ? field.value
            : field.default !== undefined
            ? field.default
            : '',
      }));
      // 帮助文档：从 driver_info.help_doc 获取
      const helpDoc = data.driver_info?.help_doc;
      if (Array.isArray(helpDoc) && helpDoc.length > 0) {
        helpDocList.value = helpDoc;
      } else {
        helpDocList.value = [];
      }
    })
    .catch(() => {})
    .finally(() => {
      editLoading.value = false;
    });
}
function handleSubmit() {
  formRef.value.validate((valid) => {
    if (!valid) return;

    const id = props.driverId || (route && route.params.id);
    const data = {
      driver_name: editForm.value.driver_name,
      sort: editForm.value.sort,
      status: editForm.value.status,
    };
    configFields.value.forEach((field) => {
      data[field.field] = field.value;
    });

    saveBotDriverConfigApi(id, data)
      .then((res) => {
        ElMessage.success(res.msg || '保存成功');
        emit('success');
      })
      .catch((err) => {
        ElMessage.error(err.msg || '保存失败');
      });
  });
}
</script>

<style scoped>
.form-tip {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 1.5;
  margin-top: 5px;
}
.help-doc-tag a {
  color: inherit;
  text-decoration: none;
}
.help-doc-tag a:hover {
  color: #1890ff;
}
.help-doc-module {
  background: #fafafa;
  border-radius: 6px;
  padding: 16px 20px;
  margin-bottom: 12px;
}
.help-doc-module:last-child {
  margin-bottom: 0;
}
.help-doc-title {
  font-weight: 700;
  font-size: 14px;
  color: var(--prev-color-text-regular);
  margin-bottom: 10px;
}
.form-actions {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.form-actions .el-button + .el-button {
  margin-left: 0;
}
</style>
