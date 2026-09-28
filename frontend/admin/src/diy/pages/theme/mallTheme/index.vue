<template>
  <div class="mall-theme-page">
    <!-- 顶部标题栏 -->
    <div class="page-header">
      <div class="header-left">
        <span class="page-title">当前主题：{{ title }}</span>
        <div class="theme-colors" v-if="themeColors.length">
          <span
            class="color-dot"
            v-for="(item, index) in themeColors.slice(0, 3)"
            :key="index"
            :style="{ backgroundColor: item }"
          ></span>
        </div>
        <span class="status-tag" v-if="confuse">
          <span class="text">混搭使用中</span>
        </span>
      </div>
      <div class="header-right">
        <i class="el-icon-refresh-left icon"></i>
        <span @click="handleRestore">还原主题</span>
      </div>
    </div>

    <!-- 页面列表 -->
    <div class="theme-list">
      <div class="list-wrapper">
        <div class="list-item" v-for="(item, index) in pageList" :key="index">
          <div class="theme-card">
            <div class="card-header">
              <span class="card-title">{{ item.name || '未命名主题' }}</span>
              <div class="card-actions">
                <span class="action-btn" @click="handleEdit(item)">编辑</span>
                <el-divider direction="vertical"></el-divider>
                <span class="action-btn" @click="handleReplace(item, ['home', 'category', 'detail', 'user'][index])"
                  >更换</span
                >
              </div>
            </div>
            <div class="card-info">
              <div class="theme-name line1">{{ item.themeName }}</div>
              <div class="last-modified">上次修改：{{ item.updateTime }}</div>
            </div>
            <div class="card-preview">
              <div class="phone-mockup" :class="{ 'has-image': item.image }">
                <template v-if="item.image">
                  <div class="phone-notch"></div>
                  <img :src="item.image" class="preview-image" alt="preview" />
                </template>
                <div class="empty-container" v-else>
                  <img src="@/assets/images/no-theme-poster.png" class="empty-poster" alt="no poster" />
                  <div class="empty-text">
                    暂无主题封面
                    <el-tooltip content="请前往设计页面保存封面" placement="top">
                      <i class="el-icon-question"></i>
                    </el-tooltip>
                  </div>
                  <el-button type="primary" @click="handleEdit(item)">去编辑</el-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 更换主题弹窗 -->
    <theme-select-dialog
      v-model:visible="dialogVisible"
      activeTab="mall"
      type="mall"
      :theme-id="id"
      :current-type="currentType"
      @success="selectTheme"
      v-if="dialogVisible"
    ></theme-select-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import { restoreTheme, getThemeUsing as getThemeUsingApi } from '@diy/api';
import ThemeSelectDialog from '../components/themeSelect/index.vue';
import Setting from '@/setting';

defineOptions({ name: 'MallTheme' });

const router = useRouter();

const pageList = ref([]);
const themeColors = ref([]); // 模拟后端返回的主题色值
const dialogVisible = ref(false);
const title = ref('');
const is_diy = ref(false);
const id = ref('');
const confuse = ref(false);
const currentType = ref('');

// created
getThemeUsing();

function getThemeUsing() {
  getThemeUsingApi().then((res) => {
    // 解构变量与上面的响应式变量同名，别名避免遮蔽
    const { title: resTitle, data_info, theme_data, id: resId, confuse: resConfuse } = res.data;
    title.value = resTitle;
    id.value = resId;
    confuse.value = resConfuse;
    themeColors.value = Object.values(theme_data || {});
    pageList.value = data_info.map((item, index) => {
      return {
        id: item.key,
        name: getPageName(item.key),
        themeName: item.title,
        updateTime: item.update_time,
        image: item.image,
        type: ['home', 'category', 'detail', 'user'][index],
      };
    });
  });
}

function getPageName(key) {
  const names = {
    home: '商城首页',
    category: '商品分类页',
    detail: '商品详情页',
    user: '个人中心页',
  };
  return names[key] || key;
}

function handleEdit(item) {
  // 跳转到编辑页面，假设路由结构
  router.push({
    path: Setting.routePre + '/setting/edit_theme',
    query: { type: item.type, id: id.value },
  });
}

