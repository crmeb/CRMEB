<template>
  <div class="skill-manage">
    <el-card shadow="never" class="mt16">
      <div class="toolbar">
        <div>
          <div class="toolbar-title">技能文件</div>
          <div class="toolbar-tip">
            Skill 以 Markdown 文件存储，Agent 对话时按选择注入上下文（Markdown 指南、流程说明等）。
          </div>
        </div>
        <el-button type="primary" icon="Plus" v-db-click @click="openEditor()">新建 Skill</el-button>
      </div>

      <el-table :data="list" v-loading="loading" row-key="key">
        <el-table-column label="Skill" min-width="200">
          <template #default="scope">
            <div class="skill-name">{{ scope.row.name }}</div>
            <div class="muted mono">{{ scope.row.key }}</div>
          </template>
        </el-table-column>
        <el-table-column label="描述" min-width="260" show-overflow-tooltip>
          <template #default="scope">{{ scope.row.description || '暂无描述' }}</template>
        </el-table-column>
        <el-table-column label="更新时间" min-width="150">
          <template #default="scope">{{ formatTime(scope.row.update_time) }}</template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              @change="changeStatus(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="190">
          <template #default="scope">
            <a v-db-click @click="openEditor(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="openAttachments(scope.row)">附件</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="removeSkill(scope.row)">删除</a>
          </template>
        </el-table-column>
        <template #empty>暂无 Skill，点击右上角「新建 Skill」创建</template>
      </el-table>
    </el-card>

    <!-- Skill 编辑抽屉（右侧） -->
    <el-drawer
      :title="editing ? '编辑 Skill' : '新建 Skill'"
      v-model="editorVisible"
      direction="rtl"
      size="60%"
      append-to-body
      class="ai-skill-drawer"
      :close-on-click-modal="false"
    >
      <div class="drawer-body">
        <el-form ref="skillForm" :model="form" :rules="rules" label-width="90px">
          <el-row :gutter="16">
            <el-col :span="12">
              <el-form-item label="标识" prop="key">
                <el-input
                  v-model.trim="form.key"
                  :disabled="editing"
                  maxlength="64"
                  placeholder="小写字母开头，可含数字、-、_"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="名称" prop="name">
                <el-input v-model.trim="form.name" maxlength="100" placeholder="Skill 显示名称" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-form-item label="描述">
            <el-input v-model.trim="form.description" maxlength="300" placeholder="一句话说明该 Skill 的用途">
              <template #append>
                <el-button :loading="descGenerating" v-db-click @click="generateDescription">AI 生成</el-button>
              </template>
            </el-input>
          </el-form-item>
          <el-form-item label="AI 写作">
            <el-button type="primary" plain v-db-click @click="openAiWrite('skill')">
              {{ form.content.trim() ? 'AI 优化正文' : 'AI 生成正文' }}
            </el-button>
            <span class="ai-inline-tip">
              {{
                form.content.trim()
                  ? '基于现有正文输入修改意见，AI 改写完善后替换正文'
                  : 'Markdown 源码编辑；生成结果将以纯 Markdown 追加到正文末尾'
              }}
            </span>
          </el-form-item>
          <el-form-item label="正文" prop="content">
            <el-input
              v-model="form.content"
              type="textarea"
              placeholder="Markdown 正文，将作为上下文注入选择了该 Skill 的 Agent 对话"
              class="content-editor"
            />
          </el-form-item>
          <el-form-item label="状态">
            <el-radio-group v-model="form.status">
              <el-radio :value="1">启用</el-radio>
              <el-radio :value="0">停用</el-radio>
            </el-radio-group>
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <span>
          <el-button @click="editorVisible = false">取消</el-button>
          <el-button type="primary" :loading="saving" v-db-click @click="saveSkill">保存</el-button>
        </span>
      </template>
    </el-drawer>

    <!-- 附属资料管理：技能目录下随 SKILL.md 存储的 .md 文件，Agent 经 crmeb_skill_load 按需加载 -->
    <el-drawer
      :title="`附属资料 - ${currentSkill.name}`"
      v-model="attachmentsVisible"
      direction="rtl"
      size="60%"
      append-to-body
      class="ai-skill-drawer"
      :close-on-click-modal="false"
    >
      <div class="drawer-body">
        <div class="attach-tip">
          附属资料不随对话注入，Agent 判断需要更完整资料时按需加载；文件为 .md 格式（SKILL.md 与 EXAMPLES.md
          为保留名）。
        </div>
        <div class="attach-toolbar">
          <span class="attach-count">共 {{ attachmentList.length }} 个文件</span>
          <el-button type="primary" icon="Plus" v-db-click @click="openAttachmentEditor()">新建附件</el-button>
        </div>
        <el-table :data="attachmentList" v-loading="attachmentLoading" row-key="file">
          <el-table-column label="文件名" min-width="200">
            <template #default="scope">
              <div class="skill-name mono">{{ scope.row.file }}</div>
            </template>
          </el-table-column>
          <el-table-column label="标题" min-width="160" show-overflow-tooltip>
            <template #default="scope">{{ scope.row.title || '—' }}</template>
          </el-table-column>
          <el-table-column label="大小" width="80">
            <template #default="scope">{{ formatSize(scope.row.size) }}</template>
          </el-table-column>
          <el-table-column label="更新时间" min-width="130">
            <template #default="scope">{{ formatTime(scope.row.update_time) }}</template>
          </el-table-column>
          <el-table-column label="操作" width="110" fixed="right">
            <template #default="scope">
              <a v-db-click @click="openAttachmentEditor(scope.row)">编辑</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="removeAttachment(scope.row)">删除</a>
            </template>
          </el-table-column>
          <template #empty>暂无附属资料，点击右上角「新建附件」添加</template>
        </el-table>
      </div>
      <template #footer>
        <span>
          <el-button @click="attachmentsVisible = false">关闭</el-button>
        </span>
      </template>
    </el-drawer>

    <!-- 附属资料编辑抽屉（右侧） -->
    <el-drawer
      :title="attachmentEditing ? '编辑附件' : '新建附件'"
      v-model="attachmentEditorVisible"
      direction="rtl"
      size="60%"
      append-to-body
      class="ai-skill-drawer"
      :close-on-click-modal="false"
    >
      <div class="drawer-body">
        <el-form ref="attachmentFormRef" :model="attachmentForm" :rules="attachmentRules" label-width="90px">
          <el-form-item label="文件名" prop="file">
            <el-input
              v-model.trim="attachmentForm.file"
              :disabled="attachmentEditing"
              maxlength="100"
              placeholder="如：功能索引（.md 后缀自动添加，不含路径分隔符）"
            />
          </el-form-item>
          <el-form-item label="AI 写作">
            <el-button type="primary" plain v-db-click @click="openAiWrite('attachment')">
              {{ attachmentForm.content.trim() ? 'AI 优化内容' : 'AI 生成内容' }}
            </el-button>
            <span class="ai-inline-tip">
              {{
                attachmentForm.content.trim()
                  ? '基于现有内容输入修改意见，AI 改写完善后替换内容'
                  : 'Markdown 源码编辑；生成结果将以纯 Markdown 追加到内容末尾'
              }}
            </span>
          </el-form-item>
          <el-form-item label="内容" prop="content">
            <el-input
              v-model="attachmentForm.content"
              type="textarea"
              placeholder="Markdown 全文；Agent 加载后以原文为准"
              class="content-editor"
            />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <span>
          <el-button @click="attachmentEditorVisible = false">取消</el-button>
          <el-button type="primary" :loading="attachmentSaving" v-db-click @click="saveAttachment">保存</el-button>
        </span>
      </template>
    </el-drawer>

    <!-- AI 写作弹窗：提示词一律留空由用户直接输入需求（格式文体要求由系统提示词约束）；rewrite=基于现有内容 + 用户修改意见改写（结果整体替换） -->
    <AiWriteModal
      v-model="aiWriteVisible"
      :target-label="aiWriteTarget === 'skill' ? 'Skill 正文' : '附件内容'"
      :mode="aiWriteMode"
      :source-text="aiWriteTarget === 'skill' ? form.content : attachmentForm.content"
      :context-text="aiWriteContext"
      @insert="onAiInsert"
    />
  </div>
