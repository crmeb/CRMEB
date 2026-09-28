<template>
  <div>
    <div class="i-layout-page-header">
      <router-link :to="{ path: $routeProStr + '/setting/pages/devise' }"
        ><el-button class="mr20">返回</el-button></router-link
      >
      <span class="ivu-page-header-title mr20">页面设计</span>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="flex-wrapper">
        <!-- :src="iframeUrl" -->
        <iframe class="iframe-box" :src="iframeUrl" frameborder="0" ref="iframe"></iframe>
        <div>
          <div class="content">
            <rightConfig :name="configName" :pageId="pageId"></rightConfig>
          </div>
        </div>
        <links></links>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { diyGetInfo } from '@diy/api';
import Setting from '@/setting';
import { toHttpsUrl } from '@/libs/util';
import rightConfig from '@diy/components/legacy/rightConfig/index';
import links from './links';
import { useMorenStore } from '@diy/store/legacy/moren';

defineOptions({ name: 'index' });

const route = useRoute();
const morenStore = useMorenStore();

const configName = ref('');
const iframeUrl = ref('');
const pageId = ref(0);

// 原 Vuex 写法为动态 commit（this.$store.commit(`${routePre}/${names}/setConfig`)），
// 迁移 Pinia 后按迁移后同名页面 devisePage/index.vue 的约定直接调用 moren store
function add(data) {
  morenStore.setConfig(data);
}
function upData(data) {
  morenStore.updataConfig(data);
}

// 接收iframe值
function handleMessage(event) {
  if (event.data.name) {
    configName.value = event.data.name;
    add(event.data.name);
  }
}

{
  let pageIdQuery = route.query.id;
  pageId.value = parseInt(pageIdQuery);
  iframeUrl.value = `${toHttpsUrl(Setting.h5BaseURL) || location.origin}/pages/index/index?mdType=iframeWindow`;
  diyGetInfo(parseInt(pageIdQuery)).then((datas) => {
    let data = datas.data.info.value;
    upData(data);
  });
}

onMounted(() => {
  //监听子页面给当前页面传值
  window.addEventListener('message', handleMessage, false);
});
</script>

<style lang="scss" scoped>
.content {
  width: 450px;
}
.flex-wrapper {
  display: flex;
}
.iframe-box {
  width: 375px;
  height: 700px;
  /* border: 1px solid #ddd; */
  border-radius: 4px;
  box-shadow: 0 0 7px #cccccc;
}
.right-box {
  width: 400px;
  margin-left: 50px;
  border: 1px solid #ddd;
  border-radius: 4px;
  .title-bar {
    width: 100%;
    height: 38px;
    line-height: 38px;
    padding-left: 24px;
    color: #333;
    border-radius: 4px;
    border-bottom: 1px solid #eee;
  }
}
</style>
