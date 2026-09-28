<template>
  <el-row :gutter="16" class="dashboard-console-grid">
    <el-col v-bind="grid" class="ivu-mb" v-auth="['admin-user-user-index']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/user/list' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-110-yonghu1-xianxing" style="color: #0256ff"></i>
          </div>
          <p>用户</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb" v-auth="['admin-statistic']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/statistic/order' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-166-tongji-xianxing" style="color: #b37feb"></i>
          </div>
          <p>订单</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb" v-auth="['admin-center-product-index']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/product/center/list' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-196-shangpinguanli-xianxing" style="color: #0fc6c2"></i>
          </div>
          <p>商品</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb" v-auth="['cms-article-index']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/cms/article/index' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-208-wenzhangguanli-xianxing" style="color: #7da2ff"></i>
          </div>
          <p>内容</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/setting/mall_theme' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-873-zhuti-xianxing" style="color: #f7ba1e"></i>
          </div>
          <p>装修</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb" v-auth="['aiagent-manage']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/aiagent/index' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-663-AI-mianxing" style="color: #722ed1"></i>
          </div>
          <p>AI 对话</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb" v-auth="['setting-system-config']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/setting/system_config/129' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-746-xitongshezhi-xianxing" style="color: #9fdb1d"></i>
          </div>
          <p>设置</p>
        </router-link>
      </el-card>
    </el-col>
    <el-col v-bind="grid" class="ivu-mb" v-auth="['plugin-list']">
      <el-card shadow="never">
        <router-link :to="{ path: $routeProStr + '/plugin/list' }">
          <div class="icon">
            <i class="crmeb-icon CRMEB-717-yingyongliebiao-mianxing" style="color: #0fc6c2"></i>
          </div>
          <p>插件</p>
        </router-link>
      </el-card>
    </el-col>
  </el-row>
</template>
<script setup>
const grid = {
  xl: 3,
  lg: 3,
  md: 6,
  sm: 6,
  xs: 12,
};
</script>
<style lang="scss" scoped>
.dashboard-console-grid {
  text-align: center;
  .ivu-card-body {
    padding: 0;
  }
  .icon {
    width: 48px;
    height: 48px;
    background-color: #f6f6f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  i {
    font-size: 24px;
  }
  a {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: inherit;
    padding: 16px;
  }
  p {
    margin-top: 8px;
    font-size: 14px;
    font-weight: 400;
    color: var(--prev-color-text-regular);
    line-height: 22px;
    white-space: nowrap;
  }
}
</style>