</template>

<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  aiChatApi,
  aiSkillAttachmentDeleteApi,
  aiSkillAttachmentListApi,
  aiSkillAttachmentReadApi,
  aiSkillAttachmentSaveApi,
  aiSkillDeleteApi,
  aiSkillDetailApi,
  aiSkillListApi,
  aiSkillSaveApi,
  aiSkillStatusApi,
  aiSkillUpdateApi,
} from '../api';
import AiWriteModal from './AiWriteModal.vue';

defineOptions({ name: 'SkillManage' });

const emptyForm = () => ({
  key: '',
  name: '',
  description: '',
  content: '',
  status: 1,
});

const loading = ref(false);
const saving = ref(false);
const editing = ref(false);
const editorVisible = ref(false);
const list = ref([]);
const form = ref(emptyForm());
const skillForm = ref(null);
const rules = {
  key: [
    { required: true, message: '请输入标识', trigger: 'blur' },
    { pattern: /^[a-z][a-z0-9_-]{0,63}$/, message: '小写字母开头，仅可含数字、-、_，最长 64 位', trigger: 'blur' },
  ],
  name: [{ required: true, message: '请输入名称', trigger: 'blur' }],
  content: [{ required: true, message: '请输入正文内容', trigger: 'blur' }],
};

const loadList = () => {
  loading.value = true;
  aiSkillListApi()
    .then((res) => {
      list.value = res.data || [];
    })
    .finally(() => {
      loading.value = false;
    });
};

