<template>
  <div>
    <pages-header ref="pageHeader" title="页面设计" :backUrl="$routeProStr + '/setting/pages/devise/0'"></pages-header>
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

<script>
import { diyGetInfo, diySave } from '@/api/diy';
import { mapMutations } from 'vuex';
import rightConfig from '@/components/rightConfig/index';
import links from './links';
import { getCookies, setCookies } from '@/libs/util';
export default {
  name: 'index',
  components: {
    rightConfig,
    links,
  },
  data() {
    return {
      configName: {},
      configNum: 'default',
      iframeUrl: '',
      setConfig: '',
      updataConfig: '',
      pageId: 0,
    };
  },
  created() {
    this.show = true;
    let pageId = this.$route.query.id;
    let defaultData = this.$store.state.moren.defaultConfig;
    this.pageId = parseInt(pageId);
    let moveLink = getCookies('moveLink');
    if (Number(this.$route.query.type) === 1) {
      this.iframeUrl = `${moveLink}/pages/index/index?mdType=iframeWindow`;
    } else {
      this.iframeUrl = `${location.origin}/pages/index/index?mdType=iframeWindow`;
    }
    diyGetInfo(parseInt(pageId)).then((datas) => {
      let data = datas.data.info.value;
      if (data) {
        this.upData(data);
      } else {
        diySave(parseInt(pageId), {
          value: defaultData,
        }).then((res) => {});
      }
    });
  },
  mounted() {
    //监听子页面给当前页面传值
    window.addEventListener('message', this.handleMessage, false);
  },
  methods: {
    //接收iframe值
    handleMessage(event) {
      if (event.data.name) {
        let obj = { name: event.data.name, num: event.data.dataName };
        this.configName = obj;
        this.configNum = event.data.dataName;
        this.add(event.data.name);
      }
    },
    add(data) {
      this.$store.commit('moren/setConfig', data);
    },
    upData(data) {
      this.$store.commit('moren/updataConfig', data);
    },
    // ...mapMutations({
    //     add: 'diy/setConfig',
    //     upData:'diy/updataConfig'
    // })
  },
};
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
