<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          危险操作集中区：清理回收站、测试订单等数据，删除后不可恢复，请先备份。
        </span>
      </div>
      <span class="clear_tit">
        <i class="el-icon-info" style="color: #ed4014" />
        <span>清除数据请谨慎，清除就无法恢复哦！</span>
      </span>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row :gutter="24">
        <el-col v-bind="grid" class="mb20" v-for="(item, index) in tabList" :key="index">
          <div class="clear_box">
            <span class="clear_box_sp1" v-text="item.title"></span>
            <span class="clear_box_sp2" v-text="item.tlt"></span>
            <el-button
              :type="item.typeName"
              v-text="item.typeName === 'primary' ? '立即更换' : '立即清理'"
              v-db-click
              @click="onChange(item)"
            ></el-button>
          </div>
        </el-col>
      </el-row>
    </el-card>
    <!-- 更换域名-->
    <el-dialog v-model="modals" class="tableBox" title="更换域名" width="540px" :close-on-click-modal="false">
      <div class="acea-row row-column">
        <span>请输入需要替换的域名，格式为：http://域名。</span>
        <span>替换规则：会使用当前[设置]里面的[网站域名]去替换成当前您输入的域名。</span>
        <span class="mb15">替换成功后再去更换[网站域名]。</span>
        <el-input v-model="value6" type="textarea" :rows="4" placeholder="请输入网站域名..." />
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="modals = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="changeYU">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { replaceSiteUrlApi } from '@/api/system';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'systemCleardata' });

const value6 = ref('');
const modals = ref(false);
const grid = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 24,
  xs: 24,
};
const tabList = [
  {
    title: '更换域名',
    tlt: '替换所有本地上传的图片域名',
    typeName: 'primary',
    type: '11',
  },
  {
    title: '清除用户生成的临时附件',
    tlt: '清除用户生成的临时附件，不会影响商品图',
    typeName: 'error',
    type: 'temp',
  },
  {
    title: '清除回收站商品',
    tlt: '清除回收站商品，谨慎操作',
    typeName: 'error',
    type: 'recycle',
  },
  {
    title: '清除用户数据',
    tlt: '用户相关的所有表都将被清除，谨慎操作',
    typeName: 'error',
    type: 'user',
  },
  {
    title: '清除商城数据',
    tlt: '清除所有商城数据，谨慎操作',
    typeName: 'error',
    type: 'store',
  },
  {
    title: '清除商品分类',
    tlt: '会清除所有商品分类，谨慎操作',
    typeName: 'error',
    type: 'category',
  },
  {
    title: '清除订单数据',
    tlt: '清除用户所有订单数据，谨慎操作',
    typeName: 'error',
    type: 'order',
  },
  {
    title: '清除客服数据',
    tlt: '清除添加的客服数据，谨慎操作',
    typeName: 'error',
    type: 'kefu',
  },
  {
    title: '清除微信数据',
    tlt: '清除微信菜单保存数据，微信关键字无效回复',
    typeName: 'error',
    type: 'wechat',
  },
  {
    title: '清除内容分类',
    tlt: '清除添加的文章和文章分类,谨慎操作',
    typeName: 'error',
    type: 'article',
  },
  {
    title: '清除所有附件',
    tlt: '清除所有附件用户生成和后台上传,谨慎操作',
    typeName: 'error',
    type: 'attachment',
  },
  {
    title: '清除系统记录',
    tlt: '清除系统记录,谨慎操作',
    typeName: 'error',
    type: 'system',
  },
];

// 清除数据
function onChange(item) {
  if (item.type === '11') {
    modals.value = true;
  } else {
    clearFroms(item);
  }
}
function clearFroms(item) {
  let delfromData = {
    title: item.title,
    url: `system/clear/${item.type}`,
    method: 'get',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 更换域名
function changeYU() {
  replaceSiteUrlApi({ url: value6.value })
    .then((res) => {
      modals.value = false;
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.clear_tit {
  align-items: center;
  margin: 15px;
  span {
    font-size: 14px;
    color: #ed4014;
  }
}
.clear_box {
  border: 1px solid #dadfe6;
  border-radius: 3px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 10px;
  box-sizing: border-box;
  .clear_box_sp1 {
    font-size: 16px;
    color: #000000;
    display: block;
  }
  .clear_box_sp2 {
    font-size: 14px;
    color: #808695;
    display: block;
    margin: 12px 0;
  }
}
.clear_box :deep(.ivu-btn-error) {
  color: #fff;
  background-color: #ed4014;
  border-color: #ed4014;
}
.product_tabs :deep(.ivu-page-header-title) {
  margin-bottom: 0 !important;
}
</style>
