<template>
  <el-row :gutter="16" class="dashboard-grid">
    <el-col
      v-for="item in menus"
      :key="item.path"
      v-bind="grid"
      class="dashboard-grid__col"
      v-auth="item.auth || undefined"
    >
      <el-card shadow="never" class="menu-card">
        <router-link :to="{ path: $routeProStr + item.path }" class="menu-link">
          <div class="menu-icon" :class="item.tone">
            <i class="crmeb-icon" :class="item.icon"></i>
          </div>
          <p class="menu-name">{{ item.name }}</p>
        </router-link>
      </el-card>
    </el-col>
  </el-row>
</template>

<script setup>
defineOptions({ name: 'gridMenu' });

const grid = {
  xl: 3,
  lg: 3,
  md: 6,
  sm: 6,
  xs: 12,
};

const menus = [
  {
    name: '用户',
    path: '/user/list',
    icon: 'CRMEB-110-yonghu1-xianxing',
    tone: 'tone-primary',
    auth: ['admin-user-user-index'],
  },
  {
    name: '订单',
    path: '/statistic/order',
    icon: 'CRMEB-166-tongji-xianxing',
    tone: 'tone-warning',
    auth: ['admin-statistic'],
  },
  {
    name: '商品',
    path: '/product/center/list',
    icon: 'CRMEB-196-shangpinguanli-xianxing',
    tone: 'tone-success',
    auth: ['admin-center-product-index'],
  },
  {
    name: '内容',
    path: '/cms/article/index',
    icon: 'CRMEB-208-wenzhangguanli-xianxing',
    tone: 'tone-info',
    auth: ['cms-article-index'],
  },
  {
    name: '装修',
    path: '/setting/mall_theme',
    icon: 'CRMEB-873-zhuti-xianxing',
    tone: 'tone-warning',
  },
  {
    name: 'AI 对话',
    path: '/aiagent/index',
    icon: 'CRMEB-663-AI-mianxing',
    tone: 'tone-primary',
    auth: ['aiagent-manage'],
  },
  {
    name: '设置',
    path: '/setting/system_config/129',
    icon: 'CRMEB-746-xitongshezhi-xianxing',
    tone: 'tone-success',
    auth: ['setting-system-config'],
  },
  {
    name: '插件',
    path: '/plugin/list',
    icon: 'CRMEB-717-yingyongliebiao-mianxing',
    tone: 'tone-info',
    auth: ['plugin-list'],
  },
];
</script>

<style lang="scss" scoped>
.dashboard-grid__col {
  margin-bottom: 16px;
}

.menu-card {
  transition: background-color 0.2s ease;

  :deep(.el-card__body) {
    padding: 0;
  }

  &:hover {
    background: var(--prev-bg-color);

    .menu-icon {
      transform: translateY(-1px);
    }
  }
}

.menu-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 18px 12px;
  color: inherit;
}

.menu-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  transition: transform 0.2s ease;

  i {
    font-size: 24px;
  }

  &.tone-primary {
    color: var(--prev-color-primary);
    background: var(--prev-color-primary-light-9);
  }

  &.tone-success {
    color: var(--el-color-success);
    background: var(--el-color-success-light-9);
  }

  &.tone-warning {
    color: var(--el-color-warning);
    background: var(--el-color-warning-light-9);
  }

  &.tone-info {
    color: var(--el-color-info);
    background: var(--el-color-info-light-9);
  }
}

.menu-name {
  margin-top: 10px;
  font-size: 13px;
  line-height: 20px;
  color: var(--prev-color-text-regular);
  white-space: nowrap;
}
</style>
