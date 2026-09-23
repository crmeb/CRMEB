<template>
  <div class="custom-design" ref="customDesignRef">
    <!-- Toolbar -->
    <div class="header">
      <div class="left">
        <span class="close-btn" @click="$emit('close')"><i class="icon el-icon-arrow-left"></i>返回</span>
        <span class="title">设计组件</span>
      </div>
      <div class="center"></div>
      <div class="right">
        <el-button @click="save(false)">保存</el-button>
        <el-button type="primary" @click="save(true)">保存关闭</el-button>
      </div>
    </div>

    <div class="body">
      <!-- Left Panel -->
      <div class="left-panel">
        <ComponentList :type="type" />

        <div class="height-setting">
          <div class="label">内容高度</div>
          <div class="control">
            <el-slider
              v-model="canvasHeight"
              :min="10"
              :max="1000"
              input-size="small"
              style="flex: 1; margin-right: 10px"
            ></el-slider>
            <el-input-number
              v-model="canvasHeight"
              :min="10"
              :max="1000"
              controls-position="right"
              style="width: 100px"
            ></el-input-number>
          </div>
        </div>

        <div class="layer-list">
          <div class="title">已选组件 ({{ componentData.length }})</div>
          <el-scrollbar style="height: calc(100% - 40px)">
            <draggable v-model="reversedComponentData" handle=".handle" animation="200" item-key="id">
              <template #item="{ element: item }">
                <div
                  class="layer-item"
                  :class="{ active: curComponent && curComponent.id === item.id }"
                  @click="setCurComponent(item, componentData.indexOf(item))"
                >
                  <span class="handle iconfont iconxingzhuangjiehe"></span>
                  <span class="name">{{ item.label || item.component }}</span>
                  <div class="actions">
                    <span class="iconfont iconic_edit2" @click.stop="editName(item)"></span>
                    <span class="iconfont iconic_Eyes" v-if="!item.isHidden" @click.stop="toggleHide(item)"></span>
                    <span class="iconfont iconic_eye" v-else style="color: #ccc" @click.stop="toggleHide(item)"></span>
                    <span
                      class="iconfont iconshanchu3"
                      @click.stop="deleteComponent(componentData.indexOf(item))"
                    ></span>
                  </div>
                </div>
              </template>
            </draggable>
          </el-scrollbar>
        </div>
      </div>

      <!-- Center Canvas -->
      <div class="center-panel" @contextmenu="handleContextMenu" @mousedown="handleBackgroundClick">
        <ContextMenu
          v-if="menuVisible"
          :visible="menuVisible"
          :top="menuTop"
          :left="menuLeft"
          :curComponent="curComponent"
          @action="handleMenuAction"
          @close="closeMenu"
        />
        <div class="history-controls">
          <div class="operation-bar">
            <div class="operation-item" :class="{ disabled: snapshotIndex <= 0 }" @click="snapshotIndex > 0 && undo()">
              <span class="iconfont iconshangyibu1"></span>
            </div>
            <div
              class="operation-item"
              :class="{ disabled: snapshotIndex === snapshotData.length - 1 }"
              @click="snapshotIndex < snapshotData.length - 1 && redo()"
            >
              <span class="iconfont iconshangyibu"></span>
            </div>
            <div class="divider"></div>
            <div class="operation-item text" @click="historyVisible = !historyVisible">历史记录</div>
          </div>
        </div>
        <div class="history-list" v-show="historyVisible">
          <div class="history-header">
            <span>历史记录</span>
            <i class="el-icon-close close-icon" @click.stop="historyVisible = false"></i>
          </div>
          <div class="history-content">
            <div
              class="history-item"
              v-for="(item, index) in snapshotData"
              :key="index"
              :class="{ active: index === snapshotIndex }"
              @click="handleHistorySelect(index)"
            >
              <span class="type">{{ item.type || '操作' }}</span>
              <div class="right-info">
                <span class="time">{{ item.time }}</span>
                <span class="iconfont icon-check" v-if="index === snapshotIndex">
                  <i class="el-icon-check"></i>
                </span>
                <span v-else class="iconfont"></span>
              </div>
            </div>
          </div>
        </div>
        <div class="component-actions" v-if="curComponent" :style="actionBarStyle">
          <div class="action-item" title="删除" @click="deleteComponent(curComponentIndex)">
            <span class="iconfont iconshanchu3"></span>
          </div>
          <div class="action-item" title="复制" @click="copyComponent(curComponent)">
            <i class="el-icon-document-copy"></i>
          </div>
          <div
            class="action-item"
            :class="{ disabled: isTop }"
            title="上移一层"
            @click="moveComponent({ index: curComponentIndex, type: 'up' })"
          >
            <i class="el-icon-arrow-up"></i>
          </div>
          <div
            class="action-item"
            :class="{ disabled: isBottom }"
            title="下移一层"
            @click="moveComponent({ index: curComponentIndex, type: 'down' })"
          >
            <i class="el-icon-arrow-down"></i>
          </div>
          <div
            class="action-item"
            :class="{ disabled: isTop }"
            title="置顶"
            @click="moveComponent({ index: curComponentIndex, type: 'top' })"
          >
            <span class="iconfont iconzhiding"></span>
          </div>
          <div
            class="action-item"
            :class="{ disabled: isBottom }"
            title="置底"
            @click="moveComponent({ index: curComponentIndex, type: 'bottom' })"
          >
            <span class="iconfont iconzhidi"></span>
          </div>
        </div>
        <div class="canvas-container">
          <Editor
            ref="editor"
            :componentData="componentData"
            :canvasStyle="{ width: canvasWidth + 'px', height: canvasHeight + 'px' }"
            :curComponent="curComponent"
            :type="type"
            :fieldList="fieldList"
            @update:componentData="(val) => (componentData = val)"
            @select="setCurComponent"
            @record="recordSnapshot"
            @delete="deleteComponent"
            @copy="copyComponent"
            @move="moveComponent"
            @multi-select="handleMultiSelect"
          />
        </div>
      </div>

      <!-- Right Panel -->
      <div class="right-panel">
        <AttrList
          v-if="curComponent || activeComponentIds.length > 1"
          :curComponent="curComponent"
          :activeComponentIds="activeComponentIds"
          :componentData="componentData"
          :canvasWidth="canvasWidth"
          :canvasHeight="canvasHeight"
          :type="type"
          :fieldList="fieldList"
          @change="handleAttrChange"
          @update-group="handleUpdateGroup"
        />
        <div v-else class="empty-tip">
          <img src="@/assets/images/no-sel-cum.png" alt="" />
          <p>暂未选中任何组件～</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import ComponentList from './ComponentList.vue';
