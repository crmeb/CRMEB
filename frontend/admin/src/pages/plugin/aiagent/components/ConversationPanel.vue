<template>
  <aside class="conversation-panel">
    <div class="panel-brand">AI 对话</div>
    <nav class="panel-nav">
      <div class="nav-item" @click="emit('create')">
        <el-icon><ChatDotSquare /></el-icon>
        <span>新建会话</span>
      </div>
      <div class="nav-item" @click="emit('nav', 'mcp')">
        <i class="el-icon-connection" />
        <span>MCP管理</span>
      </div>
      <div class="nav-item" @click="emit('nav', 'skill')">
        <el-icon><Collection /></el-icon>
        <span>Skill管理</span>
      </div>
      <div class="nav-item" @click="emit('nav', 'task')">
        <el-icon><AlarmClock /></el-icon>
        <span>自动化任务</span>
      </div>
    </nav>
    <div class="panel-section">会话记录</div>
    <div v-loading="loading" class="conversation-list">
      <div
        v-for="item in conversations"
        :key="item.id"
        :class="['conversation-item', { active: activeId === item.id }]"
        @click="emit('select', item)"
      >
        <div class="conversation-info">
          <span class="conversation-title">{{ item.title }}</span>
          <span class="conversation-time">{{ formatTime(item.update_time) }}</span>
        </div>
        <div class="conversation-actions">
          <!-- 三点更多操作：悬停展示 置顶/编辑/删除 菜单，command 与事件名对应 -->
          <el-dropdown trigger="hover" placement="bottom-end" @command="(cmd) => emit(cmd, item)">
            <el-icon :class="['action-icon', { pinned: item.is_pin === 1, hoverOnly: item.is_pin !== 1 }]" @click.stop>
              <MoreFilled />
            </el-icon>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="pin">{{ item.is_pin === 1 ? '取消置顶' : '置顶' }}</el-dropdown-item>
                <el-dropdown-item command="rename">编辑</el-dropdown-item>
                <el-dropdown-item command="remove" divided>删除</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>
      <div v-if="!conversations.length && !loading" class="conversation-empty">暂无会话</div>
    </div>
  </aside>
</template>

<script setup>
defineOptions({ name: 'AiAgentConversationPanel' });

defineProps({
  /** 会话列表（置顶优先，组内按更新时间倒序） */
  conversations: { type: Array, default: () => [] },
  /** 列表加载中 */
  loading: { type: Boolean, default: false },
  /** 当前选中会话 ID */
  activeId: { type: Number, default: 0 },
});

/** create=新建会话 / select(item) / remove(item) / pin(item) 置顶切换 / rename(item) 重命名 / nav('mcp'|'skill'|'task') */
const emit = defineEmits(['create', 'select', 'remove', 'pin', 'rename', 'nav']);

/**
 * 将接口返回的秒级时间戳、毫秒级时间戳或日期字符串统一转换为毫秒级时间戳。
 * 历史会话的数据字段可能来自不同版本，不能假设 update_time 始终为秒级数字。
 */
function toTimestamp(time) {
  if (time === null || time === undefined || time === '') return 0;

  const numericTime = Number(time);
  if (Number.isFinite(numericTime) && String(time).trim() !== '') {
    return numericTime < 100000000000 ? numericTime * 1000 : numericTime;
  }

  return Date.parse(String(time).trim().replace(' ', 'T')) || 0;
}

/** 会话时间展示：月-日 时:分 */
function formatTime(time) {
  const timestamp = toTimestamp(time);
  if (!timestamp) return '';

  const date = new Date(timestamp);
  if (Number.isNaN(date.getTime())) return '';

  const pad = (value) => String(value).padStart(2, '0');
  return `${date.getMonth() + 1}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}
</script>

<style scoped lang="scss">
.conversation-panel {
  display: flex;
  width: 240px;
  flex-shrink: 0;
  flex-direction: column;
  padding: 16px 12px;
  overflow-y: auto;
  background: var(--prev-bg-color);
  border-right: 1px solid var(--prev-border-color-lighter);
}
.panel-brand {
  margin: 0 8px 16px;
  color: var(--prev-color-text-regular);
  font-size: 17px;
  font-weight: 700;
}
.panel-nav {
  margin-bottom: 8px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 2px;
  padding: 9px 10px;
  color: var(--prev-color-text-regular);
  font-size: 14px;
  cursor: pointer;
  border-radius: 8px;
  i {
    color: var(--prev-color-text-primary);
    font-size: 16px;
  }
  &:hover {
    background: var(--prev-color-hover);
  }
}
.panel-section {
  margin: 12px 8px 6px;
  color: var(--prev-color-text-secondary);
  font-size: 12px;
}
.conversation-list {
  flex: 1;
}
.conversation-item {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
  padding: 10px;
  cursor: pointer;
  border-radius: 8px;
}
.conversation-item:hover,
.conversation-item.active {
  color: var(--prev-color-primary);
  background: var(--prev-bg-white);
}
.conversation-info {
  display: flex;
  min-width: 0;
  flex: 1;
  flex-direction: column;
  /* 右侧留出右上角三点图标的位置，防止标题被遮挡 */
  padding-right: 24px;
}
.conversation-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.conversation-time {
  margin-top: 4px;
  color: var(--prev-color-text-secondary);
  font-size: 12px;
}
.conversation-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  /* dropdown 包裹层与图标对齐 */
  :deep(.el-dropdown) {
    display: flex;
    align-items: center;
  }
}
.action-icon {
  color: var(--prev-color-text-secondary);
  font-size: 16px;
  cursor: pointer;
  &:hover {
    color: var(--prev-color-primary);
  }
  /* 置顶中的会话：三点图标常显并高亮 */
  &.pinned {
    color: var(--prev-color-primary);
  }
  /* 非置顶会话的三点图标：悬停列表项时才显示 */
  &.hoverOnly {
    opacity: 0;
  }
}
.conversation-item:hover .hoverOnly {
  opacity: 1;
}
.conversation-empty {
  color: var(--prev-color-text-secondary);
  font-size: 13px;
  text-align: center;
}
</style>
