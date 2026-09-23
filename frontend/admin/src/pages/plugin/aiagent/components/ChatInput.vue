<template>
  <div class="chat-input">
    <!-- 快捷场景标签：悬浮在输入框上方常显，点击回填输入框 -->
    <div class="input-topics">
      <button v-for="topic in topics" :key="topic.label" class="topic-tag" type="button" @click="fillTopic(topic)">
        <el-icon><component :is="topic.icon" /></el-icon>
        <span>{{ topic.label }}</span>
      </button>
    </div>

    <!-- 输入卡片 -->
    <div class="input-card">
      <el-input
        ref="inputRef"
        v-model="input"
        type="textarea"
        :rows="2"
        maxlength="4000"
        resize="none"
        class="input-textarea"
        placeholder="输入问题，按 Enter 发送，Shift+Enter 换行"
        @keydown.enter="handleEnterKey"
      />
      <div class="input-card-toolbar">
        <div class="toolbar-left">
          <!-- 连接器选择：多选弹出面板，空=默认挂载全部可用项 -->
          <el-popover
            v-if="mcpServers.length"
            placement="top-start"
            trigger="click"
            :width="280"
            popper-class="model-popover"
          >
            <div class="model-panel">
              <div class="panel-tip">未勾选时默认挂载全部可用连接器</div>
              <div
                v-for="server in mcpServers"
                :key="server.id"
                :class="['model-item', { active: serverIds.includes(server.id) }]"
                @click="toggleInList(serverIds, server.id)"
              >
                <span class="model-name">{{ server.name }}</span>
                <i v-if="serverIds.includes(server.id)" class="el-icon-check" />
              </div>
              <div class="model-item model-manage" @click="emit('manage', 'mcp')">
                <div class="model-info">
                  <span class="model-name"
                    ><el-icon><EditPen /></el-icon> 管理 MCP 连接器</span
                  >
                </div>
              </div>
            </div>
            <template #reference>
              <button class="model-trigger" type="button">
                <i class="el-icon-connection" />
                <span>{{ serverIds.length ? `连接器 ${serverIds.length}` : '全部连接器' }}</span>
                <i class="el-icon-arrow-down" />
              </button>
            </template>
          </el-popover>
          <!-- Skill 选择：显式选中的 Skill 全文注入，空=按目录按需加载 -->
          <el-popover
            v-if="skills.length"
            placement="top-start"
            trigger="click"
            :width="280"
            popper-class="model-popover"
          >
            <div class="model-panel">
              <div class="panel-tip">未勾选时按需加载：AI 判断问题相关后自动加载规则</div>
              <div
                v-for="skill in skills"
                :key="skill.key"
                :class="['model-item', { active: skillKeys.includes(skill.key) }]"
                @click="toggleInList(skillKeys, skill.key)"
              >
                <span class="model-name">{{ skill.name }}</span>
                <i v-if="skillKeys.includes(skill.key)" class="el-icon-check" />
              </div>
              <div class="model-item model-manage" @click="emit('manage', 'skill')">
                <div class="model-info">
                  <span class="model-name"
                    ><el-icon><EditPen /></el-icon> 管理 Skill</span
                  >
                </div>
              </div>
            </div>
            <template #reference>
              <button class="model-trigger" type="button">
                <i class="el-icon-collection-tag" />
                <span>{{ skillKeys.length ? `Skill ${skillKeys.length}` : 'Skill 规则' }}</span>
                <i class="el-icon-arrow-down" />
              </button>
            </template>
          </el-popover>
        </div>
        <div class="toolbar-right">
          <!-- 模型选择：弹出面板（当前模型名 + 列表勾选 + 配置入口） -->
          <el-popover placement="top-end" trigger="click" :width="300" popper-class="model-popover">
            <div class="model-panel">
              <div
                v-for="model in models"
                :key="model.id"
                :class="['model-item', { active: modelId === model.id }]"
                @click="emit('select-model', model)"
              >
                <span class="model-name">{{ model.name }}</span>
                <i v-if="modelId === model.id" class="el-icon-check" />
              </div>
              <div class="model-item model-manage" @click="emit('manage', 'model')">
                <div class="model-info">
                  <span class="model-name"
                    ><el-icon><EditPen /></el-icon> 配置自定义模型</span
                  >
                </div>
              </div>
            </div>
            <template #reference>
              <button class="model-trigger" type="button">
                <i class="el-icon-cpu" />
                <span>{{ currentModelName }}</span>
                <i class="el-icon-arrow-down" />
              </button>
            </template>
          </el-popover>
          <!-- 发送 / 停止：流式输出进行中按钮变为红色停止态，点击终止输出 -->
          <button v-if="chatting" class="send-btn stop" type="button" v-db-click @click="emit('stop')">
            <el-icon><VideoPause /></el-icon>
          </button>
          <button v-else class="send-btn" type="button" :disabled="!input.trim()" v-db-click @click="emit('send')">
            <el-icon><Top /></el-icon>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';

defineOptions({ name: 'AiAgentChatInput' });

const props = defineProps({
  /** 快捷场景标签（label/icon/prompt） */
  topics: { type: Array, default: () => [] },
  /** 可选模型列表（id/name） */
  models: { type: Array, default: () => [] },
  /** 当前模型 ID */
  modelId: { type: Number, default: 0 },
  /** 可选 MCP 连接器（id/name，启用中） */
  mcpServers: { type: Array, default: () => [] },
  /** 可选 Skill（key/name，启用中） */
  skills: { type: Array, default: () => [] },
  /** 是否有流式请求进行中（进行中禁止发送） */
  chatting: { type: Boolean, default: false },
});

