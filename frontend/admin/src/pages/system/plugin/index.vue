<template>
  <div>
    <!-- 顶部：主 Tab（应用插件/驱动插件）+ 上传按钮 -->
    <el-card :bordered="false" shadow="never" class="ivu-mt mb16">
      <div class="main-tab-bar">
        <div class="segment-tabs">
          <div class="segment-item" :class="{ active: activeMainTab === 'app' }" @click="activeMainTab = 'app'">
            应用插件
            <span class="segment-count">{{ appTotal }}</span>
          </div>
          <div class="segment-item" :class="{ active: activeMainTab === 'driver' }" @click="activeMainTab = 'driver'">
            驱动插件
            <span class="segment-count">{{ driverTotal }}</span>
          </div>
        </div>
        <el-button v-auth="['plugin-plugin-upload']" type="primary" v-db-click @click="openUploadDialog()">
          上传插件
        </el-button>
      </div>
    </el-card>

    <div v-loading="loading">
      <!-- 应用插件：按分类平铺往下展示 -->
      <template v-if="activeMainTab === 'app'">
        <el-card v-for="group in appGroups" :key="group.key" :bordered="false" shadow="never" class="ivu-mt mb16">
          <template #header>
            <div class="group-header">
              <span class="group-title">{{ group.label }}</span>
              <el-tag size="small" type="info" effect="plain">{{ group.items.length }}</el-tag>
            </div>
          </template>
          <el-row :gutter="16">
            <el-col v-for="item in group.items" :key="item.key" :xs="24" :sm="12" :md="8" :xl="6" class="mb16">
              <PluginCard
                :item="item"
                @install="handleInstall"
                @uninstall="handleUninstall"
                @enable="handleEnable"
                @disable="handleDisable"
                @upgrade="openUploadDialog"
              />
            </el-col>
          </el-row>
        </el-card>
        <el-card v-if="!appGroups.length" :bordered="false" shadow="never" class="ivu-mt">
          <el-empty description="暂无应用插件" />
        </el-card>
      </template>

      <!-- 驱动插件：按驱动类别 Tab 切换查看 -->
      <el-card v-else :bordered="false" shadow="never" class="ivu-mt mb16">
        <el-tabs v-if="driverCategories.length" v-model="currentDriverCategory">
          <el-tab-pane v-for="cat in driverCategories" :key="cat.key" :name="cat.key">
            <template #label>
              {{ cat.label }}<span class="tab-count">（{{ cat.items.length }}）</span>
            </template>
            <el-row :gutter="16" class="driver-pane">
              <el-col v-for="item in cat.items" :key="item.key" :xs="24" :sm="12" :md="8" :xl="6" class="mb16">
                <PluginCard
                  :item="item"
                  @install="handleInstall"
                  @uninstall="handleUninstall"
                  @enable="handleEnable"
                  @disable="handleDisable"
                  @upgrade="openUploadDialog"
                />
              </el-col>
            </el-row>
          </el-tab-pane>
        </el-tabs>
        <el-empty v-else description="暂无驱动插件" />
      </el-card>
    </div>

    <!-- 上传插件对话框 -->
    <el-dialog
      :title="uploadTarget ? `升级插件：${uploadTarget.name}` : '上传插件'"
      v-model="uploadVisible"
      width="450px"
      :close-on-click-modal="false"
    >
      <el-upload
        ref="upload"
        :auto-upload="false"
        :limit="1"
        :on-change="handleFileChange"
        :before-upload="handleBeforeUpload"
        accept=".zip"
        drag
        action=""
      >
        <i class="el-icon-upload"></i>
        <div class="el-upload__text">将插件 ZIP 包拖拽到此处，或<em>点击选择</em></div>
        <template #tip><div class="el-upload__tip">仅支持 .zip 格式的插件安装包，大小不超过 50MB</div></template>
      </el-upload>
      <template #footer
        ><span>
          <el-button @click="uploadVisible = false">取消</el-button>
          <el-button type="primary" :loading="uploading" @click="handleUpload">
            {{ uploading ? '上传中...' : '确定上传' }}
          </el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  pluginListApi,
  pluginInstallApi,
  pluginUninstallApi,
  pluginEnableApi,
  pluginDisableApi,
  pluginUploadApi,
} from '@/api/plugin';
import { getMenusUnique } from '@/api/systemMenus';
import { formatFlatteningRoutes } from '@/libs/system';
import bus from '@/utils/bus';
import { useUserInfoStore } from '@/store/module/userInfo';
import { useMenusStore } from '@/store/module/menus';
import { useRoutesListStore } from '@/store/module/routesList';
import PluginCard from './components/PluginCard.vue';