import Editor from './Editor.vue';
import AttrList from './AttrList.vue';
import ContextMenu from './ContextMenu.vue';
import draggable from 'vuedraggable';
import { deepCopy } from './utils/utils';
import generateID from './utils/generateID';

import { getDiyField } from '@diy/api';

defineOptions({ name: 'CustomDesign' });

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
  columnNum: {
    type: Number,
    default: 1,
  },
  type: {
    type: String,
    default: 'user',
  },
});

const emit = defineEmits(['save', 'close']);

const customDesignRef = ref(null);
const editor = ref(null);

const componentData = ref([]);
const canvasHeight = ref(375);
const curComponent = ref(null);
const curComponentIndex = ref(-1);
const snapshotData = ref([]);
const snapshotIndex = ref(-1);
const fieldList = ref({});
const historyVisible = ref(false);
const activeComponentIds = ref([]);
const menuVisible = ref(false);
const menuTop = ref(0);
const menuLeft = ref(0);

const canvasWidth = computed(() => {
  // Default 375, divided by columns if > 1
  const base = 375;
  return props.columnNum > 1 ? base / props.columnNum : base;
});
const actionBarStyle = computed(() => {
  return {
    left: `calc(50% + ${canvasWidth.value / 2}px + 10px)`,
    top: '50%',
    transform: 'translateY(-50%)',
  };
});
const isTop = computed(() => {
  return curComponentIndex.value === componentData.value.length - 1;
});
const isBottom = computed(() => {
  return curComponentIndex.value === 0;
});
const reversedComponentData = computed({
  get() {
    return componentData.value.slice().reverse();
  },
  set(val) {
    componentData.value = val.slice().reverse();
    recordSnapshot('调整层级');
  },
});

