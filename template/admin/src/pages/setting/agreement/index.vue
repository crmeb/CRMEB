<template>
  <div class="agreemant">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px' }">
      <div>
        <el-tabs v-model="currentTab" @tab-click="changeTab">
          <el-tab-pane
            :label="item.label"
            :name="item.value.toString()"
            v-for="(item, index) in headerList"
            :key="index"
          />
        </el-tabs>
      </div>
    </el-card>

    <el-row class="content">
      <el-col :span="16">
        <WangEditor style="width: 100%" :content="formValidate.content" @editorContent="getEditorContent"></WangEditor>
      </el-col>
      <el-col :span="6" style="width: 33%">
        <div class="ifam">
          <div class="content" v-html="content"></div>
        </div>
      </el-col>
    </el-row>
    <!-- <el-row class="mb10 content">
      <el-button class="bnt" type="primary" v-db-click @click="save" :loading="loadingExist"
        >保存</el-button
      >
    </el-row> -->

    <el-card :bordered="false" shadow="never" class="fixed-card" :style="{ left: `${fixBottomWidth}` }">
      <div class="acea-row row-center">
        <el-button class="bnt" type="primary" v-db-click @click="save" :loading="loadingExist">保存</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import WangEditor from '@/components/wangEditor/index.vue';
import { getAgreements, setAgreements } from '@/api/system';

export default {
  components: { WangEditor },
  data() {
    return {
      loadingExist: false,
      currentTab: '1',
      headerList: [
        { label: '付费会员协议', value: '1' },
        { label: '代理商协议', value: '2' },
        { label: '隐私协议', value: '3' },
        { label: '用户协议', value: '4' },
        { label: '注销协议', value: '5' },
        { label: '积分协议', value: '6' },
        { label: '分销协议', value: '8' },
      ],
      ueConfig: {
        autoHeightEnabled: false,
        initialFrameHeight: 500,
        initialFrameWidth: '100%',
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
      id: 0,
      formValidate: {
        content: '',
      },
      content: '',
      spinShow: false,
    };
  },
  computed: {
    // 设置是否显示 tagsView
    fixBottomWidth() {
      let { layout, isCollapse } = this.$store.state.themeConfig.themeConfig;
      let w;
      if (['columns'].includes(layout)) {
        if (isCollapse) {
          w = '85px';
        } else {
          w = '265px';
        }
      } else if (['classic'].includes(layout)) {
        if (isCollapse) {
          w = '85px';
        } else {
          w = '180px';
        }
      } else if (['defaults', 'classic'].includes(layout)) {
        if (isCollapse) {
          w = '64px';
        } else {
          w = '180px';
        }
      } else {
        w = '0px';
      }
      return w;
    },
  },
  created() {
    this.changeTab(this.currentTab);
  },
  methods: {
    save() {
      this.formValidate.content = this.content;
      setAgreements(this.formValidate)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    getEditorContent(content) {
      this.content = content;
    },
    changeTab() {
      this.formValidate.content = ' ';
      getAgreements(this.currentTab).then((res) => {
        this.formValidate.id = res.data.id || 0;
        this.formValidate.type = res.data.type;
        this.formValidate.title = res.data.title;
        this.formValidate.content = res.data.content;
        this.content = res.data.content;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.agreemant {
  background-color: #fff;
}
.content {
  padding: 10px 16px;
}
.ifam {
  width: 344px;
  height: 644px;
  background: url('../../../assets/images/ag-phone.png') no-repeat center top;
  background-size: 344px 644px;
  padding: 40px 20px;
  padding-top: 50px;
  margin: 0 auto 0 20px;
  .content {
    height: 560px;
    overflow: hidden;
    scrollbar-width: none; /* firefox */
    -ms-overflow-style: none; /* IE 10+ */
    overflow-x: hidden;
    overflow-y: auto;
  }
  .content::-webkit-scrollbar {
    display: none; /* Chrome Safari */
  }
}
.new_tab {
  ::v-deep .ivu-tabs-nav .ivu-tabs-tab {
    padding: 4px 16px 20px !important;
    font-weight: 500;
  }
}
</style>