defineOptions({ name: 'plugin_list' });

const upload = ref(null);

const loading = ref(false);
const uploading = ref(false);
const list = ref([]);
const uploadVisible = ref(false);
const uploadFile = ref(null);
const uploadTarget = ref(null);

// 主 Tab：应用插件 / 驱动插件
const activeMainTab = ref('app');

// 应用插件分类定义（key → 显示名称 + 匹配的 type 值），按展示顺序排列
const appCategoryGroups = {
  product: { label: '产品类型', match: ['product', 'product_type'] },
  mode: { label: '交易模式', match: ['mode'] },
  marketing: { label: '营销活动', match: ['marketing', 'activity'] },
  user: { label: '用户增值', match: ['user'] },
  channel: { label: '用户渠道', match: ['channel'] },
  distribution: { label: '分销代理', match: ['distribution', 'agent'] },
  service: { label: '服务扩展', match: ['service'] },
  system: { label: '系统组件', match: ['system', 'notice', 'business'] },
};

// 驱动类别中文映射（path 第二段 → 类别名），按 tab 展示顺序排列
const driverCategoryLabels = {
  pay: '支付',
  sms: '短信',
  upload: '存储',
  express: '电子面单',
  logistics: '物流',
  invoice: '电子发票',
  email: '邮件',
  bot: '机器人',
  copyproduct: '复制商品',
  printer: '小票打印',
};

// 应用插件分组：只展示有数据的分类，未归类的进"其它"分组
const appGroups = computed(() => {
  const groups = [];
  const knownTypes = new Set();
  for (const [key, group] of Object.entries(appCategoryGroups)) {
    const items = list.value.filter((item) => item.type !== 'driver' && group.match.includes(item.type));
    if (items.length) {
      groups.push({ key, label: group.label, items });
      group.match.forEach((t) => knownTypes.add(t));
    }
  }
  const otherItems = list.value.filter((item) => item.type !== 'driver' && !knownTypes.has(item.type));
  if (otherItems.length) {
    groups.push({ key: 'other', label: '其它', items: otherItems });
  }
  return groups;
});

// 主 Tab 总数徽标
const appTotal = computed(() => appGroups.value.reduce((sum, g) => sum + g.items.length, 0));
const driverTotal = computed(() => driverCategories.value.reduce((sum, c) => sum + c.items.length, 0));

// 驱动插件按类别分组（类别从 path 第二段提取，如 driver/sms/Aliyun → sms）
const driverCategories = computed(() => {
  const map = new Map();
  for (const item of list.value) {
    if (item.type !== 'driver') continue;
    const catKey = driverCategoryOf(item.path);
    if (!map.has(catKey)) {
      map.set(catKey, { key: catKey, label: driverCategoryLabels[catKey] || catKey, items: [] });
    }
    map.get(catKey).items.push(item);
  }
  // 已知类别按映射顺序排列，未知类别排后面
  const order = Object.keys(driverCategoryLabels);
  return [...map.values()].sort((a, b) => {
    const ia = order.indexOf(a.key);
    const ib = order.indexOf(b.key);
    return (ia === -1 ? order.length : ia) - (ib === -1 ? order.length : ib);
  });
});

// 当前选中的驱动类别 tab，数据变化后自动校正为有效项
const currentDriverCategory = ref('');
watch(driverCategories, (cats) => {
  if (!cats.some((cat) => cat.key === currentDriverCategory.value)) {
    currentDriverCategory.value = cats.length ? cats[0].key : '';
  }
});

/**
 * 从插件 path 提取驱动类别
 * @param {string} path 插件路径，如 driver/sms/Aliyun
 * @returns {string} 驱动类别 key
 */
function driverCategoryOf(path) {
  const parts = String(path || '')
    .split('/')
    .filter(Boolean);
  return parts.length >= 2 ? parts[1] : 'other';
}

getList();