/** send()：回车或点击发送（内容由父层从 v-model 取）；stop()：终止流式输出；select-model(model)；manage('model'|'mcp'|'skill') */
const emit = defineEmits(['send', 'stop', 'select-model', 'manage']);

/** 输入框内容（v-model:input 双向绑定，真实状态由父层持有） */
const input = defineModel('input', { type: String, default: '' });

/** 选中的连接器 ID / Skill 标识（v-model 双向绑定，真实状态由父层持有；空数组=默认全部/按需加载） */
const serverIds = defineModel('serverIds', { type: Array, default: () => [] });
const skillKeys = defineModel('skillKeys', { type: Array, default: () => [] });

/** 列表多选切换（勾选/取消一项） */
function toggleInList(list, value) {
  const next = list.includes(value) ? list.filter((item) => item !== value) : [...list, value];
  if (list === serverIds.value) {
    serverIds.value = next;
  } else {
    skillKeys.value = next;
  }
}

const inputRef = ref(null);

const currentModelName = computed(() => {
  const model = props.models.find((item) => item.id === props.modelId);
  return model ? model.name : '一号通AI';
});

/** 回车发送 / Shift+回车换行（中文输入法组词期间的回车不触发；流式进行中回车不发送） */
function handleEnterKey(event) {
  if (event.shiftKey || event.isComposing || props.chatting) {
    return;
  }
  event.preventDefault();
  emit('send');
}

/** 快捷场景标签：仅把预设问题回填输入框并聚焦，不自动发送 */
function fillTopic(topic) {
  input.value = topic.prompt;
  focus();
}

/** 聚焦输入框并把光标移到末尾（供父级编辑回填后调用） */
function focus() {
  nextTick(() => {
    const textarea = inputRef.value?.textarea || inputRef.value?.$el?.querySelector('textarea');
    if (textarea) {
      textarea.focus();
      textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    }
  });
}

defineExpose({ focus });
</script>

<style scoped lang="scss">
/* 根容器：宽屏时限宽居中，与消息区 max-width 一致；32px 水平内边距在此统一承载，保证与消息内容对齐 */
.chat-input {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 0 32px;
}
/* 快捷场景标签：悬浮输入框上方常显 */
.input-topics {
  display: flex;
  gap: 10px;
  margin: 0 0 10px;
  flex-wrap: wrap;
}
.topic-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  color: #606266;
  font-size: 13px;
  cursor: pointer;
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 999px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: color 0.15s, border-color 0.15s, background 0.15s;
  i {
    font-size: 14px;
  }
  &:hover {
    color: var(--prev-color-primary);
    background: #f0f7ff;
    border-color: var(--prev-color-primary);
  }
}
/* 输入卡片 */
.input-card {
  margin: 0 0 24px;
  border: 1px solid #e4e7ed;
  border-radius: 20px;
  transition: border-color 0.2s;
  &:focus-within {
    border-color: var(--prev-color-primary);
  }
}
.input-card :deep(.el-textarea__inner) {
  padding: 16px 18px 0;
  color: #303133;
  font-size: 14px;
  background: transparent;
  border: none;
  box-shadow: none;
}
.input-card-toolbar {
  display: flex;
  align-items: center;
  padding: 8px 14px 12px;
}
/* 左侧：连接器与 Skill 选择（可空=默认全部/按需加载） */
.toolbar-left {
  display: flex;
  align-items: center;
  gap: 4px;
  flex: 1;
  min-width: 0;
  overflow: hidden;
}
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}
.panel-tip {
  padding: 2px 10px 8px;
  color: #909399;
  font-size: 12px;
}
.model-trigger {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  max-width: 220px;
  padding: 6px 10px;
  color: #303133;
  font-size: 13px;
  cursor: pointer;
  background: transparent;
  border: none;
  border-radius: 8px;
  &:hover {
    color: var(--prev-color-primary);
    background: #f2f3f5;
  }
  span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
.send-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  color: #fff;
  font-size: 17px;
  cursor: pointer;
  background: var(--prev-color-primary);
  border: none;
  border-radius: 50%;
  transition: opacity 0.2s;
  &:hover:not(:disabled) {
    opacity: 0.85;
  }
  &:disabled {
    color: #a8abb2;
    cursor: not-allowed;
    background: #e9e9eb;
  }
  /* 停止态：流式输出进行中，点击终止输出 */
  &.stop {
    cursor: pointer;
    background: #f56c6c;
    &:hover {
      opacity: 0.85;
    }
  }
}
/* 模型弹出面板 */
.model-panel {
  max-height: 320px;
  overflow-y: auto;
}
.model-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 10px;
  cursor: pointer;
  border-radius: 6px;
  &:hover {
    background: #f5f7fa;
  }
  &.active {
    color: var(--prev-color-primary);
    background: #f0f7ff;
    i {
      color: var(--prev-color-primary);
    }
  }
  .el-icon-check {
    font-weight: 600;
  }
}
.model-name {
  overflow: hidden;
  font-size: 14px;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.model-manage {
  color: #606266;
  border-top: 1px solid #ebeef5;
  border-radius: 0;
  &:hover {
    color: var(--prev-color-primary);
  }
}
</style>