function handleReplace(item, type) {
  currentType.value = type;
  dialogVisible.value = true;
}

function handleRestore() {
  ElMessageBox.confirm('确定要还原主题吗？所有自定义修改将丢失。', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  })
    .then(() => {
      // 调用还原接口
      restoreTheme(id.value).then((res) => {
        ElMessage.success('主题已还原');
        getThemeUsing();
      });
    })
    .catch(() => {});
}

function selectTheme() {
  getThemeUsing();
}
</script>

<style>
body .v-modal {
  background: rgba(0, 0, 0, 0.5) !important;
  opacity: 1 !important;
}
</style>

<style lang="scss" scoped>
.mall-theme-page {
  min-height: 100%;

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 16px;
    background: var(--prev-bg-white);
    margin-bottom: 20px;
    border-radius: 4px;

    .header-left {
      display: flex;
      align-items: center;

      .page-title {
        font-size: 18px;
        font-weight: bold;
        color: var(--prev-color-text-regular);
        margin-right: 15px;
      }

      .theme-colors {
        display: flex;
        align-items: center;
        margin-right: 15px;

        .color-dot {
          width: 16px;
          height: 16px;
          border-radius: 50%;
          margin-right: -6px;
          position: relative;
          z-index: 5;

          &:first-child {
            z-index: 3;
          }

          &:nth-child(2) {
            z-index: 4;
          }
        }
      }

      .status-tag {
        display: flex;
        align-items: center;
        background: var(--prev-color-hover);
        padding: 2px 10px;
        border-radius: 4px;
        .text {
          font-size: 12px;
          color: var(--prev-color-text-regular);
        }
      }
    }
    .header-right {
      display: flex;
      align-items: center;
      font-size: 14px;
      color: var(--prev-color-text-regular);
      cursor: pointer;
      .icon {
        margin-right: 5px;
      }
      &:hover {
        color: var(--prev-color-primary-light-1);
      }
    }
  }
  .theme-list {
    padding: 16px;
    border-radius: 5px;
    background-color: var(--prev-bg-white);

    .list-wrapper {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -10px;
    }

    .list-item {
      min-width: 270px;
      width: 25%;
      padding: 0 10px;
      box-sizing: border-box;
    }
  }
  .theme-card {
    width: 100%;
    background: var(--prev-bg-white);
    border: 1px solid var(--prev-border-color-lighter);
    border-radius: 8px;
    padding: 17px;
    transition: all 0.3s;
    margin-bottom: 20px;

    &:hover {
      box-shadow: var(--el-box-shadow-light);
      transform: translateY(-2px);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;

      .card-title {
        font-size: 12px;
        color: var(--prev-color-text-regular);
        border-radius: 0px 20px 20px 0px;
        padding: 7px 12px;
        margin-left: -17px;
        background: var(--prev-color-hover);
      }

      .card-actions {
        .action-btn {
          font-size: 14px;
          color: var(--prev-color-primary);
          cursor: pointer;

          &:hover {
            color: var(--prev-color-primary-light-1);
          }
        }
      }
    }

    .card-info {
      margin-bottom: 15px;

      .theme-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--prev-color-text-regular);
        margin-bottom: 5px;
      }

      .last-modified {
        font-size: 12px;
        color: var(--prev-color-text-secondary);
      }
    }

    .card-preview {
      display: flex;
      justify-content: center;
      background: var(--prev-bg-color);
      padding: 0;
      border-radius: 12px;

      .phone-mockup {
        width: 100%;
        background: #fff;
        border-radius: 12px;
        overflow-y: scroll;
        position: relative;
        border: 1px solid #f9f9f9;
        max-height: calc(100vh - 360px);
        &.has-image {
          aspect-ratio: 1/2;
        }

        .preview-image {
          width: 100%;
          object-fit: cover;
        }

        .empty-container {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 75px 0 330px;
          height: 100%;
          min-height: 100%;
          background: #f9f9f9;
          .empty-poster {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
          }

          .empty-text {
            font-size: 14px;
            color: #999999;
            margin-bottom: 20px;

            i {
              color: #999;
              cursor: help;
            }
          }
        }
      }
      // 隐藏滚动条
      ::-webkit-scrollbar {
        display: none;
      }
    }
  }
}
</style>
