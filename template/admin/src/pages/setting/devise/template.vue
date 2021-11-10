<template>
  <div>
    Î
    <div class="i-layout-page-header">
      <router-link :to="{ path: '/admin/setting/pages/devise' }"
        ><Button icon="ios-arrow-back" size="small" class="mr20"
          >返回</Button
        ></router-link
      >
      <span class="ivu-page-header-title mr20">页面设计</span>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="flex-wrapper">
        <!-- :src="iframeUrl" -->
        <iframe
          class="iframe-box"
          :src="iframeUrl"
          frameborder="0"
          ref="iframe"
        ></iframe>
        <div>
          <div class="content">
            <rightConfig :name="configName" :pageId="pageId"></rightConfig>
          </div>
        </div>
        <links></links>
      </div>
    </Card>
  </div>
</template>

<script>
import { diyGetInfo, diySave } from "@/api/diy";
import { mapMutations } from "vuex";
import rightConfig from "@/components/rightConfig/index";
import links from "./links";
export default {
  name: "index",
  components: {
    rightConfig,
    links,
  },
  data() {
    return {
      configName: "",
      iframeUrl: "",
      setConfig: "",
      updataConfig: "",
      pageId: 0,
    };
  },
  created() {
    let pageId = this.$route.query.id;
    let names = this.$route.query.name;
    this.setConfig = "admin/" + names + "/setConfig";
    this.updataConfig = "admin/" + names + "/updataConfig";
    this.pageId = parseInt(pageId);
    this.iframeUrl = `${location.origin}?type=iframeWindow`;
    diyGetInfo(parseInt(pageId)).then((datas) => {
      let data = datas.data.info.value;
      this.upData(data);
    });
  },
  mounted() {
    //监听子页面给当前页面传值
    window.addEventListener("message", this.handleMessage, false);
  },
  methods: {
    //接收iframe值
    handleMessage(event) {
      if (event.data.name) {
        this.configName = event.data.name;
        this.add(event.data.name);
      }
    },
    add(data) {
      this.$store.commit(this.setConfig, data);
    },
    upData(data) {
      this.$store.commit(this.updataConfig, data);
    },
    // ...mapMutations({
    //     add: 'diy/setConfig',
    //     upData:'diy/updataConfig'
    // })
  },
};
</script>

<style scoped lang="stylus">
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
