<template>
  <div class="sidebar-container" :class="{ collapsed }">
    <div class="back-btn" @click="goBack">
      <i class="el-icon-arrow-left"></i>
      <span v-if="!collapsed">返回</span>
    </div>

    <div class="menu-list">
      <div
        class="menu-item"
        v-for="item in menuList"
        :key="item.key"
        :class="{ active: activeMenu === item.key }"
        @click="selectMenu(item.key)"
      >
        <span class="iconfont" :class="item.icon"></span>
        <span class="menu-name" v-if="!collapsed">{{ item.name }}</span>
      </div>
    </div>

    <div class="bottom-menu" @click="toggle">
      <div class="menu-item">
        <i :class="collapsed ? 'el-icon-s-unfold' : 'el-icon-s-fold'"></i>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessageBox } from 'element-plus';

defineOptions({ name: 'Sidebar' });

const props = defineProps({
  activeMenu: {
    type: String,
    default: 'home',
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
  unsaved: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['change', 'save', 'toggle']);

const route = useRoute();
const router = useRouter();

const menuList = [
  { key: 'home', name: '商城首页', icon: 'iconic_home' },
  { key: 'category', name: '商品分类', icon: 'icona-ic_Picturearrangement' },
  { key: 'detail', name: '商品详情', icon: 'iconic_commodity' },
  { key: 'user', name: '个人中心', icon: 'icona-ic_user1' },
  { key: 'theme', name: '商城风格', icon: 'iconic_zhuti' },
];

function selectMenu(key) {
  if (props.activeMenu === key) return;
  let type = route.query.type;
  if (props.unsaved) {
    ElMessageBox.confirm('确定离开此页面？系统可能不会保存您所做的更改。', '提示', {
      confirmButtonText: '保存',
      cancelButtonText: '不保存',
      type: 'warning',
      distinguishCancelAndClose: true,
      showClose: false,
    })
      .then(() => {
        emit('save', type);
        emit('change', key);
      })
      .catch((action) => {
        emit('change', key);
      });
  } else {
    emit('change', key);
  }
}

function toggle() {
  emit('toggle');
}

function goBack() {
  let type = route.query.type;
  ElMessageBox.confirm('确定离开此页面？系统可能不会保存您所做的更改。', '提示', {
    confirmButtonText: '保存',
    cancelButtonText: '退出',
    type: 'warning',
    distinguishCancelAndClose: true,
    showClose: false,
  })
    .then(() => {
      emit('save', type);
      nextTick(() => {
        router.back();
      });
    })
    .catch(() => {
      router.back();
    });
}
</script>

<style lang="scss" scoped>
.sidebar-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: linear-gradient(180deg, #0550ff 0%, #009dff 100%);
  color: #fff;

  .back-btn {
    height: 50px;
    display: flex;
    align-items: center;
    padding: 0 20px;
    cursor: pointer;
    font-size: 14px;
    white-space: nowrap;

    i {
      margin-right: 5px;
    }
  }

  .menu-list {
    flex: 1;
    padding-top: 20px;

    .menu-item {
      display: flex;
      height: 60px;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s;
      opacity: 0.9;
      font-size: 14px;
      position: relative;
      background-color: transparent;
      border-radius: 8px 0 0 8px;
      font-weight: 400;
      &::before,
      &::after {
        position: absolute;
        right: 0;
        content: '';
        width: 30px;
        height: 30px;
        border-radius: 100%;
        box-shadow: 0 0 0 15px transparent;
        transition: all 0.3s;
        z-index: 1;
      }

      &::before {
        top: -30px;
        clip-path: inset(50% 0 0 50%);
      }

      &::after {
        bottom: -30px;
        clip-path: inset(0 0 50% 50%);
      }

      // &:hover {
      //   opacity: 1;
      //   background: #fff;
      //   color: #1f74ff;
      //   margin-left: 8px;
      // }

      // &:hover::before,
      // &:hover::after {
      //   box-shadow: 0 0 0 15px #fff;
      // }

      &.active {
        opacity: 1;
        background: #fff;
        color: #1f74ff;
        margin-left: 8px;
      }

      &.active::before,
      &.active::after {
        box-shadow: 0 0 0 15px #fff;
      }

      .iconfont {
        font-size: 16px;
        font-weight: bold;
      }

      .menu-name {
        margin-left: 10px;
        font-size: 14px;
        white-space: nowrap;
      }
    }
  }

  .bottom-menu {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    .menu-item {
      height: 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      position: relative;

      i {
        font-size: 20px;
        margin-bottom: 5px;
      }
    }
  }

  &.collapsed {
    .menu-item .menu-name {
      display: none;
    }
    .back-btn span {
      display: none;
    }
  }
}
</style>