const formatTime = (timestamp) => {
  if (!timestamp) return '-';
  const date = new Date(timestamp * 1000);
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(
    date.getMinutes(),
  )}`;
};

const openEditor = (row) => {
  if (!row) {
    editing.value = false;
    form.value = emptyForm();
    editorVisible.value = true;
    nextTick(() => skillForm.value && skillForm.value.clearValidate());
    return;
  }
  aiSkillDetailApi(row.key).then((res) => {
    const data = res.data || {};
    editing.value = true;
    form.value = {
      key: data.key || row.key,
      name: data.name || '',
      description: data.description || '',
      content: data.content || '',
      status: Number(data.status) === 0 ? 0 : 1,
    };
    editorVisible.value = true;
    nextTick(() => skillForm.value && skillForm.value.clearValidate());
  });
};

const saveSkill = () => {
  skillForm.value.validate((valid) => {
    if (!valid) return;
    saving.value = true;
    const request = editing.value ? aiSkillUpdateApi(form.value.key, form.value) : aiSkillSaveApi(form.value);
    request
      .then((res) => {
        ElMessage.success(res.msg || '保存成功');
        editorVisible.value = false;
        loadList();
      })
      .finally(() => {
        saving.value = false;
      });
  });
};

/* 描述 AI 生成：基于名称与正文（若有）生成 frontmatter 的 description（对话时模型据此判断是否加载该技能） */
const descGenerating = ref(false);
// 清洗描述：剥离模型复述任务的引导语（如「根据…生成符合30-60字要求的表述：」），仅当首个冒号前片段像元指令时截取冒号后内容
const sanitizeDesc = (text) => {
  let t = text
    .trim()
    .replace(/^["'「『]+|["'」』]+$/g, '')
    .replace(/\s+/g, ' ');
  const idx = t.search(/[：:]/);
  if (idx > -1) {
    const head = t.slice(0, idx);
    if (head.length <= 30 && /生成|要求|输出|描述|表述|撰写/.test(head)) {
      t = t.slice(idx + 1).trim();
    }
  }
  return t;
};
const generateDescription = () => {
  const name = form.value.name.trim();
  if (!name) {
    ElMessage.error('请先填写名称');
    return;
  }
  descGenerating.value = true;
  const content = form.value.content.trim();
  const prompt =
    `请为管理后台 AI 助手技能「${name}」撰写 description 字段。\n` +
    (content ? `【技能正文节选】\n${content.slice(0, 2000)}\n\n` : '') +
    '要求：第一句就是描述本身（30-60 字），说明该技能解决什么问题、适用于哪些请求；禁止复述本任务的要求或任何引导语（如「根据…生成…如下」），不要解释、引号或前后缀。';
  aiChatApi(prompt, [
    '你是 CRMEB 管理后台的 Skill 元信息助手。description 用于对话时模型判断当前问题是否属于该技能的适用范围，必须写清技能的适用边界，禁止空话套话。直接输出描述文本本身。',
  ])
    .then((res) => {
      const text = sanitizeDesc(String(res.data?.content || '')).slice(0, 300);
      if (!text) {
        ElMessage.error('AI 未返回描述，请重试');
        return;
      }
      form.value.description = text;
      ElMessage.success('描述已生成，可编辑微调');
    })
    .finally(() => {
      descGenerating.value = false;
    });
};

const changeStatus = (row) => {
  aiSkillStatusApi(row.key, row.status)
    .then(() => {
      ElMessage.success('修改成功');
    })
    .catch(() => {
      row.status = row.status === 1 ? 0 : 1;
    });
};

const removeSkill = (row) => {
  ElMessageBox.confirm(
    `确定删除 Skill“${row.name}”吗？该操作会删除 SKILL.md 及其全部附属资料文件，不可恢复。`,
    '提示',
    { type: 'warning' },
  )
    .then(() => aiSkillDeleteApi(row.key))
    .then(() => {
      ElMessage.success('删除成功');
      loadList();
    })
    .catch(() => {});
};

/* ---------------- 附属资料管理 ---------------- */

const currentSkill = ref({ key: '', name: '' });
const attachmentsVisible = ref(false);
const attachmentLoading = ref(false);
const attachmentList = ref([]);
const attachmentEditorVisible = ref(false);
const attachmentEditing = ref(false);
const attachmentSaving = ref(false);
const attachmentForm = ref({ file: '', content: '' });
const attachmentFormRef = ref(null);
const attachmentRules = {
  file: [
    { required: true, message: '请输入文件名', trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        // 用户无需输入 .md 后缀（输入了也兼容），保存时统一补全
        const base = value.replace(/\.md$/i, '');
        if (!/^[^\\/:*?"<>|]+$/.test(base) || base.startsWith('..')) {
          callback(new Error('不含路径分隔符与特殊字符'));
        } else if (/^(SKILL|EXAMPLES)$/i.test(base)) {
          callback(new Error('SKILL 与 EXAMPLES 为保留文件名'));
        } else {
          callback();
        }
      },
      trigger: 'blur',
    },
  ],
  content: [{ required: true, message: '请输入附件内容', trigger: 'blur' }],
};

const openAttachments = (row) => {
  currentSkill.value = { key: row.key, name: row.name };
  attachmentsVisible.value = true;
  loadAttachments();
};

const loadAttachments = () => {
  attachmentLoading.value = true;
  aiSkillAttachmentListApi(currentSkill.value.key)
    .then((res) => {
      attachmentList.value = res.data || [];
    })
    .finally(() => {
      attachmentLoading.value = false;
    });
};

const formatSize = (size) => {
  if (!size && size !== 0) return '-';
  if (size < 1024) return `${size} B`;
  return `${(size / 1024).toFixed(1)} KB`;
};

const openAttachmentEditor = (row) => {
  if (!row) {
    attachmentEditing.value = false;
    attachmentForm.value = { file: '', content: '' };
    attachmentEditorVisible.value = true;
    nextTick(() => attachmentFormRef.value && attachmentFormRef.value.clearValidate());
    return;
  }
  aiSkillAttachmentReadApi(currentSkill.value.key, row.file).then((res) => {
    const data = res.data || {};
    attachmentEditing.value = true;
    attachmentForm.value = { file: data.file || row.file, content: data.content || '' };
    attachmentEditorVisible.value = true;
    nextTick(() => attachmentFormRef.value && attachmentFormRef.value.clearValidate());
  });
};

const saveAttachment = () => {
  attachmentFormRef.value.validate((valid) => {
    if (!valid) return;
    attachmentSaving.value = true;
    // 文件名统一补全 .md 后缀后提交
    const payload = { ...attachmentForm.value, file: attachmentForm.value.file.replace(/\.md$/i, '') + '.md' };
    aiSkillAttachmentSaveApi(currentSkill.value.key, payload)
      .then((res) => {
        ElMessage.success(res.msg || '保存成功');
        attachmentEditorVisible.value = false;
        loadAttachments();
      })
      .finally(() => {
        attachmentSaving.value = false;
      });
  });
};

const removeAttachment = (row) => {
  ElMessageBox.confirm(`确定删除附件“${row.file}”吗？删除后 Agent 将无法再加载该资料。`, '提示', { type: 'warning' })
    .then(() => aiSkillAttachmentDeleteApi(currentSkill.value.key, row.file))
    .then(() => {
      ElMessage.success('删除成功');
      loadAttachments();
    })
    .catch(() => {});
};

/* ---------------- AI 写作（write：按名称/描述自动构造提示词并自动生成；rewrite：基于现有内容+修改意见改写） ---------------- */

const aiWriteVisible = ref(false);
const aiWriteTarget = ref('skill');
// 写作模式：write=从零撰写；rewrite=基于现有内容改写（正文/内容非空时自动切换）
const aiWriteMode = ref('write');
// 附加上下文（技能名称/描述等元信息），随请求携带帮助 AI 理解目标定位；对应文件 frontmatter，由保存时后端重建
const aiWriteContext = ref('');

const openAiWrite = (target) => {
  aiWriteTarget.value = target;
  const isSkill = target === 'skill';
  const existing = (isSkill ? form.value.content : attachmentForm.value.content).trim();
  const name = form.value.name.trim();
  const desc = form.value.description.trim();
  const file = attachmentForm.value.file.trim();
  aiWriteContext.value = isSkill
    ? (name ? `技能名称：${name}\n` : '') + (desc ? `技能描述：${desc}` : '')
    : file
    ? `文件名：${file}`
    : '';
  aiWriteMode.value = existing ? 'rewrite' : 'write';
  aiWriteVisible.value = true;
};

// 回填：rewrite 模式整体替换（改写完善）；write 模式追加到末尾（无损保留已有内容）
const onAiInsert = (text) => {
  const replace = aiWriteMode.value === 'rewrite';
  if (aiWriteTarget.value === 'skill') {
    form.value.content = replace
      ? text + '\n'
      : (form.value.content ? form.value.content.replace(/\s+$/, '') + '\n\n' : '') + text + '\n';
  } else {
    attachmentForm.value.content = replace
      ? text + '\n'
      : (attachmentForm.value.content ? attachmentForm.value.content.replace(/\s+$/, '') + '\n\n' : '') + text + '\n';
  }
};

// 编辑器关闭时联动关闭 AI 写作弹窗，防止 AI 弹窗脱离父级残留悬浮
watch(editorVisible, (open) => {
  if (!open) aiWriteVisible.value = false;
});
watch(attachmentEditorVisible, (open) => {
  if (!open) aiWriteVisible.value = false;
});

onMounted(() => {
  loadList();
});
</script>

<style scoped lang="scss">
.skill-manage {
  .mt16 {
    margin-top: 0;
  }
}
.mt16 {
  margin-top: 16px;
}
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.toolbar-title {
  color: var(--prev-color-text-regular);
  font-size: 18px;
  font-weight: 600;
}
.toolbar-tip,
.muted {
  margin-top: 6px;
  color: var(--prev-color-text-secondary);
  font-size: 13px;
}
.skill-name {
  color: var(--prev-color-text-regular);
  font-weight: 600;
}
.mono {
  font-family: Menlo, Consolas, monospace;
  font-size: 12px;
}
.content-editor :deep(.el-textarea__inner) {
  height: max(calc(100vh - 310px), 520px);
  resize: vertical;
  font-family: Menlo, Consolas, monospace;
  font-size: 13px;
  line-height: 1.7;
}
.drawer-body {
  /* 左右留白由 .ai-skill-drawer 全局规则（16px）提供，此处只补底部间距，避免双重内边距 */
  padding: 0 0 10px;
}
.ai-inline-tip {
  margin-left: 10px;
  color: var(--prev-color-text-secondary);
  font-size: 12px;
}
/* 附属资料抽屉：说明独立成行，操作行（计数 + 新建按钮）右对齐，与卡片页工具栏同构 */
.attach-tip {
  margin-bottom: 14px;
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 18px;
}
.attach-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}
.attach-count {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
}
</style>

<style lang="scss">
/* 三个抽屉均 append-to-body 挂载在 body 下，scoped 样式无法命中，
   用业务前缀类收窄 body 水平内边距（全局 35px 对 60% 宽抽屉偏大） */
.ai-skill-drawer .el-drawer__body {
  padding: 16px;
}
</style>