function openUploadDialog(row = null) {
  uploadTarget.value = row;
  uploadFile.value = null;
  uploadVisible.value = true;
}
function getList() {
  loading.value = true;
  pluginListApi()
    .then((res) => {
      list.value = res.data || [];
    })
    .finally(() => {
      loading.value = false;
    });
}
// 刷新菜单缓存
function refreshMenus() {
  getMenusUnique().then((res) => {
    const data = res.data;
    useUserInfoStore().setUniqueAuth(data.uniqueAuth);
    useMenusStore().getmenusNav(data.menus);
    useRoutesListStore().setRoutesList(data.menus);
    const routes = formatFlatteningRoutes(data.menus);
    useMenusStore().setOneLvRoute(routes);
    // 同步更新本地缓存，避免页面刷新或导航切换后菜单显示不一致
    window.localStorage.setItem('menuList', JSON.stringify(data.menus));
    bus.emit('routesListChange');
  });
}
function handleInstall(row) {
  ElMessageBox.confirm(`确定安装插件「${row.name}」吗？`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'info',
  }).then(() => {
    pluginInstallApi({ plugin_key: row.key })
      .then(() => {
        ElMessage.success('安装成功');
        getList();
        refreshMenus();
      })
      .catch((error) => {
        const message = (error && (error.msg || error.message)) || '安装失败';
        // 依赖链信息较长，用弹窗保证用户能完整看到缺失的依赖插件。
        if (message.indexOf('依赖插件') !== -1 || message.indexOf('module:') !== -1) {
          ElMessageBox.alert(message, '插件安装失败', {
            confirmButtonText: '知道了',
            type: 'warning',
          });
        } else {
          ElMessage.error(message);
        }
      });
  });
}
function handleUninstall(row) {
  ElMessageBox.confirm(`确定卸载插件「${row.name}」吗？卸载将删除相关数据表和菜单，此操作不可恢复！`, '危险操作', {
    confirmButtonText: '确定卸载',
    cancelButtonText: '取消',
    type: 'warning',
  }).then(() => {
    pluginUninstallApi({ plugin_key: row.key })
      .then(() => {
        ElMessage.success('卸载成功');
        getList();
        refreshMenus();
      })
      .catch((error) => {
        ElMessage.error((error && error.msg) || '卸载失败');
      });
  });
}
function handleEnable(row) {
  pluginEnableApi({ plugin_key: row.key }).then(() => {
    ElMessage.success('启用成功');
    getList();
    refreshMenus();
  });
}
function handleDisable(row) {
  ElMessageBox.confirm(`确定禁用插件「${row.name}」吗？禁用后插件功能将不可用。`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  }).then(() => {
    pluginDisableApi({ plugin_key: row.key }).then(() => {
      ElMessage.success('禁用成功');
      getList();
      refreshMenus();
    });
  });
}
function handleFileChange(file) {
  uploadFile.value = file.raw;
}
function handleBeforeUpload(file) {
  const isZip = file.name.endsWith('.zip');
  if (!isZip) {
    ElMessage.error('仅支持 ZIP 格式的插件安装包');
    return false;
  }
  uploadFile.value = file;
  return false;
}
function handleUpload() {
  if (!uploadFile.value) {
    ElMessage.warning('请先选择插件安装包');
    return;
  }
  uploading.value = true;
  const formData = new FormData();
  formData.append('plugin_package', uploadFile.value);

  pluginUploadApi(formData)
    .then((res) => {
      const data = res.data || {};
      if (data.action === 'upgrade') {
        ElMessage.success(`插件「${data.name}」已从 ${data.from_version} 升级到 ${data.version}`);
      } else {
        ElMessage.success(`插件「${data.name}」上传成功`);
      }
      uploadVisible.value = false;
      uploadFile.value = null;
      uploadTarget.value = null;
      upload.value.clearFiles();
      getList();
      if (data.action === 'upgrade') refreshMenus();
    })
    .catch((err) => {
      ElMessage.error(err.msg || '上传失败');
    })
    .finally(() => {
      uploading.value = false;
    });
}
</script>

<style scoped>
/* 主 Tab 栏：分段切换器 + 上传按钮同行 */
.main-tab-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
/* 分段胶囊切换器 */
.segment-tabs {
  display: inline-flex;
  background: #f2f3f5;
  border-radius: 8px;
  padding: 4px;
  gap: 4px;
}
.segment-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 22px;
  border-radius: 6px;
  font-size: 14px;
  color: #4e5969;
  cursor: pointer;
  user-select: none;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
}
.segment-item:hover {
  color: #1d2129;
}
.segment-item.active {
  background: #fff;
  color: var(--el-color-primary);
  font-weight: 600;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.1);
}
.segment-count {
  min-width: 20px;
  text-align: center;
  font-size: 12px;
  line-height: 18px;
  border-radius: 9px;
  padding: 0 5px;
  background: #e5e6eb;
  color: #86909c;
}
.segment-item.active .segment-count {
  background: var(--el-color-primary-light-9, #ecf5ff);
  color: var(--el-color-primary);
}
/* 分组标题 */
.group-header {
  display: flex;
  align-items: center;
  gap: 8px;
}
.group-title {
  font-size: 15px;
  font-weight: 600;
  color: #17233d;
}
/* 驱动类别 tab 数量 */
.tab-count {
  font-size: 12px;
  color: #999;
}
/* 驱动 tab 面板顶部留白 */
.driver-pane {
  padding-top: 8px;
}
</style>
