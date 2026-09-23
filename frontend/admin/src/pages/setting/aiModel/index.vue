<template>
  <div class="model-manage">
    <!-- 页面说明头 -->
    <pages-header
      class="mb16"
      title="模型管理"
      description="填写接口地址与密钥即可接入 DeepSeek、通义、Kimi 等厂商，同协议厂商无需重复开发。"
    />

    <!-- 数据卡 -->
    <el-card shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <!-- 不用 el-row：全局样式将 .el-row:first-child 的 margin-top 强制为 0，会吃掉顶部间距 -->
      <div class="mt14">
        <el-button type="primary" v-db-click @click="openEditor()">新增模型</el-button>
      </div>

      <el-table :data="tableRows" v-loading="loading" row-key="id" class="mt14">
        <el-table-column label="模型" min-width="200">
          <template #default="scope">
            <div class="model-name">
              {{ scope.row.name }}
              <el-tag v-if="scope.row.is_default === 1" size="small" type="warning">默认</el-tag>
            </div>
            <div class="muted">{{ scope.row.model }}</div>
          </template>
        </el-table-column>
        <el-table-column label="接口地址" min-width="200" show-overflow-tooltip>
          <template #default="scope">{{ scope.row.base_url || '-' }}</template>
        </el-table-column>
        <el-table-column label="密钥" min-width="140">
          <template #default="scope">
            <span v-if="scope.row.virtual && !scope.row.configured" class="danger">未配置，请一键配置</span>
            <span v-else-if="scope.row.virtual" class="muted">复用一号通凭证</span>
            <span v-else class="mono">{{ scope.row.has_api_key ? scope.row.api_key : '未配置' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="能力" min-width="120">
          <template #default="scope">
            <el-tag v-if="scope.row.supports_tools === 1" size="small" type="success">工具调用</el-tag>
            <el-tag v-if="scope.row.supports_vision === 1" size="small" type="info">视觉</el-tag>
            <span v-if="scope.row.supports_tools !== 1 && scope.row.supports_vision !== 1" class="muted">-</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="scope">
            <span v-if="scope.row.virtual" class="muted">-</span>
            <el-switch
              v-else
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
              @change="changeStatus(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template #default="scope">
            <template v-if="scope.row.virtual">
              <a v-if="!scope.row.configured" v-db-click @click="goYihaotongConfig">一键配置</a>
              <span v-else-if="scope.row.is_default === 1" class="muted">内置模型</span>
              <a v-else v-db-click @click="setDefault(scope.row)">设为默认</a>
            </template>
            <template v-else>
              <span v-if="scope.row.is_default === 1 || scope.row.status !== 1" class="muted">设为默认</span>
              <a v-else v-db-click @click="setDefault(scope.row)">设为默认</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="openEditor(scope.row)">编辑</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="removeModel(scope.row)">删除</a>
            </template>
          </template>
        </el-table-column>
      </el-table>

      <div class="acea-row row-right page">
        <pagination v-if="count" :total="count" v-model:page="page" v-model:limit="limit" @pagination="loadList" />
      </div>
    </el-card>

    <el-dialog
      v-model="editorVisible"
      :title="form.id ? '编辑 AI 模型' : '新增 AI 模型'"
      width="640px"
      top="8vh"
      append-to-body
      :close-on-click-modal="false"
    >
      <el-form ref="modelForm" :model="form" :rules="rules" label-position="top">
        <el-form-item prop="base_url">
          <div class="base-url-head">
            <span class="base-url-label"><i class="required-star">*</i>自定义请求地址</span>
            <span class="base-url-switch">
              <i class="el-icon-link" />
              <span>完整 URL</span>
              <el-switch v-model="form.full_url" :active-value="1" :inactive-value="0" />
            </span>
          </div>
          <div class="field-tip">
            {{
              form.full_url === 1
                ? '请填写完整请求 URL，请求将直接使用此 URL，不拼接路径。'
                : '请填写兼容 OpenAI API 的服务端点地址，不要以斜杠结尾。/chat/completions 将会被补全到你填写的地址末尾。'
            }}
          </div>
          <el-input
            v-model.trim="form.base_url"
            maxlength="255"
            :placeholder="
              form.full_url === 1 ? '例如 https://api.openai.com/v1/chat/completions' : '例如 https://api.openai.com/v1'
            "
          />
        </el-form-item>
        <el-form-item label="模型 ID" prop="model">
          <el-input
            v-model.trim="form.model"
            maxlength="100"
            placeholder="输入模型 ID，例如 deepseek-chat、qwen-plus"
          />
        </el-form-item>
        <el-form-item label="模型名称" prop="name">
          <el-input
            v-model.trim="form.name"
            maxlength="32"
            placeholder="请输入模型名称"
            show-word-limit
            @input="nameTouched = true"
          />
          <div class="field-tip">在模型列表中展示的名称，未设置时默认显示模型 ID。</div>
        </el-form-item>
        <el-form-item label="API 密钥">
          <el-input
            v-model.trim="form.api_key"
            maxlength="255"
            show-password
            :placeholder="form.id ? '留空保持原密钥不变' : '厂商控制台签发的 API Key'"
          />
        </el-form-item>
        <el-collapse v-model="advancedOpen" class="advanced-collapse">
          <el-collapse-item title="高级配置" name="advanced">
            <el-form-item label="模型能力">
              <el-checkbox v-model="form.supports_tools" :true-value="1" :false-value="0">支持工具调用</el-checkbox>
              <el-checkbox v-model="form.supports_vision" :true-value="1" :false-value="0">支持视觉输入</el-checkbox>
            </el-form-item>
            <el-form-item label="采样温度">
              <el-slider
                v-model="form.temperature"
                :min="0"
                :max="2"
                :step="0.1"
                show-input
                :show-input-controls="false"
              />
            </el-form-item>
            <el-form-item label="最大输出 Token">
              <el-input-number v-model="form.max_tokens" :min="1" :max="128000" :step="256" />
              <span class="field-tip inline-tip">0 表示使用服务端默认值</span>
            </el-form-item>
            <el-form-item label="上下文窗口">
              <el-input-number v-model="form.context_window" :min="0" :max="10000000" :step="1000" />
              <span class="field-tip inline-tip">仅用于展示，0 表示未填写</span>
            </el-form-item>
            <el-form-item label="排序">
              <el-input-number v-model="form.sort" :min="0" :max="9999" />
              <span class="field-tip inline-tip">数值越大越靠前</span>
            </el-form-item>
            <el-form-item label="状态">
              <el-radio-group v-model="form.status">
                <el-radio :value="1">启用</el-radio>
                <el-radio :value="0">停用</el-radio>
              </el-radio-group>
            </el-form-item>
            <!-- 停用的模型不能担任系统默认：仅在启用时展示默认勾选，切换停用时自动取消勾选 -->
            <el-form-item v-if="form.status === 1" label="默认模型">
              <el-checkbox v-model="form.is_default" :true-value="1" :false-value="0">设为系统默认模型</el-checkbox>
              <!-- 占满整行强制换行，避免提示文案与勾选框挤在同一行 -->
              <div class="field-tip default-tip">未勾选且无其他默认模型时，系统 AI模型 默认为内置一号通AI</div>
            </el-form-item>
          </el-collapse-item>
        </el-collapse>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <span class="footer-tip"><i class="el-icon-info" /> 连通性测试会发起一次真实请求，会消耗少量模型 Token</span>
          <span>
            <el-button v-db-click @click="resetForm">重置</el-button>
            <el-button type="primary" :loading="!!savingPhase" v-db-click @click="saveModel">{{
              savingText
            }}</el-button>
          </span>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useRouter } from 'vue-router';
import setting from '@/setting';
import {
  aiModelDeleteApi,
  aiModelDetailApi,
  aiModelListApi,
  aiModelSaveApi,
  aiModelSetDefaultApi,
  aiModelStatusApi,
  aiModelTestApi,
  aiModelUpdateApi,
} from '@/api/ai';

defineOptions({ name: 'AiModelManage' });

const router = useRouter();

const emptyForm = () => ({
  id: 0,
  name: '',
  protocol: 'openai_compatible',
  provider: '',
  model: '',
  base_url: '',
  api_key: '',
  full_url: 0,
  supports_tools: 0,
  supports_vision: 0,
  context_window: 0,
  max_tokens: 2048,
  temperature: 0.7,
  is_default: 0,
  sort: 0,
  status: 1,
});

const loading = ref(false);
// 保存按钮阶段：''=空闲，test=连通测试进行中，save=落库进行中；非空时按钮进入 loading 并禁用
const savingPhase = ref('');
// 按钮文案随阶段切换：空闲显示动作名，进行中显示当前阶段状态
const savingText = computed(() => {
  if (!savingPhase.value) return form.value.id ? '保存' : '添加模型';
  return savingPhase.value === 'test' ? '连通测试中' : '保存中';
});
const editorVisible = ref(false);
const list = ref([]);
const count = ref(0);
const page = ref(1);
const limit = ref(15);
const form = ref(emptyForm());
const advancedOpen = ref([]);
const nameTouched = ref(false);
const modelForm = ref(null);

const rules = {
  model: [{ required: true, message: '请填写模型 ID', trigger: 'blur' }],
  base_url: [
    {
      required: true,
      validator: (rule, value, callback) => {
        if (!/^https?:\/\//i.test(value || '')) {
          callback(new Error('接口地址必须以 http:// 或 https:// 开头'));
        } else {
          callback();
        }
      },
      trigger: 'blur',
    },
  ],
};

/** 模型名称默认自动跟随模型 ID，用户手动编辑过名称后停止同步 */
watch(
  () => form.value.model,
  (val) => {
    if (!nameTouched.value) {
      form.value.name = val;
    }
  },
);

/** 切换为停用时自动取消「设为系统默认模型」（勾选框同时隐藏），与后端强制清零逻辑保持一致 */
watch(
  () => form.value.status,
  (val) => {
    if (val === 0) {
      form.value.is_default = 0;
    }
  },
);

/** 内置一号通 AI 配置状态（未配置时提示「一键配置」并跳转一号通账户页） */
const yihaotongConfigured = ref(true);

/** 列表行 = 内置一号通模型（固定首行，不可编辑删除） + 登记模型 */
const tableRows = computed(() => {
  // 系统默认唯一：存在登记的默认模型时，一号通行不再标记默认
  const hasRegisteredDefault = list.value.some((row) => row.is_default === 1);
  return [
    {
      virtual: true,
      id: 0,
      name: hasRegisteredDefault ? '一号通AI' : '一号通AI(系统内置)',
      protocol_name: '一号通 AI',
      model: '由一号通平台决定',
      supports_tools: 1,
      is_default: hasRegisteredDefault ? 0 : 1,
      configured: yihaotongConfigured.value,
    },
    ...list.value,
  ];
});

const loadList = () => {
  loading.value = true;
  aiModelListApi({ page: page.value, limit: limit.value })
    .then((res) => {
      const data = res.data || {};
      list.value = data.list || [];
      count.value = data.count || 0;
      // 后端明确返回未配置时置为 false，其余（含老接口无字段）保持已配置的默认展示
      yihaotongConfigured.value = data.yihaotong_configured !== 0;
    })
    .finally(() => {
      loading.value = false;
    });
};

/** 一键配置：跳转一号通账户页完成 appid/密钥配置 */
const goYihaotongConfig = () => {
  router.push(setting.routePre + '/setting/yihaotong/index');
};

const openEditor = (row) => {
  if (!row) {
    form.value = emptyForm();
    nameTouched.value = false;
    advancedOpen.value = [];
    editorVisible.value = true;
    nextTick(() => modelForm.value && modelForm.value.clearValidate());
    return;
  }
  aiModelDetailApi(row.id).then((res) => {
    form.value = { ...emptyForm(), ...(res.data || {}) };
    nameTouched.value = !!form.value.name;
    advancedOpen.value = [];
    editorVisible.value = true;
    nextTick(() => modelForm.value && modelForm.value.clearValidate());
  });
};

/** 重置弹窗表单：新增时清空，编辑时还原为已保存数据 */
const resetForm = () => {
  if (form.value.id) {
    aiModelDetailApi(form.value.id).then((res) => {
      form.value = { ...emptyForm(), ...(res.data || {}) };
      nameTouched.value = !!form.value.name;
      nextTick(() => modelForm.value && modelForm.value.clearValidate());
    });
  } else {
    form.value = emptyForm();
    nameTouched.value = false;
    nextTick(() => modelForm.value && modelForm.value.clearValidate());
  }
};

/** 保存前先做连通性测试：测试通过才落库，失败时提示原因 */
const saveModel = () => {
  modelForm.value.validate((valid) => {
    if (!valid) return;
    // 第一阶段：连通测试，按钮禁用并显示「连通测试中」
    savingPhase.value = 'test';
    const payload = { ...form.value };
    delete payload.id;
    aiModelTestApi({ ...form.value })
      .then((res) => {
        const test = res.data || {};
        if (test.ok !== 1) {
          ElMessage.error(test.message || '连通性测试失败，请检查模型配置');
          return undefined;
        }
        // 第二阶段：落库，按钮切换为「保存中」
        savingPhase.value = 'save';
        const request = form.value.id ? aiModelUpdateApi(form.value.id, payload) : aiModelSaveApi(payload);
        return request.then(() => {
          ElMessage.success(form.value.id ? '修改成功' : '添加成功');
          editorVisible.value = false;
          loadList();
        });
      })
      .catch(() => {})
      .finally(() => {
        // 无论成功失败恢复按钮状态，允许用户调整配置后重试
        savingPhase.value = '';
      });
  });
};

const changeStatus = (row) => {
  aiModelStatusApi(row.id, row.status)
    .then(() => {
      ElMessage.success('修改成功');
      loadList();
    })
    .catch(() => {
      row.status = row.status === 1 ? 0 : 1;
    });
};

const setDefault = (row) => {
  aiModelSetDefaultApi(row.id)
    .then(() => {
      ElMessage.success('已设为系统默认模型');
      loadList();
    })
    .catch(() => {});
};

const removeModel = (row) => {
  ElMessageBox.confirm(`确定删除模型“${row.name}”吗？删除后对话中将无法再选择该模型。`, '提示', {
    type: 'warning',
  })
    .then(() => aiModelDeleteApi(row.id))
    .then(() => {
      ElMessage.success('删除成功');
      loadList();
    })
    .catch(() => {});
};

onMounted(() => {
  loadList();
});
</script>

<style scoped lang="scss">
/* 表格次级文本 */
.muted {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
}
.mono {
  font-family: Menlo, Consolas, monospace;
  font-size: 12px;
}
.danger {
  color: var(--prev-color-danger);
}
/* 表单字段下方提示文案 */
.field-tip {
  margin-top: 6px;
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 1.5;
}
.inline-tip {
  margin-left: 12px;
}
/* 自定义请求地址：标签行（左标题 + 右完整URL开关） */
.base-url-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  /* 标签(0) + 提示(6px) 合计 8px，对齐 EP top 标签与内容的默认间距 */
  margin-bottom: 2px;
}
.base-url-label {
  /* 与全局 .el-form-item__label（12px / text-regular）对齐，避免比其他标签大一圈 */
  color: var(--prev-color-text-regular);
  font-size: 12px;
  font-weight: 400;
  .required-star {
    margin-right: 4px;
    color: var(--prev-color-danger);
    font-style: normal;
  }
}
.base-url-switch {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--prev-color-text-primary);
  font-size: 12px;
  i {
    color: var(--prev-color-text-secondary);
  }
}
/* 默认模型提示：占满整行换行显示，与勾选框保持垂直间距 */
.default-tip {
  flex-basis: 100%;
}
/* 高级配置折叠面板 */
.advanced-collapse {
  margin-top: 4px;
  border-top: 1px solid var(--prev-border-color-lighter);
}
.advanced-collapse :deep(.el-collapse-item__header) {
  color: var(--prev-color-text-regular);
  font-size: 14px;
  font-weight: 600;
}
.advanced-collapse :deep(.el-collapse-item__content) {
  padding-bottom: 8px;
}
/* 弹窗底部：左侧连通性测试提示 + 右侧操作按钮 */
.dialog-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.footer-tip {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  i {
    margin-right: 4px;
    color: var(--prev-color-primary);
  }
}
</style>