// created
if (props.initialData) {
  if (props.initialData.list) {
    componentData.value = deepCopy(props.initialData.list);
  }
  if (props.initialData.canvasHeight) {
    canvasHeight.value = props.initialData.canvasHeight;
  }
}
recordSnapshot();
getFieldList();

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
  window.addEventListener('click', closeMenu);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
  window.removeEventListener('click', closeMenu);
});

function handleBackgroundClick(e) {
  // Check if the click target is center-panel or canvas-container
  // to avoid triggering when clicking on controls
  if (e.target.classList.contains('center-panel') || e.target.classList.contains('canvas-container')) {
    curComponent.value = null;
    curComponentIndex.value = -1;
    activeComponentIds.value = [];
  }
}
function getFieldList() {
  getDiyField().then((res) => {
    fieldList.value = res.data;
  });
}
function setCurComponent(component, index) {
  curComponent.value = component;
  curComponentIndex.value = index;
}
function deleteComponent(index) {
  componentData.value.splice(index, 1);
  curComponent.value = null;
  curComponentIndex.value = -1;
  recordSnapshot('删除组件');
}
function toggleHide(item) {
  item['isHidden'] = !item.isHidden;
}
function editName(item) {
  ElMessageBox.prompt('请输入组件名称', '修改名称', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    inputValue: item.label,
  })
    .then(({ value }) => {
      item['label'] = value;
    })
    .catch(() => {});
}
function formatTime(date) {
  const h = date.getHours().toString().padStart(2, '0');
  const m = date.getMinutes().toString().padStart(2, '0');
  const s = date.getSeconds().toString().padStart(2, '0');
  return `${h}:${m}:${s}`;
}
function recordSnapshot(type = '操作') {
  // 简单的撤销重做实现
  snapshotIndex.value++;
  snapshotData.value[snapshotIndex.value] = {
    componentData: deepCopy(componentData.value),
    canvasHeight: canvasHeight.value,
    type,
    time: formatTime(new Date()),
  };
  // 删除后续的历史记录
  if (snapshotIndex.value < snapshotData.value.length - 1) {
    snapshotData.value = snapshotData.value.slice(0, snapshotIndex.value + 1);
  }

  // Limit to 15 records
  const limit = 15;
  if (snapshotData.value.length > limit) {
    const diff = snapshotData.value.length - limit;
    snapshotData.value.splice(0, diff);
    snapshotIndex.value -= diff;
  }
}
function undo() {
  if (snapshotIndex.value > 0) {
    snapshotIndex.value--;
    const data = deepCopy(snapshotData.value[snapshotIndex.value]);
    componentData.value = data.componentData;
    canvasHeight.value = data.canvasHeight;
    curComponent.value = null;
  }
}
function redo() {
  if (snapshotIndex.value < snapshotData.value.length - 1) {
    snapshotIndex.value++;
    const data = deepCopy(snapshotData.value[snapshotIndex.value]);
    componentData.value = data.componentData;
    canvasHeight.value = data.canvasHeight;
    curComponent.value = null;
  }
}
function copyComponent(item) {
  const component = deepCopy(item);
  component.id = generateID();
  component.style.top += 20;
  component.style.left += 20;
  componentData.value.push(component);
  recordSnapshot('复制组件');
}
function moveComponent({ index, type }) {
  const component = componentData.value[index];
  if (type === 'up') {
    if (index < componentData.value.length - 1) {
      componentData.value.splice(index, 1);
      componentData.value.splice(index + 1, 0, component);
      curComponentIndex.value = index + 1;
      recordSnapshot('上移一层');
    } else {
      ElMessage.warning('已经到顶了');
    }
  } else if (type === 'down') {
    if (index > 0) {
      componentData.value.splice(index, 1);
      componentData.value.splice(index - 1, 0, component);
      curComponentIndex.value = index - 1;
      recordSnapshot('下移一层');
    } else {
      ElMessage.warning('已经到底了');
    }
  } else if (type === 'top') {
    if (index < componentData.value.length - 1) {
      componentData.value.splice(index, 1);
      componentData.value.push(component);
      curComponentIndex.value = componentData.value.length - 1;
      recordSnapshot('置顶');
    } else {
      ElMessage.warning('已经到顶了');
    }
  } else if (type === 'bottom') {
    if (index > 0) {
      componentData.value.splice(index, 1);
      componentData.value.unshift(component);
      curComponentIndex.value = 0;
      recordSnapshot('置底');
    } else {
      ElMessage.warning('已经到底了');
    }
  }
}
function handleHistorySelect(index) {
  snapshotIndex.value = index;
  const data = deepCopy(snapshotData.value[snapshotIndex.value]);
  componentData.value = data.componentData;
  canvasHeight.value = data.canvasHeight;
  curComponent.value = null;
  historyVisible.value = false;
}
function handleAttrChange() {
  recordSnapshot('修改属性');
}
function handleUpdateGroup() {
  recordSnapshot('调整对齐/位置');
}
function save(close = false) {
  const data = {
    list: componentData.value,
    canvasHeight: canvasHeight.value,
  };
  emit('save', data);
  if (close) {
    emit('close');
  } else {
    ElMessage.success('保存成功');
  }
}
function handleMultiSelect(ids) {
  activeComponentIds.value = ids;
}
function handleKeydown(e) {
  // Avoid triggering when editing text
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

  if (e.ctrlKey || e.metaKey) {
    if (e.shiftKey && (e.key === 'z' || e.key === 'Z')) {
      e.preventDefault();
      redo();
    } else if (e.key === 'z' || e.key === 'Z') {
      e.preventDefault();
      undo();
    }
  } else if (e.key === 'Delete' || e.key === 'Backspace') {
    e.preventDefault();
    handleDeleteKey();
  }
}
function handleDeleteKey() {
  if (activeComponentIds.value && activeComponentIds.value.length > 0) {
    componentData.value = componentData.value.filter((item) => !activeComponentIds.value.includes(item.id));
    curComponent.value = null;
    curComponentIndex.value = -1;
    activeComponentIds.value = [];
    recordSnapshot('删除组件');
  } else if (curComponent.value) {
    deleteComponent(curComponentIndex.value);
  }
}
function closeMenu() {
  menuVisible.value = false;
}
function handleContextMenu(e) {
  e.preventDefault();
  e.stopPropagation();

  const panel = customDesignRef.value.querySelector('.center-panel');
  const rect = panel.getBoundingClientRect();

  menuLeft.value = e.clientX - rect.left;
  menuTop.value = e.clientY - rect.top;
  menuVisible.value = true;
}
function handleMenuAction(action) {
  if (!curComponent.value) return;

  switch (action) {
    case 'copy':
      copyComponent(curComponent.value);
      break;
    case 'delete':
      deleteComponent(curComponentIndex.value);
      break;
    case 'top':
      moveComponent({ index: curComponentIndex.value, type: 'top' });
      break;
    case 'bottom':
      moveComponent({ index: curComponentIndex.value, type: 'bottom' });
      break;
    case 'up':
      moveComponent({ index: curComponentIndex.value, type: 'up' });
      break;
    case 'down':
      moveComponent({ index: curComponentIndex.value, type: 'down' });
      break;
    case 'lock':
      curComponent.value['isLock'] = true;
      recordSnapshot('锁定组件');
      break;
    case 'unlock':
      curComponent.value['isLock'] = false;
      recordSnapshot('解锁组件');
      break;
  }
}
</script>

