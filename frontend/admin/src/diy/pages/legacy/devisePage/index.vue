<template>
  <div>
    <pages-header
      ref="pageHeader"
      title="页面设计"
      :backUrl="routePre + '/setting/pages/devise/0'"
      description="配置页面组件与属性，实时预览移动端效果，保存后同步到前台。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt15">
      <div class="flex-wrapper">
        <!-- :src="iframeUrl" -->
        <iframe class="iframe-box" :src="iframeUrl" frameborder="0" ref="iframe"></iframe>
        <div>
          <div class="content">
            <rightConfig :name="configName" :pageId="pageId" :configNum="configNum"></rightConfig>
          </div>
        </div>
        <!-- <links v-if="show"></links> -->
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { diyGetInfo, diySave } from '@diy/api';
import rightConfig from '@diy/components/legacy/rightConfig/index';
import { getCookies, setCookies } from '@/libs/util';
import { useMorenStore } from '@diy/store/legacy/moren';
import Setting from '@/setting';

defineOptions({ name: 'index' });

const route = useRoute();
const morenStore = useMorenStore();

const pageHeader = ref(null);
const iframe = ref(null);

const configName = ref({});
const configNum = ref('default');
const iframeUrl = ref('');
const setConfig = ref('');
const updataConfig = ref('');
const pageId = ref(0);

// 接收iframe值
function handleMessage(event) {
  if (event.data.name) {
    let obj = { name: event.data.name, num: event.data.dataName };
    configName.value = obj;
    configNum.value = event.data.dataName;
    add(event.data.name);
  }
}
function add(data) {
  morenStore.setConfig(data);
}
function upData(data) {
  morenStore.updataConfig(data);
}
// ...mapMutations({
//     add: 'diy/setConfig',
//     upData:'diy/updataConfig'
// })

let show = true;
{
  let routePageId = route.query.id;
  let defaultData = morenStore.defaultConfig;
  pageId.value = parseInt(routePageId);
  let moveLink = getCookies('moveLink');
  if (Number(route.query.type) === 1) {
    iframeUrl.value = `${moveLink}/pages/index/index?mdType=iframeWindow`;
  } else {
    iframeUrl.value = `${Setting.h5BaseURL || location.origin}/pages/index/index?mdType=iframeWindow`;
  }
  diyGetInfo(parseInt(routePageId)).then((datas) => {
    let data = datas.data.info.value;
    if (data) {
      upData(data);
    } else {
      diySave(parseInt(routePageId), {
        value: defaultData,
      }).then((res) => {});
    }
  });
}

onMounted(() => {
  //监听子页面给当前页面传值
  window.addEventListener('message', handleMessage, false);
});
</script>

<style lang="scss" scoped>
.content {
}
.flex-wrapper {
  display: flex;
}
.iframe-box {
  min-width: 375px;
  height: 700px;
  /* border: 1px solid #ddd; */
  border-radius: 4px;
  box-shadow: 0 0 7px #cccccc;
}
.right-box {
  width: 500px;
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
