<template>
  <div class="plugin-card">
    <!-- 头部：名称 + 状态 -->
    <div class="plugin-card__header">
      <span class="plugin-card__name" :title="item.name">{{ item.name }}</span>
      <el-tag v-if="item.enabled" size="small" type="success">已启用</el-tag>
      <el-tag v-else-if="item.installed" size="small" type="warning">已安装</el-tag>
      <el-tag v-else size="small" type="info">未安装</el-tag>
    </div>

    <!-- 标识 -->
    <div class="plugin-card__key">
      <span class="plugin-key-text" :title="item.key">{{ item.key }}</span>
    </div>

    <!-- 描述 -->
    <div class="plugin-card__desc" :title="item.description">{{ item.description || '暂无描述' }}</div>

    <!-- 元信息：可升级提示 -->
    <div v-if="hasUpdate" class="plugin-card__meta">
      <el-tag size="small" type="warning" effect="plain">可升级至 v{{ item.online_version }}</el-tag>
    </div>

    <!-- 底部：版本（左下角）+ 操作区（按安装/启用状态渲染） -->
    <div class="plugin-card__footer">
      <span class="plugin-card__version">
        v{{ item.version }}<template v-if="item.author"> · {{ item.author }}</template>
      </span>
      <div class="plugin-card__actions">
        <!-- 未安装 -->
        <el-button
          v-if="!item.installed"
          v-auth="['plugin-plugin-install']"
          type="primary"
          size="small"
          v-db-click
          @click="emit('install', item)"
          >安装</el-button
        >

        <!-- 已安装未启用 -->
        <template v-if="item.installed && !item.enabled">
          <el-button
            v-if="!item.required"
            v-auth="['plugin-plugin-enable']"
            type="success"
            size="small"
            v-db-click
            @click="emit('enable', item)"
            >启用</el-button
          >
          <el-button
            v-if="!item.required"
            v-auth="['plugin-plugin-uninstall']"
            type="danger"
            plain
            size="small"
            v-db-click
            @click="emit('uninstall', item)"
            >卸载</el-button
          >
        </template>

        <!-- 已启用（核心模块不允许禁用/卸载） -->
        <template v-if="item.enabled">
          <el-button
            v-if="!item.required"
            v-auth="['plugin-plugin-disable']"
            type="warning"
            size="small"
            v-db-click
            @click="emit('disable', item)"
            >禁用</el-button
          >
          <el-button
            v-if="!item.required"
            v-auth="['plugin-plugin-uninstall']"
            type="danger"
            plain
            size="small"
            v-db-click
            @click="emit('uninstall', item)"
            >卸载</el-button
          >
        </template>

        <!-- 在线版本高于当前版本时显示升级按钮（升级包上传后自动执行迁移） -->
        <el-button
          v-if="hasUpdate"
          v-auth="['plugin-plugin-upload']"
          type="primary"
          plain
          size="small"
          v-db-click
          @click="emit('upgrade', item)"
          >升级</el-button
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ name: 'PluginCard' });

const props = defineProps({
  // 插件数据项（后端 getStatusList 返回结构）
  item: {
    type: Object,
    required: true,
  },
});

// 操作事件：install/uninstall/enable/disable/upgrade
const emit = defineEmits(['install', 'uninstall', 'enable', 'disable', 'upgrade']);

// 是否有在线新版本：在线版本存在且高于当前版本时显示升级按钮
const hasUpdate = computed(() => {
  const online = String(props.item.online_version || '').trim();
  if (!online) return false;
  return compareVersion(online, props.item.version) > 0;
});

/**
 * 比较两个点分版本号大小（逐段按数字比较，缺失段按 0 处理）
 * @param {string} a 版本号 a，如 1.2.0
 * @param {string} b 版本号 b
 * @returns {number} a>b 返回 1，a<b 返回 -1，相等返回 0
 */
function compareVersion(a, b) {
  const pa = String(a).split('.');
  const pb = String(b).split('.');
  const len = Math.max(pa.length, pb.length);
  for (let i = 0; i < len; i++) {
    const na = parseInt(pa[i], 10) || 0;
    const nb = parseInt(pb[i], 10) || 0;
    if (na > nb) return 1;
    if (na < nb) return -1;
  }
  return 0;
}
</script>

<style scoped>
.plugin-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 14px 16px;
  background: #fff;
  border: 1px solid #e7e7eb;
  border-radius: 6px;
  transition: box-shadow 0.2s;
}
.plugin-card:hover {
  box-shadow: 0 4px 12px rgba(0, 21, 41, 0.1);
}
.plugin-card__header {
  display: flex;
  align-items: center;
  gap: 8px;
}
.plugin-card__name {
  flex: 1;
  min-width: 0;
  font-weight: 600;
  font-size: 15px;
  color: #17233d;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.plugin-card__key {
  margin-top: 8px;
}
.plugin-key-text {
  display: inline-block;
  max-width: 100%;
  font-family: Menlo, Consolas, monospace;
  font-size: 12px;
  color: #666;
  background: #f5f7f9;
  padding: 2px 6px;
  border-radius: 3px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.plugin-card__desc {
  margin-top: 8px;
  min-height: 40px;
  color: #808695;
  font-size: 13px;
  line-height: 20px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.plugin-card__meta {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.plugin-card__footer {
  margin-top: auto;
  padding-top: 10px;
  border-top: 1px solid #f2f3f5;
  display: flex;
  align-items: center;
  gap: 8px;
}
.plugin-card__version {
  flex: 1;
  min-width: 0;
  color: #999;
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.plugin-card__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 8px;
}
.plugin-card__actions .el-button {
  margin-left: 0;
}
</style>