<style scoped lang="scss">
.custom-design {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f5f5f5;

  .header {
    height: 60px;
    background: #fff;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px 0 10px;
    .left {
      display: flex;
      align-items: center;
      .title {
        font-size: 16px;
        font-weight: 500;
        margin-left: 10px;
      }
    }
    .close-btn {
      display: flex;
      align-items: center;
      font-size: 14px;
      color: #333;
      cursor: pointer;
      position: relative;
      padding-right: 10px;
      border-right: 1px solid #eee;
    }
  }

  .body {
    flex: 1;
    display: flex;
    overflow: hidden;

    .left-panel {
      width: 300px;
      background: #fff;
      display: flex;
      flex-direction: column;

      .height-setting {
        padding: 15px;
        border-top: 6px solid #f0f2f5;
        border-bottom: 6px solid #f0f2f5;
        .label {
          margin-bottom: 10px;
          font-size: 14px;
          color: #333;
        }
        .control {
          display: flex;
          align-items: center;
        }
      }

      .layer-list {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        .title {
          padding: 10px 15px;
          color: #333;
          font-size: 14px;
          background: #f9f9f9;
        }
        .layer-item {
          display: flex;
          align-items: center;
          padding: 10px 15px;
          cursor: pointer;
          &:hover {
            background: var(--prev-color-primary-light-9);
          }
          &.active {
            background: var(--prev-color-primary-light-9);
            border-left: 3px solid var(--prev-color-primary);
          }
          .handle {
            cursor: move;
            margin-right: 10px;
            color: rgba(192, 196, 204, 1);
            font-size: 14px;
            line-height: 18px;
          }
          .name {
            flex: 1;
            font-size: 13px;
          }
          .actions {
            .iconfont {
              margin-left: 5px;
              color: rgba(187, 187, 187, 1);
              &:hover {
                color: var(--prev-color-primary);
              }
            }
            .iconic_edit2 {
              margin-right: 12px;
              padding-right: 12px;
              border-right: 1px solid rgba(51, 51, 51, 0.05);
            }
          }
        }
      }
    }

    .center-panel {
      flex: 1;
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
      padding-top: 70px;

      .canvas-container {
        width: 100%;
        height: 100%;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .history-controls {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100;
        background: #fff;
        padding: 5px;
        border-radius: 4px;
        // box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);

        .operation-bar {
          display: flex;
          align-items: center;
          height: 32px;

          .operation-item {
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #333;
            margin: 0 10px;
            transition: all 0.3s;

            &.disabled {
              color: #ccc;
              cursor: not-allowed;
            }

            &:hover:not(.disabled) {
              color: var(--prev-color-primary);
            }

            .iconfont {
              font-size: 16px;
            }

            &.text {
              font-size: 14px;
              color: #606266;
              &:hover {
                color: var(--prev-color-primary);
              }
              &.active {
                color: var(--prev-color-primary);
              }
            }
          }

          .divider {
            width: 1px;
            height: 16px;
            background: #e6e6e6;
            margin: 0 5px;
          }
        }
      }

      .history-list {
        position: absolute;
        top: 20px;
        right: 0;
        width: 187px;
        background: #fff;
        border: 1px solid #e4e7ed;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        z-index: 100;
        display: flex;
        flex-direction: column;

        .history-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 10px 15px;
          border-bottom: 1px solid #f0f0f0;
          font-size: 12px;
          font-weight: 400;
          color: #333;

          .close-icon {
            cursor: pointer;
            color: #333;
          }
        }

        .history-content {
          max-height: 300px;
          overflow-y: auto;
          padding: 5px 0;
        }

        .history-item {
          padding: 8px 15px;
          cursor: pointer;
          font-size: 12px;
          color: rgba(51, 51, 51, 1);
          display: flex;
          justify-content: space-between;
          align-items: center;

          &:hover {
            background-color: #f5f7fa;
          }
          &.active {
            color: #606266;
            background-color: #f5f7fa;
            // font-weight: bold;
          }

          .type {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }

          .right-info {
            display: flex;
            align-items: center;
            margin-left: 10px;

            .time {
              color: rgba(51, 51, 51, 1);
              font-size: 13px;
              margin-right: 8px;
            }

            .iconfont {
              color: var(--prev-color-primary);
              font-size: 12px;
              width: 12px;
            }
          }
        }
      }

      .component-actions {
        position: absolute;
        width: 32px;
        background: var(--prev-color-primary);
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        padding: 10px 0;
        z-index: 100;

        .action-item {
          width: 100%;
          height: 30px;
          display: flex;
          justify-content: center;
          align-items: center;
          cursor: pointer;
          color: #fff;
          transition: all 0.3s;
          position: relative;

          &.disabled {
            opacity: 0.3;
            cursor: not-allowed;
            pointer-events: none;
          }

          &:hover {
            background-color: rgba(255, 255, 255, 0.1);
          }
          i {
            font-size: 16px;
          }
        }
      }
    }

    .right-panel {
      width: 400px;
      background: #fff;
      overflow-y: auto;
      :deep(.el-form-item__label) {
        color: #999;
      }
      .empty-tip {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #999;
        img {
          width: 150px;
          margin-bottom: 20px;
        }
        p {
          font-size: 14px;
        }
      }
    }
  }
}
</style>
